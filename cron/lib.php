<?php
/**
 * lib.php — shared article-generation engine.
 * Used by both cron/autoblog.php (scheduled) and admin/api.php (on-demand).
 */

if (!function_exists('ab_logline')) {
    function ab_logline($msg) {
        $log = __DIR__ . '/autoblog.log';
        file_put_contents($log, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND);
    }
}

function ab_paths() {
    $cronDir  = __DIR__;
    $siteRoot = dirname($cronDir);
    return [
        'cron'    => $cronDir,
        'root'    => $siteRoot,
        'queue'   => $cronDir . '/queue.json',
        'posts'   => $cronDir . '/posts.json',
        'prompt'  => $cronDir . '/prompt.txt',
        'sitemap' => $siteRoot . '/sitemap.xml',
    ];
}

function ab_slugify($s) {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function ab_save_json($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function ab_read_json($path, $fallback = []) {
    if (!file_exists($path)) return $fallback;
    $d = json_decode(@file_get_contents($path), true);
    return is_array($d) ? $d : $fallback;
}

function ab_anthropic_complete($cfg, $prompt) {
    $payload = json_encode([
        'model'      => $cfg['model'],
        'max_tokens' => (int)$cfg['max_tokens'],
        'messages'   => [[ 'role' => 'user', 'content' => $prompt ]],
    ]);
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'x-api-key: ' . $cfg['api_key'],
            'anthropic-version: 2023-06-01',
        ],
    ]);
    $out  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($out === false) { ab_logline("curl error: $err"); return [null, "Network error: $err"]; }
    if ($code !== 200) { ab_logline("API HTTP $code: " . substr($out, 0, 400)); return [null, "API returned HTTP $code. Check your API key and billing."]; }
    $j = json_decode($out, true);
    $text = $j['content'][0]['text'] ?? null;
    if (!$text) return [null, "Empty response from the AI."];
    return [$text, null];
}

function ab_parse_article_json($text, $kw, $defaultCat) {
    $t = trim($text);
    $t = preg_replace('/^```(json)?/i', '', $t);
    $t = preg_replace('/```$/', '', trim($t));
    $obj = json_decode($t, true);
    if (!$obj && preg_match('/\{.*\}/s', $t, $m)) $obj = json_decode($m[0], true);
    if (!$obj || empty($obj['bodyHtml'])) return null;
    if (empty($obj['slug'])) $obj['slug'] = ab_slugify($obj['title'] ?? $kw);
    $obj['slug'] = ab_slugify($obj['slug']);
    $obj['title'] = $obj['title'] ?? $kw;
    $obj['category'] = $obj['category'] ?? $defaultCat;
    $obj['metaDescription'] = $obj['metaDescription'] ?? '';
    if (empty($obj['wordCount'])) $obj['wordCount'] = str_word_count(strip_tags($obj['bodyHtml']));
    return $obj;
}

function ab_add_to_sitemap($path, $url, $date) {
    if (!file_exists($path)) return;
    $xml = file_get_contents($path);
    if (strpos($xml, "<loc>$url</loc>") !== false) return;
    $entry = "  <url>\n    <loc>$url</loc>\n    <lastmod>$date</lastmod>\n    <changefreq>yearly</changefreq>\n    <priority>0.8</priority>\n  </url>\n";
    $xml = str_replace('</urlset>', $entry . '</urlset>', $xml);
    file_put_contents($path, $xml);
}

function ab_render_page($d, $cfg, $slug) {
    $title = htmlspecialchars($d['title'], ENT_QUOTES);
    $meta  = htmlspecialchars($d['metaDescription'], ENT_QUOTES);
    $url   = rtrim($cfg['domain'], '/') . '/' . $slug . '.html';
    $today = date('Y-m-d');
    $ld = json_encode([
        '@context' => 'https://schema.org', '@type' => 'Article',
        'headline' => $d['title'], 'description' => $d['metaDescription'],
        'datePublished' => $today, 'dateModified' => $today,
        'author' => ['@type'=>'Person','name'=>$cfg['author'],'jobTitle'=>'Commonwealth-registered Civil Marriage Celebrant','memberOf'=>['@type'=>'Organization','name'=>'Australian Marriage Celebrants','identifier'=>'M001457']],
        'publisher' => ['@type'=>'Organization','name'=>$cfg['brand_name']],
        'mainEntityOfPage' => ['@type'=>'WebPage','@id'=>$url],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $body = $d['bodyHtml'];
    return <<<HTML
<!DOCTYPE html>
<html lang="en-AU">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$title | {$cfg['brand_name']}</title>
<meta name="description" content="$meta">
<link rel="canonical" href="$url">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script type="application/ld+json">$ld</script>
<style>
  *{box-sizing:border-box;} body{margin:0;background:#F6F2EA;color:#2B2B27;font-family:'Mulish',sans-serif;}
  a{color:#8a6d3f;} a:hover{color:#B08D57;}
  header,footer{background:#FBF9F4;} .bar{max-width:1160px;margin:0 auto;padding:0 28px;}
  .nav{height:76px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(51,70,59,0.12);}
  .brand{font-family:'Cormorant Garamond',serif;font-size:25px;font-weight:600;color:#33463B;text-decoration:none;}
  .navlinks a{margin-left:26px;text-decoration:none;color:#5C6157;font-size:14px;font-weight:500;}
  article{max-width:720px;margin:0 auto;padding:56px 28px;}
  article h1{font-family:'Cormorant Garamond',serif;font-weight:600;font-size:44px;line-height:1.12;color:#26352C;margin:0 0 10px;}
  article h2{font-family:'Cormorant Garamond',serif;font-weight:600;font-size:30px;color:#26352C;margin:40px 0 0;}
  article h3{font-size:18px;font-weight:700;color:#26352C;margin:28px 0 0;}
  article p{font-size:17px;line-height:1.85;color:#3d423a;margin:16px 0 0;}
  article ul{margin:16px 0 0;padding-left:22px;} article li{font-size:16.5px;line-height:1.7;margin:8px 0;color:#3d423a;}
  .byline{font-size:14px;color:#6b7066;margin:0 0 8px;}
  footer{border-top:1px solid rgba(51,70,59,0.12);margin-top:40px;} .foot{padding:34px 28px;color:#6b7066;font-size:13px;text-align:center;}
</style>
</head>
<body>
<header><div class="bar nav">
  <a class="brand" href="index.html">Linda Mani</a>
  <nav class="navlinks">
    <a href="About.dc.html">About</a><a href="Services.dc.html">Services</a>
    <a href="Areas.dc.html">Service Areas</a><a href="Blog.dc.html">Blog</a><a href="Contact.dc.html">Contact</a>
  </nav>
</div></header>
<article>
  <div class="byline">By {$cfg['author']} · Registered Marriage Celebrant · {$today}</div>
  $body
</article>
<footer><div class="foot">© {$cfg['brand_name']} · Macquarie Fields, South West Sydney · <a href="Contact.dc.html">Enquire</a></div></footer>
</body>
</html>
HTML;
}

/**
 * Generate ONE article for a keyword and publish it (write file, sitemap, posts.json).
 * Returns ['ok'=>bool, 'error'=>?, 'post'=>?].
 */
function ab_generate_and_publish($cfg, $keyword) {
    $P = ab_paths();
    if (strpos($cfg['api_key'], 'sk-ant-') !== 0) {
        return ['ok'=>false, 'error'=>'No valid API key set in cron/config.php'];
    }
    $tpl = file_get_contents($P['prompt']);
    $prompt = strtr($tpl, [
        '{{keyword}}'     => $keyword,
        '{{brand_name}}'  => $cfg['brand_name'],
        '{{domain_name}}' => $cfg['domain'],
        '{{sitemap_url}}' => $cfg['sitemap_url'],
    ]);

    list($resp, $err) = ab_anthropic_complete($cfg, $prompt);
    if ($resp === null) return ['ok'=>false, 'error'=>$err];

    $data = ab_parse_article_json($resp, $keyword, $cfg['default_category']);
    if (!$data) return ['ok'=>false, 'error'=>'Could not parse the AI response into an article.'];

    $outDir = rtrim($P['root'] . '/' . trim($cfg['output_dir'], '/'), '/');
    $slug   = $data['slug'];
    $file   = $outDir . '/' . $slug . '.html';
    file_put_contents($file, ab_render_page($data, $cfg, $slug));

    $today = date('Y-m-d');
    $posts = ab_read_json($P['posts'], []);
    $post = [
        'title' => $data['title'], 'slug' => $slug, 'category' => $data['category'],
        'author' => $cfg['author'], 'date' => $today, 'meta' => $data['metaDescription'],
        'words' => $data['wordCount'], 'url' => rtrim($cfg['domain'],'/') . '/' . $slug . '.html',
    ];
    array_unshift($posts, $post);
    ab_save_json($P['posts'], $posts);
    ab_add_to_sitemap($P['sitemap'], $post['url'], $today);

    ab_logline("PUBLISHED: {$data['title']} -> $slug.html ({$data['wordCount']} words)");
    return ['ok'=>true, 'post'=>$post];
}

/** Publish the next pending keyword in the queue. Returns result + keyword. */
function ab_publish_next($cfg) {
    $P = ab_paths();
    $queue = ab_read_json($P['queue'], []);
    $idx = -1;
    foreach ($queue as $i => $q) { if (($q['status'] ?? '') === 'pending') { $idx = $i; break; } }
    if ($idx < 0) return ['ok'=>false, 'error'=>'No pending keywords in the queue.'];

    $kw = $queue[$idx]['keyword'];
    $res = ab_generate_and_publish($cfg, $kw);
    if ($res['ok']) { $queue[$idx]['status'] = 'published'; $queue[$idx]['date'] = date('Y-m-d'); }
    else            { $queue[$idx]['status'] = 'failed'; }
    ab_save_json($P['queue'], $queue);
    $res['keyword'] = $kw;
    return $res;
}

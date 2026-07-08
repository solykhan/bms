<?php
/**
 * admin/api.php — JSON API for the back-office dashboard.
 * All actions require a logged-in session (except "login").
 */
session_start();
header('Content-Type: application/json');

$cfg = require __DIR__ . '/../cron/config.php';
require __DIR__ . '/../cron/lib.php';

function out($data) { echo json_encode($data); exit; }
function need_auth() { if (empty($_SESSION['ab_admin'])) out(['ok'=>false, 'error'=>'Not logged in', 'auth'=>false]); }

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$P = ab_paths();

switch ($action) {

    case 'login':
        $pw = $_POST['password'] ?? '';
        if (!hash_equals((string)$cfg['admin_password'], (string)$pw)) {
            usleep(400000);
            out(['ok'=>false, 'error'=>'Incorrect password']);
        }
        $_SESSION['ab_admin'] = true;
        out(['ok'=>true]);

    case 'logout':
        $_SESSION = []; session_destroy();
        out(['ok'=>true]);

    case 'status':
        out(['ok'=>true, 'auth'=>!empty($_SESSION['ab_admin'])]);

    case 'state':
        need_auth();
        $keyOk = strpos($cfg['api_key'], 'sk-ant-') === 0;
        out([
            'ok'=>true,
            'queue'=>ab_read_json($P['queue'], []),
            'posts'=>ab_read_json($P['posts'], []),
            'prompt'=>@file_get_contents($P['prompt']) ?: '',
            'keyConfigured'=>$keyOk,
            'brand'=>$cfg['brand_name'], 'domain'=>$cfg['domain'], 'model'=>$cfg['model'],
        ]);

    case 'generate':          // generate + publish one keyword immediately
        need_auth();
        set_time_limit(200);
        $kw = trim($_POST['keyword'] ?? '');
        if ($kw === '') out(['ok'=>false, 'error'=>'Enter a keyword.']);
        $res = ab_generate_and_publish($cfg, $kw);
        out($res);

    case 'publish_next':      // generate + publish next pending queue item
        need_auth();
        set_time_limit(200);
        out(ab_publish_next($cfg));

    case 'queue_add':
        need_auth();
        $raw = trim($_POST['keywords'] ?? '');
        $lines = array_filter(array_map('trim', preg_split('/\r?\n/', $raw)));
        if (!$lines) out(['ok'=>false, 'error'=>'No keywords provided.']);
        $queue = ab_read_json($P['queue'], []);
        foreach ($lines as $l) $queue[] = ['keyword'=>$l, 'status'=>'pending'];
        ab_save_json($P['queue'], $queue);
        out(['ok'=>true, 'queue'=>$queue]);

    case 'queue_remove':
        need_auth();
        $kw = $_POST['keyword'] ?? '';
        $queue = ab_read_json($P['queue'], []);
        $queue = array_values(array_filter($queue, fn($q) => $q['keyword'] !== $kw));
        ab_save_json($P['queue'], $queue);
        out(['ok'=>true, 'queue'=>$queue]);

    case 'queue_retry':       // reset a failed item to pending
        need_auth();
        $kw = $_POST['keyword'] ?? '';
        $queue = ab_read_json($P['queue'], []);
        foreach ($queue as &$q) if ($q['keyword'] === $kw) $q['status'] = 'pending';
        unset($q);
        ab_save_json($P['queue'], $queue);
        out(['ok'=>true, 'queue'=>$queue]);

    case 'post_delete':       // remove from posts.json AND delete the .html file
        need_auth();
        $slug = ab_slugify($_POST['slug'] ?? '');
        $posts = ab_read_json($P['posts'], []);
        $posts = array_values(array_filter($posts, fn($p) => $p['slug'] !== $slug));
        ab_save_json($P['posts'], $posts);
        $file = rtrim($P['root'] . '/' . trim($cfg['output_dir'], '/'), '/') . '/' . $slug . '.html';
        if ($slug && is_file($file)) @unlink($file);
        out(['ok'=>true, 'posts'=>$posts]);

    case 'save_prompt':
        need_auth();
        $p = $_POST['prompt'] ?? '';
        if (strlen($p) < 50) out(['ok'=>false, 'error'=>'Prompt looks too short — not saved.']);
        file_put_contents($P['prompt'], $p);
        out(['ok'=>true]);

    default:
        out(['ok'=>false, 'error'=>'Unknown action']);
}

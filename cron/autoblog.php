<?php
/**
 * autoblog.php — generates and publishes the next queued blog post.
 * Run by cron (see README.md). Safe to run repeatedly; a lock prevents overlap.
 *
 * Usage:  php /path/to/site/cron/autoblog.php
 */

date_default_timezone_set('Australia/Sydney');
require __DIR__ . '/lib.php';
$cfg = require __DIR__ . '/config.php';

$LOCK = __DIR__ . '/autoblog.lock';

// ---- prevent overlapping runs ----
$fp = fopen($LOCK, 'c');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) { ab_logline('Another run is in progress — exiting.'); exit; }

// ---- basic check ----
if (strpos($cfg['api_key'], 'sk-ant-') !== 0) { ab_logline('ERROR: set your Anthropic API key in config.php'); flock($fp, LOCK_UN); fclose($fp); exit(1); }

$per = max(1, (int)$cfg['posts_per_run']);
$done = 0;
for ($n = 0; $n < $per; $n++) {
    $res = ab_publish_next($cfg);
    if (!$res['ok']) {
        if (($res['error'] ?? '') === 'No pending keywords in the queue.') { ab_logline('Nothing to publish (queue empty or all done).'); }
        else { ab_logline('FAILED: ' . ($res['keyword'] ?? '?') . ' — ' . $res['error']); }
        break;
    }
    $done++;
}

if ($done > 0) ab_logline("Run complete — published $done post(s).");
flock($fp, LOCK_UN); fclose($fp);

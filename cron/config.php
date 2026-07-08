<?php
/**
 * config.php — settings for the auto-blog cron.
 * Edit the values below, then never share this file publicly.
 */
return [
    // ---- REQUIRED ----
    // Get a key at https://console.anthropic.com  (starts with "sk-ant-")
    'api_key'        => getenv('ANTHROPIC_API_KEY') ?: 'sk-ant-PASTE-YOUR-KEY-HERE',

    // ---- ADMIN LOGIN (for the back-office at /admin) ----
    // Change this password before uploading. Used to log in to the dashboard.
    'admin_password' => 'ChangeMe-2026!',

    // ---- SITE ----
    'brand_name'     => 'Linda Mani Wedding Celebrant',
    'domain'         => 'https://brightmorningstar.net',   // no trailing slash
    'sitemap_url'    => 'https://brightmorningstar.net/sitemap.xml',
    'author'         => 'Linda Mani',

    // ---- BEHAVIOUR ----
    'model'          => 'claude-sonnet-4-5',   // quality model for articles
    'max_tokens'     => 8000,
    'posts_per_run'  => 1,                      // how many queued keywords to publish each run
    'default_category' => 'Guide',

    // Where finished article .html files are written (relative to the site root,
    // which is the parent folder of this /cron directory). "" = site root.
    'output_dir'     => '',
];

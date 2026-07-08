# Back Office (Admin Dashboard) — Setup

A live, password-protected admin panel that runs on your Hostinger hosting and lets
you generate and publish blog posts **on demand** from the browser — using your own
Anthropic API key. It reuses the same config, prompt and queue as the daily cron.

## URL
After uploading, open:
```
https://brightmorningstar.net/admin/
```

## One-time setup
1. Make sure your Anthropic key is in **`cron/config.php`** (`api_key` → `sk-ant-…`).
   The admin uses the same key as the cron.
2. In **`cron/config.php`**, change **`admin_password`** from the default
   (`ChangeMe-2026!`) to your own password. This is what you log in with.
3. Upload the whole site (including the `admin/` and `cron/` folders) to `public_html`.
4. Visit `https://brightmorningstar.net/admin/` and sign in.

## What you can do in the Back Office
- **Generate** tab — type a keyword/title, click *Generate & Publish*. In ~30–90 seconds
  it writes a 1500+ word article, saves it as a live `.html` page, adds it to your
  sitemap, and lists it on your Blog. A link to the live page appears when it's done.
- **Queue** tab — add many keywords, see pending/published/failed counts, publish the
  next queued item on demand, retry failed items, remove items.
- **Posts** tab — view every published post, open its live page, or delete it
  (removes the file and the sitemap/blog listing).
- **Prompt** tab — edit the article prompt (same file the daily cron uses).

## How it relates to the daily cron
- **Back Office** = you press a button, a post is created now.
- **Cron (autoblog.php)** = one post is created automatically each day from the queue.
- They share `cron/config.php`, `cron/prompt.txt`, `cron/queue.json` and `cron/posts.json`,
  so anything you queue in the Back Office is also available to the daily cron, and vice versa.

## Security
- The admin is protected by the password in `cron/config.php`. **Use a strong one.**
- Keep the `cron/.htaccess` (`Deny from all`) file in place so your key and logs
  can't be read over the web. It does NOT block the admin — server-side includes
  still work.
- The admin pages send `noindex,nofollow`, so search engines won't list them.
- Requires PHP with the **cURL** extension (Hostinger has this on by default).

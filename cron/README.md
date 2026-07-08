# Daily Auto-Blog — Cron Setup

This publishes **one new blog post per day** automatically after your site is hosted.

## What it does each run
1. Takes the next `pending` keyword from `cron/queue.json`.
2. Generates a 1500+ word E-E-A-T article with the AI (using `cron/prompt.txt`).
3. Writes a branded `your-slug.html` page into your site.
4. Adds the new URL to `sitemap.xml`.
5. Records it in `cron/posts.json` and marks the keyword `published`.
6. Logs everything to `cron/autoblog.log`.

## IMPORTANT — you need your own AI key
The on-site "Blog Generator" panel uses a built-in AI helper that only works inside
the design tool. A cron on YOUR server can't use that — it needs your own key.

1. Create a key at **https://console.anthropic.com** (it starts with `sk-ant-`).
2. Open `cron/config.php` and paste it into `api_key`
   (or set an `ANTHROPIC_API_KEY` environment variable — safer).
3. Check `brand_name`, `domain`, `sitemap_url` in `cron/config.php` are correct.
4. Add/replace keywords in `cron/queue.json` (one object per keyword).

Cost is roughly a few cents to ~US$0.10 per article on Claude Sonnet.

---

## Option A — cPanel / shared hosting (most common)
Most shared hosts (cPanel, Plesk) support **PHP** and a **Cron Jobs** tool.

1. Upload the whole site, including the `cron/` folder, via File Manager or FTP.
2. In cPanel open **Cron Jobs**.
3. Add a new cron job. For once daily at 8am:
   - Common settings: `Once a day (0 0 * * *)` — or set minute `0`, hour `8`.
4. In the **Command** box enter (adjust the path to match your account):

   ```
   /usr/local/bin/php /home/USERNAME/public_html/cron/autoblog.php
   ```

   (Ask your host for the correct PHP binary path if unsure — often just `php`.)
5. Save. That's it. Watch `cron/autoblog.log` fill up after the first run.

**Protect your key:** make sure `cron/config.php`, `queue.json`, `posts.json` and
`autoblog.log` are not web-accessible. Add a `.htaccess` in `/cron/` containing:
```
Deny from all
```

---

## Option B — VPS / Linux server (crontab)
```
crontab -e
```
Add this line (daily at 08:00 server time):
```
0 8 * * * /usr/bin/php /var/www/brightmorningstar/cron/autoblog.php >/dev/null 2>&1
```
Set the key once in the shell profile or systemd unit:
```
export ANTHROPIC_API_KEY="sk-ant-..."
```

---

## Option C — Git-hosted static site (Netlify / Vercel / GitHub Pages / Cloudflare Pages)
These don't run PHP cron, so use the included **GitHub Action**:
`.github/workflows/autoblog.yml`.

1. Push the whole project to a GitHub repo.
2. In the repo: **Settings → Secrets and variables → Actions → New secret**
   - Name: `ANTHROPIC_API_KEY`   Value: your `sk-ant-...` key.
3. The workflow runs daily, generates the post, and commits it back to the repo.
   Your host redeploys automatically on the new commit.
4. Trigger a test run any time from the repo's **Actions** tab → *Daily Auto-Blog* → *Run workflow*.

---

## Test it before trusting the schedule
Run once by hand and check the output:
```
php cron/autoblog.php
cat cron/autoblog.log
```
You should see a new `.html` file at your site root and a new entry in `sitemap.xml`.

## Posting cadence
Change how many per run in `config.php` → `posts_per_run`, and how often in your cron
schedule. One per day from a well-stocked queue is the sweet spot for steady, natural growth.

## Showing new posts on the Blog page
New articles are live at their own URLs and in your sitemap immediately (so Google/AI find
them). Your existing `Blog.dc.html` index lists the original hand-written posts; if you'd
like it to auto-list the cron's posts from `posts.json` too, ask and I'll wire that up.

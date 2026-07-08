# Linda Mani Wedding Celebrant — Website Handoff

A 6-page website plus 5 long-form articles for Linda Mani, civil marriage
celebrant, Macquarie Fields NSW. Built as self-contained HTML that renders
in the browser (no build step, no server framework required).

## Pages
- Home.dc.html ................ Home
- About.dc.html ............... About Linda (E-E-A-T)
- Services.dc.html ........... Ceremonies, guide prices, FAQ (+ FAQ schema)
- Areas.dc.html .............. Service areas within 20km
- Blog.dc.html ............... Journal index
- Contact.dc.html ............ Enquiry form + details
- article-*.dc.html .......... 5 x 1500+ word articles (+ Article schema)

Shared building blocks (loaded automatically, don't delete):
- SiteHeader.dc.html, SiteFooter.dc.html
- support.js .................. rendering runtime (required)
- image-slot.js .............. drag-and-drop photo placeholders
- sitemap.xml, robots.txt .... SEO crawl files

## Before publishing — checklist
1. PHOTOS: every image is an empty drop-slot. Open each page and drag in
   real photos of Linda and ceremonies (they persist automatically).
2. TESTIMONIALS: the home-page reviews are PLACEHOLDERS. Replace with your
   real Google reviews before going live.
3. CONTACT FORM: currently front-end only (shows a thank-you). Connect it to
   an email service (e.g. Formspree, Netlify Forms) when hosting.
4. DOMAIN: schema and sitemap use https://brightmorningstar.net/ — update if
   your live domain or URL paths differ.
5. PHONE/EMAIL/AMC no. are set: 0421 768 296 · info@brightmorningstar.net ·
   AMC Member M001457.

## Clean URLs
Files use the .dc.html extension. The sitemap lists pretty URLs
(e.g. /about, /services). Configure your host to serve each .dc.html file at
its clean path (a simple rewrite/redirect map), or rename files and update the
internal links to match — either works.

## Hosting
Upload all files to any static host (Netlify, Cloudflare Pages, Vercel,
or plain shared hosting). Keep all files in the same folder so the shared
header/footer and runtime load correctly. Set Home.dc.html as the home page.

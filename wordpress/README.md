# edelta-danube / wordpress

## edelta-danube — WordPress plugin

Displays Danube **water level** and **water temperature** for a selected port as a
**chart**, **table**, or **both**, using the public api.edelta.ro API.

- Shortcode: `[edelta_danube port="2" days="30" display="both"]`
- Global defaults via **Settings → edelta Danube Levels**; per-shortcode
  attributes override them (`port`, `days`, `display`, `border`, `api_base`,
  `backlink`, `cache`).
- **No API key required** — uses the public, rate-limited endpoints (max 30 days).
- Server-side fetch + transient caching (default 10 min) protects the API.
- Chart.js v4 bundled locally (MIT) — no CDN dependency.
- Optional backlink to `https://edelta.ro` at the bottom (toggle).
- Languages: English, Romanian, German, Russian.

### Install

1. Upload `edelta-danube` to `wp-content/plugins/` (or install `edelta-danube-1.0.0.zip` via Plugins → Add New).
2. Activate, then add the shortcode to any page/post.

### Layout

```
edelta-danube/
├── edelta-danube.php            # main plugin
├── readme.txt                   # WP readme
├── uninstall.php
├── includes/
│   ├── class-edelta-api.php         # API client + transient cache
│   ├── class-edelta-shortcode.php   # [edelta_danube] renderer
│   └── class-edelta-settings.php    # settings page
├── assets/
│   ├── js/chart.umd.min.js          # Chart.js v4 (MIT)
│   ├── js/edelta-danube.js
│   └── css/edelta-danube.css
└── languages/                      # en, ro_RO, de_DE, ru_RU (.pot/.po/.mo)
```

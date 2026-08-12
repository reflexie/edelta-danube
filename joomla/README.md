# edelta-danube / joomla

## mod_edelta_dunare — Joomla module

Displays Danube **water level** and **water temperature** for a selected port as a
**chart**, **table**, or **both** (configurable in the module settings).

- Joomla 4 / 5 / 6 compatible (modern namespaced structure).
- Fetches data **server-side** from the public api.edelta.ro endpoints — **no API
  key required** and no key is ever shipped in the extension.
- The public API serves the **latest ≤30 days**; when more is requested the
  module shows a small "more data on edelta.ro" link (configurable URL) to drive
  visitors to the full history on edelta.ro.
- API responses are cached (default 10 minutes) to protect the public API.
- Chart.js is bundled locally (v2.9.1, MIT) — no external CDN needed.
- Languages: English, Romanian, German, Russian.

### Install

1. Download `mod_edelta_dunare_1.0.0.zip` from the [Releases](https://github.com/reflexie/edelta-danube/releases).
2. Joomla admin → **System → Install → Extensions → Upload Package File** → select the zip → Install.
3. Publish the module (Content → Site Modules → New → type `edelta_dunare`).
4. Configure: port, days, **display mode (chart / table / both)**, chart line color.
5. Optional: override the API base URL (default `https://api.edelta.ro`).

### Prerequisite

The public, rate-limited endpoints must be enabled on the API host — see
[`api/`](../api/public-endpoints.md). Until then the module shows a friendly
"Unable to load data." message instead of failing.

### Embed in content

Use the `{loadmodule mod_edelta_dunare,<Module Title>}` syntax in any article.

### Layout

```
mod_edelta_dunare/
├── mod_edelta_dunare.xml      # manifest
├── services/provider.php      # DI registration
├── src/
│   ├── Dispatcher/Dispatcher.php
│   └── Helper/EdeltaDunareHelper.php   # API fetch + cache
├── tmpl/default.php           # layout
├── assets/                    # Chart.js + CSS (local)
└── language/                  # en-GB, ro-RO, de-DE, ru-RU
```

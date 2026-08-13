## [Unreleased]

### Added

- Repository scaffold (GPL-2.0-or-later, README, .gitignore)
- Public API integration documentation + reference code + rate limiting (`api/`)
  - `api/public-endpoints.md` — endpoint contract & hard limits
  - `api/reference/public_api.php` — single-file PHP reference implementation
  - `api/nginx-rate-limit.conf.example` — nginx throttle config
- `joomla/mod_edelta_dunare` Joomla module
  - Modern J4/J5/J6 namespaced structure (services/provider.php)
  - Server-side fetch from public api.edelta.ro endpoints, cached (default 10 min)
  - Admin params: port, days, display mode (chart / table / both), line color
  - Bundled Chart.js v2.9.1 (MIT) + en-GB/ro-RO/de-DE/ru-RU

## [1.0.0] — 2026-08-13 (WordPress plugin)

### Added

- WordPress plugin `edelta-danube` (`wordpress/edelta-danube/`)
  - `[edelta_danube]` shortcode with per-instance attributes + global defaults
  - chart / table / both display modes; Chart.js v4 bundled (MIT)
  - server-side fetch from the public api.edelta.ro `recent` endpoint (max 30 days)
    with transient caching; no API key required
  - optional backlink to edelta.ro (admin toggle)
  - i18n: en, ro_RO, de_DE, ru_RU
  - verified on WordPress 7.0.2 (local): all modes, backlink on/off, ro_RO strings

## [1.1.3] — 2026-08-13

### Changed

- Admin **module edit form**: the module name and description now translate
  correctly (the two keys were only in the `.sys.ini`, which the edit form does
  not load — they are now also present in the regular `.ini` files).
- The "More data URL" text field was replaced by an informative admin toggle:
  **"Enable backlink to edelta.ro?"** (Yes/No). The bottom link target is fixed
  to `https://edelta.ro` and is no longer editable.

## [1.1.2] — 2026-08-13

### Changed

- A small, permanent **"more data on edelta.ro"** link is now rendered at the
  bottom of the module (always, regardless of the selected days), using the
  configurable `more_url` parameter.

## [1.1.1] — 2026-08-13

### Changed

- Days dropdown limited to **7 / 14 / 30** (the public API max is 30 days);
  the 60/90/365 options were removed since the public endpoint never serves
  more than 30 days.

## [1.1.0] — 2026-08-13

### Changed

- Public API now serves at most the **latest 30 days** (`/api/measurements/recent`);
  `/api/measurements/range` is **key-protected**. The module clamps to 30 days and,
  when more is requested, shows a configurable **"more data on edelta.ro"** link
  (`more_url` param)  to the full history.

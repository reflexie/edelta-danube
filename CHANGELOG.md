# Changelog

All notable changes to this project will be documented in this file.

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

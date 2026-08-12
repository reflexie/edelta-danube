# edelta-danube / api

Documentation, reference implementation and rate-limiting config for exposing
**public, rate-limited** endpoints of the Danube "Cote" API at
[api.edelta.ro](https://api.edelta.ro).

## Why public endpoints?

The `edelta-danube` extensions are free and public. They must work **without an
API key**, so the private `X-API-Key` is never shipped inside an extension and
never exposed to site visitors.

This folder describes how to open a small, read-only, rate-limited subset of the
API to the public while keeping everything else key-protected.

## Contents

| File | Purpose |
|------|---------|
| [`public-endpoints.md`](public-endpoints.md) | Endpoint contract (request/response, limits) |
| [`reference/public_api.php`](reference/public_api.php) | Single-file PHP reference implementation |
| [`reference/README.md`](reference/README.md) | How to deploy the reference implementation |
| [`nginx-rate-limit.conf.example`](nginx-rate-limit.conf.example) | nginx `limit_req` snippet (defense-in-depth) |

> **Production status:** the live `api.edelta.ro` integrates these routes directly
> into its own front controller (repo: `cote-api`, `public/index.php`) with
> per-IP rate limiting (`api_ip_ratelimit` table, `sql/migrate-public.sql`) —
> the same contract and limits described here. The single-file reference exists
> as a portable implementation for other hosts.

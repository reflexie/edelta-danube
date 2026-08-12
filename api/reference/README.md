# Public API reference implementation

Single-file PHP (PDO/MySQL) implementation of the public, rate-limited endpoints.
Use it as a reference to adapt into your existing `api.edelta.ro` service, or as a
standalone endpoint.

## What it does

- Reads DB credentials from environment variables (never hard-coded).
- Serves:
  - `GET /api/ports`
  - `GET /api/measurements/latest?port_id=X`
  - `GET /api/measurements/range?port_id=X&from&to[&limit]`
- Enforces:
  - valid `port_id` (1..23)
  - window max 366 days, `limit` max 365, floor date `2011-01-01`
  - per-IP rate limit via a small `api_requests` table (falls back to off if table missing)
- Output matches the existing api.edelta.ro envelopes exactly, so clients that work
  against the key-protected API work unchanged against the public one.

## DB tables used

- `cote_loc(id_locdunare, nume_locdunare, ...)` → ports
- `cote_data(id, date, id_locdunare, cota, temperatura, ...)` → measurements
- `api_requests(ip, ts, route)` → optional rate-limit counter (auto-created)

## Deploy

1. Place `public_api.php` behind a rewrite for `/api/*` (or point your front
   controller at it). Example nginx:

   ```nginx
   location /api/ {
       try_files $uri /public_api.php?$query_string;
   }
   ```

2. Export env vars (or copy to a `.env` loaded by your service):

   ```bash
   COTE_DB_HOST=localhost
   COTE_DB_NAME=cote
   COTE_DB_USER=cote
   COTE_DB_PASSWORD=*****
   ```

3. Apply `../nginx-rate-limit.conf.example` for an extra per-IP throttle.

4. Test:

   ```bash
   curl -s 'https://api.edelta.ro/api/ports'
   curl -s 'https://api.edelta.ro/api/measurements/latest?port_id=1'
   curl -s 'https://api.edelta.ro/api/measurements/range?port_id=2&from=2026-08-01&to=2026-08-10'
   ```

> Prefer integrating these routes into your **existing** API app (reuse its
> handlers/DB layer) and simply removing the auth requirement for them. This file
> exists so you have a working, correct reference for the contract and limits.


# What it does


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



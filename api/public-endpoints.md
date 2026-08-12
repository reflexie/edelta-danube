# Public endpoints — contract

These routes are **unauthenticated**, **read-only** and **rate-limited**.
Everything else on `api.edelta.ro` — including `/api/measurements/range` and
`/api/measurements` — keeps requiring `X-API-Key`.

The public API intentionally exposes only recent data (latest reading, plus up to
**30 days** back). Full date-window history is available with an API key.

Base URL: `https://api.edelta.ro`

All responses are `application/json; charset=utf-8`.

## Success envelope

```json
{ "success": true, "data": { ... } }
```

## Error envelope

```json
{ "success": false, "error": "Unauthorized", "code": 401, "details": "..." }
```

HTTP status codes: `200` OK · `400` bad params · `403` port outside scope ·
`429` rate limited · `500` server error.

---

## GET `/api/ports`

Returns the list of ports (locations).

**Response `data`:**

```json
{
  "ports": [
    { "id": 1, "name": "SULINA " },
    { "id": 2, "name": "TULCEA " }
  ]
}
```

> Note: names intentionally keep their original trailing space for backward
> compatibility with existing clients; consumers should `trim()`.

---

## GET `/api/measurements/latest?port_id=X`

Returns the most recent measurement for a port.

**Query params:**

| Param      | Type | Required | Notes               |
|------------|------|----------|---------------------|
| `port_id`  | int  | yes      | 1..23 (valid ports) |

**Response `data`:**

```json
{
  "measurement": { "date": "2026-08-10", "cota": 54, "temperatura": "28" },
  "meta": { "port_id": 1, "port_name": "SULINA " }
}
```

---

## GET `/api/measurements/recent?port_id=X&days=N`

Returns the most recent measurements, counting **backward** from the latest
reading, up to a maximum of **30 days** (`days` is clamped server-side).

**Query params:**

| Param     | Type | Required | Notes                                |
|-----------|------|----------|--------------------------------------|
| `port_id` | int  | yes      | 1..23 (valid ports)                  |
| `days`    | int  | no       | 1..30 (default 30, clamped to 30)    |

**Response `data`:**

```json
{
  "measurements": [
    { "date": "2026-07-12", "cota": 41, "temperatura": "27" },
    { "date": "2026-07-13", "cota": 41, "temperatura": "27" }
  ],
  "meta": { "port_id": 2, "port_name": "TULCEA ", "days": 30, "count": 31 }
}
```

> For more history than 30 days, use an API key with `/api/measurements/range`
> (or visit the source site — e.g. edelta.ro — which links to the full history).

---

## Key-protected (not public)

- `GET /api/measurements/range?port_id=X&from=Y&to=Z` — full date window
- `GET /api/measurements?port_id=X&...` — paginated measurements
- `GET /api/measurements/extremes?port_id=X&from=Y&to=Z` — min/max aggregates
- `GET /api/ports/{id}` — single port detail

---

## Hard limits (server-enforced)

| Rule                        | Value                         |
|-----------------------------|-------------------------------|
| Public `recent` window      | max **30 days** (`days` clamped) |
| Rate limit (public routes)  | **30 req/min per IP**         |
| Public port scope           | ports 1..`PUBLIC_PORT_LIMIT` (23) |
| Read-only                   | no state changes              |
| Earliest data               | `2011-01-01`                  |

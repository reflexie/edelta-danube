# Public endpoints — contract

These routes are **unauthenticated**, **read-only** and **rate-limited**. Everything
else on `api.edelta.ro` keeps requiring `X-API-Key`.

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

HTTP status codes: `200` OK · `400` bad params · `429` rate limited · `500` server error.

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

| Param      | Type | Required | Notes                       |
|------------|------|----------|-----------------------------|
| `port_id`  | int  | yes      | 1..23 (valid ports)         |

**Response `data`:**

```json
{
  "measurement": { "date": "2026-08-10", "cota": 54, "temperatura": "28" },
  "meta": { "port_id": 1, "port_name": "SULINA " }
}
```

---

## GET `/api/measurements/range?port_id=X&from=YYYY-MM-DD&to=YYYY-MM-DD`

Returns all daily measurements in a date window (ascending).

**Query params:**

| Param     | Type   | Required | Notes                                          |
|-----------|--------|----------|------------------------------------------------|
| `port_id` | int    | yes      | 1..23                                          |
| `from`    | date   | yes      | `YYYY-MM-DD`, >= `2011-01-01`                  |
| `to`      | date   | yes      | `YYYY-MM-DD`, <= today                         |
| `limit`   | int    | no       | **Capped server-side at 365** (rows)           |

**Response `data`:**

```json
{
  "measurements": [
    { "date": "2026-08-09", "cota": 18, "temperatura": "28" },
    { "date": "2026-08-10", "cota": 19, "temperatura": "28" }
  ],
  "meta": { "port_id": 2, "port_name": "TULCEA ", "from": "2026-08-01", "to": "2026-08-10", "count": 2 }
}
```

---

## Hard limits (server-enforced)

| Rule                            | Value               |
|----------------------------------|---------------------|
| Date window                       | max 366 days (`to - from`) |
| `limit` param                     | max 365 rows         |
| Rate limit (all public routes)    | **30 req/min per IP** (e.g. nginx `limit_req`); range can be stricter |
| Read-only                        | no state changes     |
| Earliest data                    | `2011-01-01`         |

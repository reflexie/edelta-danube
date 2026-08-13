# edelta-danube — WordPress plugin user manual

Displays the **Danube water level** and **water temperature** for a selected port
as a **chart**, **table**, or **both**, using the public api.edelta.ro API.
No API key is required.

**Plugin version:** 1.0.0 · **License:** GPL-2.0-or-later

---

## 1. Requirements

- WordPress **5.0 or newer**
- PHP **7.4 or newer**
- An internet connection from the server to `https://api.edelta.ro`

---

## 2. Installation

### Option A — from the WordPress admin (recommended)

1. Download `edelta-danube-1.0.0.zip`.
2. In the admin go to **Plugins → Add New → Upload Plugin**.
3. Choose the `.zip` file and click **Install Now**.
4. Click **Activate**.

### Option B — manual (FTP)

1. Unzip `edelta-danube-1.0.0.zip`.
2. Upload the `edelta-danube` folder into `/wp-content/plugins/`.
3. In the admin go to **Plugins** and click **Activate** under "edelta Danube Levels".

---

## 3. Quick start

Add the shortcode to any page or post:

```
[edelta_danube]
```

This shows the widget using the default settings (port 2, last 30 days, chart + table).
Publish the page and you are done.

---

## 4. Configuration

There are two levels of configuration:

### 4.1 Global defaults — Settings → edelta Danube Levels

| Setting | Default | Description |
|---|---|---|
| Select the port | 2 | The Danube port id (1..23) to show data for. |
| Days | 30 | Number of recent days (the public API serves at most **30**). |
| Display | Chart and table | `Chart`, `Table`, or `Chart and table`. |
| Chart line color | #436741 | Color of the water-level line. |
| API base URL | https://api.edelta.ro | Base URL of the public API. |
| Enable backlink to edelta.ro? | on | Show a small link to edelta.ro at the bottom of the widget. |
| Cache time (seconds) | 600 | How long API responses are cached (min 60). |

### 4.2 Per-widget overrides — shortcode attributes

Each shortcode can override the defaults. All attributes are optional.

```
[edelta_danube port="2" days="30" display="both" border="#436741"
              api_base="https://api.edelta.ro" backlink="1" cache="600"]
```

| Attribute | Values | Default |
|---|---|---|
| `port` | 1..23 | settings default (2) |
| `days` | 7, 14, 30 | settings default (30) |
| `display` | `chart`, `table`, `both` | settings default (both) |
| `border` | any hex color, e.g. `#436741` | settings default |
| `api_base` | a URL | `https://api.edelta.ro` |
| `backlink` | `1` or `0` | settings default (1) - Recommended|
| `cache` | seconds (min 60) | settings default (600) |

**Examples**

A small table-only widget with the backlink hidden:

```
[edelta_danube port="1" days="7" display="table" backlink="0"]
```

A chart-only widget for Corabia with a custom line color:

```
[edelta_danube port="13" days="30" display="chart" border="#1a73e8"]
```

---

## 5. What the widget shows

- **Info line** — the port name and the shown period (e.g. *TULCEA — Last 30 days*).
- **Chart** — water level (left axis, cm) and water temperature (right axis, °C) over time.
- **Table** — date / level / temperature for each day.
- **Backlink** (optional) — a small "More data on edelta.ro" link at the bottom,
  pointing to `https://edelta.ro`.

---

## 6. Data, caching and privacy

- The plugin fetches data **server-side** from the public, rate-limited
  api.edelta.ro endpoints. No API key is shipped or exposed.
- The public API serves at most the **latest 30 days**; the full history is
  available on https://edelta.ro.
- Responses are cached with WordPress **transients** (default 10 minutes) to
  protect the API and speed up page loads. Lowering `cache` increases API calls.
- The widget is rendered by your server; visitors' browsers receive only the
  final HTML, the Chart.js library, and the chart data for the shown period.
  No visitor data is sent to api.edelta.ro beyond the standard request.

---

## 7. Languages

The plugin is translated into:

- English (default)
- Romanian (ro_RO)
- German (de_DE)
- Russian (ru_RU)

Translations load automatically when WordPress is set to that site language.

---

## 8. Troubleshooting

| Problem | Likely cause / fix |
|---|---|
| "Unable to load data." | The API is unreachable from your server (firewall/SSL), or the API base URL is wrong. Check the shortcode `api_base` / the settings value. |
| Only 30 days are shown | Intended — the public API serves at most 30 days. Full history is on https://edelta.ro. |
| The chart is empty but the table has data | Chart.js did not load (check the browser console) or the page uses a very old browser. |
| Data looks old | The transient cache is still valid. Reduce `cache` (or wait) and re-check. |
| The widget is not displayed at all | The shortcode syntax is wrong (e.g. missing quotes) or the plugin is deactivated. |

---

## 9. Uninstall

1. Deactivate the plugin under **Plugins**.
2. Click **Delete**.
3. The uninstall routine removes the plugin option and all cached API responses.

---

## 10. Changelog

**1.0.0** — initial release: shortcode, chart/table/both, settings, caching,
backlink toggle, i18n.

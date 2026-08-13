=== edelta Danube Levels ===
Contributors: reflexie
Tags: danube, water level, temperature, chart, river
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Displays Danube water level and water temperature (chart and/or table) using the public api.edelta.ro API. Free, no API key required.

== Description ==

Shows the latest Danube water level and water temperature for a selected port
as a **chart**, **table**, or **both**. Data comes from the public, rate-limited
api.edelta.ro endpoints — no API key needed and none is shipped with the plugin.

* Joomla / WordPress compatible data source (same free API used by the Joomla module).
* Public data is limited to the latest **30 days**.
* Chart.js is bundled locally (v4, MIT) — no external CDN required.
* Optional backlink to edelta.ro at the bottom of the widget.
* Server-side fetch + caching (default 10 minutes) protects the public API.
* Languages: English, Romanian, German, Russian.

== Installation ==

1. Upload the `edelta-danube` folder to `/wp-content/plugins/` (or install the zip via Plugins → Add New).
2. Activate the plugin through the Plugins screen.
3. Add the shortcode to any page/post:

   `[edelta_danube port="2" days="30" display="both"]`

4. Optionally set global defaults under **Settings → edelta Danube Levels**.

Shortcode attributes (each optional, fall back to the settings defaults):

`port` (1..23) · `days` (7/14/30) · `display` (chart|table|both) · `border` (hex color) · `api_base` (URL) · `backlink` (1|0) · `cache` (seconds)

== Frequently Asked Questions ==

= Do I need an API key? =

No. The plugin uses the public, rate-limited api.edelta.ro endpoints.

= Why is data limited to 30 days? =

The public API intentionally serves only the latest 30 days. Full history is
available on https://edelta.ro.

== Changelog ==

= 1.0.1 =
* Table now shows only the most recent 7 records, newest first.

= 1.0.0 =
* Initial release.

=== WeightLossAffordable Core ===
Contributors: weightlossaffordable
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 7.4
Version: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Treatments, structured sections, Elementor widgets, forms, SEO and demo import
for the WeightLossAffordable theme.

== Description ==

This plugin holds everything that should not be lost if the design changes:

* **Treatments** (`nrx_treatment`) — structured fields for medication details,
  pricing, variations, benefits, FAQs, safety text and SEO, with repeatable
  groups managed in a native meta box.
* **Page sections** — 17 section types stored as JSON and editable in the classic
  editor; each one is also registered as a native Elementor widget.
* **Forms** — enquiry and consultation forms processed on the server with a
  nonce, honeypot, timing check, per-IP rate limit, consent validation and
  private storage of submissions (IP stored as a hash).
* **SEO** — titles, descriptions, canonical, Open Graph/Twitter, JSON-LD and
  noindex rules; steps back when a dedicated SEO plugin is active.
* **Settings** — business details, notification recipients, retention window and
  clinical review metadata.
* **Demo importer** — one click, idempotent, never deletes content it did not
  create, adopts the untouched WordPress privacy/sample stubs where safe.
* **WP-CLI** — `wp wla import-demo`, `wp wla status`.

== Installation ==

1. Upload the ZIP and activate the plugin.
2. Install and activate the WeightLossAffordable theme.
3. WeightLossAffordable -> Dashboard & import -> Import demo content.

== Frequently Asked Questions ==

= Is Elementor required? =
No. It is recommended for visual editing; without it the theme renders the same
sections from stored data.

= Does the importer delete my content? =
No. It only updates records it tagged itself. Removal is a separate, confirmed
action limited to those records.

== Changelog ==

= 3.0.0 =
* First release of the companion plugin: treatments, sections, Elementor widgets,
  forms, SEO, settings, demo importer, compliance checklist and WP-CLI commands.

# 01 — Installation & setup

## 1. Requirements

| Item | Minimum | Tested with |
| --- | --- | --- |
| WordPress | 6.4 | 6.7.2 |
| PHP | 7.4 | 8.3 |
| MySQL / MariaDB | 5.7 / 10.3 | MySQL 8 (and SQLite for the test bench only) |
| Elementor (free) | 3.18 | **not installed in the test environment** — see the QA report |
| Elementor Pro | optional | only for Theme Builder headers/footers |

No Composer, npm or build step is required. The packages are plain PHP, CSS and
JavaScript.

## 2. Install

1. **Back up first.** Take a database dump and a copy of `wp-content/themes` and
   `wp-content/plugins` (see [05 — Backup & rollback](05-BACKUP-AND-ROLLBACK.md)).
2. **Plugins → Add New → Upload Plugin** → `weightloss-affordable-core.zip` →
   Install → **Activate**.
3. **Appearance → Themes → Add New → Upload Theme** →
   `weightloss-affordable.zip` → Install → **Activate**.
   If the theme is already active on the site, this replaces it in place; the
   plugin keeps working through the update.
4. **Plugins → Add New** → install and activate **Elementor** (free).
5. Go to **WeightLossAffordable → Dashboard & import** and press
   **Import demo content**.

The importer is idempotent: running it again updates the records it owns and
never creates duplicates. It never deletes posts it did not create — the only
destructive action is the separate **Remove demo content** button, which is
limited to records tagged `_wla_demo_key`, and it asks for confirmation first.

## 3. What the import creates

| Content | Count | Notes |
| --- | --- | --- |
| Pages | 19 | Home, treatments, comparison, how it works, pricing, eligibility, about, FAQ, resources, articles, reviews, contact and 7 policy/safety pages |
| Treatments | 2 | Semaglutide, Tirzepatide — each with variations, FAQs, pricing and safety fields |
| Articles | 3 | Written as general information, not medical advice |
| Menus | 3 | Primary, footer-legal, footer-resources |
| Front page / posts page | 1 each | Home and Articles |
| Privacy policy page | 1 | Adopts the untouched WordPress draft stub if it exists, otherwise creates one |

Every imported page also gets an Elementor document (`_elementor_data`) made of
this site's own section widgets, so the page opens in the Elementor editor with
one widget per section and nothing to rebuild by hand.

## 4. First-run checklist (10 minutes)

- [ ] **Settings → General** — site title, tagline, timezone, and language set to
      **English (United States)**.
- [ ] **WeightLossAffordable → Settings** — business name, public email, phone,
      address, business hours, service area, notification recipients. Anything
      you leave blank stays hidden on the front end.
- [ ] **WeightLossAffordable → Settings → Clinical review** — reviewer name and
      credentials and last review date. They render in the treatment and medical
      pages, and are deliberately empty until you supply them.
- [ ] **Treatments** — confirm prices, billing period, eligibility wording and
      disclaimers. Prices in the demo are placeholders (see the claims register).
- [ ] **Appearance → Menus** — confirm the three menus are assigned to the
      Primary, Footer legal and Footer resources locations.
- [ ] **Settings → Permalinks** — the importer sets `/%postname%/` automatically;
      save the screen once if you use a caching plugin, then purge that cache.
- [ ] **Send a test enquiry** through the contact form and confirm the
      notification email arrives. If it does not, fix SMTP first — WordPress
      cannot send mail on most hosts without an SMTP plugin.
- [ ] **Delete the "Sample Page"** WordPress created at install. The importer warns
      about it but never removes content it did not create.
- [ ] **Appearance → Customize → Header & footer text** — top strip line, phone
      number, footer introduction and medical disclaimer.

## 5. Elementor notes

* Each page is a normal Elementor document. Open **Edit with Elementor** and you
  will see one widget per section (`Section: Hero`, `Section: Product`, …).
* The same content is also editable in the classic editor through the **Page
  sections** box (drag-to-reorder list with per-section fields). Both edit the
  same data; saving in either place keeps the other in sync.
* Do not wrap these widgets inside another Elementor "inner section" unless you
  also adjust spacing — the section widgets carry their own full-bleed
  background and padding.
* The theme intentionally has no Elementor Theme Builder templates in the ZIP.
  The header and footer are theme templates, so the site looks correct even if
  Elementor is deactivated.

## 6. Uninstall behaviour

* **Deactivating the plugin** removes the widgets from the editor but leaves all
  content, treatments and enquiries in the database. Pages fall back to the
  version stored in `_wla_sections`, which the plugin also wrote, so the site
  keeps rendering.
* **Deleting the plugin** does not delete content (no `uninstall.php`). Clean up
  manually only if you intend to remove the site data, and take a backup first.

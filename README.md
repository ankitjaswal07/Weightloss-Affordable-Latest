# WeightLossAffordable — installable build

Production-ready rebuild of the WeightLossAffordable website: the existing theme
design, extended into a backend-manageable, Elementor-editable site for
clinician-led weight-management services.

## Deliverables

| File | What it is |
| --- | --- |
| `weightloss-affordable.zip` | WordPress theme, v3.0.0 — the design |
| `weightloss-affordable-core.zip` | Companion plugin, v3.0.0 — treatments, sections, forms, SEO, demo importer |
| `docs/` | Installation, site manual, QA report, plugin/version list, backup & rollback, credentials & approvals, claims register, reference analysis |
| `src/` | Unpacked sources for both packages (what the ZIPs are built from) |

Start here: [`docs/README.md`](docs/README.md) → installation guide → site manual.

## Quick start

1. Back up the site (database + `wp-content`).
2. Install and activate `weightloss-affordable-core.zip`.
3. Install and activate `weightloss-affordable.zip`.
4. Install Elementor (free) if you want visual editing.
5. **WeightLossAffordable → Dashboard & import → Import demo content.**
6. Work through the first-run checklist in the installation guide.

The import is idempotent and never deletes content it did not create.

## What was built

* **Treatments CPT** with structured fields: medication details, pricing,
  variations/plans, benefits, FAQs, eligibility, safety, disclaimers, SEO.
* **17 section types**, each editable as a native Elementor widget *and* in the
  classic editor, with one new section added to showcase the products.
* **Working forms** — enquiry and consultation — with nonce, honeypot, timing
  check, rate limiting, consent validation, private storage and hashed IPs.
* **Demo importer** — one click, repeatable, no duplicates, no automatic deletion.
* **Technical SEO** — titles, descriptions, canonical, Open Graph, JSON-LD,
  noindex rules, SEO-plugin coexistence.
* **Documentation set** covering install, editing, QA, versions, rollback,
  credentials and a full claims register.
* English-only interface, translation templates included, no external CDNs, no
  paid dependencies, no fabricated testimonials or clinical claims.

## Verify it yourself

The QA report lists every test run against a real WordPress 6.7.2 installation,
including the 25 crawled routes, the form-validation matrix and the importer's
idempotency run. Two items can only be closed on your own machine (browser-based
visual review and the Elementor editor round-trip) and are listed as open in the
report.

## License

GPL-2.0-or-later for both packages, matching WordPress.

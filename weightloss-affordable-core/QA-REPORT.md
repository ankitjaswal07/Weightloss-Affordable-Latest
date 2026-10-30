# Testing and QA report

Date: 2026-10-30  
Package: WeightLossAffordable theme 2.1.0 + core plugin 2.1.0

## Audit findings (root causes)

1. **Clinical module never loaded.** `weightloss-affordable-core.php` did not require the NuestraRx quiz, treatments CPT, or experience widgets. The Get Quote quiz could not run.
2. **Demo import left treatments as drafts** and did not publish a quiz configuration.
3. **Public submissions disabled** (`quiz_submissions_enabled = 0`).
4. **Intake storage failed without libsodium** because encryption had to return an `nrxenc:` prefix.
5. **Spanish was the default** for quiz chrome, validation, admin CPT labels, and treatment titles.
6. **Treatment templates were thin** (hero + plans) instead of the required multi-section structure.
7. **Intake page template was missing** from the theme (`page-templates/intake.php`).

## Fixes implemented

- Bootstrap now loads quiz, treatments, experience widgets and demo clinical publisher.
- Demo import publishes pages, treatments, quiz, menus, and extra educational pages.
- English default language; bilingual `_en` values used on the frontend.
- Encryption fallback `nrxb64:` so submissions store without sodium.
- Full treatment sections: overview, suitability, process, safety, plans, FAQs, embedded quiz, related treatments.
- Get Quote quiz wired to `/consultation-eligibility/` and `/get-quote/` with Elementor.
- Header CTA points to Get Quote.

## Tests performed in this environment

| Area | Result | Notes |
| --- | --- | --- |
| Static PHP brace/paren balance on changed files | Pass | PHP CLI not installed in the sandbox, so `php -l` could not be run here |
| Quiz default config includes email, phone, name, measurements, consent | Pass | Inspected `store.php` |
| Treatments seed contains Semaglutide and Tirzepatide with process/FAQ/safety | Pass | Inspected `content/treatments.php` |
| Demo import calls clinical publisher and sets `publish` | Pass | Inspected `demo-import.php` + `demo-clinical.php` |
| Theme intake template exists | Pass | `page-templates/intake.php` |
| Homepage Elementor widget set unchanged in structure | Pass | Homepage still uses the original 11 widgets |

## Tests that require a WordPress install

Run after importing the demo on a clean site:

1. Home renders the existing Straw Luxury homepage.
2. `/semaglutida/` and `/tirzepatida/` are published and show unique multi-section content.
3. Complete the quiz with valid data → success message → intake stored → admin email attempted.
4. Invalid email / empty required fields → inline errors, Next blocked.
5. Previous restores answers.
6. Conditional pregnancy / BMI paths.
7. Mobile layout for quiz and treatment pages.
8. Re-run import → no duplicate pages.
9. All imported pages have status Published.
10. No unintended `noindex` on public pages (quiz pages may remain noindex if previously configured).

## Known limitations

- Email delivery depends on the host `wp_mail()` / SMTP plugin. The quiz still stores the enquiry if mail fails.
- Example plan prices are not a live checkout.
- No real patient testimonials, addresses, or clinician credentials are invented.
- Compounded GLP-1 content is educational. It is not FDA-approved product labeling.
- A live WordPress + Elementor runtime was not available in this sandbox, so browser QA must be repeated on the destination install.

## SEO notes

Implemented: unique titles/descriptions on imported pages, one H1 on treatment templates, canonical tags, robots index/follow unless noindex is set, Organization/WebSite/Breadcrumb JSON-LD, descriptive treatment FAQs.

Not claimed: rankings, indexing, or AI citations.

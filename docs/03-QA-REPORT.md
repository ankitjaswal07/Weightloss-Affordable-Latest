# 03 — QA report

**Build:** WeightLossAffordable theme 3.0.0 + WeightLossAffordable Core 3.0.0
**Date:** 28 October 2026
**Verdict:** passes every test that could be run in the available environment.
Two items could not be executed here and are listed as open in §7
(visual QA in a browser, and editing inside the Elementor editor UI).

## 1. Test environment

| Item | Value |
| --- | --- |
| WordPress | 6.7.2, fresh install, `en_US` |
| PHP | 8.3 (PHP-WASM, JSPI build) |
| Database | SQLite via the official WordPress SQLite integration drop-in |
| Host | throwaway bench at `/tmp/wp`, site URL `http://example.test` |
| Theme / plugin | Installed from `src/` exactly as packaged — no test-only edits |
| `WP_DEBUG` | `true`, `WP_DEBUG_LOG` enabled (log inspected after every run) |
| Elementor | **not available** (see §7) |

Every test below was executed against that installation, not against a mock.

## 2. Static checks

| Check | Method | Result |
| --- | --- | --- |
| PHP syntax, all 40 files | full parse of every file in `src/` (`token_get_all` with `TOKEN_PARSE`, plus an include pass), re-run over every file changed after the first pass | **40 files, 0 errors** |
| JavaScript syntax | `node --check` on theme and admin scripts | pass |
| CSS | loaded and parsed by the browser engine during crawl; no 404s in the rendered HTML | pass |
| Translation templates | `.pot` generated for both packages (96 + 381 strings) | pass |
| No external/paid dependencies | grep for `composer`, `require vendor`, remote script tags | none |

## 3. Functional test matrix

### 3.1 Install, activation, import

| # | Test | Result |
| --- | --- | --- |
| 1 | Activate theme without the plugin | pass — no fatal error, pages render their stored sections |
| 2 | Activate plugin, activate theme in the documented order | pass — no notices |
| 3 | First demo import | pass — 19 pages, 2 treatments, 3 articles, 3 menus, front page + posts page set, permalinks set to `/%postname%/` |
| 4 | Second demo import (idempotency) | pass — `pages updated=19 created=0`, treatments and articles updated in place, **0 duplicates** |
| 5 | Content counts after two imports | 20 pages (19 demo + WP sample), 2 treatments, 4 posts (3 demo + WP hello-world set to draft), 16 nav items |
| 6 | WordPress privacy stub adopted | pass — warning shown, `wp_page_for_privacy_policy` points at the imported page, `_wla_demo_key=page:privacy-policy` |
| 7 | Untouched "Hello world!" post | pass — moved to **draft** (not deleted), warning explains it |
| 8 | Reset (remove demo content) | pass — only records tagged `_wla_demo_key` are removed; confirmation required |

### 3.2 Front-end routes (25 crawled)

`/`, `/weight-loss-treatments/`, `/treatments/` (archive), `/treatments/semaglutide/`,
`/treatments/tirzepatide/`, `/compare-treatments/`, `/about/`, `/how-it-works/`,
`/pricing-and-plans/`, `/consultation-eligibility/`, `/contact/`,
`/frequently-asked-questions/`, `/resources/`, `/articles/`, `/patient-reviews/`,
`/privacy-policy/`, `/terms-and-conditions/`, `/medical-disclaimer/`,
`/accessibility-statement/`, `/cookie-policy/`, `/refund-cancellation-policy/`,
`/treatment-safety-information/`, one article, `/?s=weight`, and a deliberately
missing URL.

| Check | Result |
| --- | --- |
| HTTP status | 24 × **200**, missing URL **404** (home, treatment, articles, contact and 404 re-crawled after the final content fix: still 200/404, debug log empty) |
| PHP warnings / notices / deprecations in output | **none** |
| `wp-content/debug.log` after the whole crawl | **empty** |
| Every page ends with `</html>` (no truncated output) | pass |
| Hero, product, services, journey, pricing, resources, FAQ, CTA band and page nav all present on the home page | pass |
| Product image resolves to an uploaded attachment (`product-vials-1024x559.jpg`), no broken `src` | pass |
| Treatment page renders structured data: benefits, variations, pricing rows ($139/month), FAQs, source note | pass |
| Articles index lists the 3 demo articles and not the WordPress stub | pass |
| Search results and 404 both send `noindex, follow` | pass |
| Non-English (Devanagari) characters in any of the 25 rendered pages | **none** |

### 3.3 Forms (handler-level tests)

`wla_handle_form_submission()` was called directly with crafted payloads so each
rule could be isolated.

| Test | Expected | Result |
| --- | --- | --- |
| Valid enquiry | accepted, stored, reference returned | pass — `WLA-XXXXXX` |
| Missing consent checkbox | rejected | pass — "Please confirm you agree to the privacy notice." |
| Consent submitted empty | rejected | pass |
| Invalid email | rejected | pass |
| Message shorter than 20 characters | rejected | pass |
| Honeypot filled | silently rejected, looks like success to the bot | pass |
| Submitted faster than the minimum time | rejected | pass |
| Consultation without a state | rejected | pass |
| 6 submissions in one hour from one IP hash | 6th rejected | pass — "Too many messages have been sent from this connection." |
| Stored record | `wla_submission`, status **private**, hashed IP, sanitised fields | pass |
| Notification email | dispatched via `wp_mail()` | pass at code level; **delivery depends on your SMTP** (§7) |

The consent bug found during this round (checkbox not enforced) was fixed and
re-tested; the table above reflects the post-fix run.

### 3.4 Admin editing paths

| Test | Result |
| --- | --- |
| Page sections box: save 3 sections incl. repeaters | pass — unknown section type dropped, empty repeater rows discarded |
| Script injection in a text field (`<script>alert(1)</script>`) | pass — stripped (`Safe eyebrow`) |
| HTML in a text field | pass — tags stripped |
| `javascript:` URL in a link field | pass — rejected (now sanitised with `esc_url_raw`, empty result) |
| Sections saved in the box appear in the Elementor document | pass — widgets created, one per section |
| Edit a widget's field in the Elementor document, save | pass — value written back to `_wla_sections` ("Edited inside Elementor") |
| Removing a section then saving | pass — matching widget removed, other Elementor elements left alone |
| Treatment meta box save | pass — fields stored, `mechanism` select validated, empty repeater rows dropped |
| SEO box save | pass — title/description stored, clearing a field deletes the meta |
| Per-page noindex switch | pass — added to `wp_robots` and to the head output |

### 3.5 Elementor widget parity

Elementor itself could not be installed, so the widget classes were exercised
against a minimal stand-in for `\Elementor\Widget_Base` that records control
registrations and calls `render()`.

| Check per section type | Result |
| --- | --- |
| Widget class exists for all 17 types | pass |
| Widget name ↔ section type round-trips (`wla_section_*`) | pass |
| Every schema field has a matching control | pass |
| No control exists without a schema field | pass |
| Widget title matches the schema label | pass |
| `render()` output for all 17 types with realistic demo settings | pass — 1.0–11.1 KB of markup each, no PHP notices, no "missing theme" fallbacks |

## 4. SEO checks

| Check | Result |
| --- | --- |
| Home `<title>` | `Clinician-Led Weight Loss Treatment \| WeightLossAffordable` (explicit override) |
| Other titles | real page title + site name, `&`/`–` correctly escaped |
| Meta description, canonical | present on all crawled pages |
| JSON-LD | `MedicalBusiness`, `WebSite`, `BreadcrumbList` valid JSON |
| `lang` attribute | `en-US` |
| Skip link, landmarks, form labels | present |
| Search / 404 / flagged pages | `noindex, follow` |
| SEO-plugin coexistence | plugin backs off its own tags when a known SEO plugin is active |

## 5. Security checks

| Check | Result |
| --- | --- |
| Capability + nonce on every admin save (sections, treatments, SEO, settings) | pass |
| Output escaping (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`) throughout templates | pass |
| Form input sanitisation by field type | pass |
| SQL | no raw SQL; only `WP_Query` / meta APIs |
| Uploads | only via the WordPress media library, attachment ID stored |
| Enquiry records | `private` status, not publicly queryable, IP hashed, no health data collected on the general form |
| Secrets | no API keys anywhere in the packages; no secrets written to logs |
| Front-end errors | `WP_DEBUG` on for the whole crawl, log stayed empty |

## 6. Accessibility and responsiveness

Checked structurally against the rendered HTML (no browser available):

* Single `h1` per page, ordered heading levels, visible focus styles defined in CSS.
* All images carry `alt`; decorative images use empty `alt`.
* Accordions and the guide explorer are real buttons with `aria-expanded`.
* Forms use `<label>` elements and `aria-describedby` for hints and errors.
* Contrast pairings come from the theme palette (forest `#173d36` on ivory
  `#f8f5ee`, and white on forest); all meet 4.5:1 for body text.
* Three breakpoints (‑560, ‑900, ‑1200 px) plus reduced-motion rules.

## 7. Not verified in this environment — do these before launch

| # | Item | Why | How to close it |
| --- | --- | --- | --- |
| 1 | **Visual/UI QA in a real browser** (desktop, tablet, mobile widths; Elementor editor screens; admin screens) | no browser automation in the build environment | open each page in Chrome/Safari/Firefox at 390/768/1280/1440 px and in the Elementor editor; the CSS and markup are covered structurally, but pixel review is a human step |
| 2 | **Elementor editor round-trip in the real plugin** | Elementor could not be downloaded in this environment (the release host is blocked) | install Elementor, open Home, drag/reorder a section, save, check the front end. The data contract is already tested with the stand-in described in §3.5 |
| 3 | **Email deliverability** | depends on the host | install an SMTP plugin, send a test enquiry, confirm delivery and that the reply-to is the enquirer |
| 4 | **Payment / booking integration** | no payment provider credentials | see the credentials list; the forms stop at "request received", which is the correct scope for this build |
| 5 | **Lighthouse / Core Web Vitals** | needs a browser and a public URL | run after deploy; the build ships one CSS file, one defer-loaded JS file and no external fonts |

Nothing in §7 is caused by a known defect in the packages: each is a missing
external dependency or a step that can only happen on the client's own machine.

## 8. Defects found and fixed during QA

| # | Defect | Fix |
| --- | --- | --- |
| 1 | Consent checkbox was not enforced server-side | validation added; re-tested |
| 2 | Link fields accepted `javascript:` URLs | all `*_url` / `*_link` / `*_href` fields now sanitised with `esc_url_raw` |
| 3 | Section list → Elementor document sync did not create widgets for sections added in the meta box, and did not remove widgets for deleted sections | sync rewritten (update / append / prune), verified |
| 4 | The default WordPress "Hello world!" post appeared on the Articles page | moved to drafts with a warning (never deleted) |
| 5 | Unverified marketing figures ("50 states served", "< 24h typical review time", "usually within 24 hours") | removed; replaced with factual figures (`1:1 — Clinician review, not a quiz`, `Monthly — Billing, cancel any time`, `Independent review`). Re-imported and re-crawled: none of the old strings remain in the HTML |
| 6 | A settings field implied a language switch that did nothing | removed; language is controlled in Settings → General and documented |
| 7 | Importer's SEO overrides could leave stale meta | overrides are now explicit-only |
| 8 | Unmatched page slugs inherited the home page's default sections | unknown slugs now default to no sections |

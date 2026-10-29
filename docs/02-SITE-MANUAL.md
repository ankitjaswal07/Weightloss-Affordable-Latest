# 02 — Site manual

## 1. How content is stored

There are three ways a page body is produced, in priority order:

1. **Elementor document** (`_elementor_data`) — used when Elementor is active and
   the page has been opened/saved in it.
2. **Page sections** (`_wla_sections` JSON) — always written, read when Elementor
   is not in use. This is what the classic "Page sections" box edits.
3. **Classic content** — the normal WordPress editor, used for blog posts.

Saving the **Page sections** box writes both #2 and #1, so the Elementor
document always mirrors the section list. Editing a widget inside Elementor
writes back to #2 on save. You can use either editor; you cannot create a
disagreement between them.

## 2. Editing a page

### Option A — Elementor
`Pages → All Pages → [page] → Edit with Elementor`. Each section is one widget.
Click it and the fields appear in the left panel under **Content**. Layout
controls (top/bottom spacing, hide on mobile) are under **Style**.

### Option B — the Page sections box
`Pages → All Pages → [page]` and scroll to **Page sections**:

* Reorder with the up/down arrows in the section header.
* Add a section with the **Add section** dropdown (17 types).
* Remove a section with the **Remove** link (its data is discarded on save).
* Within a section, repeatable blocks (cards, steps, FAQs, specs, pricing rows)
  have **Add row** / **Remove row** buttons.

## 3. Section types

| Type | Used for | Key fields |
| --- | --- | --- |
| `hero` | Home page opening | eyebrow, three-line headline, body, two buttons, floating badge, three proof figures |
| `intro` | Editorial introduction | index number, eyebrow, title, body, note, link |
| `product` | **New product showcase section** | eyebrow, title, product image + product name, specs, three metrics, bullets, two buttons |
| `services` | Treatment/service cards | eyebrow, title, body, up to N cards (tag, title, text, CTA, icon) |
| `journey` | Four-stage care journey board | eyebrow, title, four stages with bullet lists and durations |
| `approach` | "How we work" list | eyebrow, title, numbered rows |
| `pricing` | Plan cards | eyebrow, title, note, plans (name, price, period, features, CTA, featured flag) |
| `resources` | Interactive guide explorer | eyebrow, title, guide categories and questions with answers |
| `reviews` | Published, attributable quotes | eyebrow, title, quotes (rating, author, author meta) — only add quotes you can attribute in writing |
| `faq` | Accordion | eyebrow, title, question/answer pairs |
| `contact` | Enquiry form + contact details | title, lead, form type, sidebar details |
| `subpage_hero` | Interior page header | eyebrow, title, lead, image, badge |
| `cta_band` | Closing call to action | title, body, buttons |
| `page_nav` | Cross-links between pages | list of page links |
| `rich_text` | Long-form policy text | title, HTML body |
| `treatment_grid` | Listing from the Treatments CPT | eyebrow, title, limit, CTA |
| `treatment_detail` | Single treatment body | renders the treatment's structured fields |

## 4. Treatments (backend-manageable)

`WeightLossAffordable → Treatments` (also `Treatments` in the admin menu).

Each treatment has structured fields, all optional and all hidden on the front
end when blank:

* **Overview** — short description (used on cards), feature flag, display order.
* **Medication** — product/medication name, active ingredient, receptor class,
  dosage form, administration frequency, pharmacy statement, notes.
* **Pricing** — price, comparison price, billing period, price note.
* **Eligibility & safety** — eligibility text, contraindications, required
  disclaimer.
* **CTA** — button label and URL.
* **SEO** — title and meta description for the treatment page.
* **Repeatable groups** — benefits/how-it-works rows (icon, title, text),
  **variations/plans** (label, price, period, note) and **FAQs** (question,
  answer). Empty rows are discarded on save.

The listing page uses the `treatment_grid` section, and the single template is
`single-nrx_treatment.php`; neither hardcodes treatment content.

## 5. Forms

Two forms ship with the theme: the **contact / enquiry form** and the
**consultation request form** (a superset: it adds state and preferred contact
method). Both post to `admin-post.php` and are processed by the plugin.

What is enforced server-side on every submission:

| Check | Failure message |
| --- | --- |
| Nonce | "Your session expired…" |
| Honeypot field (`wla_company`) | Silent success-shaped rejection (bots get no signal) |
| Minimum time on page (default 2s) | "That was quick — please check your details and try again." |
| Rate limit (5 per hour per IP hash) | "Too many messages have been sent from this connection." |
| Required name / email / message | Field-specific messages |
| Valid email | "Please enter a valid email address." |
| Message length ≥ 20 characters | "Please add a little more detail…" |
| Consultation requires state | "Please choose your state…" |
| **Consent checkbox** | "Please confirm you agree to the privacy notice." |

On success the enquiry is stored as a **private** `wla_submission` post with a
`WLA-XXXXXX` reference, the IP is stored **hashed**, and a notification email is
sent to the configured recipients. The success panel shows the reference number.

Enquiries older than the retention window (default 365 days; `0` disables) are
deleted by a daily cron job.

Where to look: `WeightLossAffordable → Enquiries`. The list shows reference, form
type and email; the detail screen shows all submitted fields plus the source URL.

## 6. Settings

`WeightLossAffordable → Settings` holds everything that should not be hardcoded:

* **Business** — name, contact email/phone/address, hours, service area, pharmacy
  statement.
* **Form notifications** — toggle, recipients, minimum submit time, retention
  days, form disclaimer.
* **Clinical review & locale** — reviewer name/credentials, last review date,
  currency code and symbol.

## 7. SEO

The plugin outputs the technical basics without an SEO plugin: title overrides,
meta description, canonical, Open Graph/Twitter tags, JSON-LD (`MedicalBusiness`,
`WebSite`, `BreadcrumbList`), and `noindex` for search results, 404s and any page
you mark. Every page and post has a **SEO and social preview** box for the title,
description, sharing image and a noindex switch.

If Yoast, Rank Math, SEOPress or All in One SEO is active, the plugin steps back
and outputs only its JSON-LD, so nothing is duplicated.

## 8. Menus and header/footer

* Menus: **Appearance → Menus** — three locations: Primary, Footer legal, Footer
  resources.
* Top strip text, phone number, footer introduction and medical disclaimer:
  **Appearance → Customize → Header & footer text**.
* Logo: **Appearance → Customize → Site Identity** (SVG mark ships in
  `assets/images/`).

## 9. Language

The site is English throughout: `en_US` at install, all interface strings in
English, and no mixed-language labels. Both packages ship `.pot` templates
(`languages/`) so a translation can be added later without touching code.

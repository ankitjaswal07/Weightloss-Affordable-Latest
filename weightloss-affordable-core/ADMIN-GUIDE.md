# Admin configuration guide

## Editing pages

All imported pages use Elementor. Open a page → **Edit with Elementor**.

Reusable widgets:

- Straw Luxury homepage sections (`wla_hero_pro`, steps, FAQ, contact, CTA)
- Clinical homepage sections: Protected Start, GLP-1 Signal, Two Treatments, Transformations, Video Stories, GLP-1 Science
- **Experience Section** widget: Treatment Plans, Treatment Detail, Get a Quote (embed quiz), calculators

## Treatments

**Treatments** in wp-admin is the product source of truth.

The admin editor is English-only. The public site still offers EN / ES through the live translator. English `_en` values are what visitors see by default.

Editable groups include:

- Hero eyebrow, badge, tagline, overview
- Plans and example prices
- Benefits / suitability / not recommended
- Process steps
- Safety and aftercare
- FAQs
- Related treatment slugs
- SEO title and description (WLA SEO box)

Prices in the demo are **example figures copied from publicly listed compounding GLP-1 plan structures**. Confirm them before using the site in production. Do not invent live checkout.

## Get Quote quiz

Administrators can add, edit, delete and reorder steps and fields without code.

Open the quiz builder (NuestraRx / Quiz admin screen bundled in the core plugin):

- Step types: text, email, telephone, dropdown, radio, checkboxes, date, textarea, measurements, consent
- Required flags and validation messages
- Conditional visibility
- Button labels and success / ineligible screens
- Notification email (defaults to the WordPress admin email after import)

Frontend behaviour:

- Previous / Next
- Progress bar
- Answers kept when moving backward
- Required-field validation before continue
- Duplicate-submit guard
- Success screen after storage
- Network error handling

Submissions are stored as private intake records with encrypted payloads. Completing the quiz is **not** a diagnosis or a guaranteed prescription.

## Language

Default language is English. The live EN/ES translator remains available in the header for visitors. Do not add duplicate Spanish copy pages.

## SEO

Each page has:

- SEO title, meta description, keywords, OG image, noindex checkbox
- Canonical URL
- Organization / WebSite / WebPage / Breadcrumb JSON-LD

Do not add fake review ratings or fabricated credentials.

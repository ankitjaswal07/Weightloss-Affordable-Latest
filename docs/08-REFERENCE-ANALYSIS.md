# 08 — Reference analysis and build mapping

NuestraRx (`https://nuestrarx.com/`, reviewed 28 October 2026) was analysed as a
**functional and structural** reference only. No text, image, logo, colour or
component was copied.

## 1. Page inventory — reference vs this build

| Purpose | NuestraRx | This build | Notes |
| --- | --- | --- | --- |
| Home | long single page: hero with price + trust badges, stats, 4-step how-it-works, treatments grid, FAQ, closing CTA, floating WhatsApp button | `/` — hero, product showcase, services, 4-stage journey, approach, pricing, resource explorer, FAQ, CTA band, page nav, floating contact button | same storytelling order, original design and copy |
| Treatments list | grid of treatments with price per month | `/weight-loss-treatments/` and `/treatments/` (CPT archive) | renders from the Treatments CPT, never hardcoded |
| Treatment detail | one page per medication with price, what's included, FAQs | `/treatments/semaglutide/`, `/treatments/tirzepatide/` via `single-nrx_treatment.php` | structured fields: medication, pricing, variations, benefits, FAQs, safety |
| Comparison | not present as a dedicated page | `/compare-treatments/` | added: side-by-side table from the CPT |
| How it works | 4-step section on the home page | dedicated `/how-it-works/` page plus the journey section | |
| Pricing | weekly pricing block on the home page | `/pricing-and-plans/` plus the pricing section | monthly, self-pay framing |
| Eligibility / consultation | formulary and intake links | `/consultation-eligibility/` with the consultation form | |
| About | about section | `/about/` | |
| FAQ | accordion | `/frequently-asked-questions/` plus per-page FAQ sections | |
| Resources / education | not really present | `/resources/` with the interactive guide explorer, `/articles/` blog with 3 articles | added depth |
| Reviews | testimonials | `/patient-reviews/` — section exists, **ships empty** | no fabricated testimonials |
| Contact | WhatsApp + form | `/contact/` with the enquiry form | |
| Legal/safety | refund, safety, compliance pages | privacy, terms, medical disclaimer, refund/cancellation, cookie, accessibility, treatment safety | |
| Order/checkout | product/e-commerce flow | **out of scope** — the build ends at a consultation request | documented in the credentials list |

## 2. Feature comparison

| Feature | Reference | This build | Where |
| --- | --- | --- | --- |
| Price shown up front | yes | yes — pricing section + treatment prices from structured fields | `pricing`, `treatment_detail` |
| Trust indicators | badges | three factual proof figures, editable | `hero` |
| Step-by-step process | 4 steps | 4-stage journey board + dedicated page | `journey` |
| Treatments managed in the backend | presumably a product catalogue | `nrx_treatment` CPT with structured meta and repeaters (variations, price rows, FAQs) | plugin |
| Repeatable content blocks | page builder | 17 typed sections, each a native Elementor widget **and** editable in the classic editor | plugin + theme |
| Forms | enquiry + WhatsApp | enquiry and consultation forms with nonce, honeypot, timing check, rate limit, consent, private storage, hashed IP | plugin |
| Floating contact | WhatsApp float | floating contact button driven by the site's own contact settings | theme |
| Language | Spanish/English mix | English only, `en_US`, no mixed labels | theme + plugin |
| SEO | unknown | titles, descriptions, canonical, OG/Twitter, JSON-LD, noindex rules | plugin |
| Demo import | n/a | one-click, idempotent, no automatic deletion, dependency checks | plugin |
| Translation-ready | n/a | `.pot` for both packages | both |

## 3. What was intentionally *not* replicated

* **E-commerce checkout.** The reference sells through an order flow; this build
  stops at a consultation request, because prices, fulfilment and payment
  credentials are unconfirmed. The dependency is documented instead of faked.
* **Spanish-language layer.** Your requirement is English default; the
  translation templates are in place if a second language is added later.
* **WhatsApp integration.** Replaced with the site's own contact details so no
  third-party account or phone number is invented.
* **Testimonials and before/after imagery.** Deliberately absent.

## 4. Design continuity

The design is the existing theme, not the reference: forest/ivory/sage palette,
Fraunces + Manrope, soft rounded cards, generous white space, pill buttons,
label-and-rule section headers, the four-column journey board and the modal
guide explorer. The one new block is the **product showcase** section, added in
the same visual language (image panel with a floating badge, spec list, three
metric tiles) so it reads as part of the original design rather than an insert.

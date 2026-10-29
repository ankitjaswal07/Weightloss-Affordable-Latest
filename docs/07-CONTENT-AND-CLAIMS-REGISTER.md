# 07 — Content and claims register

Every statement in the demo content that makes a factual, commercial or clinical
claim, with its current wording and what it needs before launch. Use this as a
checklist: nothing here was invented to look good for a screenshot, but some
items are written generically and must be replaced with your confirmed facts.

Legend: **A** = approved as-is, **C** = must be confirmed before launch,
**R** = must be reviewed by a clinician or lawyer, **X** = placeholder, delete
before launch.

## 1. Home page

| Item | Current wording | Status | Note |
| --- | --- | --- | --- |
| Hero eyebrow | "Medically guided weight care · English-language support" | A | describes the service model |
| Hero headline | "Weight care that makes sense." | A | no promise |
| Hero body | "…a questionnaire alone never guarantees treatment." | A | accurate |
| Proof 1 | "Written reply to every request" | C | operational commitment |
| Proof 2 | "04 — Clear stages" | A | describes this site's process |
| Proof 3 | "0 — Guaranteed outcomes promised" | A | statement of policy |
| Hero badge | "Clinician-led care" / "Independent review for every request" | C | confirm independent review exists |
| Product showcase | "2 — Medication options", "1:1 — Clinician review, not a quiz", "Monthly — Billing, cancel any time" | C | confirm all three; the "50 states served" and "< 24h review time" placeholders were **removed** as unverifiable |
| Journey | four stages: questionnaire ("About 5 minutes"), independent review, treatment decision, follow-up | C | confirm the real process; the previous "usually within 24 hours" promise was **removed** — no turnaround is stated anywhere now |
| Pricing band | "Transparent monthly pricing" | C | confirm prices before publishing |
| FAQ "Where is the service available?" | "Availability depends on whether a clinician licensed in your state can treat you." | A | deliberately avoids a state list |
| FAQ on compounding | "Compounded preparations are not FDA-approved products…" | R | legally significant; keep verbatim unless counsel says otherwise |

## 2. Treatment pages (Semaglutide, Tirzepatide)

| Field | Current state | Status |
| --- | --- | --- |
| Medication name | "Compounded semaglutide" / "Compounded tirzepatide" | C — confirm which products you dispense |
| Active ingredient, receptor class, dosage form, frequency | generic, correct for the drug class | R |
| Price / comparison price | `$139` per month (was `$199`); `$239` (was `$299`) | C — demo values, confirm current pricing and that a "was" price is lawful in your market |
| Eligibility | "sometimes alongside a related condition such as prediabetes or high blood pressure" | R |
| Safety / contraindications | MTC/MEN2, pancreatitis, gallbladder, dehydration, hypoglycaemia, allergic reaction, pregnancy | R — reviewed against public prescribing information, needs clinical sign-off |
| Variations | monthly plan rows | X — replace with your real plan names |
| FAQs | 5 per treatment, including "Is this the same as a branded medication?" | R |
| Disclaimer | "Compounded preparations are not FDA-approved products and are not interchangeable with branded medications." | R |
| Reviewer line | hidden until a name and date are entered in Settings | C |

## 3. Articles

Three articles ship, each ending with "General information only. This article is
not medical advice."

| Article | Claim type | Status |
| --- | --- | --- |
| "Side effects: what to expect and when to get help" | symptom list and urgency guidance | R |
| "Who typically qualifies for prescription weight management" | BMI-and-comorbidity framing, no specific thresholds | R |
| "How GLP-1 medications are described in the medical literature" | mechanism description, explicitly non-promissory | R |

## 4. What was deliberately removed or never written

* No invented statistics, no "clinically proven", no percentage weight-loss
  figures, no before/after imagery.
* No testimonials. The reviews section exists and renders, but ships with no
  quotes: only add quotes you can attribute in writing and have permission to
  publish.
* No state count, no "50 states served" badge, no "trusted by X patients".
* No FDA-approval claim for compounded products — the opposite is stated.
* No stock photos of people presented as patients; the four demo photos are
  licensed-free illustration only and carry descriptive alt text.
* No copied text or imagery from NuestraRx. The reference site was used only to
  decide which pages and features to build (see document 08).

## 5. Legal pages

Privacy policy, terms and conditions, medical disclaimer, refund and cancellation
policy, cookie policy, accessibility statement and treatment safety information
are drafted to match how the site actually behaves (private enquiry storage,
hashed IPs, no analytics by default, no payment on site). They are a starting
point for counsel, not legal advice. Confirm at minimum:

* the legal entity name, registered address and jurisdiction;
* the retention period actually configured in Settings;
* whether analytics or marketing cookies will be added later (if so, the cookie
  policy and a consent banner must be updated);
* the refund rules for medication already dispensed and shipped;
* the complaints/escalation route for clinical concerns.

## 6. Sign-off

| Content area | Reviewed by | Date | Notes |
| --- | --- | --- | --- |
| Home page claims | | | |
| Treatment data (both pages) | | | |
| Safety and side-effect text | | | |
| Articles | | | |
| Legal pages | | | |
| Prices and plans | | | |

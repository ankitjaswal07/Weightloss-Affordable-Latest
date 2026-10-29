# 06 — Credentials and approvals required before launch

Nothing in this list blocks the site from being installed and demonstrated. Each
item blocks a *claim* or a *function* that must not be invented: where a value was
unknown, the build either leaves it blank (and hides the element) or uses neutral
wording that demands nothing from the client. This is the list of things only you
can supply.

## 1. Access and accounts

| # | Item | Needed for | Owner | Status |
| --- | --- | --- | --- | --- |
| 1 | WordPress administrator login for the target site | installing the ZIPs, running the importer | client / host | required |
| 2 | SFTP/SSH or hosting file manager | backups, reading `debug.log`, emergency recovery | client / host | required |
| 3 | Database backup credentials or panel access | pre-deploy dump | client / host | required |
| 4 | Domain DNS access | pointing the domain, HTTPS certificate | client | required at cutover |
| 5 | Google Search Console + Analytics (or an alternative) | verifying indexing, measuring traffic | client | required at launch |
| 6 | SMTP service credentials (host SMTP, SendGrid, Postmark, Mailgun…) | reliable form email delivery | client | **required** — WordPress mail alone is not dependable |
| 7 | Elementor account (optional) | only for Elementor Pro / Template Kits; the free plugin needs no account | client | optional |

No third-party API key, payment key or licence key is stored in the packages.

## 2. Business information (fills visible fields)

| # | Item | Where it appears | Current state |
| --- | --- | --- | --- |
| 8 | Legal business name | footer, JSON-LD, policy pages | placeholder = site title |
| 9 | Public contact email | footer, contact section, form errors | placeholder = admin email |
| 10 | Public phone number | top strip, contact section | **empty** — hidden until supplied |
| 11 | Business address / registered entity | footer, policy pages | **empty** — hidden until supplied |
| 12 | Business hours | contact section | demo value "Monday–Friday, 9am–5pm" |
| 13 | Service area (states/countries served) | settings, contact section | demo value: "Confirm with your clinical partner before publishing" |
| 14 | Pharmacy / supplier statement | treatment pages, footer | neutral statement, needs legal review |
| 15 | Currency and pricing | pricing + treatment pages | USD / `$`; prices are demo values |

## 3. Clinical and regulatory approvals

| # | Item | Why it matters | Status |
| --- | --- | --- | --- |
| 16 | **Clinical reviewer name and credentials** | the site promises clinician-led care and shows a review line on treatment and medical pages | **not supplied** — the field is empty and the line stays hidden |
| 17 | **Date of last clinical review** | shown next to the reviewer line | **not supplied** |
| 18 | **Approval of the treatment pages** (eligibility, contraindications, side effects, disclaimers) | these are medical statements; they were written from general published information, not from a licensed clinician's sign-off | **needs sign-off** |
| 19 | Confirmation of which products are actually offered | the demo shows **compounded semaglutide** and **compounded tirzepatide** as examples | **needs confirmation** |
| 20 | Confirmation of prices ($139/mo semaglutide, $239/mo tirzepatide in the demo) | published prices are a binding commercial statement in many jurisdictions | **needs confirmation** |
| 21 | Compounded-medication wording approval | "not FDA-approved, not interchangeable with branded products" statements are legally significant | **needs sign-off** |
| 22 | Telehealth licensing confirmation per state | the site says eligibility depends on a clinician licensed in the visitor's state | **needs confirmation** |
| 23 | Privacy policy, terms, refund, cookie and accessibility pages | they ship as drafts written to be accurate for this service model; they are **not legal advice** | **needs legal review** |
| 24 | Data-processing agreements (host, SMTP, CRM, analytics) | GDPR/CCPA obligations for enquiry data | **client responsibility** |
| 25 | Medical-director or compliance-officer approval (if your jurisdiction requires one) | advertising of prescription medicines | **client responsibility** |

## 4. Marketing claims requiring written substantiation

Each of these is in the demo content. Keep it only if you can evidence it;
otherwise delete the line. All are editable in the page sections box.

| Claim (current demo wording) | Where | Evidence needed |
| --- | --- | --- |
| "Written reply to every request" | home hero proof figures | an operational commitment you can keep |
| "04 — Clear stages" | home hero proof figures | describes this site's four-stage process, no external evidence needed |
| "0 — Guaranteed outcomes promised" | home hero proof figures | factual statement about this site's own policy |
| "No insurance required" | home hero | confirm the service is self-pay only |
| "1:1 — Clinician review, not a quiz" | product showcase | confirm every request is reviewed by a licensed clinician |
| Published prices and "was" comparison prices | pricing and treatment pages | current, confirmed price list |
| Anything on the Patient reviews page | `reviews` section | **only** quotes you can attribute in writing, with permission; the demo section ships empty on purpose |

## 5. Approvals at cutover (sign-off sheet)

| Item | Approved by | Date |
| --- | --- | --- |
| Design and layout | | |
| All clinical wording on treatment and safety pages | | |
| Prices, billing periods and refund rules | | |
| Legal pages (privacy, terms, cookies, accessibility, refund) | | |
| Contact details, hours and service area | | |
| SEO titles and descriptions | | |
| Forms: fields, consent wording, retention period, recipients | | |
| Backup schedule and who owns restores | | |

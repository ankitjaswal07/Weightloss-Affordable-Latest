# WeightLossAffordable — delivery documentation

This folder contains every non-code deliverable for the rebuild. Start with the
installation guide, then the site manual; the rest are reference documents for
QA, operations and launch approvals.

| Document | Use it for |
| --- | --- |
| [01 — Installation & setup](01-INSTALLATION-AND-SETUP.md) | Installing the ZIPs, running the demo import, first-run checklist |
| [02 — Site manual](02-SITE-MANUAL.md) | Editing pages in Elementor, the sections box, treatments, forms, settings, menus |
| [03 — QA report](03-QA-REPORT.md) | What was tested, on what, results, and known limitations |
| [04 — Plugins & versions](04-PLUGINS-AND-VERSIONS.md) | Exact versions, compatibility matrix, update policy |
| [05 — Backup & rollback](05-BACKUP-AND-ROLLBACK.md) | Backup before deploy, rollback of theme/plugin/content, emergency recovery |
| [06 — Credentials & approvals](06-CREDENTIALS-AND-APPROVALS.md) | What is required from you before launch, and who owns each item |
| [07 — Content & claims register](07-CONTENT-AND-CLAIMS-REGISTER.md) | Every statement that needs client/clinical sign-off, with its current wording |
| [08 — Reference analysis](08-REFERENCE-ANALYSIS.md) | NuestraRx page inventory and feature comparison behind the rebuild |

## Packages

| File | Type | Version |
| --- | --- | --- |
| `weightloss-affordable.zip` | WordPress theme (the design) | 3.0.0 |
| `weightloss-affordable-core.zip` | Companion plugin (data, forms, Elementor widgets, importer) | 3.0.0 |

Both ZIPs are built from `src/` in this repository. Install order: **plugin
first, then theme** (see the installation guide for the one-minute path).

## Design direction

The build keeps the existing theme's design language — forest/ivory/sage
palette, Fraunces headings with Manrope body text, soft card surfaces, generous
section padding, pill buttons and the four-stage care journey board — and adds
one new section type that showcases the products (`product`, rendered on the
home page as "Treatment, explained plainly"). NuestraRx was used only to decide
*what* the site should be able to do (page inventory and features); none of its
text, imagery or branding was copied.

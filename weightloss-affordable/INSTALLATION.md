# WeightLossAffordable — Installation and Demo Import

English is the default language for the frontend, WordPress administration labels in this package, demo content, quiz validation, and imported pages.

## Requirements

- WordPress 6.6 or later
- PHP 7.4 or later
- Free Elementor (Elementor Pro is not required)
- A writable uploads directory for demo images

Do not modify WordPress core files.

## Install

1. Upload and activate the theme ZIP `weightloss-affordable.zip` under **Appearance → Themes**.
2. Upload and activate the plugin ZIP `weightloss-affordable-core.zip` under **Plugins → Add New**.
3. Install and activate the free **Elementor** plugin.
4. Open **WeightLoss Setup → Demo Import**.
5. Click **Import Complete 100% Preview Website**.

The importer is idempotent. Running it more than once updates the same seeded pages, treatments, menus and quiz instead of creating duplicates.

## What the import publishes

Everything below is created with `publish` status (not draft):

- Home and supporting pages (About, Treatments, How it works, Pricing, FAQs, Contact, legal pages)
- Get Quote / Consultation eligibility page with the working step-by-step quiz
- Homepage clinical sections (treatments, protection, GLP-1 signal, how it works, science, transformations, video stories)
- Treatment → package → Get Quote workflow (`?treatment=` and `?plan=` are stored with the enquiry)
- Weekly payment, How GLP-1 works, Clinicians, and Pharmacy transparency pages
- Semaglutide and Tirzepatide treatment records (`/semaglutida/`, `/tirzepatida/`)
- Educational articles
- Primary and footer menus
- Quiz configuration (draft + published) with submissions enabled
- Notification recipient set to the site admin email

## After import

1. Confirm **Settings → General → Site Language** is English.
2. Open **Pages** and **Treatments** and verify every item is Published.
3. Visit `/consultation-eligibility/` or `/get-quote/` and complete the quiz.
4. Confirm the enquiry appears under **Intakes** (or the NuestraRx / quiz submissions screen).
5. Edit page sections in Elementor. Treatment body copy also lives on each Treatment record.

## Rollback

Keep a full backup of the database and `wp-content` before import. To roll back, restore that backup. Deactivating the plugin does not delete imported pages or intakes.

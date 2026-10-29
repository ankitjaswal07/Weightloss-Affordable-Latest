# 04 — Plugins, packages and versions

## 1. What you are installing

| Package | Type | Version | Slug | Text domain |
| --- | --- | --- | --- | --- |
| `weightloss-affordable.zip` | Theme | **3.0.0** | `weightloss-affordable` | `weightloss-affordable` |
| `weightloss-affordable-core.zip` | Plugin | **3.0.0** | `weightloss-affordable-core` | `weightloss-affordable-core` |

The theme needs the plugin for treatments, forms, Elementor section widgets,
SEO and the demo importer. The plugin needs the theme for its section renderers;
without the theme it stays inert instead of breaking the site. **There is no
child theme**, and none is needed: all design changes go through `theme.json`,
the Customizer, Elementor and the page sections data, none of which require
editing theme files.

## 2. Compatibility

| Requirement | Minimum | Recommended | Notes |
| --- | --- | --- | --- |
| WordPress | 6.4 | 6.7 or newer | tested on 6.7.2 |
| PHP | 7.4 | 8.2 / 8.3 | tested on 8.3 |
| MySQL | 5.7 | 8.0 | MariaDB 10.3+ also fine |
| Elementor | 3.18 (free) | current free | used for visual editing only |
| Elementor Pro | — | optional | only needed for Theme Builder headers/footers; the theme already renders header and footer |

## 3. Third-party plugins

| Plugin | Status | Why |
| --- | --- | --- |
| **Elementor** (free) | recommended, not bundled | visual editing of every page; each section is a native Elementor widget |
| SMTP plugin (e.g. WP Mail SMTP, Post SMTP) | **required for reliable form email** | PHP `mail()` is unreliable on most hosts |
| Caching plugin | optional | if used, purge after deploy and after changing menus or permalinks |
| SEO plugin (Yoast / Rank Math / SEOPress / AIOSEO) | optional | the plugin detects them and stops emitting its own title/description/canonical to avoid duplicates |
| Backup plugin (UpdraftPlus, BlogVault, host-level) | recommended | see the backup & rollback procedure |
| Security/anti-spam plugin | optional | the forms already have a honeypot, a timing check and a per-IP rate limit |

Nothing in the packages calls a paid API, and no licence key is embedded.

## 4. Bundled libraries

None. No jQuery plugin, no icon font, no external CDN, no Google Fonts request.
Fraunces and Manrope are declared in CSS with system-serif and system-sans
fallbacks, so the site works offline and has no third-party font dependency.

## 5. Files

| Package | PHP files | CSS | JS | Images | Total size |
| --- | --- | --- | --- | --- | --- |
| Theme | 18 | 2 (`theme.css` ≈ 6 000 lines, `editor.css`) | 1 | 5 (4 photos + 1 SVG mark) | ≈ 1.1 MB |
| Plugin | 22 | 1 (admin) | 1 (admin) | 4 (demo photos) | ≈ 1.0 MB |

## 6. Versioning and updates

Both packages use semantic versioning in their headers (`Version: 3.0.0`).
To deploy a change: bump the version in `style.css` / the plugin header **and**
the `WLA_THEME_VERSION` / `WLA_CORE_VERSION` constant, rebuild the ZIPs from
`src/`, then install the ZIP over the existing copy (WordPress replaces the
files, keeps the data). Section schema changes are additive-safe: unknown fields
are ignored and missing fields fall back to defaults.

## 7. Version history

| Version | Change |
| --- | --- |
| 2.0.0 | Previous release (design-only pages, no structured data) |
| 3.0.0 | Rebuild: treatments CPT with structured fields, 17 Elementor section widgets, page-sections editor, forms with spam controls, technical SEO, demo importer with idempotency and stub adoption, compliance checklist, WP-CLI commands, translation templates |

# 05 — Backup & rollback procedure

Take a backup **before** installing, and keep it until the new site has been live
for at least a week. The procedure below assumes you keep backups outside the
site (a downloadable archive), not only inside it.

## 1. Before you deploy (15 minutes)

1. **Database**
   * Hosting panel or a backup plugin: create a full database dump, download it,
     and note the timestamp and the WordPress version it came from.
   * Or with WP-CLI: `wp db export pre-wla-$(date +%F).sql`
2. **Files**
   * Archive `wp-content/themes/` and `wp-content/plugins/` (or the whole
     `wp-content` if disk space allows).
   * Also copy `wp-config.php` and `.htaccess` if you have custom rules.
3. **Record the current state**
   * Active theme and version, active plugins and versions
     (`wp plugin list --status=active`), and the site URL.
   * A screenshot of the live home page — useful when comparing after the swap.
4. **Test on staging first** if the host offers it. Deploying a theme + plugin
   pair to a live site during business hours without a staging test is the main
   avoidable risk in this project.

## 2. Deploy

1. Upload `weightloss-affordable-core.zip` and activate it.
2. Upload `weightloss-affordable.zip` and activate it.
3. Run the demo import (or copy the content from staging).
4. Walk the launch checklist in the installation guide.
5. Purge any cache plugin, CDN and server-side page cache.

## 3. Rollback — theme and plugin only

This is the common case: the design or a feature has a problem and you want the
previous site back.

1. **Deactivate the new theme** — `Appearance → Themes` → activate the previous
   theme. WordPress will fall back to it immediately.
2. **Deactivate** WeightLossAffordable Core. Content stays in the database
   (posts, treatments, enquiries, settings option), so nothing is lost.
3. Reinstall the previous ZIPs (from the backup you took in §1) if you also want
   the old files back: `Appearance → Themes → Add New → Upload Theme` and
   `Plugins → Add New → Upload Plugin` will offer to replace the current version.
4. Purge caches and spot-check the home page, one treatment page and one form.

Content created by the importer does not need to be removed for a theme
rollback, but if you want it gone, use **WeightLossAffordable → Dashboard →
Remove demo content** *before* deactivating the plugin — it only deletes records
tagged `_wla_demo_key` and never touches pages you created yourself.

## 4. Rollback — full restore

Use when the database itself is wrong (for example, a partial restore or collided
IDs).

1. Put the site into maintenance mode (or set it to private) so no enquiries
   arrive mid-restore.
2. Restore the files from the archive in §1.
3. Import the database dump:
   * Hosting panel import, or `wp db import pre-wla-YYYY-MM-DD.sql`.
4. If the site URL changed during the failed deploy, correct it:
   `wp option update home 'https://example.com'` and `wp option update siteurl 'https://example.com'`.
5. Purge caches, then check: home page loads, `/wp-admin` loads, a test enquiry
   is submitted and received, treatments appear in the admin menu.

## 5. Emergency recovery (site down after a deploy)

Start with the error, not with a restore.

1. `wp-content/debug.log` — read the last fatal error. Add `WP_DEBUG_LOG` and
   `WP_DEBUG_DISPLAY=false` to `wp-config.php` if the log is empty.
2. Rename `wp-content/themes/weightloss-affordable` to
   `weightloss-affordable.bak` — WordPress falls back to an installed theme.
3. Rename `wp-content/plugins/weightloss-affordable-core` to
   `...-core.bak` — WordPress deactivates it silently.
4. If the admin is still unreachable, add to `wp-config.php`:
   `define( 'WP_HTTPS_FORCE', false );` is *not* the fix for most issues — instead
   restore the file archive from §1 and retry.
5. Only after the site is stable, re-apply the packages and reproduce the error
   on staging with the same PHP version.

## 6. Data-specific notes

| Data | Where | Included in a DB dump | Safe to delete on rollback? |
| --- | --- | --- | --- |
| Pages, articles, menus | `wp_posts`, `wp_postmeta`, `wp_terms` | yes | importer-created records only, via the dashboard reset |
| Page sections (`_wla_sections`) and Elementor data (`_elementor_data`) | `wp_postmeta` | yes | no — they are the page content |
| Treatments | `nrx_treatment` posts + `_wla_*` meta | yes | yes, if demo-only |
| Enquiries | `wla_submission` posts, `private`, IP hashed | yes | **no** — export before any cleanup, they are business records |
| Business settings | `wla_settings` option | yes | re-enter by hand after a restore |
| Demo bookkeeping | `_wla_demo_key` meta, `wla_demo_imported_at` option | yes | drives the reset, so keep it consistent with the content |

## 7. Recommended retention

* Daily automated database backup, kept 30 days (a host feature or a plugin).
* Weekly full-site backup (files + database), kept 8 weeks.
* One archive taken immediately before each deploy, kept until the next deploy
  is signed off.
* Enquiries: the site deletes them automatically after the retention window set
  in **Settings → Form notifications** (365 days by default; `0` disables). Make
  sure your data-retention policy matches whatever you configure, and export the
  records you must keep for longer.

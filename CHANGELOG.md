# Changelog

All notable changes to `acrossai-co/main-menu` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.30] - 2026-08-02

### Added
- **Cross-plugin notice system.** Any AcrossAI consumer plugin can push admin-notice records into a shared collection via the new `acrossai_notices` filter. Notices appear in two places:
  - **Notices submenu** under the AcrossAI parent menu — always visible when at least one notice exists, with a WP-style count bubble (`.awaiting-mod`) in the menu label. Full styled list matching the dashboard aesthetic. Menu is *not* registered when count is zero.
  - **Top-of-page summary** — one WordPress-native `.notice.notice-warning.is-dismissible` printed on every other admin page ("AcrossAI has N notifications for your attention — View notices →"). Clicking the ✕ persists dismissal until the notice set changes (fingerprint-based: `sha1` of sorted notice IDs stored in per-user meta `_acrossai_notices_summary_fp`).
- **New public classes** under `AcrossAI_Main_Menu\`: `Notices`, `NoticesPageRenderer`, `NoticesAjaxHandlers`, `SummaryNoticeEmitter`.
- **New page slug** `SettingsPage::NOTICES_SLUG = 'acrossai-notices'`.
- **New static accessor** `SettingsPage::get_notices(): ?Notices` for consumers that want to inspect the current notice list programmatically.
- **New AJAX endpoint** `wp_ajax_acrossai_notices_dismiss_summary` — nonce + `manage_options` guarded; server re-validates the client-supplied fingerprint against the current notice set (blocks poisoning the user meta with an unrelated hash).

### Notice record shape
```php
add_filter( 'acrossai_notices', function ( array $notices ): array {
    $notices[] = [
        'id'      => 'wp_cron_disabled',   // required, unique per registration
        'title'   => __( 'WP-Cron is disabled', 'my-plugin' ),
        'message' => __( 'Scheduled tasks will not run until you configure a real system cron to hit wp-cron.php.', 'my-plugin' ),
        'type'    => 'warning',            // error | warning | info | success (default: warning)
        'source'  => 'My Plugin',          // optional label shown on the notice card
        'action'  => [                     // optional CTA rendered as a purple button
            'label' => __( 'Read the docs', 'my-plugin' ),
            'url'   => 'https://developer.wordpress.org/plugins/cron/',
        ],
    ];
    return $notices;
} );
```
Later registrations of the same `id` are ignored (first-wins). Missing `id` or both `title` and `message` empty → the entry is dropped.

## [0.0.29] - 2026-07-31

### Changed
- **Active add-ons now render a non-clickable "● Running" pill** on the Add-ons page instead of a "Deactivate" button. The Add-ons page is a discovery surface, not a plugin manager — deactivation stays in **Plugins → Installed Plugins** where WP admins expect it. Green pill with a status dot; new CSS classes `.acrossai-addons__status` / `.acrossai-addons__status--active` / `.acrossai-addons__status-dot`.
- **Installed non-`wordpress.org` add-ons now show an in-page Activate button** instead of always rendering the external "Get add-on ↗" link. Detection is source-agnostic and driven by `AddonsInstaller::find_plugin_file()`, so a paid/off-directory add-on that the admin uploaded via **Plugins → Add New → Upload Plugin** can be activated straight from the AcrossAI Add-ons page. The Install code path remains restricted to `wordpress.org` sources (guideline #8 — no change). Card behaviour per state:
  | source        | not installed         | installed, inactive | active           |
  |---------------|-----------------------|---------------------|------------------|
  | wordpress.org | **Install**           | **Activate**        | **● Running**    |
  | anything else | **Get add-on ↗**      | **Activate**        | **● Running**    |
- **`AI Connectors` baseline entry declares `install_folder => 'acrossai-ai-connectors'`** so install detection matches the actual plugin folder even though the registry slug (`ai-connectors`) differs. Serves as the canonical example for consumers whose extracted folder ≠ slug (the `install_folder` field already existed in `AddonsInstaller::find_plugin_file()`; this is its first baseline use).

### Added
- **`AddonsAjaxHandlers::activate` is now the primary hand-off point for non-wp.org add-ons** — no code changes needed (the handler was already source-agnostic, only checking `find_plugin_file()`), but this release makes it a documented public surface via the new render decision path in `AddonsPageRenderer::render_card()`.

### Removed
- Nothing removed from the public API. The old "Deactivate" button in `button_state_for()` is replaced by a `running` state (same array shape: `action`, `label`, `css_class`); consumers who read `button_state_for()` should treat `'running'` as a display-only sentinel and NOT wire it to an AJAX handler (there's no corresponding `wp_ajax_acrossai_addons_running` endpoint).

## [0.0.28] - 2026-07-31

### Changed
- **Add-ons page — refreshed the baseline catalogue.** The hard-coded list now ships three entries: **AcrossAI Abilities Manager** (wp.org install), **AcrossAI MCP Manager** (wp.org install), and **AI Connectors** (external "Get add-on ↗" link to `acrossai.co/ai-connectors/#pricing`). Removed the AcrossAI Model Manager and Turn Off AI Features entries from the baseline — consumers can still register them via the `acrossai_addons` filter.
- **Shared brand icon for baseline cards.** All three baseline entries now render the AcrossAI SVG logo (`acrossai.co/wp-content/uploads/2026/07/acrossai-logo-2.svg`) instead of per-plugin `ps.w.org` PNGs, so the page reads as one product surface.
- **Icon fit switched from `cover` to `contain`** (with 6px padding) so wide/horizontal SVG logos render fully instead of being cropped inside the 56×56 icon box.
- **Grid is now fixed at 3 columns** (`repeat(3, minmax(0, 1fr))`) instead of `auto-fill` at a 320px min-width. Cards keep a consistent one-third width regardless of viewport width, so the layout is predictable as the catalogue grows past three entries. Responsive fallbacks: 2 columns under 1100px, 1 column under 720px.

### Added
- **`learn_more_url` add-on field** — optional URL rendered as a "Learn more" text link inside `.acrossai-addons__actions`. Applies to every add-on regardless of `source`, so external-CTA cards can point at a marketing/docs page separate from the CTA target. Replaces the previous wp.org-only "More info" link.

## [0.0.27] - 2026-07-27

### Changed
- **Add-ons page — install path is now WordPress.org-only.** Cards whose `source` is `wordpress.org` continue to render an in-page Install / Activate / Deactivate button (unchanged behaviour). Cards with any other `source` (e.g. `github`, `freemius`, or any consumer-defined value) now render an external **"Get add-on ↗"** link that opens the entry's `more_url` in a new tab — users install those add-ons via WP admin's standard **Plugins → Add New → Upload Plugin** flow, or via the vendor's own installer.
- Rejects non-`wordpress.org` sources server-side in `AddonsInstaller::install()` and in the `wp_ajax_acrossai_addons_install` handler as defense-in-depth, so a crafted POST cannot drive an install from any other source.

### Added
- `AddonsInstaller::is_installable_source( array $addon ): bool` — public helper that returns `true` only when `$addon['source'] === 'wordpress.org'`. Consumers can use this to mirror the in-page behaviour in their own UI.
- Class docblocks on `AddonsInstaller` and `AddonsPageRenderer` document the split and the WordPress.org guideline #8 rationale.

### Rationale
WordPress.org detailed plugin guideline #8 forbids "installing plugins/themes/add-ons from non-WordPress.org servers" for plugins distributed via the WordPress.org plugin directory. Restricting the install code path to `wordpress.org` add-ons keeps this package compliant when it ships inside a wp.org-hosted plugin. Non-wp.org add-ons remain fully discoverable on the page — they just link out instead of installing in place, matching the pattern used by WooCommerce and GiveWP.

## [0.0.26] - 2026-07-27

### Changed
- **Consultations page — replaced the Calendly iframe with an external-link CTA.** The submenu at `?page=acrossai-consultations` now renders a self-contained call-to-action page (Space Grotesk headline, purple accent, dotted radial background) that opens `calendly.com/acrossai/using-ai-in-wordpress` in a new browser tab when the admin clicks the button. No Calendly script, iframe, cookie, or asset is loaded inside wp-admin any more. Complies with WordPress.org detailed plugin guideline #8 ("using iframes for admin pages" is prohibited).

## [0.0.25] - 2026-07-27

### Changed
- Redesigned the Consultations page to match the AcrossAI dashboard aesthetic: two-column layout with the intro/topics on the left and a sticky Calendly card on the right. Adds a headline ("A free 30-minute, one-on-one call about using AI in WordPress"), a lede, a topic bullet list, and a dark host card with Deepak Gupta's live headshot pulled from `acrossai.co`. Fully self-contained styles scoped under `.acai-consult`.

## [0.0.24] - 2026-07-25

### Added
- **Consultations** submenu under the AcrossAI parent menu. Embeds the Calendly booking widget so admins can schedule "Using AI in WordPress" consultations without leaving WP Admin. Registered at `admin_menu` priority 1010 so it lands after Add-ons. New slug: `acrossai-consultations`; new class: `ConsultationsPageRenderer`; new constant: `SettingsPage::CONSULTATIONS_SLUG`.

## [0.0.23] - 2026-07-17

### Fixed
- Reordered submenus so Settings lands right after the Dashboard (priority 20) and Add-ons lands last (priority 1000).

## [0.0.22] - 2026-07-17

### Added
- Abilities Manager entry in the Add-ons list and reordered the list.

## [0.0.21] - 2026-07-17

### Added
- Add-ons install/activate/deactivate actions.
- `acrossai_addons` filter so consumers can register their own add-ons.
- Card styling for the Add-ons page.

## [0.0.20] - 2026-07-17

### Added
- Simple Add-ons page seeded from a hard-coded array.
- Add-ons submenu following the `SettingsPage` / `MenuRegistrar` pattern.

### Changed
- Removed Freemius integration and the legacy Add-ons page (net effect after a chain of revert/restore commits).

## [0.0.19] - 2026-07-17

### Added
- Parent-addon signal and `PluginFileLocator`; Add-ons/Settings refinements.

## [0.0.18] - 2026-07-13

### Added
- Expose `fs_has_addons` on AddonsPage `$args` to unblock the Freemius Add-ons row.

## [0.0.17] - 2026-07-13

### Changed
- Disabled Add-ons submenu registration.

## [0.0.16] - 2026-07-12

### Added
- Let consumers override the Freemius `menu` config via the `fs_menu` filter.

## [0.0.15] - 2026-07-12

### Added
- Enable Freemius Account, Contact Us, and wp.org Support Forum submenus.

## [0.0.14] - 2026-07-08

### Added
- Extracted `Tabs` base class so tab bars work outside a Settings page (custom admin screens, meta boxes, dashboard widgets).

## [0.0.13] - 2026-07-08

### Fixed
- **Breaking:** Tab-scoped `option_group` in `TabbedPageRenderer` fixes the cross-tab option-clobber bug (saving one tab silently wiped other tabs' options). Consumer plugins using the shared `'acrossai-settings'` option group in tabbed mode must migrate to `$renderer->tab_page_slug( 'your-tab' )`. See README's "Migrating from 0.0.12" section.

## [0.0.12] - 2026-07-08

### Added
- Extracted `TabbedPageRenderer` base class so other pages can reuse the tabs implementation.

## [0.0.11] - 2026-07-04

### Changed
- Resolve add-on icons from local `.wordpress-org/` as data URIs.

## [0.0.10] - 2026-07-02

### Fixed
- Sync addons-page JS with the `acrossai-*` rebrand of the data attribute.

## [0.0.9] - 2026-07-01

### Fixed
- Fix stale path math left over from the `src/` → `src/Addons/` move.

## [0.0.8] - 2026-07-01

### Removed
- Abilities/MCP/Model manager submenu registrations (feature plugins now register their own submenus).

## [0.0.7] - 2026-06-30

### Changed
- Rename addons-page `wpb-*` prefixes to `acrossai-addons-*`.

## [0.0.6] - 2026-06-30

### Added
- Merged the `addons-page` package into `main-menu` and bundled the Add-ons submenu.

## [0.0.5] - 2026-06-30

### Added
- Abilities/MCP/Model manager submenus and reordered dashboard.

## [0.0.4] - 2026-06-30

### Added
- Tab system on the shared Settings page.

## [0.0.3] - 2026-06-30

### Added
- AcrossAI dashboard landing page on the parent menu.

## [0.0.2] - 2026-06-26

### Changed
- Switched the Settings page from a React SlotFill to the WordPress Settings API.

## [0.0.1] - 2026-06-26

### Added
- Initial release: `AcrossAI` parent menu and shared Settings page.

[0.0.29]: https://github.com/acrossai-co/main-menu/compare/0.0.28...0.0.29
[0.0.28]: https://github.com/acrossai-co/main-menu/compare/0.0.27...0.0.28
[0.0.27]: https://github.com/acrossai-co/main-menu/compare/0.0.26...0.0.27
[0.0.26]: https://github.com/acrossai-co/main-menu/compare/0.0.25...0.0.26
[0.0.25]: https://github.com/acrossai-co/main-menu/compare/0.0.24...0.0.25
[0.0.24]: https://github.com/acrossai-co/main-menu/compare/0.0.23...0.0.24
[0.0.23]: https://github.com/acrossai-co/main-menu/compare/0.0.22...0.0.23
[0.0.22]: https://github.com/acrossai-co/main-menu/compare/0.0.21...0.0.22
[0.0.21]: https://github.com/acrossai-co/main-menu/compare/0.0.20...0.0.21
[0.0.20]: https://github.com/acrossai-co/main-menu/compare/0.0.19...0.0.20
[0.0.19]: https://github.com/acrossai-co/main-menu/compare/0.0.18...0.0.19
[0.0.18]: https://github.com/acrossai-co/main-menu/compare/0.0.17...0.0.18
[0.0.17]: https://github.com/acrossai-co/main-menu/compare/0.0.16...0.0.17
[0.0.16]: https://github.com/acrossai-co/main-menu/compare/0.0.15...0.0.16
[0.0.15]: https://github.com/acrossai-co/main-menu/compare/0.0.14...0.0.15
[0.0.14]: https://github.com/acrossai-co/main-menu/compare/0.0.13...0.0.14
[0.0.13]: https://github.com/acrossai-co/main-menu/compare/0.0.12...0.0.13
[0.0.12]: https://github.com/acrossai-co/main-menu/compare/0.0.11...0.0.12
[0.0.11]: https://github.com/acrossai-co/main-menu/compare/0.0.10...0.0.11
[0.0.10]: https://github.com/acrossai-co/main-menu/compare/0.0.9...0.0.10
[0.0.9]: https://github.com/acrossai-co/main-menu/compare/0.0.8...0.0.9
[0.0.8]: https://github.com/acrossai-co/main-menu/compare/0.0.7...0.0.8
[0.0.7]: https://github.com/acrossai-co/main-menu/compare/0.0.6...0.0.7
[0.0.6]: https://github.com/acrossai-co/main-menu/compare/0.0.5...0.0.6
[0.0.5]: https://github.com/acrossai-co/main-menu/compare/0.0.4...0.0.5
[0.0.4]: https://github.com/acrossai-co/main-menu/compare/0.0.3...0.0.4
[0.0.3]: https://github.com/acrossai-co/main-menu/compare/0.0.2...0.0.3
[0.0.2]: https://github.com/acrossai-co/main-menu/compare/0.0.1...0.0.2
[0.0.1]: https://github.com/acrossai-co/main-menu/releases/tag/0.0.1

# Changelog

All notable changes to `acrossai-co/main-menu` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

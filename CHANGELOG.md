# Changelog

All notable changes to this project should be documented in this file.

The format is intentionally simple so it works well for internal release prep, client delivery, and marketplace packaging.

## [Unreleased]

### Planned

- Translation asset generation for `languages/`
- Marketplace artwork and product screenshots
- Final live QA against a production-like WordPress stack

## [1.1.4] - 2026-04-08

### Added

- Dashboard action to remove only generated premade frontend pages before recreating them

### Changed

- Improved premade page width handling for plugin-driven WordPress pages
- Updated generated premade page markup defaults to work better with constrained block themes
- Removed leftover legacy theme divider lines from plugin-driven event pages

## [1.1.3] - 2026-04-08

### Added

- Editable block-based premade frontend pages for generated Events and category landing pages
- Snapshot-based protection that preserves client-edited generated pages on reruns whenever the original generated layout is still intact
- Optional shortcode header suppression for category landing pages created through the premade page generator

### Changed

- Refreshed frontend styling for premade page hero sections, CTA buttons, and highlight cards
- Updated dashboard success messaging to reflect the new editable-page workflow

## [1.0.0] - 2026-04-08

### Added

- Admin-controlled frontend design settings for plugin-managed pages
- Public repo support files with `README.md` and `.gitignore`
- Runtime token and font stack extension hooks for child builds and branded add-ons

### Changed

- Improved archive info-band layout for a cleaner three-column presentation
- Improved single-event agenda, sponsor, and FAQ spacing and visual polish
- Improved shortcode landing-page title handling to prevent duplicate headings

## [1.0.0] - 2026-04-08

### Added

- Initial commercial release packaging for Synbus Inc.
- WooCommerce-powered event commerce, attendee issuance, and refund-aware ticket invalidation
- Premium admin dashboard, event templates, customer ticket dashboard, PDF tickets, and check-in workflow
- REST, shortcode, and block foundations for frontend and operational flows

### Notes

- Verify WordPress and WooCommerce compatibility before each tagged release.
- Update this changelog alongside `readme.txt` when shipping fixes or features.

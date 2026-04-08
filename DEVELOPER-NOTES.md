# Developer Notes

## Architecture

The plugin is organized around service-style classes rather than monolithic controllers:

- `includes/` contains domain logic, WooCommerce integration, permissions, and lifecycle code
- `admin/` contains WordPress admin UX and operational views
- `public/` contains frontend delivery logic and customer-facing templates
- `templates/` contains archive, single, and My Account ticket templates

## Extension Points

Primary extension points currently include:

- `eventpro_tickets_plus_settings_defaults`
- `eventpro_tickets_plus_event_meta_defaults`
- `eventpro_tickets_plus_plugin_components`
- `eventpro_tickets_plus_before_run`
- `eventpro_tickets_plus_after_run`
- `eventpro_tickets_plus_ticket_tiers`
- `eventpro_tickets_plus_available_tickets`
- `eventpro_tickets_plus_event_payload`
- `eventpro_tickets_plus_attendees_issued`
- `eventpro_tickets_plus_qr_svg`
- `eventpro_tickets_plus_ticket_email_subject`
- `eventpro_tickets_plus_ticket_email_body`
- `eventpro_tickets_plus_ticket_email_headers`
- `eventpro_tickets_plus_ticket_download_url`
- `eventpro_tickets_plus_calendar_download_url`
- `eventpro_tickets_plus_calendar_download_body`
- `eventpro_tickets_plus_schema_data`
- `eventpro_tickets_plus_waitlist_created`
- `eventpro_tickets_plus_font_stack_map`
- `eventpro_tickets_plus_runtime_tokens_css`

## Settings Defaults

The plugin seeds opinionated defaults through `eptp_get_settings_defaults()` and stores them in the `eptp_settings` option on activation.

Commercially relevant defaults currently cover:

- branding colors and email identity
- plugin-only frontend typography and color controls
- shell widths, spacing, radius, and grid columns
- uninstall cleanup behavior
- WooCommerce My Account ticket endpoint configuration

These defaults can be filtered through `eventpro_tickets_plus_settings_defaults` before plugin initialization completes.

## Commercial Release Checklist

- Confirm WooCommerce activation and HPOS compatibility on the target version set
- Generate translation files into `languages/`
- Update `readme.txt` and `CHANGELOG.md`
- Review `README.md` before publishing to a public Git hosting service
- Review branding, support URLs, and legal text
- Run manual purchase, refund, ticket download, and check-in scenarios
- Rebuild the distributable ZIP after any last-minute doc or asset change

## Packaging Notes

- Ship the plugin as the `eventpro-tickets-plus` directory
- Keep test and internal documentation files only if your distribution channel allows them
- If packaging for a marketplace, add icons, banners, and compiled translation assets as required
- Exclude generated ZIP archives from version control; `.gitignore` is included for repo hygiene

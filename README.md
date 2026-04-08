# EventPro Tickets Plus for WooCommerce

Premium WooCommerce-powered event management, ticketing, attendee operations, PDF tickets, and staff check-in for WordPress.

## Highlights

- WooCommerce-native checkout, payments, coupons, taxes, and order lifecycle
- Event, venue, organizer, speaker, attendee, and waitlist content models
- QR-ready tickets, PDF downloads, and staff check-in workflow
- Premium archive, single-event, category, and My Account ticket templates
- Admin-controlled frontend design settings for premade plugin pages
- Extensible service architecture with filters and actions for commercial projects

## Requirements

- WordPress 6.4+
- PHP 8.1+
- WooCommerce 8.0+

## Installation

1. Copy `eventpro-tickets-plus/` into `wp-content/plugins/`.
2. Activate WooCommerce.
3. Activate EventPro Tickets Plus for WooCommerce.
4. Configure branding and frontend design controls under `EventPro Tickets > Settings`.

## Shortcodes

Use these shortcodes on normal WordPress pages, posts, or builder text/shortcode modules:

- `[eventpro_events]`
- `[eventpro_event_categories]`
- `[eventpro_ticket_dashboard]`

Examples:

```text
[eventpro_events posts_per_page="6" title="Upcoming Events" description="Browse featured event experiences."]
[eventpro_events category="business-events" posts_per_page="6" title="Business Events"]
[eventpro_event_categories title="Explore Event Categories" description="Browse event collections." show_header="yes"]
[eventpro_ticket_dashboard]
```

Supported shortcode attributes:

- `eventpro_events`: `posts_per_page`, `category`, `title`, `description`
- `eventpro_event_categories`: `title`, `description`, `limit`, `parent`, `show_header`

## Premade Page Workflow

The admin dashboard can generate starter frontend pages for:

- Events
- Event Categories
- Business Events
- Workshops
- Community Events

These premade pages are normal WordPress pages. The surrounding layout content is editable in Gutenberg, while the event/category listing areas remain dynamic through shortcode blocks.

## Builder Compatibility

The plugin is currently best integrated with Gutenberg and shortcode-friendly builders.

- Elementor: supported today through shortcode widgets, HTML widgets, and standard WordPress pages
- Divi: supported today through Code or Text modules that render plugin shortcodes

The architecture is ready for a future builder upgrade path with:

- dedicated Elementor widgets
- dedicated Divi modules
- builder-aware controls for archive cards, category cards, and ticket panels

This means the current release is builder-friendly, and a later commercial enhancement can add deeper native widget/module integrations without replacing the core event and WooCommerce logic.

## Commercial Packaging Notes

- Plugin readme for WordPress-style distribution: `readme.txt`
- Release history: `CHANGELOG.md`
- Extension and architecture notes: `DEVELOPER-NOTES.md`
- Manual QA checklist: `tests/TESTING.md`

## Support

- Product site: https://www.synbus.ph/
- Author: Iresh Rangana

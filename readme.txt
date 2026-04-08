=== EventPro Tickets Plus for WooCommerce ===
Contributors: synbusinc
Tags: events, tickets, woocommerce, attendees, qr-code
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium event management, WooCommerce-native ticketing, attendee operations, PDF tickets, and staff check-in for WordPress by Synbus Inc.

== Description ==

EventPro Tickets Plus for WooCommerce is a premium-ready event and ticketing platform designed to use WooCommerce as the commerce engine while elevating event UX across admin and frontend experiences.

Product website: https://www.synbus.ph/

Core highlights:

* Event, venue, organizer, speaker, attendee, and waitlist content models
* Ticket tier builder mapped to WooCommerce products
* Payment-state-aware attendee issuance
* Refund-aware ticket invalidation logic
* Staff check-in console backed by a dedicated log table
* QR payload architecture and printable PDF ticket downloads
* Event archive and single templates with premium styling
* Shortcodes, dynamic blocks, REST API, and My Account ticket dashboard
* Translation-ready strings, capability checks, and security-first data handling

Commercial release notes:

* Built for WordPress 6.4+ and PHP 8.1+
* Uses WooCommerce for checkout, tax, payments, coupons, and order lifecycle
* Architected for premium add-ons such as recurring events, seat maps, badge printing, and organizer commissions
* Includes extensibility hooks for ticket issuance, event payloads, waitlist handling, customer delivery flows, and runtime design tokens
* Includes admin-controlled frontend design settings for premade plugin pages only

== Features ==

* Premium event archive and single-event templates
* Ticket tiers linked to WooCommerce products
* QR-ready attendee tickets and PDF downloads
* Staff check-in workflow with validation logging
* Waitlist collection and attendee CSV export
* Structured event builder for agenda, sponsors, FAQs, and presenters
* Customer ticket dashboard inside WooCommerce My Account
* Developer-friendly architecture with service classes and extensibility hooks

== Installation ==

1. Upload the `eventpro-tickets-plus` folder to `/wp-content/plugins/`.
2. Activate WooCommerce.
3. Activate EventPro Tickets Plus for WooCommerce.
4. Create WooCommerce products for ticket tiers and assign them to events.

== Upgrade Notice ==

= 1.1.4 =

Added dashboard cleanup for premade frontend pages, improved premade page layout handling, and removed leftover legacy theme divider lines from plugin-driven event screens.

= 1.1.3 =

Editable premade frontend pages now use Gutenberg-based layouts, generated page reruns preserve client edits where possible, and supporting frontend styling has been refreshed for easier testing and overrides.

= 1.0.0 =

Initial public commercial release for Synbus Inc.

== Frequently Asked Questions ==

= Does this plugin process payments directly? =

No. Payments, taxes, coupons, order states, and gateway compatibility are handled through WooCommerce.

= How are tickets issued? =

Tickets are issued only when WooCommerce orders reach valid paid states such as `processing` or `completed`.

= Does it support refunds? =

Yes. Refunded, cancelled, and failed order states invalidate linked attendees.

= Is this plugin extensible for custom projects? =

Yes. The codebase includes hooks and filters for event payloads, settings defaults, attendee issuance, waitlist submissions, download URLs, outgoing ticket emails, font stack maps, and runtime design tokens.

== Developer Notes ==

Developers can review the bundled notes in `DEVELOPER-NOTES.md` for architecture guidance, extension points, and release packaging expectations.

== Changelog ==

= 1.1.4 =
* Added a dashboard action to remove only generated frontend pages and recreate them cleanly
* Improved premade page width handling for plugin-driven WordPress pages
* Removed visible legacy theme divider lines from event archive and related plugin pages

= 1.1.3 =
* Added editable block-based premade frontend pages for generated Events and category landing pages
* Preserved client-edited generated pages on reruns whenever the original generated snapshot is unchanged
* Added a category shortcode header toggle for cleaner page-level hero control
* Refreshed frontend styles for the new premade page cards, hero, and action layout

= 1.0.0 =
* Added admin-controlled frontend design settings for plugin-managed pages
* Improved single-event spacing for agenda, sponsors, and FAQ sections
* Refined archive information band layout and shortcode/page-title handling
* Added repo-friendly `README.md` and `.gitignore` for public distribution
* Expanded release notes and packaging guidance

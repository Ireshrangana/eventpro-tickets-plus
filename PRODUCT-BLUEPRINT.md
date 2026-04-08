# EventPro Tickets Plus for WooCommerce

## 1. Product Blueprint

EventPro Tickets Plus for WooCommerce is a premium event operations plugin that uses WooCommerce as the source of truth for catalog, cart, checkout, payment, tax, coupon, refund, and order lifecycle management. The plugin owns event modeling, attendee issuance, ticket presentation, staff check-in, analytics surfaces, and extensibility architecture for premium add-ons.

Key product principles:

- WooCommerce-native commerce and refund logic
- WordPress-native content architecture
- Service-oriented modularity
- Admin UX that feels like a SaaS product inside WordPress
- Frontend templates optimized for conversion and clarity
- Security-first form handling, REST, and downloads
- Add-on-ready extension points for recurring events, seats, SMS, wallet passes, and organizer commissions

## 2. Information Architecture

- Content models:
  - `eptp_event`
  - `eptp_venue`
  - `eptp_organizer`
  - `eptp_speaker`
  - `eptp_attendee`
  - `eptp_waitlist`
- Commerce layer:
  - WooCommerce simple or variable products mapped to ticket tiers
  - WooCommerce orders drive issuance and invalidation
- Experience layers:
  - Admin dashboard, event builder, attendee list, staff check-in, settings
  - Archive, single event, My Account tickets, shortcodes, blocks
- Service layers:
  - Event service
  - Ticket service
  - Order sync
  - Attendee service
  - QR service
  - PDF service
  - Check-in service
  - REST API

## 3. Folder Structure

See the live implementation in:

- [eventpro-tickets-plus/eventpro-tickets-plus.php](/Users/iresh/Documents/WP%20Project/WP3/eventpro-tickets-plus/eventpro-tickets-plus.php)
- [eventpro-tickets-plus/includes](/Users/iresh/Documents/WP%20Project/WP3/eventpro-tickets-plus/includes)
- [eventpro-tickets-plus/admin](/Users/iresh/Documents/WP%20Project/WP3/eventpro-tickets-plus/admin)
- [eventpro-tickets-plus/public](/Users/iresh/Documents/WP%20Project/WP3/eventpro-tickets-plus/public)
- [eventpro-tickets-plus/templates](/Users/iresh/Documents/WP%20Project/WP3/eventpro-tickets-plus/templates)

## 4. Database and Meta Strategy

- Events and supporting entities use custom post types
- Event builder data is stored in a normalized `_eptp_event_data` array
- WooCommerce products store event ticket mapping via product meta
- Attendees are stored as `eptp_attendee` posts for admin friendliness and extensibility
- High-frequency scan logs use the custom table `{$wpdb->prefix}eptp_checkin_logs`
- Plugin-wide experience settings live in the `eptp_settings` option

## 5. File Implementation Plan

- Bootstrap and dependency guards
- Capabilities and activation migrations
- Event content models and taxonomies
- Event builder meta UI
- Ticket and order sync domain services
- Staff check-in REST workflow
- Admin dashboard and settings
- Frontend templates and Woo account experience
- QR/PDF/download architecture
- Packaging, uninstall, and testing notes

## 6. Security Notes

- Capability checks guard every privileged admin and REST action
- Nonces protect event meta saving, CSV export, and PDF downloads
- Outputs are escaped and inputs are sanitized
- WooCommerce order status governs ticket validity
- Custom table writes use `$wpdb->insert()` with explicit formats

## 7. Packaging Notes

- Ship as the `eventpro-tickets-plus` directory
- Keep WooCommerce active before activation
- Generate translation files into `languages/`
- Replace placeholder vendor URIs before commercial release

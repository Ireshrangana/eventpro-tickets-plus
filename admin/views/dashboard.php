<?php
/**
 * Dashboard view.
 *
 * @var array<string,mixed> $stats
 * @var array<int,\WP_Post> $events
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap eptp-wrap">
	<div class="eptp-page-header">
		<div>
			<p class="eptp-kicker"><?php esc_html_e( 'Operations Hub', 'eventpro-tickets-plus' ); ?></p>
			<h1><?php esc_html_e( 'EventPro Tickets Plus', 'eventpro-tickets-plus' ); ?></h1>
			<p><?php esc_html_e( 'Premium event operations powered by WooCommerce checkout, payments, coupons, tax, and order states.', 'eventpro-tickets-plus' ); ?></p>
		</div>
		<div class="eptp-actions">
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=eptp_event' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Create Event', 'eventpro-tickets-plus' ); ?></a>
			<a href="<?php echo esc_url( $demo_import_url ); ?>" class="button"><?php esc_html_e( 'Create Demo Content', 'eventpro-tickets-plus' ); ?></a>
			<a href="<?php echo esc_url( $demo_pages_url ); ?>" class="button"><?php esc_html_e( 'Create Frontend Pages', 'eventpro-tickets-plus' ); ?></a>
			<a href="<?php echo esc_url( $demo_pages_delete_url ); ?>" class="button" onclick="return window.confirm('<?php echo esc_js( __( 'Remove only the generated premade frontend pages? Your events, products, and other demo content will stay untouched.', 'eventpro-tickets-plus' ) ); ?>');"><?php esc_html_e( 'Remove Frontend Pages', 'eventpro-tickets-plus' ); ?></a>
			<a href="<?php echo esc_url( $demo_delete_url ); ?>" class="button" onclick="return window.confirm('<?php echo esc_js( __( 'Remove all imported demo events, demo products, and demo profile content?', 'eventpro-tickets-plus' ) ); ?>');"><?php esc_html_e( 'Remove Demo Content', 'eventpro-tickets-plus' ); ?></a>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=eptp_export_attendees&event_id=0' ), 'eptp_export_attendees' ) ); ?>" class="button"><?php esc_html_e( 'Export All Attendees', 'eventpro-tickets-plus' ); ?></a>
			<a href="<?php echo esc_url( 'https://www.synbus.ph/' ); ?>" class="button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Synbus Inc.', 'eventpro-tickets-plus' ); ?></a>
		</div>
	</div>

	<div class="eptp-stats-grid">
		<div class="eptp-stat-card"><span><?php esc_html_e( 'Published Events', 'eventpro-tickets-plus' ); ?></span><strong><?php echo esc_html( (string) $stats['events'] ); ?></strong><small><?php esc_html_e( 'Live and upcoming experiences', 'eventpro-tickets-plus' ); ?></small></div>
		<div class="eptp-stat-card"><span><?php esc_html_e( 'Issued Attendees', 'eventpro-tickets-plus' ); ?></span><strong><?php echo esc_html( (string) $stats['attendees'] ); ?></strong><small><?php esc_html_e( 'Valid customer passes created', 'eventpro-tickets-plus' ); ?></small></div>
		<div class="eptp-stat-card"><span><?php esc_html_e( 'Woo Orders', 'eventpro-tickets-plus' ); ?></span><strong><?php echo esc_html( (string) $stats['orders'] ); ?></strong><small><?php esc_html_e( 'Commerce records linked to events', 'eventpro-tickets-plus' ); ?></small></div>
		<div class="eptp-stat-card"><span><?php esc_html_e( 'Gross Revenue', 'eventpro-tickets-plus' ); ?></span><strong><?php echo wp_kses_post( $stats['revenue'] ); ?></strong><small><?php esc_html_e( 'Processing and completed order totals', 'eventpro-tickets-plus' ); ?></small></div>
		<div class="eptp-stat-card"><span><?php esc_html_e( 'Check-Ins Today', 'eventpro-tickets-plus' ); ?></span><strong><?php echo esc_html( (string) $stats['checkins_today'] ); ?></strong><small><?php esc_html_e( 'Real-time entry activity', 'eventpro-tickets-plus' ); ?></small></div>
	</div>

	<div class="eptp-dashboard-grid">
		<section class="eptp-card">
			<h2><?php esc_html_e( 'Upcoming Event Pipeline', 'eventpro-tickets-plus' ); ?></h2>
			<?php if ( $events ) : ?>
				<ul class="eptp-event-list">
					<?php foreach ( $events as $event ) : ?>
						<?php $meta = eptp_get_event_meta( $event->ID ); ?>
						<li>
							<div>
								<strong><?php echo esc_html( get_the_title( $event->ID ) ); ?></strong>
								<span><?php echo esc_html( eptp_format_datetime( (string) $meta['start_date'] ) ); ?></span>
							</div>
							<a href="<?php echo esc_url( get_edit_post_link( $event->ID, '' ) ); ?>"><?php esc_html_e( 'Manage', 'eventpro-tickets-plus' ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No events yet. Create your first premium ticketed experience to start selling.', 'eventpro-tickets-plus' ); ?></p>
			<?php endif; ?>
		</section>
		<section class="eptp-card">
			<h2><?php esc_html_e( 'Commerce Architecture Highlights', 'eventpro-tickets-plus' ); ?></h2>
			<ul class="eptp-feature-list">
				<li><?php esc_html_e( 'Payment-state-aware ticket issuance via WooCommerce order transitions.', 'eventpro-tickets-plus' ); ?></li>
				<li><?php esc_html_e( 'Attendee records, QR payloads, PDF downloads, and staff check-in logs.', 'eventpro-tickets-plus' ); ?></li>
				<li><?php esc_html_e( 'Premium-ready layers for recurring events, commission logic, wallet passes, and seat map providers.', 'eventpro-tickets-plus' ); ?></li>
				<li><?php esc_html_e( 'Translation-ready UI, capability-gated actions, and lazy-loaded assets.', 'eventpro-tickets-plus' ); ?></li>
			</ul>
		</section>
	</div>

	<section class="eptp-insight-strip">
		<div class="eptp-insight-card">
			<h3><?php esc_html_e( 'Check-In Workflow', 'eventpro-tickets-plus' ); ?></h3>
			<p><?php esc_html_e( 'Search by ticket code, order number, or attendee email, validate in real time, and keep a clean audit log.', 'eventpro-tickets-plus' ); ?></p>
		</div>
		<div class="eptp-insight-card">
			<h3><?php esc_html_e( 'Customer Experience', 'eventpro-tickets-plus' ); ?></h3>
			<p><?php esc_html_e( 'Drive conversion with polished event cards, modern ticket blocks, downloadable PDFs, QR display, and account-based access.', 'eventpro-tickets-plus' ); ?></p>
		</div>
		<div class="eptp-insight-card">
			<h3><?php esc_html_e( 'Demo Content', 'eventpro-tickets-plus' ); ?></h3>
			<p><?php esc_html_e( 'Use the demo importer to generate three sample events, matching demo visuals, venues, speakers, and WooCommerce ticket products. Everything stays editable and can be removed later with one click.', 'eventpro-tickets-plus' ); ?></p>
		</div>
		<div class="eptp-insight-card">
			<h3><?php esc_html_e( 'Frontend Pages', 'eventpro-tickets-plus' ); ?></h3>
			<p><?php esc_html_e( 'Generate premade WordPress pages for Events, Event Categories, Business Events, Workshops, and Community Events with one click from the dashboard.', 'eventpro-tickets-plus' ); ?></p>
		</div>
	</section>

	<p class="eptp-admin-footer"><?php printf( esc_html__( 'Built and branded by %s.', 'eventpro-tickets-plus' ), 'Synbus Inc.' ); ?> <a href="<?php echo esc_url( 'https://www.synbus.ph/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'www.synbus.ph', 'eventpro-tickets-plus' ); ?></a></p>
</div>

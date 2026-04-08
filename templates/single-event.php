<?php
/**
 * Single event template.
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

$event_service = new \EventPro\TicketsPlus\Event_Service();
$ticket_service = new \EventPro\TicketsPlus\Ticket_Service();
$event = $event_service->get_event( get_the_ID() );
$tiers = $ticket_service->get_ticket_tiers( get_the_ID() );
$primary_category = ! empty( $event['categories'] ) && isset( $event['categories'][0]->name ) ? (string) $event['categories'][0]->name : '';
$hero_class = sanitize_html_class( (string) ( $event['meta']['ui_style'] ?? 'summit' ) );

$calendar_link = add_query_arg(
	array(
		'eptp_download_ics' => get_the_ID(),
		'_wpnonce'          => wp_create_nonce( 'eptp_download_ics_' . get_the_ID() ),
	),
	home_url( '/' )
);

get_header();
?>
<main class="eptp-single-shell">
	<?php eptp_render_site_header(); ?>
	<section class="eptp-hero eptp-hero--single eptp-hero--clean eptp-hero--<?php echo esc_attr( $hero_class ); ?>">
		<div class="eptp-hero__content eptp-hero__content--wide">
			<p class="eptp-section-label"><?php esc_html_e( 'Featured Event Experience', 'eventpro-tickets-plus' ); ?></p>
			<div class="eptp-chip-row">
				<?php if ( $primary_category ) : ?>
					<span class="eptp-chip"><?php echo esc_html( $primary_category ); ?></span>
				<?php endif; ?>
				<span class="eptp-chip"><?php echo esc_html( $event['status_badge'] ); ?></span>
				<?php if ( ! empty( $event['meta']['hero_badge'] ) ) : ?>
					<span class="eptp-chip eptp-chip--accent"><?php echo esc_html( $event['meta']['hero_badge'] ); ?></span>
				<?php endif; ?>
			</div>
			<h1><?php echo esc_html( $event['title'] ); ?></h1>
			<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<div class="eptp-hero-meta">
				<span><?php echo esc_html( eptp_format_datetime( (string) $event['meta']['start_date'] ) ); ?></span>
				<span><?php echo esc_html( ! empty( $event['venue']['title'] ) ? $event['venue']['title'] : ucfirst( (string) $event['meta']['event_mode'] ) ); ?></span>
				<span><?php echo esc_html( ucfirst( (string) $event['meta']['event_type'] ) ); ?></span>
			</div>
			<div class="eptp-hero-actions">
				<a href="#eptp-buy-panel" class="eptp-button eptp-button--inline"><?php esc_html_e( 'Browse ticket options', 'eventpro-tickets-plus' ); ?></a>
				<a href="#eptp-event-content" class="eptp-link-button"><?php esc_html_e( 'Explore event details', 'eventpro-tickets-plus' ); ?></a>
				<a href="<?php echo esc_url( $calendar_link ); ?>" class="eptp-link-button"><?php esc_html_e( 'Add to calendar', 'eventpro-tickets-plus' ); ?></a>
			</div>
			<div class="eptp-event-highlight-row">
				<div class="eptp-event-highlight-card">
					<span><?php esc_html_e( 'Format', 'eventpro-tickets-plus' ); ?></span>
					<strong><?php echo esc_html( ucfirst( str_replace( '-', ' ', (string) $event['meta']['event_type'] ) ) ); ?></strong>
				</div>
				<div class="eptp-event-highlight-card">
					<span><?php esc_html_e( 'Attendance', 'eventpro-tickets-plus' ); ?></span>
					<strong><?php echo esc_html( ucfirst( (string) $event['meta']['event_mode'] ) ); ?></strong>
				</div>
				<div class="eptp-event-highlight-card">
					<span><?php esc_html_e( 'Venue', 'eventpro-tickets-plus' ); ?></span>
					<strong><?php echo esc_html( ! empty( $event['venue']['title'] ) ? $event['venue']['title'] : __( 'To be announced', 'eventpro-tickets-plus' ) ); ?></strong>
				</div>
			</div>
		</div>
	</section>

	<section class="eptp-ticket-band" id="eptp-buy-panel">
		<div class="eptp-ticket-band__intro">
			<div class="eptp-ticket-band__copy">
				<p class="eptp-section-label"><?php esc_html_e( 'Ticketing', 'eventpro-tickets-plus' ); ?></p>
				<h2><?php esc_html_e( 'Reserve your spot', 'eventpro-tickets-plus' ); ?></h2>
				<p><?php esc_html_e( 'Choose a ticket tier below and complete checkout with WooCommerce. Taxes, coupons, payment gateways, and refund-aware ticket validity all stay inside a familiar commerce flow.', 'eventpro-tickets-plus' ); ?></p>
			</div>
			<div class="eptp-ticket-band__side">
				<span class="eptp-capacity-pill">
					<?php echo esc_html( PHP_INT_MAX === $event['available_tickets'] ? __( 'Open capacity', 'eventpro-tickets-plus' ) : sprintf( _n( '%d seat available', '%d seats available', (int) $event['available_tickets'], 'eventpro-tickets-plus' ), (int) $event['available_tickets'] ) ); ?>
				</span>
				<div class="eptp-ticket-band__signals">
					<span><?php esc_html_e( 'WooCommerce checkout', 'eventpro-tickets-plus' ); ?></span>
					<span><?php esc_html_e( 'PDF and QR ready', 'eventpro-tickets-plus' ); ?></span>
					<span><?php esc_html_e( 'Refund-aware validity', 'eventpro-tickets-plus' ); ?></span>
				</div>
			</div>
		</div>

		<div class="eptp-ticket-band__trust">
			<div class="eptp-purchase-trust__item">
				<strong><?php esc_html_e( 'Secure checkout', 'eventpro-tickets-plus' ); ?></strong>
				<span><?php esc_html_e( 'Taxes, payments, coupons, and order records stay inside WooCommerce-native flows.', 'eventpro-tickets-plus' ); ?></span>
			</div>
			<div class="eptp-purchase-trust__item">
				<strong><?php esc_html_e( 'Instant delivery', 'eventpro-tickets-plus' ); ?></strong>
				<span><?php esc_html_e( 'Customers receive account access, PDF downloads, and check-in-ready attendee records after valid payment states.', 'eventpro-tickets-plus' ); ?></span>
			</div>
		</div>

		<?php if ( $tiers ) : ?>
			<div class="eptp-ticket-band__grid">
				<?php foreach ( $tiers as $tier ) : ?>
					<article class="eptp-ticket-tier eptp-ticket-tier--band">
						<div class="eptp-ticket-tier__content">
							<div class="eptp-ticket-tier__top">
								<strong><?php echo esc_html( $tier['label'] ); ?></strong>
								<span class="eptp-chip"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $tier['type'] ) ) ); ?></span>
								<?php if ( ! empty( $tier['badge'] ) ) : ?>
									<span class="eptp-chip eptp-chip--accent"><?php echo esc_html( $tier['badge'] ); ?></span>
								<?php endif; ?>
							</div>
							<p><?php echo esc_html( ! empty( $tier['description'] ) ? $tier['description'] : __( 'Secure WooCommerce checkout with native stock, taxes, and coupon support.', 'eventpro-tickets-plus' ) ); ?></p>
							<div class="eptp-meta-line">
								<span><?php echo esc_html( $tier['price_label'] ); ?></span>
								<span><?php echo esc_html( sprintf( _n( 'Up to %d ticket', 'Up to %d tickets', (int) $tier['max_qty'], 'eventpro-tickets-plus' ), (int) $tier['max_qty'] ) ); ?></span>
								<?php if ( null !== $tier['stock_qty'] && '' !== (string) $tier['stock_qty'] ) : ?>
									<span><?php echo esc_html( sprintf( _n( '%d left in stock', '%d left in stock', (int) $tier['stock_qty'], 'eventpro-tickets-plus' ), (int) $tier['stock_qty'] ) ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<div class="eptp-ticket-tier__aside">
							<div class="eptp-ticket-tier__price"><?php echo wp_kses_post( $tier['price_html'] ); ?></div>
							<form method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
								<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( (string) $tier['product_id'] ); ?>">
								<input type="hidden" name="quantity" value="1">
								<button type="submit" class="eptp-button"><?php esc_html_e( 'Add to cart', 'eventpro-tickets-plus' ); ?></button>
							</form>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="eptp-empty-state">
				<p><?php esc_html_e( 'No linked ticket products yet. Assign WooCommerce products inside the event ticket builder.', 'eventpro-tickets-plus' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( 0 === $event['available_tickets'] && 'yes' === $event['meta']['waitlist_enabled'] ) : ?>
			<form class="eptp-waitlist">
				<input type="hidden" name="event_id" value="<?php echo esc_attr( (string) get_the_ID() ); ?>">
				<input type="text" name="name" placeholder="<?php esc_attr_e( 'Your name', 'eventpro-tickets-plus' ); ?>">
				<input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'eventpro-tickets-plus' ); ?>" required>
				<button type="submit"><?php esc_html_e( 'Join waitlist', 'eventpro-tickets-plus' ); ?></button>
				<p class="eptp-inline-note"><?php esc_html_e( 'We will notify you if availability opens up.', 'eventpro-tickets-plus' ); ?></p>
			</form>
		<?php endif; ?>
	</section>

	<div class="eptp-single-nav">
		<a href="#eptp-event-content"><?php esc_html_e( 'Overview', 'eventpro-tickets-plus' ); ?></a>
		<?php if ( ! empty( $event['meta']['agenda'] ) ) : ?><a href="#eptp-event-agenda"><?php esc_html_e( 'Agenda', 'eventpro-tickets-plus' ); ?></a><?php endif; ?>
		<?php if ( ! empty( $event['speakers'] ) ) : ?><a href="#eptp-event-speakers"><?php esc_html_e( 'Speakers', 'eventpro-tickets-plus' ); ?></a><?php endif; ?>
		<?php if ( ! empty( $event['meta']['faq_items'] ) ) : ?><a href="#eptp-event-faq"><?php esc_html_e( 'FAQs', 'eventpro-tickets-plus' ); ?></a><?php endif; ?>
		<?php if ( ! empty( $event['venue'] ) ) : ?><a href="#eptp-event-venue"><?php esc_html_e( 'Venue', 'eventpro-tickets-plus' ); ?></a><?php endif; ?>
	</div>

	<div class="eptp-grid-two" id="eptp-event-content">
		<div>
			<section class="eptp-section">
				<p class="eptp-section-label"><?php esc_html_e( 'Section 01', 'eventpro-tickets-plus' ); ?></p>
				<h2><?php esc_html_e( 'About this event', 'eventpro-tickets-plus' ); ?></h2>
				<?php echo wp_kses_post( $event['content'] ); ?>
			</section>

			<?php if ( ! empty( $event['meta']['agenda'] ) ) : ?>
				<section class="eptp-section" id="eptp-event-agenda">
					<p class="eptp-section-label"><?php esc_html_e( 'Section 02', 'eventpro-tickets-plus' ); ?></p>
					<h2><?php esc_html_e( 'Agenda', 'eventpro-tickets-plus' ); ?></h2>
					<?php foreach ( $event['meta']['agenda'] as $agenda_item ) : ?>
						<div class="eptp-agenda-item">
							<div class="eptp-agenda-item__time"><?php echo esc_html( $agenda_item['time'] ); ?></div>
							<div class="eptp-agenda-item__body">
								<strong><?php echo esc_html( $agenda_item['title'] ); ?></strong>
								<p><?php echo esc_html( $agenda_item['location'] . ' · ' . $agenda_item['speaker'] ); ?></p>
							</div>
							<p class="eptp-agenda-item__description"><?php echo esc_html( $agenda_item['description'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $event['speakers'] ) ) : ?>
				<section class="eptp-section" id="eptp-event-speakers">
					<p class="eptp-section-label"><?php esc_html_e( 'Section 03', 'eventpro-tickets-plus' ); ?></p>
					<h2><?php esc_html_e( 'Featured Speakers', 'eventpro-tickets-plus' ); ?></h2>
					<div class="eptp-related-grid">
						<?php foreach ( $event['speakers'] as $speaker ) : ?>
							<div class="eptp-ticket-card">
								<?php if ( $speaker['image'] ) : ?>
									<img src="<?php echo esc_url( $speaker['image'] ); ?>" alt="<?php echo esc_attr( $speaker['name'] ); ?>">
								<?php endif; ?>
								<h3><?php echo esc_html( $speaker['name'] ); ?></h3>
								<div><?php echo wp_kses_post( $speaker['bio'] ); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $event['meta']['faq_items'] ) ) : ?>
				<section class="eptp-section" id="eptp-event-faq">
					<p class="eptp-section-label"><?php esc_html_e( 'Section 04', 'eventpro-tickets-plus' ); ?></p>
					<h2><?php esc_html_e( 'FAQs', 'eventpro-tickets-plus' ); ?></h2>
					<?php foreach ( $event['meta']['faq_items'] as $faq ) : ?>
						<details class="eptp-faq-item">
							<summary><?php echo esc_html( $faq['question'] ); ?></summary>
							<p><?php echo esc_html( $faq['answer'] ); ?></p>
						</details>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>
		</div>

		<div>
			<?php if ( ! empty( $event['venue'] ) ) : ?>
				<section class="eptp-section" id="eptp-event-venue">
					<p class="eptp-section-label"><?php esc_html_e( 'Venue Details', 'eventpro-tickets-plus' ); ?></p>
					<h2><?php esc_html_e( 'Venue', 'eventpro-tickets-plus' ); ?></h2>
					<p class="eptp-venue-title"><?php echo esc_html( $event['venue']['title'] ); ?></p>
					<div><?php echo wp_kses_post( $event['venue']['content'] ); ?></div>
					<?php if ( ! empty( $event['meta']['map_url'] ) ) : ?>
						<p><a href="<?php echo esc_url( $event['meta']['map_url'] ); ?>" class="eptp-link-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open map', 'eventpro-tickets-plus' ); ?></a></p>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $event['meta']['sponsors'] ) ) : ?>
				<section class="eptp-section">
					<p class="eptp-section-label"><?php esc_html_e( 'Partner Area', 'eventpro-tickets-plus' ); ?></p>
					<h2><?php esc_html_e( 'Sponsors', 'eventpro-tickets-plus' ); ?></h2>
					<div class="eptp-related-grid eptp-related-grid--stacked">
						<?php foreach ( $event['meta']['sponsors'] as $sponsor ) : ?>
							<div class="eptp-ticket-card">
								<?php if ( ! empty( $sponsor['logo_url'] ) ) : ?>
									<img src="<?php echo esc_url( $sponsor['logo_url'] ); ?>" alt="<?php echo esc_attr( $sponsor['name'] ); ?>">
								<?php endif; ?>
								<strong><?php echo esc_html( $sponsor['name'] ); ?></strong>
								<p><?php echo esc_html( $sponsor['level'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();

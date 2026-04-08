<?php
/**
 * Event archive template.
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

$categories = get_terms(
	array(
		'taxonomy'   => 'eptp_event_category',
		'hide_empty' => true,
	)
);

get_header();
?>
<main class="eptp-archive-shell">
	<?php eptp_render_site_header(); ?>
	<section class="eptp-hero eptp-hero--archive">
		<div class="eptp-hero__content">
			<p class="eptp-section-label"><?php esc_html_e( 'Event Discovery System', 'eventpro-tickets-plus' ); ?></p>
			<div class="eptp-chip-row">
				<span class="eptp-chip"><?php esc_html_e( 'WooCommerce Powered', 'eventpro-tickets-plus' ); ?></span>
				<span class="eptp-chip eptp-chip--accent"><?php esc_html_e( 'Premium Ticketing', 'eventpro-tickets-plus' ); ?></span>
			</div>
			<h1><?php post_type_archive_title(); ?></h1>
			<p><?php esc_html_e( 'Discover polished event experiences with modern ticketing, attendee journeys, and checkout flows built on WooCommerce.', 'eventpro-tickets-plus' ); ?></p>
			<div class="eptp-hero-meta">
				<span><?php esc_html_e( 'Single-day and multi-day', 'eventpro-tickets-plus' ); ?></span>
				<span><?php esc_html_e( 'Virtual and hybrid ready', 'eventpro-tickets-plus' ); ?></span>
				<span><?php esc_html_e( 'Coupon and tax compatible', 'eventpro-tickets-plus' ); ?></span>
			</div>
		</div>
		<div class="eptp-section eptp-hero__panel">
			<p class="eptp-section-label"><?php esc_html_e( 'Conversion Highlights', 'eventpro-tickets-plus' ); ?></p>
			<h2><?php esc_html_e( 'What this archive supports', 'eventpro-tickets-plus' ); ?></h2>
			<p><?php esc_html_e( 'Single-day, multi-day, virtual, hybrid, recurring-ready, waitlist-enabled, and coupon-friendly event sales.', 'eventpro-tickets-plus' ); ?></p>
			<ul class="eptp-hero-list">
				<li><?php esc_html_e( 'Sticky purchase journeys', 'eventpro-tickets-plus' ); ?></li>
				<li><?php esc_html_e( 'Attendee management and check-in', 'eventpro-tickets-plus' ); ?></li>
				<li><?php esc_html_e( 'Customer ticket dashboard', 'eventpro-tickets-plus' ); ?></li>
			</ul>
		</div>
	</section>

	<section class="eptp-filter-bar" aria-label="<?php esc_attr_e( 'Event filters', 'eventpro-tickets-plus' ); ?>">
		<div class="eptp-filter-search">
			<label class="screen-reader-text" for="eptp-event-search"><?php esc_html_e( 'Search events', 'eventpro-tickets-plus' ); ?></label>
			<input id="eptp-event-search" type="search" placeholder="<?php esc_attr_e( 'Search events, venues, or formats', 'eventpro-tickets-plus' ); ?>">
		</div>
		<div class="eptp-filter-pills" role="tablist" aria-label="<?php esc_attr_e( 'Event category filters', 'eventpro-tickets-plus' ); ?>">
			<button type="button" class="eptp-filter-pill is-active" data-filter="all" aria-pressed="true"><?php esc_html_e( 'All', 'eventpro-tickets-plus' ); ?></button>
			<?php if ( ! is_wp_error( $categories ) && $categories ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<button type="button" class="eptp-filter-pill" data-filter="<?php echo esc_attr( $category->slug ); ?>" aria-pressed="false"><?php echo esc_html( $category->name ); ?></button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( have_posts() ) : ?>
		<section class="eptp-ui-band">
			<div class="eptp-ui-band__card">
				<p class="eptp-section-label"><?php esc_html_e( 'Layout System', 'eventpro-tickets-plus' ); ?></p>
				<h2><?php esc_html_e( 'Modern card-first browsing', 'eventpro-tickets-plus' ); ?></h2>
				<p><?php esc_html_e( 'The archive uses compact filters, strong card hierarchy, and clear calls to action so visitors can move from browsing to buying without friction.', 'eventpro-tickets-plus' ); ?></p>
			</div>
			<div class="eptp-ui-band__grid">
				<div class="eptp-mini-card"><strong><?php esc_html_e( 'Spacing', 'eventpro-tickets-plus' ); ?></strong><span><?php esc_html_e( 'Large section gaps make the page feel premium while card internals stay compact and scannable.', 'eventpro-tickets-plus' ); ?></span></div>
				<div class="eptp-mini-card"><strong><?php esc_html_e( 'Typography', 'eventpro-tickets-plus' ); ?></strong><span><?php esc_html_e( 'A clear type scale helps visitors spot dates, price cues, and event names instantly.', 'eventpro-tickets-plus' ); ?></span></div>
				<div class="eptp-mini-card"><strong><?php esc_html_e( 'Conversion', 'eventpro-tickets-plus' ); ?></strong><span><?php esc_html_e( 'Every card surfaces timing, format, and a visible route into the ticket purchase flow.', 'eventpro-tickets-plus' ); ?></span></div>
			</div>
		</section>
		<div class="eptp-event-grid" id="eptp-event-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $payload = ( new \EventPro\TicketsPlus\Event_Service() )->get_event( get_the_ID() ); ?>
				<?php include EPTP_PLUGIN_DIR . 'public/views/event-card.php'; ?>
			<?php endwhile; ?>
		</div>
		<p class="eptp-empty-state" id="eptp-empty-state" hidden><?php esc_html_e( 'No events matched your current filters.', 'eventpro-tickets-plus' ); ?></p>
	<?php else : ?>
		<section class="eptp-section">
			<h2><?php esc_html_e( 'No events found', 'eventpro-tickets-plus' ); ?></h2>
			<p><?php esc_html_e( 'Create your first event in the WordPress admin to populate this premium archive page.', 'eventpro-tickets-plus' ); ?></p>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();

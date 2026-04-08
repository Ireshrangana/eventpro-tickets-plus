<?php
/**
 * Event category archive template.
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

$term = get_queried_object();

if ( ! $term instanceof \WP_Term ) {
	return;
}

$category_title       = single_term_title( '', false );
$category_description = term_description( $term );
$event_count          = isset( $term->count ) ? absint( $term->count ) : 0;
$children             = get_terms(
	array(
		'taxonomy'   => 'eptp_event_category',
		'hide_empty' => true,
		'parent'     => $term->term_id,
	)
);

get_header();
?>
<main class="eptp-archive-shell eptp-category-shell">
	<?php eptp_render_site_header(); ?>
	<section class="eptp-hero eptp-hero--archive eptp-category-hero">
		<div class="eptp-hero__content">
			<p class="eptp-section-label"><?php esc_html_e( 'Event Category', 'eventpro-tickets-plus' ); ?></p>
			<div class="eptp-chip-row">
				<span class="eptp-chip"><?php echo esc_html( sprintf( _n( '%d event', '%d events', $event_count, 'eventpro-tickets-plus' ), $event_count ) ); ?></span>
				<span class="eptp-chip eptp-chip--accent"><?php esc_html_e( 'Category Archive', 'eventpro-tickets-plus' ); ?></span>
			</div>
			<h1><?php echo esc_html( $category_title ); ?></h1>
			<p>
				<?php
				echo wp_kses_post(
					$category_description
						? $category_description
						: esc_html__( 'Browse events inside this curated category with a clean, conversion-focused archive layout.', 'eventpro-tickets-plus' )
				);
				?>
			</p>
			<div class="eptp-hero-meta">
				<span><?php esc_html_e( 'Responsive event cards', 'eventpro-tickets-plus' ); ?></span>
				<span><?php esc_html_e( 'Category-specific browsing', 'eventpro-tickets-plus' ); ?></span>
				<span><?php esc_html_e( 'WooCommerce-ready ticket flow', 'eventpro-tickets-plus' ); ?></span>
			</div>
		</div>
		<div class="eptp-section eptp-hero__panel">
			<p class="eptp-section-label"><?php esc_html_e( 'Explore This Category', 'eventpro-tickets-plus' ); ?></p>
			<h2><?php esc_html_e( 'Designed for clearer discovery', 'eventpro-tickets-plus' ); ?></h2>
			<p><?php esc_html_e( 'This category page groups related event experiences together so visitors can compare formats, timings, and ticketing options without distraction.', 'eventpro-tickets-plus' ); ?></p>
			<?php if ( ! is_wp_error( $children ) && $children ) : ?>
				<div class="eptp-filter-pills">
					<?php foreach ( $children as $child ) : ?>
						<a class="eptp-filter-pill" href="<?php echo esc_url( get_term_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<ul class="eptp-hero-list">
					<li><?php esc_html_e( 'Beautiful event pages', 'eventpro-tickets-plus' ); ?></li>
					<li><?php esc_html_e( 'Modern ticket cards', 'eventpro-tickets-plus' ); ?></li>
					<li><?php esc_html_e( 'Mobile-friendly archive browsing', 'eventpro-tickets-plus' ); ?></li>
				</ul>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( have_posts() ) : ?>
		<div class="eptp-event-grid eptp-event-grid--category">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $payload = ( new \EventPro\TicketsPlus\Event_Service() )->get_event( get_the_ID() ); ?>
				<?php include EPTP_PLUGIN_DIR . 'public/views/event-card.php'; ?>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<section class="eptp-section">
			<h2><?php esc_html_e( 'No events found', 'eventpro-tickets-plus' ); ?></h2>
			<p><?php esc_html_e( 'There are no published events in this category yet.', 'eventpro-tickets-plus' ); ?></p>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();

<?php
/**
 * Shortcodes.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode renderer.
 */
class Shortcodes {

	/**
	 * Event service.
	 *
	 * @var Event_Service
	 */
	protected Event_Service $events;

	/**
	 * Constructor.
	 *
	 * @param Event_Service $events Events.
	 */
	public function __construct( Event_Service $events ) {
		$this->events = $events;
	}

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public function register() : void {
		add_shortcode( 'eventpro_events', array( $this, 'render_event_archive' ) );
		add_shortcode( 'eventpro_event_categories', array( $this, 'render_event_categories' ) );
		add_shortcode( 'eventpro_ticket_dashboard', array( $this, 'render_ticket_dashboard' ) );
	}

	/**
	 * Event archive shortcode.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public function render_event_archive( array $atts ) : string {
		$atts   = shortcode_atts(
			array(
				'posts_per_page' => 6,
				'category'       => '',
				'title'          => '',
				'description'    => '',
			),
			$atts,
			'eventpro_events'
		);

		$args = array(
			'posts_per_page' => absint( $atts['posts_per_page'] ),
		);

		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'eptp_event_category',
					'field'    => 'slug',
					'terms'    => sanitize_title( (string) $atts['category'] ),
				),
			);
		}

		$events = $this->events->get_events( $args );

		ob_start();
		?>
		<section class="eptp-shortcode-shell">
			<?php if ( ! empty( $atts['title'] ) || ! empty( $atts['description'] ) ) : ?>
				<header class="eptp-shortcode-header">
					<?php if ( ! empty( $atts['title'] ) ) : ?>
						<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $atts['description'] ) ) : ?>
						<p><?php echo esc_html( (string) $atts['description'] ); ?></p>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<?php if ( $events ) : ?>
				<div class="eptp-event-grid eptp-event-grid--shortcode">
					<?php foreach ( $events as $event ) : ?>
						<?php $payload = $this->events->get_event( $event->ID ); ?>
						<?php include EPTP_PLUGIN_DIR . 'public/views/event-card.php'; ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="eptp-section">
					<h2><?php esc_html_e( 'No events found', 'eventpro-tickets-plus' ); ?></h2>
					<p><?php esc_html_e( 'There are no published events matching this shortcode yet.', 'eventpro-tickets-plus' ); ?></p>
				</div>
			<?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Customer dashboard shortcode.
	 *
	 * @return string
	 */
	public function render_ticket_dashboard() : string {
		ob_start();
		include EPTP_PLUGIN_DIR . 'public/views/ticket-dashboard.php';
		return (string) ob_get_clean();
	}

	/**
	 * Event category landing shortcode.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public function render_event_categories( array $atts ) : string {
		$atts = shortcode_atts(
			array(
				'title'       => __( 'Browse Event Categories', 'eventpro-tickets-plus' ),
				'description' => __( 'Explore curated event formats with clean category cards, clear counts, and direct access to category archive pages.', 'eventpro-tickets-plus' ),
				'limit'       => 6,
				'parent'      => 0,
			),
			$atts,
			'eventpro_event_categories'
		);

		$terms = get_terms(
			array(
				'taxonomy'   => 'eptp_event_category',
				'hide_empty' => true,
				'number'     => absint( $atts['limit'] ),
				'parent'     => absint( $atts['parent'] ),
			)
		);

		ob_start();
		?>
		<section class="eptp-shortcode-shell eptp-category-landing">
			<header class="eptp-shortcode-header eptp-category-landing__header">
				<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
				<p><?php echo esc_html( (string) $atts['description'] ); ?></p>
			</header>

			<?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
				<div class="eptp-category-grid">
					<?php foreach ( $terms as $term ) : ?>
						<a class="eptp-category-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
							<div class="eptp-category-card__top">
								<span class="eptp-section-label"><?php esc_html_e( 'Event Category', 'eventpro-tickets-plus' ); ?></span>
								<span class="eptp-chip"><?php echo esc_html( sprintf( _n( '%d event', '%d events', (int) $term->count, 'eventpro-tickets-plus' ), (int) $term->count ) ); ?></span>
							</div>
							<h3><?php echo esc_html( $term->name ); ?></h3>
							<p><?php echo esc_html( wp_strip_all_tags( term_description( $term ) ?: __( 'Explore events in this category and compare formats, dates, and ticketing options.', 'eventpro-tickets-plus' ) ) ); ?></p>
							<span class="eptp-category-card__link"><?php esc_html_e( 'View category', 'eventpro-tickets-plus' ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="eptp-section">
					<h2><?php esc_html_e( 'No event categories found', 'eventpro-tickets-plus' ); ?></h2>
					<p><?php esc_html_e( 'Create event categories in WordPress to populate this landing section.', 'eventpro-tickets-plus' ); ?></p>
				</div>
			<?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}
}

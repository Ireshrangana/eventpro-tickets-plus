<?php
/**
 * Event card.
 *
 * @var array<string,mixed> $payload
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

$category_slugs = wp_list_pluck( $payload['categories'], 'slug' );
$ui_style       = sanitize_html_class( (string) ( $payload['meta']['ui_style'] ?? 'summit' ) );
$search_index   = strtolower(
	implode(
		' ',
		array_filter(
			array(
				$payload['title'],
				wp_strip_all_tags( $payload['excerpt'] ),
				$payload['meta']['event_mode'] ?? '',
				$payload['meta']['event_type'] ?? '',
				$payload['venue']['title'] ?? '',
			)
		)
	)
);
?>
<article class="eptp-event-card eptp-event-card--<?php echo esc_attr( $ui_style ); ?>" data-category="<?php echo esc_attr( implode( ' ', $category_slugs ) ); ?>" data-search="<?php echo esc_attr( $search_index ); ?>">
	<?php if ( ! empty( $payload['thumbnail'] ) ) : ?>
		<a href="<?php echo esc_url( $payload['permalink'] ); ?>" class="eptp-event-card__image">
			<img src="<?php echo esc_url( $payload['thumbnail'] ); ?>" alt="<?php echo esc_attr( $payload['title'] ); ?>">
		</a>
	<?php endif; ?>
	<div class="eptp-event-card__content">
		<p class="eptp-section-label"><?php esc_html_e( 'Featured Experience', 'eventpro-tickets-plus' ); ?></p>
		<div class="eptp-chip-row">
			<span class="eptp-chip"><?php echo esc_html( $payload['status_badge'] ); ?></span>
			<?php if ( ! empty( $payload['meta']['hero_badge'] ) ) : ?>
				<span class="eptp-chip eptp-chip--accent"><?php echo esc_html( $payload['meta']['hero_badge'] ); ?></span>
			<?php endif; ?>
		</div>
		<div class="eptp-event-card__eyebrow">
			<span><?php echo esc_html( ucfirst( (string) ( $payload['meta']['event_mode'] ?? '' ) ) ); ?></span>
			<span><?php echo esc_html( ucfirst( str_replace( '-', ' ', (string) ( $payload['meta']['event_type'] ?? '' ) ) ) ); ?></span>
		</div>
		<h3><a href="<?php echo esc_url( $payload['permalink'] ); ?>"><?php echo esc_html( $payload['title'] ); ?></a></h3>
		<p><?php echo esc_html( $payload['excerpt'] ); ?></p>
		<div class="eptp-meta-line">
			<span><?php echo esc_html( eptp_format_datetime( (string) $payload['meta']['start_date'] ) ); ?></span>
			<span><?php echo esc_html( ! empty( $payload['venue']['title'] ) ? $payload['venue']['title'] : ucfirst( (string) $payload['meta']['event_mode'] ) ); ?></span>
		</div>
		<div class="eptp-event-card__stats">
			<div><strong><?php echo esc_html( ucfirst( (string) ( $payload['meta']['event_mode'] ?? '' ) ) ); ?></strong><span><?php esc_html_e( 'Delivery', 'eventpro-tickets-plus' ); ?></span></div>
			<div><strong><?php echo esc_html( ucfirst( str_replace( '-', ' ', (string) ( $payload['meta']['event_type'] ?? '' ) ) ) ); ?></strong><span><?php esc_html_e( 'Format', 'eventpro-tickets-plus' ); ?></span></div>
		</div>
		<div class="eptp-event-card__footer">
			<a href="<?php echo esc_url( $payload['permalink'] ); ?>" class="eptp-link-button"><?php esc_html_e( 'View event', 'eventpro-tickets-plus' ); ?></a>
			<?php if ( isset( $payload['available_tickets'] ) && PHP_INT_MAX !== $payload['available_tickets'] ) : ?>
				<span class="eptp-availability"><?php echo esc_html( sprintf( _n( '%d ticket left', '%d tickets left', (int) $payload['available_tickets'], 'eventpro-tickets-plus' ), (int) $payload['available_tickets'] ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</article>

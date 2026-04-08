<?php
/**
 * Event service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Event domain logic.
 */
class Event_Service {

	/**
	 * Cached event payloads.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected array $cache = array();

	/**
	 * Get event data bundle.
	 *
	 * @param int $event_id Event ID.
	 * @return array<string, mixed>
	 */
	public function get_event( int $event_id ) : array {
		if ( isset( $this->cache[ $event_id ] ) ) {
			return $this->cache[ $event_id ];
		}

		$post = get_post( $event_id );

		if ( ! $post || 'eptp_event' !== $post->post_type ) {
			return array();
		}

		$data = eptp_get_event_meta( $event_id );

		$this->cache[ $event_id ] = array(
			'id'             => $event_id,
			'title'          => get_the_title( $event_id ),
			'permalink'      => get_permalink( $event_id ),
			'excerpt'        => get_the_excerpt( $event_id ),
			'content'        => apply_filters( 'the_content', $post->post_content ),
			'thumbnail'      => $this->get_event_thumbnail_url( $event_id, $data ),
			'meta'           => $data,
			'venue'          => $this->get_venue( (int) $data['venue_id'] ),
			'organizers'     => $this->get_people( $data['organizer_ids'] ),
			'speakers'       => $this->get_people( $data['speaker_ids'] ),
			'categories'     => wp_get_post_terms( $event_id, 'eptp_event_category' ),
			'available_tickets' => apply_filters( 'eventpro_tickets_plus_available_tickets', $this->get_available_tickets( $event_id ), $event_id ),
			'status_badge'   => ucfirst( str_replace( '-', ' ', (string) $data['status'] ) ),
		);

		$this->cache[ $event_id ] = apply_filters( 'eventpro_tickets_plus_event_payload', $this->cache[ $event_id ], $event_id, $post );

		return $this->cache[ $event_id ];
	}

	/**
	 * Get venue payload.
	 *
	 * @param int $venue_id Venue ID.
	 * @return array<string, mixed>
	 */
	public function get_venue( int $venue_id ) : array {
		if ( ! $venue_id ) {
			return array();
		}

		return array(
			'id'      => $venue_id,
			'title'   => get_the_title( $venue_id ),
			'content' => apply_filters( 'the_content', get_post_field( 'post_content', $venue_id ) ),
			'image'   => get_the_post_thumbnail_url( $venue_id, 'medium' ),
		);
	}

	/**
	 * Get people collection.
	 *
	 * @param array<int, int> $ids IDs.
	 * @return array<int, array<string, mixed>>
	 */
	protected function get_people( array $ids ) : array {
		$items = array();

		foreach ( $ids as $id ) {
			$items[] = array(
				'id'      => $id,
				'name'    => get_the_title( $id ),
				'bio'     => apply_filters( 'the_content', get_post_field( 'post_content', $id ) ),
				'image'   => get_the_post_thumbnail_url( $id, 'medium' ),
				'link'    => get_edit_post_link( $id, '' ),
			);
		}

		return $items;
	}

	/**
	 * Get upcoming events.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, \WP_Post>
	 */
	public function get_events( array $args = array() ) : array {
		$defaults = array(
			'post_type'      => 'eptp_event',
			'posts_per_page' => 12,
			'post_status'    => 'publish',
			'meta_key'       => '_eptp_event_data',
		);

		return get_posts( wp_parse_args( $args, $defaults ) );
	}

	/**
	 * Calculate capacity left.
	 *
	 * @param int $event_id Event ID.
	 * @return int
	 */
	public function get_available_tickets( int $event_id ) : int {
		$data     = eptp_get_event_meta( $event_id );
		$capacity = absint( $data['capacity'] );

		if ( 0 === $capacity ) {
			return PHP_INT_MAX;
		}

		$sold = ( new Attendee_Service() )->count_valid_attendees_for_event( $event_id );

		return max( 0, $capacity - $sold );
	}

	/**
	 * Check if event sales are open.
	 *
	 * @param int $event_id Event ID.
	 * @return bool
	 */
	public function is_sales_open( int $event_id ) : bool {
		$data = eptp_get_event_meta( $event_id );
		$now  = current_time( 'timestamp' );

		$start = ! empty( $data['sales_start'] ) ? strtotime( $data['sales_start'] ) : 0;
		$end   = ! empty( $data['sales_end'] ) ? strtotime( $data['sales_end'] ) : 0;

		if ( $start && $now < $start ) {
			return false;
		}

		if ( $end && $now > $end ) {
			return false;
		}

		return 'cancelled' !== $data['status'];
	}

	/**
	 * Resolve the event hero image URL with demo fallbacks.
	 *
	 * @param int                  $event_id Event ID.
	 * @param array<string, mixed> $data Event meta.
	 * @return string
	 */
	protected function get_event_thumbnail_url( int $event_id, array $data ) : string {
		$thumbnail = get_the_post_thumbnail_url( $event_id, 'large' );

		if ( $thumbnail ) {
			return $thumbnail;
		}

		$hero_media = isset( $data['hero_media'] ) ? sanitize_file_name( (string) $data['hero_media'] ) : '';

		if ( $hero_media ) {
			return EPTP_PLUGIN_URL . 'assets/images/' . $hero_media;
		}

		$ui_style = isset( $data['ui_style'] ) ? sanitize_key( (string) $data['ui_style'] ) : 'summit';

		return EPTP_PLUGIN_URL . 'assets/images/demo-hero-' . $ui_style . '.svg';
	}
}

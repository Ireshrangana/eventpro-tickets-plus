<?php
/**
 * Attendee service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Creates and manages attendees.
 */
class Attendee_Service {

	/**
	 * Issue attendees for an order line item.
	 *
	 * @param int          $order_id Order ID.
	 * @param int          $event_id Event ID.
	 * @param int          $product_id Product ID.
	 * @param int          $quantity Quantity.
	 * @param array<string,mixed> $customer Customer data.
	 * @return array<int, int>
	 */
	public function issue_attendees( int $order_id, int $event_id, int $product_id, int $quantity, array $customer ) : array {
		$attendee_ids = array();
		$email        = sanitize_email( $customer['email'] ?? '' );
		$name         = sanitize_text_field( $customer['name'] ?? '' );

		if ( ! is_email( $email ) || $quantity < 1 ) {
			return $attendee_ids;
		}

		for ( $i = 1; $i <= $quantity; $i++ ) {
			$ticket_code = eptp_generate_ticket_code( $event_id, $order_id );
			$post_id     = wp_insert_post(
				array(
					'post_type'   => 'eptp_attendee',
					'post_status' => 'publish',
					'post_title'  => sprintf(
						/* translators: 1: attendee email 2: ticket code */
						__( '%1$s - %2$s', 'eventpro-tickets-plus' ),
						$email,
						$ticket_code
					),
				)
			);

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			update_post_meta( $post_id, '_eptp_order_id', $order_id );
			update_post_meta( $post_id, '_eptp_event_id', $event_id );
			update_post_meta( $post_id, '_eptp_product_id', $product_id );
			update_post_meta( $post_id, '_eptp_ticket_code', $ticket_code );
			update_post_meta( $post_id, '_eptp_attendee_name', $name );
			update_post_meta( $post_id, '_eptp_attendee_email', $email );
			update_post_meta( $post_id, '_eptp_status', 'valid' );
			update_post_meta( $post_id, '_eptp_checked_in', 'no' );
			update_post_meta( $post_id, '_eptp_registration_fields', is_array( $customer['registration_fields'] ?? null ) ? $customer['registration_fields'] : array() );

			$attendee_ids[] = $post_id;
		}

		return $attendee_ids;
	}

	/**
	 * Invalidate attendees for an order.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status Status.
	 * @return void
	 */
	public function set_order_attendees_status( int $order_id, string $status ) : void {
		$attendees = get_posts(
			array(
				'post_type'      => 'eptp_attendee',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_eptp_order_id',
				'meta_value'     => $order_id,
			)
		);

		foreach ( $attendees as $attendee_id ) {
			update_post_meta( $attendee_id, '_eptp_status', sanitize_text_field( $status ) );
		}
	}

	/**
	 * Count valid attendees per event.
	 *
	 * @param int $event_id Event ID.
	 * @return int
	 */
	public function count_valid_attendees_for_event( int $event_id ) : int {
		$query = new \WP_Query(
			array(
				'post_type'      => 'eptp_attendee',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => '_eptp_event_id',
						'value' => $event_id,
					),
					array(
						'key'   => '_eptp_status',
						'value' => array( 'valid', 'checked-in' ),
						'compare' => 'IN',
					),
				),
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * Get attendees for customer.
	 *
	 * @param string $email Customer email.
	 * @return array<int, \WP_Post>
	 */
	public function get_attendees_by_email( string $email ) : array {
		return get_posts(
			array(
				'post_type'      => 'eptp_attendee',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_key'       => '_eptp_attendee_email',
				'meta_value'     => sanitize_email( $email ),
			)
		);
	}
}

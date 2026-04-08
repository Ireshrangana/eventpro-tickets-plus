<?php
/**
 * Check-in service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Check-in workflow and logging.
 */
class Checkin_Service {

	/**
	 * Find attendee by token.
	 *
	 * @param string $query Query string.
	 * @return \WP_Post|null
	 */
	public function find_attendee( string $query ) : ?\WP_Post {
		$query = trim( sanitize_text_field( $query ) );

		if ( empty( $query ) ) {
			return null;
		}

		$attendees = get_posts(
			array(
				'post_type'      => 'eptp_attendee',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'   => '_eptp_ticket_code',
						'value' => $query,
					),
					array(
						'key'   => '_eptp_attendee_email',
						'value' => $query,
					),
					array(
						'key'   => '_eptp_order_id',
						'value' => absint( $query ),
					),
				),
			)
		);

		return $attendees ? $attendees[0] : null;
	}

	/**
	 * Validate and complete check-in.
	 *
	 * @param int    $attendee_id Attendee ID.
	 * @param string $source Source.
	 * @param bool   $override Allow duplicate check-ins.
	 * @return array<string,mixed>
	 */
	public function check_in( int $attendee_id, string $source = 'manual', bool $override = false ) : array {
		$status = (string) get_post_meta( $attendee_id, '_eptp_status', true );

		if ( 'invalid' === $status ) {
			return array(
				'success' => false,
				'message' => __( 'This ticket is no longer valid due to order status or refund state.', 'eventpro-tickets-plus' ),
			);
		}

		$checked_in = (string) get_post_meta( $attendee_id, '_eptp_checked_in', true );

		if ( 'yes' === $checked_in && ! $override ) {
			return array(
				'success' => false,
				'message' => __( 'This attendee has already been checked in.', 'eventpro-tickets-plus' ),
			);
		}

		update_post_meta( $attendee_id, '_eptp_checked_in', 'yes' );
		update_post_meta( $attendee_id, '_eptp_status', 'checked-in' );
		update_post_meta( $attendee_id, '_eptp_checked_in_at', current_time( 'mysql' ) );
		update_post_meta( $attendee_id, '_eptp_checked_in_by', get_current_user_id() );

		$this->log(
			$attendee_id,
			(int) get_post_meta( $attendee_id, '_eptp_event_id', true ),
			(int) get_post_meta( $attendee_id, '_eptp_order_id', true ),
			(string) get_post_meta( $attendee_id, '_eptp_ticket_code', true ),
			'checked-in',
			$source
		);

		return array(
			'success' => true,
			'message' => __( 'Attendee checked in successfully.', 'eventpro-tickets-plus' ),
		);
	}

	/**
	 * Write scan log row.
	 *
	 * @param int    $attendee_id Attendee ID.
	 * @param int    $event_id Event ID.
	 * @param int    $order_id Order ID.
	 * @param string $ticket_code Ticket code.
	 * @param string $status Log status.
	 * @param string $source Source.
	 * @return void
	 */
	public function log( int $attendee_id, int $event_id, int $order_id, string $ticket_code, string $status, string $source ) : void {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'eptp_checkin_logs',
			array(
				'attendee_id'   => $attendee_id,
				'event_id'      => $event_id,
				'order_id'      => $order_id,
				'ticket_code'   => $ticket_code,
				'status'        => $status,
				'checked_in_by' => get_current_user_id(),
				'scan_source'   => $source,
				'notes'         => '',
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}

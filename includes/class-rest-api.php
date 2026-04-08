<?php
/**
 * REST API routes.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * REST controllers.
 */
class REST_API {

	/**
	 * Check-in service.
	 *
	 * @var Checkin_Service
	 */
	protected Checkin_Service $checkin_service;

	/**
	 * Constructor.
	 *
	 * @param Checkin_Service $checkin_service Service.
	 */
	public function __construct( Checkin_Service $checkin_service ) {
		$this->checkin_service = $checkin_service;
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register() : void {
		register_rest_route(
			'eventpro-tickets-plus/v1',
			'/checkin',
			array(
				'methods'             => 'POST',
				'permission_callback' => array( $this, 'can_manage_checkins' ),
				'callback'            => array( $this, 'handle_checkin' ),
				'args'                => array(
					'query'    => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'override' => array( 'required' => false, 'type' => 'boolean' ),
				),
			)
		);

		register_rest_route(
			'eventpro-tickets-plus/v1',
			'/waitlist',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'handle_waitlist' ),
				'args'                => array(
					'event_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'name'    => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'email'   => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
					),
				),
			)
		);
	}

	/**
	 * Permission callback.
	 *
	 * @return bool
	 */
	public function can_manage_checkins() : bool {
		return current_user_can( 'manage_eptp_checkins' ) || current_user_can( 'manage_eventpro_tickets_plus' );
	}

	/**
	 * Handle staff check-in.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function handle_checkin( WP_REST_Request $request ) : WP_REST_Response {
		if ( $request->get_param( 'override' ) && ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Only administrators can override duplicate check-ins.', 'eventpro-tickets-plus' ),
				),
				403
			);
		}

		$attendee = $this->checkin_service->find_attendee( (string) $request->get_param( 'query' ) );

		if ( ! $attendee ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'No attendee matched that ticket code, email, or order number.', 'eventpro-tickets-plus' ),
				),
				404
			);
		}

		$result = $this->checkin_service->check_in(
			$attendee->ID,
			'rest',
			(bool) $request->get_param( 'override' )
		);

		$result['attendee'] = array(
			'id'         => $attendee->ID,
			'name'       => get_post_meta( $attendee->ID, '_eptp_attendee_name', true ),
			'email'      => get_post_meta( $attendee->ID, '_eptp_attendee_email', true ),
			'event_id'   => (int) get_post_meta( $attendee->ID, '_eptp_event_id', true ),
			'event_name' => get_the_title( (int) get_post_meta( $attendee->ID, '_eptp_event_id', true ) ),
			'order_id'   => (int) get_post_meta( $attendee->ID, '_eptp_order_id', true ),
			'code'       => get_post_meta( $attendee->ID, '_eptp_ticket_code', true ),
		);

		return new WP_REST_Response( $result );
	}

	/**
	 * Handle waitlist opt-in.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function handle_waitlist( WP_REST_Request $request ) : WP_REST_Response {
		$event_id = absint( $request->get_param( 'event_id' ) );
		$email    = sanitize_email( (string) $request->get_param( 'email' ) );
		$name     = sanitize_text_field( (string) $request->get_param( 'name' ) );

		if ( ! $event_id || ! is_email( $email ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Valid event and email details are required.', 'eventpro-tickets-plus' ),
				),
				400
			);
		}

		if ( 'eptp_event' !== get_post_type( $event_id ) || 'publish' !== get_post_status( $event_id ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'That event is not available for waitlist signups.', 'eventpro-tickets-plus' ),
				),
				404
			);
		}

		$event_meta = eptp_get_event_meta( $event_id );
		$settings   = eptp_get_plugin_settings();

		if ( 'yes' !== $settings['waitlist_enabled'] || 'yes' !== $event_meta['waitlist_enabled'] ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'The waitlist is disabled for this event.', 'eventpro-tickets-plus' ),
				),
				403
			);
		}

		$rate_key = eptp_get_waitlist_rate_limit_key( $event_id, $email );

		if ( get_transient( $rate_key ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Please wait before submitting the waitlist form again.', 'eventpro-tickets-plus' ),
				),
				429
			);
		}

		$existing = get_posts(
			array(
				'post_type'      => 'eptp_waitlist',
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
						'key'   => '_eptp_email',
						'value' => $email,
					),
				),
			)
		);

		if ( ! empty( $existing ) ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'You are already on the waitlist for this event.', 'eventpro-tickets-plus' ),
				)
			);
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'eptp_waitlist',
				'post_status' => 'publish',
				'post_title'  => $email,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Unable to save your waitlist request right now.', 'eventpro-tickets-plus' ),
				),
				500
			);
		}

		update_post_meta( $post_id, '_eptp_event_id', $event_id );
		update_post_meta( $post_id, '_eptp_name', $name );
		update_post_meta( $post_id, '_eptp_email', $email );
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS );
		do_action( 'eventpro_tickets_plus_waitlist_created', $post_id, $event_id, $email, $name );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'You have been added to the waitlist.', 'eventpro-tickets-plus' ),
			)
		);
	}
}

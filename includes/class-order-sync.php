<?php
/**
 * Order sync service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Synchronizes WooCommerce orders with tickets.
 */
class Order_Sync {

	/**
	 * Attendee service.
	 *
	 * @var Attendee_Service
	 */
	protected Attendee_Service $attendees;

	/**
	 * Ticket service.
	 *
	 * @var Ticket_Service
	 */
	protected Ticket_Service $tickets;

	/**
	 * Constructor.
	 *
	 * @param Attendee_Service $attendees Attendee service.
	 * @param Ticket_Service   $tickets Ticket service.
	 */
	public function __construct( Attendee_Service $attendees, Ticket_Service $tickets ) {
		$this->attendees = $attendees;
		$this->tickets   = $tickets;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		add_action( 'woocommerce_order_status_processing', array( $this, 'issue_tickets' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'issue_tickets' ) );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'invalidate_tickets' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'invalidate_tickets' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'invalidate_tickets' ) );
	}

	/**
	 * Issue tickets after valid payment states.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function issue_tickets( int $order_id ) : void {
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order || $order->get_meta( '_eptp_tickets_issued', true ) ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$event_id   = $this->tickets->get_event_id_by_product( $product_id );

			if ( ! $event_id ) {
				continue;
			}

			$customer = array(
				'name'               => $order->get_formatted_billing_full_name(),
				'email'              => $order->get_billing_email(),
				'registration_fields'=> $item->get_meta( '_eptp_registration_fields', true ),
			);

			$issued = $this->attendees->issue_attendees(
				$order_id,
				$event_id,
				$product_id,
				(int) $item->get_quantity(),
				$customer
			);

			// Fires after attendee records are created so extensions can add custom passes,
			// wallet integrations, badges, or CRM sync actions without altering core logic.
			do_action( 'eventpro_tickets_plus_attendees_issued', $issued, $order_id, $event_id, $item );
		}

		$order->update_meta_data( '_eptp_tickets_issued', time() );
		$order->save();
	}

	/**
	 * Invalidate tickets on cancelled/refunded states.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function invalidate_tickets( int $order_id ) : void {
		$this->attendees->set_order_attendees_status( $order_id, 'invalid' );
	}
}

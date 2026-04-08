<?php
/**
 * Ticket service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Ticket tier utilities.
 */
class Ticket_Service {

	/**
	 * Get event ticket tiers.
	 *
	 * @param int  $event_id Event ID.
	 * @param bool $include_private Include hidden tiers.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_ticket_tiers( int $event_id, bool $include_private = false ) : array {
		$data  = eptp_get_event_meta( $event_id );
		$tiers = is_array( $data['ticket_tiers'] ) ? $data['ticket_tiers'] : array();

		$mapped = array();

		foreach ( $tiers as $tier ) {
			$product_id = absint( $tier['product_id'] ?? 0 );
			$product    = $product_id ? wc_get_product( $product_id ) : null;

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			if ( ! $include_private && 'yes' === ( $tier['private'] ?? 'no' ) ) {
				continue;
			}

			$mapped[] = array(
				'label'       => sanitize_text_field( $tier['label'] ?? $product->get_name() ),
				'product_id'  => $product_id,
				'type'        => sanitize_text_field( $tier['type'] ?? 'general-admission' ),
				'price_label' => sanitize_text_field( $tier['price_label'] ?? $product->get_price_html() ),
				'description' => sanitize_textarea_field( $tier['description'] ?? '' ),
				'badge'       => sanitize_text_field( $tier['badge'] ?? '' ),
				'min_qty'     => max( 1, absint( $tier['min_qty'] ?? 1 ) ),
				'max_qty'     => max( 1, absint( $tier['max_qty'] ?? 10 ) ),
				'private'     => eptp_sanitize_checkbox( $tier['private'] ?? 'no' ),
				'price_html'  => $product->get_price_html(),
				'in_stock'    => $product->is_in_stock(),
				'stock_qty'   => $product->get_stock_quantity(),
				'product'     => $product,
			);
		}

		return apply_filters( 'eventpro_tickets_plus_ticket_tiers', $mapped, $event_id, $include_private );
	}

	/**
	 * Map a product to an event.
	 *
	 * @param int $product_id Product ID.
	 * @return int
	 */
	public function get_event_id_by_product( int $product_id ) : int {
		return absint( get_post_meta( $product_id, '_eptp_event_id', true ) );
	}

	/**
	 * Validate tier before add to cart.
	 *
	 * @param int $product_id Product ID.
	 * @param int $quantity Quantity.
	 * @return true|\WP_Error
	 */
	public function validate_ticket_purchase( int $product_id, int $quantity ) : true|\WP_Error {
		$event_id = $this->get_event_id_by_product( $product_id );

		if ( ! $event_id ) {
			return true;
		}

		$event_service = new Event_Service();

		if ( ! $event_service->is_sales_open( $event_id ) ) {
			return new \WP_Error( 'sales_closed', __( 'Ticket sales for this event are currently closed.', 'eventpro-tickets-plus' ) );
		}

		if ( $event_service->get_available_tickets( $event_id ) < $quantity ) {
			return new \WP_Error( 'insufficient_capacity', __( 'There are not enough tickets left for this event.', 'eventpro-tickets-plus' ) );
		}

		return true;
	}
}

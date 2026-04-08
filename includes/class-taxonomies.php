<?php
/**
 * Taxonomy registration.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Registers event taxonomies.
 */
class Taxonomies {

	/**
	 * Register taxonomies.
	 *
	 * @return void
	 */
	public function register() : void {
		register_taxonomy(
			'eptp_event_category',
			array( 'eptp_event' ),
			array(
				'labels'       => array(
					'name'          => __( 'Event Categories', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Event Category', 'eventpro-tickets-plus' ),
				),
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'event-category' ),
			)
		);

		register_taxonomy(
			'eptp_event_label',
			array( 'eptp_event' ),
			array(
				'labels'       => array(
					'name'          => __( 'Event Labels', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Event Label', 'eventpro-tickets-plus' ),
				),
				'hierarchical' => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'event-label' ),
			)
		);
	}
}

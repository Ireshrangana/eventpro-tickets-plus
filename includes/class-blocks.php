<?php
/**
 * Blocks support.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Registers dynamic blocks.
 */
class Blocks {

	/**
	 * Shortcodes service.
	 *
	 * @var Shortcodes
	 */
	protected Shortcodes $shortcodes;

	/**
	 * Constructor.
	 *
	 * @param Shortcodes $shortcodes Shortcodes service.
	 */
	public function __construct( Shortcodes $shortcodes ) {
		$this->shortcodes = $shortcodes;
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function register() : void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'eventpro-tickets-plus/event-list',
			array(
				'api_version'     => 2,
				'render_callback' => function( $attributes ) {
					return $this->shortcodes->render_event_archive(
						array(
							'posts_per_page' => $attributes['postsPerPage'] ?? 6,
						)
					);
				},
				'attributes'      => array(
					'postsPerPage' => array(
						'type'    => 'number',
						'default' => 6,
					),
				),
			)
		);
	}
}

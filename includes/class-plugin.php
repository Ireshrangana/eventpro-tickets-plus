<?php
/**
 * Main plugin orchestrator.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps plugin services.
 */
class Plugin {

	/**
	 * Loader.
	 *
	 * @var Loader
	 */
	protected Loader $loader;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->loader = new Loader();
		$this->init();
	}

	/**
	 * Initialize services.
	 *
	 * @return void
	 */
	protected function init() : void {
		load_plugin_textdomain( 'eventpro-tickets-plus', false, dirname( EPTP_PLUGIN_BASENAME ) . '/languages' );

		$assets      = new Assets();
		$post_types  = new Post_Types();
		$taxonomies  = new Taxonomies();
		$meta        = new Meta();
		$events      = new Event_Service();
		$tickets     = new Ticket_Service();
		$attendees   = new Attendee_Service();
		$order_sync  = new Order_Sync( $attendees, $tickets );
		$checkin     = new Checkin_Service();
		$rest        = new REST_API( $checkin );
		$shortcodes  = new Shortcodes( $events );
		$blocks      = new Blocks( $shortcodes );
		$woocommerce = new WooCommerce( $tickets );
		$emails      = new Emails();
		$admin       = new \EventPro\TicketsPlus\Admin\Admin( $events, $tickets, $attendees, $checkin );
		$public      = new \EventPro\TicketsPlus\Front\Public_Facing( $events, $tickets, $attendees, new QR_Service(), new PDF_Ticket_Service() );
		$components  = apply_filters(
			'eventpro_tickets_plus_plugin_components',
			array(
				'assets'      => $assets,
				'post_types'  => $post_types,
				'taxonomies'  => $taxonomies,
				'meta'        => $meta,
				'events'      => $events,
				'tickets'     => $tickets,
				'attendees'   => $attendees,
				'order_sync'  => $order_sync,
				'checkin'     => $checkin,
				'rest'        => $rest,
				'shortcodes'  => $shortcodes,
				'blocks'      => $blocks,
				'woocommerce' => $woocommerce,
				'emails'      => $emails,
				'admin'       => $admin,
				'public'      => $public,
			)
		);

		$this->loader->add_action( 'init', $components['post_types'], 'register' );
		$this->loader->add_action( 'init', $components['taxonomies'], 'register' );
		$this->loader->add_action( 'init', $components['meta'], 'register' );
		$this->loader->add_action( 'init', $components['blocks'], 'register' );
		$this->loader->add_action( 'rest_api_init', $components['rest'], 'register' );

		$components['assets']->register();
		$components['order_sync']->register();
		$components['woocommerce']->register();
		$components['shortcodes']->register();
		$components['emails']->register();
		$components['admin']->register();
		$components['public']->register();

		add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features' ) );
	}

	/**
	 * Declare WooCommerce compatibility.
	 *
	 * @return void
	 */
	public function declare_wc_features() : void {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', EPTP_PLUGIN_FILE, true );
		}
	}

	/**
	 * Execute hooks.
	 *
	 * @return void
	 */
	public function run() : void {
		do_action( 'eventpro_tickets_plus_before_run', $this );
		$this->loader->run();
		do_action( 'eventpro_tickets_plus_after_run', $this );
	}
}

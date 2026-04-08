<?php
/**
 * Plugin Name: EventPro Tickets Plus for WooCommerce
 * Plugin URI:  https://www.synbus.ph/
 * Description: Premium WooCommerce-powered event management, ticketing, attendee, and check-in toolkit for WordPress.
 * Version:     1.0.0
 * Author:      Iresh Rangana
 * Author URI:  https://www.synbus.ph/
 * Text Domain: eventpro-tickets-plus
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'EPTP_VERSION' ) ) {
	define( 'EPTP_VERSION', '1.0.0' );
}

if ( ! defined( 'EPTP_PLUGIN_FILE' ) ) {
	define( 'EPTP_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'EPTP_PLUGIN_DIR' ) ) {
	define( 'EPTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EPTP_PLUGIN_URL' ) ) {
	define( 'EPTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'EPTP_PLUGIN_BASENAME' ) ) {
	define( 'EPTP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

require_once EPTP_PLUGIN_DIR . 'includes/helpers.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-loader.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-activator.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-permissions.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-post-types.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-taxonomies.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-meta.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-event-service.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-ticket-service.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-order-sync.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-attendee-service.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-qr-service.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-pdf-ticket-service.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-checkin-service.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-blocks.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-assets.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-woocommerce.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-emails.php';
require_once EPTP_PLUGIN_DIR . 'admin/class-admin.php';
require_once EPTP_PLUGIN_DIR . 'public/class-public.php';
require_once EPTP_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( EventPro\TicketsPlus\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( EventPro\TicketsPlus\Deactivator::class, 'deactivate' ) );

/**
 * Boot the plugin once WooCommerce is loaded.
 *
 * @return void
 */
function eventpro_tickets_plus() : void {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new EventPro\TicketsPlus\Plugin();
		$plugin->run();
	}
}

add_action( 'plugins_loaded', 'eventpro_tickets_plus', 20 );

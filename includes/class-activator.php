<?php
/**
 * Plugin activation.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Handles setup tasks.
 */
class Activator {

	/**
	 * Activate plugin.
	 *
	 * @return void
	 */
	public static function activate() : void {
		if ( ! class_exists( '\WooCommerce' ) ) {
			deactivate_plugins( EPTP_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'EventPro Tickets Plus for WooCommerce requires WooCommerce to be active.', 'eventpro-tickets-plus' ),
				esc_html__( 'WooCommerce Required', 'eventpro-tickets-plus' ),
				array( 'back_link' => true )
			);
		}

		Permissions::add_caps();
		self::create_tables();
		self::seed_settings();

		$post_types = new Post_Types();
		$taxonomies = new Taxonomies();

		$post_types->register();
		$taxonomies->register();

		flush_rewrite_rules();
	}

	/**
	 * Seed settings.
	 *
	 * @return void
	 */
	protected static function seed_settings() : void {
		if ( ! get_option( 'eptp_settings' ) ) {
			add_option( 'eptp_settings', eptp_get_settings_defaults() );
		}

		update_option( 'eptp_version', EPTP_VERSION );
	}

	/**
	 * Create custom tables.
	 *
	 * @return void
	 */
	protected static function create_tables() : void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . 'eptp_checkin_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			attendee_id BIGINT UNSIGNED NOT NULL,
			event_id BIGINT UNSIGNED NOT NULL,
			order_id BIGINT UNSIGNED NOT NULL,
			ticket_code VARCHAR(64) NOT NULL,
			status VARCHAR(24) NOT NULL,
			checked_in_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
			scan_source VARCHAR(32) NOT NULL DEFAULT 'manual',
			notes TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY attendee_id (attendee_id),
			KEY event_id (event_id),
			KEY order_id (order_id),
			KEY ticket_code (ticket_code)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}

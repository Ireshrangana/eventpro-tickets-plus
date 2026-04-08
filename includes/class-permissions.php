<?php
/**
 * Permission management.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Registers capabilities and staff role.
 */
class Permissions {

	/**
	 * Custom capabilities.
	 *
	 * @return array<int, string>
	 */
	public static function get_caps() : array {
		return array(
			'manage_eventpro_tickets_plus',
			'read_eptp_event',
			'edit_eptp_event',
			'delete_eptp_event',
			'edit_eptp_events',
			'edit_others_eptp_events',
			'edit_private_eptp_events',
			'edit_published_eptp_events',
			'delete_eptp_events',
			'delete_private_eptp_events',
			'delete_published_eptp_events',
			'delete_others_eptp_events',
			'publish_eptp_events',
			'read_private_eptp_events',
			'edit_eptp_attendees',
			'manage_eptp_checkins',
			'export_eptp_attendees',
		);
	}

	/**
	 * Add capabilities.
	 *
	 * @return void
	 */
	public static function add_caps() : void {
		$roles = array( 'administrator', 'shop_manager' );

		foreach ( $roles as $role_slug ) {
			$role = get_role( $role_slug );

			if ( ! $role ) {
				continue;
			}

			foreach ( self::get_caps() as $cap ) {
				$role->add_cap( $cap );
			}
		}

		add_role(
			'eptp_staff',
			__( 'Event Staff', 'eventpro-tickets-plus' ),
			array(
				'read'                 => true,
				'manage_eptp_checkins' => true,
			)
		);
	}

	/**
	 * Remove capabilities.
	 *
	 * @return void
	 */
	public static function remove_caps() : void {
		$roles = array( 'administrator', 'shop_manager' );

		foreach ( $roles as $role_slug ) {
			$role = get_role( $role_slug );

			if ( ! $role ) {
				continue;
			}

			foreach ( self::get_caps() as $cap ) {
				$role->remove_cap( $cap );
			}
		}

		remove_role( 'eptp_staff' );
	}
}

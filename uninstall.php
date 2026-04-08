<?php
/**
 * Uninstall routine.
 *
 * @package EventProTicketsPlus
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$settings = get_option( 'eptp_settings', array() );

if ( 'yes' !== ( $settings['delete_data_on_uninstall'] ?? 'no' ) ) {
	delete_option( 'eptp_settings' );
	delete_option( 'eptp_version' );
	return;
}

global $wpdb;

delete_option( 'eptp_settings' );
delete_option( 'eptp_version' );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}eptp_checkin_logs" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$transient_pattern         = $wpdb->esc_like( '_transient_eptp_waitlist_' ) . '%';
$transient_timeout_pattern = $wpdb->esc_like( '_transient_timeout_eptp_waitlist_' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $transient_pattern, $transient_timeout_pattern ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

$roles = array( 'administrator', 'shop_manager' );
$caps  = array(
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

foreach ( $roles as $role_slug ) {
	$role = get_role( $role_slug );

	if ( ! $role ) {
		continue;
	}

	foreach ( $caps as $cap ) {
		$role->remove_cap( $cap );
	}
}

remove_role( 'eptp_staff' );

$post_types = array( 'eptp_event', 'eptp_venue', 'eptp_organizer', 'eptp_speaker', 'eptp_attendee', 'eptp_waitlist' );

foreach ( $post_types as $post_type ) {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}

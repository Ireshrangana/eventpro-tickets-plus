<?php
/**
 * Post type registration.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Registers plugin content types.
 */
class Post_Types {

	/**
	 * Register post types.
	 *
	 * @return void
	 */
	public function register() : void {
		$settings = eptp_get_plugin_settings();

		register_post_type(
			'eptp_event',
			array(
				'labels'            => array(
					'name'          => __( 'Events', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Event', 'eventpro-tickets-plus' ),
					'add_new_item'  => __( 'Add New Event', 'eventpro-tickets-plus' ),
					'edit_item'     => __( 'Edit Event', 'eventpro-tickets-plus' ),
				),
				'public'            => true,
				'show_in_rest'      => true,
				'menu_icon'         => 'dashicons-tickets-alt',
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => sanitize_title( $settings['event_slug'] ) ),
				'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
				'capability_type'   => array( 'eptp_event', 'eptp_events' ),
				'map_meta_cap'      => true,
				'capabilities'      => array(
					'read_post'              => 'read_eptp_event',
					'edit_post'              => 'edit_eptp_event',
					'delete_post'            => 'delete_eptp_event',
					'edit_posts'             => 'edit_eptp_events',
					'edit_others_posts'      => 'edit_others_eptp_events',
					'publish_posts'          => 'publish_eptp_events',
					'read_private_posts'     => 'read_private_eptp_events',
					'delete_posts'           => 'delete_eptp_events',
					'delete_private_posts'   => 'delete_private_eptp_events',
					'delete_published_posts' => 'delete_published_eptp_events',
					'delete_others_posts'    => 'delete_others_eptp_events',
					'edit_private_posts'     => 'edit_private_eptp_events',
					'edit_published_posts'   => 'edit_published_eptp_events',
					'create_posts'           => 'edit_eptp_events',
				),
				'show_in_menu'      => false,
				'publicly_queryable'=> true,
			)
		);

		register_post_type(
			'eptp_venue',
			array(
				'labels'       => array(
					'name'          => __( 'Venues', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Venue', 'eventpro-tickets-plus' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-location-alt',
				'rewrite'      => array( 'slug' => sanitize_title( $settings['venue_slug'] ) ),
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'show_in_menu' => false,
			)
		);

		register_post_type(
			'eptp_organizer',
			array(
				'labels'       => array(
					'name'          => __( 'Organizers', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Organizer', 'eventpro-tickets-plus' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-businessperson',
				'rewrite'      => array( 'slug' => sanitize_title( $settings['organizer_slug'] ) ),
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'show_in_menu' => false,
			)
		);

		register_post_type(
			'eptp_speaker',
			array(
				'labels'       => array(
					'name'          => __( 'Speakers', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Speaker', 'eventpro-tickets-plus' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-microphone',
				'rewrite'      => array( 'slug' => sanitize_title( $settings['speaker_slug'] ) ),
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'show_in_menu' => false,
			)
		);

		register_post_type(
			'eptp_attendee',
			array(
				'labels'        => array(
					'name'          => __( 'Attendees', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Attendee', 'eventpro-tickets-plus' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => false,
				'menu_icon'     => 'dashicons-id-alt',
				'supports'      => array( 'title' ),
				'show_in_menu'  => false,
				'capabilities'  => array(
					'edit_post'          => 'edit_eptp_attendees',
					'read_post'          => 'edit_eptp_attendees',
					'delete_post'        => 'edit_eptp_attendees',
					'edit_posts'         => 'edit_eptp_attendees',
					'edit_others_posts'  => 'edit_eptp_attendees',
					'publish_posts'      => 'edit_eptp_attendees',
					'read_private_posts' => 'edit_eptp_attendees',
					'create_posts'       => 'edit_eptp_attendees',
				),
				'map_meta_cap'   => false,
			)
		);

		register_post_type(
			'eptp_waitlist',
			array(
				'labels'        => array(
					'name'          => __( 'Waitlist Entries', 'eventpro-tickets-plus' ),
					'singular_name' => __( 'Waitlist Entry', 'eventpro-tickets-plus' ),
				),
				'public'        => false,
				'show_ui'       => false,
				'supports'      => array( 'title' ),
				'show_in_menu'  => false,
			)
		);
	}
}

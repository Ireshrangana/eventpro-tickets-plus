<?php
/**
 * Helper functions.
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'eptp_get_settings_defaults' ) ) {
	/**
	 * Default plugin settings.
	 *
	 * @return array<string, mixed>
	 */
	function eptp_get_settings_defaults() : array {
		$currency_display = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : get_option( 'woocommerce_currency', 'USD' );

		$defaults = array(
			'brand_color'          => '#1358db',
			'accent_color'         => '#0d9488',
			'page_background_color'=> '#f6f8fc',
			'panel_background_color'=> '#ffffff',
			'heading_color'        => '#0b1324',
			'body_text_color'      => '#142033',
			'muted_text_color'     => '#64748b',
			'heading_font'         => 'modern-sans',
			'body_font'            => 'clean-sans',
			'frontend_shell_width' => 1240,
			'single_shell_width'   => 1320,
			'content_text_width'   => 760,
			'section_spacing'      => 28,
			'card_radius'          => 22,
			'archive_columns'      => '2',
			'shortcode_columns'    => '2',
			'category_columns'     => '2',
			'admin_surface_mode'   => 'light',
			'frontend_surface_mode'=> 'light',
			'ticket_logo_id'       => 0,
			'ticket_footer_text'   => __( 'Thank you for booking with Synbus Inc. EventPro Tickets Plus.', 'eventpro-tickets-plus' ),
			'pdf_paper_size'       => 'A4',
			'staff_role_slug'      => 'eptp_staff',
			'checkin_mode'         => 'realtime',
			'enable_schema'        => 'yes',
			'enable_my_account'    => 'yes',
			'enable_blocks'        => 'yes',
			'enable_csv_exports'   => 'yes',
			'enable_pdf_downloads' => 'yes',
			'enable_qr_tickets'    => 'yes',
			'event_slug'           => 'events',
			'venue_slug'           => 'venues',
			'organizer_slug'       => 'organizers',
			'speaker_slug'         => 'speakers',
			'tickets_endpoint'     => 'tickets',
			'email_from_name'      => __( 'Synbus Inc.', 'eventpro-tickets-plus' ),
			'email_reply_to'       => get_option( 'admin_email' ),
			'support_url'          => 'https://www.synbus.ph/',
			'company_url'          => 'https://www.synbus.ph/',
			'waitlist_enabled'     => 'yes',
			'archive_layout'       => 'grid',
			'currency_display'     => $currency_display,
			'delete_data_on_uninstall' => 'no',
		);

		return apply_filters( 'eventpro_tickets_plus_settings_defaults', $defaults );
	}
}

if ( ! function_exists( 'eptp_get_event_defaults' ) ) {
	/**
	 * Event meta defaults.
	 *
	 * @return array<string, mixed>
	 */
	function eptp_get_event_defaults() : array {
		$defaults = array(
			'event_type'            => 'single-day',
			'event_mode'            => 'physical',
			'status'                => 'scheduled',
			'timezone'              => wp_timezone_string() ? wp_timezone_string() : 'UTC',
			'start_date'            => '',
			'end_date'              => '',
			'recurrence_rule'       => '',
			'virtual_url'           => '',
			'venue_id'              => 0,
			'organizer_ids'         => array(),
			'speaker_ids'           => array(),
			'sales_start'           => '',
			'sales_end'             => '',
			'capacity'              => 0,
			'featured_label'        => '',
			'hero_badge'            => '',
			'ui_style'              => 'summit',
			'hero_media'            => '',
			'map_url'               => '',
			'cta_label'             => __( 'Get Tickets', 'eventpro-tickets-plus' ),
			'waitlist_enabled'      => 'yes',
			'registration_fields'   => array(),
			'ticket_tiers'          => array(),
			'agenda'                => array(),
			'faq_items'             => array(),
			'sponsors'              => array(),
			'gallery_ids'           => array(),
			'related_event_ids'     => array(),
			'reminder_offsets'      => array( '1440', '120' ),
			'seat_map_provider'     => '',
		);

		return apply_filters( 'eventpro_tickets_plus_event_meta_defaults', $defaults );
	}
}

if ( ! function_exists( 'eptp_array_get' ) ) {
	/**
	 * Safe array getter.
	 *
	 * @param array<string, mixed> $array Source array.
	 * @param string               $key   Key name.
	 * @param mixed                $default Default.
	 * @return mixed
	 */
	function eptp_array_get( array $array, string $key, mixed $default = '' ) : mixed {
		return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
	}
}

if ( ! function_exists( 'eptp_sanitize_checkbox' ) ) {
	/**
	 * Sanitize checkbox style values.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	function eptp_sanitize_checkbox( mixed $value ) : string {
		return in_array( (string) $value, array( 'yes', '1', 'true' ), true ) ? 'yes' : 'no';
	}
}

if ( ! function_exists( 'eptp_sanitize_allowed_value' ) ) {
	/**
	 * Sanitize value against allow list.
	 *
	 * @param mixed             $value Raw value.
	 * @param array<int,string> $allowed Allowed values.
	 * @param string            $default Default.
	 * @return string
	 */
	function eptp_sanitize_allowed_value( mixed $value, array $allowed, string $default ) : string {
		$value = sanitize_text_field( (string) $value );

		return in_array( $value, $allowed, true ) ? $value : $default;
	}
}

if ( ! function_exists( 'eptp_sanitize_datetime' ) ) {
	/**
	 * Sanitize datetime-local values.
	 *
	 * @param string $value Datetime string.
	 * @return string
	 */
	function eptp_sanitize_datetime( string $value ) : string {
		$value = sanitize_text_field( $value );

		return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ? $value : '';
	}
}

if ( ! function_exists( 'eptp_sanitize_id_list' ) ) {
	/**
	 * Sanitize integer lists.
	 *
	 * @param mixed $ids Raw IDs.
	 * @return array<int, int>
	 */
	function eptp_sanitize_id_list( mixed $ids ) : array {
		$ids = is_array( $ids ) ? $ids : array();

		return array_values(
			array_filter(
				array_map( 'absint', $ids )
			)
		);
	}
}

if ( ! function_exists( 'eptp_sanitize_repeater_rows' ) ) {
	/**
	 * Sanitize repeater rows by schema.
	 *
	 * @param mixed                 $rows   Rows.
	 * @param array<string, string> $schema Field schema.
	 * @return array<int, array<string, mixed>>
	 */
	function eptp_sanitize_repeater_rows( mixed $rows, array $schema ) : array {
		$rows = is_array( $rows ) ? $rows : array();
		$clean = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$item = array();

			foreach ( $schema as $field => $type ) {
				$value = $row[ $field ] ?? '';

				switch ( $type ) {
					case 'email':
						$item[ $field ] = sanitize_email( (string) $value );
						break;
					case 'url':
						$item[ $field ] = esc_url_raw( (string) $value );
						break;
					case 'textarea':
						$item[ $field ] = sanitize_textarea_field( (string) $value );
						break;
					case 'int':
						$item[ $field ] = absint( $value );
						break;
					case 'float':
						$item[ $field ] = wc_format_decimal( (string) $value );
						break;
					case 'bool':
						$item[ $field ] = eptp_sanitize_checkbox( $value );
						break;
					case 'datetime':
						$item[ $field ] = eptp_sanitize_datetime( (string) $value );
						break;
					default:
						$item[ $field ] = sanitize_text_field( (string) $value );
						break;
				}
			}

			if ( array_filter( $item, static fn( $value ) => '' !== $value && array() !== $value && 0 !== $value ) ) {
				$clean[] = $item;
			}
		}

		return array_values( $clean );
	}
}

if ( ! function_exists( 'eptp_get_plugin_settings' ) ) {
	/**
	 * Get normalized plugin settings.
	 *
	 * @return array<string, mixed>
	 */
	function eptp_get_plugin_settings() : array {
		$saved = get_option( 'eptp_settings', array() );

		return wp_parse_args( is_array( $saved ) ? $saved : array(), eptp_get_settings_defaults() );
	}
}

if ( ! function_exists( 'eptp_get_event_meta' ) ) {
	/**
	 * Get normalized event data.
	 *
	 * @param int $event_id Event ID.
	 * @return array<string, mixed>
	 */
	function eptp_get_event_meta( int $event_id ) : array {
		$saved = get_post_meta( $event_id, '_eptp_event_data', true );

		return wp_parse_args( is_array( $saved ) ? $saved : array(), eptp_get_event_defaults() );
	}
}

if ( ! function_exists( 'eptp_generate_ticket_code' ) ) {
	/**
	 * Generate a unique ticket code.
	 *
	 * @param int $event_id Event ID.
	 * @param int $order_id Order ID.
	 * @return string
	 */
	function eptp_generate_ticket_code( int $event_id, int $order_id ) : string {
		$seed = wp_generate_password( 10, false, false );

		return strtoupper( sprintf( 'EPTP-%d-%d-%s', $event_id, $order_id, $seed ) );
	}
}

if ( ! function_exists( 'eptp_get_navigation_menu_id' ) ) {
	/**
	 * Locate the best available navigation menu for event templates.
	 *
	 * @return int
	 */
	function eptp_get_navigation_menu_id() : int {
		$locations           = get_nav_menu_locations();
		$preferred_locations = array( 'primary', 'menu-1', 'header', 'main', 'top' );

		foreach ( $preferred_locations as $location ) {
			if ( ! empty( $locations[ $location ] ) ) {
				return absint( $locations[ $location ] );
			}
		}

		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			if ( ! empty( wp_get_nav_menu_items( $menu->term_id ) ) ) {
				return absint( $menu->term_id );
			}
		}

		return 0;
	}
}

if ( ! function_exists( 'eptp_render_site_header' ) ) {
	/**
	 * Render a consistent event-page header with the site's main navigation.
	 *
	 * @return void
	 */
	function eptp_render_site_header() : void {
		$menu_id = eptp_get_navigation_menu_id();
		?>
		<header class="eptp-site-header" aria-label="<?php esc_attr_e( 'Event page header', 'eventpro-tickets-plus' ); ?>">
			<div class="eptp-site-header__inner">
				<div class="eptp-site-header__brand">
					<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
						<div class="eptp-site-header__logo"><?php echo wp_kses_post( get_custom_logo() ); ?></div>
					<?php else : ?>
						<a class="eptp-site-header__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
					<?php endif; ?>
				</div>

				<?php if ( $menu_id > 0 ) : ?>
					<?php
					wp_nav_menu(
						array(
							'menu'            => $menu_id,
							'container'       => 'nav',
							'container_class' => 'eptp-site-header__nav',
							'container_aria_label' => __( 'Primary menu', 'eventpro-tickets-plus' ),
							'menu_class'      => 'eptp-site-header__menu',
							'depth'           => 2,
							'fallback_cb'     => false,
						)
					);
					?>
				<?php else : ?>
					<nav class="eptp-site-header__nav" aria-label="<?php esc_attr_e( 'Primary menu', 'eventpro-tickets-plus' ); ?>">
						<ul class="eptp-site-header__menu">
							<?php
							echo wp_kses_post(
								wp_list_pages(
									array(
										'title_li' => '',
										'echo'     => 0,
										'depth'    => 1,
									)
								)
							);
							?>
						</ul>
					</nav>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}
}

if ( ! function_exists( 'eptp_get_ticket_type_options' ) ) {
	/**
	 * Get supported ticket types.
	 *
	 * @return array<string,string>
	 */
	function eptp_get_ticket_type_options() : array {
		return array(
			'general-admission' => __( 'General Admission', 'eventpro-tickets-plus' ),
			'vip'               => __( 'VIP', 'eventpro-tickets-plus' ),
			'early-bird'        => __( 'Early Bird', 'eventpro-tickets-plus' ),
			'group-ticket'      => __( 'Group Ticket', 'eventpro-tickets-plus' ),
			'free-ticket'       => __( 'Free Ticket', 'eventpro-tickets-plus' ),
			'add-on-ticket'     => __( 'Add-on Ticket', 'eventpro-tickets-plus' ),
			'hidden-private'    => __( 'Hidden / Private', 'eventpro-tickets-plus' ),
		);
	}
}

if ( ! function_exists( 'eptp_get_font_family_options' ) ) {
	/**
	 * Get safe frontend font stack options for plugin pages.
	 *
	 * @return array<string,string>
	 */
	function eptp_get_font_family_options() : array {
		return array(
			'modern-sans' => __( 'Modern Sans', 'eventpro-tickets-plus' ),
			'clean-sans'  => __( 'Clean Sans', 'eventpro-tickets-plus' ),
			'editorial'   => __( 'Editorial Sans', 'eventpro-tickets-plus' ),
			'humanist'    => __( 'Humanist Sans', 'eventpro-tickets-plus' ),
		);
	}
}

if ( ! function_exists( 'eptp_generate_download_token' ) ) {
	/**
	 * Generate a signed token for a ticket download.
	 *
	 * @param int $attendee_id Attendee ID.
	 * @return string
	 */
	function eptp_generate_download_token( int $attendee_id ) : string {
		$ticket_code = (string) get_post_meta( $attendee_id, '_eptp_ticket_code', true );

		return hash_hmac( 'sha256', $attendee_id . '|' . $ticket_code, wp_salt( 'auth' ) );
	}
}

if ( ! function_exists( 'eptp_verify_download_token' ) ) {
	/**
	 * Verify a ticket download token.
	 *
	 * @param int    $attendee_id Attendee ID.
	 * @param string $token Token.
	 * @return bool
	 */
	function eptp_verify_download_token( int $attendee_id, string $token ) : bool {
		if ( empty( $token ) ) {
			return false;
		}

		return hash_equals( eptp_generate_download_token( $attendee_id ), sanitize_text_field( $token ) );
	}
}

if ( ! function_exists( 'eptp_get_waitlist_rate_limit_key' ) ) {
	/**
	 * Get transient key for waitlist submissions.
	 *
	 * @param int    $event_id Event ID.
	 * @param string $email Email.
	 * @return string
	 */
	function eptp_get_waitlist_rate_limit_key( int $event_id, string $email ) : string {
		return 'eptp_waitlist_' . md5( $event_id . '|' . strtolower( $email ) );
	}
}

if ( ! function_exists( 'eptp_format_datetime' ) ) {
	/**
	 * Format local datetime string.
	 *
	 * @param string $value Datetime.
	 * @return string
	 */
	function eptp_format_datetime( string $value ) : string {
		if ( empty( $value ) ) {
			return '';
		}

		$timestamp = strtotime( $value );

		return $timestamp ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) : '';
	}
}

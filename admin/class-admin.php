<?php
/**
 * Admin experience.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus\Admin;

use EventPro\TicketsPlus\Attendee_Service;
use EventPro\TicketsPlus\Checkin_Service;
use EventPro\TicketsPlus\Event_Service;
use EventPro\TicketsPlus\Ticket_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Admin controller.
 */
class Admin {

	/**
	 * Event service.
	 *
	 * @var Event_Service
	 */
	protected Event_Service $events;

	/**
	 * Ticket service.
	 *
	 * @var Ticket_Service
	 */
	protected Ticket_Service $tickets;

	/**
	 * Attendee service.
	 *
	 * @var Attendee_Service
	 */
	protected Attendee_Service $attendees;

	/**
	 * Check-in service.
	 *
	 * @var Checkin_Service
	 */
	protected Checkin_Service $checkin;

	/**
	 * Constructor.
	 *
	 * @param Event_Service    $events Events.
	 * @param Ticket_Service   $tickets Tickets.
	 * @param Attendee_Service $attendees Attendees.
	 * @param Checkin_Service  $checkin Checkin.
	 */
	public function __construct( Event_Service $events, Ticket_Service $tickets, Attendee_Service $attendees, Checkin_Service $checkin ) {
		$this->events    = $events;
		$this->tickets   = $tickets;
		$this->attendees = $attendees;
		$this->checkin   = $checkin;
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_notices', array( $this, 'render_notices' ) );
		add_filter( 'manage_eptp_attendee_posts_columns', array( $this, 'attendee_columns' ) );
		add_action( 'manage_eptp_attendee_posts_custom_column', array( $this, 'render_attendee_column' ), 10, 2 );
		add_action( 'admin_post_eptp_export_attendees', array( $this, 'export_attendees' ) );
		add_action( 'admin_post_eptp_create_demo_content', array( $this, 'create_demo_content' ) );
		add_action( 'admin_post_eptp_delete_demo_content', array( $this, 'delete_demo_content' ) );
		add_action( 'admin_post_eptp_create_demo_pages', array( $this, 'create_demo_pages' ) );
	}

	/**
	 * Register menus.
	 *
	 * @return void
	 */
	public function register_menus() : void {
		$cap = 'manage_eventpro_tickets_plus';

		add_menu_page(
			__( 'EventPro Tickets Plus', 'eventpro-tickets-plus' ),
			__( 'EventPro Tickets', 'eventpro-tickets-plus' ),
			$cap,
			'eptp-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-tickets-alt',
			56
		);

		add_submenu_page( 'eptp-dashboard', __( 'Dashboard', 'eventpro-tickets-plus' ), __( 'Dashboard', 'eventpro-tickets-plus' ), $cap, 'eptp-dashboard', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'eptp-dashboard', __( 'Events', 'eventpro-tickets-plus' ), __( 'Events', 'eventpro-tickets-plus' ), $cap, 'edit.php?post_type=eptp_event' );
		add_submenu_page( 'eptp-dashboard', __( 'Venues', 'eventpro-tickets-plus' ), __( 'Venues', 'eventpro-tickets-plus' ), $cap, 'edit.php?post_type=eptp_venue' );
		add_submenu_page( 'eptp-dashboard', __( 'Organizers', 'eventpro-tickets-plus' ), __( 'Organizers', 'eventpro-tickets-plus' ), $cap, 'edit.php?post_type=eptp_organizer' );
		add_submenu_page( 'eptp-dashboard', __( 'Speakers', 'eventpro-tickets-plus' ), __( 'Speakers', 'eventpro-tickets-plus' ), $cap, 'edit.php?post_type=eptp_speaker' );
		add_submenu_page( 'eptp-dashboard', __( 'Attendees', 'eventpro-tickets-plus' ), __( 'Attendees', 'eventpro-tickets-plus' ), $cap, 'edit.php?post_type=eptp_attendee' );
		add_submenu_page( 'eptp-dashboard', __( 'Check-In', 'eventpro-tickets-plus' ), __( 'Check-In', 'eventpro-tickets-plus' ), 'manage_eptp_checkins', 'eptp-checkin', array( $this, 'render_checkin' ) );
		add_submenu_page( 'eptp-dashboard', __( 'Settings', 'eventpro-tickets-plus' ), __( 'Settings', 'eventpro-tickets-plus' ), $cap, 'eptp-settings', array( $this, 'render_settings' ) );
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() : void {
		register_setting(
			'eptp_settings',
			'eptp_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'eptp_general',
			__( 'General Brand and Experience Settings', 'eventpro-tickets-plus' ),
			'__return_null',
			'eptp-settings'
		);

		$general_fields = array(
			'brand_color'        => __( 'Brand Color', 'eventpro-tickets-plus' ),
			'accent_color'       => __( 'Accent Color', 'eventpro-tickets-plus' ),
			'page_background_color' => __( 'Page Background Color', 'eventpro-tickets-plus' ),
			'panel_background_color' => __( 'Panel Background Color', 'eventpro-tickets-plus' ),
			'heading_color'      => __( 'Heading Color', 'eventpro-tickets-plus' ),
			'body_text_color'    => __( 'Body Text Color', 'eventpro-tickets-plus' ),
			'muted_text_color'   => __( 'Muted Text Color', 'eventpro-tickets-plus' ),
			'heading_font'       => __( 'Heading Font Style', 'eventpro-tickets-plus' ),
			'body_font'          => __( 'Body Font Style', 'eventpro-tickets-plus' ),
			'email_from_name'    => __( 'Email From Name', 'eventpro-tickets-plus' ),
			'email_reply_to'     => __( 'Reply-To Email', 'eventpro-tickets-plus' ),
			'ticket_footer_text' => __( 'Ticket Footer', 'eventpro-tickets-plus' ),
			'tickets_endpoint'   => __( 'My Account Endpoint', 'eventpro-tickets-plus' ),
			'delete_data_on_uninstall' => __( 'Delete Data on Uninstall', 'eventpro-tickets-plus' ),
		);

		foreach ( $general_fields as $key => $label ) {
			add_settings_field(
				$key,
				$label,
				array( $this, 'render_setting_field' ),
				'eptp-settings',
				'eptp_general',
				array( 'key' => $key )
			);
		}

		add_settings_section(
			'eptp_frontend_design',
			__( 'Frontend Design Controls', 'eventpro-tickets-plus' ),
			array( $this, 'render_frontend_design_intro' ),
			'eptp-settings'
		);

		$design_fields = array(
			'frontend_shell_width' => __( 'Landing Page Width', 'eventpro-tickets-plus' ),
			'single_shell_width'   => __( 'Single Event Width', 'eventpro-tickets-plus' ),
			'content_text_width'   => __( 'Text Content Width', 'eventpro-tickets-plus' ),
			'section_spacing'      => __( 'Section Spacing', 'eventpro-tickets-plus' ),
			'card_radius'          => __( 'Card Radius', 'eventpro-tickets-plus' ),
			'archive_columns'      => __( 'Archive Grid Columns', 'eventpro-tickets-plus' ),
			'shortcode_columns'    => __( 'Shortcode Grid Columns', 'eventpro-tickets-plus' ),
			'category_columns'     => __( 'Category Grid Columns', 'eventpro-tickets-plus' ),
		);

		foreach ( $design_fields as $key => $label ) {
			add_settings_field(
				$key,
				$label,
				array( $this, 'render_setting_field' ),
				'eptp-settings',
				'eptp_frontend_design',
				array( 'key' => $key )
			);
		}
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array<string,mixed> $input Input.
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( array $input ) : array {
		$current = eptp_get_plugin_settings();

		$current['brand_color']        = sanitize_hex_color( $input['brand_color'] ?? $current['brand_color'] ) ?: '#1358db';
		$current['accent_color']       = sanitize_hex_color( $input['accent_color'] ?? $current['accent_color'] ) ?: '#0d9488';
		$current['page_background_color'] = sanitize_hex_color( $input['page_background_color'] ?? $current['page_background_color'] ) ?: '#f6f8fc';
		$current['panel_background_color'] = sanitize_hex_color( $input['panel_background_color'] ?? $current['panel_background_color'] ) ?: '#ffffff';
		$current['heading_color']      = sanitize_hex_color( $input['heading_color'] ?? $current['heading_color'] ) ?: '#0b1324';
		$current['body_text_color']    = sanitize_hex_color( $input['body_text_color'] ?? $current['body_text_color'] ) ?: '#142033';
		$current['muted_text_color']   = sanitize_hex_color( $input['muted_text_color'] ?? $current['muted_text_color'] ) ?: '#64748b';
		$current['heading_font']       = eptp_sanitize_allowed_value( $input['heading_font'] ?? $current['heading_font'], array_keys( eptp_get_font_family_options() ), 'modern-sans' );
		$current['body_font']          = eptp_sanitize_allowed_value( $input['body_font'] ?? $current['body_font'], array_keys( eptp_get_font_family_options() ), 'clean-sans' );
		$current['frontend_shell_width'] = max( 960, min( 1600, absint( $input['frontend_shell_width'] ?? $current['frontend_shell_width'] ) ) );
		$current['single_shell_width']   = max( 1100, min( 1700, absint( $input['single_shell_width'] ?? $current['single_shell_width'] ) ) );
		$current['content_text_width']   = max( 560, min( 920, absint( $input['content_text_width'] ?? $current['content_text_width'] ) ) );
		$current['section_spacing']      = max( 20, min( 64, absint( $input['section_spacing'] ?? $current['section_spacing'] ) ) );
		$current['card_radius']          = max( 16, min( 36, absint( $input['card_radius'] ?? $current['card_radius'] ) ) );
		$current['archive_columns']      = eptp_sanitize_allowed_value( $input['archive_columns'] ?? $current['archive_columns'], array( '2', '3' ), '2' );
		$current['shortcode_columns']    = eptp_sanitize_allowed_value( $input['shortcode_columns'] ?? $current['shortcode_columns'], array( '2', '3' ), '2' );
		$current['category_columns']     = eptp_sanitize_allowed_value( $input['category_columns'] ?? $current['category_columns'], array( '2', '3' ), '2' );
		$current['email_from_name']    = sanitize_text_field( $input['email_from_name'] ?? '' );
		$current['email_reply_to']     = sanitize_email( $input['email_reply_to'] ?? '' );
		$current['ticket_footer_text'] = sanitize_textarea_field( $input['ticket_footer_text'] ?? '' );
		$current['tickets_endpoint']   = sanitize_title( $input['tickets_endpoint'] ?? 'tickets' );
		$current['delete_data_on_uninstall'] = eptp_sanitize_checkbox( $input['delete_data_on_uninstall'] ?? 'no' );

		return $current;
	}

	/**
	 * Render input field.
	 *
	 * @param array<string,string> $args Args.
	 * @return void
	 */
	public function render_setting_field( array $args ) : void {
		$settings = eptp_get_plugin_settings();
		$key      = $args['key'];
		$value    = $settings[ $key ] ?? '';

		if ( false !== strpos( $key, 'color' ) ) {
			printf( '<input type="text" class="eptp-color-field" name="eptp_settings[%1$s]" value="%2$s">', esc_attr( $key ), esc_attr( (string) $value ) );
			return;
		}

		if ( in_array( $key, array( 'heading_font', 'body_font' ), true ) ) {
			$options = eptp_get_font_family_options();
			?>
			<select name="eptp_settings[<?php echo esc_attr( $key ); ?>]">
				<?php foreach ( $options as $option_value => $label ) : ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( (string) $value, $option_value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php echo esc_html( $this->get_field_description( $key ) ); ?></span>
			<?php
			return;
		}

		if ( 'ticket_footer_text' === $key ) {
			printf( '<textarea name="eptp_settings[%1$s]" rows="4" class="large-text">%2$s</textarea>', esc_attr( $key ), esc_textarea( (string) $value ) );
			return;
		}

		if ( 'delete_data_on_uninstall' === $key ) {
			printf(
				'<label><input type="checkbox" name="eptp_settings[%1$s]" value="yes" %2$s> %3$s</label>',
				esc_attr( $key ),
				checked( 'yes', (string) $value, false ),
				esc_html__( 'Remove plugin data, attendee records, and check-in logs when uninstalling.', 'eventpro-tickets-plus' )
			);
			return;
		}

		if ( in_array( $key, array( 'frontend_shell_width', 'single_shell_width', 'content_text_width', 'section_spacing', 'card_radius' ), true ) ) {
			printf(
				'<input type="number" name="eptp_settings[%1$s]" value="%2$s" class="small-text" min="%3$s" max="%4$s" step="1"> <span class="description">%5$s</span>',
				esc_attr( $key ),
				esc_attr( (string) $value ),
				esc_attr( (string) $this->get_number_field_min( $key ) ),
				esc_attr( (string) $this->get_number_field_max( $key ) ),
				esc_html( $this->get_field_description( $key ) )
			);
			return;
		}

		if ( in_array( $key, array( 'archive_columns', 'shortcode_columns', 'category_columns' ), true ) ) {
			?>
			<select name="eptp_settings[<?php echo esc_attr( $key ); ?>]">
				<option value="2" <?php selected( (string) $value, '2' ); ?>><?php esc_html_e( '2 Columns', 'eventpro-tickets-plus' ); ?></option>
				<option value="3" <?php selected( (string) $value, '3' ); ?>><?php esc_html_e( '3 Columns', 'eventpro-tickets-plus' ); ?></option>
			</select>
			<span class="description"><?php echo esc_html( $this->get_field_description( $key ) ); ?></span>
			<?php
			return;
		}

		printf( '<input type="text" name="eptp_settings[%1$s]" value="%2$s" class="regular-text">', esc_attr( $key ), esc_attr( (string) $value ) );
	}

	/**
	 * Render intro text for frontend design controls.
	 *
	 * @return void
	 */
	public function render_frontend_design_intro() : void {
		echo '<p>' . esc_html__( 'Let clients tune the premade frontend pages without touching code. These controls adjust shell widths, text measure, spacing, card style, and archive column layouts.', 'eventpro-tickets-plus' ) . '</p>';
	}

	/**
	 * Get field helper description.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	protected function get_field_description( string $key ) : string {
		$descriptions = array(
			'page_background_color' => __( 'Background color behind the plugin’s frontend pages and shells.', 'eventpro-tickets-plus' ),
			'panel_background_color' => __( 'Surface color used by plugin cards, panels, and content shells.', 'eventpro-tickets-plus' ),
			'heading_color'        => __( 'Main heading color for plugin-controlled frontend sections.', 'eventpro-tickets-plus' ),
			'body_text_color'      => __( 'Primary paragraph and body text color for plugin pages.', 'eventpro-tickets-plus' ),
			'muted_text_color'     => __( 'Muted tone for metadata, helper copy, and secondary descriptions.', 'eventpro-tickets-plus' ),
			'heading_font'         => __( 'Choose the heading style for plugin-driven frontend layouts only.', 'eventpro-tickets-plus' ),
			'body_font'            => __( 'Choose the body text style for plugin-driven frontend layouts only.', 'eventpro-tickets-plus' ),
			'frontend_shell_width' => __( 'Controls the main width of archive and landing page shells in pixels.', 'eventpro-tickets-plus' ),
			'single_shell_width'   => __( 'Controls the overall width of single event pages in pixels.', 'eventpro-tickets-plus' ),
			'content_text_width'   => __( 'Sets the maximum readable width for hero text and intro copy.', 'eventpro-tickets-plus' ),
			'section_spacing'      => __( 'Adjusts vertical spacing between major frontend sections in pixels.', 'eventpro-tickets-plus' ),
			'card_radius'          => __( 'Controls how rounded cards, panels, and pills feel across the frontend.', 'eventpro-tickets-plus' ),
			'archive_columns'      => __( 'Choose how many event cards appear per row on the main archive.', 'eventpro-tickets-plus' ),
			'shortcode_columns'    => __( 'Choose how many cards appear on shortcode-based event pages.', 'eventpro-tickets-plus' ),
			'category_columns'     => __( 'Choose how many cards appear on event category archive pages.', 'eventpro-tickets-plus' ),
		);

		return $descriptions[ $key ] ?? '';
	}

	/**
	 * Get number field minimum.
	 *
	 * @param string $key Setting key.
	 * @return int
	 */
	protected function get_number_field_min( string $key ) : int {
		$mins = array(
			'frontend_shell_width' => 960,
			'single_shell_width'   => 1100,
			'content_text_width'   => 560,
			'section_spacing'      => 20,
			'card_radius'          => 16,
		);

		return $mins[ $key ] ?? 0;
	}

	/**
	 * Get number field maximum.
	 *
	 * @param string $key Setting key.
	 * @return int
	 */
	protected function get_number_field_max( string $key ) : int {
		$maxes = array(
			'frontend_shell_width' => 1600,
			'single_shell_width'   => 1700,
			'content_text_width'   => 920,
			'section_spacing'      => 64,
			'card_radius'          => 36,
		);

		return $maxes[ $key ] ?? 0;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Hook.
	 * @return void
	 */
	public function enqueue( string $hook ) : void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$eventpro_post_types = array( 'eptp_event', 'eptp_attendee', 'eptp_venue', 'eptp_organizer', 'eptp_speaker' );
		$is_eventpro_screen = $screen && in_array( (string) $screen->post_type, $eventpro_post_types, true );

		if ( ! str_contains( $hook, 'eptp' ) && 'post.php' !== $hook && 'post-new.php' !== $hook && ! $is_eventpro_screen ) {
			return;
		}

		wp_enqueue_style( 'eptp-admin' );
		wp_enqueue_script( 'eptp-admin' );
		wp_localize_script(
			'eptp-admin',
			'eptpAdmin',
			array(
				'restUrl' => esc_url_raw( rest_url( 'eventpro-tickets-plus/v1/checkin' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Render dashboard.
	 *
	 * @return void
	 */
	public function render_dashboard() : void {
		if ( ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'eventpro-tickets-plus' ) );
		}

		$stats = array(
			'events'         => wp_count_posts( 'eptp_event' )->publish ?? 0,
			'attendees'      => wp_count_posts( 'eptp_attendee' )->publish ?? 0,
			'orders'         => count( wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) ) ),
			'revenue'        => wc_price( $this->calculate_revenue() ),
			'checkins_today' => $this->count_today_checkins(),
		);
		$events = $this->events->get_events( array( 'posts_per_page' => 5 ) );
		$demo_import_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=eptp_create_demo_content' ),
			'eptp_create_demo_content'
		);
		$demo_delete_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=eptp_delete_demo_content' ),
			'eptp_delete_demo_content'
		);
		$demo_pages_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=eptp_create_demo_pages' ),
			'eptp_create_demo_pages'
		);

		include EPTP_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render admin notices for utility actions.
	 *
	 * @return void
	 */
	public function render_notices() : void {
		if ( ! isset( $_GET['page'] ) || 'eptp-dashboard' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		$status = isset( $_GET['eptp_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['eptp_notice'] ) ) : '';

		if ( 'demo-created' === $status ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Demo content created successfully. Review the sample events, products, and frontend layouts now.', 'eventpro-tickets-plus' ) . '</p></div>';
		}

		if ( 'demo-failed' === $status ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Demo content could not be created. Please confirm WooCommerce is active and try again.', 'eventpro-tickets-plus' ) . '</p></div>';
		}

		if ( 'demo-removed' === $status ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Demo content removed successfully. Your real event data was left untouched.', 'eventpro-tickets-plus' ) . '</p></div>';
		}

		if ( 'pages-created' === $status ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Frontend pages created successfully. Review the Events, Event Categories, and category-specific pages from Pages > All Pages.', 'eventpro-tickets-plus' ) . '</p></div>';
		}

		if ( 'pages-failed' === $status ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Frontend pages could not be created. Please try again.', 'eventpro-tickets-plus' ) . '</p></div>';
		}
	}

	/**
	 * Render check-in page.
	 *
	 * @return void
	 */
	public function render_checkin() : void {
		if ( ! current_user_can( 'manage_eptp_checkins' ) && ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'eventpro-tickets-plus' ) );
		}
		?>
		<div class="wrap eptp-wrap">
			<div class="eptp-page-header">
				<div>
					<p class="eptp-kicker"><?php esc_html_e( 'Entry Operations', 'eventpro-tickets-plus' ); ?></p>
					<h1><?php esc_html_e( 'Staff Check-In Console', 'eventpro-tickets-plus' ); ?></h1>
					<p><?php esc_html_e( 'Search by ticket code, attendee email, or WooCommerce order number.', 'eventpro-tickets-plus' ); ?></p>
				</div>
			</div>
			<div class="eptp-dashboard-grid">
				<div class="eptp-checkin-shell">
					<label class="screen-reader-text" for="eptp-checkin-query"><?php esc_html_e( 'Check-in query', 'eventpro-tickets-plus' ); ?></label>
					<input type="text" id="eptp-checkin-query" class="regular-text" placeholder="<?php esc_attr_e( 'Scan or enter code…', 'eventpro-tickets-plus' ); ?>" autocomplete="off">
					<label><input type="checkbox" id="eptp-checkin-override"> <?php esc_html_e( 'Allow override if already checked in', 'eventpro-tickets-plus' ); ?></label>
					<button type="button" class="button button-primary" id="eptp-checkin-submit"><?php esc_html_e( 'Validate Ticket', 'eventpro-tickets-plus' ); ?></button>
					<div id="eptp-checkin-result" class="eptp-checkin-result" aria-live="polite"></div>
				</div>
				<section class="eptp-card">
					<h2><?php esc_html_e( 'Operator Tips', 'eventpro-tickets-plus' ); ?></h2>
					<ul class="eptp-feature-list">
						<li><?php esc_html_e( 'Paste ticket codes directly from emails or scan values from mobile passes.', 'eventpro-tickets-plus' ); ?></li>
						<li><?php esc_html_e( 'Use order numbers when attendees arrive without their QR code.', 'eventpro-tickets-plus' ); ?></li>
						<li><?php esc_html_e( 'Override should be used only by admins when duplicate scans are legitimate.', 'eventpro-tickets-plus' ); ?></li>
					</ul>
				</section>
			</div>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings() : void {
		if ( ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'eventpro-tickets-plus' ) );
		}
		?>
		<div class="wrap eptp-wrap">
			<div class="eptp-page-header">
				<div>
					<h1><?php esc_html_e( 'EventPro Tickets Plus Settings', 'eventpro-tickets-plus' ); ?></h1>
					<p><?php esc_html_e( 'Control branding, customer ticket delivery, and premium experience defaults for your Synbus Inc. event operations stack.', 'eventpro-tickets-plus' ); ?></p>
				</div>
			</div>
			<form action="options.php" method="post" class="eptp-settings-form">
				<?php
				settings_fields( 'eptp_settings' );
				do_settings_sections( 'eptp-settings' );
				submit_button( __( 'Save Settings', 'eventpro-tickets-plus' ) );
				?>
			</form>
			<p class="eptp-admin-footer"><?php esc_html_e( 'Need product or deployment support? Visit Synbus Inc.', 'eventpro-tickets-plus' ); ?> <a href="<?php echo esc_url( 'https://www.synbus.ph/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'www.synbus.ph', 'eventpro-tickets-plus' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Attendee table columns.
	 *
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public function attendee_columns( array $columns ) : array {
		return array(
			'cb'       => $columns['cb'],
			'title'    => __( 'Attendee', 'eventpro-tickets-plus' ),
			'event'    => __( 'Event', 'eventpro-tickets-plus' ),
			'code'     => __( 'Ticket Code', 'eventpro-tickets-plus' ),
			'status'   => __( 'Status', 'eventpro-tickets-plus' ),
			'checkin'  => __( 'Checked In', 'eventpro-tickets-plus' ),
			'date'     => __( 'Date', 'eventpro-tickets-plus' ),
		);
	}

	/**
	 * Render attendee column.
	 *
	 * @param string $column Column.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_attendee_column( string $column, int $post_id ) : void {
		switch ( $column ) {
			case 'event':
				echo esc_html( get_the_title( (int) get_post_meta( $post_id, '_eptp_event_id', true ) ) );
				break;
			case 'code':
				echo esc_html( (string) get_post_meta( $post_id, '_eptp_ticket_code', true ) );
				break;
			case 'status':
				echo esc_html( ucfirst( (string) get_post_meta( $post_id, '_eptp_status', true ) ) );
				break;
			case 'checkin':
				echo 'yes' === get_post_meta( $post_id, '_eptp_checked_in', true ) ? esc_html__( 'Yes', 'eventpro-tickets-plus' ) : esc_html__( 'No', 'eventpro-tickets-plus' );
				break;
		}
	}

	/**
	 * Export attendees CSV.
	 *
	 * @return void
	 */
	public function export_attendees() : void {
		if ( ! current_user_can( 'export_eptp_attendees' ) ) {
			wp_die( esc_html__( 'You are not allowed to export attendees.', 'eventpro-tickets-plus' ) );
		}

		check_admin_referer( 'eptp_export_attendees' );

		if ( ! isset( $_GET['event_id'] ) ) {
			wp_die( esc_html__( 'You are not allowed to export attendees.', 'eventpro-tickets-plus' ) );
		}

		$event_id = absint( $_GET['event_id'] ?? 0 );
		$args = array(
			'post_type'      => 'eptp_attendee',
			'posts_per_page' => -1,
		);

		if ( $event_id ) {
			$args['meta_key']   = '_eptp_event_id';
			$args['meta_value'] = $event_id;
		}

		$posts = get_posts( $args );

		nocache_headers();
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="attendees-' . $event_id . '.csv"' );

		$output = fopen( 'php://output', 'w' );

		if ( false === $output ) {
			wp_die( esc_html__( 'Unable to open export stream.', 'eventpro-tickets-plus' ) );
		}

		fputcsv( $output, array( 'name', 'email', 'ticket_code', 'status', 'order_id' ) );

		foreach ( $posts as $post ) {
			fputcsv(
				$output,
				array(
					get_post_meta( $post->ID, '_eptp_attendee_name', true ),
					get_post_meta( $post->ID, '_eptp_attendee_email', true ),
					get_post_meta( $post->ID, '_eptp_ticket_code', true ),
					get_post_meta( $post->ID, '_eptp_status', true ),
					get_post_meta( $post->ID, '_eptp_order_id', true ),
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Create branded demo content to showcase the plugin frontend.
	 *
	 * @return void
	 */
	public function create_demo_content() : void {
		if ( ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			wp_die( esc_html__( 'You are not allowed to create demo content.', 'eventpro-tickets-plus' ) );
		}

		check_admin_referer( 'eptp_create_demo_content' );

		if ( ! function_exists( 'wc_get_product_object' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=eptp-dashboard&eptp_notice=demo-failed' ) );
			exit;
		}

		$venue_id = $this->upsert_demo_post(
			'eptp_venue',
			'_eptp_demo_key',
			'demo-venue',
			array(
				'post_title'   => __( 'Grand Horizon Hall', 'eventpro-tickets-plus' ),
				'post_content' => __( 'A premium conference venue in Colombo designed for hybrid keynote sessions, networking lounges, and sponsor activations.', 'eventpro-tickets-plus' ),
			)
		);

		$studio_venue_id = $this->upsert_demo_post(
			'eptp_venue',
			'_eptp_demo_key',
			'demo-venue-studio',
			array(
				'post_title'   => __( 'Northline Workshop Studio', 'eventpro-tickets-plus' ),
				'post_content' => __( 'A design-forward workshop venue built for product labs, creator sessions, and intimate premium roundtables with hybrid streaming support.', 'eventpro-tickets-plus' ),
			)
		);

		$lounge_venue_id = $this->upsert_demo_post(
			'eptp_venue',
			'_eptp_demo_key',
			'demo-venue-lounge',
			array(
				'post_title'   => __( 'Harbor View Lounge', 'eventpro-tickets-plus' ),
				'post_content' => __( 'A rooftop networking venue designed for sunset sessions, sponsor showcases, and stylish evening community events with a hospitality-led atmosphere.', 'eventpro-tickets-plus' ),
			)
		);

		$organizer_id = $this->upsert_demo_post(
			'eptp_organizer',
			'_eptp_demo_key',
			'demo-organizer',
			array(
				'post_title'   => __( 'Northstar Event Office', 'eventpro-tickets-plus' ),
				'post_content' => __( 'A professional sample organizer profile focused on curated business events, digital experiences, and premium attendee journeys.', 'eventpro-tickets-plus' ),
			)
		);

		$speaker_one = $this->upsert_demo_post(
			'eptp_speaker',
			'_eptp_demo_key',
			'demo-speaker-1',
			array(
				'post_title'   => __( 'Ethan Caldwell', 'eventpro-tickets-plus' ),
				'post_content' => __( 'Keynote speaker focused on digital commerce growth, customer journey design, and premium event strategy.', 'eventpro-tickets-plus' ),
			)
		);

		$speaker_two = $this->upsert_demo_post(
			'eptp_speaker',
			'_eptp_demo_key',
			'demo-speaker-2',
			array(
				'post_title'   => __( 'Commerce Leaders Panel', 'eventpro-tickets-plus' ),
				'post_content' => __( 'A fictional expert panel representing operators, brand leaders, and guest advisors for premium event demos.', 'eventpro-tickets-plus' ),
			)
		);

		$speaker_three = $this->upsert_demo_post(
			'eptp_speaker',
			'_eptp_demo_key',
			'demo-speaker-3',
			array(
				'post_title'   => __( 'Sophia Bennett', 'eventpro-tickets-plus' ),
				'post_content' => __( 'A fictional event host used for hospitality-led demos, networking intros, and polished evening experiences.', 'eventpro-tickets-plus' ),
			)
		);

		$general_product_id = $this->upsert_demo_product(
			'demo-product-general',
			array(
				'name'          => __( 'Summit Pass - General Admission', 'eventpro-tickets-plus' ),
				'regular_price' => '149.00',
				'stock_quantity'=> 120,
				'ticket_type'   => 'general-admission',
				'description'   => __( 'Sample WooCommerce ticket product for the demo event. Includes keynote access, learning sessions, and the attendee ticket dashboard experience.', 'eventpro-tickets-plus' ),
			)
		);

		$vip_product_id = $this->upsert_demo_product(
			'demo-product-vip',
			array(
				'name'          => __( 'Summit Pass - VIP Experience', 'eventpro-tickets-plus' ),
				'regular_price' => '299.00',
				'stock_quantity'=> 24,
				'ticket_type'   => 'vip',
				'description'   => __( 'Sample VIP ticket product with priority seating, a hosted networking lounge, and premium check-in flow for demonstration purposes.', 'eventpro-tickets-plus' ),
			)
		);

		$studio_product_id = $this->upsert_demo_product(
			'demo-product-studio',
			array(
				'name'          => __( 'Studio Workshop Pass', 'eventpro-tickets-plus' ),
				'regular_price' => '89.00',
				'stock_quantity'=> 40,
				'ticket_type'   => 'early-bird',
				'description'   => __( 'Sample workshop ticket product designed to show a simpler, creator-focused purchase path inside the modern UI.', 'eventpro-tickets-plus' ),
			)
		);

		$lounge_product_id = $this->upsert_demo_product(
			'demo-product-lounge',
			array(
				'name'          => __( 'Networking Night Pass', 'eventpro-tickets-plus' ),
				'regular_price' => '59.00',
				'stock_quantity'=> 80,
				'ticket_type'   => 'general-admission',
				'description'   => __( 'Sample networking event ticket product with a lower-friction purchase path for evening events, mixers, and community showcases.', 'eventpro-tickets-plus' ),
			)
		);

		$event_id = $this->upsert_demo_post(
			'eptp_event',
			'_eptp_demo_key',
			'demo-event',
			array(
				'post_title'   => __( 'Future Commerce Summit 2026', 'eventpro-tickets-plus' ),
				'post_excerpt' => __( 'A safe sample event created to help you review the modern frontend UI, ticket cards, archive layout, and WooCommerce-powered checkout flow before using real content.', 'eventpro-tickets-plus' ),
				'post_content' => '<p>' . esc_html__( 'This is safe demo content created by EventPro Tickets Plus. It is intentionally fictional and designed to help you understand the single-event layout, conversion hierarchy, and ticket purchase experience without using any live customer data.', 'eventpro-tickets-plus' ) . '</p><p>' . esc_html__( 'Use this sample to review hero presentation, section spacing, agenda blocks, FAQ rhythm, sponsor placement, and the sticky ticket panel. You can edit or remove every part of it later.', 'eventpro-tickets-plus' ) . '</p>',
			)
		);

		$studio_event_id = $this->upsert_demo_post(
			'eptp_event',
			'_eptp_demo_key',
			'demo-event-studio',
			array(
				'post_title'   => __( 'Launch Strategy Lab Live', 'eventpro-tickets-plus' ),
				'post_excerpt' => __( 'A second safe sample event with a smaller workshop tone, created so the archive feels more realistic and the modern UI can show two distinct event styles.', 'eventpro-tickets-plus' ),
				'post_content' => '<p>' . esc_html__( 'This second demo event is also fictional and safe to use for review. It gives you a more intimate workshop example so the archive does not feel repetitive and the purchase UI can be tested with different event positioning.', 'eventpro-tickets-plus' ) . '</p><p>' . esc_html__( 'It is useful for testing card variety, mobile behavior, CTA emphasis, and how the plugin handles different ticket narratives inside the same design system.', 'eventpro-tickets-plus' ) . '</p>',
			)
		);

		$lounge_event_id = $this->upsert_demo_post(
			'eptp_event',
			'_eptp_demo_key',
			'demo-event-lounge',
			array(
				'post_title'   => __( 'Sunset Leadership Networking Night', 'eventpro-tickets-plus' ),
				'post_excerpt' => __( 'A third safe sample event with a hospitality-led evening format, created to make the archive feel complete and visually varied.', 'eventpro-tickets-plus' ),
				'post_content' => '<p>' . esc_html__( 'This is a fictional demo event intended to show a more social, community-focused event style. It gives you a third frontend example for beautiful archive cards, sponsor blocks, and lighter evening conversion journeys.', 'eventpro-tickets-plus' ) . '</p><p>' . esc_html__( 'Use it to review how the plugin can present a polished one-evening event with simpler ticketing, venue emphasis, and sponsor-led storytelling.', 'eventpro-tickets-plus' ) . '</p>',
			)
		);

		if ( ! $venue_id || ! $studio_venue_id || ! $lounge_venue_id || ! $organizer_id || ! $speaker_one || ! $speaker_two || ! $speaker_three || ! $general_product_id || ! $vip_product_id || ! $studio_product_id || ! $lounge_product_id || ! $event_id || ! $studio_event_id || ! $lounge_event_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=eptp-dashboard&eptp_notice=demo-failed' ) );
			exit;
		}

		wp_set_object_terms( $event_id, __( 'Business Events', 'eventpro-tickets-plus' ), 'eptp_event_category', false );

		update_post_meta(
			$event_id,
			'_eptp_event_data',
			array(
				'event_type'          => 'single-day',
				'event_mode'          => 'hybrid',
				'status'              => 'scheduled',
				'timezone'            => wp_timezone_string() ? wp_timezone_string() : 'UTC',
				'start_date'          => gmdate( 'Y-m-d\T09:00', strtotime( '+21 days' ) ),
				'end_date'            => gmdate( 'Y-m-d\T18:00', strtotime( '+21 days' ) ),
				'recurrence_rule'     => '',
				'virtual_url'         => 'https://www.synbus.ph/',
				'venue_id'            => $venue_id,
				'organizer_ids'       => array( $organizer_id ),
				'speaker_ids'         => array( $speaker_one, $speaker_two ),
				'sales_start'         => gmdate( 'Y-m-d\T08:00', strtotime( '-1 day' ) ),
				'sales_end'           => gmdate( 'Y-m-d\T17:00', strtotime( '+20 days' ) ),
				'capacity'            => 180,
				'featured_label'      => __( 'Sample Experience', 'eventpro-tickets-plus' ),
				'hero_badge'          => __( 'Safe Demo Event', 'eventpro-tickets-plus' ),
				'ui_style'            => 'summit',
				'hero_media'          => 'demo-hero-summit.svg',
				'map_url'             => 'https://www.synbus.ph/',
				'cta_label'           => __( 'Get Tickets', 'eventpro-tickets-plus' ),
				'waitlist_enabled'    => 'yes',
				'registration_fields' => array(
					array(
						'label'    => __( 'Company Name', 'eventpro-tickets-plus' ),
						'name'     => 'company_name',
						'type'     => 'text',
						'required' => 'yes',
					),
				),
				'ticket_tiers'        => array(
					array(
						'label'       => __( 'General Admission', 'eventpro-tickets-plus' ),
						'product_id'  => $general_product_id,
						'type'        => 'general-admission',
						'price_label' => __( 'Main conference access', 'eventpro-tickets-plus' ),
						'description' => __( 'Ideal for testing the default purchase flow. Includes keynote access, breakout sessions, and attendee dashboard delivery.', 'eventpro-tickets-plus' ),
						'badge'       => __( 'Most Popular', 'eventpro-tickets-plus' ),
						'min_qty'     => 1,
						'max_qty'     => 10,
						'private'     => 'no',
					),
					array(
						'label'       => __( 'VIP Experience', 'eventpro-tickets-plus' ),
						'product_id'  => $vip_product_id,
						'type'        => 'vip',
						'price_label' => __( 'Priority lounge and front-row seating', 'eventpro-tickets-plus' ),
						'description' => __( 'Use this premium tier to review richer card hierarchy, premium pricing presentation, and lower-volume stock messaging.', 'eventpro-tickets-plus' ),
						'badge'       => __( 'Premium', 'eventpro-tickets-plus' ),
						'min_qty'     => 1,
						'max_qty'     => 4,
						'private'     => 'no',
					),
				),
				'agenda' => array(
					array(
						'time'        => '09:00',
						'title'       => __( 'Opening Keynote', 'eventpro-tickets-plus' ),
						'location'    => __( 'Main Stage', 'eventpro-tickets-plus' ),
						'speaker'     => __( 'Ethan Caldwell', 'eventpro-tickets-plus' ),
						'description' => __( 'A sample keynote block so you can review the agenda timeline styling on the single-event layout.', 'eventpro-tickets-plus' ),
					),
					array(
						'time'        => '13:30',
						'title'       => __( 'Commerce and Ticketing Panel', 'eventpro-tickets-plus' ),
						'location'    => __( 'Partner Lounge', 'eventpro-tickets-plus' ),
						'speaker'     => __( 'Commerce Leaders Panel', 'eventpro-tickets-plus' ),
						'description' => __( 'A second session block to help you visualize spacing, typography, and section hierarchy.', 'eventpro-tickets-plus' ),
					),
				),
				'faq_items' => array(
					array(
						'question' => __( 'Is this a real demo event?', 'eventpro-tickets-plus' ),
						'answer'   => __( 'It is a safe fictional sample created to help you understand how the plugin looks and behaves on the frontend.', 'eventpro-tickets-plus' ),
					),
					array(
						'question' => __( 'Can I edit or delete it?', 'eventpro-tickets-plus' ),
						'answer'   => __( 'Absolutely. Treat it like normal content and adjust any text, pricing, or schedule details as needed.', 'eventpro-tickets-plus' ),
					),
				),
				'sponsors' => array(
					array(
						'name'     => __( 'Elevate Commerce Group', 'eventpro-tickets-plus' ),
						'level'    => __( 'Title Sponsor', 'eventpro-tickets-plus' ),
						'url'      => 'https://www.synbus.ph/',
						'logo_url' => '',
					),
				),
				'gallery_ids'       => array(),
				'related_event_ids' => array( $studio_event_id, $lounge_event_id ),
				'reminder_offsets'  => array( '1440', '120' ),
				'seat_map_provider' => '',
			)
		);

		wp_set_object_terms( $studio_event_id, __( 'Workshops', 'eventpro-tickets-plus' ), 'eptp_event_category', false );

		update_post_meta(
			$studio_event_id,
			'_eptp_event_data',
			array(
				'event_type'          => 'multi-day',
				'event_mode'          => 'physical',
				'status'              => 'scheduled',
				'timezone'            => wp_timezone_string() ? wp_timezone_string() : 'UTC',
				'start_date'          => gmdate( 'Y-m-d\T10:00', strtotime( '+34 days' ) ),
				'end_date'            => gmdate( 'Y-m-d\T17:00', strtotime( '+35 days' ) ),
				'recurrence_rule'     => '',
				'virtual_url'         => '',
				'venue_id'            => $studio_venue_id,
				'organizer_ids'       => array( $organizer_id ),
				'speaker_ids'         => array( $speaker_one, $speaker_three ),
				'sales_start'         => gmdate( 'Y-m-d\T09:00', strtotime( '-1 day' ) ),
				'sales_end'           => gmdate( 'Y-m-d\T16:00', strtotime( '+33 days' ) ),
				'capacity'            => 60,
				'featured_label'      => __( 'Sample Workshop', 'eventpro-tickets-plus' ),
				'hero_badge'          => __( 'Safe Demo Event', 'eventpro-tickets-plus' ),
				'ui_style'            => 'studio',
				'hero_media'          => 'demo-hero-studio.svg',
				'map_url'             => 'https://www.synbus.ph/',
				'cta_label'           => __( 'Book Workshop Pass', 'eventpro-tickets-plus' ),
				'waitlist_enabled'    => 'yes',
				'registration_fields' => array(
					array(
						'label'    => __( 'Role', 'eventpro-tickets-plus' ),
						'name'     => 'job_role',
						'type'     => 'text',
						'required' => 'no',
					),
				),
				'ticket_tiers'        => array(
					array(
						'label'       => __( 'Workshop Pass', 'eventpro-tickets-plus' ),
						'product_id'  => $studio_product_id,
						'type'        => 'early-bird',
						'price_label' => __( 'Hands-on sessions and workbook', 'eventpro-tickets-plus' ),
						'description' => __( 'A lighter, workshop-style ticket card for testing simple pricing, smaller stock levels, and more focused event messaging.', 'eventpro-tickets-plus' ),
						'badge'       => __( 'Early Access', 'eventpro-tickets-plus' ),
						'min_qty'     => 1,
						'max_qty'     => 6,
						'private'     => 'no',
					),
				),
				'agenda' => array(
					array(
						'time'        => '10:00',
						'title'       => __( 'Offer Positioning Sprint', 'eventpro-tickets-plus' ),
						'location'    => __( 'Studio A', 'eventpro-tickets-plus' ),
						'speaker'     => __( 'Ethan Caldwell', 'eventpro-tickets-plus' ),
						'description' => __( 'A focused workshop block designed to show how smaller events can still feel premium and conversion-led.', 'eventpro-tickets-plus' ),
					),
					array(
						'time'        => '14:00',
						'title'       => __( 'Checkout Flow Critique', 'eventpro-tickets-plus' ),
						'location'    => __( 'Studio Lab', 'eventpro-tickets-plus' ),
						'speaker'     => __( 'Commerce Leaders Panel', 'eventpro-tickets-plus' ),
						'description' => __( 'Participants refine ticketing journeys and landing-page messaging for real-world launch scenarios.', 'eventpro-tickets-plus' ),
					),
				),
				'faq_items' => array(
					array(
						'question' => __( 'Why add a second demo event?', 'eventpro-tickets-plus' ),
						'answer'   => __( 'It helps the archive feel realistic and gives you a stronger reference for card hierarchy, category filtering, and event variety.', 'eventpro-tickets-plus' ),
					),
					array(
						'question' => __( 'Can this become a real event?', 'eventpro-tickets-plus' ),
						'answer'   => __( 'Yes. Update the title, copy, products, and schedule just like any normal WordPress event entry.', 'eventpro-tickets-plus' ),
					),
				),
				'sponsors' => array(
					array(
						'name'     => __( 'Launch Lab Collective', 'eventpro-tickets-plus' ),
						'level'    => __( 'Workshop Partner', 'eventpro-tickets-plus' ),
						'url'      => 'https://www.synbus.ph/',
						'logo_url' => '',
					),
				),
				'gallery_ids'       => array(),
				'related_event_ids' => array( $event_id, $lounge_event_id ),
				'reminder_offsets'  => array( '1440', '180' ),
				'seat_map_provider' => '',
			)
		);

		wp_set_object_terms( $lounge_event_id, __( 'Community Events', 'eventpro-tickets-plus' ), 'eptp_event_category', false );

		update_post_meta(
			$lounge_event_id,
			'_eptp_event_data',
			array(
				'event_type'          => 'single-day',
				'event_mode'          => 'physical',
				'status'              => 'scheduled',
				'timezone'            => wp_timezone_string() ? wp_timezone_string() : 'UTC',
				'start_date'          => gmdate( 'Y-m-d\T17:30', strtotime( '+48 days' ) ),
				'end_date'            => gmdate( 'Y-m-d\T22:00', strtotime( '+48 days' ) ),
				'recurrence_rule'     => '',
				'virtual_url'         => '',
				'venue_id'            => $lounge_venue_id,
				'organizer_ids'       => array( $organizer_id ),
				'speaker_ids'         => array( $speaker_three, $speaker_two ),
				'sales_start'         => gmdate( 'Y-m-d\T11:00', strtotime( '-1 day' ) ),
				'sales_end'           => gmdate( 'Y-m-d\T17:00', strtotime( '+47 days' ) ),
				'capacity'            => 120,
				'featured_label'      => __( 'Sample Community Event', 'eventpro-tickets-plus' ),
				'hero_badge'          => __( 'Safe Demo Event', 'eventpro-tickets-plus' ),
				'ui_style'            => 'lounge',
				'hero_media'          => 'demo-hero-lounge.svg',
				'map_url'             => 'https://www.synbus.ph/',
				'cta_label'           => __( 'Reserve Evening Pass', 'eventpro-tickets-plus' ),
				'waitlist_enabled'    => 'yes',
				'registration_fields' => array(
					array(
						'label'    => __( 'Company or Community', 'eventpro-tickets-plus' ),
						'name'     => 'community_name',
						'type'     => 'text',
						'required' => 'no',
					),
				),
				'ticket_tiers'        => array(
					array(
						'label'       => __( 'Networking Night Pass', 'eventpro-tickets-plus' ),
						'product_id'  => $lounge_product_id,
						'type'        => 'general-admission',
						'price_label' => __( 'Evening access and hosted networking', 'eventpro-tickets-plus' ),
						'description' => __( 'A clean, accessible demo ticket tier for community events, sponsor mixers, and hospitality-led evening experiences.', 'eventpro-tickets-plus' ),
						'badge'       => __( 'Easy Entry', 'eventpro-tickets-plus' ),
						'min_qty'     => 1,
						'max_qty'     => 8,
						'private'     => 'no',
					),
				),
				'agenda' => array(
					array(
						'time'        => '17:30',
						'title'       => __( 'Welcome Reception', 'eventpro-tickets-plus' ),
						'location'    => __( 'Sky Terrace', 'eventpro-tickets-plus' ),
						'speaker'     => __( 'Sophia Bennett', 'eventpro-tickets-plus' ),
						'description' => __( 'A hospitality-led opener designed to show clean event storytelling and evening-format agenda presentation.', 'eventpro-tickets-plus' ),
					),
					array(
						'time'        => '19:00',
						'title'       => __( 'Founder Mixer and Product Showcase', 'eventpro-tickets-plus' ),
						'location'    => __( 'Sponsor Lounge', 'eventpro-tickets-plus' ),
						'speaker'     => __( 'Commerce Leaders Panel', 'eventpro-tickets-plus' ),
						'description' => __( 'A relaxed feature block for sponsor activations, community networking, and polished conversion cues in the single-event view.', 'eventpro-tickets-plus' ),
					),
				),
				'faq_items' => array(
					array(
						'question' => __( 'Is this event also editable?', 'eventpro-tickets-plus' ),
						'answer'   => __( 'Yes. Like the other demo entries, this sample event is fully editable and can be repurposed or deleted at any time.', 'eventpro-tickets-plus' ),
					),
					array(
						'question' => __( 'Can demo content be removed later?', 'eventpro-tickets-plus' ),
						'answer'   => __( 'Yes. Use the Remove Demo Content action from the dashboard to clean out the imported sample data when it is no longer needed.', 'eventpro-tickets-plus' ),
					),
				),
				'sponsors' => array(
					array(
						'name'     => __( 'Harbor Circle Partners', 'eventpro-tickets-plus' ),
						'level'    => __( 'Host Partner', 'eventpro-tickets-plus' ),
						'url'      => 'https://www.synbus.ph/',
						'logo_url' => '',
					),
				),
				'gallery_ids'       => array(),
				'related_event_ids' => array( $event_id, $studio_event_id ),
				'reminder_offsets'  => array( '1440', '90' ),
				'seat_map_provider' => '',
			)
		);

		update_post_meta( $general_product_id, '_eptp_event_id', $event_id );
		update_post_meta( $general_product_id, '_eptp_ticket_type', 'general-admission' );
		update_post_meta( $vip_product_id, '_eptp_event_id', $event_id );
		update_post_meta( $vip_product_id, '_eptp_ticket_type', 'vip' );
		update_post_meta( $studio_product_id, '_eptp_event_id', $studio_event_id );
		update_post_meta( $studio_product_id, '_eptp_ticket_type', 'early-bird' );
		update_post_meta( $lounge_product_id, '_eptp_event_id', $lounge_event_id );
		update_post_meta( $lounge_product_id, '_eptp_ticket_type', 'general-admission' );

		wp_safe_redirect( admin_url( 'admin.php?page=eptp-dashboard&eptp_notice=demo-created' ) );
		exit;
	}

	/**
	 * Delete imported demo content.
	 *
	 * @return void
	 */
	public function delete_demo_content() : void {
		if ( ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			wp_die( esc_html__( 'You are not allowed to remove demo content.', 'eventpro-tickets-plus' ) );
		}

		check_admin_referer( 'eptp_delete_demo_content' );

		$demo_post_keys = array(
			'demo-venue',
			'demo-venue-studio',
			'demo-venue-lounge',
			'demo-organizer',
			'demo-speaker-1',
			'demo-speaker-2',
			'demo-speaker-3',
			'demo-event',
			'demo-event-studio',
			'demo-event-lounge',
			'demo-page-events',
			'demo-page-event-categories',
			'demo-page-business-events',
			'demo-page-workshops',
			'demo-page-community-events',
		);
		$demo_product_keys = array(
			'demo-product-general',
			'demo-product-vip',
			'demo-product-studio',
			'demo-product-lounge',
		);

		$this->delete_demo_posts_by_key( '_eptp_demo_key', $demo_post_keys, array( 'eptp_event', 'eptp_venue', 'eptp_organizer', 'eptp_speaker', 'page' ) );
		$this->delete_demo_posts_by_key( '_eptp_demo_key', $demo_product_keys, array( 'product' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=eptp-dashboard&eptp_notice=demo-removed' ) );
		exit;
	}

	/**
	 * Create premade frontend pages for events and categories.
	 *
	 * @return void
	 */
	public function create_demo_pages() : void {
		if ( ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			wp_die( esc_html__( 'You are not allowed to create frontend pages.', 'eventpro-tickets-plus' ) );
		}

		check_admin_referer( 'eptp_create_demo_pages' );

		$pages = array(
			array(
				'demo_key'     => 'demo-page-events',
				'post_title'   => __( 'Events', 'eventpro-tickets-plus' ),
				'post_name'    => 'events',
				'post_content' => '[eventpro_events posts_per_page="9" title="Featured Events" description="Discover premium conferences, workshops, and community experiences with a clean responsive event grid."]',
			),
			array(
				'demo_key'     => 'demo-page-event-categories',
				'post_title'   => __( 'Event Categories', 'eventpro-tickets-plus' ),
				'post_name'    => 'event-categories',
				'post_content' => '[eventpro_event_categories title="Explore Event Categories" description="Browse modern event collections and open category-specific pages with one clean landing experience."]',
			),
			array(
				'demo_key'     => 'demo-page-business-events',
				'post_title'   => __( 'Business Events', 'eventpro-tickets-plus' ),
				'post_name'    => 'business-events',
				'post_content' => '[eventpro_events category="business-events" posts_per_page="6" title="Business Events" description="Browse keynote-led conference experiences, networking sessions, and premium ticketed events for business audiences."]',
			),
			array(
				'demo_key'     => 'demo-page-workshops',
				'post_title'   => __( 'Workshops', 'eventpro-tickets-plus' ),
				'post_name'    => 'workshops',
				'post_content' => '[eventpro_events category="workshops" posts_per_page="6" title="Workshops" description="Explore focused workshop experiences with smaller-group formats, practical sessions, and modern registration journeys."]',
			),
			array(
				'demo_key'     => 'demo-page-community-events',
				'post_title'   => __( 'Community Events', 'eventpro-tickets-plus' ),
				'post_name'    => 'community-events',
				'post_content' => '[eventpro_events category="community-events" posts_per_page="6" title="Community Events" description="Review lighter social and community-led event pages built for networking, hosted evenings, and easy ticket access."]',
			),
		);

		foreach ( $pages as $page ) {
			$page_id = $this->upsert_demo_post(
				'page',
				'_eptp_demo_key',
				$page['demo_key'],
				array(
					'post_title'   => $page['post_title'],
					'post_content' => $page['post_content'],
					'post_excerpt' => '',
				)
			);

			if ( ! $page_id ) {
				wp_safe_redirect( admin_url( 'admin.php?page=eptp-dashboard&eptp_notice=pages-failed' ) );
				exit;
			}

			wp_update_post(
				array(
					'ID'        => $page_id,
					'post_name' => sanitize_title( (string) $page['post_name'] ),
				)
			);
		}

		wp_safe_redirect( admin_url( 'admin.php?page=eptp-dashboard&eptp_notice=pages-created' ) );
		exit;
	}

	/**
	 * Create or update a demo post by key.
	 *
	 * @param string               $post_type Post type.
	 * @param string               $meta_key Meta key.
	 * @param string               $demo_key Demo key.
	 * @param array<string,string> $post_data Post fields.
	 * @return int
	 */
	protected function upsert_demo_post( string $post_type, string $meta_key, string $demo_key, array $post_data ) : int {
		$existing = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => $meta_key,
				'meta_value'     => $demo_key,
			)
		);

		$postarr = array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_title'   => $post_data['post_title'] ?? '',
			'post_content' => $post_data['post_content'] ?? '',
			'post_excerpt' => $post_data['post_excerpt'] ?? '',
		);

		if ( ! empty( $existing ) ) {
			$postarr['ID'] = (int) $existing[0];
			$post_id = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		update_post_meta( $post_id, $meta_key, $demo_key );

		return (int) $post_id;
	}

	/**
	 * Create or update a demo WooCommerce product.
	 *
	 * @param string               $demo_key Demo key.
	 * @param array<string,mixed>  $args Product args.
	 * @return int
	 */
	protected function upsert_demo_product( string $demo_key, array $args ) : int {
		$existing = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_eptp_demo_key',
				'meta_value'     => $demo_key,
			)
		);

		$product = ! empty( $existing ) ? wc_get_product( (int) $existing[0] ) : wc_get_product_object( 'simple' );

		if ( ! $product ) {
			return 0;
		}

		$product->set_name( sanitize_text_field( (string) $args['name'] ) );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_regular_price( wc_format_decimal( (string) $args['regular_price'] ) );
		$product->set_price( wc_format_decimal( (string) $args['regular_price'] ) );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( absint( $args['stock_quantity'] ?? 0 ) );
		$product->set_stock_status( 'instock' );
		$product->set_description( sanitize_textarea_field( (string) ( $args['description'] ?? __( 'Demo ticket product created automatically by EventPro Tickets Plus to help you test the WooCommerce event purchase flow.', 'eventpro-tickets-plus' ) ) ) );
		$product_id = $product->save();

		if ( ! $product_id ) {
			return 0;
		}

		update_post_meta( $product_id, '_eptp_demo_key', $demo_key );
		update_post_meta( $product_id, '_eptp_ticket_type', sanitize_text_field( (string) $args['ticket_type'] ) );

		return (int) $product_id;
	}

	/**
	 * Delete demo posts/products by demo key.
	 *
	 * @param string              $meta_key Meta key.
	 * @param array<int, string>  $demo_keys Demo keys.
	 * @param array<int, string>  $post_types Allowed post types.
	 * @return void
	 */
	protected function delete_demo_posts_by_key( string $meta_key, array $demo_keys, array $post_types ) : void {
		foreach ( $demo_keys as $demo_key ) {
			$post_ids = get_posts(
				array(
					'post_type'      => $post_types,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_key'       => $meta_key,
					'meta_value'     => $demo_key,
				)
			);

			foreach ( $post_ids as $post_id ) {
				wp_delete_post( (int) $post_id, true );
			}
		}
	}

	/**
	 * Calculate revenue.
	 *
	 * @return float
	 */
	protected function calculate_revenue() : float {
		$total  = 0.0;
		$orders = wc_get_orders(
			array(
				'status' => array( 'wc-processing', 'wc-completed' ),
				'limit'  => -1,
			)
		);

		foreach ( $orders as $order ) {
			$total += (float) $order->get_total();
		}

		return $total;
	}

	/**
	 * Count today check-ins.
	 *
	 * @return int
	 */
	protected function count_today_checkins() : int {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}eptp_checkin_logs WHERE DATE(created_at) = %s",
				current_time( 'Y-m-d' )
			)
		);
	}
}

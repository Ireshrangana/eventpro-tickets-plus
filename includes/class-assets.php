<?php
/**
 * Asset loader.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Registers shared assets.
 */
class Assets {

	/**
	 * Register shared hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ) );
	}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register_public_assets() : void {
		wp_register_style( 'eptp-design-tokens', EPTP_PLUGIN_URL . 'assets/css/design-tokens.css', array(), EPTP_VERSION );
		wp_add_inline_style( 'eptp-design-tokens', $this->get_runtime_tokens_css() );
		wp_register_style( 'eptp-public', EPTP_PLUGIN_URL . 'public/css/public.css', array( 'eptp-design-tokens' ), EPTP_VERSION );
		wp_register_script( 'eptp-public', EPTP_PLUGIN_URL . 'public/js/public.js', array(), EPTP_VERSION, true );
	}

	/**
	 * Register admin assets.
	 *
	 * @return void
	 */
	public function register_admin_assets() : void {
		wp_register_style( 'eptp-design-tokens', EPTP_PLUGIN_URL . 'assets/css/design-tokens.css', array(), EPTP_VERSION );
		wp_add_inline_style( 'eptp-design-tokens', $this->get_runtime_tokens_css() );
		wp_register_style( 'eptp-admin', EPTP_PLUGIN_URL . 'admin/css/admin.css', array( 'eptp-design-tokens' ), EPTP_VERSION );
		wp_register_script( 'eptp-admin', EPTP_PLUGIN_URL . 'admin/js/admin.js', array( 'wp-api-fetch' ), EPTP_VERSION, true );
	}

	/**
	 * Build runtime design token overrides from settings.
	 *
	 * @return string
	 */
	protected function get_runtime_tokens_css() : string {
		$settings            = eptp_get_plugin_settings();
		$brand_color         = sanitize_hex_color( (string) ( $settings['brand_color'] ?? '' ) ) ?: '#1358db';
		$accent_color        = sanitize_hex_color( (string) ( $settings['accent_color'] ?? '' ) ) ?: '#0d9488';
		$page_background     = sanitize_hex_color( (string) ( $settings['page_background_color'] ?? '' ) ) ?: '#f6f8fc';
		$panel_background    = sanitize_hex_color( (string) ( $settings['panel_background_color'] ?? '' ) ) ?: '#ffffff';
		$heading_color       = sanitize_hex_color( (string) ( $settings['heading_color'] ?? '' ) ) ?: '#0b1324';
		$body_text_color     = sanitize_hex_color( (string) ( $settings['body_text_color'] ?? '' ) ) ?: '#142033';
		$muted_text_color    = sanitize_hex_color( (string) ( $settings['muted_text_color'] ?? '' ) ) ?: '#64748b';
		$heading_font_key    = in_array( (string) ( $settings['heading_font'] ?? 'modern-sans' ), array_keys( $this->get_font_stack_map() ), true ) ? (string) $settings['heading_font'] : 'modern-sans';
		$body_font_key       = in_array( (string) ( $settings['body_font'] ?? 'clean-sans' ), array_keys( $this->get_font_stack_map() ), true ) ? (string) $settings['body_font'] : 'clean-sans';
		$frontend_shell      = max( 960, min( 1600, absint( $settings['frontend_shell_width'] ?? 1240 ) ) );
		$single_shell        = max( 1100, min( 1700, absint( $settings['single_shell_width'] ?? 1320 ) ) );
		$content_text_width  = max( 560, min( 920, absint( $settings['content_text_width'] ?? 760 ) ) );
		$section_spacing     = max( 20, min( 64, absint( $settings['section_spacing'] ?? 28 ) ) );
		$card_radius         = max( 16, min( 36, absint( $settings['card_radius'] ?? 22 ) ) );
		$card_radius_sm      = max( 12, $card_radius - 6 );
		$archive_columns     = in_array( (string) ( $settings['archive_columns'] ?? '2' ), array( '2', '3' ), true ) ? (string) $settings['archive_columns'] : '2';
		$shortcode_columns   = in_array( (string) ( $settings['shortcode_columns'] ?? '2' ), array( '2', '3' ), true ) ? (string) $settings['shortcode_columns'] : '2';
		$category_columns    = in_array( (string) ( $settings['category_columns'] ?? '2' ), array( '2', '3' ), true ) ? (string) $settings['category_columns'] : '2';
		$font_stacks         = $this->get_font_stack_map();

		// Keep runtime styling overrideable so child plugins or client builds can
		// ship branded presets without forking the public stylesheets.
		$runtime_css = sprintf(
			':root{--eptp-primary:%1$s;--eptp-primary-strong:%1$s;--eptp-accent:%2$s;--eptp-bg:%3$s;--eptp-panel:%4$s;--eptp-card-strong:%4$s;--eptp-text-strong:%5$s;--eptp-text:%6$s;--eptp-muted:%7$s;--eptp-font-heading:%8$s;--eptp-font-body:%9$s;--eptp-shell-max-width:%10$spx;--eptp-single-shell-max-width:%11$spx;--eptp-text-measure:%12$spx;--eptp-section-spacing:%13$spx;--eptp-radius:%14$spx;--eptp-radius-sm:%15$spx;--eptp-event-grid-columns:%16$s;--eptp-shortcode-grid-columns:%17$s;--eptp-category-grid-columns:%18$s;}',
			$brand_color,
			$accent_color,
			$page_background,
			$panel_background,
			$heading_color,
			$body_text_color,
			$muted_text_color,
			$font_stacks[ $heading_font_key ],
			$font_stacks[ $body_font_key ],
			$frontend_shell,
			$single_shell,
			$content_text_width,
			$section_spacing,
			$card_radius,
			$card_radius_sm,
			$archive_columns,
			$shortcode_columns,
			$category_columns
		);

		return (string) apply_filters( 'eventpro_tickets_plus_runtime_tokens_css', $runtime_css, $settings );
	}

	/**
	 * Get safe font stacks for plugin-controlled frontend pages.
	 *
	 * @return array<string,string>
	 */
	protected function get_font_stack_map() : array {
		$map = array(
			'modern-sans' => '"Manrope", "Inter", "Segoe UI", sans-serif',
			'clean-sans'  => '"Inter", "Helvetica Neue", Arial, sans-serif',
			'editorial'   => '"Avenir Next", "Segoe UI", sans-serif',
			'humanist'    => '"Gill Sans", "Trebuchet MS", sans-serif',
		);

		return apply_filters( 'eventpro_tickets_plus_font_stack_map', $map );
	}
}

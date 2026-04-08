<?php
/**
 * WooCommerce integration layer.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce product and account integration.
 */
class WooCommerce {

	/**
	 * Ticket service.
	 *
	 * @var Ticket_Service
	 */
	protected Ticket_Service $tickets;

	/**
	 * Constructor.
	 *
	 * @param Ticket_Service $tickets Ticket service.
	 */
	public function __construct( Ticket_Service $tickets ) {
		$this->tickets = $tickets;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		$settings = eptp_get_plugin_settings();
		$endpoint = sanitize_title( $settings['tickets_endpoint'] );

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_product_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'render_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'copy_registration_meta' ), 10, 4 );
		add_action( 'init', array( $this, 'register_my_account_endpoint' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_my_account_menu_item' ) );
		add_action( 'woocommerce_account_' . $endpoint . '_endpoint', array( $this, 'render_my_account_endpoint' ) );
	}

	/**
	 * Add product tab.
	 *
	 * @param array<string,mixed> $tabs Tabs.
	 * @return array<string,mixed>
	 */
	public function add_product_tab( array $tabs ) : array {
		$tabs['eptp_ticketing'] = array(
			'label'    => __( 'Event Ticketing', 'eventpro-tickets-plus' ),
			'target'   => 'eptp_ticketing_data',
			'class'    => array(),
			'priority' => 75,
		);

		return $tabs;
	}

	/**
	 * Render product panel.
	 *
	 * @return void
	 */
	public function render_product_panel() : void {
		global $post;

		if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		$event_id   = absint( get_post_meta( $post->ID, '_eptp_event_id', true ) );
		$ticket_type = (string) get_post_meta( $post->ID, '_eptp_ticket_type', true );
		?>
		<div id="eptp_ticketing_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php
				woocommerce_wp_text_input(
					array(
						'id'          => '_eptp_event_id',
						'label'       => __( 'Linked Event ID', 'eventpro-tickets-plus' ),
						'value'       => $event_id,
						'type'        => 'number',
						'description' => __( 'Assign this WooCommerce product to an event.', 'eventpro-tickets-plus' ),
						'desc_tip'    => true,
					)
				);

				woocommerce_wp_select(
					array(
						'id'      => '_eptp_ticket_type',
						'label'   => __( 'Ticket Type', 'eventpro-tickets-plus' ),
						'value'   => $ticket_type ?: 'general-admission',
						'options' => array(
							...eptp_get_ticket_type_options(),
						),
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save product ticket meta.
	 *
	 * @param int $post_id Product ID.
	 * @return void
	 */
	public function save_product_meta( int $post_id ) : void {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}

		$event_id    = absint( $_POST['_eptp_event_id'] ?? 0 );
		$ticket_type = eptp_sanitize_allowed_value(
			wp_unslash( $_POST['_eptp_ticket_type'] ?? 'general-admission' ),
			array_keys( eptp_get_ticket_type_options() ),
			'general-admission'
		);

		if ( $event_id && 'eptp_event' !== get_post_type( $event_id ) ) {
			$event_id = 0;
		}

		update_post_meta( $post_id, '_eptp_event_id', $event_id );
		update_post_meta( $post_id, '_eptp_ticket_type', $ticket_type );
	}

	/**
	 * Validate add to cart against event rules.
	 *
	 * @param bool $passed Passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity Quantity.
	 * @return bool
	 */
	public function validate_add_to_cart( bool $passed, int $product_id, int $quantity ) : bool {
		$result = $this->tickets->validate_ticket_purchase( $product_id, $quantity );

		if ( is_wp_error( $result ) ) {
			wc_add_notice( $result->get_error_message(), 'error' );

			return false;
		}

		return $passed;
	}

	/**
	 * Render cart metadata.
	 *
	 * @param array<int,array<string,string>> $item_data Item data.
	 * @param array<string,mixed>             $cart_item Cart item.
	 * @return array<int,array<string,string>>
	 */
	public function render_cart_item_data( array $item_data, array $cart_item ) : array {
		$product_id = $cart_item['product_id'] ?? 0;
		$event_id   = $this->tickets->get_event_id_by_product( (int) $product_id );

		if ( $event_id ) {
			$item_data[] = array(
				'name'  => __( 'Event', 'eventpro-tickets-plus' ),
				'value' => get_the_title( $event_id ),
			);
		}

		return $item_data;
	}

	/**
	 * Copy registration data into order items.
	 *
	 * @param \WC_Order_Item_Product $item Order item.
	 * @param string                 $cart_item_key Cart item key.
	 * @param array<string,mixed>    $values Values.
	 * @param \WC_Order              $order Order.
	 * @return void
	 */
	public function copy_registration_meta( $item, string $cart_item_key, array $values, $order ) : void {
		if ( isset( $values['_eptp_registration_fields'] ) ) {
			$item->add_meta_data( '_eptp_registration_fields', $values['_eptp_registration_fields'], true );
		}
	}

	/**
	 * Register my account endpoint.
	 *
	 * @return void
	 */
	public function register_my_account_endpoint() : void {
		$settings = eptp_get_plugin_settings();

		add_rewrite_endpoint( sanitize_title( $settings['tickets_endpoint'] ), EP_ROOT | EP_PAGES );
	}

	/**
	 * Register account query var.
	 *
	 * @param array<int,string> $vars Vars.
	 * @return array<int,string>
	 */
	public function register_query_vars( array $vars ) : array {
		$settings = eptp_get_plugin_settings();
		$vars[]   = sanitize_title( $settings['tickets_endpoint'] );

		return $vars;
	}

	/**
	 * Add account menu item.
	 *
	 * @param array<string,string> $items Items.
	 * @return array<string,string>
	 */
	public function add_my_account_menu_item( array $items ) : array {
		$endpoint = sanitize_title( eptp_get_plugin_settings()['tickets_endpoint'] );
		$logout = $items['customer-logout'] ?? '';
		unset( $items['customer-logout'] );
		$items[ $endpoint ] = __( 'My Tickets', 'eventpro-tickets-plus' );

		if ( $logout ) {
			$items['customer-logout'] = $logout;
		}

		return $items;
	}

	/**
	 * Render tickets endpoint.
	 *
	 * @return void
	 */
	public function render_my_account_endpoint() : void {
		wc_get_template(
			'my-account-tickets.php',
			array(),
			'',
			EPTP_PLUGIN_DIR . 'templates/'
		);
	}
}

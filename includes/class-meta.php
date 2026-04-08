<?php
/**
 * Event meta registration and saving.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Handles event meta.
 */
class Meta {

	/**
	 * Register meta and save hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		register_post_meta(
			'eptp_event',
			'_eptp_event_data',
			array(
				'single'            => true,
				'type'              => 'object',
				'show_in_rest'      => true,
				'auth_callback'     => array( $this, 'can_edit_event_meta' ),
				'sanitize_callback' => array( $this, 'sanitize_event_meta' ),
			)
		);

		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_eptp_event', array( $this, 'save_event_meta' ) );
	}

	/**
	 * Register event meta boxes.
	 *
	 * @return void
	 */
	public function register_meta_boxes() : void {
		if ( ! current_user_can( 'edit_eptp_events' ) && ! current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			return;
		}

		add_meta_box(
			'eptp-event-details',
			__( 'Event Experience Builder', 'eventpro-tickets-plus' ),
			array( $this, 'render_event_meta_box' ),
			'eptp_event',
			'normal',
			'high'
		);
	}

	/**
	 * Render event meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_event_meta_box( \WP_Post $post ) : void {
		$data       = eptp_get_event_meta( $post->ID );
		$venues     = get_posts( array( 'post_type' => 'eptp_venue', 'numberposts' => -1 ) );
		$organizers = get_posts( array( 'post_type' => 'eptp_organizer', 'numberposts' => -1 ) );
		$speakers   = get_posts( array( 'post_type' => 'eptp_speaker', 'numberposts' => -1 ) );

		wp_nonce_field( 'eptp_save_event_meta', 'eptp_event_meta_nonce' );
		?>
		<div class="eptp-admin-panels">
			<section class="eptp-panel">
				<h3><?php esc_html_e( 'Core Event Settings', 'eventpro-tickets-plus' ); ?></h3>
				<div class="eptp-field-grid">
					<p>
						<label for="eptp_event_type"><?php esc_html_e( 'Event Type', 'eventpro-tickets-plus' ); ?></label>
						<select id="eptp_event_type" name="eptp_event_data[event_type]">
							<?php foreach ( array( 'single-day', 'multi-day', 'recurring', 'virtual', 'physical', 'hybrid' ) as $type ) : ?>
								<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $data['event_type'], $type ); ?>><?php echo esc_html( ucwords( str_replace( '-', ' ', $type ) ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label for="eptp_event_mode"><?php esc_html_e( 'Delivery Mode', 'eventpro-tickets-plus' ); ?></label>
						<select id="eptp_event_mode" name="eptp_event_data[event_mode]">
							<?php foreach ( array( 'physical', 'virtual', 'hybrid' ) as $mode ) : ?>
								<option value="<?php echo esc_attr( $mode ); ?>" <?php selected( $data['event_mode'], $mode ); ?>><?php echo esc_html( ucfirst( $mode ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label for="eptp_status"><?php esc_html_e( 'Event Status', 'eventpro-tickets-plus' ); ?></label>
						<select id="eptp_status" name="eptp_event_data[status]">
							<?php foreach ( array( 'scheduled', 'live', 'sold-out', 'ended', 'cancelled' ) as $status ) : ?>
								<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $data['status'], $status ); ?>><?php echo esc_html( ucwords( str_replace( '-', ' ', $status ) ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label for="eptp_timezone"><?php esc_html_e( 'Timezone', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_timezone" type="text" name="eptp_event_data[timezone]" value="<?php echo esc_attr( $data['timezone'] ); ?>">
					</p>
					<p>
						<label for="eptp_start_date"><?php esc_html_e( 'Start', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_start_date" type="datetime-local" name="eptp_event_data[start_date]" value="<?php echo esc_attr( $data['start_date'] ); ?>">
					</p>
					<p>
						<label for="eptp_end_date"><?php esc_html_e( 'End', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_end_date" type="datetime-local" name="eptp_event_data[end_date]" value="<?php echo esc_attr( $data['end_date'] ); ?>">
					</p>
					<p>
						<label for="eptp_sales_start"><?php esc_html_e( 'Ticket Sales Start', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_sales_start" type="datetime-local" name="eptp_event_data[sales_start]" value="<?php echo esc_attr( $data['sales_start'] ); ?>">
					</p>
					<p>
						<label for="eptp_sales_end"><?php esc_html_e( 'Ticket Sales End', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_sales_end" type="datetime-local" name="eptp_event_data[sales_end]" value="<?php echo esc_attr( $data['sales_end'] ); ?>">
					</p>
					<p>
						<label for="eptp_capacity"><?php esc_html_e( 'Capacity', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_capacity" type="number" min="0" name="eptp_event_data[capacity]" value="<?php echo esc_attr( (string) $data['capacity'] ); ?>">
					</p>
					<p>
						<label for="eptp_featured_label"><?php esc_html_e( 'Featured Label', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_featured_label" type="text" name="eptp_event_data[featured_label]" value="<?php echo esc_attr( $data['featured_label'] ); ?>">
					</p>
					<p>
						<label for="eptp_hero_badge"><?php esc_html_e( 'Hero Badge', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_hero_badge" type="text" name="eptp_event_data[hero_badge]" value="<?php echo esc_attr( $data['hero_badge'] ); ?>">
					</p>
					<p>
						<label for="eptp_virtual_url"><?php esc_html_e( 'Virtual Access URL', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_virtual_url" type="url" name="eptp_event_data[virtual_url]" value="<?php echo esc_attr( $data['virtual_url'] ); ?>">
					</p>
				</div>
			</section>

			<section class="eptp-panel">
				<h3><?php esc_html_e( 'Venue and People', 'eventpro-tickets-plus' ); ?></h3>
				<div class="eptp-field-grid">
					<p>
						<label for="eptp_venue_id"><?php esc_html_e( 'Venue', 'eventpro-tickets-plus' ); ?></label>
						<select id="eptp_venue_id" name="eptp_event_data[venue_id]">
							<option value="0"><?php esc_html_e( 'Select a venue', 'eventpro-tickets-plus' ); ?></option>
							<?php foreach ( $venues as $venue ) : ?>
								<option value="<?php echo esc_attr( (string) $venue->ID ); ?>" <?php selected( (int) $data['venue_id'], $venue->ID ); ?>><?php echo esc_html( $venue->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label for="eptp_map_url"><?php esc_html_e( 'Map URL', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_map_url" type="url" name="eptp_event_data[map_url]" value="<?php echo esc_attr( $data['map_url'] ); ?>">
					</p>
					<p>
						<label for="eptp_cta_label"><?php esc_html_e( 'CTA Label', 'eventpro-tickets-plus' ); ?></label>
						<input id="eptp_cta_label" type="text" name="eptp_event_data[cta_label]" value="<?php echo esc_attr( $data['cta_label'] ); ?>">
					</p>
					<p>
						<label for="eptp_waitlist_enabled"><?php esc_html_e( 'Enable Waitlist', 'eventpro-tickets-plus' ); ?></label>
						<select id="eptp_waitlist_enabled" name="eptp_event_data[waitlist_enabled]">
							<option value="yes" <?php selected( $data['waitlist_enabled'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'eventpro-tickets-plus' ); ?></option>
							<option value="no" <?php selected( $data['waitlist_enabled'], 'no' ); ?>><?php esc_html_e( 'No', 'eventpro-tickets-plus' ); ?></option>
						</select>
					</p>
				</div>
				<p>
					<label for="eptp_organizer_ids"><?php esc_html_e( 'Organizers', 'eventpro-tickets-plus' ); ?></label>
					<select id="eptp_organizer_ids" name="eptp_event_data[organizer_ids][]" multiple>
						<?php foreach ( $organizers as $organizer ) : ?>
							<option value="<?php echo esc_attr( (string) $organizer->ID ); ?>" <?php selected( in_array( $organizer->ID, $data['organizer_ids'], true ) ); ?>><?php echo esc_html( $organizer->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="eptp_speaker_ids"><?php esc_html_e( 'Featured Speakers', 'eventpro-tickets-plus' ); ?></label>
					<select id="eptp_speaker_ids" name="eptp_event_data[speaker_ids][]" multiple>
						<?php foreach ( $speakers as $speaker ) : ?>
							<option value="<?php echo esc_attr( (string) $speaker->ID ); ?>" <?php selected( in_array( $speaker->ID, $data['speaker_ids'], true ) ); ?>><?php echo esc_html( $speaker->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			</section>

			<?php $this->render_repeater_section( 'ticket_tiers', __( 'Ticket Tier Builder', 'eventpro-tickets-plus' ), $data['ticket_tiers'], array(
				'label'               => __( 'Label', 'eventpro-tickets-plus' ),
				'product_id'          => __( 'Woo Product ID', 'eventpro-tickets-plus' ),
				'type'                => __( 'Type', 'eventpro-tickets-plus' ),
				'price_label'         => __( 'Price Label', 'eventpro-tickets-plus' ),
				'min_qty'             => __( 'Min Qty', 'eventpro-tickets-plus' ),
				'max_qty'             => __( 'Max Qty', 'eventpro-tickets-plus' ),
				'private'             => __( 'Private', 'eventpro-tickets-plus' ),
			) ); ?>

			<?php $this->render_repeater_section( 'agenda', __( 'Agenda / Session Builder', 'eventpro-tickets-plus' ), $data['agenda'], array(
				'time'        => __( 'Time', 'eventpro-tickets-plus' ),
				'title'       => __( 'Title', 'eventpro-tickets-plus' ),
				'location'    => __( 'Location', 'eventpro-tickets-plus' ),
				'speaker'     => __( 'Speaker', 'eventpro-tickets-plus' ),
				'description' => __( 'Description', 'eventpro-tickets-plus' ),
			) ); ?>

			<?php $this->render_repeater_section( 'faq_items', __( 'FAQs', 'eventpro-tickets-plus' ), $data['faq_items'], array(
				'question' => __( 'Question', 'eventpro-tickets-plus' ),
				'answer'   => __( 'Answer', 'eventpro-tickets-plus' ),
			) ); ?>

			<?php $this->render_repeater_section( 'sponsors', __( 'Sponsors', 'eventpro-tickets-plus' ), $data['sponsors'], array(
				'name'     => __( 'Name', 'eventpro-tickets-plus' ),
				'level'    => __( 'Level', 'eventpro-tickets-plus' ),
				'url'      => __( 'URL', 'eventpro-tickets-plus' ),
				'logo_url' => __( 'Logo URL', 'eventpro-tickets-plus' ),
			) ); ?>
		</div>
		<?php
	}

	/**
	 * Render repeater section.
	 *
	 * @param string                     $key Section key.
	 * @param string                     $title Section title.
	 * @param array<int, array<string,mixed>> $rows Rows.
	 * @param array<string, string>      $columns Columns.
	 * @return void
	 */
	protected function render_repeater_section( string $key, string $title, array $rows, array $columns ) : void {
		?>
		<section class="eptp-panel eptp-repeater" data-repeater-key="<?php echo esc_attr( $key ); ?>">
			<div class="eptp-repeater-header">
				<h3><?php echo esc_html( $title ); ?></h3>
				<button type="button" class="button button-secondary eptp-add-row"><?php esc_html_e( 'Add Row', 'eventpro-tickets-plus' ); ?></button>
			</div>
			<input type="hidden" class="eptp-repeater-input" name="eptp_event_data[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( wp_json_encode( $rows ) ); ?>">
			<div class="eptp-repeater-columns">
				<?php foreach ( $columns as $label ) : ?>
					<span><?php echo esc_html( $label ); ?></span>
				<?php endforeach; ?>
				<span><?php esc_html_e( 'Actions', 'eventpro-tickets-plus' ); ?></span>
			</div>
			<div class="eptp-repeater-rows" data-columns="<?php echo esc_attr( wp_json_encode( $columns ) ); ?>"></div>
		</section>
		<?php
	}

	/**
	 * Save event meta.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_event_meta( int $post_id ) : void {
		if ( ! isset( $_POST['eptp_event_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eptp_event_meta_nonce'] ) ), 'eptp_save_event_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = isset( $_POST['eptp_event_data'] ) ? wp_unslash( $_POST['eptp_event_data'] ) : array();
		$raw = is_array( $raw ) ? $raw : array();

		update_post_meta( $post_id, '_eptp_event_data', $this->sanitize_event_meta( $raw ) );
	}

	/**
	 * Sanitize event meta payload.
	 *
	 * @param mixed $value Raw value.
	 * @return array<string, mixed>
	 */
	public function sanitize_event_meta( mixed $value ) : array {
		$value = is_array( $value ) ? $value : array();

		$defaults = eptp_get_event_defaults();
		$data     = wp_parse_args( $value, $defaults );

		if ( is_string( $data['ticket_tiers'] ) ) {
			$data['ticket_tiers'] = json_decode( wp_unslash( $data['ticket_tiers'] ), true );
		}

		if ( is_string( $data['agenda'] ) ) {
			$data['agenda'] = json_decode( wp_unslash( $data['agenda'] ), true );
		}

		if ( is_string( $data['faq_items'] ) ) {
			$data['faq_items'] = json_decode( wp_unslash( $data['faq_items'] ), true );
		}

		if ( is_string( $data['sponsors'] ) ) {
			$data['sponsors'] = json_decode( wp_unslash( $data['sponsors'] ), true );
		}

		return array(
			'event_type'          => eptp_sanitize_allowed_value( $data['event_type'], array( 'single-day', 'multi-day', 'recurring', 'virtual', 'physical', 'hybrid' ), $defaults['event_type'] ),
			'event_mode'          => eptp_sanitize_allowed_value( $data['event_mode'], array( 'physical', 'virtual', 'hybrid' ), $defaults['event_mode'] ),
			'status'              => eptp_sanitize_allowed_value( $data['status'], array( 'scheduled', 'live', 'sold-out', 'ended', 'cancelled' ), $defaults['status'] ),
			'timezone'            => sanitize_text_field( $data['timezone'] ),
			'start_date'          => eptp_sanitize_datetime( (string) $data['start_date'] ),
			'end_date'            => eptp_sanitize_datetime( (string) $data['end_date'] ),
			'recurrence_rule'     => sanitize_text_field( $data['recurrence_rule'] ),
			'virtual_url'         => esc_url_raw( (string) $data['virtual_url'] ),
			'venue_id'            => absint( $data['venue_id'] ),
			'organizer_ids'       => eptp_sanitize_id_list( $data['organizer_ids'] ),
			'speaker_ids'         => eptp_sanitize_id_list( $data['speaker_ids'] ),
			'sales_start'         => eptp_sanitize_datetime( (string) $data['sales_start'] ),
			'sales_end'           => eptp_sanitize_datetime( (string) $data['sales_end'] ),
			'capacity'            => absint( $data['capacity'] ),
			'featured_label'      => sanitize_text_field( $data['featured_label'] ),
			'hero_badge'          => sanitize_text_field( $data['hero_badge'] ),
			'map_url'             => esc_url_raw( (string) $data['map_url'] ),
			'cta_label'           => sanitize_text_field( $data['cta_label'] ),
			'waitlist_enabled'    => eptp_sanitize_checkbox( $data['waitlist_enabled'] ),
			'registration_fields' => eptp_sanitize_repeater_rows(
				$data['registration_fields'],
				array(
					'label'    => 'text',
					'name'     => 'text',
					'type'     => 'text',
					'required' => 'bool',
				)
			),
			'ticket_tiers' => eptp_sanitize_repeater_rows(
				$data['ticket_tiers'],
				array(
					'label'       => 'text',
					'product_id'  => 'int',
					'type'        => 'text',
					'price_label' => 'text',
					'min_qty'     => 'int',
					'max_qty'     => 'int',
					'private'     => 'bool',
				)
			),
			'agenda' => eptp_sanitize_repeater_rows(
				$data['agenda'],
				array(
					'time'        => 'text',
					'title'       => 'text',
					'location'    => 'text',
					'speaker'     => 'text',
					'description' => 'textarea',
				)
			),
			'faq_items' => eptp_sanitize_repeater_rows(
				$data['faq_items'],
				array(
					'question' => 'text',
					'answer'   => 'textarea',
				)
			),
			'sponsors' => eptp_sanitize_repeater_rows(
				$data['sponsors'],
				array(
					'name'     => 'text',
					'level'    => 'text',
					'url'      => 'url',
					'logo_url' => 'url',
				)
			),
			'gallery_ids'       => eptp_sanitize_id_list( $data['gallery_ids'] ),
			'related_event_ids' => eptp_sanitize_id_list( $data['related_event_ids'] ),
			'reminder_offsets'  => array_map( 'sanitize_text_field', is_array( $data['reminder_offsets'] ) ? $data['reminder_offsets'] : array() ),
			'seat_map_provider' => sanitize_text_field( $data['seat_map_provider'] ),
		);
	}

	/**
	 * REST auth callback for event meta.
	 *
	 * @param bool   $allowed Whether access is allowed.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 * @return bool
	 */
	public function can_edit_event_meta( bool $allowed, string $meta_key, int $post_id = 0, int $user_id = 0, string $cap = '', array $caps = array() ) : bool {
		unset( $allowed, $meta_key, $user_id, $cap, $caps );

		return current_user_can( 'edit_post', $post_id );
	}
}

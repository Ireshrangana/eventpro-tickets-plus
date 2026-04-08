<?php
/**
 * Public experience.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus\Front;

use EventPro\TicketsPlus\Attendee_Service;
use EventPro\TicketsPlus\Event_Service;
use EventPro\TicketsPlus\PDF_Ticket_Service;
use EventPro\TicketsPlus\QR_Service;
use EventPro\TicketsPlus\Ticket_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Public templates and endpoints.
 */
class Public_Facing {

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
	 * QR service.
	 *
	 * @var QR_Service
	 */
	protected QR_Service $qr;

	/**
	 * PDF service.
	 *
	 * @var PDF_Ticket_Service
	 */
	protected PDF_Ticket_Service $pdf;

	/**
	 * Constructor.
	 *
	 * @param Event_Service      $events Events.
	 * @param Ticket_Service     $tickets Tickets.
	 * @param Attendee_Service   $attendees Attendees.
	 * @param QR_Service         $qr QR.
	 * @param PDF_Ticket_Service $pdf PDF.
	 */
	public function __construct( Event_Service $events, Ticket_Service $tickets, Attendee_Service $attendees, QR_Service $qr, PDF_Ticket_Service $pdf ) {
		$this->events    = $events;
		$this->tickets   = $tickets;
		$this->attendees = $attendees;
		$this->qr        = $qr;
		$this->pdf       = $pdf;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'template_include', array( $this, 'load_templates' ) );
		add_filter( 'body_class', array( $this, 'filter_body_class' ) );
		add_action( 'init', array( $this, 'maybe_download_pdf' ) );
		add_action( 'init', array( $this, 'maybe_download_ics' ) );
		add_action( 'wp_head', array( $this, 'output_schema' ) );
	}

	/**
	 * Enqueue public assets conditionally.
	 *
	 * @return void
	 */
	public function enqueue() : void {
		$has_shortcode_content = is_singular() ? (string) get_post_field( 'post_content', get_the_ID() ) : '';
		$has_events_shortcode  = is_singular() && has_shortcode( $has_shortcode_content, 'eventpro_events' );
		$has_categories_shortcode = is_singular() && has_shortcode( $has_shortcode_content, 'eventpro_event_categories' );

		if ( is_singular( 'eptp_event' ) || is_post_type_archive( 'eptp_event' ) || is_tax( 'eptp_event_category' ) || is_account_page() || $has_events_shortcode || $has_categories_shortcode ) {
			wp_enqueue_style( 'eptp-public' );
			wp_enqueue_script( 'eptp-public' );
			wp_localize_script(
				'eptp-public',
				'eptpPublic',
				array(
					'restUrl' => esc_url_raw( rest_url( 'eventpro-tickets-plus/v1/waitlist' ) ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}

	/**
	 * Template loader.
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function load_templates( string $template ) : string {
		if ( is_post_type_archive( 'eptp_event' ) ) {
			return EPTP_PLUGIN_DIR . 'templates/archive-event.php';
		}

		if ( is_tax( 'eptp_event_category' ) ) {
			return EPTP_PLUGIN_DIR . 'templates/taxonomy-eptp_event_category.php';
		}

		if ( is_singular( 'eptp_event' ) ) {
			return EPTP_PLUGIN_DIR . 'templates/single-event.php';
		}

		return $template;
	}

	/**
	 * Add body classes for plugin-driven landing pages.
	 *
	 * @param array<int,string> $classes Body classes.
	 * @return array<int,string>
	 */
	public function filter_body_class( array $classes ) : array {
		if ( $this->page_has_plugin_shortcode() ) {
			$classes[] = 'eptp-has-shortcode-page';
		}

		return $classes;
	}

	/**
	 * Handle PDF downloads.
	 *
	 * @return void
	 */
	public function maybe_download_pdf() : void {
		$attendee_id = absint( $_GET['eptp_download_ticket'] ?? 0 );
		$token       = sanitize_text_field( wp_unslash( $_GET['eptp_token'] ?? '' ) );

		if ( ! $attendee_id ) {
			return;
		}

		$attendee = get_post( $attendee_id );

		if ( ! $attendee || 'eptp_attendee' !== $attendee->post_type ) {
			wp_die( esc_html__( 'Ticket not found.', 'eventpro-tickets-plus' ) );
		}

		if ( ! eptp_verify_download_token( $attendee_id, $token ) ) {
			wp_die( esc_html__( 'Invalid ticket download request.', 'eventpro-tickets-plus' ) );
		}

		$current_user = wp_get_current_user();
		$email        = (string) get_post_meta( $attendee_id, '_eptp_attendee_email', true );

		if ( current_user_can( 'manage_eventpro_tickets_plus' ) ) {
			$this->pdf->output_pdf( $attendee_id );
		}

		if ( $current_user->exists() && strtolower( $current_user->user_email ) !== strtolower( $email ) ) {
			wp_die( esc_html__( 'You are not allowed to download this ticket.', 'eventpro-tickets-plus' ) );
		}

		$this->pdf->output_pdf( $attendee_id );
	}

	/**
	 * Download ICS event file.
	 *
	 * @return void
	 */
	public function maybe_download_ics() : void {
		$event_id = absint( $_GET['eptp_download_ics'] ?? 0 );

		if ( ! $event_id ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'eptp_download_ics_' . $event_id ) ) {
			wp_die( esc_html__( 'Invalid calendar download request.', 'eventpro-tickets-plus' ) );
		}

		$event = $this->events->get_event( $event_id );

		if ( empty( $event ) ) {
			wp_die( esc_html__( 'Event not found.', 'eventpro-tickets-plus' ) );
		}

		$start = gmdate( 'Ymd\THis\Z', strtotime( (string) $event['meta']['start_date'] ) );
		$end   = gmdate( 'Ymd\THis\Z', strtotime( (string) $event['meta']['end_date'] ?: (string) $event['meta']['start_date'] ) );
		$body  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//EventPro Tickets Plus//EN\r\nBEGIN:VEVENT\r\nUID:eptp-{$event_id}@eventproticketsplus\r\nDTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\nDTSTART:{$start}\r\nDTEND:{$end}\r\nSUMMARY:" . $this->escape_ics( $event['title'] ) . "\r\nDESCRIPTION:" . $this->escape_ics( wp_strip_all_tags( get_the_excerpt( $event_id ) ) ) . "\r\nLOCATION:" . $this->escape_ics( $event['venue']['title'] ?? '' ) . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
		$body  = apply_filters( 'eventpro_tickets_plus_calendar_download_body', $body, $event_id, $event );

		nocache_headers();
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="event-' . $event_id . '.ics"' );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Output schema for event pages.
	 *
	 * @return void
	 */
	public function output_schema() : void {
		if ( ! is_singular( 'eptp_event' ) || 'yes' !== eptp_get_plugin_settings()['enable_schema'] ) {
			return;
		}

		$event = $this->events->get_event( get_the_ID() );

		if ( empty( $event ) ) {
			return;
		}

		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'Event',
			'name'       => $event['title'],
			'url'        => $event['permalink'],
			'startDate'  => $event['meta']['start_date'],
			'endDate'    => $event['meta']['end_date'],
			'eventAttendanceMode' => 'virtual' === $event['meta']['event_mode'] ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
			'eventStatus' => 'https://schema.org/EventScheduled',
			'image'      => $event['thumbnail'],
			'description'=> wp_strip_all_tags( get_the_excerpt( get_the_ID() ) ),
		);
		$data = apply_filters( 'eventpro_tickets_plus_schema_data', $data, $event );

		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get customer attendees.
	 *
	 * @return array<int,\WP_Post>
	 */
	public function get_customer_attendees() : array {
		$user = wp_get_current_user();

		return $user->exists() ? $this->attendees->get_attendees_by_email( $user->user_email ) : array();
	}

	/**
	 * Get QR service.
	 *
	 * @return QR_Service
	 */
	public function qr() : QR_Service {
		return $this->qr;
	}

	/**
	 * Escape ICS strings.
	 *
	 * @param string $value Raw string.
	 * @return string
	 */
	protected function escape_ics( string $value ) : string {
		return str_replace( array( '\\', ',', ';', "\n" ), array( '\\\\', '\,', '\;', '\n' ), $value );
	}

	/**
	 * Determine whether the current singular page contains plugin landing shortcodes.
	 *
	 * @return bool
	 */
	protected function page_has_plugin_shortcode() : bool {
		if ( ! is_singular() ) {
			return false;
		}

		$content = (string) get_post_field( 'post_content', get_the_ID() );

		return has_shortcode( $content, 'eventpro_events' ) || has_shortcode( $content, 'eventpro_event_categories' ) || has_shortcode( $content, 'eventpro_ticket_dashboard' );
	}
}

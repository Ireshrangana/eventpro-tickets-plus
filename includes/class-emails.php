<?php
/**
 * Email service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Sends ticket emails.
 */
class Emails {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() : void {
		add_action( 'eventpro_tickets_plus_attendees_issued', array( $this, 'send_ticket_email' ), 10, 2 );
	}

	/**
	 * Send ticket email after issuance.
	 *
	 * @param array<int,int> $attendee_ids Attendee IDs.
	 * @param int            $order_id Order ID.
	 * @return void
	 */
	public function send_ticket_email( array $attendee_ids, int $order_id ) : void {
		if ( empty( $attendee_ids ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$download_url = add_query_arg(
			array(
				'eptp_download_ticket' => $attendee_ids[0],
				'eptp_token'           => eptp_generate_download_token( $attendee_ids[0] ),
			),
			home_url( '/' )
		);
		$download_url = apply_filters( 'eventpro_tickets_plus_ticket_download_url', $download_url, $attendee_ids, $order_id, $order );

		$body = sprintf(
			'<h2>%1$s</h2><p>%2$s</p><p><a href="%3$s">%4$s</a></p><p>%5$s <a href="%6$s">%7$s</a></p>',
			esc_html__( 'Your event tickets are ready', 'eventpro-tickets-plus' ),
			esc_html__( 'Thanks for your order. You can access your tickets from your account dashboard or download the lead ticket using the secure link below.', 'eventpro-tickets-plus' ),
			esc_url( $download_url ),
			esc_html__( 'Download Ticket PDF', 'eventpro-tickets-plus' ),
			esc_html__( 'EventPro Tickets Plus is branded and distributed by Synbus Inc.', 'eventpro-tickets-plus' ),
			esc_url( 'https://www.synbus.ph/' ),
			esc_html__( 'https://www.synbus.ph/', 'eventpro-tickets-plus' )
		);
		$body = apply_filters( 'eventpro_tickets_plus_ticket_email_body', $body, $attendee_ids, $order_id, $order, $download_url );

		$content_type_filter = static fn() => 'text/html';
		add_filter( 'wp_mail_content_type', $content_type_filter );

		$headers       = array();
		$settings      = eptp_get_plugin_settings();
		$reply_to      = sanitize_email( $settings['email_reply_to'] );
		$from_name     = sanitize_text_field( $settings['email_from_name'] );

		if ( $reply_to ) {
			$headers[] = 'Reply-To: ' . $from_name . ' <' . $reply_to . '>';
		}

		$headers = apply_filters( 'eventpro_tickets_plus_ticket_email_headers', $headers, $attendee_ids, $order_id, $order );
		$subject = apply_filters( 'eventpro_tickets_plus_ticket_email_subject', __( 'Your Event Tickets', 'eventpro-tickets-plus' ), $attendee_ids, $order_id, $order );

		wp_mail(
			$order->get_billing_email(),
			$subject,
			$body,
			$headers
		);

		remove_filter( 'wp_mail_content_type', $content_type_filter );
	}
}

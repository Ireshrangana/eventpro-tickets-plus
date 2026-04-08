<?php
/**
 * QR service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Generates validation payloads and SVG badges.
 */
class QR_Service {

	/**
	 * Build signed ticket payload.
	 *
	 * @param int $attendee_id Attendee ID.
	 * @return string
	 */
	public function get_payload( int $attendee_id ) : string {
		$ticket_code = (string) get_post_meta( $attendee_id, '_eptp_ticket_code', true );
		$data        = array(
			'attendee_id' => $attendee_id,
			'ticket_code' => $ticket_code,
			'event_id'    => (int) get_post_meta( $attendee_id, '_eptp_event_id', true ),
		);

		$signature = hash_hmac( 'sha256', wp_json_encode( $data ), wp_salt( 'auth' ) );

		return rawurlencode( base64_encode( wp_json_encode( array_merge( $data, array( 'signature' => $signature ) ) ) ) );
	}

	/**
	 * Render a lightweight SVG token card.
	 *
	 * @param int $attendee_id Attendee ID.
	 * @return string
	 */
	public function render_svg( int $attendee_id ) : string {
		$payload = $this->get_payload( $attendee_id );
		$hash    = substr( hash( 'sha256', $payload ), 0, 225 );
		$blocks  = str_split( $hash );
		$size    = 9;
		$svg     = '<svg xmlns="http://www.w3.org/2000/svg" width="164" height="164" viewBox="0 0 164 164" role="img" aria-label="' . esc_attr__( 'Ticket validation code', 'eventpro-tickets-plus' ) . '"><rect width="164" height="164" rx="18" fill="#ffffff"/>';

		foreach ( $blocks as $index => $char ) {
			$x = ( $index % 15 ) * $size + 14;
			$y = (int) floor( $index / 15 ) * $size + 14;

			if ( hexdec( $char ) % 2 === 0 ) {
				$svg .= sprintf( '<rect x="%1$d" y="%2$d" width="%3$d" height="%3$d" rx="2" fill="#111827"/>', $x, $y, $size - 2 );
			}
		}

		$svg .= '<text x="82" y="154" font-size="9" font-family="monospace" text-anchor="middle" fill="#4b5563">' . esc_html( substr( rawurldecode( $payload ), 0, 18 ) ) . '</text></svg>';

		return (string) apply_filters( 'eventpro_tickets_plus_qr_svg', $svg, $attendee_id, $payload );
	}
}

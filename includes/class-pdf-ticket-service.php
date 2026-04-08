<?php
/**
 * PDF ticket service.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Creates lightweight PDF downloads without third-party libraries.
 */
class PDF_Ticket_Service {

	/**
	 * Generate PDF file contents.
	 *
	 * @param int $attendee_id Attendee ID.
	 * @return string
	 */
	public function generate_pdf( int $attendee_id ) : string {
		$event_id     = (int) get_post_meta( $attendee_id, '_eptp_event_id', true );
		$event_title  = get_the_title( $event_id );
		$name         = (string) get_post_meta( $attendee_id, '_eptp_attendee_name', true );
		$email        = (string) get_post_meta( $attendee_id, '_eptp_attendee_email', true );
		$ticket_code  = (string) get_post_meta( $attendee_id, '_eptp_ticket_code', true );
		$event_data   = eptp_get_event_meta( $event_id );
		$event_date   = eptp_format_datetime( (string) $event_data['start_date'] );
		$lines        = array(
			'EventPro Tickets Plus',
			$event_title,
			sprintf( 'Attendee: %s', $name ),
			sprintf( 'Email: %s', $email ),
			sprintf( 'Ticket Code: %s', $ticket_code ),
			sprintf( 'Event Date: %s', $event_date ),
		);

		$y       = 760;
		$content = "BT /F1 18 Tf 72 {$y} Td (" . $this->escape_pdf_text( array_shift( $lines ) ) . ") Tj ET\n";

		foreach ( $lines as $line ) {
			$y      -= 32;
			$content .= "BT /F1 12 Tf 72 {$y} Td (" . $this->escape_pdf_text( $line ) . ") Tj ET\n";
		}

		$length = strlen( $content );

		return "%PDF-1.4
1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj
2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj
3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj
4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj
5 0 obj << /Length {$length} >> stream
{$content}endstream endobj
xref
0 6
0000000000 65535 f 
0000000010 00000 n 
0000000062 00000 n 
0000000117 00000 n 
0000000243 00000 n 
0000000313 00000 n 
trailer << /Root 1 0 R /Size 6 >>
startxref
{$this->calculate_startxref( $content )}
%%EOF";
	}

	/**
	 * Stream PDF download.
	 *
	 * @param int $attendee_id Attendee ID.
	 * @return void
	 */
	public function output_pdf( int $attendee_id ) : void {
		nocache_headers();
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="ticket-' . $attendee_id . '.pdf"' );
		echo $this->generate_pdf( $attendee_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Escape text for a PDF stream.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	protected function escape_pdf_text( string $text ) : string {
		return str_replace( array( '\\', '(', ')' ), array( '\\\\', '\(', '\)' ), $text );
	}

	/**
	 * Calculate PDF xref position.
	 *
	 * @param string $content Content stream.
	 * @return int
	 */
	protected function calculate_startxref( string $content ) : int {
		$before = "%PDF-1.4
1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj
2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj
3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj
4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj
5 0 obj << /Length " . strlen( $content ) . " >> stream
{$content}endstream endobj
";

		return strlen( $before );
	}
}

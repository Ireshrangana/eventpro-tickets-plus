<?php
/**
 * Ticket dashboard shortcode view.
 *
 * @package EventProTicketsPlus
 */

defined( 'ABSPATH' ) || exit;

$user      = wp_get_current_user();
$attendees = $user->exists() ? ( new \EventPro\TicketsPlus\Attendee_Service() )->get_attendees_by_email( $user->user_email ) : array();
$qr_service = new \EventPro\TicketsPlus\QR_Service();
?>
<div class="eptp-ticket-dashboard">
	<div class="eptp-section-heading">
		<h2><?php esc_html_e( 'My Tickets', 'eventpro-tickets-plus' ); ?></h2>
		<p><?php esc_html_e( 'View ticket codes, event details, and downloadable ticket files for your upcoming experiences.', 'eventpro-tickets-plus' ); ?></p>
	</div>
	<?php if ( $attendees ) : ?>
		<div class="eptp-ticket-grid">
			<?php foreach ( $attendees as $attendee ) : ?>
				<?php
				$event_id      = (int) get_post_meta( $attendee->ID, '_eptp_event_id', true );
				$ticket_code   = (string) get_post_meta( $attendee->ID, '_eptp_ticket_code', true );
				$download_link = add_query_arg(
					array(
						'eptp_download_ticket' => $attendee->ID,
						'eptp_token'           => eptp_generate_download_token( $attendee->ID ),
					),
					home_url( '/' )
				);
				?>
				<div class="eptp-ticket-card">
					<p class="eptp-kicker"><?php esc_html_e( 'Digital Pass', 'eventpro-tickets-plus' ); ?></p>
					<h3><?php echo esc_html( get_the_title( $event_id ) ); ?></h3>
					<p><?php echo esc_html( eptp_format_datetime( (string) eptp_get_event_meta( $event_id )['start_date'] ) ); ?></p>
					<strong><?php echo esc_html( $ticket_code ); ?></strong>
					<div class="eptp-qr"><?php echo $qr_service->render_svg( $attendee->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a href="<?php echo esc_url( $download_link ); ?>" class="eptp-button"><?php esc_html_e( 'Download PDF', 'eventpro-tickets-plus' ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'No tickets found yet.', 'eventpro-tickets-plus' ); ?></p>
	<?php endif; ?>
</div>

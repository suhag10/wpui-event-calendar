<?php
/**
 * Display table templates
 *
 * @package WPUI Event Calendar
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<table>
	<tbody>
		<tr>
			<th scope="row"><label for="event_date"><?php esc_html_e( 'Event Date', 'wpui-event-calendar' ); ?></label></th>
			<td>
				<input type="date" name="event_date" id="event_date" value="<?php echo isset( $event_date ) ? esc_attr( $event_date ) : ''; ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="event_time"><?php esc_html_e( 'Event Time', 'wpui-event-calendar' ); ?></label></th>
			<td>
				<input type="time" name="event_time" id="event_time" value="<?php echo isset( $event_time ) ? esc_attr( $event_time ) : ''; ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="event_venue"><?php esc_html_e( 'Event Venue', 'wpui-event-calendar' ); ?></label></th>
			<td>
				<input type="text" name="event_venue" id="event_venue" value="<?php echo isset( $event_venue ) ? esc_attr( $event_venue ) : ''; ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="event_organiser"><?php esc_html_e( 'Event Organiser', 'wpui-event-calendar' ); ?></label></th>
			<td>
				<input type="text" name="event_organiser" id="event_organiser" value="<?php echo isset( $event_organiser ) ? esc_attr( $event_organiser ) : ''; ?>" />
			</td>
		</tr>
	</tbody>
</table>

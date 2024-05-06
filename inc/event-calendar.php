<?php
/**
 * Display calendar
 *
 * @package WPUI Event Calendar
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Global database.
global $wpdb;

// Results event data.
$results = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type='events' AND post_status='publish'" );

// Stuff here for allowed roles.
if ( ! current_user_can( 'manage_options' ) ) {
	$wpdb->close();
}

?>

<script>
	document.addEventListener('DOMContentLoaded', function() {

		var calendarEl = document.getElementById('calendar');
		var calendar = new FullCalendar.Calendar(calendarEl, {

			initialView: 'dayGridMonth',
			editable: true,
			events: [
				<?php
				foreach ( $results as $event ) {

					$date     = get_post_meta( $event->ID, 'event_date', true );
					$time     = get_post_meta( $event->ID, 'event_time', true );
					$datetime = $date . ( empty( $time ) ? null : ' ' . $time );

					if ( ! empty( $event->post_title ) && ! empty( $date ) ) {
						?>
							{ 
								title: '<?php echo esc_attr( $event->post_title ); ?>', 
								start: '<?php echo esc_attr( $datetime ); ?>',
							},
							<?php
					}
				}
				?>
				{
					title: 'Dummy JS Event', 
					start: new Date(),
				},
			],
		});

		calendar.render();
	});
</script>

<div class="wrap">

	<h1 class="wp-heading-inline"><?php echo esc_html__( 'Event Calendar', 'wpui-event-calendar' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=events' ) ); ?>" class="page-title-action">
		<?php echo esc_html__( 'Events Table', 'wpui-event-calendar' ); ?>
	</a>
	<hr class="wp-header-end">

	<div id='calendar' style="margin-top: 30px;"></div>
	
</div>

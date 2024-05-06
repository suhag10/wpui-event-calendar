<?php
/**
 * Plugin Name: WPUI Event Calendar
 * Description: A plugin for wpui event calendar in WordPress.
 * Version:     1.0.0
 * Author:      Suhag Ahmed
 * Author URI:  https://github.com/suhag10/
 * Plugin URI:  https://github.com/suhag10/wpui-event-calendar
 * Text Domain: wpui-event-calendar
 *
 * @package WPUI Event Calendar
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class
 */
final class WPUI_Event_Calendar {

	/**
	 * Construcotr
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initializes
	 */
	public function init() {
		// Register custom post type.
		$this->register_event_cpt();

		add_action( 'wp_head', array( $this, 'count_event_view' ) );
		add_action( 'save_post', array( $this, 'save_post_event' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box_event' ) );

		// Add custom admin column.
		add_filter( 'manage_events_posts_columns', array( $this, 'add_event_date_column' ) );
		add_filter( 'manage_events_posts_custom_column', array( $this, 'display_event_date_column' ), 10, 2 );

		// add sortable column.
		add_filter( 'manage_edit-events_sortable_columns', array( $this, 'add_sortable_column' ) );

		// Add custom admin page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Save a custom meta box
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_post_event( $post_id ) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['event_nonce'] ) ? sanitize_file_name( $_POST['event_nonce'] ) : '';
		$nonce_action = 'add-event-nonce';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$event_date      = isset( $_POST['event_date'] ) ? sanitize_text_field( $_POST['event_date'] ) : '';
		$event_time      = isset( $_POST['event_time'] ) ? sanitize_text_field( $_POST['event_time'] ) : '';
		$event_venue     = isset( $_POST['event_venue'] ) ? sanitize_text_field( $_POST['event_venue'] ) : '';
		$event_organiser = isset( $_POST['event_organiser'] ) ? sanitize_text_field( $_POST['event_organiser'] ) : '';

		update_post_meta( sanitize_key( $post_id ), 'event_date', $event_date );
		update_post_meta( sanitize_key( $post_id ), 'event_time', $event_time );
		update_post_meta( sanitize_key( $post_id ), 'event_venue', $event_venue );
		update_post_meta( sanitize_key( $post_id ), 'event_organiser', $event_organiser );
	}

	/**
	 * Add custom meta box
	 */
	public function add_meta_box_event() {
		add_meta_box(
			'event_manage',                               // id.
			__( 'Event Manage', 'wpui-event-calendar' ), // title.
			array( $this, 'show_event' ),               // callback.
			array( 'events', 'advanced' ),             // screen.
		);
	}

	/**
	 * Display custom meta box
	 *
	 * @param WP_Post $post Post object.
	 */
	public function show_event( $post ) {
		$event_date      = get_post_meta( $post->ID, 'event_date', true );
		$event_time      = get_post_meta( $post->ID, 'event_time', true );
		$event_venue     = get_post_meta( $post->ID, 'event_venue', true );
		$event_organiser = get_post_meta( $post->ID, 'event_organiser', true );

		// Add nonce for security and authentication.
		wp_nonce_field( 'add-event-nonce', 'event_nonce' );

		include_once plugin_dir_path( __FILE__ ) . 'inc/display-table.php';
	}

	/**
	 * Register custom post type
	 */
	public function register_event_cpt() {
		register_post_type(
			'events',
			array(
				'labels'          => array(
					'name'          => __( 'Events', 'wpui-event-calendar' ),
					'singular_name' => __( 'Event', 'wpui-event-calendar' ),
					'add_new'       => __( 'Add New Event', 'wpui-event-calendar' ),
				),
				'public'          => true,
				'show_in_rest'    => true,
				'capability_type' => 'post',
				'has_archive'     => 'events',
				'rewrite'         => array(
					'slug'       => 'events',
					'with_front' => true,
				),
				'supports'        => array( 'title', 'editor', 'comments', 'thumbnail', 'excerpt' ),
				'taxonomies'      => array( 'category', 'post_tag' ),
			)
		);
	}

	/**
	 * Display custom admin column content
	 *
	 * @param WP_Post $column Post column.
	 * @param int     $post_id Post ID.
	 */
	public function display_event_date_column( $column, $post_id ) {
		// Get event from post meta.
		if ( 'event_date' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'event_date', true ) );
		}

		if ( 'event_time' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'event_time', true ) );
		}

		if ( 'event_views' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'event_views', true ) );
		}

		if ( 'event_venue' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'event_venue', true ) );
		}

		if ( 'event_organiser' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'event_organiser', true ) );
		}
	}

	/**
	 * Function to count post views
	 */
	public function count_event_view() {
		if ( is_single() ) {
			$event_views = get_post_meta( get_the_ID(), 'event_views', true );
			$event_views = $event_views ? $event_views : 0;
			++$event_views;
			update_post_meta( get_the_ID(), 'event_views', $event_views );
		}
	}

	/**
	 * Add custom admin column for event date
	 *
	 * @param WP_List_Table $columns Gets a list of columns.
	 * @return array
	 */
	public function add_event_date_column( $columns ) {
		$columns['event_date']      = esc_html__( 'Event Date', 'wpui-event-calendar' );
		$columns['event_time']      = esc_html__( 'Event Time', 'wpui-event-calendar' );
		$columns['event_views']     = esc_html__( 'Views', 'wpui-event-calendar' );
		$columns['event_venue']     = esc_html__( 'Venue', 'wpui-event-calendar' );
		$columns['event_organiser'] = esc_html__( 'Organiser', 'wpui-event-calendar' );

		return $columns;
	}

	/**
	 * Add sortable table
	 *
	 * @param WP_List_Table $columns Gets a list of sortable columns.
	 */
	public function add_sortable_column( $columns ) {
		$columns['event_date']  = 'Event Date';
		$columns['event_views'] = 'Views';

		return $columns;
	}

	/**
	 * Add custom admin page
	 */
	public function add_admin_menu() {
		$menu_title = 'Event Calendar';
		$capability = 'manage_options';
		$menu_slug  = 'wpui-event-calendar';
		$icon_url   = 'dashicons-calendar-alt';
		$position   = 20;

		add_menu_page(
			esc_html( $menu_title ),
			esc_html( $menu_title ),
			$capability,
			esc_attr( $menu_slug ),
			array( $this, 'display_calendar_page' ),
			esc_attr( $icon_url ),
			$position,
		);

		add_submenu_page(
			esc_attr( $menu_slug ),
			'Events Table',
			'Events Table',
			$capability,
			'edit.php?post_type=events',
		);

		remove_menu_page( 'edit.php?post_type=events' );
	}

	/**
	 * Display custom admin page content
	 */
	public function display_calendar_page() {
		include_once plugin_dir_path( __FILE__ ) . 'inc/event-calendar.php';
	}

	/**
	 * Enqueue scripts for admin page
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'toplevel_page_wpui-event-calendar' === $hook || isset( $_GET['page'] ) === 'wpui-event-calendar' ) {
			wp_enqueue_script( 'fullcalendar', '//cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js', array(), '1.0', true );
		}
	}
}

// Initialize the plugin.
new WPUI_Event_Calendar();

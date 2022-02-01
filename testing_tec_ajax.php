<?php
/***
Plugin Name: Testing The Events Calendar AJAX
Plugin URI: https://eventcalendarnewsletter.com/the-events-calendar-shortcode/
Description: Test ajax and tribe_get_events / tribe_events
Version: 1.0
Author: Event Calendar Newsletter
Author URI: https://eventcalendarnewsletter.com/the-events-calendar-shortcode
Contributors: brianhogg
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: the-events-calendar-shortcode
*/

function ajax_testing_get_calendar_events() {
    $atts = array_map( 'sanitize_text_field', $_POST );
    if ( ! isset( $atts['buttonlink'] ) ) {
        $atts['buttonlink'] = '';
    }

    $posts = tribe_get_events( [
        'post_status' => 'publish',
        'hide_upcoming' => true,
        'posts_per_page' => 500,
        'tax_query' => '',
        'meta_key' => '_EventStartDate',
        'orderby' => 'event_date',
        'author' => null,
        'order' => 'ASC',
        'meta_query' => [
            'relation' => 'AND',
        ],
        'start_date' => '2021-12-25',
        'end_date' => '2022-02-15',
    ] );

    $retval = [];
    foreach ( $posts as $post ) {
        setup_postdata( $post );
        $category_slugs = [];
        $event_categories = get_the_terms( $post->ID, Tribe__Events__Main::TAXONOMY );
        if ( is_array( $event_categories ) ) {
            foreach ( (array) $event_categories as $category ) {
                $category_slugs[] = ' ' . $category->slug . '_ecs_calendar_category';
            }
        }
        $retval[] = [
            'details' => tribe_events_template_data( $post ),
            'title' => get_the_title(),
            'start' => tribe_get_start_date( null, false, 'Y-m-d' ) . ( ( ! tribe_event_is_all_day() ) ? 'T' . tribe_get_start_time( null, 'H:i:s' ) : '' ),
            // Add one day for an all-day event so the calendar shows it correctly (inclusive)
            'end' => ( tribe_event_is_all_day() ? ( date( 'Y-m-d', strtotime( tribe_get_end_date( null, false, 'Y-m-d' ) . ' +1 day' ) ) ) : tribe_get_end_date( null, false, 'Y-m-d' ) ) . ( ( ! tribe_event_is_all_day() ) ? 'T' . tribe_get_end_time( null, 'H:i:s' ) : '' ),
            'url' => ( 'website' == $atts['buttonlink'] && tribe_get_event_website_url() ) ? tribe_get_event_website_url() : tribe_get_event_link(),
            'allDay' => tribe_event_is_all_day(),
            'categories' => implode( '', $category_slugs ),
            'excerpt' => tribe_events_get_the_excerpt( $post->ID, null, true ),
        ];
    }

    wp_send_json( [ 'events' => $retval ] );
}
add_action( 'wp_ajax_testing_calendar_events', 'ajax_testing_get_calendar_events' );
add_action( 'wp_ajax_nopriv_testing_calendar_events', 'ajax_testing_get_calendar_events' );

function testing_fetch_events( $atts ) {
    return '<div><button id="ecs-testing-events">Test Events</button></div>';
}
add_shortcode( 'testing-events', 'testing_fetch_events' );

function testing_events_register_scripts() {
    wp_enqueue_script( 'testing-fetch-events', plugins_url( 'testing_tec_ajax.js', __FILE__ ), ['jquery'], '1.0', true );
    wp_localize_script( 'testing-fetch-events', 'testing_fetch_events_object', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
}
add_action( 'wp_enqueue_scripts', 'testing_events_register_scripts' );
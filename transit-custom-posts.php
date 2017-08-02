<?php
/*
Plugin Name: Transit Custom Posts
Description: Creates route, alert, timetable, and other custom post types used for transit sites. Programmatically updates data from a GTFS feed.
Version: 1.0
Author: NomeQ
License: GPL2
*/

// Set up admin settings page
require_once('settings-page.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/alert.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/route.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/timetable.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/board-meeting.php');

if ( get_option('tcp_custom_types') ) {
	$custom_types = get_option('tcp_custom_types');
	
	// Use alerts if selected
	if ( in_array('tcp_use_alerts', $custom_types) ) {
		$alerts = TCP_Alert::getInstance();
	}
	if ( in_array('tcp_use_routes', $custom_types) ) {
		$routes = TCP_Route::getInstance();
	}
}

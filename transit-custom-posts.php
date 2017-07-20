<?php
/*
Plugin Name: Transit Custom Posts
Description: Creates route, alert, timetable, and other custom post types used for transit sites. Programmatically updates data from a GTFS feed.
Version: 1.0
Author: NomeQ
License: GPL2
*/

// Set up admin settings page

// For each setting, check if selected and add desired custom post types

require_once( plugin_dir_path( __FILE__ ) . 'cpts/custom-post-type.php');
$alert = new TCP_CustomPostType( 'Alert', array('menu_icon' => 'dashicons-warning') );


<?php
/*
 * Creates the plugin settings page using Wordpress settings API
 */

function tcp_settings_pages() {
    // Root menu for Plugin
    $page_title = 'Transit Custom Posts';
    $menu_title = 'Transit Custom Posts';
    $capability = 'manage_options';
    $menu_slug  = 'tcp_settings_page';
    $callback = 'tcp_custom_post_settings_content';
    $icon = 'dashicons-location';
    $position = 85;
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon, $position);
    
    $parent_slug = 'tcp_settings_page';
    $page_title = 'Custom Post Types';
    $menu_title = 'Custom Post Types';
    $capability = 'manage_options';
    $menu_slug = 'tcp_settings_page';
    $callback = 'tcp_custom_post_settings_content';
    add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback);
    
    $parent_slug = 'tcp_settings_page';
    $page_title = 'GTFS Settings';
    $menu_title = 'GTFS Settings';
    $capability = 'manage_options';
    $menu_slug = 'tcp_gtfs_settings';
    $callback = 'tcp_gtfs_settings_content';
    add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback);
    
    $parent_slug = 'tcp_settings_page';
    $page_title = 'Usage Guide';
    $menu_title = 'Usage Guide';
    $capability = 'manage_options';
    $menu_slug = 'tcp_usage_guide';
    $callback = 'tcp_usage_guide_content';
    add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback);   
}
add_action( 'admin_menu', 'tcp_settings_pages');

function tcp_custom_post_settings_content() {
    
}

function tcp_gtfs_settings_content() {
    
}

function tcp_usage_guide_content() {
    
}

    

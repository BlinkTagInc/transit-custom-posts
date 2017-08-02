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

function tcp_setup_settings_sections() {
	add_settings_section( 'cpt_fields', '', '', 'tcp_cpt_fields'  );
	add_settings_section( 'gtfs_fields', '', '', 'tcp_gtfs_fields' );
}
add_action( 'admin_init', 'tcp_setup_settings_sections' );

function tcp_setup_fields() {
	$fields = array(
		array(
			'uid'		=> 'tcp_custom_types',
			'label'		=> 'Custom Post Types',
			'section'	=> 'cpt_fields',
			'type'		=> 'multiple_checkbox',
			'options'	=> array(
				'tcp_use_routes'	=> 'Routes',
				'tcp_use_alerts'	=> 'Alerts',
				'tcp_use_timetables'	=> 'Timetables',
				'tcp_use_board'		=> 'Board Meetings',
			),
			'placeholder'	=> '',
			'helper'		=> '',
			'supplemental'	=> 'See the usage guide for more information.',
			'default'		=> array(),
			'settings'		=> 'tcp_cpt_fields',
			'classes'		=> '',
		),
		array(
			'uid' 		=> 'tcp_route_display',
			'label' 	=> 'Route Display',	
			'section'	=> 'tcp_routes_options',
			'type'		=> 'select',
			'options'	=> array(
				'long_name' => 'Long Name',
				'short_name' => 'Short Name',
				'circle_name' => 'Route Circle + Name',
			),
			'placeholder' => '',
			'helper'	=> '',
			'supplemental' => '',
			'default' => 'long_name',
			'settings' => 'tcp_cpt_fields',		
			'classes' => '',					
		),
		array(
			'uid'			=> 'tcp_gtfs_url',
			'label'			=> 'GTFS Feed Url',
			'section'		=> 'gtfs_fields',
			'type'			=> 'text',
			'options'		=> false,
			'placeholder'	=> '',
			'helper'		=> '',
			'supplemental'	=> 'Should point to a ZIP of your GTFS feed',
			'default'		=> '',
			'settings'		=> 'tcp_gtfs_fields',
			'classes'		=> 'regular-text',
		),	
	);
	foreach ( $fields as $field ) {
		add_settings_field( $field['uid'], $field['label'], 'tcp_field_callback', $field['settings'], $field['section'], $field);
		register_setting( $field['settings'], $field['uid'] );
	}
}
add_action( 'admin_init', 'tcp_setup_fields' );

function tcp_field_callback( $arguments ) {
    $value = get_option( $arguments['uid'] ); 
    if( ! $value ) { 
       $value = $arguments['default']; 
   }
// Check which type of field we want
switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
			case 'email' :
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" class="%5$s"/>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], esc_attr($value), $arguments['classes'] );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], esc_textarea($value) );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
			case 'multiple_checkbox':
				if (! empty ($arguments['options']) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					$iterator = 0;
					foreach( $arguments['options'] as $key => $label){
						$iterator++;
						$options_markup .= sprintf( '<label for="%1$s_%5$s"><input id="%1$s_%5$s" name="%1$s[%2$s]" type="checkbox" value="%2$s" %3$s> %4$s</label><br/>', $arguments['uid'], $key, in_array($key, $value) ? 'checked' : '', $label, $iterator);
					}
					printf( '<fieldset>%s</fieldset>', $options_markup );
				}
				break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value, $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }

// If there is help text
   if( $helper = $arguments['helper'] ){
       printf( '<span class="helper"> %s</span>', $helper ); 
   }

// If there is supplemental text
   if( $supplemental = $arguments['supplemental'] ){
       printf( '<p class="description">%s</p>', $supplemental );
   }
}

function tcp_custom_post_settings_content() {
    ?>
	<div class="wrap">
		<h1>Custom Posts and Settings</h1>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'tcp_cpt_fields' );
			do_settings_sections( 'tcp_cpt_fields' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function tcp_gtfs_settings_content() {
	?>
	<div class="wrap">
		<h1>GTFS Feed and Options</h1>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'tcp_gtfs_fields' );
			do_settings_sections( 'tcp_gtfs_fields' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function tcp_usage_guide_content() {
	?>
	<div class="wrap">
		<h1>Transit Custom Posts Usage Guide</h1>
		<p>Thank you for downloading Transit Custom Posts. For a complete working starter theme, download the TransitPress theme. Otherwise, you will need to customize your theme (or a child theme) to utilize most of this plugin's features.</p>
		<h2>Getting Started</h2>
		<p>Lorem ipsum, etc</p>
		<h2>GTFS Update</h2>
		<p>Beep Boop</p>
		<h2>Plugin Functions</h2>
		<p>More stuff</p>
	</div>
	<?php
}

if ( get_option('tcp_custom_types') ) {
	$custom_types = get_option('tcp_custom_types');

	if ( in_array('tcp_use_routes', $custom_types) ) {
		add_action( 'admin_init', 'tcp_setup_route_options' );
	}
	if ( in_array('tcp_use_alerts', $custom_types) ) {
		add_action( 'admin_init', 'tcp_setup_alert_options' );
	}
	if ( in_array('tcp_use_timetables', $custom_types) ) {
		add_action( 'admin_init', 'tcp_setup_timetable_options' );
	}
	if ( in_array('tcp_use_board', $custom_types) ) {
		add_action( 'admin_init', 'tcp_setup_board_options' );
	}
}

function tcp_setup_route_options() {
	add_settings_section( 'tcp_routes_options', 'Route Options', '', 'tcp_cpt_fields'  );
}

function tcp_setup_alert_options() {
	add_settings_section( 'tcp_alerts_options', 'Alert Options', '', 'tcp_cpt_fields' );
}

function tcp_setup_timetable_options() {
	add_settings_section( 'tcp_timetables_options', 'Timetable Options', '', 'tcp_cpt_fields' );
}

function tcp_setup_board_options() {
	add_settings_section( 'tcp_board_options', 'Board Meeting Options', '', 'tcp_cpt_fields' );
}

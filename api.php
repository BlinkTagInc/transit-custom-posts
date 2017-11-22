<?php
/**
 * Convenience Functions for accessing fields and data
 **/

// TODO: determine whether or not to use post_id, route_id, both, or 
// provide additional api functions

// TODO: create shortcodes that can be included in posts
function tcp_list_routes( $args = array() ) {
	if ( !post_type_exists( 'route') ) {
		// Fail silently?
		return;
	}
	$defaults = array(
		'before'		=> '<div class="tcp_route_list">',
		'after'			=> '</div>',
		'sep'			=> ' ',
		'use_color'		=> false,
		'show_circle'	=> false,
		'route_name'	=> 'long_name',
		'show_alert'	=> false,
		
	);
	$args = wp_parse_args( $args, $defaults );
	
	// route sort 
	$order = get_option('tcp_route_sortorder');
	$orderby = $order == 'route_sort_order' ? array( 'meta_value_num' => 'ASC', 'title' => 'ASC') : 'title';
	$route_args = array(
		'post_type'		=> 'route',
		'numberposts'	=> -1,
		'meta_key'			=> $order,
		'orderby'			=> $orderby,
	);
	$route_posts = get_posts( $route_args );
	$rcolor = '';
	$routes = array();
	foreach ( $route_posts as $route ) {
		if ( $args['use_color'] ) {
			$text = '#' . get_post_meta( $route->ID, 'route_text_color', true);
			$background = '#' . get_post_meta( $route->ID, 'route_color', true);
			$rcolor = 'style="background:'. $background . '; color:' . $text . ';"';
		}
		$alert_icon = '';
		if ( $args['show_alert'] ) {
			$query_args = array(
				'post_type'			=> 'alert',
				'posts_per_page'	=> -1,
				'meta_query'		=> array(
					'relation'	=> 'AND',
					array(
						'relation' => 'OR',
						array(
							'key'		=> 'end_date',
							'value'		=> '',
							'compare'	=> '==',
						),
						array(
							'key'		=> 'end_date',
							'value' 	=> date("Y-m-d"),
							'compare'	=> '>=',
							'type'		=> 'DATE',
						),
					),
					array(
						'key'		=> 'affected_routes',
						'value'		=> $route->post_name,
						'compare'	=> 'LIKE',
					)
				)
			);
			$q = new WP_Query( $query_args );
			if ( $q->have_posts() ) {
				$alert_icon = file_get_contents( plugin_dir_path( __FILE__ ) . 'inc/icon-alert.php' );
			}	
		}
		$routes[] = '<a href="' . get_the_permalink($route->ID) . '" class="' . $route->post_name . '"' . $rcolor . '>' . $alert_icon .get_route_name($route->ID) . '</a>';
		
	}
	echo $args['before'] . join( $args['sep'], $routes ) . $args['after'];
}

/*
 * For use inside the loop
 * TODO: add handling for route circles instead of route color
 * Create this as a filter for 'the_title' instead of as a custom fn?
 */
function the_route_title() {
	global $post;
	if ( !post_type_exists( 'route' ) || $post->post_type != 'route' ) {
		return;
	}
	$title = get_route_name($post->ID);
	$style = '';
	if ( strpos(get_option('tcp_route_display'), '%route_circle%') === false ) {
		$color = '#' . get_post_meta( $post->ID, 'route_color', true);
		$text = '#' . get_post_meta( $post->ID, 'route_text_color', true);
		$style = 'style="background:' . $color . '; color:' . $text . ';"';
	}
	$html = '<h1 class="page-title route-title" ' . $style .'>' . $title . '</h1>';
	echo $html;
}

function get_route_name($post_id = NULL) {
	// Fail silently if routes aren't in use
	if ( !post_type_exists( 'route' ) ) {
		return '';
	}
	// Use the current global post if no id is provided
	if ( empty($post_id) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	// Allow name to be overridden
	if ( get_post_meta( $post_id, 'route_custom_name', true ) != '' ) {
		return get_post_meta( $post_id, 'route_custom_name', true );
	}
	$format = get_option('tcp_route_display');
	
	$format = str_replace('%short_name%', get_post_meta($post_id, 'route_short_name', true), $format);
	$format = str_replace('%long_name%', get_post_meta($post_id, 'route_long_name', true), $format);
	$format = str_replace('%route_circle%', get_route_circle($post_id), $format);
	
	// Provide filter for even further customization
	$format = apply_filters('tcp_route_name', $format);
	
	return $format;
}

/*
 * Retrieves route information from timetables
 * Requires timetables.txt 
 */
function the_route_meta() {
	return;
}

function get_route_circle($post_id = NULL, $size = "medium" ) {
	if ( !post_type_exists( 'route' ) ) {
		return;
	}
	if ( empty($post_id) ) {
		global $post;
		$post_id = $post->ID;
	}
	$route_color = get_post_meta( $post_id, 'route_color', true );
	$text_color = get_post_meta( $post_id, 'route_text_color', true );
	$text = get_post_meta( $post_id, 'route_short_name', true);
	$html = sprintf('<span class="route-circle route-circle-%1$s" style="background-color: #%2$s; color: #fff">%4$s</span>', $size, $route_color, $text_color, $text);
	return $html;
}

/* TODO: create option to use outside loop? */
function the_route_description() {
	global $post;
	if ( !post_type_exists( 'route' ) ) {
		return;
	}
	echo get_post_meta( $post->ID, 'route_desc', true );
}


// TODO: add option for route circles, etc with Affected Routes output
function tcp_do_alerts( $args = array() ) {
	if ( !post_type_exists( 'alert' ) ) {
		return;
	}
	$defaults = array(
		'collapse'				=> true,
		'single_route'			=> false,
		'show_affected'			=> true,
		'sep_affected'			=> ', ',
		'display_affected'		=> get_option('tcp_route_display'),
		'number_posts'			=> -1,
	);
	global $post;
	if ( $post->post_type == 'route' ) {
		$defaults['single_route']	= true;
		$defaults['show_affected']	= false;
	}
	$args = wp_parse_args( $args, $defaults );
	$query_args = array(
		'post_type'			=> 'alert',
		'posts_per_page'	=> $args['number_posts'],	
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'		=> 'end_date',
				'value'		=> '',
				'compare'	=> '==',
			),
			array(
				'key'		=> 'end_date',
				'value' 	=> date("Y-m-d"),
				'compare'	=> '>=',
				'type'		=> 'DATE',
			),
		),
	);
	if ( $args['single_route'] ) {
		$query_args['meta_query'] = array(
			'relation'	=> 'AND',
			array(
				'relation' => 'OR',
				array(
					'key'		=> 'end_date',
					'value'		=> '',
					'compare'	=> '==',
				),
				array(
					'key'		=> 'end_date',
					'value' 	=> date("Y-m-d"),
					'compare'	=> '>=',
					'type'		=> 'DATE',
				),
			),
			array(
				'relation' => 'OR',
				array(
					'key'		=> 'affected_routes',
					'value' 	=> 'all',
					'compare'	=> 'LIKE',
				),
				array(
					'key'		=> 'affected_routes',
					'value'		=> $post->post_name,
					'compare'	=> 'LIKE',
				),
			),
		);
	}
	$alert_query = new WP_Query( $query_args );
	if ( $alert_query->have_posts() ) {
		echo '<div class="tcp_alerts">';
		echo '<h3 class="tcp_alert_header">' . file_get_contents( plugin_dir_path( __FILE__ ) . 'inc/icon-alert.php' ) . 'Alerts</h3>';
		echo '<div class="container">';
		
		while ( $alert_query->have_posts() ) {
			$alert_query->the_post();
			
			$date_text = tcp_get_alert_dates( get_the_ID() );
			$affected_text = $args['show_affected'] ? 'Affected routes: ' . tcp_get_affected( get_the_ID(), $args['sep_affected'] ) : '';
			
			echo '<div class="tcp_panel">';
			
			printf('<div class="panel_heading %s" data-target="%s"><h4><a href="%s">%s</a></h4><div class="panel_subheading">%s<span class="tcp_affected_routes">%s</span></div></div>', $args['collapse'] ? 'collapse_toggle' : '', 'panel_' . get_the_ID(), get_permalink(), get_the_title(), $date_text, $affected_text);
			
			if ( $args['collapse'] ) {
				printf('<div class="panel_body" id="%s">%s</div>', 'panel_' . get_the_ID(), get_the_content());
			}
			echo '</div>';
		}
		echo '</div></div>';
		
		wp_reset_postdata();
	}
	return;
}

function tcp_get_alert_dates( $post_id = null ) {
	if ( empty($post_id) ) {
		global $post;
		$post_id = $post->ID;
	}
	$effective_date = mysql2date( get_option('date_format'), get_post_meta($post_id, 'effective_date', true) );
	
	$end_date = mysql2date( get_option('date_format'), get_post_meta($post_id, 'end_date', true) );
	
	// Logic for printing the date if start, end, or both are present
	$date_text = '';
	if ( !empty($effective_date) ) {
		$date_text = 'Effective: ' . $effective_date;
		
		if ( !empty($end_date) ) {
			$date_text .= ' - ' . $end_date;
		}
	} elseif ( !empty($end_date) ) {
		$date_text = 'Expires: ' . $end_date;
	}
	return $date_text;
}

function the_timetables( $args = array() ) {
	global $post;
	if ( !post_type_exists( 'timetable' ) || $post->post_type != 'route') {
		return;
	}
	$route_id = get_post_meta($post->ID, 'route_id', true);
	$date = new DateTime();
	$today = intval($date->format('Ymd'));
	$date->add(new DateInterval('P3D'));
	$soon = intval($date->format('Ymd'));
	
	$timetable_args = array(
		'post_type'			=> 'timetable',
		'posts_per_page'	=> -1,
		'meta_query'		=> array(
			'relation'	=> 'AND',
			array(
				'key' => 'route_id',
				'value' => $route_id,
			),
			array(
				'relation' => 'AND',
				array(
					'key' => 'start_date',
					'value' => $soon,
					'compare'=> '<=',
					'type' => 'NUMERIC'
				),
				array(
					'key' => 'end_date',
					'value' => $today,
					'compare' => '>=',
					'type' => 'NUMERIC'
				),
			)
		)
	);
	$timetables = new WP_Query( $timetable_args );
	if ( $timetables->have_posts() ) {
		while ( $timetables->have_posts() ) {
			$timetables->the_post();
			$dir = get_post_meta( get_the_ID(), 'direction_label', true);
			$days = get_post_meta( get_the_ID(), 'days_of_week', true);
			printf('<div class="timetable-holder" data-dir="%s" data-days="%s">', $dir, $days);
			echo '<h2>' . $dir . '</h2>';
			echo '<div class="subtitle">' . $days . '</div>';
			the_content();
			echo '</div>';
		}
		wp_reset_postdata();
	}
}

// Where the $post is an alert
function tcp_get_affected( $post_id = null, $sep = ', ') {
	if ( !post_type_exists('route') || !post_type_exists('alert')) {
		return;
	}
	if ( empty($post_id) ) {
		global $post;
		$post_id = $post->ID;
	}
	$the_affected = get_post_meta( $post_id, 'affected_routes', true );
	if ( get_option('tcp_alert_custom_display_affected') ) {
		$the_affected = apply_filters( 'tcp_display_affected', $the_affected );
	} else {
		$the_affected = array_map( 'tcp_route_name_from_tag', $the_affected );
	}
	return join($the_affected, $sep);
}

function tcp_route_name_from_tag( $route_tag ) {
	//get the id from the tag (slug)
	$r_post = get_page_by_path( $route_tag, OBJECT, 'route' );
	if ( empty( $r_post) ) {
		// Page doesn't exist or filter was applied
		return $route_tag;
	}
	return get_route_name( $r_post->ID );
}
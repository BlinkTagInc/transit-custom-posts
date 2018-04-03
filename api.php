<?php
/**
 * Public API functions
 */

 /**
 * Returns a sorted array of route objects.
 */
 function tcp_get_routes() {
 	if ( !post_type_exists( 'route') ) {
 		// Fail silently
 		return;
 	}

 	// route sort
 	$order = get_option('tcp_route_sortorder');
 	$orderby = $order == 'route_sort_order' ? array( 'meta_value_num' => 'ASC', 'title' => 'ASC') : 'title';
 	$route_args = array(
 		'post_type'		=> 'route',
 		'numberposts'	=> -1,
 		'meta_key'			=> $order,
 		'orderby'			=> $orderby,
 	);
 	return get_posts( $route_args );
}

/**
* Outputs all route names with formatting.
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type string "before" Text or HTML displayed before route list.
*         Default: '<div class="tcp_route_list">'
*     @type string "after" Text or HTML displayed after route list.
*         Default: '</div>'
*     @type string "sep" Text or HTML displayed between items.
*         Default: ' '
*     @type bool "use_color" Add route color as background style.
*         Default: false
*     @type bool "show_alert" Display alert icon if route has active alert
*         Default: false
*     @type bool "show_circle" Deprecated. @see get_route_name()
*     @type string "route_name" Deprecated. @see get_route_name()
* }
*/
function tcp_list_routes( $args = array() ) {
	if ( !post_type_exists( 'route') ) {
		// Fail silently
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

	$route_posts = tcp_get_routes();
	$rcolor = '';
	$routes = array();

	// Format and output each route in the database
	foreach ( $route_posts as $route ) {

		// Use route_color and route_text_color to style route
		if ( $args['use_color'] ) {
			$text = '#' . get_post_meta( $route->ID, 'route_text_color', true);
			$background = '#' . get_post_meta( $route->ID, 'route_color', true);
			$rcolor = 'style="background:'. $background . '; color:' . $text . ';"';
		}
		$alert_icon = '';


		if ( $args['show_alert'] ) {

			// Query active alerts for the route
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
		// Add formatted route link to an array
		$routes[] = '<a href="' . get_the_permalink($route->ID) . '" class="' . $route->post_name . '"' . $rcolor . '>' . $alert_icon .get_route_name($route->ID) . '</a>';

	}
	echo $args['before'] . join( $args['sep'], $routes ) . $args['after'];
}

/**
* Displays the route title with formatting from plugin options.
*
* To be used inside the loop of a route post, otherwise fails silently.
*
* @global WP_Post $post
*
*/
function the_route_title() {
	global $post;
	if ( !post_type_exists( 'route' ) || $post->post_type != 'route' ) {

		// Fail silently
		return;
	}

	$title = get_route_name( $post->ID );

	$style = '';

	// Use route color as background if route circle not in use
	if ( strpos(get_option('tcp_route_display'), '%route_circle%') === false ) {
		$color = '#' . get_post_meta( $post->ID, 'route_color', true);
		$text = '#' . get_post_meta( $post->ID, 'route_text_color', true);
		$style = 'style="background:' . $color . '; color:' . $text . ';"';
	}

	$html = '<h1 class="page-title route-title" ' . $style .'>' . $title . '</h1>';

	echo $html;
}

/**
* Outputs formatted route name.
*
* @global WP_Post $post
*
* @param int $post_id Optionally specify post id for route outside loop.
* @return string Formatted route name.
*/
function get_route_name( $post_id = NULL ) {

	if ( !post_type_exists( 'route' ) ) {

		// Return empty string if routes not in use
		return '';
	}

	// Use the current global post if no id is provided
	if ( empty( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}

	// Allow name to be overridden
	if ( get_post_meta( $post_id, 'route_custom_name', true ) != '' ) {
		return get_post_meta( $post_id, 'route_custom_name', true );
	}
	$format = get_option('tcp_route_display');

	// Replace magic tags with meta values
	$format = str_replace('%short_name%', get_post_meta($post_id, 'route_short_name', true), $format);
	$format = str_replace('%long_name%', get_post_meta($post_id, 'route_long_name', true), $format);
	$format = str_replace('%route_circle%', get_route_circle($post_id), $format);

	/**
	* Filters the formatted route name.
	*
	* @param string $format The formatted route name
	*/
	$format = apply_filters('tcp_route_name', $format);

	return $format;
}

/**
 * Not implemented.
 */
function the_route_meta() {
	return;
}

/**
* Generates HTML for a route circle.
*
* @global WP_Post $post
*
* @param int $post_id Optionally specify post id for route outside loop.
* @param string $size Size class to output. Default: "medium".
*
* @return string Formatted route circle HTML.
*/
function get_route_circle( $post_id = NULL, $size = "medium" ) {

	if ( !post_type_exists( 'route' ) ) {

		// Fail silently if routes don't exist.
		return;
	}

	if ( empty( $post_id ) ) {

		// Setup global postdata.
		global $post;
		$post_id = $post->ID;
	}

	// Get route metadata
	$route_color = get_post_meta( $post_id, 'route_color', true );
	$text_color = get_post_meta( $post_id, 'route_text_color', true );
	$text = get_post_meta( $post_id, 'route_short_name', true);

	$html = sprintf('<span class="route-circle route-circle-%1$s" style="background-color: #%2$s; color: #fff">%4$s</span>', $size, $route_color, $text_color, $text);

	return $html;
}

/**
* Outputs route description from post meta.
*
* Shortcut for outputting the route description inside the loop.
*
* @global WP_Post $post
* @see get_post_meta()
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type string "before" Text or HTML displayed before route description.
*         Default: '<div class="tcp_route_description">'
*     @type string "after" Text or HTML displayed after route description.
*         Default: '</div>'
*/
function the_route_description($args = array()) {
	global $post;
	if ( !post_type_exists( 'route' ) ) {
		return;
	}

	$defaults = array(
		'before'		=> '<div class="tcp_route_description">',
		'after'			=> '</div>',
	);
	$args = wp_parse_args( $args, $defaults );

	$description = get_post_meta( $post->ID, 'route_description', true );

	echo $args['before'] . $description . $args['after'];
}

/**
* Outputs all current alerts with metadata and formatting.
*
* By default, creates a collapsible container and only outputs a single
* route's alerts when used within the loop on a route page.
*
* @global WP_Post $post
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type bool "collapse" Create collapsible div with full alert text.
*         Default: true
*     @type bool "single_route" Only show a single route's alerts.
*         Default: false
*     @type bool "show_affected" Show routes affected by this alert.
*         Default: true
*     @type string sep_affected" Separator to use if "show_affected" is true.
*         Default: ", "
*     @type int "number_posts" Number of alerts to show.
*         Default: -1
* }
*
* @return int Number of alerts or false
*/
function tcp_do_alerts( $args = array() ) {

	if ( !post_type_exists( 'alert' ) ) {
		return false;
	}

	$defaults = array(
		'collapse'				=> true,
		'single_route'			=> false,
		'show_affected'			=> true,
		'sep_affected'			=> ', ',
		'number_posts'			=> -1,
		'affected_text'			=> 'Affected Routes: ',
	);

	global $post;
	if ( $post->post_type == 'route' ) {
		$defaults['single_route']	= true;
		$defaults['show_affected']	= false;
	}

	// Overwrite defaults with supplied $args
	$args = wp_parse_args( $args, $defaults );

	// Get alerts where the end date is either not set
	// or is in the future.
	// TODO: alerts with no end date are still not appearing.
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

	// Overwrite meta query for single route alerts
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

			// Retrieve formatted date text for effective date(s)
			$date_text = tcp_get_alert_dates( get_the_ID() );

			$affected_text = $args['show_affected'] ? $args['affected_text'] . tcp_get_affected( get_the_ID(), $args['sep_affected'] ) : '';

			echo '<div class="tcp_panel">';

			printf('<div class="panel_heading %s" data-target="%s"><h4><a href="%s">%s</a></h4><div class="panel_subheading">%s<span class="tcp_affected_routes">%s</span></div></div>', $args['collapse'] ? 'collapse_toggle' : '', 'panel_' . get_the_ID(), get_permalink(), get_the_title(), $date_text, $affected_text);

			// Create collapisble panel with the_content() of alert
			if ( $args['collapse'] ) {
				printf('<div class="panel_body" id="%s">%s</div>', 'panel_' . get_the_ID(), get_the_content());
			}
			echo '</div>';
		}
		echo '</div></div>';

		wp_reset_postdata();
		return $alert_query->post_count;
	}
	return false;
}

/**
* Creates text for alert effective date range.
*
* @global WP_Post $post
*
* @param int $post_id Optionally specify post id for route outside loop.
*
* @return string Formatted date range text.
*/
function tcp_get_alert_dates( $post_id = null ) {

	if ( empty($post_id) ) {

		// Setup postdata
		global $post;
		$post_id = $post->ID;
	}

	// Get the effective date and format using global site settings
	$effective_date = mysql2date( get_option('date_format'), get_post_meta($post_id, 'effective_date', true) );

	// Get the end date and also format using global site settings
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

/**
* Outputs all timetables for a route from inside the loop.
*
* @global WP_Post $post
*
* @param array $args Not implemented.
*/
function the_timetables( $args = array() ) {

	global $post;

	if ( !post_type_exists( 'timetable' ) || $post->post_type != 'route') {
		// Fail silently.
		return;
	}

	$route_id = get_post_meta($post->ID, 'route_id', true);

	$date = new DateTime();
	$today = intval($date->format('Ymd'));

	// Set a date 3 days in the future from today
	$date->add(new DateInterval('P3D'));
	$soon = intval($date->format('Ymd'));

	// Only grab timetables that are active or will be active starting $soon
	$timetable_args = array(
		'post_type'			=> 'timetable',
		'posts_per_page'	=> -1,
		'orderby'			=> 'meta_value_num',
		'meta_key'			=> 'timetable_id',
		'order'				=> 'ASC',
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
					'type' => 'NUMERIC',
				),
				array(
					'key' => 'end_date',
					'value' => $today,
					'compare' => '>=',
					'type' => 'NUMERIC',
				),
			),
		),
	);
	$timetables = new WP_Query( $timetable_args );

	if ( $timetables->have_posts() ) {
		while ( $timetables->have_posts() ) {
			$timetables->the_post();

			// Get timetable metadata
			$dir = get_post_meta( get_the_ID(), 'direction_label', true);
			$days = get_post_meta( get_the_ID(), 'days_of_week', true);

			// Create a timetable div with data attributes for optional JS manipulation
			printf('<div class="timetable-holder" data-dir="%s" data-days="%s">', $dir, $days);
			echo '<h2>' . $dir . '</h2>';
			echo '<div class="subtitle">' . $days . '</div>';

			// Should be HTML or an image
			the_content();

			echo '</div>';
		}
		wp_reset_postdata();
	}
}

/**
* Retrives and formats affected route postmeta from an alert.
*
* @global WP_Post $post
*
* @param int $post_id Optional post id if used outside the loop
* @param string $sep Separator to use between route names. Default: ", "
*
* @return string Formatted route names.
*/
function tcp_get_affected( $post_id = null, $sep = ', ') {

	if ( !post_type_exists('route') || !post_type_exists('alert')) {
		// Fail silently.
		return;
	}

	if ( empty($post_id) ) {
		global $post;
		$post_id = $post->ID;
	}

	$the_affected = get_post_meta( $post_id, 'affected_routes', true );

	if ( get_option('tcp_alert_custom_display_affected') ) {

		/**
		* Filters the display text for an alert's affected routes.
		*
		* @param array $the_affected Array of route slug strings.
		*/
		$the_affected = apply_filters( 'tcp_display_affected', $the_affected );

	} else {

		$the_affected = array_map( 'tcp_route_name_from_tag', $the_affected );

	}

	return join($the_affected, $sep);
}

/**
* Gets the formatted route name using the route post slug.
*
* @param string $route_tag Route post slug.
*
* @return string Formatted route name.
*/
function tcp_route_name_from_tag( $route_tag ) {

	//get the id from the tag (slug)
	$r_post = get_page_by_path( $route_tag, OBJECT, 'route' );

	if ( empty( $r_post ) ) {

		// Page doesn't exist or filter was applied
		return $route_tag;
	}
	return get_route_name( $r_post->ID );
}

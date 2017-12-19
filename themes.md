# Theme Integration

We attempt in all cases to follow best practices and separate form from function. Thus, this plugin is of little use without a theme to implement it. You can choose to use the API functions in a child theme or use a transit-custom-posts compatible theme. You are welcome to use and build off our [Transit Base Template](https://github.com/trilliumtransit/transit-base-template), or simply use it as a working example for how to implement the functions in the [plugin API](api.md).

## How to Use

The [Transit Base Template](https://github.com/trilliumtransit/transit-base-template) is a great example of php templates, css styles, and api functions in action. A combination of all three will allow you to take advantage of the most powerful features of this plugin. Read more about using [custom post types in your theme](https://codex.wordpress.org/Post_Types) in the Wordpress codex.

#### Recommended PHP templates

* `single-route.php` : Displays routes, can make use of `the_route_title()`, `the_route_description()`, `tcp_do_alerts()`, and `the_timetables()` from the API
* `front-page.php` : Although not specifically a custom post template, most transit agencies will want a static homepage as opposed to a blog view. Can make use of `tcp_do_alerts()` and `tcp_list_routes()` to include relevant information of the front page
* `single-alert.php` : Use custom display for alerts instead of default `single.php`
* `archive-alert.php` : Show all alerts in blog view (alternative: create `template-alert.php` for a static alert page)

#### Recommended Styles

See this [set of plugin-related styles](https://github.com/trilliumtransit/transit-base-template/blob/master/sass/site/_custom-posts.scss) for an idea of elements that require default styles.

```css
.route-circle {
	position: relative;
	display: inline-block;
	text-align: center;
	border: 1px solid #fff;
}

.route-circle-small {
	font-size: 13px;
	width: 22px;
	height: 22px;
	line-height: 20px;
	border-radius: 11px;
}

/* Etc for other circle sizes such as medium, large */

.tcp_alerts {
	/* Style the alerts widget */
}

```

#### Using Plugin Options

It is possible to override plugin options (set in the WP Admin area) within the theme. Most API functions will take plugin options into account, such as route sort order and route custom names. Unless necessary for your particular theme or agency, we recommend using either the API functions or implementing the user options.

## Compatible Themes

Please let us know of any transit-custom-posts compatible themes and we will add them to this list.

* [Transit Base Template](https://github.com/trilliumtransit/transit-base-template)
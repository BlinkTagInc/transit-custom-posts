# Developer API

[Read the full docs](https://trilliumtransit.github.io/transit-custom-posts/api-docs/)

The developer API provides a number of convenience functions to use in your theme that handle issues such as consistent route names, accessing active alerts and timetables, and retrieving post metadata imported from GTFS.

The easiest way to become familiar with the API is to take a look at `front-page.php` and `template-parts/content-route.php` inside the [Transit Base Template](https://github.com/trilliumtransit/transit-base-template). 

## Examples

```html
<!-- content-route.php -->
<div class="single-route route">
	
	<?php the_route_title(); ?>

	<div class="entry-content">
		
		<?php the_route_description(); ?>
		
		<?php tcp_do_alerts( array('collapse' => 'false' ) ); ?>

		<?php the_timetables(); ?>
		
	</div><!-- .entry-content -->
</div>
```

[Return to overview](index.md)
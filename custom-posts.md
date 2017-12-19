# Custom Post Types

There are four optional custom post types that can be individually activated: routes, timetables, alerts, and board meetings. Routes and timetables can be updated via [GTFS update](gtfs-update.md). Although alerts and meetings are not derived from GTFS data, we've opted to include them because they are present on most transit sites and benefit greatly from being integrated. For example, when creating an alert you are able to select affected routes from a menu of all active routes.

## Settings

The plugin has two settings pages: Custom Post Types and GTFS Settings. The GTFS Settings are covered in the [GTFS update](gtfs-update.md) section. If you have not selected any custom post types, the settings page will look quite empty. Each custom post type has its own settings for display. These settings can, in some cases, be overridden in the theme.

## Routes

Routes are the most common custom post type and often one of the most important types of content on a transit site. (There are, of course, exceptions.) The plugin will pull the route's short name, long name, description, color, and sort order from the GTFS if the fields are available. 

### Options

**Route Display** : For the most part, this will determine how route names show up on the site (It is possible to override this in the theme). Three magic keywords are available.

* *%long_name%* shows the route long name from the GTFS
* *%short_name%* shows the route short name from the GTFS
* *%route_circle%* will create a small route circle icon. It relies on the routes having numeric short names and colors set.
* All other text will be displayed as-is

Common route display options include `%route_circle %long_name` and `Route %short_name%`

These route display settings can be overridden individually from the Edit Route screen in the WP Admin area using the *Route Custom Name* field. 

**Sort Order** : The sort order selection is primarily a back-up if your GTFS data does not have route sort orders set. If the route sort order field is null or non-existent for any of your routes, it may break several functions throughout the site. In that case, choose to sort by either short or long name.

### Adding and Editing Routes

Although you should be able to get most of the pertinent information from the GTFS feed, you can still create a new route simply by clicking on Routes > Add New from the WP Admin screen. Similarly, you can edit existing routes if for some reason you need the site information to deviate from the GTFS feed data. 

Some themes may implement the content from the Wordpress editor for displaying route pages as well. 

## Alerts

It is simple to create alerts, attach them to specific routes (or make them system-wide), and set an expiration date so they disappear without maintenance. By default, only alerts which affect a specific route will show up on that route page, and all alerts will show up on the home page or other (non-route) pages.

### Options

**Custom display affected routes** : Allow the theme to override how affected routes are shown in the alert box and alert widget. This is primarily a developer option; themes should note if it is necessary. See the [developer API](api.md) for more information.

### Adding and Editing Alerts

An alert will only show up if both an effective date and an end date are set. The alert will no longer show up after the end date, although you will still need to delete it manually to remove it completely. 

In cases where there is no end date (e.g. when the alert is a permanent change, such as a new stop order), we recommend making the end date something safely after the change, to give time for riders to read the notice. This is a known [bug](https://github.com/trilliumtransit/transit-custom-posts/issues/1) that some users may wish to not specify a date for clarity.

### The Alert Widget

An alert widget makes it easy to drag-and-drop alerts into any type of Wordpress theme from the Widgets screen. It can be customized with the number of alerts to show, and whether or not to include affected routes. 

## Timetables

We recommend generating timetables with [GTFS-to-HTML](https://github.com/BlinkTagInc/gtfs-to-html); generating the timetable HTML is beyond the scope of this plugin. Both GTFS-to-HTML and this plugin rely on an optional GTFS file: `timetables.txt`. More information can be found in [GTFS update](gtfs-update.md).

You can still use the timetable custom posts without the `timetables.txt` file or using GTFS-to-HTML, however, you will manually need to enter at least:

* *Route ID* : The connected route ID (currently the plugin does not support multiple routes for a single timetable)
* *Direction Label* : Service direction, or 'Loop'
* *Days of Week* : A label for what days this timetable represents, e.g. Mon-Thurs
* *Start Date* : yyyymmdd format
* *End Date* : yyyymmdd format

In addition, you'll need to supply either the timetable HTML or an image as the post content. 

## Board Meetings

The meetings custom post type is still under discussion; it's commonly used by many transit agencies but doesn't actually interact with the GTFS in any way. 


[Back to Overview](index.md)
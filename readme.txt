=== WP Event Calendar ===
Contributors: johnjamesjacoby, stuttter
Tags: event, calendar, session, appointment, month, week, day, category, tag, term, type
Requires at least: 4.7
Tested up to: 4.8
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9Q4F4EL5YJ62J

== Description ==

WP Event Calendar is the best way to manage events in WordPress!

> We know you have your choice of calendar & events plugins. We appreciate you giving this one a try, and we hope you really love it!

= Details =

* Seamlessly integrates into WordPress's dashboard interface
* Month, week, and day list-table views
* Organize events by type, category, & tag
* Easily & logically paginate through date ranges
* Safe, secure, & efficient
* Full of developer hooks for integration with other plugins & themes
* Integrates with User Groups, Alerts, Activity, and Profiles

= Works great with =

* [WP Chosen](https://wordpress.org/plugins/wp-chosen/ "Make long, unwieldy select boxes much more user-friendly.")
* [WP Pretty Filters](https://wordpress.org/plugins/wp-pretty-filters/ "Makes post filters better match what's already in Media & Attachments.")
* [WP Media Categories](https://wordpress.org/plugins/wp-media-categories/ "Add categories to media & attachments.")
* [WP Term Order](https://wordpress.org/plugins/wp-term-order/ "Sort taxonomy terms, your way.")
* [WP Term Authors](https://wordpress.org/plugins/wp-term-authors/ "Authors for categories, tags, and other taxonomy terms.")
* [WP Term Colors](https://wordpress.org/plugins/wp-term-colors/ "Pretty colors for categories, tags, and other taxonomy terms.")
* [WP Term Families](https://wordpress.org/plugins/wp-term-families/ "Families of taxonomies for taxonomy terms.")
* [WP Term Icons](https://wordpress.org/plugins/wp-term-icons/ "Pretty icons for categories, tags, and other taxonomy terms.")
* [WP Term Images](https://wordpress.org/plugins/wp-term-images/ "Pretty images for categories, tags, and other taxonomy terms.")
* [WP Term Visibility](https://wordpress.org/plugins/wp-term-visibility/ "Visibilities for categories, tags, and other taxonomy terms.")
* [WP User Activity](https://wordpress.org/plugins/wp-user-activity/ "The best way to log activity in WordPress.")
* [WP User Alerts](https://wordpress.org/plugins/wp-user-alerts/ "Alert registered users when new content is published.")
* [WP User Avatars](https://wordpress.org/plugins/wp-user-avatars/ "Allow users to upload avatars or choose them from your media library.")
* [WP User Groups](https://wordpress.org/plugins/wp-user-groups/ "Group users together with taxonomies & terms.")
* [WP User Parents](https://wordpress.org/plugins/wp-user-parents/ "Allow parent users to manage their direct decendants.")
* [WP User Profiles](https://wordpress.org/plugins/wp-user-profiles/ "A sophisticated way to edit users in WordPress.")

== Screenshots ==

1. Month View
2. Week View
3. Metabox
4. Date Picker

== Installation ==

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.

== Frequently Asked Questions ==

= Does this create new database tables? =

No. It uses WordPress's custom post-type, custom taxonomy, and metadata APIs.

= Does this modify existing database tables? =

No. All of WordPress's core database tables remain untouched.

= Where can I get support? =

* Basic: https://wordpress.org/support/plugin/wp-event-calendar/
* Priority: https://chat.flox.io/support/channels/wp-event-calendar/

= Where can I find documentation? =

http://github.com/stuttter/wp-event-calendar/

== Changelog ==

= [1.1.0] - 2016-12-09 =
* Improved editor metabox experience
* Fix bug caused when relocating the "Details" metabox

= [1.0.0] - 2016-09-07 =
* Improved support for mu-plugins location
* Improved performance of all ranged calendar queries
* Updated plugin file organization structure

= [0.4.0] - 2016-05-12 =
* More improvements to capability mappings
* Support for additional post types
* Helper functions for iCal integration
* Query functions

= [0.3.1] - 2016-05-03 =
* Fix capability regression in 0.3.1
* Add contextual help to Calendar page

= [0.3.0] - 2016-03-30 =
* Improve capability mapping
* Improve TinyMCE compatibility
* Update calendar styling

= [0.2.4] - 2016-02-05 =
* Dropdown select elements for hours & minutes

= [0.2.3] - 2016-02-01 =
* Rename metabox titles
* Make "Location" optional
* Add revision support to events
* General code & styling clean-up

= [0.2.2] - 2016-01-10 =
* Improve support for all-day & multi-day events
* Improve month, week, and day views
* Improve contrast of current & active items

= [0.2.1] - 2015-12-30 =
* Improve taxonomy & status filtering

= [0.2.0] - 2015-11-30 =
* Add "Day" view
* Improve "Month" view

= [0.1.10] - 2015-11-30 =
* Filter events by registered taxonomy

= [0.1.9] - 2015-11-16 =
* Move events to "Passed" status twice daily

= [0.1.8] - 2015-9-10 =
* Added week view
* Added persistent pagination for mode, status, & search
* Added styling for a few event types
* Added help text
* Added legend in help text area

= [0.1.7] - 2015-9-8 =
* Updated date-picker styling

= [0.1.6] - 2015-9-7 =
* Sort by start, end, and repetition

= [0.1.5] - 2015-9-6 =
* All-day events

= [0.1.4] - 2015-9-5 =
* More flexible mapped capabilities

= [0.1.3] - 2015-9-4 =
* Revert role & capability changes

= [0.1.2] - 2015-9-3 =
* Remove dependency
* Start, end, repeat, & expiring events
* Date picker
* Cleanup

= [0.1.1] - 2015-9-2 =
* Show pointers on event clicks
* Add flexible methods for pointers output
* Add actions & filters
* Rename main class to be for month-only view
* Use new icon for private posts

= [0.1.0] - 2015-9-1 =
* Initial release

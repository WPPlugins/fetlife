=== Plugin Name ===
Contributors: meitar
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TJLPJYXHSRBEE&lc=US&item_name=WP-FetLife&item_number=WP-FetLife&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: FetLife, widgets
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 0.1.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily show off your FetLife activity on your WordPress blog using widgets, shortcodes, and more.

== Description ==

Easily display FetLife events, groups, and more on your blog by adding widgets to your theme and using shortcodes in your posts, or pages. This plugin is very lightweight. Just log in to FetLife as you normally do from the plugin settings screen. Once that's done, a built-in plugin cache automatically speeds up all future requests to FetLife.com.

= Widgets and shortcodes =

This single plugin provides a bunch of different add-ons for your WordPress blog.

**Widgets**

WP-FetLife adds numerous widgets that work well with any theme. See the [Screenshots](https://wordpress.org/plugins/fetlife/screenshots/) section for screenshots of some of these examples. Some of the Widgets this plugin provides are:

* FetLife Profile - Events
    * This widget shows the name of and links to any upcoming FetLife events on a FetLife profile page. Choose from events that the given user profile is "organizing," "going to" or "maybe going to," along with how many events to show, and more.
* FetLife Profile - Groups
    * This widget shows the name of and links to any FetLife groups a user belongs. Similarly, you can customize the widget to show only those groups the FetLife user is "leading," how many groups to show in the widget, and more.
* FetLife Events - Participants
    * This widget displays the RSVP list for a FetLife event. Choose to show "going" or "maybe going" RSVPs, or both!

*Every widget provided by this plugin is also available as a shortcode.* Use the `[wp_fetlife_widget WIDGET_NAME]` shortcode to call a particular widget. You can even set its options right from the shortcode. For example, this shortcode displays the events that FetLife user number `1` is `organizing`:

    [wp_fetlife_widget profile_events fl_id="1" show_events_organizing]

(The FetLife user number for a given profile is the number at the end of the web address for that user's profile page. So, for instance, `https://fetlife.com/users/1` is the profile page for user number 1.)

To display *all* upcoming events that FetLife user `1` is participating in, use:

    [wp_fetlife_widget profile_events fl_id="1" show_events_organizing show_events_going show_events_maybe_going]

Similarly, the following shortcode shows all of user `1`'s groups:

    [wp_fetlife_widget profile_groups fl_id="1"]

Or, show only the groups for which this user is the leader:

    [wp_fetlife_widget profile_groups fl_id="1" show_only_groups_lead]

Some widgets (like these examples) require the `fl_id` parameter. Other parameters are optional and can be included as additional attribute values or omited to use the defaults. For example, to add a headline to the output of any of the above widgets, use the `title` attribute:

    [wp_fetlife_widget profile_groups title="Join one of my FetLife groups!" show_only_groups_lead]

For a complete reference of feature additions, shortcode syntax, and so on, see [Other notes](https://wordpress.org/plugins/fetlife/).

= Related tools =

To export your FetLife content (Writings, etc.) and import them to your website as native WordPress content (blog Posts, etc.), use this plugin's sister plugin, [WP FetLife Importer](https://wordpress.org/plugins/wp-fetlife-importer/).

Have your own ideas widgets or other features? Share it with the developer and other users in the [support forum](https://wordpress.org/support/plugin/fetlife). :)

== Installation ==

1. Upload the unzipped `wp-fetlife` directory to your `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure the plugin's connection to FetLife in its settings screen. See [Screenshots](https://wordpress.org/plugins/fetlife/screenshots/) for a visual walkthrough of this process.
1. Add widgets, shortcodes, and more to your blog!

= Technical notes =

This plugin ships with a copy of [libFetLife](https://github.com/meitar/libFetLife), a PHP class implementing a simple API to interact with FetLife.com.

== Frequently Asked Questions ==

= The plugin says "mkdir() permission denied"? =
Make sure the plugin's `lib/FetLife` folder is read and writeable by your webserver. (This is the default on most systems.)

== Screenshots ==

1. When you first install WP-FetLife, you'll need to connect it to your FetLife account before you can add its widgets and other features to your blog. This screenshot shows how its options screen first appears after you activate the plugin.

2. Once you enter and save your connection information, the option screens provides a button allowing you to test your connection and shows you any additional options available. You can return to this screen at any time to modify your connection settings or test to see if your current connection settings are operational. This screenshot shows the result of a successful connection test using the `auto` proxy configuration.

3. The included "FetLife Profile" widgets make it easy to show off parts of your FetLife profile on your website. This screenshot shows the "FetLife Profile - Events" widget to display a list of events I'll be going to. Other FetLife Profile widgets let you display a list of your FetLife groups, recent activity, and more. All the widgets let you customize how many items to show, and so on.

4. In addition to a cache control in every widget, shortcode, etc., the plugin also provides some tools for managing and troubleshooting any problems that might occur. This screenshot shows the "Clear plugin cache" screen, available in the Tools submenu. Use this tool to clear the cache of all plugin widgets, shortcodes, etc. in one click.

== Change log ==

= Version 0.1.1 =

* Feature: Make every widget available as a shortcode, too. For instance, to use the "FetLife Profile - Groups" widget as a shortcode, write `[wp_fetlife_widget profile_groups fl_id="1"]` in any post or page. See [Other notes](https://wordpress.org/plugins/fetlife/other_notes/) for more details about using widgets as shortcodes.

= Version 0.1 =

* Initial release.

== Other notes ==

If you like this plugin, **please consider [making a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TJLPJYXHSRBEE&lc=US&item_name=WP-FetLife&item_number=WP-FetLife&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
) for your use of the plugin**, [purchasing one of Meitar's web development books](http://www.amazon.com/gp/redirect.html?ie=UTF8&location=http%3A%2F%2Fwww.amazon.com%2Fs%3Fie%3DUTF8%26redirect%3Dtrue%26sort%3Drelevancerank%26search-type%3Dss%26index%3Dbooks%26ref%3Dntt%255Fathr%255Fdp%255Fsr%255F2%26field-author%3DMeitar%2520Moscovitz&tag=maymaydotnet-20&linkCode=ur2&camp=1789&creative=390957) or, better yet, contributing directly to [Meitar's Cyberbusking fund](http://Cyberbusking.org/). (Publishing royalties ain't exactly the lucrative income it used to be, y'know?) Your support is appreciated!

= Widgets =

The widgets this plugin provides are:

**Profile widgets**

The following widgets display information from a given user's FetLife profile page.

* **FetLife Profile - Events** - Displays upcoming FetLife events shown on a FetLife profile page in a list. As a shortcode:
    * `[wp_fetlife_widget profile_events fl_id=""]`
    * `show_events_organizing` - Whether to include events the user is organizing in the output. (Default: `false`.)
    * `show_events_going` - Whether to include events the user RSVP'ed "I'm going" in the output. (Default: `false`.)
    * `show_events_maybe_going` - Whether to include events the user RSVP'ed "I'm maybe going" in the output. (Default: `false`.)
* **FetLife Profile - Groups** - Displays the FetLife groups a user belongs to in a list. As a shortcode:
    * `[wp_fetlife_widget profile_groups fl_id=""]`
    * `show_only_groups_lead` - Restrict the output to only those groups being lead by the given user.

**Event widgets**

* **FetLife Event - Participants** - Displays the RSVPs for a FetLife event in a list.
    * `[wp_fetlife_widget event_participants fl_id=""]`
    * `show_organizer` - Whether to include the event's organizer in the output.
    * `show_going` - Whether to include the users who have RSVP'ed "going" in the output.
    * `show_maybe_going` - Whether to include the users who have RSVP'ed "maybe_going" in the output.

Each widget can accept the following additional optional parameters:

* `clear_cache` - Disables the built-in plugin cache for this shortcode. Using this attribute is *not recommended*, except for debugging purposes, as it will significantly slow down your website. (Default: `false`.)
* `length` - How many items to output. For example, to list the first ten groups for user `1`, use: `[wp_fetlife_widget profile_groups fl_id="1" length="10"]`. (Default: `5`.)
* `title` - A headline to include before other output. (Default: none.)

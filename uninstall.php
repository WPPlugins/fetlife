<?php
/**
 * WP-FetLife
 *
 * @package plugin
 */

// Don't execute any uninstall code unless WordPress core requests it.
if (!defined('WP_UNINSTALL_PLUGIN')) { exit(); }

$wp_fetlife = new WP_FetLife_Plugin();

// Delete options.
delete_option($wp_fetlife->getPrefix() . 'settings');

// Delete widget settings.
delete_option('widget_wp_fl_wdgt_events');
delete_option('widget_wp_fl_wdgt_groups');
delete_option('widget_wp_fl_wdgt_participants');

// Delete caches.
$wp_fetlife->clearCachedTransients();

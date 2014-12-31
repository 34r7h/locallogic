<?php
/*
Plugin Name: GeoDirectory Custom Google Maps
Plugin URI: http://wpgeodirectory.com
Description: This plugin gives an advanced style system for Google Maps.
Version: 1.0.0
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/

/* Define Constants */
define('GEODIR_CUSTOMGMAPS_VERSION', '1.0.0');

define('GEODIR_CUSTOMGMAPS_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)));
define('GEODIR_CUSTOMGMAPS_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));

global $wpdb, $plugin_prefix, $geodir_addon_list;
if (is_admin()) {
	require_once('gd_update.php'); // require update script
}

// GEODIRECTORY CORE ALIVE CHECK START
if (is_admin()) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if (!is_plugin_active('geodirectory/geodirectory.php')) {
		return;
	}
}
// GEODIRECTORY CORE ALIVE CHECK END

$geodir_addon_list['geodir_custom_google_maps_manager'] = 'yes' ;

if (!isset($plugin_prefix)) {
	$plugin_prefix = $wpdb->prefix.'geodir_';
}

if (!defined('GEODIRCUSTOMGMAPS_TEXTDOMAIN')) {
	define('GEODIRCUSTOMGMAPS_TEXTDOMAIN', 'geodir_customgmaps');
}
/* ---- Table Names ---- */
if (!defined('GEODIR_CUSTOM_GMAPS_TABLE')) {
	define('GEODIR_CUSTOM_GMAPS_TABLE', $plugin_prefix . 'custom_gmaps');
}	

$locale = apply_filters('plugin_locale', get_locale(), GEODIRCUSTOMGMAPS_TEXTDOMAIN);
load_textdomain(GEODIRCUSTOMGMAPS_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRCUSTOMGMAPS_TEXTDOMAIN.'/'.GEODIRCUSTOMGMAPS_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRCUSTOMGMAPS_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-customgmaps-languages');

include_once('language.php'); // Define language constants

/**
 * Include core files
 **/
require_once('geodir_custom_gmaps_functions.php'); 
require_once('geodir_custom_gmaps_template_functions.php'); 
require_once('geodir_custom_gmaps_hooks_actions.php');

/**
 * Admin init + activation hooks
 **/
if (is_admin()) {
	register_activation_hook(__FILE__ , 'geodir_custom_gmaps_activation');
	register_deactivation_hook(__FILE__ , 'geodir_custom_gmaps_deactivation');
	
	register_uninstall_hook(__FILE__, 'geodir_custom_gmaps_uninstall');
}

add_action('activated_plugin','geodir_custom_google_maps_plugin_activated') ;
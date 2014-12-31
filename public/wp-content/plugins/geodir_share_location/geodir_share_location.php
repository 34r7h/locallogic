<?php
/*
Plugin Name: GeoDirectory Share Location
Plugin URI: http://wpgeodirectory.com	
Description: This plugin gives an option to share location with the system.
Version: 1.0.0
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/


/* Define Constants */
define("GDSHARELOCATION_VERSION", "1.0.0");

define( 'GDSHARELOCATION_PLUGINDIR_PATH', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) );
define( 'GDSHARELOCATION_PLUGINDIR_URL', WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__) )  );

global $plugin,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}

$geodir_addon_list['geodir_share_location'] = 'yes' ;

$plugin = plugin_basename( __FILE__ );

$plugin_prefix = 'geodir_';

if (!defined('POST_LOCATION_TABLE')) define('POST_LOCATION_TABLE', $plugin_prefix . 'post_locations' );
if (!defined('POST_DETAIL_TABLE')) define('POST_DETAIL_TABLE', $plugin_prefix . 'post_detail' );


if (!defined('GEODIRSHARELOCATION_TEXTDOMAIN')) define('GEODIRSHARELOCATION_TEXTDOMAIN','geodir_share_location');
load_plugin_textdomain(GEODIRSHARELOCATION_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-share-location-languages');

require_once('geodir_share_location_hooks.php');
require_once('geodir_share_location_functions.php');

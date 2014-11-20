<?php
/*
Plugin Name: Users Ultra Pro
Plugin URI: http://usersultra.com
Description: This is a powerful user profiles plugin for WordPress. This versatile plugin allows you to create user communities in few minutes. It comes with tons of useful shortcodes which give you the capability to customize any WordPress Theme.
Version: 1.1.29
Author: Users Ultra Pro
Author URI: http://usersultra.com/users-pro.html
*/
define('xoousers_url',plugin_dir_url(__FILE__ ));
define('xoousers_path',plugin_dir_path(__FILE__ ));
define('xoousers_template','basic');

// Get plugin version from header
function xoousersultra_get_plugin_version()
{
    $default_headers = array( 'Version' => 'Version' );
    $plugin_data = get_file_data( __FILE__, $default_headers, 'plugin' );
    return $plugin_data['Version'];
}


$plugin = plugin_basename(__FILE__);

// Auto updates
if (is_admin()){
	
 add_action('init', '__wuultra__wpuultra_pluign_au');
 
}

function __wuultra__wpuultra_pluign_au()
{
	require_once ('wp_autoupdate.php');
	
	$va = get_option('uultra_c_key');
	$wptuts_plugin_current_version = '1.1.29';
	$wptuts_plugin_remote_path = 'http://www.usersultra.com/upgrades/uultra-plugin-update.php?serial_n='.$va;
	
	$wptuts_plugin_slug = plugin_basename(__FILE__);
	new __wuultra__wpuultrapro_plugin_auto_update ($wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug);
}

/* Loading Function */
require_once (xoousers_path . 'functions/functions.php');

/* Init */
require_once (xoousers_path . 'init/init.php');

/* Master Class  */
require_once (xoousers_path . 'xooclasses/xoo.userultra.class.php');

// Helper to activate a plugin on another site without causing a fatal error by

register_activation_hook( __FILE__, 'uultra_activation');
 
function  uultra_activation( $network_wide ) 
{
	$plugin = "users-ultra-pro/xoousers.php";	
	
	if ( is_multisite() && $network_wide ) // See if being activated on the entire network or one blog
	{ 
		activate_plugin($plugin_path,NULL,true);
			
		
	} else { // Running on a single blog	
	   	
			
		activate_plugin($plugin_path,NULL,false);
		
		
	}
}

$xoouserultra = new XooUserUltra();
$xoouserultra->plugin_init();

/* load addons */
require_once xoousers_path . 'addons/photocategories/index.php';
//require_once xoousers_path . 'addons/messaging/index.php';
require_once xoousers_path . 'addons/badges/index.php';
require_once xoousers_path . 'addons/defender/index.php';
require_once xoousers_path . 'addons/maintenance/index.php';
//require_once xoousers_path . 'addons/groups/index.php';
require_once xoousers_path . 'addons/forms/index.php';
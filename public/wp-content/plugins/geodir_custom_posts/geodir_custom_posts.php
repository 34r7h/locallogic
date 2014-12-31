<?php
/*
Plugin Name: GeoDirectory Custom Post Types
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Custom Post Types plugin.
Version: 1.1.1
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/


/**
 * Globals
 **/
 
define("GEODIR_CP_VERSION", "1.1.1");

global $wpdb,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}

///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END


$geodir_addon_list['geodir_custom_posts_manager'] = 'yes';

if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';


/**
 * Constants
 **/
if(!defined('WP_POST_REVISIONS'))
	define( 'WP_POST_REVISIONS', 0); // To stop post revisions for wordpress




/**
 * Localisation
 **/
if (!defined('GEODIR_CP_TEXTDOMAIN')) define('GEODIR_CP_TEXTDOMAIN', 'geodir_custom_posts');
$locale = apply_filters('plugin_locale', get_locale(), GEODIR_CP_TEXTDOMAIN);
load_textdomain(GEODIR_CP_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIR_CP_TEXTDOMAIN.'/'.GEODIR_CP_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIR_CP_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/geodir-cp-languages');

require_once( 'language.php' ); // Define language constants




/**
 * Include core files
 **/
require_once('geodir_cp_functions.php'); 
require_once('geodir_cp_template_tags.php'); 
require_once('geodir_cp_hooks_actions.php');
if ( is_admin() ){
require_once('gd_upgrade.php');	
}
if ( is_admin() ) :
	register_activation_hook( __FILE__ , 'geodir_custom_post_type_activation' );
	/*register_deactivation_hook( __FILE__ , 'geodir_custom_post_type_deactivation' );*/
	register_uninstall_hook(__FILE__,'geodir_custom_post_type_uninstall');
	
endif;


add_action('activated_plugin','geodir_custom_post_type_plugin_activated') ;
function geodir_custom_post_type_plugin_activated($plugin)
{
	if (!get_option('geodir_installed')) 
	{
		$file = plugin_basename(__FILE__);
		if($file == $plugin) 
		{
			$all_active_plugins = get_option( 'active_plugins', array() );
			if(!empty($all_active_plugins) && is_array($all_active_plugins))
			{
				foreach($all_active_plugins as $key => $plugin)
				{
					if($plugin ==$file)
						unset($all_active_plugins[$key]) ;
				}
			}
			update_option('active_plugins',$all_active_plugins);
			
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIR_CP_TEXTDOMAIN));
	}
	
}
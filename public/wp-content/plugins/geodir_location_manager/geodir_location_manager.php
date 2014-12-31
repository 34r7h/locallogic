<?php
/*
Plugin Name: GeoDirectory Location Manager
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Location Manager plugin.
Version: 1.2.2
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/

define("GEODIRLOCATION_VERSION", "1.2.2");

global $wpdb, $plugin_prefix, $is_custom_loop,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END

$geodir_addon_list['geodir_location_manager'] = 'yes' ;

if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';

$path_location_url = plugins_url('',__FILE__);


if (!defined('GEODIR_LOCATION_MANAGER_PATH')) define('GEODIR_LOCATION_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
if (!defined('POST_LOCATION_TABLE')) define('POST_LOCATION_TABLE', $plugin_prefix . 'post_locations' );
if (!defined('POST_NEIGHBOURHOOD_TABLE')) define('POST_NEIGHBOURHOOD_TABLE', $plugin_prefix . 'post_neighbourhood' );
if (!defined('COUNTRIES_TABLE')) define('COUNTRIES_TABLE', $plugin_prefix . 'countries' );
if (!defined('LOCATION_SEO_TABLE')) define('LOCATION_SEO_TABLE', $plugin_prefix . 'location_seo' );	

/**
 * Localisation
 **/

if (!defined('GEODIRLOCATION_TEXTDOMAIN')) define('GEODIRLOCATION_TEXTDOMAIN', 'geodirlocation');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRLOCATION_TEXTDOMAIN);
load_textdomain(GEODIRLOCATION_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRLOCATION_TEXTDOMAIN.'/'.GEODIRLOCATION_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRLOCATION_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-location-languages');

require_once( 'language.php' ); /* Define language constants */



/**
 * activation hooks
 **/
if ( is_admin() ) :
	register_activation_hook( __FILE__, 'geodir_location_activation' );
	register_deactivation_hook( __FILE__, 'geodir_location_deactivation' );
	register_uninstall_hook(__FILE__,'geodir_location_uninstall'); 
	
endif;

include_once('geodir_location_hooks_actions.php');
include_once('geodir_location_functions.php');
include_once('geodir_location_template_tags.php');
include_once('geodir_location_widgets.php');
include_once('geodir_location_shortcodes.php');
if ( is_admin() ){
require_once('gd_upgrade.php');	
}


add_action('activated_plugin','geodir_location_plugin_activated') ;
function geodir_location_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRLOCATION_TEXTDOMAIN));
	}
	
}


?>
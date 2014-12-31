<?php 
/*
Plugin Name: GeoDirectory Events
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Events plugin .
Version: 1.0.9
Author: GeoDirectory
Author URI: http://wpgeodirectory.com

*/

define("GDEVENTS_VERSION", "1.0.9");
if (!session_id()) session_start();

/**
 * Globals
 **/ 
global $wpdb,$plugin_prefix,$geodir_addon_list, $geodir_date_time_format, $geodir_date_format ,$geodir_time_format;

if(is_admin()){
	require_once('gd_update.php'); // require update script
}

$geodir_date_time_format = get_option('date_format'). ' ' .	get_option('time_format');
$geodir_date_format = get_option('date_format') ;
$geodir_time_format = get_option('time_format') ;
$geodir_addon_list['geodir_event_manager'] = 'yes' ;



if(!isset($plugin_prefix))
	$plugin_prefix = 'geodir_';



/* ---- Table Names ---- */
if (!defined('EVENT_DETAIL_TABLE')) define('EVENT_DETAIL_TABLE', $plugin_prefix . 'gd_event_detail' );	
if (!defined('EVENT_SCHEDULE')) define('EVENT_SCHEDULE', $plugin_prefix . 'event_schedule' );	

/**
 * Localisation
 **/
if (!defined('GEODIREVENTS_TEXTDOMAIN')) define('GEODIREVENTS_TEXTDOMAIN', 'geodirevents');	
$locale = apply_filters('plugin_locale', get_locale(), GEODIREVENTS_TEXTDOMAIN);
load_textdomain(GEODIREVENTS_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIREVENTS_TEXTDOMAIN.'/'.GEODIREVENTS_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIREVENTS_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/gdevents-languages');

require_once( 'language.php' ); // Define language constants




include_once( 'gdevents_template_functions.php' );
include_once( 'gdevents_functions.php' ); 
include_once( 'gdevents_hooks_actions.php' );
include_once( 'gdevents_widget.php' );

if ( is_admin() ) :
 
	require_once( 'gdevents-admin/admin_functions.php' );
	require_once( 'gdevents-admin/admin_hooks_actions.php' );
	require_once( 'gdevents-admin/admin_install.php' );
 	
	register_activation_hook( __FILE__, 'geodir_events_activation' );
	register_deactivation_hook( __FILE__, 'geodir_event_deactivation' );
	register_uninstall_hook(__FILE__,'geodir_event_uninstall');  
	
endif;


add_action('activated_plugin','geodir_event_plugin_activated') ;
function geodir_event_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIREVENTS_TEXTDOMAIN));
	}
	
}


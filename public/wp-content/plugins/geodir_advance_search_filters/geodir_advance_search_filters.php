<?php
/*
Plugin Name: GeoDirectory Advance Search Filters
Plugin URI: http://wpgeodirectory.com/
Description: GeoDirectory Advance Search Filters.
Version: 1.0.6
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/ 

global $wpdb, $plugin_prefix;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
$plugin_prefix = 'geodir_';

$path_location_url = plugins_url('',__FILE__);

if (!defined('GEODIR_ADVANCE_SEARCH_TABLE')) define('GEODIR_ADVANCE_SEARCH_TABLE', $plugin_prefix . 'custom_advance_search_fields' );


if (!defined('GEODIRADVANCESEARCH_TEXTDOMAIN')) define('GEODIRADVANCESEARCH_TEXTDOMAIN', 'geodiradvancesearch');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRADVANCESEARCH_TEXTDOMAIN);
load_textdomain(GEODIRADVANCESEARCH_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRADVANCESEARCH_TEXTDOMAIN.'/'.GEODIRADVANCESEARCH_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRADVANCESEARCH_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-advance-search-languages');	
require_once( 'language.php' ); // Define language constants

 
/**
 * Admin init + activation hooks
 **/


include_once('geodirectory_advance_search_function.php'); 
include_once('geodirectory_advance_search_hooks_actions.php');

if ( is_admin() ) :

	register_activation_hook( __FILE__ , 'geodir_advance_search_filters_activation' );
	
	register_uninstall_hook(__FILE__,'geodir_advance_search_filters_uninstall');
	
endif;


add_action('activated_plugin','geodir_advance_search_filters_plugin_activated') ;
function geodir_advance_search_filters_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRADVANCESEARCH_TEXTDOMAIN));
	}
	
}

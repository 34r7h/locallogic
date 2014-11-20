<?php
/*
Plugin Name: GeoDirectory Claim Manager
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Cliam Manager plugin.
Version: 1.0.6
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/

global $wpdb,$plugin_prefix,$site_login_url,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
$geodir_addon_list['geodir_claim_manager'] = 'yes' ;

if(!isset($plugin_prefix))
	$plugin_prefix = 'geodir_';


$site_login_url = get_option('siteurl').'?geodir_signup=true';

$path_url = plugins_url('',__FILE__);


/**
 * Localisation
 **/
 
if (!defined('GEODIR_CLAIM_TABLE')) define('GEODIR_CLAIM_TABLE', $plugin_prefix . 'claim' );


if (!defined('GEODIRCLAIM_TEXTDOMAIN')) define('GEODIRCLAIM_TEXTDOMAIN', 'geodirclaim');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRCLAIM_TEXTDOMAIN);
load_textdomain(GEODIRCLAIM_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRCLAIM_TEXTDOMAIN.'/'.GEODIRCLAIM_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRCLAIM_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-claim-languages');
require_once( 'language.php' ); // Define language constants





/**
 * Admin init + activation hooks
 **/
 
if ( is_admin() ) :
	
	register_activation_hook( __FILE__ , 'geodir_claim_listing_activation' );
	/* register_deactivation_hook( __FILE__ , 'geodir_claim_listing_deactivation' ); */
	register_uninstall_hook(__FILE__,'geodir_claim_listing_uninstall');

endif;



include_once('geodir_claim_hooks_actions.php');
include_once('geodir_claim_template_tags.php');
include_once('geodir_claim_template_functions.php');
include_once('geodir_claim_functions.php');

add_action('activated_plugin','geodir_claim_listing_plugin_activated') ;
function geodir_claim_listing_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRCLAIM_TEXTDOMAIN));
	}
	
}

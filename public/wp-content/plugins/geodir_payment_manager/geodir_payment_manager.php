<?php
/*
Plugin Name: GeoDirectory Payment Manager
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Payment Manager plugin.
Version: 1.1.0
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/

define("GEODIRPAYMENT_VERSION", "1.1.0");
global $wpdb,$plugin_prefix,$is_custom_loop,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END
$geodir_addon_list['geodir_payment_manager'] = 'yes' ;

if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';
	
$geodir_get_package_info_cache = array();// This will store the cached package info per package for each page load so not to run for each listing

if (!defined('GEODIR_PAYMENT_MANAGER_PATH')) define('GEODIR_PAYMENT_MANAGER_PATH', plugin_dir_path( __FILE__ ) );

/* ---- Table Names ---- */
if (!defined('GEODIR_PRICE_TABLE')) define('GEODIR_PRICE_TABLE', $plugin_prefix . 'price' );	
if (!defined('INVOICE_TABLE')) define('INVOICE_TABLE', $plugin_prefix . 'invoice' );	
if (!defined('COUPON_TABLE')) define('COUPON_TABLE', $plugin_prefix . 'coupons' );	

if (!defined('GEODIRPAYMENT_TEXTDOMAIN')) define('GEODIRPAYMENT_TEXTDOMAIN','geodir_payments');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRPAYMENT_TEXTDOMAIN);
load_textdomain(GEODIRPAYMENT_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRPAYMENT_TEXTDOMAIN.'/'.GEODIRPAYMENT_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRPAYMENT_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-payment-languages');

// add field for sort order
if(!$wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_PRICE_TABLE." WHERE field = 'display_order'")){
	$wpdb->query("ALTER TABLE `".GEODIR_PRICE_TABLE."` ADD `display_order` INT( 11 ) NOT NULL DEFAULT '0' AFTER `sub_active`");
}

include_once('language.php');



include_once( 'geodir_payment_functions.php' );
include_once( 'geodir_payment_template_functions.php' );
include_once( 'geodir_payment_actions.php' );  
 
if ( is_admin() ) :

	register_activation_hook( __FILE__, 'geodir_payment_activation' ); 
	
	register_deactivation_hook( __FILE__, 'geodir_payment_deactivation' );
	 
	register_uninstall_hook(__FILE__,'geodir_payment_uninstall');
	
endif;
if ( is_admin() ){
require_once('gd_upgrade.php');	
}

add_action('activated_plugin','geodir_payment_plugin_activated') ;
function geodir_payment_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRPAYMENT_TEXTDOMAIN));
	}
}
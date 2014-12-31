<?php 
/*
Plugin Name: GeoDirectory Review Rating Manager
Plugin URI: http://wpgeodirectory.com	
Description: This plugin gives a advanced comment system with multi rating system on post comments.
Version: 1.1.0
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/

define("GEODIRREVIEWRATING_VERSION", "1.1.0");
global $plugin,$plugin_prefix,$vailed_file_type;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END

if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';

$plugin = plugin_basename( __FILE__ );
$vailed_file_type = array('image/png','image/gif','image/jpg','image/jpeg');	

/* Define Constants */ 
 
define( 'GEODIR_REVIEWRATING_PLUGINDIR_PATH', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) );
define( 'GEODIR_REVIEWRATING_PLUGINDIR_URL', WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__) )  );
 
/* ---- Create Tables Cinstants ---- */
if (!defined('GEODIR_REVIEWRATING_STYLE_TABLE')) define('GEODIR_REVIEWRATING_STYLE_TABLE', $plugin_prefix . 'rating_style' );	
if (!defined('GEODIR_REVIEWRATING_CATEGORY_TABLE')) define('GEODIR_REVIEWRATING_CATEGORY_TABLE', $plugin_prefix.'rating_category');	
if (!defined('GEODIR_REVIEWRATING_POSTREVIEW_TABLE')) define('GEODIR_REVIEWRATING_POSTREVIEW_TABLE', $plugin_prefix . 'post_review' );	
if (!defined('GEODIR_UNASSIGN_COMMENT_IMG_TABLE')) define('GEODIR_UNASSIGN_COMMENT_IMG_TABLE', $plugin_prefix . 'unassign_comment_images' );
if (!defined('GEODIR_COMMENTS_REVIEWS_TABLE')) define('GEODIR_COMMENTS_REVIEWS_TABLE', $plugin_prefix . 'comments_reviews' );	



if (!defined('GEODIRREVIEWRATING_TEXTDOMAIN')) define('GEODIRREVIEWRATING_TEXTDOMAIN','geodir_reviewratings');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRREVIEWRATING_TEXTDOMAIN);
load_textdomain(GEODIRREVIEWRATING_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRREVIEWRATING_TEXTDOMAIN.'/'.GEODIRREVIEWRATING_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRREVIEWRATING_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-reviewrating-languages');

include_once('language.php');



include_once('geodir_reviewrating_template_tags.php');
include_once( 'geodir_reviewrating_functions.php' );
include_once( 'geodir_reviewrating_template_functions.php' );
include_once( 'geodir_reviewrating_actions.php' );  

/**
 * Admin init + activation hooks
 **/
if ( is_admin() ) :
	
	register_activation_hook( __FILE__ , 'geodir_reviewrating_activation' );
	
	register_uninstall_hook( __FILE__, 'geodir_reviewrating_uninstall' );
	
endif;
if ( is_admin() ){
require_once('gd_upgrade.php');	
}

add_action('activated_plugin','geodir_reviewrating_plugin_activated') ;
function geodir_reviewrating_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRREVIEWRATING_TEXTDOMAIN));
	}
	
}
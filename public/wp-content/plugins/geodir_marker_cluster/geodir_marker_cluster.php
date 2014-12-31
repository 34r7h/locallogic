<?php
/*
Plugin Name: GeoDirectory Marker Cluster
Plugin URI: http://wpgeodirectory.com	
Description: This plugin gives an advanced marker cluster system for Google Maps.
Version: 1.1.0
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/


/* Define Constants */
define("GDCLUSTER_VERSION", "1.1.0");

define( 'GDCLUSTER_PLUGINDIR_PATH', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) );
define( 'GDCLUSTER_PLUGINDIR_URL', WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__) )  );

global $plugin,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END
$geodir_addon_list['geodir_marker_cluster'] = 'yes' ;

$plugin = plugin_basename( __FILE__ );
if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';


if (!defined('GEODIRMARKERCLUSTER_TEXTDOMAIN')) define('GEODIRMARKERCLUSTER_TEXTDOMAIN','geodir_markercluster');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRMARKERCLUSTER_TEXTDOMAIN);
load_textdomain(GEODIRMARKERCLUSTER_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRMARKERCLUSTER_TEXTDOMAIN.'/'.GEODIRMARKERCLUSTER_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRMARKERCLUSTER_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-markercluster-languages');

include_once('language.php');


/**
 * Admin init + activation hooks
 **/
if ( is_admin() ) :

	register_activation_hook( __FILE__ , 'geodir_marker_cluster_activation' );
	
	register_deactivation_hook( __FILE__ , 'geodir_marker_cluster_deactivation' );
	
endif;


add_action('activated_plugin','geodir_marker_cluster_plugin_activated') ;
function geodir_marker_cluster_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRMARKERCLUSTER_TEXTDOMAIN));
	}
	
}


function geodir_marker_cluster_activation(){
	
	if (get_option('geodir_installed')) {
	
		 add_option('geodir_marker_cluster_activation_redirect_opt', 1);
	}

}


add_action('admin_init', 'geodir_marker_cluster_activation_redirect');

function geodir_marker_cluster_activation_redirect(){
	if (get_option('geodir_marker_cluster_activation_redirect_opt', false))
	{
		delete_option('geodir_marker_cluster_activation_redirect_opt');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=design_settings&active_tab=geodir_marker_cluster_settings')); 
	}
}


function geodir_marker_cluster_deactivation(){

	update_option( 'geodir_marker_cluster_on_maps', ' ');

}



/* Scripts loader */

add_action( 'wp_enqueue_scripts', 'gdcluster_templates_scripts',100);
if (!function_exists('gdcluster_templates_scripts')) {
function gdcluster_templates_scripts(){

	wp_enqueue_script( 'jquery' );
	
	wp_register_script( 'gdcluster-js', GDCLUSTER_PLUGINDIR_URL.'/js/marker_cluster.js',array('jquery') );
	wp_enqueue_script( 'gdcluster-js' );
	
	wp_register_script( 'gdcluster-script', GDCLUSTER_PLUGINDIR_URL.'/js/cluster_script.js',array('jquery','gdcluster-js'),'1',true );
	wp_enqueue_script( 'gdcluster-script' );
	
}}


///add marker cluster option///
add_filter('geodir_design_settings' , 'geodir_map_marker_cluster', 2, 10 ) ;
function geodir_map_marker_cluster($arr){

	$arr[] = array( 'name' => __( 'Marker  Cluster' , GEODIRMARKERCLUSTER_TEXTDOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'geodir_marker_cluster_settings ' );
	
	

	$arr[] = array( 	'name' => __( 'Enable Marker Cluster',  GEODIRMARKERCLUSTER_TEXTDOMAIN), 
				'type' => 'sectionstart',
				'desc' => '', 
				'id' => 'geodir_marker_cluster_settings' );
	
		$arr[] = array(  
				'name' => __( 'Show marker cluster on selected maps',  GEODIRMARKERCLUSTER_TEXTDOMAIN),
				'desc' 		=> '',
				'tip' 		=> '',
				'id' 		=> 'geodir_marker_cluster_on_maps',
				'css' 		=> 'min-width:300px;',
				'type' 		=> 'multiselect',
				'placeholder_text' => __( 'Select maps to cluster', GEODIRMARKERCLUSTER_TEXTDOMAIN ),
				'class'		=> 'chosen_select',
				'options' => array_unique( geodir_map_marker_cluster_choose_maps())
			   );
		
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_marker_cluster_settings');
	
	
	$arr[] = array( 	'name' => __( 'Marker Cluster Settings',  GEODIRMARKERCLUSTER_TEXTDOMAIN), 
				'type' => 'sectionstart',
				'desc' => '', 
				'id' => 'geodir_marker_cluster_settings_options' );
	
	$arr[] = array(  
			'name' => __( 'Grid Size', GEODIRECTORY_TEXTDOMAIN ),
			'desc' 		=> __( 'The grid size of a cluster in pixel. Each cluster will be a square. If you want the algorithm to run faster, you can set this value larger. The default value is 60', GEODIRECTORY_TEXTDOMAIN ),
			'id' 		=> 'geodir_marker_cluster_size',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '60',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( array( 
				'60' => __( 'Default (60)', GEODIRECTORY_TEXTDOMAIN ),
				'10' => __( '10', GEODIRECTORY_TEXTDOMAIN ),
				'20' => __( '20', GEODIRECTORY_TEXTDOMAIN ),
				'30' => __( '30', GEODIRECTORY_TEXTDOMAIN ),
				'40' => __( '40', GEODIRECTORY_TEXTDOMAIN ),
				'50' => __( '50', GEODIRECTORY_TEXTDOMAIN ),
				'70' => __( '70', GEODIRECTORY_TEXTDOMAIN ),
				'80' => __( '80', GEODIRECTORY_TEXTDOMAIN ),
				'90' => __( '90', GEODIRECTORY_TEXTDOMAIN ),
				'100' => __( '100', GEODIRECTORY_TEXTDOMAIN ),
				))
		);
	
	$arr[] = array(  
			'name' => __( 'Max Zoom', GEODIRECTORY_TEXTDOMAIN ),
			'desc' 		=> __( 'The max zoom level monitored by a marker cluster. When maxZoom is reached or exceeded all markers will be shown without cluster.', GEODIRECTORY_TEXTDOMAIN ),
			'id' 		=> 'geodir_marker_cluster_zoom',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '15',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( array( 
				'15' => __( 'Default (15)', GEODIRECTORY_TEXTDOMAIN ),
				'1' => __( '1', GEODIRECTORY_TEXTDOMAIN ),
				'2' => __( '2', GEODIRECTORY_TEXTDOMAIN ),
				'3' => __( '3', GEODIRECTORY_TEXTDOMAIN ),
				'4' => __( '4', GEODIRECTORY_TEXTDOMAIN ),
				'5' => __( '5', GEODIRECTORY_TEXTDOMAIN ),
				'6' => __( '6', GEODIRECTORY_TEXTDOMAIN ),
				'7' => __( '7', GEODIRECTORY_TEXTDOMAIN ),
				'8' => __( '8', GEODIRECTORY_TEXTDOMAIN ),
				'9' => __( '9', GEODIRECTORY_TEXTDOMAIN ),
				'10' => __( '10', GEODIRECTORY_TEXTDOMAIN ),
				'11' => __( '11', GEODIRECTORY_TEXTDOMAIN ),
				'12' => __( '12', GEODIRECTORY_TEXTDOMAIN ),
				'13' => __( '13', GEODIRECTORY_TEXTDOMAIN ),
				'14' => __( '14', GEODIRECTORY_TEXTDOMAIN ),
				'16' => __( '16', GEODIRECTORY_TEXTDOMAIN ),
				'17' => __( '17', GEODIRECTORY_TEXTDOMAIN ),
				'18' => __( '18', GEODIRECTORY_TEXTDOMAIN ),
				'19' => __( '19', GEODIRECTORY_TEXTDOMAIN ),

				))
		);
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_marker_cluster_settings_options');
	return $arr;
}

function geodir_map_marker_cluster_choose_maps(){
	
	$home_map_widgets = get_option('widget_geodir_map_v3_home_map');
	
	$map_canvas_arr = array();
	
	if(!empty($home_map_widgets)){
		foreach($home_map_widgets as $key=>$value){
			if(is_numeric($key))
			$map_canvas_arr['geodir_map_v3_home_map_'.$key] = 'geodir_map_v3_home_map_'.$key;
		}
	}
	
	return $map_canvas_arr;
	
}


//now modify the widget, enable marker cluster options
// apply filters
$marker_cluster_maps = get_option('geodir_marker_cluster_on_maps') ;
if(!empty($marker_cluster_maps) && is_array($marker_cluster_maps))
{
	foreach($marker_cluster_maps as $map_canvas_name)
	{
	  	add_filter('geodir_map_options_' . $map_canvas_name , 'geodir_marker_cluster_update_map_options') ;
	}
}

function geodir_marker_cluster_update_map_options($map_options)
{
	$map_options['enable_marker_cluster'] = true ;
	return $map_options ;
}



add_filter( 'geodir_all_js_msg', 'geodir_marker_cluster_add_settings_msg', 10, 1 );

function geodir_marker_cluster_add_settings_msg($msgs){
	global $wpdb;
	
	if($size = get_option('geodir_marker_cluster_size')){
		$msgs['geodir_marker_cluster_size'] = $size;
	}
	
	if($size = get_option('geodir_marker_cluster_zoom')){
		$msgs['geodir_marker_cluster_zoom'] = $size;
	}
	return $msgs;
}
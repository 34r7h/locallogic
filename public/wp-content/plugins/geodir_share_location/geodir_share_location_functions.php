<?php

function geodir_share_location_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRSHARELOCATION_TEXTDOMAIN));
	}
	
}


function geodir_share_location_js_scripts()
{

	wp_enqueue_script( 'jquery' );
	
	wp_register_script( 'gdsharelocation-js', GDSHARELOCATION_PLUGINDIR_URL.'/js/share_location.js',array('jquery') );
	wp_enqueue_script( 'gdsharelocation-js' );
}


function geodir_localize_all_share_location_js_msg()
{
	
	$arr_alert_msg = array(
							'geodir_plugin_url' => geodir_plugin_url(),
							'geodir_admin_ajax_url' => admin_url('admin-ajax.php'),
							'request_param' =>  geodir_get_request_param(),
							'ask_for_share_location' => apply_filters('geodir_ask_for_share_location' , false ) ,
							'UNKNOWN_ERROR' =>__('Unable to find your location.',GEODIRSHARELOCATION_TEXTDOMAIN),
						
							'PERMISSION_DENINED' =>	__('Permission denied in finding your location.',GEODIRSHARELOCATION_TEXTDOMAIN),
						
							'POSITION_UNAVAILABLE' =>	__('Your location is currently unknown.',GEODIRSHARELOCATION_TEXTDOMAIN),	
						
							'BREAK' =>	__('Attempt to find location took too long.',GEODIRSHARELOCATION_TEXTDOMAIN),
						
						//start not show alert msg
						
							'DEFAUTL_ERROR' =>	__('Browser unable to find your location.',GEODIRSHARELOCATION_TEXTDOMAIN)
							// end not show alert msg
							
							
						);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}

	$script = "var geodir_all_share_location_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>'	;
}

function geodir_get_request_param(){
	global $wp_query;
	
	$request_param = array();
	
	if ( is_tax() && geodir_get_taxonomy_posttype() ){
		global $current_term,$wp_query;
		
		$request_param['geo_url'] = 'is_term';
		$request_param['geo_term_id'] = $current_term->term_id;
		$request_param['geo_taxonomy'] = $current_term->taxonomy;
		
	}elseif ( is_post_type_archive() && in_array(get_query_var('post_type'),geodir_get_posttypes()) ){
	
		$request_param['geo_url'] = 'is_archive';
		$request_param['geo_posttype'] = get_query_var('post_type');
	
	}elseif( is_author() && isset($_REQUEST['geodir_dashbord'] ) ){
		$request_param['geo_url'] = 'is_author';
		$request_param['geo_posttype'] = $_REQUEST['stype'];
	}elseif( is_search() && isset($_REQUEST['geodir_search']) ){
		$request_param['geo_url'] = 'is_search';
		$request_param['geo_request_uri'] = $_SERVER['QUERY_STRING'];
	}else{
		$request_param['geo_url'] = 'is_location';
	}
	
	return json_encode($request_param);
} 




function geodir_share_location()
{
	echo apply_filters('geodir_share_location' , home_url() ) ;
	die;
}
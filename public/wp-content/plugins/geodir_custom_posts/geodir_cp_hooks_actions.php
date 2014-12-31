<?php

add_action('admin_init', 'geodir_cp_activation_redirect');


add_action('geodir_admin_option_form', 'geodir_get_admin_cp_form', 3);

add_action('admin_init', 'geodir_cp_from_submit_handler');

add_action( 'admin_enqueue_scripts', 'geodir_custom_post_type_script' );

add_filter('geodir_settings_tabs_array','geodir_cp_fields_tab',3);

add_action('geodir_before_admin_panel' , 'geodir_display_cp_messages');

add_action('wp_ajax_geodir_cp_ajax_action', 'geodir_custom_post_type_ajax');

add_action( 'wp_ajax_nopriv_geodir_cp_ajax_action', 'geodir_custom_post_type_ajax' ); 

add_action('admin_init', 'geodir_payment_remove_unnecessary_fields');


add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_CPT', 10,1); 
function geodir_diagnose_multisite_conversion_CPT($table_arr){
	global $wpdb;
	$post_types = geodir_get_posttypes();
	
	if(!empty($post_types))
	{
		foreach($post_types as $p_type)
		{	if($p_type=='gd_place' || $p_type=='gd_event'){continue;}
			$table_arr["geodir_".$p_type."_detail"] = "CTP: geodir_".$p_type."_detail";
		}
		
	}
	return $table_arr;
}

// This is used to change the post type args before saving into the post type options array in database.
// This helps to rewrite the post type url on wordpress default rule.
add_filter('geodir_post_type_args', 'geodir_custom_post_type_args_modify',1, 2) ;


function geodir_custom_post_type_args_modify( $args ,$post_type)
{
	
	if(isset($_REQUEST['geodir_save_post_type']))
	{

			$custom_post_type	= htmlentities(trim($_REQUEST['geodir_custom_post_type']));
			$listing_slug 		= htmlentities(trim($_REQUEST['geodir_listing_slug']));
			
			if($custom_post_type == 'place')
				update_option('geodir_listing_prefix', $listing_slug);
			
			if(strtolower($post_type) == strtolower($custom_post_type))
			{
				if(array_key_exists('has_archive' ,$args ))
					$args['has_archive']  = $listing_slug ;
					
				if(array_key_exists('rewrite' ,$args ))
				{
					if(array_key_exists('slug' ,$args['rewrite']))
						$args['rewrite']['slug'] = $listing_slug.'/%gd_taxonomy%' ;		
				}
			}
					
	}
	
	return $args ;
}

function geodir_cp_fields_tab($tabs)
{
	$tabs['geodir_manage_custom_posts'] = array( 'label' =>__( 'Custom Post Types',GEODIR_CP_TEXTDOMAIN ));
	$geodir_post_types = get_option( 'geodir_post_types' );
	$post_types = geodir_get_posttypes();
	
	if(!empty($post_types))
	{
		foreach($post_types as $p_type)
		{
			if (!array_key_exists($p_type.'_fields_settings', $tabs))
			{
				
				$post_type_array = $geodir_post_types[$p_type];
				$listing_slug = $post_type_array['labels']['singular_name'];
				
				$tabs[$p_type.'_fields_settings'] = array( 	
						'label' => sprintf(__('%s Settings', GEODIR_CP_TEXTDOMAIN ), ucfirst($listing_slug) ),
						'subtabs' => array(
													array('subtab' => 'custom_fields',
																'label' =>__( 'Custom Fields', GEODIR_CP_TEXTDOMAIN),
																'request' => array('listing_type'=>$p_type)),
													array('subtab' => 'sorting_options',
																'label' =>__( 'Sorting Options', GEODIR_CP_TEXTDOMAIN),
																'request' => array('listing_type'=>$p_type)),
												),
						'request' => array('listing_type'=>$p_type) 
						);
			}
		}
	}
	
	return $tabs; 
	
}

function geodir_get_admin_cp_form($tab_name)
{
	switch ($tab_name)
	{
		case 'geodir_manage_custom_posts':
			if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'cp_addedit')
				geodir_cp_add_edit_form();
			else
				geodir_cp_listing();
			
			break;
	}
	
}

add_filter('geodir_custom_post_type_default_menu_icon','geodir_custom_post_type_default_menu_icon');

function geodir_custom_post_type_default_menu_icon($menu_icon){
	
	if($menu_icon ==''){
		//replace menu icon with available icon in plugin image folder or from core.
	}
	
	return $menu_icon;
	
}

add_action('wp_footer','geodir_custom_post_type_localize_all_js_msg');

add_action('admin_footer','geodir_custom_post_type_localize_all_js_msg');


function geodir_custom_post_type_localize_all_js_msg(){

	global $path_location_url;
	
	$arr_alert_msg = array(
		
		'geodir_cp_post_type_char_validation' => __('Post Type can not be more than 17 characters.', GEODIR_CP_TEXTDOMAIN),
		'geodir_cp_post_type_illegal_characters_validation' => __('Post Type contains illegal characters.', GEODIR_CP_TEXTDOMAIN),
		'geodir_cp_post_type_blank_validation' => __('Post Type must not be blank.', GEODIR_CP_TEXTDOMAIN),
		'geodir_cp_listing_slug_illegal_characters_validation' => __('Listing Slug contains illegal characters.', GEODIR_CP_TEXTDOMAIN),
		'geodir_cp_listing_slug_blank_validation' => __('Listing Slug must not be blank.', GEODIR_CP_TEXTDOMAIN),
		'geodir_cp_listing_order_value_validation' => __('Enter valid order value.', GEODIR_CP_TEXTDOMAIN),
		
	);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_custom_post_type_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}


add_filter('next_post_link', 'geodir_single_next_previous_fix',10,4);
add_filter('previous_post_link', 'geodir_single_next_previous_fix',10,4);

if (!function_exists('geodir_single_next_previous_fix')) { // we add this in location manager and CPT 
function geodir_single_next_previous_fix($url,$link,$direction,$post) {
	global $wpdb,$plugin_prefix,$post;
	$post_type_array = geodir_get_posttypes();
	if(isset($post->post_type) && in_array($post->post_type , $post_type_array))
	{
	
		$post_date = $timestamp = strtotime($post->post_date);

	$where ='';
	if(isset($post->country_slug) && $post->country_slug != '')
		$where .= " AND FIND_IN_SET('[".$post->country_slug."]', post_locations) ";
	
	if(isset($post->region_slug) && $post->region_slug != '')
		$where .= " AND FIND_IN_SET('[".$post->region_slug."]', post_locations) ";

	if(isset($post->city_slug) && $post->city_slug != '')
		$where .= " AND FIND_IN_SET('[".$post->city_slug."]', post_locations) ";

	
		
		if($direction==__('Next',GEODIRLOCATION_TEXTDOMAIN)){$op = '>';}else{$op = '<';}
		$table = $plugin_prefix.$post->post_type.'_detail';
			$pid = $wpdb->get_var(
				$wpdb->prepare(
				"SELECT  post_id FROM ".$table." WHERE submit_time $op %d  AND post_status='publish' $where AND post_id !=%d LIMIT 1",
				$post_date,$post->ID
				)
			);

			if($pid){
				$just_url = preg_match("/href=\"([^\"]*)\"/", $url, $matches);
				return str_replace($matches[1], get_permalink($pid), $url);
			}
		
	}
	
    return $url;
}
}

// change tab heading text on post detrail page
add_filter('geodir_detail_page_tab_list_extend', 'geodir_detail_page_tab_headings_change_ctp');
function geodir_detail_page_tab_headings_change_ctp($tabs_arr) {
	global $wpdb;
	
	$post_type = geodir_get_current_posttype();
	$all_postypes = geodir_get_posttypes();
		
	if (!empty($tabs_arr) && $post_type != '' && in_array($post_type, $all_postypes)) {
		$geodir_post_types = get_option('geodir_post_types');
		if (empty($geodir_post_types[$post_type])) {
			return $tabs_arr;
		}
		$post_type_array = $geodir_post_types[$post_type];
		if (array_key_exists('post_profile', $tabs_arr) && !empty($post_type_array['labels']['label_post_profile'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_profile']);
			
			if (isset($tabs_arr['post_profile']['heading_text']) && $field_title != '') {
				$tabs_arr['post_profile']['heading_text'] = __($field_title, GEODIR_CP_TEXTDOMAIN);
			}
		}
		
		if (array_key_exists('post_info', $tabs_arr) && !empty($post_type_array['labels']['label_post_info'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_info']);
			
			if (isset($tabs_arr['post_info']['heading_text']) && $field_title != '') {
				$tabs_arr['post_info']['heading_text'] = __($field_title, GEODIR_CP_TEXTDOMAIN);
			}
		}
		
		if (array_key_exists('post_images', $tabs_arr) && !empty($post_type_array['labels']['label_post_images'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_images']);
			
			if (isset($tabs_arr['post_images']['heading_text']) && $field_title != '') {
				$tabs_arr['post_images']['heading_text'] = __($field_title, GEODIR_CP_TEXTDOMAIN);
			}
		}
		
		if (array_key_exists('post_map', $tabs_arr) && !empty($post_type_array['labels']['label_post_map'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_map']);
			
			if (isset($tabs_arr['post_map']['heading_text']) && $field_title != '') {
				$tabs_arr['post_map']['heading_text'] = __($field_title, GEODIR_CP_TEXTDOMAIN);
			}
		}
		
		if (array_key_exists('reviews', $tabs_arr) && !empty($post_type_array['labels']['label_reviews'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_reviews']);
			
			if (isset($tabs_arr['reviews']['heading_text']) && $field_title != '') {
				$tabs_arr['reviews']['heading_text'] = __($field_title, GEODIR_CP_TEXTDOMAIN);
			}
		}
		
		if (array_key_exists('related_listing', $tabs_arr) && !empty($post_type_array['labels']['label_related_listing'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_related_listing']);
			
			if (isset($tabs_arr['related_listing']['heading_text']) && $field_title != '') {
				$tabs_arr['related_listing']['heading_text'] = __($field_title, GEODIR_CP_TEXTDOMAIN);
			}
		}
	
	}
	
	return $tabs_arr;
}

?>
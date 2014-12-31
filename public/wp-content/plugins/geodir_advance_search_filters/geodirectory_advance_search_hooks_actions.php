<?php

add_filter('geodir_settings_tabs_array','geodir_advace_search_manager_tabs',100);

add_action('admin_init', 'geodir_advance_search_activation_redirect');

add_action('geodir_manage_selected_fields', 'geodir_manage_advace_search_selected_fields');

add_action('geodir_manage_available_fields', 'geodir_manage_advace_search_available_fields');

add_filter('geodir_sort_options','geodir_get_cat_sort_fields');

add_action('pre_get_posts', 'geodir_advance_search_filter',12); 

add_action('wp_ajax_geodir_ajax_advance_search_action', "geodir_advance_search_ajax_handler");

add_action( 'wp_ajax_nopriv_geodir_ajax_advance_search_action', 'geodir_advance_search_ajax_handler' );

add_filter('geodir_show_filters','geodirectory_advance_search_custom_fields',0,2);

add_action('geodir_after_search_button', 'geodir_advance_search_button');

add_action('geodir_after_search_form', 'geodir_advance_search_form');

add_action('geodir_search_fields','geodir_show_filters_fields');

add_action('geodir_after_post_type_deleted', 'geodir_advance_search_after_post_type_deleted');

add_action('geodir_after_custom_field_deleted', 'geodir_advance_search_after_custom_field_deleted', 1, 3);

add_action('geodir_advance_custom_fields','geodir_advance_admin_custom_fields');


add_filter('geodir_advance_custom_fields_heading', 'geodir_advance_admin_custom_fields_heading', 1, 2);
function geodir_advance_admin_custom_fields_heading($title, $field_type){
	
	$title = __('Advanced sort & filters options',GEODIRADVANCESEARCH_TEXTDOMAIN);
	return $title;
	
}


add_filter('geodir_custom_fields_panel_head' , 'geodir_advance_search_panel_head' , 10, 3) ;
function geodir_advance_search_panel_head($heading , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$heading =	sprintf(__('Manage advance search options.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
		
	}
	return $heading;
}


add_filter('geodir_cf_panel_available_fields_head' , 'geodir_advance_search_available_fields_head' , 10, 3) ;
function geodir_advance_search_available_fields_head($heading , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$heading =	sprintf( __('Available advance search option for %s listing and search results.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));;
		break;
	}
	return $heading;
}


add_filter('geodir_cf_panel_available_fields_note' , 'geodir_advance_search_available_fields_note' , 10, 3) ;
function geodir_advance_search_available_fields_note($note , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$note =	sprintf(__('Click on any box below to make it appear in advance search form on %s listing and search results.<br />To make a filed available here, go to custom fields tab and expand any field from selected fields panel and tick the checkbox saying \'Include this field in advance search option\'.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
	}
	return $note;
}


add_filter('geodir_cf_panel_selected_fields_head' , 'geodir_advance_search_selected_fields_head' , 10, 3) ;
function geodir_advance_search_selected_fields_head($heading , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$heading =	$heading =	sprintf(__('List of fields those will appear in advance search form on %s listing and search resutls page.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
		
	}
	return $heading;
}


add_filter('geodir_cf_panel_selected_fields_note' , 'geodir_advance_search_selected_fields_note' , 10, 3) ;
function geodir_advance_search_selected_fields_note($note , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$note =	sprintf(__('Click to expand and view field related settings. You may drag and drop to arrange fields order in advance search form on %s listing and search results page.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
		
	}
	return $note;
}


function geodir_advance_search_scripts() {
 $plugins_path =  plugins_url('geodir_advance_search_filters');
	wp_enqueue_script(
		'custom_advance_search_fields',
		 $plugins_path.'/advance_search_admin/js/custom_advance_search_fields.js',
		array( 'jquery' )
	);
}

if(isset($_REQUEST['page']) && $_REQUEST['page'] =='geodirectory')
	add_action( 'admin_enqueue_scripts', 'geodir_advance_search_scripts' );


?>
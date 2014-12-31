<?php  

add_filter( 'template_include', 'geodir_event_template_loader',0);
 
function geodir_event_template_loader($template) {
	
	if(geodir_get_current_posttype() == 'gd_event'){
	
		add_action('geodir_before_detail_fields','geodir_event_schedule_date_fields', 3);
		add_action('geodir_before_detail_fields','geodir_event_show_business_fields_html',4);
		add_filter('geodir_detail_page_sidebar_content', 'geodir_event_detail_page_sitebar_content', 2);
		add_action('geodir_before_listing', 'geodir_event_display_filter_options', 100);
		
	}
	
	return $template;
}

add_action('wp_ajax_geodir_event_manager_ajax', "geodir_event_manager_ajax");

add_action( 'wp_ajax_nopriv_geodir_event_manager_ajax', 'geodir_event_manager_ajax' );

add_filter('geodir_show_filters','geodir_add_search_fields',10,2);

add_action('geodir_after_save_listing','geodir_event_save_data',12,2);

add_action('widgets_init', 'geodir_event_register_widgets');

add_action('pre_get_posts','geodir_event_loop_filter' ,2 ); 

add_action('delete_post', 'geodir_event_delete_schedule' );

add_action( 'wp_enqueue_scripts', 'geodir_event_templates_styles' );

add_filter('geodir_hide_save_button', 'geodir_event_hide_save_button');

add_action('geodir_after_description_field', 'geodir_event_add_event_features', 1);

add_action('geodir_before_default_field_in_meta_box', 'geodir_event_add_event_features', 1);

add_action('geodir_after_description_on_listing_detail', 'geodir_event_before_description', 1);

add_action('geodir_after_description_on_listing_preview', 'geodir_event_before_description', 1);

add_filter('geodir_save_post_key', 'geodir_event_remove_illegal_htmltags', 1, 2);

add_filter('geodir_design_settings', 'geodir_event_add_listing_settings', 1);

add_filter('geodir_search_page_title',"geodir_event_calender_search_page_title", 1);

add_action('geodir_after_listing_post_title',"geodir_calender_event_details_after_post_title", 1);


add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_events', 10,1); 
function geodir_diagnose_multisite_conversion_events($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_gd_event_detail'] = __('Events',GEODIREVENTS_TEXTDOMAIN);
	$table_arr['geodir_event_schedule'] = __('Event schedule',GEODIREVENTS_TEXTDOMAIN);
	return $table_arr;
}

function geodir_event_templates_styles(){
	
	wp_register_style( 'geodir-event-frontend-style', geodir_event_plugin_url().'/gdevents-assets/css/style.css' );
	wp_enqueue_style( 'geodir-event-frontend-style' );
	
	wp_register_style('geodir-event-calendar-css', geodir_event_plugin_url().'/gdevents-assets/css/calendar.css');
	wp_enqueue_style( 'geodir-event-calendar-css' );
	
}


add_action( 'wp_enqueue_scripts', 'geodir_event_templates_script' );
function geodir_event_templates_script(){
	
	wp_enqueue_script( 'jquery' );
	
	wp_register_script( 'geodir-event-utilities', geodir_event_plugin_url().'/gdevents-assets/js/utilities.js');
	wp_enqueue_script( 'geodir-event-utilities' );
	
	wp_register_script('geodir-event-calendar-min', geodir_event_plugin_url().'/gdevents-assets/js/calendar-min.js');
	wp_enqueue_script( 'geodir-event-calendar-min' );
	
	
wp_localize_script( 'geodir-event-calendar-min', 'cal_trans', geodir_get_cal_trans_array() );
	
	wp_register_script('geodir-event-custom-js', geodir_event_plugin_url().'/gdevents-assets/js/event-custom.js');
	wp_enqueue_script( 'geodir-event-custom-js' );

}

function geodir_event_calenders_script(){
	
	wp_register_script( 'geodir-event-calender', geodir_event_plugin_url().'/gdevents-assets/js/event_custom.js');
	wp_enqueue_script( 'geodir-event-calender');
	
}


add_action('wp_footer','geodir_event_localize_vars',10);
add_action('admin_footer','geodir_event_localize_vars',10);
function geodir_event_localize_vars()
{
	global $pagenow;
	
	if(geodir_is_page('add-listing') || $pagenow == 'post.php' || $pagenow == 'post-new.php'){
	
		$arr_alert_msg = array(
								'geodir_event_ajax_url' => geodir_event_manager_ajaxurl(),
								'EVENT_PLEASE_WAIT' =>__( 'Please wait...', GEODIREVENTS_TEXTDOMAIN ),
								'EVENT_CHOSEN_NO_RESULT_TEXT' =>__( 'No Business', GEODIREVENTS_TEXTDOMAIN ),
								'EVENT_CHOSEN_KEEP_TYPE_TEXT' =>__( 'Please wait...', GEODIREVENTS_TEXTDOMAIN ),
								'EVENT_CHOSEN_LOOKING_FOR_TEXT' =>__( 'We are searching for', GEODIREVENTS_TEXTDOMAIN ),
							);
		
		foreach ( $arr_alert_msg as $key => $value ) 
		{
			if ( !is_scalar($value) )
				continue;
			$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
		}
	
		$script = "var geodir_event_alert_js_var = " . json_encode($arr_alert_msg) . ';';
		echo '<script>';
		echo $script ;	
		echo '</script>';
		
	}
	
}
// display linked business under detail page tabs
add_filter( 'geodir_detail_page_tab_list_extend', 'geodir_detail_page_link_business_tab' );
function geodir_detail_page_link_business_tab( $tabs_arr ) {
	global $post, $wpdb, $plugin_prefix;;
	$post_type = geodir_get_current_posttype();
	$all_postypes = geodir_get_posttypes();
		
	if ( !empty($post) && !empty($post->ID) && !empty( $tabs_arr ) && $post_type != 'gd_event' && in_array( $post_type, $all_postypes ) && ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) ) {			
		
		$post_number = get_option('geodir_related_post_count');
		$list_sort = get_option('geodir_related_post_sortby');
		$character_count = get_option('geodir_related_post_excerpt');
		
		$listing_ids = geodir_event_link_businesses( $post->ID, $post_type );
		if ( !empty( $listing_ids ) ) {
			$listings_data = geodir_event_link_businesses_data( $listing_ids );
			
			if ( !empty( $listings_data ) ) {
				$html = geodir_event_get_link_business( $listings_data );
				if ( $html ) {
					$post->link_business = '';
					$tabs_arr['link_business'] = array( 
														'heading_text' => __( 'Events', GEODIREVENTS_TEXTDOMAIN ),
														'is_active_tab' => false,
														'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, 'link_business'),
														'tab_content' => $html
													);
				}
			}
		}
	}
	return $tabs_arr;
}

// display link business on event detail page to go back to the linked listing
add_action( 'geodir_after_detail_page_more_info', 'geodir_event_display_link_business' );
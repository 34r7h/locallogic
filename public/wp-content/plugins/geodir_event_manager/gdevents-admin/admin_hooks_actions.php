<?php
/**
 * GeoDirectory Event Admin
 * 
 * Main admin file which loads all settings panels and sets up admin menus.
 *
 * @author 		Vikas Sharma
 * @category 	Admin
 * @package 	GeoDirectory Events
 */
 
/* Admin init loader */
 
add_action('admin_init', 'geodir_event_activation_redirect');

add_filter('geodir_general_settings', 'geodir_event_add_dummy_install_tab');

add_action('geodir_insert_dummy_posts_gd_event','geodir_event_insert_dummy_posts',1);

add_action('geodir_delete_dummy_posts_gd_event','geodir_event_delete_dummy_posts',1);

add_action( 'add_meta_boxes', 'geodir_event_meta_box_add' );

add_action('menu_order', 'geodir_event_admin_menu_order',12);

add_action('custom_menu_order', 'geodir_event_admin_custom_menu_order');

add_action('geodir_event_add_fields_on_metabox', 'geodir_event_show_event_fields_html');

add_action('geodir_event_business_fields_on_metabox', 'geodir_event_show_business_fields_html');

add_action('geodir_payment_package_extra_fields','geodir_event_package_add_extra_fields', 2, 1);

add_action('geodir_before_admin_panel' , 'geodir_display_event_messages'); 

add_action('geodir_admin_option_form' , 'geodir_event_tab_content', 110);

add_filter('geodir_settings_tabs_array','geodir_event_manager_tabs',110);

add_action( 'admin_enqueue_scripts', 'geodir_event_admin_templates_styles' );

add_action( 'admin_enqueue_scripts', 'geodir_event_admin_templates_script' );

add_action('admin_init', 'geodir_event_delete_unnecessary_fields');

function geodir_event_admin_templates_script(){
	
	wp_register_script( 'geodir-event-utilities', geodir_event_plugin_url().'/gdevents-assets/js/utilities.js');
	wp_enqueue_script( 'geodir-event-utilities' );
	
	wp_register_script('geodir-event-calendar-min', geodir_event_plugin_url().'/gdevents-assets/js/calendar-min.js');
	wp_enqueue_script( 'geodir-event-calendar-min' );
	wp_localize_script( 'geodir-event-calendar-min', 'cal_trans', geodir_get_cal_trans_array() );
	wp_register_script('geodir-event-custom-js', geodir_event_plugin_url().'/gdevents-assets/js/event-custom.js');
	wp_enqueue_script( 'geodir-event-custom-js' );

}

function geodir_event_admin_templates_styles(){

	wp_register_style( 'geodir-event-backend-style', geodir_event_plugin_url().'/gdevents-assets/css/admin-style.css' );
	wp_enqueue_style( 'geodir-event-backend-style' );
	
	wp_register_style('geodir-event-calendar-css', geodir_event_plugin_url().'/gdevents-assets/css/calendar.css');
	wp_enqueue_style( 'geodir-event-calendar-css' );
	
}

function geodir_event_meta_box_add()
{
	global $post;
  
	add_meta_box( 'geodir_event_schedule', __( 'Event Schedule', GEODIREVENTS_TEXTDOMAIN ), 'geodir_event_event_schedule_setting', 'gd_event','normal', 'high' );
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if(!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
	
	if(isset($package_info->link_business_pkg) && $package_info->link_business_pkg  == '1'){	
		
		add_meta_box('geodir_event_business',__( 'Businesses', GEODIREVENTS_TEXTDOMAIN ),'geodir_event_business_setting','gd_event','side','high');
		
	}
	
}


function geodir_event_add_dummy_install_tab($arr){

		$arr[] = array( 'name' => __( 'Event Dummy Data', GEODIREVENTS_TEXTDOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'gdevent_dummy_data_settings' );
		
		$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'gdevent_dummy_data_installer',
		'post_type' => 'gd_event',
		'type' 		=> 'dummy_installer',
		'css' 		=> 'min-width:300px;',
		'std' 		=> '40'
		);
		$arr[] = array( 'type' => 'sectionend', 'id' => 'gdevent_dummy_data_settings');
	
		return $arr;
}


add_action('geodir_sample_csv_download_link', 'geodir_sample_csv_for_events_download_link', 1);

function geodir_sample_csv_for_events_download_link(){
	?>
	<div class="geodir_event_csv_download">
	<a href="<?php echo geodir_event_plugin_url() . '/gdevents-assets/event_listing.csv'?>" ><?php _e("Download sample csv for Events", GEODIREVENTS_TEXTDOMAIN)?></a>
	</div>
	<?php
}


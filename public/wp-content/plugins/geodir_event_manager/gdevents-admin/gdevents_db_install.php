<?php 

/**
 * Geo Directory Event Database Install *
 * 
 * Plugin install database tables
 *
 * @author 		Vikas Sharma
 * @category 	Admin
 * @package 	GeoDirectory Events
 *
 */


function geodir_event_tables_install() {
	
	global $wpdb;

	$wpdb->hide_errors();
	
	// rename db for multisite
	if($wpdb->query("SHOW TABLES LIKE 'geodir_gd_event_detail'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_gd_event_detail'")==0){$wpdb->query("RENAME TABLE geodir_gd_event_detail TO ".$wpdb->prefix."geodir_gd_event_detail");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_event_schedule'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_event_schedule'")==0){$wpdb->query("RENAME TABLE geodir_event_schedule TO ".$wpdb->prefix."geodir_event_schedule");}

	$collate = '';
	if($wpdb->has_cap( 'collation' )) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}
	
	$event_detail = "CREATE TABLE IF NOT EXISTS ".EVENT_DETAIL_TABLE." (
					`post_id` int(11) NOT NULL,
					`post_title` text NULL DEFAULT NULL,
					`post_status` varchar(20) NULL DEFAULT NULL,
					`default_category` INT NULL DEFAULT NULL,
					`post_tags` varchar(254) NULL DEFAULT NULL,
					`geodir_link_business` varchar(10) NULL DEFAULT NULL,
					`post_location_id` int(11) NOT NULL,
					`marker_json` text NULL DEFAULT NULL,
					`claimed` ENUM( '1', '0' ) NULL DEFAULT '0',
					`businesses` ENUM( '1', '0' ) NULL DEFAULT '0',
					`is_featured` ENUM( '1', '0' ) NULL DEFAULT '0',
					`featured_image` VARCHAR( 254 ) NULL DEFAULT NULL,
					`paid_amount` DOUBLE NOT NULL DEFAULT '0', 
					`package_id` INT(11) NOT NULL DEFAULT '1',
					`alive_days` INT(11) NOT NULL DEFAULT '0',
					`paymentmethod` varchar(30) NULL DEFAULT NULL,
					`expire_date` VARCHAR( 25 ) NULL DEFAULT NULL,
					`recurring_dates` TEXT NOT NULL,
					`event_reg_desc` text NULL DEFAULT NULL,
					`event_reg_fees` varchar(200) NULL DEFAULT NULL,
					`submit_time` varchar(15) NULL DEFAULT NULL,
					`submit_ip` varchar(254) NULL DEFAULT NULL,
					`overall_rating` float(11) DEFAULT NULL, 
					`rating_count` INT(11) DEFAULT '0', 
					`post_locations` VARCHAR( 254 ) NULL DEFAULT NULL,
					`post_latitude` varchar(20) NULL,
					`post_longitude` varchar(20) NULL,
					`post_dummy` ENUM( '1', '0' ) NULL DEFAULT '0', 
					PRIMARY KEY (`post_id`)) $collate ";
	
					
	$wpdb->query($event_detail);
	
	do_action('geodir_after_custom_detail_table_create', 'gd_event', EVENT_DETAIL_TABLE);
	
	if($wpdb->get_var("SHOW TABLES LIKE '".EVENT_DETAIL_TABLE."'") == EVENT_DETAIL_TABLE){
		
		if(!$wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'recurring_dates'")){
		
			$wpdb->query("ALTER TABLE ".EVENT_DETAIL_TABLE."
					ADD `recurring_dates` TEXT NOT NULL AFTER `expire_date` ,
					ADD `event_reg_desc` text NULL DEFAULT NULL AFTER `recurring_dates` ,
					ADD `event_reg_fees` varchar(200) NULL DEFAULT NULL AFTER `event_reg_desc` 
					");
			}
			
		if(!$wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'post_latitude'")){
		
			$wpdb->query("ALTER TABLE ".EVENT_DETAIL_TABLE."
					ADD `post_latitude` varchar(20) NULL AFTER `post_locations` ,
					ADD `post_longitude` varchar(20) NULL AFTER `post_latitude` 
					");
			}
	}
	
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `".EVENT_SCHEDULE."` (
		`event_id` int(11) NOT NULL,
		`event_date` datetime NOT NULL,
		`event_starttime` time,
		`event_endtime` time
	)  $collate ");
	
	
	
}	
		

function geodir_event_create_default_fields(){
	
	$package_info = array() ;
	$package_info = geodir_post_package_info($package_info , '', 'gd_event');
	$package_id = $package_info->pid;
	
	$fields = array();
	
	$fields[]	= array('listing_type' 	=> 'gd_event', 
						'data_type' 	=> 'VARCHAR', 
						'field_type' 	=> 'taxonomy', 
						'admin_title' 	=> __('Category', GEODIREVENTS_TEXTDOMAIN), 
						'admin_desc' 	=> __('Select listing category from here. Select at least one category', GEODIREVENTS_TEXTDOMAIN), 
						'site_title' 	=> __('Category', GEODIREVENTS_TEXTDOMAIN), 
						'htmlvar_name' 	=> 'gd_eventcategory', 
						'default_value'	=> '', 
						'is_default'  	=> '1',
						'is_admin'			=> '1',
						'show_on_pkg' => array($package_id),
						'is_required'	=> '1', 
						'clabels'		=> __('Category', GEODIREVENTS_TEXTDOMAIN));
	
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'address', 
							'admin_title' 	=> __('Address', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> ADDRESS_MSG, 
							'site_title' 	=> __('Address', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'post', 
							'default_value'	=> '', 
							'option_values' => '', 
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'is_required'	=> '1',
							'required_msg'	=> __('Address fields are required', GEODIREVENTS_TEXTDOMAIN),
							'clabels'		=> __('Address', GEODIREVENTS_TEXTDOMAIN),
							'extra'	=> array(	'show_city'=> 1 , 'city_lable' => __('City', GEODIREVENTS_TEXTDOMAIN),
												'show_region' => 1, 'region_lable' => __('Region', GEODIREVENTS_TEXTDOMAIN),
												'show_country' => 1, 'country_lable' => __('Country', GEODIREVENTS_TEXTDOMAIN),
												'show_zip' => 1, 'zip_lable' => __('Zip/Post Code', GEODIREVENTS_TEXTDOMAIN),
												'show_map' => 1, 'map_lable' => __('Set Address On Map', GEODIREVENTS_TEXTDOMAIN),
												'show_mapview' => 1, 'mapview_lable' => __('Select Map View', GEODIREVENTS_TEXTDOMAIN),
												'show_latlng' => 1));
							
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'text', 
							'admin_title' 	=> __('Time', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('Enter Business or Listing Timing Information.<br/>eg. : 10.00 am to 6 pm every day', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Time', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'timing', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id), 
							'clabels'		=> __('Time', GEODIREVENTS_TEXTDOMAIN));
	
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'phone', 
							'admin_title' 	=> __('Phone', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('You can enter phone number,cell phone number etc.', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Phone', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'contact', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Phone', GEODIREVENTS_TEXTDOMAIN));
	
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'email', 
							'admin_title' 	=> __('Email', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('You can enter your business or listing email.', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Email', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'email', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Email', GEODIREVENTS_TEXTDOMAIN));												
							
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'url', 
							'admin_title' 	=> __('Website', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('You can enter your business or listing website.', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Website', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'website', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id), 
							'clabels'		=> __('Website', GEODIREVENTS_TEXTDOMAIN));
	
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'url', 
							'admin_title' 	=> __('Twitter', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('You can enter your business or listing twitter url.', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Twitter', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'twitter', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Twitter', GEODIREVENTS_TEXTDOMAIN));
	
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'url', 
							'admin_title' 	=> __('Facebook', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('You can enter your business or listing facebook url.', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Facebook', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'facebook', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Facebook', GEODIREVENTS_TEXTDOMAIN));
							
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'TEXT', 
							'field_type' 	=> 'textarea', 
							'admin_title' 	=> __('Video', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('Add video code here, YouTube etc.', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Video', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'video', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '0',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Video', GEODIREVENTS_TEXTDOMAIN));
	
	$fields[]	= array(	'listing_type'	=> 'gd_event', 
							'data_type' 	=> 'TEXT', 
							'field_type' 	=> 'textarea', 
							'admin_title' 	=> __('Special Offers', GEODIREVENTS_TEXTDOMAIN), 
							'admin_desc' 	=> __('Note: List out any special offers (optional)', GEODIREVENTS_TEXTDOMAIN), 
							'site_title' 	=> __('Special Offers', GEODIREVENTS_TEXTDOMAIN), 
							'htmlvar_name' 	=> 'special_offers', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '0',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Special Offers', GEODIREVENTS_TEXTDOMAIN));																								
				
	foreach($fields as $field_index => $field )
	{ 
		geodir_custom_field_save( $field ); 
	}							
}
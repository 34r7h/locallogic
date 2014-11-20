<?php

/**
 * Geo Directory Event Install *
 * 
 * Plugin install script which adds default pages, taxonomies, and database tables
 *
 * @author 		Vikas Sharma
 * @category 	Admin
 * @package 	GeoDirectory Events
 *
 */

/**
 * Include core instalation files
 */
 
include_once('gdevents_db_install.php');

/**
 * Activate gdevents_
 */ 

function geodir_events_activation() {
	
	if (get_option('geodir_installed')) {
	
		 gdevents_install();        
	
			update_option( "gdevents_installed", 1 );
			add_option('geodir_events_activation_redirect', 1);
				
	}
	
}


function gdevents_install() {

	global $gdevents_settings;
	
	geodir_event_tables_install();
	geodir_event_post_type();
	geodir_event_create_default_fields();
	
	geodir_update_options( geodir_event_general_setting_options(), true );
	update_option( "gdevents_db_version", GDEVENTS_VERSION );
}


function geodir_event_post_type() {
		
		
	global $wpdb;
	
	$menu_icon  = file_exists(geodir_plugin_path() . '/geodirectory-assets/images/favicon.ico') ? geodir_plugin_url() . '/geodirectory-assets/images/favicon.ico' : geodir_event_plugin_url() . '/gdevents-assets/images/favicon.ico';
	
	/* Event taxonomy */
	
	if ( ! taxonomy_exists('gd_eventcategory') ){

		$gd_placecategory = array();
		$gd_placecategory['object_type']= 'gd_event';
		$gd_placecategory['listing_slug']= 'events';
		$gd_placecategory['args'] = array (
			'public' => true,
			'hierarchical'  => true,
			'rewrite' => array ('slug' =>'events', 'with_front' =>false, 'hierarchical' =>true),
			'query_var' => true,
			'labels' => array (
				'name'          => __( 'Event Categories', GEODIREVENTS_TEXTDOMAIN ),
				'singular_name' => __( 'Event Category', GEODIREVENTS_TEXTDOMAIN ),
				'search_items'  => __( 'Search Event Categories', GEODIREVENTS_TEXTDOMAIN ),
				'popular_items' => __( 'Popular Event Categories', GEODIREVENTS_TEXTDOMAIN ),
				'all_items'     => __( 'All Event Categories', GEODIREVENTS_TEXTDOMAIN ),
				'edit_item'     => __( 'Edit Event Category', GEODIREVENTS_TEXTDOMAIN ),
				'update_item'   => __( 'Update Event Category', GEODIREVENTS_TEXTDOMAIN ),
				'add_new_item'  => __( 'Add New Event Category', GEODIREVENTS_TEXTDOMAIN ),
				'new_item_name' => __( 'New Event Category', GEODIREVENTS_TEXTDOMAIN ),
				'add_or_remove_items' => __( 'Add or remove Event categories', GEODIREVENTS_TEXTDOMAIN ),
			),
		);

		$geodir_taxonomies = get_option('geodir_taxonomies');
		$geodir_taxonomies['gd_eventcategory'] = $gd_placecategory;
		update_option( 'geodir_taxonomies', $geodir_taxonomies );
		
		flush_rewrite_rules();
	}
	
	if ( ! taxonomy_exists('gd_event_tags') ){

		$gd_placetags = array();
		$gd_placetags['object_type']= 'gd_event';
		$gd_placetags['listing_slug']= 'events/tags';
		$gd_placetags['args'] = array (
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array ( 'slug' => 'events/tags', 'with_front' => false, 'hierarchical' => false ),
			'query_var' => true,
			
			'labels' => array (
				'name'          => __( 'Event Tags', GEODIREVENTS_TEXTDOMAIN ),
				'singular_name' => __( 'Event Tag', GEODIREVENTS_TEXTDOMAIN ),
				'search_items'  => __( 'Search Event Tags', GEODIREVENTS_TEXTDOMAIN ),
				'popular_items' => __( 'Popular Event Tags', GEODIREVENTS_TEXTDOMAIN ),
				'all_items'     => __( 'All Event Tags', GEODIREVENTS_TEXTDOMAIN ),
				'edit_item'     => __( 'Edit Event Tag', GEODIREVENTS_TEXTDOMAIN ),
				'update_item'   => __( 'Update Event Tag', GEODIREVENTS_TEXTDOMAIN ),
				'add_new_item'  => __( 'Add New Event Tag', GEODIREVENTS_TEXTDOMAIN ),
				'new_item_name' => __( 'New Event Tag Name', GEODIREVENTS_TEXTDOMAIN ),
				'add_or_remove_items' => __( 'Add or remove Event tags', GEODIREVENTS_TEXTDOMAIN ),
				'choose_from_most_used' => __( 'Choose from the most used Event tags', GEODIREVENTS_TEXTDOMAIN ),
				'separate_items_with_commas' => __( 'Separate Event tags with commas', GEODIREVENTS_TEXTDOMAIN ),
				),
		);

		
		$geodir_taxonomies = get_option('geodir_taxonomies');
		$geodir_taxonomies['gd_event_tags'] = $gd_placetags;
		update_option( 'geodir_taxonomies', $geodir_taxonomies );

		flush_rewrite_rules();

	}

	/**
	 * Post Types
	 **/
	if ( ! post_type_exists('gd_event') ) {
		
		$labels = array (
		'name'          => __('Events', GEODIREVENTS_TEXTDOMAIN),
		'singular_name' => __('Event', GEODIREVENTS_TEXTDOMAIN),
		'add_new'       => __('Add New', GEODIREVENTS_TEXTDOMAIN),
		'add_new_item'  => __('Add New Event', GEODIREVENTS_TEXTDOMAIN),
		'edit_item'     => __('Edit Event', GEODIREVENTS_TEXTDOMAIN),
		'new_item'      => __('New Event', GEODIREVENTS_TEXTDOMAIN),
		'view_item'     => __('View Event', GEODIREVENTS_TEXTDOMAIN),
		'search_items'  => __('Search Events', GEODIREVENTS_TEXTDOMAIN),
		'not_found'     => __('No Event Found', GEODIREVENTS_TEXTDOMAIN),
		'not_found_in_trash' => __('No Event Found In Trash', GEODIREVENTS_TEXTDOMAIN) );
		
		$place_default = array (
		'labels' => $labels,	
		'can_export' => true,
		'capability_type' => 'post',
		'description' => __('Event post type.', GEODIREVENTS_TEXTDOMAIN),
		'has_archive' => 'events',
		'hierarchical' => false,
		'map_meta_cap' => true,
		'menu_icon' => $menu_icon,
		'public' => true,
		'query_var' => true,
		'rewrite' => array ('slug' => 'events/%gd_taxonomy%', 'with_front' => false, 'hierarchical' => true),
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments' ),
		'taxonomies' => array('gd_eventcategory','gd_event_tags') );
		
		//Update custom post types
		$geodir_post_types = get_option( 'geodir_post_types' );
		$geodir_post_types['gd_event'] = $place_default;
		update_option( 'geodir_post_types', $geodir_post_types );
		
		flush_rewrite_rules();
		
	}
	
	geodir_register_taxonomies();
	geodir_register_post_types();
	
	do_action( 'geodir_create_new_post_type', 'gd_event' );
	
}



<?php
/*
Plugin Name: GeoDirectory - Enfold Theme Compatibility
Plugin URI: http://wpgeodirectory.com
Description: This plugin lets the GeoDirectory Plugin use the Enfold theme HTML wrappers to fit and work perfectly.
Version: 1.0.2
Author: GeoDirectory
Author URI: http://wpgeodirectory.com

*/


// BECAUSE THIS PLUGIN IS CALLED BEFORE GD WE MUST CALL THIS PLUGIN ONCE GD LOADS
add_action( 'plugins_loaded', 'enfold_action_calls', 10 );
function enfold_action_calls(){
	
	/* ACTIONS
	****************************************************************************************/
	// LOAD STYLESHEET
	add_action( 'wp_enqueue_scripts', 'wpgeo_enfold_styles' );
	
	// Add body class for styling purposes
	add_filter('body_class','wpgeo_enfold_body_class');
	
	// LOCATION MANAGER MENU ACTIONS - set the location menu item before the Enfold search
	if (function_exists('geodir_location_menu_items')) {
		remove_filter('wp_nav_menu_items','geodir_location_menu_items', 110);
		add_filter('wp_nav_menu_items','geodir_location_menu_items', 8, 2);
	}
	// GEODIR MENU ACTIONS - set the GeoDir menu items before the Enfold search
	remove_filter('wp_nav_menu_items','geodir_menu_items', 100);
	add_filter('wp_nav_menu_items','geodir_menu_items', 7, 2);
	
	// HOME TOP SIDEBAR
	remove_action( 'geodir_home_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10 );
	//add_action( 'ava_after_main_container', 'enfold_home_sidebar' );
	add_action( 'geodir_before_search_form', 'enfold_search_container_open' );
	add_action( 'geodir_after_search_form', 'enfold_search_container_close' );
	
	// WRAPPER OPEN ACTIONS
	remove_action( 'geodir_wrapper_open', 'geodir_action_wrapper_open', 10 );
	add_action( 'geodir_wrapper_open', 'enfold_action_wrapper_open', 9 );
	add_action( 'geodir_wrapper_open', 'enfold_detail_title', 8,2 ); // ADD GEODIR TITLE
	
	// WRAPPER CLOSE ACTIONS
	remove_action( 'geodir_wrapper_close', 'geodir_action_wrapper_close', 10);
	add_action( 'geodir_wrapper_close', 'enfold_action_wrapper_close', 11);	
	
	// WRAPPER CONTENT OPEN ACTIONS
	remove_action( 'geodir_wrapper_content_open', 'geodir_action_wrapper_content_open', 10 );
	add_action( 'geodir_wrapper_content_open', 'enfold_action_wrapper_content_open', 9, 3 );
	
	// WRAPPER CONTENT CLOSE ACTIONS
	remove_action( 'geodir_wrapper_content_close', 'geodir_action_wrapper_content_close', 10);
	add_action( 'geodir_wrapper_content_close', 'enfold_action_wrapper_content_close', 11);
	
	// SIDEBAR RIGHT OPEN ACTIONS
	remove_action( 'geodir_sidebar_right_open', 'geodir_action_sidebar_right_open', 10 );
	add_action( 'geodir_sidebar_right_open', 'enfold_action_sidebar_right_open', 10, 4 );
	
	// SIDEBAR RIGHT CLOSE ACTIONS
	remove_action( 'geodir_sidebar_right_close', 'geodir_action_sidebar_right_close', 10);
	add_action( 'geodir_sidebar_right_close', 'enfold_action_sidebar_right_close', 10,1);
	
	// HOME PAGE BREADCRUMBS
	remove_action( 'geodir_home_before_main_content', 'geodir_breadcrumb', 20 );
	
	// LISTINGS PAGE BREADCRUMBS & TITLES
	remove_action( 'geodir_listings_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_listings_page_title', 'geodir_action_listings_title',10);
	
	// DETAILS PAGE BREADCRUMBS & TITLES
	remove_action( 'geodir_detail_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_details_main_content', 'geodir_action_page_title',20);
	
	// SEARCH PAGE BREADCRUMBS & TITLES
	remove_action( 'geodir_search_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_search_page_title', 'geodir_action_search_page_title',10);
	
	// AUTHOR PAGE BREADCRUMBS & TITLES
	remove_action( 'geodir_author_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_author_page_title', 'geodir_action_author_page_title',10);
	
	// DISABLE ENFOLD MAPS CALL
	add_filter( 'avf_load_google_map_api', 'gd_enfold_remove_maps_api', 10, 1 );
	
} // Close enfold_action_calls

/* FUNCTIONS
****************************************************************************************/

// ENQUEUE STYLESHEET & ADD BODY CLASS
function wpgeo_enfold_styles() {
    // Register the style like this for a plugin:
    wp_register_style( 'wpgeo-enfold-style', plugins_url( '/css/plugin.css', __FILE__ ), array(), 'all' );
    wp_enqueue_style( 'wpgeo-enfold-style' );
}

function wpgeo_enfold_body_class($classes) {
	$classes[] = 'wpgeo-enfold';
	return $classes;
}

// ADD OPENING WRAP TO SEARCHBAR
function enfold_search_container_open() {
	echo '<div class="container">';
}

// ADD CLOSING WRAP TO SEARCHBAR
function enfold_search_container_close() {
	echo '</div>';
}

// WRAPPER OPEN FUNCTIONS
function enfold_action_wrapper_open(){
	global $wp;
		if ( $wp->query_vars['page_id'] == get_option( 'geodir_location_page' ) || is_home() && !$_GET['geodir_signup'] ) {
			echo '<div class="enfold-home-top">';
        	dynamic_sidebar('geodir_home_top');
			echo '</div>';
			echo "<div class='container_wrap container_wrap_first main_color ".avia_layout_class( 'main' ,false)."'>";
		} elseif ( is_home() && $_GET['geodir_signup'] ) {
			echo "<div class='container_wrap container_wrap_first main_color fullsize'>";
		} else {
			echo "<div class='container_wrap container_wrap_first main_color ".avia_layout_class( 'main' ,false)."'>";
		}
		echo "<div class='container template-blog '>";
}

// PAGE TITLE & BREADCRUMB FUNCTIONS
function enfold_detail_title($page,$class){
	//echo '###'.$page;
	global $wp;
	if ( $wp->query_vars['page_id'] == get_option( 'geodir_location_page' ) && !$_GET['geodir_signup'] ) {
		add_action( 'avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8,2 );
		echo avia_title();
	}elseif($page=='details-page'){
		add_action( 'avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8,2 );
		echo avia_title();
	}elseif($page=='listings-page' || $page=='search-page'){
		add_action( 'avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8,2 );
		ob_start() ; // Start buffering;
		geodir_action_listings_title();
		$gd_title = ob_get_clean();
		$title_p = explode('">',$gd_title);
		$title = str_replace('</h1></header>',"",$title_p[2]);
		//print_r($title_p);
		echo avia_title(array('title' => $title));
	}elseif($page=='author-page'){
		add_action( 'avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8,2 );
		ob_start() ; // Start buffering;
		geodir_action_author_page_title();
		$gd_title = ob_get_clean();
		$gd_title = str_replace('<h1>',"",$gd_title);
		$gd_title = str_replace('</h1>',"",$gd_title);
		echo avia_title(array('title' => $gd_title));
	}elseif($page=='add-listing-page'){
		add_action( 'avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8,2 );
		echo avia_title();
	}
}

function enfold_detail_breadcrum($trail, $args ){
	ob_start() ; // Start buffering;
		geodir_breadcrumb();
		$gd_crums = ob_get_clean();
		if($gd_crums){
		$gd_crums = str_replace('<div class="geodir-breadcrumb clearfix"><ul id="breadcrumbs"><li>',"",$gd_crums);
		$gd_crums = str_replace('</li></ul></div>',"",$gd_crums);
		$gd_crums = str_replace('</li><li>',"",$gd_crums);
		$gd_crums = explode(" > ", $gd_crums);
		$trail_end = array_pop($gd_crums);
		$gd_crums['trail_end']=$trail_end;
		//print_r($gd_crums);
	//print_r($trail);
	$trail = $gd_crums;
		}
		return $trail;
}

// WRAPPER CLOSE FUNCTIONS
function enfold_action_wrapper_close(){
	echo '</div></div><!-- content ends here-->';
}

// WRAPPER CONTENT OPEN FUNCTIONS
function enfold_action_wrapper_content_open($type='',$id='',$class=''){
	if ( is_home() && $_GET['geodir_signup'] ) {
		echo "<main class='template-page content twelve alpha units' ". avia_markup_helper(array('context' => 'content','post_type'=>'page','echo'=>false))." ".$class.">";
	} else {
		echo "<main class='template-page content ".avia_layout_class( 'content',false )." units' ". avia_markup_helper(array('context' => 'content','post_type'=>'page','echo'=>false))." ".$class.">";
	}
	echo '<div class="entry-content-wrapper">';
}

// WRAPPER CONTENT CLOSE FUNCTIONS
function enfold_action_wrapper_content_close(){
	echo '</div></main>';
}

// SIDEBAR RIGHT OPEN FUNCTIONS
function enfold_action_sidebar_right_open($type='',$id='',$class='',$itemtype=''){
$sidebar_smartphone = avia_get_option('smartphones_sidebar') == 'smartphones_sidebar' ? 'smartphones_sidebar_active' : "";
	echo "<aside class='sidebar sidebar_right ".$sidebar_smartphone." ".avia_layout_class( 'sidebar', false )." units' ".avia_markup_helper(array('context' => 'sidebar', 'echo' => false)).">";
	echo "<div class='inner_sidebar extralight-border'>";
}

// SIDEBAR RIGHT CLOSE FUNCTIONS
function enfold_action_sidebar_right_close($type=''){
	echo '</div></aside><!-- sidebar ends here-->';
}

// DISABLE MAPS API FUNCTION
function gd_enfold_remove_maps_api($call) {
return false;	
}





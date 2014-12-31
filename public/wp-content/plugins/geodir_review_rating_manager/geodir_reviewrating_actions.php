<?php
/**
 * activation hooks
 **/
if ( is_admin() ) :
	 
	add_action('admin_init', 'geodir_reviewrating_activation_redirect');
	 
	add_action( 'admin_enqueue_scripts', 'geodir_reviewrating_admin_scripts', 11);
	
	add_action( 'admin_enqueue_scripts', 'geodir_reviewrating_admin_styles', 11);
	 
	add_filter('geodir_settings_tabs_array','geodir_reviewrating_navigations',5);
	
	add_action('geodir_admin_option_form' , 'geodir_reviewrating_option_forms',5);
	
	add_action('wp_ajax_geodir_reviewrating_ajax', "geodir_reviewrating_ajax_actions");
	
	add_action( 'wp_ajax_nopriv_geodir_reviewrating_ajax', 'geodir_reviewrating_ajax_actions' );
	
	add_action( 'add_meta_boxes', 'geodir_reviewrating_comment_metabox', 13 );
	 
	add_action('admin_init', 'geodir_reviewrating_reviews_change_unread_to_read');
	
endif;

 
add_action('init','geodir_reviewrating_remove_all_filters',100);
add_action( 'init', 'geodir_reviewrating_action_on_init' );

function geodir_reviewrating_remove_all_filters(){
	
	if(has_action( 'comment_form_logged_in_after', 'geodir_comment_rating_fields' )){
		
		if(get_option('geodir_reviewrating_enable_rating')):
			remove_action('wp_set_comment_status','geodir_update_rating_status_change');
			remove_action( 'comment_form_logged_in_after', 'geodir_comment_rating_fields' );
			remove_action( 'comment_form_before_fields', 'geodir_comment_rating_fields' );
			remove_action( 'edit_comment','geodir_update_rating' );
			remove_action( 'delete_comment', 'geodir_comment_delete_comment' );
			remove_filter( 'comment_text', 'geodir_wrap_comment_text');
			remove_action( 'add_meta_boxes_comment', 'geodir_comment_add_meta_box' );
			remove_filter( 'comment_row_actions', 'geodir_comment_meta_row_action', 11, 1 );
		endif;
	
		remove_action( 'comment_post','geodir_save_rating' );
	}	
}


add_action( 'wp_enqueue_scripts', 'geodir_reviewrating_comments_script');

add_action('wp_ajax_geodir_reviewrating_plupload', "geodir_reviewrating_plupload_action");
 
add_action( 'wp_ajax_nopriv_geodir_reviewrating_plupload', 'geodir_reviewrating_plupload_action' );

add_filter('geodir_after_custom_detail_table_create','geodir_reviewrating_after_custom_detail_table_create',1,2);

add_action( 'delete_comment', 'geodir_reviewrating_delete_comments' );

add_action('wp_set_comment_status','geodir_reviewrating_set_comment_status',100,2);
//add_action('edit_comment','geodir_reviewrating_set_comment_status',100,2);

add_filter('comments_array', 'geodir_reviewrating_filter_comments'); 

add_action( 'geodir_create_new_post_type', 'geodir_reviewrating_create_new_post_type', 1, 1 );

add_action( 'geodir_after_post_type_deleted', 'geodir_reviewrating_delete_post_type', 1, 1 );


/* Show overall comments on comments listing (backend) */
if(get_option('geodir_reviewrating_enable_rating')){
	add_filter( 'comment_row_actions', 'geodir_reviewrating_comment_meta_row_action', 12, 1 );
}


/* Show Comment Rating */
if(get_option('geodir_reviewrating_enable_rating') || get_option('geodir_reviewrating_enable_images') || get_option('geodir_reviewrating_enable_review') || get_option('geodir_reviewrating_enable_sorting') || get_option('geodir_reviewrating_enable_sharing')){
	add_filter('comment_text', 'geodir_reviewrating_wrap_comment_text',12,2);
}
 
/* Show Post Rating */
if(get_option('geodir_reviewrating_enable_rating') && get_option('geodir_reviewrating_enable_sorting')){
	add_action("comments_template",'geodir_reviewrating_show_post_ratings');
}

/* Modify Comment Form Fields Taxt */
if(get_option('geodir_reviewrating_enable_rating')):
	//add_filter('comment_form_defaults', 'gdreviewratings_set_comment_defaults'); 
endif;


add_filter('geodir_reviews_rating_comment_shorting', 'geodir_reviews_rating_update_comment_shorting_options');
function geodir_reviews_rating_update_comment_shorting_options($arr){

	if(get_option('geodir_reviewrating_enable_images')){
		$arr['least_images'] = 'Least Images';
		$arr['highest_images'] = 'Highest Images';
	}
												 
	if(get_option('geodir_reviewrating_enable_review')){
		$arr['low_review'] = 'Lowest Reviews';
		$arr['high_review'] = 'Highest Reviews';
	}
	
	return $arr;
}
 
/* Show Rating Fields In Comment Form */
add_action( 'comment_form_logged_in_after', 'geodir_reviewrating_comment_rating_fields' );
 
add_action( 'comment_form_before_fields', 'geodir_reviewrating_comment_rating_fields' );
 
add_filter('comment_reply_link', 'geodir_reviewrating_comment_replylink');/* Wrap Comment reply link */

add_filter('cancel_comment_reply_link', 'geodir_reviewrating_cancle_replylink');/* Wrap Cancel rply link */
 
add_filter('comment_save_pre','geodir_reviewrating_update_comments');/* update Comment Rating */
  
add_action('comment_post','geodir_reviewrating_save_rating');/* Save Comment Rating */

add_action('init', 'geodir_reviewrating_remove_unncesssary_directories'); 

add_action('geodir_before_admin_panel' , 'geodir_reviewrating_display_messages'); 

add_action('wp_footer','geodir_reviewrating_localize_all_js_msg');

add_action('admin_footer','geodir_reviewrating_localize_all_js_msg');

add_action('admin_head-media-upload-popup','geodir_reviewrating_localize_all_js_msg');
 

add_action('geodir_before_review_rating_stars_on_listview', 'geodir_before_reviewrating_advance_stars_on_listview', 2, 2 ) ;
add_action('geodir_after_review_rating_stars_on_listview', 'geodir_after_reviewrating_advance_stars_on_listview', 2, 2 ) ;

add_action('geodir_before_review_rating_stars_on_gridview', 'geodir_before_reviewrating_advance_stars_on_gridview', 2, 2 ) ;
add_action('geodir_after_review_rating_stars_on_gridview', 'geodir_after_reviewrating_advance_stars_on_gridview', 2, 2 ) ;

add_action('geodir_before_review_rating_stars_on_detail', 'geodir_before_reviewrating_advance_stars_on_detail', 2, 2 ) ;
add_action('geodir_after_review_rating_stars_on_detail', 'geodir_after_reviewrating_advance_stars_on_detail', 2, 2 ) ;


add_filter('geodir_review_rating_stars_on_infowindow', 'geodir_reviewrating_advance_stars_on_infowindow', 2, 3 ) ;

 
function geodir_reviewrating_localize_all_js_msg(){

	global $path_location_url;
	
	$arr_alert_msg = array(
							'geodir_reviewrating_admin_url' => admin_url('admin.php'),
							'geodir_reviewrating_admin_ajax_url' => geodir_reviewrating_ajax_url(),
							'geodir_reviewrating_select_overall_rating_off_img' => __('Please select overall rating Off image.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_overall_rating_on_img' => __('Please select overall rating on image.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_overall_rating_half_img' => __('Please select Overall rating half image.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_please_enter' => __('Please enter', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_score_text' => __('Score text', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_star_text' => __('Star Text', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_enter_title' => __('Please enter Title.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_rating_delete_confirmation' => __('Do you want to delete this rating?', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_please_select' => __('Please select', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_categories_text' => __('Categories.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_post_type' => __('Please select Post Type.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_enter_rating_title' => __('Please enter rating title.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_multirating_style' => __('Please Select multirating style.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_review_like_img' => __('Please select review like image.', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_review_unlike_img' => __('Please select review unlike image.', GEODIRREVIEWRATING_TEXTDOMAIN),
							
							'geodir_reviewrating_hide_images' => __('Hide Images', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_show_images' => __('Show Images', GEODIRREVIEWRATING_TEXTDOMAIN),
							
							'geodir_reviewrating_hide_ratings' => __('Hide Multi Ratings', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_show_ratings' => __('Show Multi Ratings', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_delete_image_confirmation' => __('Are you sure want to delete this image?', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_please_enter_below' => __('Please enter below', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_please_enter_above' => __('Please enter above', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_numeric_validation' => __('Please enter only numeric value', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_maximum_star_rating_validation' => __('You are create maximum seven star rating', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_star_and_input_box_validation' => __('Your input box number and number of star is not same', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_star_and_score_text_validation' => __('Your input box number and number of Score text is not same', GEODIRREVIEWRATING_TEXTDOMAIN),
							'geodir_reviewrating_select_rating_off_img' => __('Please select rating off image.', GEODIRREVIEWRATING_TEXTDOMAIN),
							
						);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_reviewrating_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}
add_filter('geodir_is_reviews_show', 'geodir_review_rating_is_reviews_show', 2, 2);

/* google rich snippets for reviews */
add_action( 'wp_footer', 'geodir_review_rating_reviews_rich_snippets' );

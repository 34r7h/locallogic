<?php
 
function geodir_reviewrating_navigations($tabs){
	 
	$total_readunread_reviews = '';
	if(geodir_reviewrating_unread_reviews())
	{
		$total_readunread_reviews = '<span id="unaproved_reviews">'.geodir_reviewrating_unread_reviews().'</span>';
	}
	
	$tabs['multirating_fields'] = array( 'label' =>__( 'MultiRatings', GEODIRREVIEWRATING_TEXTDOMAIN ),
																				'subtabs' => array(
																													array('subtab' => 'geodir_multirating_options',
																																'label' =>__( 'General', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_setting')),
																													array('subtab' => 'geodir_rating_settings',
																																'label' =>__( 'Overall Rating', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_overall_setting')),
																													array('subtab' => 'geodir_rating_style',
																																'label' =>__( 'Rating Styles', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_styles')),
																													array('subtab' => 'geodir_create_rating',
																																'label' =>__( 'Create Ratings', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_rating_category')),
																													array('subtab' => 'geodir_manage_review',
																																'label' =>__( 'Like / Unlike Icons', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_review_setting'))
																													)
																				);
	
	$tabs['reviews_fields'] = array( 'label' =>__( 'Reviews <span id="">'.$total_readunread_reviews.'</span>', GEODIRREVIEWRATING_TEXTDOMAIN ),
																				'subtabs' => array(
																													array('subtab' => 'all',
																																'label' =>__( 'All ('.geodir_reviewrating_get_comments_count().')', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_setting')),
																													array('subtab' => 'pending',
																																'label' =>__( 'Pending ('.geodir_reviewrating_get_comments_count('pending').')', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_overall_setting')),
																													array('subtab' => 'approved',
																																'label' =>__( 'Approve ('.geodir_reviewrating_get_comments_count('approved').')', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_styles')),
																													array('subtab' => 'spam',
																																'label' =>__( 'Spam ('.geodir_reviewrating_get_comments_count('spam').')', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_rating_category')),
																													array('subtab' => 'trash',
																																'label' =>__( 'Trash ('.geodir_reviewrating_get_comments_count('trash').')', GEODIRREVIEWRATING_TEXTDOMAIN),
																																'form_action' => admin_url('admin-ajax.php?action=geodir_reviewrating_ajax&ajax_action=update_review_setting'))
																													)
																													);
	
	return $tabs; 
}


function geodir_reviewrating_admin_scripts($hook){

	if( ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'multirating_fields') || 'comment.php' == $hook){	
	
		wp_register_script( 'geodir-reviewrating-rating-js', GEODIR_REVIEWRATING_PLUGINDIR_URL .'/js/rating-script.js' );
		wp_enqueue_script( 'geodir-reviewrating-rating-js' );
	}	
	
	if(( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'reviews_fields') || 'comment.php' == $hook){
	
		wp_register_script( 'geodir-reviewrating-review-script', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/js/comments-script.js' );
		wp_enqueue_script( 'geodir-reviewrating-review-script' );

		wp_register_script( 'geodir-reviewrating-lightbox-jquery', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/js/jquery.lightbox-0.5.js' );
		wp_enqueue_script( 'geodir-reviewrating-lightbox-jquery' );	
		
	}
	
}

function geodir_reviewrating_admin_styles($hook){

	if( ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'multirating_fields') || 'comment.php' == $hook){	
		
		wp_register_style( 'geodir-reviewrating-rating-admin-css', GEODIR_REVIEWRATING_PLUGINDIR_URL .'/css/admin_style.css' );
		wp_enqueue_style( 'geodir-reviewrating-rating-admin-css' );
	}	
	
	if(( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'reviews_fields') || 'comment.php' == $hook){
		
		wp_register_style( 'geodir-reviewrating-comments-admin-css', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/css/admin_style.css' );
		wp_enqueue_style( 'geodir-reviewrating-comments-admin-css' );
	
		wp_register_style( 'geodir-reviewrating-lightbox-css', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/css/jquery.lightbox-0.5.css' );
		wp_enqueue_style( 'geodir-reviewrating-lightbox-css' );
			
	}
	
}


function geodir_reviewrating_comments_script(){

	wp_enqueue_script( 'jquery' );
	wp_register_script( 'geodir-reviewrating-rating-js', GEODIR_REVIEWRATING_PLUGINDIR_URL .'/js/rating-script.js' );
	wp_enqueue_script( 'geodir-reviewrating-rating-js' );
	
	if( geodir_is_page('detail') )
	{
		wp_register_script( 'geodir-reviewrating-review-script', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/js/comments-script.js' );
		wp_enqueue_script( 'geodir-reviewrating-review-script' );
		
		wp_register_style( 'geodir-reviewratingrating-style', GEODIR_REVIEWRATING_PLUGINDIR_URL .'/css/style.css' );
		wp_enqueue_style( 'geodir-reviewratingrating-style' );
	}
	
	// SCRIPT FOR UPLOAD
	wp_enqueue_script('plupload-all');
	wp_enqueue_script('jquery-ui-sortable');	 
	
	wp_register_script( 'geodir-reviewrating-plupload-script', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/js/geodir-plupload.js' );
	wp_enqueue_script( 'geodir-reviewrating-plupload-script' );
	
	// place js config array for plupload
    $geodir_plupload_init = array(
        'runtimes' => 'html5,silverlight,flash,html4',
        'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
        'container' => 'plupload-upload-ui', // will be adjusted per uploader
        'drop_element' => 'dropbox', // will be adjusted per uploader
        'file_data_name' => 'async-upload', // will be adjusted per uploader
        'multiple_queues' => true,
        'max_file_size' => '2mb',
        'url' => admin_url('admin-ajax.php'),
        'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
        'filters' => array(array('title' => __('Allowed Files', GEODIRREVIEWRATING_TEXTDOMAIN), 'extensions' => '*')),
        'multipart' => true,
        'urlstream_upload' => true,
        'multi_selection' => false, // will be added per uploader
         // additional post data to send to our ajax hook
        'multipart_params' => array(
            '_ajax_nonce' => "", // will be added per uploader
            'action' => 'geodir_reviewrating_plupload', // the ajax action name
            'imgid' => 0 // will be added per uploader
        )
    );
		
		
		$geodir_reviewrating_plupload_config = json_encode($geodir_plupload_init);
		
		$geodir_plupload_init = array( 	'geodir_reviewrating_plupload_config' => $geodir_reviewrating_plupload_config,
									'geodir_totalImg' => 0,
									'geodir_image_limit' => 10,
									'geodir_upload_img_size' => '2mb' );
		
		wp_localize_script('geodir-reviewrating-plupload-script','geodir_reviewrating_plupload_localize',$geodir_plupload_init);
	
}



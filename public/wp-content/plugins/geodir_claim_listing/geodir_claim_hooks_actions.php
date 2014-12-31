<?php

add_action('admin_init', 'geodir_admin_claim_listing_init');
function geodir_admin_claim_listing_init() 
{
	if(is_admin()):
		add_filter('geodir_settings_tabs_array','geodir_admin_claim_listing_tabs' , 4); 
		add_action('geodir_admin_option_form', 'geodir_get_admin_claim_listing_option_form',4);
		add_action( 'add_meta_boxes', 'geodir_add_claim_option_metabox', 12 );
		add_action('geodir_before_admin_panel' , 'geodir_display_claim_messages');
		add_filter('geodir_claim_notifications', 'geodir_enable_editor_on_claim_notifications', 1);
	endif;	
}

add_action('before_delete_post','geodir_delete_claim_listing_info', 11);

add_action( 'admin_enqueue_scripts', 'geodir_admincss_claim_manager', 10 );

add_action( 'admin_enqueue_scripts', 'geodir_claim_admin_scripts' );

add_action('wp_footer','geodir_claim_localize_all_js_msg');

add_action('admin_footer','geodir_claim_localize_all_js_msg');

add_action('wp_ajax_geodir_claim_ajax_action', "geodir_claim_manager_ajax");

add_action( 'wp_ajax_nopriv_geodir_claim_ajax_action', 'geodir_claim_manager_ajax' );

add_action('admin_init', 'geodirclaimlisting_activation_redirect');

add_action('admin_init', 'geodir_claims_change_unread_to_read');

add_action('wp_enqueue_scripts', 'geodir_add_claim_listing_stylesheet');

add_action('wp_enqueue_scripts', 'geodir_add_claim_listing_scripts');

add_action('geodir_after_edit_post_link', 'geodir_display_post_claim_link', 2);

add_filter( 'template_include', 'geodir_claim_template_loader', 11 );

add_action('geodir_before_main_form_fields' , 'geodir_add_claim_fields_before_main_form', 1); 

add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_claim_manager', 10,1); 

function geodir_diagnose_multisite_conversion_claim_manager($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_claim'] = __('Claim listing',GEODIRCLAIM_TEXTDOMAIN);
	return $table_arr;
}

function geodir_add_claim_fields_before_main_form(){
	
	global $post;
	$is_claimed = isset($post->claimed) ? $post->claimed : ''; ?>
	
	<div id="geodir_claimed_row" class="required_field geodir_form_row clearfix">
			<label><?php echo CLAIM_BUSINESS_OWNER_ASSOCIATE;?><span>*</span> </label>
			<input class="gd-radio" <?php if($is_claimed == '1'){echo 'checked="checked"';} ?> type="radio" name="claimed" value="1" field_type="radio">
			<?php echo CLAIM_YES_TEXT;?>
			<input class="gd-radio" <?php if($is_claimed == '0'){echo 'checked="checked"';} ?> type="radio" name="claimed" value="0" field_type="radio">
			<?php echo CLAIM_NO_TEXT;?>
		 <span class="geodir_message_error"><?php echo CLAIM_DECLARE_OWNER_ASSOCIATE;?></span>
	</div><?php
	
}

function geodir_claim_localize_all_js_msg(){

	global $path_location_url;
	
	$arr_alert_msg = array(
							'geodir_claim_admin_url' => admin_url('admin.php'),
							'geodir_claim_admin_ajax_url' => admin_url('admin-ajax.php'),
							'geodir_want_to_delete_claim' => CLAIM_WANT_TO_DELETE,
							'geodir_want_to_approve_claim' => CLAIM_WANT_TO_APPROVE,
							'geodir_want_to_reject_claim' => CLAIM_WANT_TO_REJECT,
							'geodir_want_to_undo_claim' => CLAIM_WANT_TO_UNDO,
							'geodir_what_is_claim_process' => WHAT_IS_CLAIM_PROCESS,
							'geodir_claim_process_hide' => CLAIM_LISTING_PROCESS_HIDE,
							'geodir_claim_field_id_required' =>  __('This field is required.',GEODIRCLAIM_TEXTDOMAIN),
							'geodir_claim_valid_email_address_msg' =>  __('Please enter valid email address.',GEODIRCLAIM_TEXTDOMAIN),
						);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_claim_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}

?>
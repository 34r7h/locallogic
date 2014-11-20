<?php

/**
 * Deactivate gdevent
 */


function geodir_event_inactive_posttype(){

	global $wpdb, $plugin_prefix;
		
	update_option( "gdevents_installed", 0 );
	
	$posttype = 'gd_event';
	
	$geodir_taxonomies = get_option('geodir_taxonomies');
				
	if (array_key_exists($posttype.'category', $geodir_taxonomies))
	{
		unset($geodir_taxonomies[$posttype.'category']);
		update_option( 'geodir_taxonomies', $geodir_taxonomies );
	}
	
	if (array_key_exists($posttype.'_tags', $geodir_taxonomies))
	{
		unset($geodir_taxonomies[$posttype.'_tags']);
		update_option( 'geodir_taxonomies', $geodir_taxonomies );
	}
	
	
	$geodir_post_types = get_option( 'geodir_post_types' );
	
	if (array_key_exists($posttype, $geodir_post_types))
	{
		unset($geodir_post_types[$posttype]);
		update_option( 'geodir_post_types', $geodir_post_types );
	}
	 
	//UPDATE SHOW POST TYPES NAVIGATION OPTIONS 
	
	$get_posttype_settings_options = array('geodir_add_posttype_in_listing_nav','geodir_allow_posttype_frontend','geodir_add_listing_link_add_listing_nav','geodir_add_listing_link_user_dashboard','geodir_listing_link_user_dashboard');
	
	foreach($get_posttype_settings_options as $get_posttype_settings_options_obj)
	{
		$geodir_post_types_listing = get_option( $get_posttype_settings_options_obj );
		
		if (in_array($posttype, $geodir_post_types_listing))
		{
			$geodir_update_post_type_nav = array_diff($geodir_post_types_listing, array($posttype));
			update_option( $get_posttype_settings_options_obj, $geodir_update_post_type_nav );
		}
	}

}
 
function geodir_event_deactivation(){
	
	geodir_event_inactive_posttype();
	
}


function geodir_event_uninstall(){
	if ( ! isset($_REQUEST['verify-delete-adon']) ) 
	{
		$plugins = isset( $_REQUEST['checked'] ) ? (array) $_REQUEST['checked'] : array();
			//$_POST = from the plugin form; $_GET = from the FTP details screen.
			
			wp_enqueue_script('jquery');
					require_once(ABSPATH . 'wp-admin/admin-header.php');
					printf( '<h2>%s</h2>' ,__( 'Warning!!' , GEODIREVENTS_TEXTDOMAIN) );
					printf( '%s<br/><strong>%s</strong><br /><br />%s <a href="http://wpgeodirectory.com">%s</a>.' , __('You are about to delete a Geodirectory Adon which has important option and custom data associated to it.' ,GEODIREVENTS_TEXTDOMAIN) ,__('Deleting this and activating another version, will be treated as a new installation of plugin, so all the data will be lost.', GEODIREVENTS_TEXTDOMAIN), __('If you have any problem in upgrading the plugin please contact Geodirectroy', GEODIREVENTS_TEXTDOMAIN) , __('support' ,GEODIREVENTS_TEXTDOMAIN) ) ;
					
	?><br /><br />
		<form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display:inline;">
						<input type="hidden" name="verify-delete" value="1" />
						<input type="hidden" name="action" value="delete-selected" />
						<input type="hidden" name="verify-delete-adon" value="1" />
						<?php
							foreach ( (array) $plugins as $plugin )
								echo '<input type="hidden" name="checked[]" value="' . esc_attr($plugin) . '" />';
						?>
						<?php wp_nonce_field('bulk-plugins') ?>
						<?php submit_button(  __( 'Delete plugin files only' , GEODIREVENTS_TEXTDOMAIN ), 'button', 'submit', false ); ?>
					</form>
					<form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display:inline;">
						<input type="hidden" name="verify-delete" value="1" />
						<input type="hidden" name="action" value="delete-selected" />
                        <input type="hidden" name="verify-delete-adon" value="1" />
						<input type="hidden" name="verify-delete-adon-data" value="1" />
						<?php
							foreach ( (array) $plugins as $plugin )
								echo '<input type="hidden" name="checked[]" value="' . esc_attr($plugin) . '" />';
						?>
						<?php wp_nonce_field('bulk-plugins') ?>
						<?php submit_button(  __( 'Delete both plugin files and data' , GEODIREVENTS_TEXTDOMAIN) , 'button', 'submit', false ); ?>
					</form>
					
	<?php
		require_once(ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}
	
	
	if ( isset($_REQUEST['verify-delete-adon-data']) ) 
	{
	
		global $wpdb, $plugin_prefix;
			
		update_option( "gdevents_installed", 0 );
		
		$posttype = 'gd_event';
		
		geodir_event_inactive_posttype();
		
		$args = array( 'post_type' => $posttype, 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
		
		$geodir_all_posts = get_posts( $args );
		
		if(!empty($geodir_all_posts)){
		
			foreach($geodir_all_posts as $posts)
			{
				wp_delete_post($posts->ID);
			}
		}
		
		do_action('geodir_after_post_type_deleted', $posttype);
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type=%s",array($posttype)));
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." WHERE post_type=%s",array($posttype)));
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_ADVANCE_SEARCH_TABLE." WHERE post_type=%s",array($posttype)));
		
		$wpdb->query("DROP TABLE ".$plugin_prefix.$posttype.'_detail');
		$wpdb->query("DROP TABLE ".$plugin_prefix.'event_schedule');	
		
		
		$default_options = geodir_event_general_setting_options();
		
		if(!empty($default_options)){
			foreach($default_options as $value){
				if(isset($value['id']) && $value['id'] != '')
					delete_option($value['id'], '');
			}
		}
	}
}


function geodir_event_activation_redirect() {

	if (get_option('geodir_events_activation_redirect', false)) {
		
		delete_option('geodir_events_activation_redirect');
		
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=gd_event_fields_settings&subtab=gd_event_general_options')); 
			
	}
	
}

function geodir_event_hide_save_button($hide_save_button){
	
	if(isset($_REQUEST['active_tab']) && $_REQUEST['active_tab']=='gdevent_dummy_data_settings')
		$hide_save_button = "style='display:none;'" ;

	return $hide_save_button;
}

function geodir_event_insert_dummy_posts(){
	
	geodir_event_default_taxonomies();
	
	ini_set('max_execution_time', 999999); //300 seconds = 5 minutes
	
	global $wpdb,$current_user;
	
	include_once('gdevents_dummy_post.php');
	
}


function geodir_event_delete_dummy_posts(){

	global $wpdb, $plugin_prefix;
	
	$post_ids = $wpdb->get_results("SELECT post_id FROM ".EVENT_DETAIL_TABLE." WHERE post_dummy='1'");
	
	foreach($post_ids as $post_ids_obj)
	{
		wp_delete_post($post_ids_obj->post_id);
	}
}


function geodir_event_default_taxonomies(){
	 
	global $wpdb,$dummy_image_path;
		
	$category_array = array('Events');
	
	$last_catid = isset($last_catid) ? $last_catid : '';
	
	$last_term = get_term($last_catid, 'gd_eventcategory');
			
	$uploads = wp_upload_dir(); // Array of key => value pairs
		
	
	for($i=0;$i < count($category_array); $i++)
	{
		$parent_catid = 0;
		if(is_array($category_array[$i]))
		{
			$cat_name_arr = $category_array[$i];
			for($j=0;$j < count($cat_name_arr);$j++)
			{
				$catname = $cat_name_arr[$j];
				
				if(!term_exists( $catname, 'gd_eventcategory' )){
					$last_catid = wp_insert_term( $catname, 'gd_eventcategory', $args = array('parent'=>$parent_catid) );
		
					if($j==0)
					{
						$parent_catid = $last_catid;
					}
					
					
					if(geodir_event_dummy_folder_exists())
						$dummy_image_url = geodir_event_plugin_url() . "/gdevents-admin/dummy/cat_icon";
					else
						$dummy_image_url = 'http://www.wpgeodirectory.com/dummy_event/cat_icon';
					$catname = str_replace(' ', '_', $catname);	
					$uploaded =  (array)fetch_remote_file("$dummy_image_url/".$catname.".png");
					
					if(empty($uploaded['error']))
					{	
						$new_path = $uploaded['file'];
						$new_url = $uploaded['url'];
					}
					
					$wp_filetype = wp_check_filetype(basename($new_path), null );
				    
				    $attachment = array(
					 'guid' => $uploads['baseurl'] . '/' . basename( $new_path ), 
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => preg_replace('/\.[^.]+$/', '', basename($new_path)),
					 'post_content' => '',
					 'post_status' => 'inherit'
				    );
				    $attach_id = wp_insert_attachment( $attachment, $new_path );
				  	
					// you must first include the image.php file
				    // for the function wp_generate_attachment_metadata() to work
				    require_once(ABSPATH . 'wp-admin/includes/image.php');
				    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
				    wp_update_attachment_metadata( $attach_id, $attach_data );
					
					if(!get_tax_meta($last_catid['term_id'], 'ct_cat_icon'))
					{update_tax_meta($last_catid['term_id'], 'ct_cat_icon', array( 'id' => 'icon', 'src' => $new_url));}
				}
			}
			
		}else
		{
			$catname = $category_array[$i];
			
			if(!term_exists( $catname, 'gd_eventcategory' )){
				$last_catid = wp_insert_term( $catname, 'gd_eventcategory' );
				
				if(geodir_event_dummy_folder_exists())
					$dummy_image_url = geodir_event_plugin_url() . "/gdevents-admin/dummy/cat_icon";
				else
					$dummy_image_url = 'http://www.wpgeodirectory.com/dummy_event/cat_icon';
				$catname = str_replace(' ', '_', $catname);		
				$uploaded = (array) fetch_remote_file("$dummy_image_url/".$catname.".png");
				
				if(empty($uploaded['error']))
				{	
					$new_path = $uploaded['file'];
					$new_url = $uploaded['url'];
				}
				
				$wp_filetype = wp_check_filetype(basename($new_path), null );
				    
				    $attachment = array(
					 'guid' => $uploads['baseurl']  . '/' . basename( $new_path ), 
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => preg_replace('/\.[^.]+$/', '', basename($new_path)),
					 'post_content' => '',
					 'post_status' => 'inherit'
				    );
					$attach_id = wp_insert_attachment( $attachment, $new_path );

				  	
					// you must first include the image.php file
				    // for the function wp_generate_attachment_metadata() to work
				    require_once(ABSPATH . 'wp-admin/includes/image.php');
				    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
				    wp_update_attachment_metadata( $attach_id, $attach_data );
				
				if(!get_tax_meta($last_catid['term_id'], 'ct_cat_icon', false, 'gd_event'))
				{update_tax_meta($last_catid['term_id'], 'ct_cat_icon', array( 'id' => $attach_id, 'src' => $new_url), 'gd_event');}
			}
		}
		
	}
}


function geodir_event_event_schedule_setting(){
	
	global $post,$post_id,$post_info;  
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_event_event_schedule_noncename' );
	
	$post_info_recurring_dates = '';
	if(isset($post->ID))
		$post_info_recurring_dates = unserialize(geodir_get_post_meta($post->ID,'recurring_dates',true));
	
	$recuring_data = unserialize($post_info_recurring_dates);  
	
	do_action('geodir_event_add_fields_on_metabox', $recuring_data);
	
}


function geodir_event_business_setting(){
	
	global $post,$post_id,$post_info;  
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_event_business_noncename' );
	
	do_action('geodir_event_business_fields_on_metabox');
	
}


/* ------------------------------------------------------------------*/
/* Check if dummy folder exists or not , if not then fatch from live url */
/*--------------------------------------------------------------------*/
function geodir_event_dummy_folder_exists(){

	$path = geodir_event_plugin_path(). '/gdevents-admin/dummy/';
	if(!is_dir($path))
		return false;
	else
		return true;
		
}

function geodir_event_admin_menu_order( $menu_order ) {
	
	// Initialize our custom order array
	$gdevents_menu_order = array();
	$gdevents_menu_order[] = 'edit.php?post_type=gd_event';
	
	// Get index of deals menu
	$gdevents_events = array_search( 'edit.php?post_type=gd_event', $menu_order );
	
	if($gdevents_separator = array_search( 'separator-geodirectory', $menu_order )){
		array_splice( $menu_order, $gdevents_separator + 1, 0, $gdevents_menu_order ); 
		unset( $menu_order[$gdevents_events] );
	}	
	
	// Return order
	return $menu_order;
}


function geodir_event_admin_custom_menu_order() {
	if ( !current_user_can( 'manage_options' ) ) return false;
	return true;
}


function geodir_event_package_add_extra_fields($priceinfo=array()){
	
	?>
	
	
	
	<tr valign="top" class="single_select_page">
	<th class="titledesc" scope="row"><?php _e('Event Features Only', GEODIREVENTS_TEXTDOMAIN);?></th>
	<td class="forminp"><div class="gtd-formfield"> </div></td>
	</tr>
    
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Link business', GEODIREVENTS_TEXTDOMAIN);?></th>
		<td class="forminp"><div class="gtd-formfield">
				<select style="min-width:200px;" name="gd_link_business_pkg" >
					<option value="0" <?php if((isset($priceinfo->link_business_pkg) && $priceinfo->link_business_pkg=='0') || !isset($priceinfo->link_business_pkg)){ echo 'selected="selected"';}?> >
					<?php _e("No", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
					<option value="1" <?php if(isset($priceinfo->link_business_pkg) && $priceinfo->link_business_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
				</select>
			</div></td>
	</tr>
	<?php /*?><tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Recurring Event', GEODIREVENTS_TEXTDOMAIN);?></th>
		<td class="forminp"><div class="gtd-formfield">
				<select name="gd_recurring_pkg" >
					<option value="0" <?php if(!isset($priceinfo->recurring_pkg) || $priceinfo->recurring_pkg=='0'){ echo 'selected="selected"';}?> >
					<?php _e("No", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
					<option value="1" <?php if(isset($priceinfo->recurring_pkg) && $priceinfo->recurring_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
					</option>
				</select>
			</div></td>
	</tr>
	<?php */?>
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Registration Description', GEODIREVENTS_TEXTDOMAIN);?></th>
		<td class="forminp"><div class="gtd-formfield">
				<select name="gd_reg_desc_pkg" >
					<option value="0" <?php if(!isset($priceinfo->reg_desc_pkg) || $priceinfo->reg_desc_pkg=='0'){ echo 'selected="selected"';}?> >
					<?php _e("No", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
					<option value="1" <?php if(isset($priceinfo->reg_desc_pkg) && $priceinfo->reg_desc_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
				</select>
			</div></td>
	</tr>
	
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Registration Fees', GEODIREVENTS_TEXTDOMAIN);?></th>
		<td class="forminp"><div class="gtd-formfield">
				<select name="gd_reg_fees_pkg" >
					<option value="0" <?php if(!isset($priceinfo->reg_fees_pkg) || $priceinfo->reg_fees_pkg=='0'){ echo 'selected="selected"';}?> >
					<?php _e("No", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
					<option value="1" <?php if(isset($priceinfo->reg_fees_pkg) && $priceinfo->reg_fees_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", GEODIREVENTS_TEXTDOMAIN);?>
					</option>
				</select>
			</div></td>
	</tr>
		
	<?php
}


function geodir_event_manager_tabs($tabs){

$geodir_post_types = get_option( 'geodir_post_types' );

	foreach($geodir_post_types as $geodir_post_type => $geodir_posttype_info){
		
		$originalKey = $geodir_post_type.'_fields_settings';
		
		if($geodir_post_type == 'gd_event'){
		
			if(array_key_exists($originalKey, $tabs)){
				
				if(array_key_exists('subtabs', $tabs[$originalKey])){
					
					$tabs[$originalKey]['request'] = array();
					
					$insertValue = array('subtab' => $geodir_post_type.'_general_options',
													'label' =>__( 'General', GEODIREVENTS_TEXTDOMAIN),
													'form_action' => admin_url('admin-ajax.php?action=geodir_event_manager_ajax')
												);
					
					$new_array = array();	
					$new_array[] = $insertValue;						
					foreach($tabs[$originalKey]['subtabs'] as $key => $val){
						
						$new_array[] = $val;
					
					}
					
					$tabs[$originalKey]['subtabs'] = $new_array;
					
				}
				
			}
			
		}
		
	}
	
	return $tabs;
	
}


function geodir_event_tab_content($tab){
	
	if($tab == 'gd_event_fields_settings' && isset($_REQUEST['subtab']) && $_REQUEST['subtab']=='gd_event_general_options') { 
	
		geodir_admin_fields( geodir_event_general_setting_options() ); ?>
	
		<p class="submit">
		<input name="gd_event_general_settings" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIREVENTS_TEXTDOMAIN ); ?>" />
		<input type="hidden" name="subtab" value="" id="last_tab" />
		</p>
		</div> <?php

		
	}
	
}


function geodir_event_general_setting_options($arr=array())
{

	$arr[] = array( 'name' => __( 'Filter Settings', GEODIREVENTS_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'geodir_eventgeneral_options' );
	
	
	$arr[] = array( 'name' => __( 'Listing settings', GEODIREVENTS_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'geodir_event_general_options');
	
	$arr[] = array(  
			'name' => __( 'Default event filter', GEODIREVENTS_TEXTDOMAIN ),
			'desc' 		=> __( 'Set the default filter view of event on listing page', GEODIREVENTS_TEXTDOMAIN ),
			'id' 		=> 'geodir_event_defalt_filter',
			'css' 		=> 'min-width:300px;',
			'std' 		=> 'today',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( array( 
				'all' => __( 'All', GEODIREVENTS_TEXTDOMAIN ),
				'today' => __( 'Today', GEODIREVENTS_TEXTDOMAIN ),
				'upcoming' => __( 'Upcoming', GEODIREVENTS_TEXTDOMAIN ),
				'past' => __( 'Past', GEODIREVENTS_TEXTDOMAIN )
				))
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_event_general_options');
	
	$arr = apply_filters('geodir_ajax_duplicate_general_options' ,$arr );
	
	return $arr;
}


function geodir_display_event_messages(){

	if(isset($_REQUEST['event_success']) && $_REQUEST['event_success'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' . __( $_REQUEST['event_success'], GEODIREVENTS_TEXTDOMAIN ) . '</strong></p></div>';			
				
	}
	
}


function geodir_event_delete_unnecessary_fields(){
	global $wpdb;
	
	if(!get_option('geodir_event_delete_unnecessary_fields')){
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'categories'"))
			$wpdb->query("ALTER TABLE `".EVENT_DETAIL_TABLE."` DROP `categories`");
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'Recurring'"))
			$wpdb->query("ALTER TABLE `".EVENT_DETAIL_TABLE."` DROP `Recurring`");
			
		if($wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'event_start'"))
			$wpdb->query("ALTER TABLE `".EVENT_DETAIL_TABLE."` DROP `event_start`");
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'event_end'"))
			$wpdb->query("ALTER TABLE `".EVENT_DETAIL_TABLE."` DROP `event_end`");
			
		if($wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'event_start_time'"))
			$wpdb->query("ALTER TABLE `".EVENT_DETAIL_TABLE."` DROP `event_start_time`");
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".EVENT_DETAIL_TABLE." WHERE field = 'event_end_time'"))
			$wpdb->query("ALTER TABLE `".EVENT_DETAIL_TABLE."` DROP `event_end_time`");
		
		update_option('geodir_event_delete_unnecessary_fields', '1');
		
	}
}
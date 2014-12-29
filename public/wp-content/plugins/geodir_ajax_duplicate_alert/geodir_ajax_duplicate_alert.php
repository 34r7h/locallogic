<?php
/*
Plugin Name: GeoDirectory Ajax Duplicate Alert
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Ajax Duplicate Alert plugin.
Version: 1.0.2
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/

global $wpdb,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
$geodir_addon_list['geodir_ajax_duplicate_alert_manager'] = 'yes' ;

if(!isset($plugin_prefix))
	$plugin_prefix = 'geodir_';


if (!defined('GEODIRDUPLICATEALERT_TEXTDOMAIN')) define('GEODIRDUPLICATEALERT_TEXTDOMAIN','geodir_duplicatealert');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRDUPLICATEALERT_TEXTDOMAIN);
load_textdomain(GEODIRDUPLICATEALERT_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRDUPLICATEALERT_TEXTDOMAIN.'/'.GEODIRDUPLICATEALERT_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRDUPLICATEALERT_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-duplicatealert-languages');
include_once('language.php');



if ( is_admin() ) :

	register_activation_hook( __FILE__, 'geodir_duplicate_alert_activation' ); 
	
	register_uninstall_hook(__FILE__,'geodir_duplicate_alert_uninstall');
	
	add_filter('geodir_settings_tabs_array','geodir_adminpage_duplicate_alert',5); 
	
	add_action('geodir_admin_option_form' , 'geodir_duplicate_alert_tab_content', 5);
	
endif;

add_action('admin_init', 'geodir_duplicate_alert_activation_redirect');
add_action('admin_init', 'geodir_duplicate_alert_from_submit_handler');


add_action('activated_plugin','geodir_duplicate_alert_plugin_activated') ;
function geodir_duplicate_alert_plugin_activated($plugin)
{
	if (!get_option('geodir_installed')) 
	{
		$file = plugin_basename(__FILE__);
		if($file == $plugin) 
		{
			$all_active_plugins = get_option( 'active_plugins', array() );
			if(!empty($all_active_plugins) && is_array($all_active_plugins))
			{
				foreach($all_active_plugins as $key => $plugin)
				{
					if($plugin ==$file)
						unset($all_active_plugins[$key]) ;
				}
			}
			update_option('active_plugins',$all_active_plugins);
			
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRDUPLICATEALERT_TEXTDOMAIN));
	}
	
}

function geodir_duplicate_alert_activation(){
	
	// First check if geodir main pluing is active or not.
	if (get_option('geodir_installed')) {
		
		add_option('geodir_duplicate_alert_manager_activation_redirect', 1);
		
	}
	
}


function geodir_duplicate_alert_activation_redirect(){

	if (get_option('geodir_duplicate_alert_manager_activation_redirect', false))
	{
		delete_option('geodir_duplicate_alert_manager_activation_redirect');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=duplicatealert_fields')); 
	}
	
}


function geodir_duplicate_alert_uninstall(){
	
	delete_option('geodir_post_types_duplicate', '');
				
	$post_types = geodir_get_posttypes('object');
			
	foreach($post_types as $key => $post_types_obj){
	
		delete_option('geodir_duplicate_field_'.$key,'');
		
	}
	
}


add_action('wp_ajax_geodir_duplicatealert_ajax_action', "geodir_duplicate_alert_ajax_action");
add_action( 'wp_ajax_nopriv_geodir_duplicatealert_ajax_action', 'geodir_duplicate_alert_ajax_action' );

function geodir_duplicate_alert_ajax_url(){
	return admin_url('admin-ajax.php?action=geodir_duplicatealert_ajax_action');
}

function geodir_duplicate_alert_ajax_action(){
	
	if(isset($_REQUEST['search_val']) && $_REQUEST['search_val'] != '')
		geodir_search_duplicate_field();
	exit;
	
}


function geodir_search_duplicate_field(){
	
	global $wpdb, $plugin_prefix;
	
	$field = $_REQUEST['field'];
	$post_type = $_REQUEST['post_type'];
	
	if (isset($_REQUEST['search_val'])) {
					$search = htmlentities($_REQUEST['search_val']);
	} else  $search ='';

	if($search){
	
		$table = $plugin_prefix.$post_type.'_detail';
		
		if($wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM ".$table." WHERE field = %s", array($field)))){
				
			$query = $wpdb->prepare("SELECT d.".$field." FROM $wpdb->posts p, ".$table." d	WHERE p.ID=d.post_id AND d.".$field."=%s AND p.post_type=%s AND p.post_status='publish'", array($search, $post_type));
			
			$results = $wpdb->get_results( $query );
			
			if(count($results) > 0){
				
				$post_types = geodir_get_posttypes('object');
			
				$name = $post_types->$post_type->labels->singular_name;
				
				echo __('A', GEODIRDUPLICATEALERT_TEXTDOMAIN ).' '.ucfirst($name).__(' with this field is already listed!<br/> Please make sure you are not adding a duplicate entry', GEODIRDUPLICATEALERT_TEXTDOMAIN);
				
			}
		
		}
		
	}
	
}


function geodir_adminpage_duplicate_alert($tabs){
	
	$tabs['duplicatealert_fields'] = array( 
	'label' =>__( 'Duplicate Alert', GEODIRDUPLICATEALERT_TEXTDOMAIN ),
	'label' =>__( 'Duplicate Alert', GEODIRDUPLICATEALERT_TEXTDOMAIN )
	);
	
	return $tabs;
}


function geodir_duplicate_alert_from_submit_handler(){
		
	if(isset($_REQUEST['geodir_duplicatealert_general_options_save'])){
			
			
			$post_types =  '';
			if(isset($_REQUEST['geodir_post_types_duplicate']) && is_array($_REQUEST['geodir_post_types_duplicate'])){
				$post_types = implode(',',$_REQUEST['geodir_post_types_duplicate']);
				
			}
			
			update_option('geodir_post_types_duplicate', $post_types);
			
			$post_types = geodir_get_posttypes('object');
					
			foreach($post_types as $key => $post_types_obj){
				
				if(isset($_REQUEST['geodir_duplicate_field_'.$key]))
					update_option('geodir_duplicate_field_'.$key, $_REQUEST['geodir_duplicate_field_'.$key]);
				
			}
			
			$msg = 'Your settings have been saved.';
		
			$msg = urlencode($msg);
			
				$location = admin_url()."admin.php?page=geodirectory&tab=duplicatealert_fields&adl_success=".$msg;
			wp_redirect($location);
			exit;
			
		}

}


function geodir_duplicate_alert_tab_content($tab){
	
	switch($tab){
		
		case 'duplicatealert_fields':
		
			geodir_duplicatealert_setting_fields();
			
		break;
		
	}
	
}

function geodir_duplicatealert_setting_fields(){
	global $wpdb;
	?>
	
	<div class="inner_content_tab_main">
		<div class="gd-content-heading active">
			<h3><?php _e('Show alert when duplicate value is entered in selected field per selected post type(s) on new Listing page.',GEODIRDUPLICATEALERT_TEXTDOMAIN); ?></h3>
			
			<table cellpadding="5" class="widefat post fixed" >
		
				<thead>
						<tr>
								<th width="100" align="left"><strong><?php _e('S.No.',GEODIRDUPLICATEALERT_TEXTDOMAIN); ?></strong></th>
							 
								<th width="250" align="left"><strong><?php _e('Listing Type',GEODIRDUPLICATEALERT_TEXTDOMAIN); ?></strong></th>
								
								<th width="250" align="left"><strong><?php _e('Field Name',GEODIRDUPLICATEALERT_TEXTDOMAIN); ?></strong></th>
								
							
						</tr>
						
						<?php
						
						$selected_posttypes = array();
						
						if(get_option('geodir_post_types_duplicate') && get_option('geodir_post_types_duplicate') != '')
							$selected_posttypes = explode(',',get_option('geodir_post_types_duplicate'));
						
						$post_types = geodir_get_posttypes('object');
						$i = 0;
						foreach($post_types as $key => $post_types_obj)
						{
							$post_selected = '';
							
							if(!empty($selected_posttypes) && is_array($selected_posttypes))
									if(in_array($key, $selected_posttypes))
										$post_selected = 'checked="checked"';
							
							
							$i++;
							?>
							<tr>
								<td align="left"><?php echo $i; ?>.</th>
							 
								<td align="left">
								<input <?php echo $post_selected;?> type="checkbox" value="<?php echo $key; ?>" name="geodir_post_types_duplicate[]" /> <?php echo $post_types_obj->labels->singular_name; ?> </th>
								
								<td align="left">
							
								<?php 
									$field_records = $wpdb->get_results( 
										$wpdb->prepare(
											"select	htmlvar_name, site_title, extra_fields FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type = %s AND is_active=%s AND field_type IN ('email','phone','text','address') order by sort_order asc",
											array($key, '1')
										)
									);
									
									$duplicate_alert_fields = array();
									
									$duplicate_alert_fields = array(
										'' => 'Select Field',
										'post_title' => 'Listing Title',
										
									);
									
									foreach($field_records as $fields){
										
										if($fields->htmlvar_name == 'post'){
											
											$duplicate_alert_fields['post_address'] = $fields->site_title;
											
											if($fields->extra_fields != ''){
											
												$extra_fields = unserialize($fields->extra_fields);
												
												if(isset($extra_fields['show_zip']) && $extra_fields['show_zip'] == '1')
														$duplicate_alert_fields['post_zip'] = $extra_fields['zip_lable'];
												
											}
										
										}else{
											
											$duplicate_alert_fields[$fields->htmlvar_name] = $fields->site_title;
											
										}
										
									}
									
								?>
								
								
								<select id="geodir_duplicate_field_<?php echo $key;?>" style="min-width: 300px;" name="geodir_duplicate_field_<?php echo $key;?>">
									
									<?php
									
									$duplicate_alert_fields = apply_filters('geodir_ajax_duplicate_alert_fields_'.$key, $duplicate_alert_fields);
									
									if(!empty($duplicate_alert_fields)){
									
										foreach($duplicate_alert_fields as $field_key => $value){
											
											$selected = '';
											if(get_option('geodir_duplicate_field_'.$key) == $field_key)
												$selected = 'selected="selected"';
											
											echo '<option '.$selected.' value="'.$field_key.'">'. __($duplicate_alert_fields[$field_key], GEODIRDUPLICATEALERT_TEXTDOMAIN).'</option>';
											
										}
									
									}
									?>
								
								</select>
								
								
								</th>
							</tr>
							<?php
						}
						?>
						
					</thead>
				</table>
	
	
	<p class="submit" style="margin-top:10px;">
<input name="geodir_duplicatealert_general_options_save" class="button-primary" type="submit" value="<?php _e( 'Save changes',GEODIRDUPLICATEALERT_TEXTDOMAIN ); ?>" />
<input type="hidden" name="subtab" id="last_tab" />
</p>

		</div>
	</div>
	
	<?php

}

 
add_action('wp_footer','geodir_duplicate_alert_localize_vars',10);
function geodir_duplicate_alert_localize_vars()
{
	
	if(geodir_is_page('add-listing')){
	
		global $post;
		$geodir_current_posttype = 	isset($post->post_type) ? $post->post_type : '';
		
		if(isset($_REQUEST['listing_type']) && $_REQUEST['listing_type'] != ''){
			$geodir_current_posttype = 	$_REQUEST['listing_type'];
		}elseif(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
			 $geodir_current_posttype = get_post_type($_REQUEST['pid']);
		}elseif(isset($_REQUEST['backandedit'])){
			$post = (object)unserialize($_SESSION['listing']);
			$geodir_current_posttype = $post->listing_type;	
		}	
		
		$geodir_field_name = get_option('geodir_duplicate_field_'.$geodir_current_posttype);
		
		
		$arr_alert_msg = array(
								'geodir_duplicate_alert_ajax_url' => geodir_duplicate_alert_ajax_url(),
								'geodir_post_types_duplicate' => get_option('geodir_post_types_duplicate'),
								'geodir_duplicate_field_name' => $geodir_field_name,
								'geodir_duplicate_current_posttype' => $geodir_current_posttype,
							);
		
		foreach ( $arr_alert_msg as $key => $value ) 
		{
			if ( !is_scalar($value) )
				continue;
			$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
		}
	
		$script = "var geodir_duplicate_alert_js_var = " . json_encode($arr_alert_msg) . ';';
		echo '<script>';
		echo $script ;	
		echo '</script>';
		
	}
	
}


add_action('geodir_before_admin_panel' , 'geodir_display_duplicate_alert_messages'); 

function geodir_display_duplicate_alert_messages(){

	if(isset($_REQUEST['adl_success']) && $_REQUEST['adl_success'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' . __( $_REQUEST['adl_success'], GEODIRDUPLICATEALERT_TEXTDOMAIN ) . '</strong></p></div>';			
				
	}
	
}


add_action( 'wp_enqueue_scripts', 'geodir_ajax_duplicate_alert_templates_styles' );
function geodir_ajax_duplicate_alert_templates_styles(){
	
	if(geodir_is_page('add-listing')){
	
		wp_register_style( 'geodir-duplicate-frontend-style', WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__) ).'/css/custom_duplicate_alert.css' );
		wp_enqueue_style( 'geodir-duplicate-frontend-style' );
		
	}
}


add_action( 'wp_enqueue_scripts', 'geodir_ajax_duplicate_alert_templates_script' );
function geodir_ajax_duplicate_alert_templates_script(){
	
	if(geodir_is_page('add-listing')){
		wp_enqueue_script( 'jquery' );
		wp_register_script('geodir-duplicate-custom-js', WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__) ).'/js/custom_duplicate_alert.js');
		wp_enqueue_script( 'geodir-duplicate-custom-js' );
		
	}
}
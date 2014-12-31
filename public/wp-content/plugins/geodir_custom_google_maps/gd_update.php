<?php
// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
//set_site_transient('update_plugins', null);

if (!defined('GEODIRECTORY_TEXTDOMAIN')) define('GEODIRECTORY_TEXTDOMAIN', 'geodirectory');	
$api_url = 'http://wpgeodirectory.com/updates/';
$plugin_slug = basename(dirname(__FILE__));


if (!function_exists('gd_check_for_plugin_update')) {
function gd_check_for_plugin_update($checked_data) {
	global $api_url, $plugin_slug;

	$gd_arr = array();
	if (empty($checked_data->checked)){
		return $checked_data;
	}else{
		foreach($checked_data->checked as $key=>$value){
			if(strpos($key,'geodir_') !== false){
	
		$pieces = explode("/", $key);
		$uname = get_option( 'gd_update_uname' );
	$request_args = array(
		'slug' => $pieces[0],
		'version' => $value,
		'site' => home_url(),
		'user'	=>	$uname,
	);
	
	$request_string = gd_prepare_request('basic_check', $request_args);

	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);
	
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
		
		
	if (is_object($response) && !empty($response) && !empty($response->new_version)) {// Feed the update data into WP updater
		$response->plugin = $key;
		$response->url = 'http://wpgeodirectory.com/';
		$checked_data->response[$key] = $response;
	}
			
	
			}
		}
	}
	return $checked_data;
}
}




if (!function_exists('gd_api_info_call')) {
function gd_api_info_call($def, $action, $args) {
	global $plugin_slug, $api_url;
		
	if(strpos($args->slug,'geodir_') !== false){}else{return false;}// if not a geodir plugin bail

	
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = 1;
	$args->version = $current_version;
	
	$request_string = gd_prepare_request($action, $args);
	
	$request = wp_remote_post($api_url, $request_string);
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	
	return $res;
}
}


if (!function_exists('gd_prepare_request')) {
function gd_prepare_request($action, $args) {
	global $wp_version;
	
	return array(
		'body' => array(
			'action' => $action, 
			'request' => serialize($args),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);	
}
}


if (!function_exists('gd_plugin_upgrade_errors')) {
function gd_plugin_upgrade_errors($false,$src,$Uthis){
	global $wpdb;
if(strstr($src,'http://wpgeodirectory.com/download/')){// if downloading e then verify login details
	$Uthis->strings['incompatible_archive'] = __('Login details for GeoDirectory failed! Please check GeoDirectory>Auto Updates and that your membership is active.','geotheme');
	$Uthis->strings['download_failed'] = __('Login details for GeoDirectory failed! Please check GeoDirectory>Auto Updates and that your membership is active.','geotheme');	
	}
	
return $false;
}
}




if (!function_exists('gd_plugin_upgrade_login')) {
function gd_plugin_upgrade_login($args,$src){
	
	global $wpdb;
if(strstr($src,'http://wpgeodirectory.com/download/')){// if downloading then verify login details
	$uname = get_option( 'gd_update_uname' );
	$upass = get_option( 'gd_update_upass' );
	if($uname){
	$args['method']='POST';
	$args['body']='gd_auth_update=1&uname='.base64_encode($uname).'&upass='.$upass;
	}
	}
	
return $args;
}
}





if ( is_admin() ){
	
	// Take over the update check
	add_filter('pre_set_site_transient_update_plugins', 'gd_check_for_plugin_update');
	
	// Take over the Plugin info screen
	add_filter('plugins_api', 'gd_api_info_call', 10, 3);
	
	add_filter('upgrader_pre_download', 'gd_plugin_upgrade_errors', 10, 3);

	add_filter('http_request_args', 'gd_plugin_upgrade_login', 10, 2);
	
	add_filter('geodir_settings_tabs_array','geodir_adminpage_auto_update',5); 
	
	add_action('geodir_admin_option_form' , 'geodir_auto_update_tab_content', 5);
	
	add_action('admin_init', 'geodir_auto_update_from_submit_handler');
	
}


if (!function_exists('geodir_adminpage_auto_update')) {
function geodir_adminpage_auto_update($tabs){
	
	$tabs['auto_update_fields'] = array( 
	'label' =>__( 'Auto Updates', GEODIRECTORY_TEXTDOMAIN )
	);
	
	return $tabs;
}
}

if (!function_exists('geodir_auto_update_tab_content')) {
function geodir_auto_update_tab_content($tab){
	
	switch($tab){
		
		case 'auto_update_fields':
		
			geodir_auto_update_setting_fields();
			
		break;
		
	}
	
}
}

if (!function_exists('geodir_auto_update_setting_fields')) {
function geodir_auto_update_setting_fields(){
	global $wpdb;
	?>
	
	<div class="inner_content_tab_main">
		<div class="gd-content-heading active">
			<h3><?php _e('Enter your GeoDirectory membership details to allow you to update plugins from dashboard',GEODIRECTORY_TEXTDOMAIN); ?></h3>
			
			<table class="form-table">
<?php
$uname = get_option( 'gd_update_uname' );
$upass = get_option( 'gd_update_upass' );
if($upass){$upass = 'fakepass';}
?>
            		<tbody>
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Geodirectory username/email',GEODIRECTORY_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gd_update_uname" id="gd_update_uname" type="text" style=" min-width:300px;" value="<?php echo $uname;?>"> 
                    <span class="description"><?php _e('Enter your GeoDirectory username or email',GEODIRECTORY_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
					
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Geodirectory password',GEODIRECTORY_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gd_update_upass" id="gd_update_upass" type="password" style=" min-width:300px;" value="<?php echo $upass;?>"> 
                    <span class="description"><?php _e('Enter your GeoDirectory password',GEODIRECTORY_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
                    
                    
					</tbody></table>
											
	
	
	<p class="submit" style="margin-top:10px;">
<input name="geodir_auto_update_general_options_save" class="button-primary" type="submit" value="<?php _e( 'Save changes',GEODIRECTORY_TEXTDOMAIN ); ?>" />
<input type="hidden" name="subtab" id="last_tab" />
</p>

		</div>
	</div>
	
	<?php

}
}

if (!function_exists('geodir_auto_update_from_submit_handler')) {
function geodir_auto_update_from_submit_handler(){
		
	if(isset($_REQUEST['geodir_auto_update_general_options_save'])){
						
			if($_REQUEST['gd_update_uname']){update_option('gd_update_uname', $_REQUEST['gd_update_uname']);}
			if($_REQUEST['gd_update_upass'] && $_REQUEST['gd_update_uname']!='fakepass' ){update_option('gd_update_upass', base64_encode($_REQUEST['gd_update_upass']));}
			
			$msg = 'Your settings have been saved.';
		
			$msg = urlencode($msg);
			
				$location = admin_url()."admin.php?page=geodirectory&tab=auto_update_fields&adl_success=".$msg;
			wp_redirect($location);
			exit;
			
		}

}
}
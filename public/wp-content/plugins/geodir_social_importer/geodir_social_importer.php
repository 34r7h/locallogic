<?php
/*
Plugin Name: GeoDirectory Social Importer
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Social Importer
Version: 1.0.1
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/


global $wpdb,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END


if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';

if (!defined('GEODIRSOCIALIMPORT_TEXTDOMAIN')) define('GEODIRSOCIALIMPORT_TEXTDOMAIN', 'geodir_socialimporter');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRSOCIALIMPORT_TEXTDOMAIN);
load_textdomain(GEODIRSOCIALIMPORT_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRSOCIALIMPORT_TEXTDOMAIN.'/'.GEODIRSOCIALIMPORT_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRSOCIALIMPORT_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-socialimporter-languages');
require_once ('yelp-functions.php');

if (!defined('GEODIR_COUNTRIES_TABLE')) define('GEODIR_COUNTRIES_TABLE', $plugin_prefix . 'countries' );
if ( is_admin() ){
	
	
	add_filter('geodir_settings_tabs_array','gdfi_adminpage_facebook_integration',5); 
	
	add_action('geodir_admin_option_form' , 'gdfi_facebook_integration_tab_content', 5);
	
	add_action('admin_init', 'gdfi_facebook_integration_oauth');
	
	add_action('wp_ajax_gdfi_facebook_integration_ajax_action', "gdfi_facebook_integration_ajax");
	
}


/**
 * Facebook Page Feed Parser 
 * 
 * @using cURL
 */
function gdfi_facebook_integration_ajax()
{ //echo '###';
//print_r( $_REQUEST);

	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_gdfi_options')
	{
		
		gdfi_facebook_integration_from_submit_handler();
		
		$msg = "Your settings have been saved.";
		
		$msg = urlencode($msg);
		
		$location = admin_url()."admin.php?page=geodirectory&tab=facebook_integration&subtab=".$_REQUEST['subtab']."&claim_success=".$msg;
		
		wp_redirect($location);
		exit;
		
	}
	elseif(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'manage_gdfi_options_yelp')
	{
		gdfi_yelp_integration_from_submit_handler();
		
		$msg = __("Your settings have been saved.",GEODIRSOCIALIMPORT_TEXTDOMAIN);
		
		$msg = urlencode($msg);
		
		$location = admin_url()."admin.php?page=geodirectory&tab=facebook_integration&subtab=".$_REQUEST['subtab']."&claim_success=".$msg;
		
		wp_redirect($location);
		exit;
		
	}
}
 
function gdfi_adminpage_facebook_integration($tabs){
		
		$tabs['facebook_integration'] = array( 'label' =>__( 'Social Importer', GEODIRSOCIALIMPORT_TEXTDOMAIN ),
										'subtabs' => array(
																				array('subtab' => 'geodir_gdfi_options',
																					'label' =>__( 'Facebook', GEODIRSOCIALIMPORT_TEXTDOMAIN),
																					'form_action' => admin_url('admin-ajax.php?action=gdfi_facebook_integration_ajax_action')),
																				array('subtab' => 'manage_gdfi_options_yelp',
																					'label' =>__( 'Yelp', GEODIRSOCIALIMPORT_TEXTDOMAIN),
																					'form_action' => admin_url('admin-ajax.php?action=gdfi_facebook_integration_ajax_action')),
																				/*array('subtab' => 'geodir_claim_notification',
																					'label' =>__( 'Notifications', GEODIRSOCIALIMPORT_TEXTDOMAIN),
																					'form_action' => admin_url('admin-ajax.php?action=gdfi_facebook_integration_ajax_action'))*/
																				)
									);
	
	return $tabs; 
	$tabs['facebook_integration'] = array( 
	'label' =>__( 'Facebook Integration', GEODIRSOCIALIMPORT_TEXTDOMAIN )
	);
	
	return $tabs;
}


function gdfi_facebook_integration_tab_content($tab){
	global $wpdb;
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_gdfi_options' )
	{
		gdfi_facebook_integration_setting_fields();
	}
	
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'manage_gdfi_options_yelp' )
	{
		gdfi_yelp_integration_setting_fields();
		
	}
	

	
}

 
 
 function gdfi_facebook_integration_setting_fields(){
	global $wpdb;
	?>
	
	<div class="inner_content_tab_main">
		<div class="gd-content-heading active">
			<h3><?php _e('Enter your Facebook app details.',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></h3>

			<table class="form-table">
<?php
$gdfi_config = get_option( 'gdfi_config' );
//print_r($gdfi_config);
?>
            		<tbody>
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Facebook App ID',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gdfi_app_id" id="gdfi_app_id" type="text" style=" min-width:300px;" value="<?php if(!empty($gdfi_config['app_id'])){echo $gdfi_config['app_id'];}?>"> 
                    <span class="description"><?php _e('Enter your Facebook app ID',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
					
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Facebook App Secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gdfi_app_secret" id="gdfi_app_secret" type="password" style=" min-width:300px;" value="<?php if(!empty($gdfi_config['app_secret'])){echo $gdfi_config['app_secret'];}?>"> 
                    <span class="description"><?php _e('Enter your Facebook app secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
                    
                    <?php if(!empty($gdfi_config['app_id']) && !empty($gdfi_config['app_secret'])){?>
					<script type="text/javascript">
					win = '';
					function gdfi_auth_popup(){
						win = window.open("https://www.facebook.com/dialog/oauth?client_id=<?php echo $gdfi_config['app_id'];?>&display=popup&redirect_uri=<?php echo urlencode(admin_url()."admin.php?page=geodirectory&tab=facebook_integration");?>&scope=email,create_event,publish_actions,rsvp_event,manage_pages,status_update", "gdfi_auth", "scrollbars=no,menubar=no,height=400,width=600,resizable=yes,toolbar=no,status=no");
						var pollTimer = window.setInterval(function() {
						if (win.closed !== false) { // !== is required for compatibility with Opera
							window.clearInterval(pollTimer);
							location.reload();// reload the page to show the app as connected
						}
					}, 200);
						return false;
					}
					
					</script>
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Connect App',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <a class="button-primary" onclick="gdfi_auth_popup();" href="" target="_blank"><?php if(!empty($gdfi_config['access_token'])){_e('Refresh Access Token',GEODIRSOCIALIMPORT_TEXTDOMAIN);}else{_e('Connect Your App',GEODIRSOCIALIMPORT_TEXTDOMAIN);} ?></a>
                    </td>
					</tr>	
                    
                    
                     <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Post to page',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                   <select name="gdfi_app_page_post" id="gdfi_app_page_post">
                      <option value=""><?php _e('Select page',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></option>
                      <?php echo gdfi_get_fb_pages($gdfi_config['app_page_post']);?>
                    </select>
                    <span class="description"><?php _e('Select a Facebook page to post new listings to.',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
						
					<?php }
					
					if(!empty($gdfi_config['access_token'])){
					?>
                    
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Facebook Access Token',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <?php _e('Active, expires: ',GEODIRSOCIALIMPORT_TEXTDOMAIN); echo date('F j, Y, g:i a', $gdfi_config['access_token_expire']);?>
                    <span class="description"><?php ?></span>
                    </td>
					</tr>
                    
					
					<?php }?>						
	</tbody></table>
	
	<p class="submit" style="margin-top:10px;">
<input name="gdfi_facebook_integration_options_save" class="button-primary" type="submit" value="<?php _e( 'Save changes',GEODIRSOCIALIMPORT_TEXTDOMAIN ); ?>" />
<input type="hidden" name="subtab" id="last_tab" value="<?php echo $_REQUEST['subtab'];?>" />
</p>

		</div>
	</div>
	
	<?php

}


function gdfi_facebook_integration_from_submit_handler(){
		
	if(isset($_REQUEST['gdfi_facebook_integration_options_save'])){
			
			//echo "<pre>"; print_r($_REQUEST);
			
			$gdfi_config = get_option( 'gdfi_config' );
			if(!$gdfi_config){
			$gdfi_config_new = array('app_id'=>$_REQUEST['gdfi_app_id'],'app_secret'=>$_REQUEST['gdfi_app_secret']);	
			}else{$gdfi_config_new = $gdfi_config;}
			
			if(!empty($_REQUEST['gdfi_app_id'])){$gdfi_config_new['app_id']=$_REQUEST['gdfi_app_id'];}
			if(!empty($_REQUEST['gdfi_app_secret'])){$gdfi_config_new['app_secret']=$_REQUEST['gdfi_app_secret'];}
			if(!empty($_REQUEST['gdfi_app_page_post'])){$gdfi_config_new['app_page_post']=$_REQUEST['gdfi_app_page_post'];}
			
			update_option('gdfi_config', $gdfi_config_new);
			
			$msg = __('Your settings have been saved.',GEODIRSOCIALIMPORT_TEXTDOMAIN);
		
			$msg = urlencode($msg);
			
			//$location = admin_url()."admin.php?page=geodirectory&tab=facebook_integration&adl_success=".$msg;
			//wp_redirect($location);
			//exit;
			
		}

}
 
 
function gdfi_facebook_integration_oauth(){
	if(isset($_REQUEST['tab']) && $_REQUEST['tab']=='facebook_integration' && isset($_REQUEST['code']) ){
			
			$error_msg =  __('Something went wrong',GEODIRSOCIALIMPORT_TEXTDOMAIN);
			$gdfi_config = get_option( 'gdfi_config' );
			$code=$_REQUEST['code'];
			$response =  wp_remote_get( "https://graph.facebook.com/oauth/access_token?client_id=".$gdfi_config['app_id']."&redirect_uri=".urlencode(admin_url()."admin.php?page=geodirectory&tab=facebook_integration")."&client_secret=".$gdfi_config['app_secret']."&code=$code");
			
			if(!empty($response['response']['code']) && $response['response']['code']==200){

				$parts = $response['body'];
				$parts_arr = explode('=',$parts);
				if($parts_arr[0]!='access_token'){echo $error_msg;exit;}
				else{
				$gdfi_config_new = $gdfi_config;
				$gdfi_config_new['access_token'] = $parts_arr[1];
				$gdfi_config_new['access_token_expire'] = time()+$parts_arr[2];
				
				update_option('gdfi_config', $gdfi_config_new);	
				?><script>window.close();</script><?php
				}
				
				
			}else{echo $error_msg;exit;}
exit;

				
				
		}
}
 
 


function gdfi_fb_at_debug(){
	global $wpdb;
	$gdfi_config = get_option( 'gdfi_config' );	
	$url = 'https://graph.facebook.com/debug_token?input_token='.$gdfi_config['access_token'].'&access_token='.$gdfi_config['access_token'];//.$gdfi_config['access_token'];
	$result = wp_remote_get($url);
	print_r($result);
}

function gdfi_fb_post($msg='',$link=''){
	 global $wpdb;
	$gdfi_config = get_option( 'gdfi_config' );	
	$page_at = gdfi_get_fb_page_accesstoken();
	//print_r($gdfi_config);exit;
	if(!isset($gdfi_config['app_page_post']) || $gdfi_config['app_page_post']==''){return;}// if not fb page to post to bail.
	if(!isset($page_at) || $page_at==''){return;}// if not fb page access token bail.
	
	$url = 'https://graph.facebook.com/'.$gdfi_config['app_page_post'].'/feed';

	
	$args =  array(); 
	
	$args['body']['access_token'] = $page_at;
	$args['body']['message'] = $msg;
	$args['body']['link'] = $link;
	$args['timeout'] = 30;
	
	//print_r($args);exit;
	$result = wp_remote_post($url,$args);
	if ( is_wp_error( $result ) ) {
		return false;
	}else if($result['response']['code']=='200'){return true;}
	

}


function gdfi_get_videos($video_url){

$result = wp_remote_get($video_url);

if(!empty($result['response']['code']) && $result['response']['code']==200){
$result_arr = json_decode($result['body']);
$videos = '';
foreach($result_arr->data as $video){
	foreach($video->format as $vid){
		if($vid->filter=="480x480"){$videos .= $vid->embed_html;}
	}
	
}
return $videos;
//print_r($result_arr);echo 'xxxxx';
}

}

function gdfi_get_images($image_url){

$result = wp_remote_get($image_url);

if(!empty($result['response']['code']) && $result['response']['code']==200){
$result_arr = json_decode($result['body']);
//print_r($result_arr);return;
$images = '';
foreach($result_arr->data as $image){
	if (strpos($image->source,'?') !== false) {$images[] = $image->source;}
	else{$images[] = $image->source;}
	
}

if($images){return  implode(",",$images);}
//print_r($result_arr);echo 'xxxxx';
}

}

function gdfi_get_fb_pages($at=''){
	global $wpdb;
	$gdfi_config = get_option( 'gdfi_config' );	
	if(!isset($gdfi_config['access_token'])){return;}
	$url = 'https://graph.facebook.com/me/accounts?access_token='.$gdfi_config['access_token'];
	
	$result = wp_remote_get($url);
	$result_arr = json_decode($result['body']);
	
	
	$result_page_arr ='';
	if(!empty($result_arr)){
		//print_r($result_arr);exit;
		foreach($result_arr->data as $fpage){
	
			if($at==$fpage->id){$selected = "selected='selected'";}else{$selected ='';}
			$result_page_arr .= '<option '.$selected.' value="'.$fpage->id.'">'.$fpage->name.'</option>';
		}

	return $result_page_arr;	
	}
}

function gdfi_get_fb_page_accesstoken($at=''){
	global $wpdb;
	$gdfi_config = get_option( 'gdfi_config' );	
	if(!isset($gdfi_config['access_token'])){return;}
	if(!isset($gdfi_config['app_page_post'])){return;}
	$url = 'https://graph.facebook.com/me/accounts?access_token='.$gdfi_config['access_token'];
	
	$result = wp_remote_get($url);
	$result_arr = json_decode($result['body']);
	
	
	$result_page_arr ='';
	if(!empty($result_arr)){
		//print_r($result_arr);exit;
		foreach($result_arr->data as $fpage){
	if($gdfi_config['app_page_post']==$fpage->id){return $fpage->access_token;}
		}

	return '';	
	}
}

function gdfi_get_fb_owner($page_id){
	global $wpdb;
	$gdfi_config = get_option( 'gdfi_config' );	
	$url = 'https://graph.facebook.com/' . $page_id . '?metadata=1&access_token='.$gdfi_config['access_token'];
	
	$result = wp_remote_get($url);
	
	$result_arr = json_decode($result['body']);
	if($result_arr){
	if(!empty($result_arr->location)){unset($result_arr->location);}	
	if(!empty($result_arr->name)){unset($result_arr->name);}	
	if(!empty($result_arr->description)){unset($result_arr->description);}	
	return $result_arr;	
	}
}
function gdfi_get_fb_meta($page_id){
	global $wpdb;
	$gdfi_config = get_option( 'gdfi_config' );	
	$url = 'https://graph.facebook.com/' . $page_id . '?metadata=1&access_token='.$gdfi_config['access_token'];
	
	$result = wp_remote_get($url);
	
	//print_r($result);
	if(!empty($result['response']['code']) && $result['response']['code']==200){
		$result_arr = json_decode($result['body']);
		if(isset($result_arr->metadata->connections->videos) && $result_arr->metadata->connections->videos){
			$videos = gdfi_get_videos($result_arr->metadata->connections->videos);
			if($videos){$result_arr->videos=$videos;$result['body']=json_encode($result_arr);}
			}
			
		if(isset($result_arr->metadata->connections->photos) && $result_arr->metadata->connections->photos){
			$photos = gdfi_get_images($result_arr->metadata->connections->photos);
			
			if($photos){$result_arr->photos=$photos;$result['body']=json_encode($result_arr);}
			}
			
		if(isset($result_arr->owner->id) && $result_arr->owner->id){
			$owner = gdfi_get_fb_owner($result_arr->owner->id);
			if($owner){
				$result_arr = (object) array_merge((array) $result_arr, (array) $owner);
				$result['body']=json_encode($result_arr);}
			}
			
		if(isset($result_arr->start_time) && $result_arr->start_time){ 
			$date = $result_arr->start_time; 
			$result_arr->event_start_date = date('m/d/Y', strtotime($date));
			$result_arr->event_start_time = date('H:i', strtotime($date));
			$result['body']=json_encode($result_arr);
			}
			
		if(isset($result_arr->end_time) && $result_arr->end_time){ 
			$date = $result_arr->end_time; 
			$result_arr->event_end_date = date('m/d/Y', strtotime($date));
			$result_arr->event_end_time = date('H:i', strtotime($date));
			$result['body']=json_encode($result_arr);
			}
		
		return $result['body'];
	}else{//print_r($result);
	return 	__('Something went wrong[111]',GEODIRSOCIALIMPORT_TEXTDOMAIN); 
	}
	
}

function gdfi_get_import_page_id($url){
	
	if (strpos($url,'facebook.com/') !== false) {
   			if (strpos($url,'?') !== false) {$temp_url = explode('?',$url);$url = $temp_url[0];}
   			if (strpos($url,'facebook.com/') !== false) {$temp_url = explode('facebook.com/',$url);$url = $temp_url[1];}
   			if (strpos($url,'groups/') !== false) {$temp_url = explode('groups/',$url);$url = $temp_url[1];}
   			if (strpos($url,'pages/') !== false) {$temp_url = explode('pages/',$url);$url = $temp_url[1];}
   			if (strpos($url,'events/') !== false) {$temp_url = explode('events/',$url);$url = $temp_url[1];}
   			//if (strpos($url,'/') !== false) {$temp_url = explode('/',$url);$url = $temp_url[0];}
			if (strpos($url,'/') !== false) {
				$temp_url = explode('/',$url);
				if(is_numeric($temp_url[1]) && strlen($temp_url[1])>5){$url = $temp_url[1];}else{$url = $temp_url[0];}
			
			}
			echo gdfi_get_fb_meta($url);
	}
	elseif (strpos($url,'www.yelp.') !== false) {
   			if (strpos($url,'?') !== false) {$temp_url = explode('?',$url);$url = $temp_url[0];}
   			if (strpos($url,'/biz/') !== false) {$temp_url = explode('/biz/',$url);$url = $temp_url[1];}
   			//if (strpos($url,'/events/') !== false) {$temp_url = explode('/events/',$url);$url = $temp_url[1];}
   			if (strpos($url,'#') !== false) {$temp_url = explode('#',$url);$url = $temp_url[0];}
			//echo $url;
			echo gdfi_yelp_get($url);
	}
	//return $url;
	
}

function gd_add_listing_bottom_code() {if(isset($_REQUEST['pid']) && $_REQUEST['pid']){return;}// if editing a listing then don't show ?>
  <script type="text/javascript">
 gdfi_codeaddress = false;
 gdfi_city = '';
 gdfi_street = '';
 gdfi_zip= '';
  // Here is a VERY basic generic trigger method
function gdfi_triggerEvent(el, type)
{
    if ((el[type] || false) && typeof el[type] == 'function')
    {
        el[type](el);
    }
}

  jQuery(document).ready(function(){
   jQuery( "#gd_facebook_import" ).click(function() {

		var gdfi_url = jQuery('#gdfi_import_url').val();
		if(!gdfi_url){alert('<?php _e('Please enter a value',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?>');return false;}
		jQuery.ajax({
		type:"POST",
		url: "<?php echo admin_url().'admin-ajax.php';?>",
		data: {action:'gdfi_get_fb_page_data',gdfi_url:gdfi_url},
		beforeSend: function () {
			jQuery('#modal-loading').css("visibility", "visible");
        },
		success:function(data){
			console.log(data);
			try
       {
               data = jQuery.parseJSON(data);
       }
       catch(err)
       {
               alert(data);return
       } 
			//if(data = jQuery.parseJSON(data)){}else{alert(data);return}
			
			console.log(data);
			jQuery('#modal-loading').css("visibility", "hidden");
			
			var tags ='';
			if(data.name){tags = data.name;}
			if(data.is_yelp){ // fix things for yelp
				
				if(!data.description && data.snippet_text){data.description = data.snippet_text;}
				if(data.categories){
					jQuery.each(data.categories, function (index, value) {
					if(tags==''){tags = value[0];}else{tags = tags+','+value[0];}
					});
				}
				
				if(data.location.address[0]){data.location.street = data.location.address[0];}
				if(data.location.postal_code){data.location.zip = data.location.postal_code;}
				if(data.display_phone){data.phone = data.display_phone;}
				if(data.image_url){data.photos = data.image_url;}
							
				if(data.deals && jQuery('#geodir_special_offers').length){
					deals_txt = '';
					u_open = '';
					u_close = '';
					if(data.deals[0].url){u_open = "<a href='"+data.deals[0].url+"' target='_blank' >";u_close = "</a>";}
					if(data.deals[0].title){deals_txt = u_open+"<h2>"+data.deals[0].title+"</h2>"+u_close;}
					if(data.deals[0].what_you_get){deals_txt = deals_txt+data.deals[0].what_you_get;}
					if(data.deals[0].image_url){deals_txt = deals_txt+"<img s"+"rc='"+data.deals[0].image_url+"' />";}
					jQuery('#geodir_special_offers').val(deals_txt);}
					if(data.url && jQuery('#geodir_website').length){data.website=data.url;}

			}
			
			// Standard facebook
			
			//enforce desc maxlength
			if(data.description && jQuery('#post_desc').attr('maxlength')){
				descMax = jQuery('#post_desc').attr('maxlength');
				if (data.description.length > descMax) {  
                data.description = data.description.substring(0, descMax);  
            	} 
			}
			
			
			
			
			
			if(data.name && jQuery('#post_title').length){jQuery('#post_title').val(data.name);jQuery("#post_title").change();}
			if(data.description && jQuery('#post_desc').length){jQuery('#post_desc').val(data.description);jQuery('#post_desc').change();}
			if(typeof(tinyMCE) != "undefined"){if(data.description && tinyMCE.get('post_desc')){tinyMCE.get('post_desc').setContent(data.description);jQuery('#post_desc').change();}}
			if(!data.description && data.about && jQuery('#post_desc').length){jQuery('#post_desc').val(data.about);}
			if(data.category_list && jQuery('#post_tags').length){jQuery.each(data.category_list, function (index, value) {
        	if(tags==''){tags = value.name;}else{tags = tags+','+value.name;}
    		});}
			else if(data.category && jQuery('#post_tags').length){if(tags==''){tags = data.category}else{tags = tags+','+data.category;}}
			
			
			
			// hack for events location
			if(data.venue){data.location = data.venue;}
			
			if(data.location && data.location.street && jQuery('#post_address').length){jQuery('#post_address').val(data.location.street);}
			
			if(data.location && data.location.latitude && data.location.longitude){
			latlon = new google.maps.LatLng(data.location.latitude,data.location.longitude);
			jQuery.goMap.map.setCenter(latlon);
			updateMarkerPosition(latlon);
			centerMarker();
			if(!data.location.street){google.maps.event.trigger(baseMarker, 'dragend');}// geocode address only if no street name present

			}

			if(data.location && data.location.country && jQuery('#post_country').length){jQuery('#post_country').val(data.location.country);/*jQuery("#post_country").trigger('change');*/jQuery("#post_country").trigger("chosen:updated");}
			if(data.location && data.location.city && jQuery('#post_city').length){jQuery('#post_city').val(data.location.city);jQuery("#post_city").trigger("chosen:updated");}// no region is provided so this is useless but we will keep it for future implementation
			if(data.location && data.location.zip && jQuery('#post_zip').length){jQuery('#post_zip').val(data.location.zip);}
			
			
			if(data.location && data.location.country && data.location.city && data.location.zip && data.location.street){
			 gdfi_codeaddress = true;
			 gdfi_city = data.location.city;
			 gdfi_street = data.location.street;
			 gdfi_zip= data.location.zip;
			 codeAddress(true);
			 setTimeout(function(){ google.maps.event.trigger(baseMarker, 'dragend');}, 600);
			
			 }
			
			if(data.phone && jQuery('#geodir_contact').length){jQuery('#geodir_contact').val(data.phone);}
			if(data.email && jQuery('#geodir_email').length){jQuery('#geodir_email').val(data.email);}
			if(data.website && jQuery('#geodir_website').length){jQuery('#geodir_website').val(data.website);}
			if(data.twitter && jQuery('#geodir_twitter').length){jQuery('#geodir_twitter').val(data.twitter);}
			if(data.link && jQuery('#geodir_facebook').length){jQuery('#geodir_facebook').val(data.link);}
			if(data.videos && jQuery('#geodir_video').length){jQuery('#geodir_video').val(data.videos);}
			
			if(data.photos && jQuery('#post_images').length && jQuery('#post_imagesimage_limit').length && jQuery('#post_imagesimage_limit').val()!=''){
			var iLimit = 	jQuery('#post_imagesimage_limit').val();
			var iArray = data.photos.split(",");
				if(iArray.length>iLimit){
					iArray = iArray.slice(0, iLimit);
					data.photos = iArray.join();
				}
			}
			if(data.photos && jQuery('#post_images').length){jQuery('#post_images').val(data.photos);plu_show_thumbs('post_images');}

			
			
			// facebook Events
			if(data.owner && data.owner.category_list && jQuery('#post_tags').length){jQuery.each(data.owner.category_list, function (index, value) {
        	if(tags==''){tags = value.name;}else{tags = tags+','+value.name;}
    		});}
			else if(data.owner && data.owner.category && jQuery('#post_tags').length){if(tags==''){tags = data.owner.category}else{tags = tags+','+data.owner.category;}}
			
			
			
			
			
			if(data.event_start_date && jQuery('#dates').length){jQuery('#dates').val(data.event_start_date);
			cal.select(data.event_start_date);
			 var selectedDates = cal.getSelectedDates();
                if (selectedDates.length > 0) {
                    var firstDate = selectedDates[0];
                    cal.cfg.setProperty("pagedate", (firstDate.getMonth()+1) + "/" + firstDate.getFullYear());
                    cal.render();
                }
			}
			
			if(data.event_start_time && jQuery('#starttime').length){jQuery('#starttime').val(data.event_start_time);jQuery("#starttime").trigger("chosen:updated");}
			if(data.event_end_time && jQuery('#starttime').length){jQuery('#endtime').val(data.event_end_time);jQuery("#endtime").trigger("chosen:updated");}

			
			
			
			
			
			
			
		// populate tags last
		if(tags && jQuery('#post_tags').length){jQuery('#post_tags').val(tags);}
		
		//enforce tags maxlength
			if(jQuery('#post_tags').length && jQuery('#post_tags').attr('maxlength')){
				tagsMax = jQuery('#post_tags').attr('maxlength');
				if (jQuery('#post_tags').val().length > tagsMax) {  
                jQuery('#post_tags').val(jQuery('#post_tags').val().substring(0, tagsMax));
            	} 
			}
			
		
		}
		});
		
		return false;

    });
   });
   </script>
   <?php
	
}
function gd_add_listing_top_code() {
	 if(isset($_REQUEST['pid']) && $_REQUEST['pid']){return;}// if editing a listing then don't show
   ?>
   <h5><?php _e('Import Details from Social',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></h5>
   <input type="text" placeholder="<?php _e('Enter facebook page/event url or Yelp url',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?>"  id="gdfi_import_url" />
   <span id="gd_facebook_import" style="margin-top:0px;" class="geodir_button" ><?php _e('Import Details',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
   <div id="modal-loading" style="margin:0px;display:inline-block;visibility:hidden;" ><i class="fa fa-refresh fa-spin"></i></div>
   
   <?php
}


add_action( 'geodir_before_detail_fields', 'gd_add_listing_top_code',3 );
add_action( 'geodir_after_main_form_fields', 'gd_add_listing_bottom_code',10 );




function gdfi_post_to_facebook( $new_status, $old_status, $post ) {
    if ( $old_status != 'publish' && $new_status == 'publish' ) {
		if(get_post_meta( $post->ID, 'gdfi_posted_facebook', true )){return;}// if posted to facebook don't post again.
       // print_r($post);exit;
		   if($post->post_type=='post' || substr( $post->post_type, 0, 3 ) === "gd_"){
			global $wpdb;
			$to_post = array();
			$to_post = get_option( 'gdfi_post_to_facebook');
			$to_post[$post->ID] = array('ID'=>$post->ID,'title'=>$post->post_title,'link'=>get_permalink( $post->ID ));
			update_option( 'gdfi_post_to_facebook', $to_post );
			
			//gdfi_fb_post($post->post_title,get_permalink( $post->ID )); // post to facebook
			update_post_meta($post->ID, 'gdfi_posted_facebook', '1'); // mark it as posted to facebook
			//exit;
		   }
    }
}
add_action( 'transition_post_status', 'gdfi_post_to_facebook', 5000, 3 );

function gdfi_check_post_fb(){
	global $wpdb;
	if($posts = get_option( 'gdfi_post_to_facebook')){
	//print_r($posts);//exit;
		foreach($posts as $p){
		gdfi_fb_post($p['title'],$p['link']); // post to facebook	
		}
	delete_option( 'gdfi_post_to_facebook');
	}
	
}
if(isset($_REQUEST['pid'])){
add_action( 'wp_footer', 'gdfi_check_post_fb', 5000 );
}

function gdfi_get_fb_page_data(){
//do something
gdfi_get_import_page_id($_POST['gdfi_url']);
die();
}
add_action('wp_ajax_gdfi_get_fb_page_data', 'gdfi_get_fb_page_data');



function gdfi_add_listing_codeaddress_js_vars_change(){?>
if(gdfi_codeaddress==true){
city = gdfi_city;
region = '';
zip = gdfi_zip;
address = gdfi_street;
address = '';
address = address + ',' + zip + ',' + city + ',' + country; 
}
<?php
}

add_action( 'geodir_add_listing_codeaddress_js_vars', 'gdfi_add_listing_codeaddress_js_vars_change',10 );


function gdfi_add_listing_geocode_js_vars_change(){
if(!is_admin()){?>
if(gdfi_codeaddress==true){
getAddress = gdfi_street;
gdfi_codeaddress=false;
}
<?php }
}

add_action( 'geodir_add_listing_geocode_js_vars', 'gdfi_add_listing_geocode_js_vars_change',10 );

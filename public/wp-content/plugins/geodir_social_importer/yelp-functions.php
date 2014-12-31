<?php

//
// From http://non-diligent.com/articles/yelp-apiv2-php-example/
//


// Enter the path that the oauth library is in relation to the php file
require_once ('inc/yelp-oauth.php');


function gdfi_yelp_example(){
// For example, request business with id 'the-waterboy-sacramento'
$unsigned_url = "http://api.yelp.com/v2/business/the-waterboy-sacramento";

// For examaple, search for 'tacos' in 'sf'
//$unsigned_url = "http://api.yelp.com/v2/search?term=tacos&location=sf";

global $wpdb;
$gdfi_config_yelp = get_option( 'gdfi_config_yelp' );
// Set your keys here
$consumer_key = $gdfi_config_yelp['key'];
$consumer_secret = $gdfi_config_yelp['key_secret'];
$token = $gdfi_config_yelp['token'];
$token_secret = $gdfi_config_yelp['token_secret'];

// Token object built using the OAuth library
$token = new OAuthToken($token, $token_secret);

// Consumer object built using the OAuth library
$consumer = new OAuthConsumer($consumer_key, $consumer_secret);

// Yelp uses HMAC SHA1 encoding
$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

// Build OAuth Request using the OAuth PHP library. Uses the consumer and token object created above.
$oauthrequest = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $unsigned_url);

// Sign the request
$oauthrequest->sign_request($signature_method, $consumer, $token);

// Get the signed URL
$signed_url = $oauthrequest->to_url();

// Send Yelp API Call
$ch = curl_init($signed_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);
$data = curl_exec($ch); // Yelp response
curl_close($ch);

// Handle Yelp response data
$response = json_decode($data);

// Print it for debugging
print_r($response);
}


function gdfi_yelp_get($page_id){
// For example, request business with id 'the-waterboy-sacramento'
$aip_url = "http://api.yelp.com/v2/business/";
$unsigned_url = $aip_url.$page_id;

// For examaple, search for 'tacos' in 'sf'
//$unsigned_url = "http://api.yelp.com/v2/search?term=tacos&location=sf";

global $wpdb;
$gdfi_config_yelp = get_option( 'gdfi_config_yelp' );
// Set your keys here
$consumer_key = $gdfi_config_yelp['key'];
$consumer_secret = $gdfi_config_yelp['key_secret'];
$token = $gdfi_config_yelp['token'];
$token_secret = $gdfi_config_yelp['token_secret'];

// Token object built using the OAuth library
$token = new OAuthToken($token, $token_secret);

// Consumer object built using the OAuth library
$consumer = new OAuthConsumer($consumer_key, $consumer_secret);

// Yelp uses HMAC SHA1 encoding
$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

// Build OAuth Request using the OAuth PHP library. Uses the consumer and token object created above.
$oauthrequest = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $unsigned_url);

// Sign the request
$oauthrequest->sign_request($signature_method, $consumer, $token);

// Get the signed URL
$signed_url = $oauthrequest->to_url();

// Send Yelp API Call
$ch = curl_init($signed_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);
$data = curl_exec($ch); // Yelp response
curl_close($ch);

// Handle Yelp response data
$response = json_decode($data);
//print_r($response);
$response->is_yelp = true;
$country_name = $wpdb->get_var($wpdb->prepare("SELECT Country FROM ".GEODIR_COUNTRIES_TABLE." WHERE ISO2=%s",array($response->location->country_code)));
$response->location->country = $country_name;
$response->image_url = str_replace("ms.jpg","l.jpg",$response->image_url);
// Print it for debugging
return json_encode($response);
}



 function gdfi_yelp_integration_setting_fields(){
	global $wpdb;
	
	?>
	
	<div class="inner_content_tab_main">
		<div class="gd-content-heading active">
			<h3><?php _e('Enter your Yelp API settings',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?> <a href="http://www.yelp.co.uk/developers/manage_api_keys"><?php _e('Find them here',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></a></h3>
            <?php
			//gdfi_yelp_example();
			
			?>

			
			<table class="form-table">
<?php
$gdfi_config_yelp = get_option( 'gdfi_config_yelp' );
//print_r($gdfi_config_yelp);
?>
            		<tbody>
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Consumer Key',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gdfi_yelp_key" id="gdfi_yelp_key" type="text" style=" min-width:300px;" value="<?php if(!empty($gdfi_config_yelp['key'])){echo $gdfi_config_yelp['key'];}?>"> 
                    <span class="description"><?php _e('Enter your Yelp Consumer Key',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
					
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Consumer Secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gdfi_yelp_key_secret" id="gdfi_yelp_key_secret" type="password" style=" min-width:300px;" value="<?php if(!empty($gdfi_config_yelp['key_secret'])){echo $gdfi_config_yelp['key_secret'];}?>"> 
                    <span class="description"><?php _e('Enter your Yelp Consumer Secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
                    
                    
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Token',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gdfi_yelp_token" id="gdfi_yelp_token" type="text" style=" min-width:300px;" value="<?php if(!empty($gdfi_config_yelp['token'])){echo $gdfi_config_yelp['token'];}?>"> 
                    <span class="description"><?php _e('Enter your Yelp Token',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
					
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Token Secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <input name="gdfi_yelp_token_secret" id="gdfi_yelp_token_secret" type="password" style=" min-width:300px;" value="<?php if(!empty($gdfi_config_yelp['token_secret'])){echo $gdfi_config_yelp['token_secret'];}?>"> 
                    <span class="description"><?php _e('Enter your Yelp Token Secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
                    
                    
                    
                    
                    
                    <?php if(!empty($gdfi_config['app_id']) && !empty($gdfi_config['app_secret'])){?>
					<script type="text/javascript">
					win = '';
					function gdfi_auth_popup(){
						win = window.open("https://www.facebook.com/dialog/oauth?client_id=<?php echo $gdfi_config['app_id'];?>&redirect_uri=<?php echo urlencode(admin_url()."admin.php?page=geodirectory&tab=facebook_integration");?>&scope=email,create_event,publish_actions,rsvp_event", "gdfi_auth", "scrollbars=no,menubar=no,height=400,width=600,resizable=yes,toolbar=no,status=no");
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
						
					<?php }
					if(!empty($gdfi_config['access_token'])){
					?>
                    
                    <tr valign="top">
					<th scope="row" class="titledesc"><?php _e('Facebook Access Token',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></th>
					<td class="forminp">
                    <?php _e('Active, expires: ',GEODIRSOCIALIMPORT_TEXTDOMAIN); echo date('F j, Y, g:i a', $gdfi_config['access_token_expire']);?>
                    <span class="description"><?php _e('Enter your Facebook app secret',GEODIRSOCIALIMPORT_TEXTDOMAIN); ?></span>
                    </td>
					</tr>
                    
					<?php }?>						
	</tbody></table>
	
	<p class="submit" style="margin-top:10px;">
<input name="gdfi_yelp_integration_options_save" class="button-primary" type="submit" value="<?php _e( 'Save changes',GEODIRSOCIALIMPORT_TEXTDOMAIN ); ?>" />
<input type="hidden" name="subtab" id="last_tab" value="<?php echo $_REQUEST['subtab'];?>" />
</p>

		</div>
	</div>
	
	<?php

}

function gdfi_yelp_integration_from_submit_handler(){
		global $wpdb;
	if(isset($_REQUEST['gdfi_yelp_integration_options_save'])){
			
			//echo "<pre>"; print_r($_REQUEST);
			
			$gdfi_config_yelp = get_option( 'gdfi_config_yelp' );
			if(!$gdfi_config_yelp){
			$gdfi_config_new = array('key'=>$_REQUEST['gdfi_yelp_key'],'key_secret'=>$_REQUEST['gdfi_yelp_key_secret'],'token' => $_REQUEST['gdfi_yelp_token'],'token_secret' => $_REQUEST['gdfi_yelp_token_secret']);	
			}else{$gdfi_config_new = $gdfi_config_yelp;}
			
			if(!empty($_REQUEST['gdfi_yelp_key'])){$gdfi_config_new['key']=$_REQUEST['gdfi_yelp_key'];}
			if(!empty($_REQUEST['gdfi_yelp_key_secret'])){$gdfi_config_new['key_secret']=$_REQUEST['gdfi_yelp_key_secret'];}
			if(!empty($_REQUEST['gdfi_yelp_token'])){$gdfi_config_new['token']=$_REQUEST['gdfi_yelp_token'];}
			if(!empty($_REQUEST['gdfi_yelp_token_secret'])){$gdfi_config_new['token_secret']=$_REQUEST['gdfi_yelp_token_secret'];}
			update_option('gdfi_config_yelp', $gdfi_config_new);
			
			$msg = 'Your settings have been saved.';
		
			$msg = urlencode($msg);
			
			//$location = admin_url()."admin.php?page=geodirectory&tab=facebook_integration&adl_success=".$msg;
			//wp_redirect($location);
			//exit;
			
		}

}
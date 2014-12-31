<?php 
/*
Plugin Name: Stripe Payment GeoDirectory Add-on
Plugin URI: http://wpgeodirectory.com
Description: Adds Stripe as a payment on GeoDirectory Payment Manager plugin.
Version: 1.5.0
Author: GeoDirectory, Daniele Biggiogero
Author URI: http://wpgeodirectory.com
*/

if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END


/**
 * Localisation
 **/
if (!defined('GEODIRSTRIPE_TEXTDOMAIN')) define('GEODIRSTRIPE_TEXTDOMAIN', 'geodirstripe');	
$locale = apply_filters('plugin_locale', get_locale(), GEODIRSTRIPE_TEXTDOMAIN);
load_textdomain(GEODIRSTRIPE_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRSTRIPE_TEXTDOMAIN.'/'.GEODIRSTRIPE_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRSTRIPE_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/gdstripe-languages');

require_once 'geodir_stripe_utilities.php';
require_once('lib/Stripe.php');

if ( is_admin() ) :

	register_activation_hook( __FILE__, 'geodir_stripe_activation' ); 
	register_deactivation_hook( __FILE__, 'geodir_stripe_uninstall' );
	register_uninstall_hook(__FILE__,'geodir_stripe_uninstall');
	
endif;

function geodir_stripe_activation() {

	$stripeCheck = get_option('payment_method_stripe');
	
	if ( false === $stripeCheck ) {
		global $wpdb;
		
		$order = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s ORDER BY option_id", array('payment_method_%') ) );
		
		$payOpts = array();
		
		$payOpts[] = array(
			'field_type' 	=> 'text',
			'title' 		=> 'Secret Key',
			'fieldname' 	=> 'secretkey',
			'value' 		=> 'sk_test_BQokikJOvBiI2HlWgH4olfQ2',
			'description' 	=> 'Example : sk_test_BQokikJOvBiI2HlWgH4olfQ2'
		);
		
		$payOpts[] = array(
			'field_type' 	=> 'text',
			'title' 		=> 'Publishable Key',
			'fieldname' 	=> 'publishablekey',
			'value' 		=> 'pk_test_6pRNASCoBOKtIshFeQd4XMUh',
			'description' 	=> 'Example : pk_test_6pRNASCoBOKtIshFeQd4XMUh'
		);
		
		$payOpts[] = array(
			'field_type' 	=> 'text',
			'title' 		=> 'Webhooks URL',
			'fieldname' 	=> 'webhooksurl',
			'value' 		=> home_url()."/?pay_action=ipn&pmethod=stripe",
			'description' 	=> 'You must set this in your stripe account to match the value above, in stripe see Your Account>AccountSettings>Webhooks'
		);
		
		$paymenthodinfo = array(
			'name' 			 => 'Stripe',
			'key' 			 => 'stripe',
			'isactive' 		 => 1,
			'display_order'  => ( (integer) count($order) + 1 ),
			'payment_mode' 	 => 'live',
			'payOpts' 		 => apply_filters('geodir_payment_stripe_options' ,$payOpts),
		);
		
		$install = add_option( 'payment_method_stripe' , $paymenthodinfo );
	}
}

// deactivation & uninstall are the same. It's just deleting the stripe payment option.
function geodir_stripe_uninstall() {
	$uninstall = delete_option( 'payment_method_stripe' );
}

add_action( 'wp_enqueue_scripts', 'geodir_stripe_enqueue_scripts' );
function geodir_stripe_enqueue_scripts() {
	
	$payData = get_payment_options( 'stripe' );
	$page = get_page_by_path( 'listing-preview' );
		
	wp_register_script( 'stripe-checkout', esc_url_raw( 'https://checkout.stripe.com/checkout.js' ), array(), '2.0', true );// fails in IE unless u use https uising just // fails in IE
	wp_register_script( 'geodir-stripe-script', plugins_url( 'geodir_stripe.js', __FILE__ ), array( 'jquery' , 'stripe-checkout' ), '1.0', true );
	
	$data = array(
		'ajaxurl'   	 => admin_url( 'admin-ajax.php' ),
		'nonce'			 => wp_create_nonce( 'geodir-stripe-ajax-nonce' ),
		'publishablekey' => $payData['publishablekey'],
		'currency'		 => geodir_get_currency_type()
	);
	wp_localize_script( 'geodir-stripe-script', 'sData', $data );
	if( is_page( $page->ID ) ) {
		wp_enqueue_script( 'stripe-checkout' );
		wp_enqueue_script( 'geodir-stripe-script' );	
	}
}

add_action('geodir_payment_form_handler_stripe' , 'geodir_payment_form_stripe');
function geodir_payment_form_stripe( $invoice_id ) {
	global $wpdb;
	$payData 	  		= get_payment_options('stripe');
	$invoice_info 		= geodir_get_invoice( $invoice_id );
	
	$payable_amount 	= $invoice_info->paied_amount;
	$last_postid 		= $invoice_info->post_id;
	$post_author		= $invoice_info->post_author;
	$post_title		 	= $invoice_info->post_title;
	$package_id 		= $invoice_info->package_id;
	$coupon_code		= $invoice_info->coupon_code;
	
	$listing_price_info = geodir_get_post_package_info($package_id ,$last_postid);
	
	$token 		  		= $_POST['stripe_token'];
	$receipt_mail		= $_POST['stripe_email'];
	$redirect_url_success = geodir_getlink(home_url(),array('pay_action'=>'success', 'pmethod' => 'stripe', 'pid' => $last_postid ),false);
	$go_charge			= false;
	
//	Initialize the Stripe API
	Stripe::setApiKey( $payData['secretkey'] );
	
//	Check if the package is a subscription
	if( !empty( $listing_price_info['sub_active'] ) ) {
		
		
		if($coupon_code){
			
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".COUPON_TABLE." WHERE coupon_code = %s",array($coupon_code)));
						
			if(!empty($coupon)){
				
				try {
					$actual_coupon_code = Stripe_Coupon::retrieve($coupon_code);
				}catch (Stripe_Error $e) {
					
					if($coupon->discount_type=='per'){$dtype = 'percent_off';$discount_amount=$coupon->discount_amount;}else{$dtype = 'amount_off';$discount_amount=$coupon->discount_amount*100;}
					if($coupon->recurring=='1'){$duration = 'once';}else{$duration = 'forever';}
					
					
					try {
						$actual_coupon_code = Stripe_Coupon::create(array(
						  $dtype => $discount_amount,
						  "duration" => $duration,
						  "currency" => geodir_get_currency_type(),
						  "id" => $coupon->coupon_code)
						);
					} catch (Stripe_Error $e) {
						
						log_write( print_r( $e, true ) );
					}
					
				}
				
				
			}
		
		}
		//print_r($actual_coupon_code);echo '###';exit;
		
		
		$sub_active = $listing_price_info['sub_active'];
		$sub_units_num = $listing_price_info['sub_units_num'];
		$sub_units = $listing_price_info['sub_units'];
		$sub_num_trial_days = $listing_price_info['sub_num_trial_days'];
		
		if($sub_num_trial_days==''){$sub_num_trial_days = 0;}
		
		$sub_units_num_times = $listing_price_info['sub_units_num_times'];
		$planID = "geodir_sub_$package_id";

		try {
			$plan = Stripe_Plan::retrieve( $planID );
		} catch (Stripe_Error $e) {
			if($sub_units=='D'){ $int = "day"; }
			if($sub_units=='W'){ $int = "week"; }
			if($sub_units=='M'){ $int = "month"; }
			if($sub_units=='Y'){ $int = "year"; }
			
			$planArray = array(
				"amount" 			=> (int)(  $invoice_info->amount * 100 ),
				"currency" 			=> geodir_get_currency_type(),
				"interval" 			=> $int,
				"name" 				=> $invoice_info->package_title,
				"id" 				=> $planID,
				"interval_count" 	=> $sub_units_num,
				"trial_period_days" => $sub_num_trial_days
			);
			
			//log_write( print_r( $planArray , true ) );
			try {
				$plan = Stripe_Plan::create( $planArray );
			} catch (Stripe_Error $e) {
				
				log_write( print_r( $e, true ) );
			}
			//log_write( print_r( $plan, true ) );
		}
			
		// now create a customer
		$customerArray = array( 'card' => $token );
		
		if(isset($actual_coupon_code) && $actual_coupon_code){
		$customerArray['coupon'] = 	$actual_coupon_code->id;
		}
			 
		try {
			$customer = Stripe_Customer::create( $customerArray );
			$subArray = array(
				'plan' 		=> $plan->id,
				'metadata' 	=> array( 
					"sub_units_num_times" => $sub_units_num_times,
					"post_id"		=> $last_postid
				 ));
			$customer->subscriptions->create( $subArray );
			
			$go_charge = true;
		} catch (Stripe_Error $e) {
			log_write( print_r( $e, true ) );
		}
		
	} else { // Simple charge for signle payments
		
		$chargeArray = array(
		  "amount" 			=> (int)(  $invoice_info->amount * 100 ), // amount in cents
		  "currency" 		=> geodir_get_currency_type(),
		  "card" 			=> $token,
		  "receipt_email" 	=> $receipt_mail,
		  "description" 	=> $invoice_info->package_title,
		  "metadata"		=> array(
		  		"post_id" 	=> $last_postid
		  ));
		
		try {
			$charge = Stripe_Charge::create( $chargeArray );
			
			$go_charge = true;
		} catch(Stripe_CardError $e) {
			log_write( print_r( $e, true ) );
		} 
	}
	
//		Card transaction has been successful 
	if( $go_charge !== false ) {
		wp_redirect( $redirect_url_success );
	} else {
//		The card has been declined
		$redirect_url = geodir_getlink(home_url(),array('pay_action'=>'cancel', 'pid' => $last_postid ),false);
		wp_redirect( $redirect_url );
	}
}

add_action('wp_ajax_geodir_stripe_get_payment_info', 'geodir_stripe_get_payment_info');
add_action('wp_ajax_nopriv_geodir_stripe_get_payment_info', 'geodir_stripe_get_payment_info');
function geodir_stripe_get_payment_info() {
	global $wpdb;
	if ( !wp_verify_nonce( $_REQUEST['_nonce'], 'geodir-stripe-ajax-nonce' ) ) { die( 'Not authorized!' ); }
	
	$output = geodir_get_post_package_info( $_REQUEST['price'] );
	//print_r($output);
	if(isset($_REQUEST['coupon_code']) &&  $_REQUEST['coupon_code'] && isset($_REQUEST['post_type'])){
	$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".COUPON_TABLE." WHERE coupon_code = %s",array($_REQUEST['coupon_code'])));	
	
		if(!empty($coupon) &&  geodir_is_valid_coupon($_REQUEST['post_type'],$_REQUEST['coupon_code'])){
			
			if($coupon->recurring=='1'){
			$paied_amount = geodir_get_payable_amount_with_coupon($output['amount'],$coupon->coupon_code);
			$output['amount']=$paied_amount;
			}else{
			$paied_amount = geodir_get_payable_amount_with_coupon($output['amount'],$coupon->coupon_code);
			$output['amount']=$paied_amount;
			}
			
		}
		
	//print_r($coupon);
	}
	
	$output = json_encode($output);
	if(is_array($output)){ print_r($output); }
		else{ echo $output; }
    die;
}

add_action('geodir_ipn_handler_stripe' , 'geodir_ipn_handler_stripe');
function geodir_ipn_handler_stripe() {

	global $wpdb;
	  
	//echo '###';exit;
	if($_GET['pay_action'] == 'ipn' && $_GET['pmethod'] == 'stripe' ) {
		
		$payData = get_payment_options('stripe');
		
		Stripe::setApiKey( $payData['secretkey'] );
	
		// Retrieve the request's body and parse it as JSON
		$input = @file_get_contents("php://input");
		$event_json = json_decode($input);
		
		$event_id = $event_json->id;
		$event_type = $event_json->type;
		
		//log_write( $event_type );
		//log_write( $input );
		
		// This will send receipts on succesful invoices
		try {
			$event 		= Stripe_Event::retrieve($event_id);
			$invoice 	= $event->data->object;
			
			$event_type = $event->type;
			
			//if($event_type=='customer.subscription.created' || $event_type=='charge.succeeded'){}else{return;}// if other notifications then bail
			
			$post_id 	= $invoice->metadata->post_id;
			//log_write( $event );
			//log_write( $post_id );
				log_write(print_r($invoice,true) );	
			$post_pkg 	= geodir_get_post_meta( $post_id, 'package_id', true ); // get the post price package ID
			$priceinfo	= geodir_get_post_package_info( $post_pkg ,$post_id );
			$payment_type = ( !empty( $priceinfo['sub_active'] ) ) ? 'subscription' : 'single payment' ;
			$post_default_status = geodir_new_post_default_status();
			if($post_default_status=='') { $post_default_status = 'publish'; }
			
			$currency_code = geodir_get_currency_type(); // get the actual curency code
			$pkg_price = $wpdb->get_var($wpdb->prepare("SELECT paied_amount FROM ".INVOICE_TABLE." WHERE post_id = %d AND is_current=%s", array($post_id,'1')));
			$paid_amount_with_currency = $pkg_price . $currency_code;
			$productinfosql = $wpdb->prepare( "SELECT ID,post_title,guid,post_author FROM $wpdb->posts WHERE ID = %d", array( $post_id ));
			
			$productinfo = $wpdb->get_results($productinfosql);
			foreach($productinfo as $productinfoObj) {
				$post_title = '<a href="'.get_permalink($post_id).'">'.$productinfoObj->post_title.'</a>'; 
				$aid = $productinfoObj->post_author;
				$userInfo = geodir_get_author_info($aid);
				$to_name = $userInfo->user_nicename;
				$to_email = $userInfo->user_email;
				$user_email = $userInfo->user_email;
			}
			
			
			
			$transaction_details='';
			if($event_type == 'customer.subscription.created' ){$transaction_details .= '####### '.__('THIS IS A SUBSCRIPTION SIGNUP AND IF A FREE TRIAL WAS OFFERD NO PAYMENT WILL BE RECIVED', GEODIRSTRIPE_TEXTDOMAIN).' ######\n';}
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= sprintf(__("Payment Details for Listing ID #%s", GEODIRSTRIPE_TEXTDOMAIN), $post_id ) ."<br />";
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= sprintf(__("Listing Title: %s", GEODIRSTRIPE_TEXTDOMAIN),$post_title)."<br />";
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= sprintf(__("Trans ID: %s", GEODIRSTRIPE_TEXTDOMAIN), $event->id)."<br />";
			$transaction_details .= sprintf(__("Status: %s", GEODIRSTRIPE_TEXTDOMAIN), $event->type)."<br />";
			$transaction_details .= sprintf(__("Amount: %s", GEODIRSTRIPE_TEXTDOMAIN), $paid_amount_with_currency )."<br />";
			$transaction_details .= sprintf(__("Type: %s", GEODIRSTRIPE_TEXTDOMAIN), $payment_type )."<br />";
			$transaction_details .= sprintf(__("Date: %s", GEODIRSTRIPE_TEXTDOMAIN), date( "Y-m-d H:i:s", time()) )."<br />";
			
			if($payment_type=='subscription'){
				
			/*$sub_units_num = $priceinfo['sub_units_num'];
			$sub_units = $priceinfo['sub_units'];
		
			if($sub_units=='D'){ $int = "day"; }
			if($sub_units=='W'){ $int = "week"; }
			if($sub_units=='M'){ $int = "month"; }
			if($sub_units=='Y'){ $int = "year"; }
			*/
			//$transaction_details .= sprintf(__("Subscription Start: %s", GEODIRSTRIPE_TEXTDOMAIN), date( "Y-m-d H:i:s", $invoice->start) )."<br />";
			//$transaction_details .= sprintf(__("Subscription End: %s", GEODIRSTRIPE_TEXTDOMAIN), date( "Y-m-d H:i:s", $invoice->current_period_end) )."<br />";
			}
			
			$transaction_details .= sprintf(__("Method: %s", GEODIRSTRIPE_TEXTDOMAIN), 'Stripe')."<br />";
			$transaction_details .= "--------------------------------------------------<br />";		
			$transaction_details .= __("Information Submitted URL", GEODIRSTRIPE_TEXTDOMAIN)."<br />";
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= "  $post_title<br />";
			
			if( $event_type == 'charge.succeeded' || $event_type == 'customer.subscription.created' ) { geodir_set_post_status( $post_id , $post_default_status ); }
			
			switch ( $event_type ) {
				case 'charge.succeeded':
					
					$pid_sql = $wpdb->prepare("UPDATE ".INVOICE_TABLE." SET status = 'Paid',html = %s WHERE post_id = %d AND is_current = 1", array($transaction_details,$post_id));
					$invoice_id = $wpdb->query($pid_sql);
					if(isset($invoice->metadata->post_id) && $invoice->metadata->post_id){
					geodir_payment_adminEmail($post_id,$aid,'payment_success',$transaction_details); /*email to admin*/
					geodir_payment_clientEmail($post_id,$aid,'payment_success',$transaction_details); /*email to client*/
					}
					break;
				case 'customer.subscription.created':
					$pid_sql = $wpdb->prepare("UPDATE ".INVOICE_TABLE." SET 
									`paymentmethod` = 'Stripe',
									status = 'Subscription-Payment',
									html = %s 
									WHERE post_id = %d AND is_current = 1",
									array($transaction_details,$post_id)
									);
						
					$invoice_id = $wpdb->query($pid_sql);
					if(isset($invoice->metadata->post_id) && $invoice->metadata->post_id){
					geodir_payment_adminEmail($post_id,$aid,'payment_success',$transaction_details); /*email to admin*/
					geodir_payment_clientEmail($post_id,$aid,'payment_success',$transaction_details); /*email to client*/
					}
					break;
				case 'customer.subscription.deleted':
					/* Set the subscription ac canceled*/
					//$post_content = str_replace("&", "\n", urldecode($req));
					$post_content = '\n############## '.__('ORIGINAL SUBSCRIPTION INFO BELOW', GEODIRSTRIPE_TEXTDOMAIN).' ####################\n';
					$post_content .= $invoice_id->post_content;
					
					$pid_sql = 	$wpdb->prepare("UPDATE ".INVOICE_TABLE." SET 
												status = 'Subscription-Canceled',
												html = %s
												WHERE post_id = %d AND is_current = 1",
												array($post_content,$post_id)
											);
						
					$invoice_id = $wpdb->query($pid_sql);
					
					/* Set the experation date*/
					$pid_sql2 = $wpdb->prepare("SELECT id, date FROM ".INVOICE_TABLE." WHERE post_id = %d AND status IN(%s,%s) ORDER BY date desc", array($post_id,'Subscription-Payment','Paid'));
					
					$invoice_id2 = $wpdb->get_row($pid_sql2);
					$d1 = $invoice_id2->post_date; /* get past payment date */
					$d2 = date('Y-m-d'); /* get current date */
					$date_diff = round(abs(strtotime($d1)-strtotime($d2))/86400); /* get the differance in days*/
					if($priceinfo['sub_units']=='D'){$mult = 1;}
					if($priceinfo['sub_units']=='W'){$mult = 7;}
					if($priceinfo['sub_units']=='M'){$mult = 30;}
					if($priceinfo['sub_units']=='Y'){$mult = 365;}
					$pay_days = ($priceinfo['sub_units_num']*$mult);
					$days_left = ($pay_days-$date_diff); /* Get days left*/
					$expire_date = date('Y-m-d', strtotime("+".$days_left." days"));
					geodir_update_post_meta($post_id, "expire_date", $expire_date);
					
					break;
			}
			
			
		} catch (Stripe_Error $e) {
			log_write( print_r( $e, true ) );
		}	
	}

	//http_response_code(200); // PHP 5.4 or greater
}

add_action( 'geodir_subscription_methods', 'geodir_add_stripe_method',10,1);
function geodir_add_stripe_method($methods){
	$methods[] = 'payment_method_stripe';
	return $methods;
}

add_action('geodir_subscription_supported_by','geodir_subscription_supported_by_stripe',10,1);
function geodir_subscription_supported_by_stripe($methods){
	$methods[]=__('Stripe', GEODIRSTRIPE_TEXTDOMAIN);
	return $methods;
}
?>
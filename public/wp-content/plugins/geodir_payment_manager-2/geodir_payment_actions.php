<?php
/**
 * activation hooks
 **/
if ( is_admin() ) :

	add_action( 'admin_enqueue_scripts', 'geodir_admincss_payment_manager', 10 );
	
	add_filter('geodir_settings_tabs_array','geodir_payment_manager_tabs',2);
	
	add_action('geodir_admin_option_form' , 'geodir_payment_manager_tab_content', 2);
	
	add_action('wp_ajax_geodir_payment_manager_ajax', "geodir_payment_manager_ajax");
	add_action( 'wp_ajax_nopriv_geodir_payment_manager_ajax', 'geodir_payment_manager_ajax' ); 
	add_action( 'add_meta_boxes', 'geodir_payment_metabox_add',12 );  
	add_action( 'save_post', 'geodir_post_transaction_save' );
	
	add_action('admin_init', 'geodir_payment_activation_redirect');
	
	add_action( 'admin_enqueue_scripts', 'geodir_payment_admin_scripts' );

	add_action('admin_footer','geodir_payment_localize_all_js_msg');
	
	add_filter('geodir_payment_notifications', 'geodir_enable_editor_on_payment_notifications', 1);
	
	add_action( 'geodir_create_new_post_type', 'geodir_payment_create_new_post_type', 1, 1 );
	
	add_action( 'geodir_after_post_type_deleted', 'geodir_payment_delete_post_type', 1, 1 );
	
	add_filter('geodir_after_custom_detail_table_create', 'geodir_payment_after_custom_detail_table_create',2,2);
	
endif;




function geodir_payment_localize_all_js_msg(){

	global $path_location_url;
	
	$arr_alert_msg = array(
							'geodir_payment_admin_url' => admin_url('admin.php'),
							'geodir_payment_admin_ajax_url' => admin_url('admin-ajax.php'),
							'geodir_want_to_delete_price' =>__('Are you sure want to delete price?',GEODIRPAYMENT_TEXTDOMAIN),
							'geodir_payment_enter_title' =>__('Please enter Title',GEODIRPAYMENT_TEXTDOMAIN),
							'geodir_payment_coupon_code' =>__('Please enter coupon code.',GEODIRPAYMENT_TEXTDOMAIN),
							'geodir_payment_select_post_type' =>__('Please select post type.',GEODIRPAYMENT_TEXTDOMAIN),
							'geodir_payment_enter_discount' =>__('Please enter discount amount.',GEODIRPAYMENT_TEXTDOMAIN),
							'geodir_payment_delete_coupon' =>__('Are you sure want to delete coupon?',GEODIRPAYMENT_TEXTDOMAIN),
							
						);
						
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_payment_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}

/* All payment gateways form related handlers */
/* show paypal form */
add_action('geodir_payment_form_handler_paypal' , 'geodir_payment_form_paypal');
function geodir_payment_form_paypal($invoice_id)
{
	$invoice_info = geodir_get_invoice($invoice_id); 
							
	$payable_amount = $invoice_info->paied_amount;
	$last_postid = $invoice_info->post_id;
	$post_title = $invoice_info->post_title;
	$package_id = $invoice_info->package_id;
	$listing_price_info=geodir_get_post_package_info($package_id ,$last_postid);

	if($listing_price_info['sub_active']){
		$sub_active = $listing_price_info['sub_active'];
		$sub_units_num = $listing_price_info['sub_units_num'];
		$sub_units = $listing_price_info['sub_units'];
		$sub_num_trial_days = $listing_price_info['sub_num_trial_days'];
		$sub_units_num_times = $listing_price_info['sub_units_num_times'];
	}
	
	$paymentOpts = get_payment_options( $invoice_info->paymentmethod);

	$merchantid = $paymentOpts['merchantid'];
	$returnUrl = $paymentOpts['returnUrl'];
	$cancel_return = $paymentOpts['cancel_return'];
	$notify_url = $paymentOpts['notify_url'];
	$paymode = $paymentOpts['payment_mode'];
	$currency_code = geodir_get_currency_type();
	$sub_action='';
	if($paymode =='sandbox')
		$action = 'https://www.sandbox.paypal.com/us/cgi-bin/webscr' ;
	else
		$action = 'https://www.paypal.com/cgi-bin/webscr';
	
?>
	
	<form name="frm_payment_method" action="<?php echo $action;?>" method="post">
	
	<?php 
	/* PAYPAL RECURRING CODE */
	if(isset($sub_active) && $sub_active != ''){
		$sub_action='-subscriptions';
		if(!empty($sub_num_trial_days) && $sub_num_trial_days !=0)
		{
	?>	
    		<input type="hidden" value="<?php echo $sub_num_trial_days;?>" name="p1">
            <input type="hidden" value="D" name="t1">
            <input type="hidden" value="0" name="a1">
    		
    	<?php
        }
		?>
	<input type="hidden" value="<?php echo $payable_amount;?>" name="a3">
	<input type="hidden" value="<?php echo $sub_units;?>" name="t3">
	<input type="hidden" value="<?php echo $sub_units_num;?>" name="p3">
	<input type="hidden" value="1" name="src">
	<input type="hidden" value="2" name="rm">
    <?php
		if(!empty($sub_units_num_times) && $sub_units_num_times !=0)
		{
	?>
    		<input type="hidden" value="<?php echo $sub_units_num_times;?>" name="srt">
    <?php
		}
	}?>
	<input type="hidden" value="<?php echo $payable_amount;?>" name="amount"/>
	<input type="hidden" value="<?php echo $returnUrl;?>&pid=<?php echo $last_postid;?>" name="return"/>
	<input type="hidden" value="<?php echo $cancel_return;?>&pid=<?php echo $last_postid;?>" name="cancel_return"/>
	<input type="hidden" value="<?php echo $notify_url;?>" name="notify_url"/>
	<input type="hidden" value="_xclick<?php echo $sub_action;?>" name="cmd"/>
	<input type="hidden" value="<?php echo apply_filters( 'geodir_paypal_item_name', site_url().' - '.$post_title, $post_title, $last_postid ); ?>" name="item_name"/>
	<input type="hidden" value="<?php echo $merchantid;?>" name="business"/>
	<input type="hidden" value="<?php echo $currency_code;?>" name="currency_code"/>
	<input type="hidden" value="<?php echo $last_postid;?>" name="custom" />
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="no_shipping" value="1">
    <!--<input type="submit" value="submit" />-->
    </form>
	<div class="wrapper" >
			<div class="clearfix container_message">
			<center><h1 class="head2"><?php echo PAYPAL_MSG; ?></h1></center>
			 </div>
	</div>
	<script>
	setTimeout("document.frm_payment_method.submit()",50); 
	</script> 
<?php
}

/* 2co form */
add_action('geodir_payment_form_handler_2co' , 'geodir_payment_form_2co');
function geodir_payment_form_2co($invoice_id)
{
	$invoice_info = geodir_get_invoice($invoice_id); 
							
	$payable_amount = $invoice_info->paied_amount;
	$last_postid = $invoice_info->post_id;
	$post_title = $invoice_info->post_title;
	
	global $current_user;
	$display_name = $current_user->data->display_name;
	$user_email = $current_user->data->user_email;
	
	$paymentOpts = get_payment_options( $invoice_info->paymentmethod);
	$merchantid = $paymentOpts['vendorid'];
	if($merchantid == '')
	{
		$merchantid = '1303908';
	}
	$ipnfilepath = $paymentOpts['ipnfilepath'];
	$currency_code = geodir_get_currency_type();
?>

    <form method="post" action="https://www.2checkout.com/checkout/purchase" name="frm_payment_method">
    <input type="hidden" value="73453" name="c_prod"/>
    <input type="hidden" value="<?php echo $post_title;?>" name="c_name"/>
    <input type="hidden" value="<?php echo $post_title;?>" name="c_description"/>
    <input type="hidden" value="<?php echo $payable_amount;?>" name="c_price"/>
    <input type="hidden" value="1" name="id_type"/>
    <input type="hidden" value="<?php echo $last_postid;?>" name="cart_order_id"/>
    <input type="hidden" value="<?php echo $payable_amount;?>" name="total"/>
    <input type="hidden" value="<?php echo $merchantid;?>" name="sid"/>
    <input type="hidden" name="c_tangible" value="N">
    <input type='hidden' name='x_receipt_link_url' value='<?php echo $ipnfilepath;?>' />
    <input type='hidden' name='x_amount' value='<?php echo $payable_amount;?>' />
    <input type='hidden' name='x_login' value='<?php echo $merchantid;?>' />
    <input type='hidden' name='x_invoice_num' value='<?php echo $last_postid;?>' />
    <input type='hidden' name='x_first_name' value='<?php echo $display_name;?>' />
    <input type='hidden' name='x_email' value='<?php echo $user_email;?>' />
    <input type="hidden" name="tco_currency" value="<?php echo $currency_code;?>" />
     <!--<input type="submit" value="Buy from 2CO" name="purchase" class="submit"/>-->
    </form>
     <div class="wrapper" >
            <div class="clearfix container_message">
                    <center><h1 class="head2"><?php echo TWOCO_MSG;?></h1></center>
                </div>
    
    <script>
    setTimeout("document.frm_payment_method.submit()",50); 
    </script>
<?php
}

/* Authorizenet form */
add_action('geodir_payment_form_handler_authorizenet' , 'geodir_payment_form_authorizenet');
function geodir_payment_form_authorizenet($invoice_id)
{
	$invoice_info = geodir_get_invoice($invoice_id); 
							
	$payable_amount = $invoice_info->paied_amount;
	$last_postid = $invoice_info->post_id;
	$post_title = $invoice_info->post_title;
	
	$paymentOpts = get_payment_options( $invoice_info->paymentmethod);
	global $current_user;
	$display_name = $current_user->data->display_name;
	$user_email = $current_user->data->user_email;
	$user_phone = isset($current_user->data->user_phone) ? $current_user->data->user_phone : '';
	require_once('authorizenet/authorizenet.class.php');
?>
	<div class="wrapper" >
    	<div class="clearfix container_message">
            <h1 class="head2"><?php echo AUTHORISE_NET_MSG;?></h1>
        </div>
    </div>
<?php
	$a = new authorizenet_class;
	
	/*You login using your login, login and tran_key, or login and password.  It
	varies depending on how your account is setup.
	I believe the currently reccomended method is to use a tran_key and not
	your account password.  See the AIM documentation for additional information.*/
	
	$a->add_field('x_login', $paymentOpts['loginid']);
	$a->add_field('x_tran_key', $paymentOpts['transkey']);
	/*$a->add_field('x_password', 'CHANGE THIS TO YOUR PASSWORD');*/
	
	$a->add_field('x_version', '3.1');
	$a->add_field('x_type', 'AUTH_CAPTURE');
	/*$a->add_field('x_test_request', 'TRUE');     Just a test transaction*/
	$a->add_field('x_relay_response', 'FALSE');
	
	/*You *MUST* specify '|' as the delim char due to the way I wrote the class.
	I will change this in future versions should I have time.  But for now, just
	 make sure you include the following 3 lines of code when using this class.*/
	
	$a->add_field('x_delim_data', 'TRUE');
	$a->add_field('x_delim_char', '|');     
	$a->add_field('x_encap_char', '');
	
	
	/* Setup fields for customer information.  This would typically come from an
	array of POST values froma secure HTTPS form.*/
	
	$a->add_field('x_first_name', $display_name);
	$a->add_field('x_last_name', '');
/*	$a->add_field('x_address', $address);
	$a->add_field('x_city', $userInfo['user_city']);
	$a->add_field('x_state', $userInfo['user_state']);
	$a->add_field('x_zip', $userInfo['user_postalcode']);
	$a->add_field('x_country', 'US');
	$a->add_field('x_country',  $userInfo['user_country']);*/
	$a->add_field('x_email', $user_email);
	$a->add_field('x_phone', $user_phone);
	
	/* Using credit card number '4007000000027' performs a successful test.  This
	 allows you to test the behavior of your script should the transaction be
	 successful.  If you want to test various failures, use '4222222222222' as
	 the credit card number and set the x_amount field to the value of the
	 Response Reason Code you want to test. 
	
	 For example, if you are checking for an invalid expiration date on the
	 card, you would have a condition such as:
	 if ($a->response['Response Reason Code'] == 7) ... (do something)
	
	 Now, in order to cause the gateway to induce that error, you would have to
	 set x_card_num = '4222222222222' and x_amount = '7.00'
	
	  Setup fields for payment information*/
	$a->add_field('x_method', $_REQUEST['cc_type']);
	$a->add_field('x_card_num', $_REQUEST['cc_number']);
	/*$a->add_field('x_card_num', '4007000000027');   // test successful visa
	$a->add_field('x_card_num', '370000000000002');   // test successful american express
	$a->add_field('x_card_num', '6011000000000012');  // test successful discover
	$a->add_field('x_card_num', '5424000000000015');  // test successful mastercard
	 $a->add_field('x_card_num', '4222222222222');    // test failure card number*/
	$a->add_field('x_amount', $payable_amount);
	$a->add_field('x_exp_date', $_REQUEST['cc_month'].substr($_REQUEST['cc_year'],2,strlen($_REQUEST['cc_year'])));    /* march of 2008*/
	$a->add_field('x_card_code', $_REQUEST['cv2']);    // Card CAVV Security code
	/* Process the payment and output the results*/
	switch ($a->process()) {
	
	   case 1:  /* Successs */
			set_property_status($last_postid,'publish');
			$redirectUrl = home_url()."/?ptype=payment_success&pid=".$last_postid;
			wp_redirect($redirectUrl);
			break;
	   case 2:  /* Declined */
			$paymentFlag = 0;
			$_SESSION['display_message'] = $a->get_response_reason_text();
			break;
		 
	   case 3:  /* Error */
		   $paymentFlag = 0;
		  /*echo "<b>Error with Transaction:</b><br>";
		 	echo $a->get_response_reason_text();
		 	echo "<br><br>Details of the transaction are shown below...<br><br>";*/
		 $_SESSION['display_message'] = $a->get_response_reason_text();
		  break;
	}
	if($paymentFlag == 0)
	{
		 wp_redirect(home_url()."/?ptype=checkout");
		 exit;
	}	
}

/* Googlechkout form */
add_action('geodir_payment_form_handler_googlechkout' , 'geodir_payment_form_googlechkout');
function geodir_payment_form_googlechkout($invoice_id)
{
	$invoice_info = geodir_get_invoice($invoice_id); 
							
	$payable_amount = $invoice_info->paied_amount;
	$last_postid = $invoice_info->post_id;
	$post_title = $invoice_info->post_title;
	$currency_code = geodir_get_currency_type();
	$paymentOpts = get_payment_options( $invoice_info->paymentmethod);
	$merchantid = $paymentOpts['merchantid'];
	$merchantkey = $paymentOpts['merchantsecret'];
	
	
require_once  (GEODIR_PAYMENT_MANAGER_PATH.'/googlewallet/generate_token.php');
	?>

 
 
  <div class="wrapper" >
		<div class="clearfix container_message">
            	<h1 class="head2"><?php echo GOOGLE_CHKOUT_MSG;?></h1>
            </div>
 

<?php if($paymentOpts['payment_mode']=='sandbox'){?>
<script src="https://sandbox.google.com/checkout/inapp/lib/buy.js"></script>
<?php }else{?>
<script src="https://wallet.google.com/inapp/lib/buy.js"></script>
<?php }?>

<script type='text/javascript'>

	function gtPaymentSuccess(result){
	  var p_arr = result.request.sellerData;
	  var res = p_arr.split(":");
	  var p_id =res[2];
	  window.location.replace("<?php echo home_url().'/?pay_action=return&pmethod=googlewallet&pid=';?>"+p_id);
  	}
	
	function gtPaymentFailure(result){
		var p_arr = result.request.request.sellerData;
	  var res = p_arr.split(":");
	  var p_id =res[2];
		window.location.replace("<?php echo home_url().'/?pay_action=cancel&pmethod=googlewallet&pid=';?>"+p_id);
  	}

  function payGoogle(jwt_value) {
	google.payments.inapp.buy({
	  jwt: jwt_value,
        success: function(result) {console.log('success');console.log(result);gtPaymentSuccess(result);},
	  failure: function(result) {console.log(result.response.errorType);gtPaymentFailure(result);}
	});
	return false;
  }
</script>
<script>
(function(){
    payGoogle("<?php echo $jwtToken;?>");
})();
</script>
<?php
}

/* Payondelevary form */
add_action('geodir_payment_form_handler_payondelevary' , 'geodir_payment_form_payondelevary');
function geodir_payment_form_payondelevary($invoice_id)
{
}


/* Prebanktransfer form */
add_action('geodir_payment_form_handler_prebanktransfer' , 'geodir_payment_form_prebanktransfer');
function geodir_payment_form_prebanktransfer($invoice_id)
{
	$invoice_info = geodir_get_invoice($invoice_id); 
	$post_id = $invoice_info->post_id;
	
	$redirect_url = geodir_getlink(home_url(),array('pay_action'=>'success', 'pid' =>$post_id),false);
	wp_redirect($redirect_url); 
	 
}

/* Worldpay form */
add_action('geodir_payment_form_handler_worldpay' , 'geodir_payment_form_worldpay');
function geodir_payment_form_worldpay($invoice_id)
{
	$invoice_info = geodir_get_invoice($invoice_id); 
							
	$payable_amount = $invoice_info->paied_amount;
	$last_postid = $invoice_info->post_id;
	$post_title = $invoice_info->post_title;
	$currency_code = geodir_get_currency_type();
	$paymentOpts = get_payment_options( $invoice_info->paymentmethod);
	$instId = $paymentOpts['instId'];
	$accId1 = $paymentOpts['accId1'];

?>
<form action="https://select.worldpay.com/wcc/purchase" method="post" target="_top" name="frm_payment_method">	
<input type="hidden" value="<?php echo $payable_amount;?>" name="amount"/>
<input type="hidden" value="<?php echo $instId;?>" name="instId"/>
<input type="hidden" value="<?php echo $accId1;?>" name="accId1"/>
<input type="hidden" value="<?php echo $last_postid;?>" name="cartId"/>
<input type="hidden" value="<?php echo $post_title;?>" name="desc"/>
<input type="hidden" value="<?php echo $currency_code;?>" name="currency"/>
<input type="hidden" value="" name="testMode"/>
</form>
 
 
  <div class="wrapper" >
    <div class="clearfix container_message">
            <h1 class="head2"><?php echo WORLD_PAY_MSG?></h1>
    </div>

<script>
setTimeout("document.frm_payment_method.submit()",50);
</script>
<?php
}
/* End of payment gateways form handler section  */

add_action('init','geodir_payment_ipn');
add_filter( 'template_include', 'geodir_payment_response',200);
function geodir_payment_ipn(){
	
	if(isset($_REQUEST['pay_action']) )
	{
		global $wp_query;
		if($_REQUEST['pay_action'] == 'ipn' && isset($_REQUEST['pmethod']))	
		{
			do_action('geodir_ipn_handler_' .$_REQUEST['pmethod'] ); /* ADD IPN handler action */
			return ;
		}
	}
}

function geodir_payment_response($template){
	
	if(isset($_REQUEST['pay_action']) )
	{
		global $wp_query;
		if($_REQUEST['pay_action'] == 'cancel'){	
			$template = locate_template( array( geodir_plugin_url() . '/cancel.php' ) );
			if ( ! $template ) 
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/cancel.php';
		}
		
		if($_REQUEST['pay_action'] == 'return'){	
			$template = locate_template( array( geodir_plugin_url() . '/return.php' ) );
			if ( ! $template )
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/return.php';
		}	
		if($_REQUEST['pay_action'] == 'success'){	
			$template = locate_template( array( geodir_plugin_url() . '/success.php' ) );
			if ( ! $template ) 
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/success.php';
		}
	}
	return $template;
}

/* Start of IPN handler */
/* Paypal IPN Handler */
add_action('geodir_ipn_handler_paypal' , 'geodir_ipn_handler_paypal');
function geodir_ipn_handler_paypal()
{
	global $wpdb;
	$header='';
	$paymentOpts = get_payment_options('paypal');
	$paymode = $paymentOpts['payment_mode'];
	//mail('rightmentor@gmail.com' , 'In IPN'  , 'Just started the IPN') ;
	/* read the post from PayPal system and add 'cmd' */
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) 
	{
		$value = urlencode(stripslashes($value));
		$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);/* this fiexs paypals invalid IPN , STIOFAN */
		$req .= "&$key=$value";
	}
	
	$post_content = str_replace("&", "\n", urldecode($req));
	/* post back to PayPal system to validate */
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$skip_trans_verifired = false;
	
		
	//	$fp = fsockopen ('ssl://www.sandbox.paypal.com/cgi-bin/webscr', 80, $errno, $errstr, 30);
	//else 
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
	
	if (!$fp) 
	{ 
		/* HTTP ERROR */
	} 
	else 
	{
		fputs ($fp, $header . $req);
	
		while (!feof($fp)) 
		{
			$res = fgets ($fp, 1024);
			//mail('rightmentor@gmail.com','IPN Prodess Test 111',$res);
			/*mail('rightmentor@gmail.com','Paypal Response' . time(), $res);*/
			/*continue ;*/
			if (strcmp ($res, "INVALID") == 0)
			{
				if($paymode =='sandbox')
					$skip_trans_verifired = true;
			}
			
			if ( (strcmp ($res, "VERIFIED") == 0 ) || $skip_trans_verifired) // it will enter in conditon in test mode. 
			{
				/* yes valid recipt */
				$postid               = $_POST['custom'];
				$item_name			  = $_POST['item_name'];
				$txn_id				  = $_POST['txn_id'];
				$payment_status       = $_POST['payment_status'];
				$payment_type         = $_POST['payment_type'];
				$payment_date         = $_POST['payment_date'];
				$txn_type             = $_POST['txn_type'];
				
				$mc_currency          = $_POST['mc_currency']; /* get currency code*/
				$mc_gross             = $_POST['mc_gross'];
				$mc_amount3           = $_POST['mc_amount3'];
				$receiver_email       = $_POST['receiver_email'];
				
				//mail('rightmentor@gmail.com','txn_type ' . $txn_id	 ,$txn_type);
				//mail('phpdeveloper3.protoindex@gmail.com','txn_type ' . $txn_id	 ,$txn_type);
				$post_pkg = geodir_get_post_meta($postid, 'package_id',true); /* get the post price package ID*/
				
				global $wpdb;
				
				$pricesql = $wpdb->prepare(
											"select * from ".GEODIR_PRICE_TABLE." where status=1 and pid=%d", 
											array($post_pkg)
										);
				
				
				$priceinfo = $wpdb->get_row($pricesql, ARRAY_A); /* Get the price package info*/
				
				//$pkg_price = $priceinfo['amount']; /* get the price of the package		*/
				$currency_code = geodir_get_currency_type(); /* get the actual curency code		*/
				$merchantid = $paymentOpts['merchantid']; /* Get the site paypal address*/
				if($mc_gross){$paid_amt = $mc_gross;}else{$paid_amt = $mc_amount3;}
				
				
				/* ---- get paied amount ---- */
				$pkg_price = $wpdb->get_var($wpdb->prepare("SELECT paied_amount FROM ".INVOICE_TABLE." WHERE post_id = %d AND is_current=%s", array($postid,'1')));
				
				$productinfosql = $wpdb->prepare(
														"select ID,post_title,guid,post_author from $wpdb->posts where ID = %d",
														array($postid) 
													);
				
				$productinfo = $wpdb->get_results($productinfosql);
				foreach($productinfo as $productinfoObj)
				{
					/*$post_link = home_url().'/?ptype=preview&alook=1&pid='.$postid;*/
					$post_title = '<a href="'.get_permalink($postid).'">'.$productinfoObj->post_title.'</a>'; 
					$aid = $productinfoObj->post_author;
					$userInfo = geodir_get_author_info($aid);
					$to_name = $userInfo->user_nicename;
					$to_email = $userInfo->user_email;
					$user_email = $userInfo->user_email;
				}
				
				/*######################################
				######## FRAUD CHECKS ################
				######################################*/
				$fraud=0; /* Set no fraude*/
				$fraud_msg=''; /*Set blank fraud message*/
				$transaction_details=''; /*Set blank transaction message*/
				//mail('rightmentor@gmail.com' , 'Receiver and Merchant Emails' ,$receiver_email .' -- ' .$merchantid );
				if($receiver_email!=$merchantid){$fraud=1; $fraud_msg= __('### The PayPal reciver email address does not match the paypal address for this site ###<br />', GEODIRPAYMENT_TEXTDOMAIN);}
				if($paid_amt!=$pkg_price){$fraud=1; $fraud_msg.= __('### The paid amount does not match the price package selected ###<br />', GEODIRPAYMENT_TEXTDOMAIN);}
				if($mc_currency!=$currency_code){$fraud=1; $fraud_msg.= __('### The currency code returned does not match the code on this site. ###<br />', GEODIRPAYMENT_TEXTDOMAIN);}
				;
				/*######################################
				######## PAYMENT SUCCESSFUL ##########
				######################################*/
				
				if($txn_type == 'web_accept' || $txn_type == 'subscr_payment' || $txn_type == 'recurring_payment' || $txn_type == 'express_checkout'){
				
					$post_default_status = geodir_new_post_default_status();
					if($post_default_status=='')
					{
						$post_default_status = 'publish';
					}
					//mail('rightmentor@gmail.com',"No Fraud $fraud - " . $postid	 ,$post_default_status);
					
					if(!$fraud){geodir_set_post_status($postid,$post_default_status);}
					
					if($fraud){$transaction_details .= __('WARNING FRAUD DETECTED PLEASE CHECK THE DETAILS - (IF CORRECT, THEN PUBLISH THE POST)', GEODIRPAYMENT_TEXTDOMAIN)."<br />";}
					$paid_amount_with_currency = get_option('geodir_currencysym') .$paid_amt ;
					
					$transaction_details .= $fraud_msg;
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Payment Details for Listing ID #%s", GEODIRPAYMENT_TEXTDOMAIN), $postid ) ."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Listing Title: %s", GEODIRPAYMENT_TEXTDOMAIN),$item_name)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Trans ID: %s", GEODIRPAYMENT_TEXTDOMAIN), $txn_id)."<br />";
					$transaction_details .= sprintf(__("Status: %s", GEODIRPAYMENT_TEXTDOMAIN), $payment_status)."<br />";
					$transaction_details .= sprintf(__("Amount: %s", GEODIRPAYMENT_TEXTDOMAIN),$paid_amount_with_currency)."<br />"
					;
					$transaction_details .= sprintf(__("Type: %s", GEODIRPAYMENT_TEXTDOMAIN),$payment_type)."<br />";
					$transaction_details .= sprintf(__("Date: %s", GEODIRPAYMENT_TEXTDOMAIN), $payment_date)."<br />";
					$transaction_details .= sprintf(__("  Method: %s", GEODIRPAYMENT_TEXTDOMAIN), $txn_type)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";		
					$transaction_details .= __("Information Submitted URL", GEODIRPAYMENT_TEXTDOMAIN)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= "  $post_title<br />";
					/*############ SET THE INVOICE STATUS START ############*/
					if($txn_type == 'recurring_payment' || $txn_type == 'subscr_payment'){
						
						$pid_sql = $wpdb->prepare("UPDATE ".INVOICE_TABLE." SET 
									`paymentmethod` = 'Paypal',
									status = 'Subscription-Payment',
									html = %s 
									WHERE post_id = %d AND is_current = 1",
									array($transaction_details,$postid)
									);
						
						$invoice_id = $wpdb->query($pid_sql);
						
					}else{
						$pid_sql = $wpdb->prepare("UPDATE ".INVOICE_TABLE." SET status = 'Paid',html = %s WHERE post_id = %d AND is_current = 1", array($transaction_details,$postid));
						
						$invoice_id = $wpdb->query($pid_sql);
						
					}
					/*############ SET THE INVOICE STATUS END ############*/
					
					geodir_payment_adminEmail($postid,$aid,'payment_success',$transaction_details); /*email to admin*/
					geodir_payment_clientEmail($postid,$aid,'payment_success',$transaction_details); /*email to client*/
				
				}elseif($txn_type == 'subscr_cancel' || $txn_type == 'subscr_failed'){
				
					/* Set the subscription ac canceled*/
					$post_content = str_replace("&", "\n", urldecode($req));
					$post_content .= '\n############## '.__('ORIGINAL SUBSCRIPTION INFO BELOW', GEODIRPAYMENT_TEXTDOMAIN).' ####################\n';
					$post_content .= $invoice_id->post_content;
					
					$pid_sql = 	$wpdb->prepare("UPDATE ".INVOICE_TABLE." SET 
												status = 'Subscription-Canceled',
												html = %s
												WHERE post_id = %d AND is_current = 1",
												array($post_content,$postid)
											);
						
					$invoice_id = $wpdb->query($pid_sql);
				
				
					/* Set the experation date*/
					$pid_sql2 = $wpdb->prepare("SELECT id, date FROM ".INVOICE_TABLE." WHERE post_id = %d AND status IN(%s,%s) ORDER BY date desc", array($postid,'Subscription-Payment','Paid'));
					
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
					geodir_update_post_meta($postid, "expire_date", $expire_date);
				
				
				}elseif($txn_type == 'subscr_signup' ){
				
					$post_content = str_replace("&", "\n", urldecode($req));
					$user_id = $aid;
				
					$pid_sql = $wpdb->prepare("UPDATE ".INVOICE_TABLE." SET 
										'paymentmethod' = 'Paypal',
										status = 'Subscription-Active',
										user_id = %d,
										html = %s 
										WHERE post_id = %d AND is_current = 1",
										array($user_id,$post_content,$postid));
						
					$invoice_id = $wpdb->query($pid_sql);
					/*############# INSERT TRANSCATION DETAILS END #################*/
				}
				
				/*######################################
	
				######## PAYMENT SUCCESSFUL ##########
				######################################*/
				
			}else if (strcmp ($res, "INVALID") == 0){
				geodir_payment_adminEmail($_POST['custom'],'1','payment_fail'); /* email to admin*/
			}
	
		}
	}
}
/* Paypal IPN Handler */
add_action('geodir_ipn_handler_googlewallet' , 'geodir_ipn_handler_googlewallet');
function geodir_ipn_handler_googlewallet()
{
global $wpdb;
require_once  (GEODIR_PAYMENT_MANAGER_PATH.'/googlewallet/JWT.php');
$paymentOpts = get_payment_options('googlechkout');
//print_r($paymentOpts);
$merchantkey = $paymentOpts['merchantsecret'];
	$currency_code = geodir_get_currency_type();
	$merchantid = $paymentOpts['merchantid'];
	$merchantkey = $paymentOpts['merchantsecret'];
	
	$encoded_jwt = $_POST['jwt']; 
$decodedJWT = JWT::decode($encoded_jwt, $merchantkey);


	$post_title = $decodedJWT->request->name;
	$payable_amount = $decodedJWT->request->price;
	
	// yes valid recipt
		$p_arr = explode(",", $decodedJWT->request->sellerData);
		$p_arr2 = explode(":", $p_arr[1]);
		
		
		$last_postid = $p_arr2[1];
require_once  (GEODIR_PAYMENT_MANAGER_PATH.'/googlewallet/generate_token.php');
//$encoded_jwt = $_POST['jwt']; 


// get orderId
$orderId = $decodedJWT->response->orderId;

		if( $_POST['jwt']){
				if ($orderId) 
				{		// yes valid recipt
		$p_arr = explode(",", $decodedJWT->request->sellerData);
		$p_arr2 = explode(":", $p_arr[1]);
		
		
		$postid               = $p_arr2[1];
		$item_name			  = $decodedJWT->request->name;
		$txn_id				  = $orderId;
		$payment_status       = 'PAID';
		$payment_type         = 'Google Wallet';
		$payment_date         = date("F j, Y, g:i a");
		$txn_type             = $decodedJWT->typ;
		
		$mc_currency          = $decodedJWT->request->currencyCode; // get curancy code
		$mc_gross             = $decodedJWT->request->price;
		$mc_amount3           = $decodedJWT->request->price;
################################################################################################################################################################################
	global $wpdb;
	$header='';


				$post_pkg = geodir_get_post_meta($postid, 'package_id',true); /* get the post price package ID*/
				
				global $wpdb;
				
				$pricesql = $wpdb->prepare(
											"select * from ".GEODIR_PRICE_TABLE." where status=1 and pid=%d", 
											array($post_pkg)
										);
				
				
				$priceinfo = $wpdb->get_row($pricesql, ARRAY_A); /* Get the price package info*/
				
				$pkg_price = $priceinfo['amount']; /* get the price of the package		*/
				$currency_code = geodir_get_currency_type(); /* get the actual curency code		*/
				$merchantid = $paymentOpts['merchantid']; /* Get the site paypal address*/
				if($mc_gross){$paid_amt = $mc_gross;}else{$paid_amt = $mc_amount3;}
				
				$productinfosql = $wpdb->prepare(
														"select ID,post_title,guid,post_author from $wpdb->posts where ID = %d",
														array($postid) 
													);
				$productinfo = $wpdb->get_results($productinfosql);
				foreach($productinfo as $productinfoObj)
				{
					/*$post_link = home_url().'/?ptype=preview&alook=1&pid='.$postid;*/
					$post_title = '<a href="'.get_permalink($postid).'">'.$productinfoObj->post_title.'</a>'; 
					$aid = $productinfoObj->post_author;
					$userInfo = geodir_get_author_info($aid);
					$to_name = $userInfo->user_nicename;
					$to_email = $userInfo->user_email;
					$user_email = $userInfo->user_email;
				}
				
			
				/*######################################
				######## PAYMENT SUCCESSFUL ##########
				######################################*/
				
				if($txn_type){
					$post_default_status = geodir_new_post_default_status();
					if($post_default_status=='')
					{
						$post_default_status = 'publish';
					}
					//mail('rightmentor@gmail.com',"No Fraud $fraud - " . $postid	 ,$post_default_status);
					geodir_set_post_status($postid,$post_default_status);
					
					$transaction_details ='';
					$paid_amount_with_currency = get_option('geodir_currencysym') .$paid_amt;
					
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Payment Details for Listing ID #%s", GEODIRPAYMENT_TEXTDOMAIN), $postid ) ."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Listing Title: %s", GEODIRPAYMENT_TEXTDOMAIN),$item_name)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Trans ID: %s", GEODIRPAYMENT_TEXTDOMAIN), $txn_id)."<br />";
					$transaction_details .= sprintf(__("Status: %s", GEODIRPAYMENT_TEXTDOMAIN), $payment_status)."<br />";
					$transaction_details .= sprintf(__("Amount: %s", GEODIRPAYMENT_TEXTDOMAIN),$paid_amount_with_currency)."<br />"
					;
					$transaction_details .= sprintf(__("Type: %s", GEODIRPAYMENT_TEXTDOMAIN),$payment_type)."<br />";
					$transaction_details .= sprintf(__("Date: %s", GEODIRPAYMENT_TEXTDOMAIN), $payment_date)."<br />";
					$transaction_details .= sprintf(__("  Method: %s", GEODIRPAYMENT_TEXTDOMAIN), $txn_type)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";		
					$transaction_details .= __("Information Submitted URL", GEODIRPAYMENT_TEXTDOMAIN)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= "  $post_title<br />";
					/*############ SET THE INVOICE STATUS START ############*/
				
						$pid_sql = $wpdb->prepare("UPDATE ".INVOICE_TABLE." SET status = 'Paid',html = %s WHERE post_id = %d AND is_current = 1", array($transaction_details,$postid));
						
						$invoice_id = $wpdb->query($pid_sql);
						
					
					/*############ SET THE INVOICE STATUS END ############*/
					
					geodir_payment_adminEmail($postid,$aid,'payment_success',$transaction_details); /*email to admin*/
					geodir_payment_clientEmail($postid,$aid,'payment_success',$transaction_details); /*email to client*/
				
				}
				
				/*######################################
	
				######## PAYMENT SUCCESSFUL ##########
				######################################*/
				header("HTTP/1.0 200 OK"); 
				echo $orderId;
				
			}else if (strcmp ($res, "INVALID") == 0){
				geodir_payment_adminEmail($_POST['custom'],'1','payment_fail'); /* email to admin*/
			}
	
		}
	
}

add_action('geodir_ipn_handler_2co' , 'geodir_ipn_handler_2co');
function geodir_ipn_handler_2co()
{
	global $Cart,$General;
	$req = '';
	foreach ($_POST as $field=>$value)
	{
		$ipnData["$field"] = $value;
		$req .= "&$field=$value";
	}
	
	$postid    = intval($ipnData['x_invoice_num']);
	$pnref      = $ipnData['x_trans_id'];
	$amount     = geodir_get_currency_sym().doubleval($ipnData['x_amount']);
	$result     = intval($ipnData['x_response_code']);
	$respmsg    = $ipnData['x_response_reason_text'];
	$customer_email    = $ipnData['x_email'];
	$customer_name = $ipnData['x_first_name'];
	
	$fromEmail = get_option('site_email');
	$fromEmailName = get_site_emailName();
	$subject = "Acknowledge for Place Listing ID #$postid payment";

	if ($result == '1')
	{
		/* Valid IPN transaction.*/
		$post_default_status = geodir_new_post_default_status();
		if($post_default_status=='')
		{
			$post_default_status = 'publish';
		}
		set_property_status($postid,$post_default_status);

		$productinfosql =	$wpdb->prepare(
												"select ID,post_title,guid,post_author from $wpdb->posts where ID = %d",
												array($postid)
											);
		
		$productinfo = $wpdb->get_results($productinfosql);
		foreach($productinfo as $productinfoObj)
		{
			$post_title = '<a href="'.$productinfoObj->guid.'">'.$productinfoObj->post_title.'</a>'; 
			$aid = $productinfoObj->post_author;
			$userInfo = get_author_info($aid);
			$to_name = $userInfo->user_nicename;
			$to_email = $userInfo->user_email;
		}
		$message = __('
				<p>
				payment for Place Listing ID #'.$postid.' confirmation.<br>
				</p>
				<p>
				<b>You may find the details below:</b>
				</p>
				<p>----</p>
				<p>Place Listing Id : '.$postid.'</p>
				<p>Place Listing Title : '.$post_title.'</p>
				<p>User Name : '.$to_name.'</p>
				<p>User Email : '.$to_email.'</p>
				<p>Paid Amount :       '.$amount.'</p>
				<p>Transaction ID :       '.$pnref.'</p>
				<p>Result Code : '.$result.'</p>
				<p>Response Message : '.$respmsg.'</p>
				<p>----</p><br><br>
				<p>Thank you.</p>',GEODIRPAYMENT_TEXTDOMAIN);
		$subject = get_option('post_payment_success_admin_email_subject');
		if(!$subject)
		{
			$subject = __("Place Listing Submitted and Payment Success Confirmation Email", GEODIRPAYMENT_TEXTDOMAIN);
		}
		$content = get_option('post_payment_success_admin_email_content');
		$store_name = get_option('blogname');
		$fromEmail = get_option('site_email');
		$search_array = array('[#to_name#]','[#information_details#]','[#site_name#]');
		$replace_array = array($fromEmail,$message,$store_name);
		/*$message = str_replace($search_array,$replace_array,$content);*/
		
		geodir_payment_adminEmail($postid,$aid,'payment_success',$message); /* email to admin*/
		geodir_payment_clientEmail($postid,$aid,'payment_success',$message); /* email to client*/
		/*@wp_mail($fromEmail,$subject,$message,$headerarr);*/ /* email to admin*/
		
			/*############ SET THE INVOICE STATUS START ############*/
		
			$pid_sql =	$wpdb->prepare(
										"select p.ID from $wpdb->posts p join $wpdb->postmeta pm on pm.post_id=p.ID where p.post_title=%s AND meta_key=%s AND meta_value=%s ORDER BY p.ID desc", 
										array($postid,'_status','Unpaid')
									);
			
			$invoice_id = $wpdb->get_var($pid_sql);
			update_post_meta($invoice_id, "_status", 'Paid');
			/*$my_post['post_content'] = str_replace("&", "\n", urldecode($req));*/
			$my_post['post_content'] = $req;
			$my_post['ID'] = $invoice_id;
			/*$my_post['post_author'] = $aid;*/
			$last_postid = wp_update_post($my_post);
			
			/*############ SET THE INVOICE STATUS END ############*/
			
	if($ct_on && file_exists($child_dir.'/library/includes/success.php')){include_once ($child_dir.'/library/includes/success.php');}
	else{include_once (TEMPLATEPATH . '/library/includes/success.php');}
		exit;
		
		return true;
	}
	else if ($result != '1')
	{
		$message = __('
				<p>
				payment for Place Listing ID #'.$postid.' incompleted.<br>
				because of '.$respmsg.'
				</p>
				<p>
				<b>You may find the details below:</b>
				</p>
				<p>----</p>
				<p>Place Listing Id : '.$postid.'</p>
				<p>Place Listing Title : '.$post_title.'</p>
				<p>User Name : '.$to_name.'</p>
				<p>User Email : '.$to_email.'</p>
				<p>Paid Amount :       '.$amount.'</p>
				<p>Transaction ID :       '.$pnref.'</p>
				<p>Result Code : '.$result.'</p>
				<p>Response Message : '.$respmsg.'</p>
				<p>----</p><br><br>
	
				<p>Thank you.</p>', GEODIRPAYMENT_TEXTDOMAIN);
		$subject = get_option('post_payment_success_client_email_subject');
		if(!$subject)
		{
			$subject = __("Place Listing Submitted and Payment Success Confirmation Email", GEODIRPAYMENT_TEXTDOMAIN);
		}
		$content = get_option('post_payment_success_client_email_content');
		$store_name = get_option('blogname');
		$search_array = array('[#to_name#]','[#information_details#]','[#site_name#]');
		$replace_array = array($to_name,$message,$store_name);
		/*$message = str_replace($search_array,$replace_array,$content);*/
		
		geodir_payment_adminEmail($postid,$aid,'payment_success',$message); /* email to admin*/
		geodir_payment_clientEmail($postid,$aid,'payment_success',$message); /* email to client*/
		/*@wp_mail($to_email,$subject,$message,$headerarr);*/ /* email to client*/
		
			/*############ SET THE INVOICE STATUS START ############*/
			
			$pid_sql =	$wpdb->prepare(
										"select p.ID from $wpdb->posts p join $wpdb->postmeta pm on pm.post_id=p.ID where p.post_title=%s AND meta_key=%s AND meta_value=%s ORDER BY p.ID desc",
										array($postid,'_status','Unpaid')
									);
			
			$invoice_id = $wpdb->get_var($pid_sql);
			/*update_post_meta($invoice_id, "_status", 'Paid');*/
			/*$my_post['post_content'] = str_replace("&", "\n", urldecode($req));*/
			$my_post['post_content'] = $req;
			$my_post['ID'] = $invoice_id;
			/*$my_post['post_author'] = $aid;*/
			$last_postid = wp_update_post($my_post);
			
			/*############ SET THE INVOICE STATUS END ############*/
		
		if($ct_on && file_exists($child_dir.'/library/includes/success.php')){include_once ($child_dir.'/library/includes/success.php');}
	else{include_once (TEMPLATEPATH . '/library/includes/success.php');}
		exit;
		
		return false;
	}
}
/*End of ipn handler */



add_action('geodir_before_detail_fields' , 'geodir_build_payment_list', 1); 

add_action('geodir_before_detail_fields' , 'geodir_build_coupon', 2); 

add_filter('geodir_post_package_info', 'geodir_get_post_package_info_on_listing' , 2, 3) ;

add_action('geodir_before_admin_panel' , 'geodir_display_payment_messages'); 

add_action('geodir_after_edit_post_link', 'geodir_display_post_upgrade_link', 1); 

add_action('geodir_before_edit_post_link_on_listing', 'geodir_display_post_upgrade_link_on_listing', 1);


add_filter('geodir_publish_listing_form_message', 'geodir_payment_publish_listing_form_message', 1, 2);
function geodir_payment_publish_listing_form_message($form_message){
	return $form_message = '';
}


add_filter('geodir_publish_listing_form_go_back', 'geodir_payment_publish_listing_form_go_back', 1, 2);
function geodir_payment_publish_listing_form_go_back($listing_form_go_back){
	return $listing_form_go_back = '';
}

add_filter('geodir_publish_listing_form_button', 'geodir_payment_publish_listing_form_button', 1, 2);
function geodir_payment_publish_listing_form_button($listing_form_button){
	return $listing_form_button = '';
}

if(isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != ''){
	add_filter('geodir_publish_listing_form_action', 'geodir_payment_publish_listing_form_action', 1, 2);
}

function geodir_payment_publish_listing_form_action($form_action_url){
	
	global $post;
	
	$post_type = $post->listing_type;
	
	$package_price_info = geodir_get_post_package_info($_REQUEST['package_id']);
	
	$payable_amount = $package_price_info['amount'];
	
	if(isset($_REQUEST['coupon_code']) && $_REQUEST['coupon_code']!='')
	{
		if(geodir_is_valid_coupon($post_type, $_REQUEST['coupon_code']))
		{
			$payable_amount = geodir_get_payable_amount_with_coupon($payable_amount,$_REQUEST['coupon_code']);
		}
	}

	if($payable_amount > 0){
			
			$form_action_url = geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=paynow&listing_type='.$post_type;
			
	}

	return $form_action_url;
}

add_action('geodir_publish_listing_form_before_msg', 'geodir_publish_payment_listing_form_before_msg', 1);
function geodir_publish_payment_listing_form_before_msg(){
	
	global $post, $wpdb;
	
	
	$post_type = $post->listing_type;
	
	if(isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != ''){
		$package_price_info = geodir_get_post_package_info($_REQUEST['package_id']);
	}else{
		if(!empty($post) && isset($post->package_id))
			$package_price_info = geodir_get_post_package_info($post->package_id);
	}
	
	
	$package_id = isset($package_price_info['pid']) ? $package_price_info['pid'] : '';
	$payable_amount = isset($package_price_info['amount']) ? $package_price_info['amount'] : 0;
	$alive_days = isset($package_price_info['days']) ? $package_price_info['days'] : 0;
	$type_title = isset($package_price_info['title']) ? $package_price_info['title'] : '';
	$sub_active = isset($package_price_info['sub_active']) ? $package_price_info['sub_active'] : '';
	
	if($sub_active){
			$sub_units_num_var = $package_price_info['sub_units_num'];
			$sub_units_var = $package_price_info['sub_units'];
			if($sub_units_var=='D'){$alive_days = $sub_units_num_var; }
			if($sub_units_var=='W'){$alive_days = $sub_units_num_var * 7; }
			if($sub_units_var=='M'){$alive_days = $sub_units_num_var * 30; }
			if($sub_units_var=='Y'){$alive_days = $sub_units_num_var * 365; }
	}
	
	$org_payable_amount = $payable_amount;
	
	/* -------- START LISTING FORM MESSAGE*/
	ob_start();
	
		if(isset($_REQUEST['coupon_code']) && $_REQUEST['coupon_code']!='')
		{
			
			if(geodir_is_valid_coupon($post_type, $_REQUEST['coupon_code']))
			{
				$payable_amount = geodir_get_payable_amount_with_coupon($payable_amount,$_REQUEST['coupon_code']);
			}else
			{
				echo '<p class="error_msg_fix">'. WRONG_COUPON_MSG.'</p>';
			}
		}
	
		if($payable_amount > 0){ 
					
				if($alive_days==0){$alive_days = UNLIMITED;}
				echo '<h5 class="geodir_information">';
				printf(GOING_TO_PAY_MSG, geodir_get_currency_sym().$payable_amount,$alive_days,$type_title);
				echo '</h5>';
		
		}else{
			
				if($alive_days==0){$alive_days = UNLIMITED;}
				
				echo '<h5 class="geodir_information">';
				
				if(!isset($_REQUEST['pid']) || $_REQUEST['pid']=='')
					printf(GOING_TO_FREE_MSG, $type_title,$alive_days);
				else
					printf(GOING_TO_UPDATE_MSG, geodir_get_currency_sym().$payable_amount,$alive_days,$type_title);
				
				echo '</h5>';
		}
	
	 echo $form_message = ob_get_clean();
	 /* -------- END LISTING FORM MESSAGE*/
	 
	 
	 /* -------- START LISTING FORM PAYMENT OPTIONS*/
	 ob_start();
	 	
		?>
		<input type="hidden" name="price_select" value="<?php if(isset($package_id)){ echo $package_id;}?>" />
		<input type="hidden" name="coupon_code" value="<?php if(isset($_REQUEST['coupon_code'])){ echo $_REQUEST['coupon_code'];}?>" />
		<?php	
		
	 if($payable_amount > 0){
	
		if($sub_active){
		
			$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s order by option_id", array('payment_method_paypal'));
			
		} else {
			
			$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s order by option_id", array('payment_method_%'));
		}
		
		$paymentinfo = $wpdb->get_results($paymentsql);
		
		if($paymentinfo){?>
			
			<h5 class="geodir_payment_head"> <?php echo SELECT_PAY_MEHTOD_TEXT; ?></h5>
			<ul class="geodir_payment_method">
			
			<?php
			
			$paymentOptionArray = array();
			$paymethodKeyarray = array();
			
			foreach($paymentinfo as $paymentinfoObj){
				
				$paymentInfo = unserialize($paymentinfoObj->option_value);
				if($paymentInfo['isactive']){
					$paymethodKeyarray[] = $paymentInfo['key'];
					$paymentOptionArray[$paymentInfo['display_order']][] = $paymentInfo;
				}
				
			}
			
			ksort($paymentOptionArray);
			
			if($paymentOptionArray){
				
				foreach($paymentOptionArray as $key=>$paymentInfoval){
				
					for($i=0;$i<count($paymentInfoval);$i++){
					
						$paymentInfo = $paymentInfoval[$i];
						$jsfunction = 'onclick="showoptions(this.value);"';
						
						$chked = '';
						if($key==1)
						$chked = 'checked="checked"';
						
						?><li id="<?php echo $paymentInfo['key'];?>">
							<input <?php echo $jsfunction;?>  type="radio" value="<?php echo $paymentInfo['key'];?>" id="<?php echo $paymentInfo['key'];?>_id" name="paymentmethod" <?php echo $chked;?> />  <?php echo $paymentInfo['name']?>
							<?php 
							 if(file_exists(GEODIR_PAYMENT_MANAGER_PATH.$paymentInfo['key'].'/'.$paymentInfo['key'].'.php')) 
									include_once(GEODIR_PAYMENT_MANAGER_PATH.$paymentInfo['key'].'/'.$paymentInfo['key'].'.php');	?> 
						</li><?php
					}
				}
				
				if(isset($paymethodKeyarray)){
		?>
			<script type="application/x-javascript">
							function showoptions(paymethod){
					<?php for($i=0;$i<count($paymethodKeyarray);$i++) { ?>
	
							showoptvar = '<?php echo $paymethodKeyarray[$i]?>options';
							if(eval(document.getElementById(showoptvar)))
							{
								document.getElementById(showoptvar).style.display = 'none';
								if(paymethod=='<?php echo $paymethodKeyarray[$i]?>')
								{ document.getElementById(showoptvar).style.display = ''; }
							}
						
						<?php }	?>
				}
				
							<?php for($i=0;$i<count($paymethodKeyarray);$i++) { ?>
						if(document.getElementById('<?php echo $paymethodKeyarray[$i];?>_id').checked)
						{ showoptions(document.getElementById('<?php echo $paymethodKeyarray[$i];?>_id').value);}
							<?php }	?>
			</script>  
			 
			<?php }
				
			}else{?>
				<li><?php echo NO_PAYMENT_METHOD_MSG;?></li>
				<?php }?>
			</ul>
			<?php
		}
	
	}
	
	echo $html = ob_get_clean();
	
	/* -------- END LISTING FORM PAYMENT OPTIONS*/
	
	
	/* -------- START LISTING FORM BUTTON*/
	
	ob_start();
	
	if((!isset($_REQUEST['pid']) || $_REQUEST['pid']=='') && $payable_amount == 0){
		
		?> <input type="submit" name="Submit and Pay" value="<?php echo PRO_SUBMIT_BUTTON;?>" class="geodir_button geodir_publish_button" /><?php
		
	}elseif((isset($_REQUEST['pid']) && $_REQUEST['pid']!='') && $payable_amount == 0){
	
		?> <input type="submit" name="Submit and Pay" value="<?php echo PRO_UPDATE_BUTTON;?>" class="geodir_button geodir_publish_button" /><?php
		
	}elseif((isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != '') && $payable_amount > 0 && (!isset($_REQUEST['pid']) || $_REQUEST['pid']=='')){
		
		?><input type="submit" name="Submit and Pay" value="<?php echo PRO_SUBMIT_PAY_BUTTON;?>" class=" geodir_button geodir_publish_button" /><?php
		
	}elseif(isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != '' && $org_payable_amount > 0 && (isset($_REQUEST['pid']) || $_REQUEST['pid']!='')){
		
		$post_status = get_post_status( $_REQUEST['pid'] );
		
		if($post_status == 'draft'){
			?><input type="submit" name="Submit and Pay" value="<?php echo PRO_RENEW_BUTTON;?>" class="geodir_button geodir_publish_button" /><?php
		}else{
			?><input type="submit" name="Submit and Pay" value="<?php echo PRO_UPGRADE_BUTTON;?>" class="geodir_button geodir_publish_button" /><?php
		}
		
	}
	
	echo $listing_form_button = ob_get_clean();
	
	/* -------- END LISTING FORM BUTTON*/
	
	
	
	/* -------- START LISTING GO BACK LINK*/
	
	
	$post_id = '';
	if(isset($post->pid)){
		$post_id = $post->pid;
	}elseif(isset($_REQUEST['pid'])){
		$post_id = $_REQUEST['pid'];
	}
	
	$postlink = get_permalink( get_option('geodir_add_listing_page') );
	
	$postlink = geodir_getlink($postlink,array('pid'=>$post_id,'backandedit'=>'1','listing_type'=>$post_type),false);
	
	
	if(isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != ''){
		
		$postlink = geodir_getlink($postlink,array('package_id'=>$_REQUEST['package_id']),false);
	}
	
	
	ob_start();	 
		?>
			<a href="<?php echo $postlink;?>" class="geodir_goback" ><?php echo PRO_BACK_AND_EDIT_TEXT;?></a>
			<input type="button" name="Cancel" value="<?php echo (PRO_CANCEL_BUTTON);?>" class="geodir_button cancle_button"  onclick="window.location.href='<?php echo geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=cancel&pid='.$post_id.'&listing_type='.$post_type;?>'" />
		<?php

	echo $listing_form_go_back = ob_get_clean();
	
	
	
	

}


add_action('init', 'payment_handler');

function payment_handler(){
	
	if(isset($_REQUEST['geodir_ajax']) && $_REQUEST['geodir_ajax'] == 'add_listing'){
		
		switch($_REQUEST['ajax_action']):
			
			case "paynow" :
				
				$request = isset($_SESSION['listing']) ? unserialize($_SESSION['listing']) : '';
				
				if(isset($request['geodir_spamblocker']) && $request['geodir_spamblocker']=='64' && isset($request['geodir_filled_by_spam_bot']) && $request['geodir_filled_by_spam_bot']=='')
				{
					
					if(isset($_REQUEST['paymentmethod']) && isset($_SESSION['listing'])):
						
						$last_id = geodir_save_listing();
						
						$invoice_id = geodir_create_invoice($last_id,$_REQUEST['price_select'],$_REQUEST['paymentmethod'],$_REQUEST['coupon_code']);
						
						geodir_update_invoice_status($invoice_id,'unpaid'); 
						$paymentmethod = $_REQUEST['paymentmethod'];
						do_action('geodir_payment_form_handler_' . $paymentmethod  , $invoice_id);
					
					else: 
						
						 $postlink = get_permalink( get_option('geodir_add_listing_page') );
						 $redirect_url = geodir_getlink($postlink,array('listing_type'=>$_REQUEST['listing_type']),false);
						 wp_redirect($redirect_url); 
						
					endif;
				
			}else{
						
				if(isset($_SESSION['listing']))
					unset($_SESSION['listing']);
				wp_redirect( home_url() );
			
			}
				
			break;
			
		endswitch;
	
	}

}


add_action('geodir_after_save_listing', 'geodir_save_listing_payment', 2, 2);

function geodir_save_listing_payment($last_post_id,$request_info){
	
	$payment_info = array();
	$package_info = array();
	
	if(isset($request_info['alive_days']) && isset($request_info['expire_date'])  ){
	
		if($request_info['alive_days'] > 0){
			
			$old_alive_days = geodir_get_post_meta($last_post_id,'alive_days',true);
			$old_expire_date = $request_info['expire_date'];
			
			$actual_date = date('Y-m-d');
			
			if($old_alive_days > 0){
				$actual_date = date('Y-m-d', strtotime($old_expire_date."-".$old_alive_days." days"));
			}
			
			$payment_info['expire_date'] = date('Y-m-d', strtotime($actual_date."+".$request_info['alive_days']." days"));
			
		}else{
			
			$payment_info['expire_date'] = 'Never';
		}
		
		$payment_info['alive_days'] = $request_info['alive_days'];
		$payment_info['package_id'] = $request_info['package_id'];
		$payment_info['is_featured'] = $request_info['is_featured'];
		
	}
	
	
	if(isset($request_info['package_id']) && $request_info['package_id'] != '' && empty($payment_info)){
		
		$package_info = (array)geodir_get_package_info($request_info['package_id']);
		
		if(!empty($package_info)){	
		
			if(isset($package_info['sub_active']) && $package_info['sub_active']=='1' && isset($package_info['sub_units_num']) && $package_info['sub_units_num']>0){
				
				if($package_info['sub_units']=='D'){$mult = 1;}
				if($package_info['sub_units']=='W'){$mult = 7;}
				if($package_info['sub_units']=='M'){$mult = 30;}
				if($package_info['sub_units']=='Y'){$mult = 365;}
				$pay_days = ($package_info['sub_units_num']*$mult);
				$payment_info['expire_date'] = date('Y-m-d', strtotime("+".$pay_days." days"));
				$payment_info['alive_days'] = $pay_days;
				
			}elseif(isset($package_info['days']) && $package_info['days'] != 0){
			
				$payment_info['expire_date'] = date('Y-m-d', strtotime("+".$package_info['days']." days"));
				$payment_info['alive_days'] = $package_info['days'];
			}else{$payment_info['expire_date'] = 'Never'; $payment_info['alive_days'] = $package_info['days'];}
			
				$payment_info['package_id'] = $package_info['pid'];
				
				$payment_info['is_featured'] = $package_info['is_featured'];	
		}
		
	}
	
	
	$payment_info['expire_notification'] = 'false';
	
	if(!empty($payment_info))
		geodir_save_post_info($last_post_id, $payment_info);
		
}


add_action('geodir_payment_invoice_created', 'geodir_payment_detail_fields_update', 1, 1);

function geodir_payment_detail_fields_update($invoice_id){
	
	$invoice_info = geodir_get_invoice($invoice_id);
	
	if(!empty($invoice_info)){
	
		$payment_info = array();
		$payment_info['paymentmethod'] = $invoice_info->paymentmethod;
		$payment_info['paid_amount'] = $invoice_info->paied_amount;
		geodir_save_post_info($invoice_info->post_id, $payment_info);
		
		if($payment_info['paid_amount'] > 0){
			
			$post['ID'] = $invoice_info->post_id;
			$post['post_status'] = 'draft';
			$last_post_id =  wp_update_post( $post );
			
		}
		
	}
	
}


add_action('before_delete_post','geodir_payment_delete_listing_info', 1, 2);

function geodir_payment_delete_listing_info($deleted_postid, $force = false){
	
	global $wpdb;
	
	$post_type = get_post_type( $deleted_postid );
	
	$all_postypes = geodir_get_posttypes();

	if(!in_array($post_type, $all_postypes))
		return false;
			
	$wpdb->query($wpdb->prepare("DELETE FROM ".INVOICE_TABLE." WHERE status = 'pending' AND `post_id` = %d", array($deleted_postid)));
	
} 


add_action( 'add_meta_boxes', 'geodir_package_meta_box_add', 0, 2 );
function geodir_package_meta_box_add()
{	
	global $post;
	
	$geodir_post_types = geodir_get_posttypes('array');
	$geodir_posttypes = array_keys($geodir_post_types);
	if( isset($post->post_type) &&  in_array($post->post_type,$geodir_posttypes) ):
	
		$geodir_posttype = $post->post_type;
		$post_typename = ucwords($geodir_post_types[$geodir_posttype]['labels']['singular_name']);
		
		add_meta_box( 'geodir_post_package_setting', $post_typename.' Package Settings', 'geodir_post_package_setting', $geodir_posttype,'side', 'high' );
	
	endif;
	
}


function geodir_post_package_setting(){
	global $post,$post_id, $package_id;
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_package_setting_noncename' );
	
	$package_price_info = geodir_package_list_info($post->post_type);
	
	if(isset($_REQUEST['package_id']))
	{
		$package_id = $_REQUEST['package_id'];
	}
	elseif($post_package_id = geodir_get_post_meta($post_id,'package_id') )
	{
		$package_id = $post_package_id;	
	}
	else{
		foreach($package_price_info as $pck_val )
		{
			if($pck_val->is_default){
				$package_id = $pck_val->pid;
			}	
		}
	}	
	?>
		
	<div class="misc-pub-section" >
		<h4 style="display:inline;"><?php echo SELECT_PACKAGE_TEXT;?></h4>
		<?php 
		
			foreach($package_price_info as $pkg){ 
			
				$checkbox_alive_days = 'unlimited';
				$post_pkg_link = '';
				if($pkg->days)
					$checkbox_alive_days = $pkg->days;
				
				$post_pkg_link = get_edit_post_link( $post_id ).'&package_id='.$pkg->pid;
				
				?>
				<div class="gd-package" style="width:100%; margin:5px 0px;">
				<input class="gd-checkbox"  name="package_id" type="radio" value="<?php echo $pkg->pid;?>"  <?php if($package_id == $pkg->pid) echo 'checked="checked"';?> onclick="window.location.href='<?php echo $post_pkg_link;?>'">
				<?php 
				_e(stripslashes($pkg->title_desc), GEODIRPAYMENT_TEXTDOMAIN);
				
				?>
				</div>
				
			<?php } ?>	
			
	</div>
	<?php
	
	if(geodir_get_post_meta($post_id, 'alive_days',true) != '')
		$alive_days = geodir_get_post_meta($post_id, 'alive_days',true);
	
	if(geodir_get_post_meta($post_id, 'is_featured',true) != '')
		$is_featured = geodir_get_post_meta($post_id, 'is_featured',true);
	
	if(geodir_get_post_meta($post_id, 'expire_date',true) != '')		
		$expire_date = geodir_get_post_meta($post_id,'expire_date',true);
	?>
    
     <div class="misc-pub-section">
        <h4 style="display:inline;"><?php _e('Alive Days:', GEODIRPAYMENT_TEXTDOMAIN); ?></h4>
        <input type="text" name="alive_days" value="<?php if(isset($alive_days)){ echo $alive_days;} else{echo '0';};?>"  />
		<br />
        <h4 style="display:inline;"><?php _e('Expire Date:', GEODIRPAYMENT_TEXTDOMAIN); ?>(ie: YYYY-MM-DD)</h4>
		<input type="text" name="expire_date" value="<?php if(isset($expire_date)){ echo $expire_date;} else{echo 'Never';};?>" />
        
    </div>
    
   
    
    <div class="misc-pub-section">
        <h4 style="display:inline;"><?php _e('Is Featured:', GEODIRPAYMENT_TEXTDOMAIN); ?></h4>
        
                <input type="radio" class="gd-checkbox" name="is_featured" id="is_featured_yes" <?php if(isset($is_featured) && $is_featured=='1' ){echo 'checked="checked"';}?>  value="1"   /> <?php _e('Yes', GEODIRPAYMENT_TEXTDOMAIN);?>
                <input type="radio" class="gd-checkbox" name="is_featured" id="is_featured_no" <?php if((isset($is_featured) && $is_featured=='0') || !isset($is_featured)){echo 'checked="checked"';}?> value="0"   /> <?php _e('No', GEODIRPAYMENT_TEXTDOMAIN);?>
           
        
    </div><?php
	 
}



add_filter('geodir_packages_list_on_custom_fields','geodir_pay_packages_list_on_custom_fields',1, 2);

function geodir_pay_packages_list_on_custom_fields( $html, $field_info){
	
	$field_display = '';
	if(isset($field_info->is_admin) && $field_info->is_admin == '1' && ($field_info->field_type == 'taxonomy' || $field_info->field_type == 'address') ){
		$field_display = 'style="display:none;"';
	}
	
	?>
	<tr <?php echo $field_display;?> >
			<td ><strong><?php _e('Show only on these price packages ? :', GEODIRPAYMENT_TEXTDOMAIN);?></strong></td>
			<td align="left">
					
					<select name="show_on_pkg[]" id="show_on_pkg" multiple="multiple" style="height: 100px; width:90%;">
							<?php 
							$priceinfo = geodir_package_list_info($_REQUEST['listing_type']);
							$pricearr = array();
							if(isset($field_info->packages) && $field_info->packages)
							{
									$pricearr = explode(',',$field_info->packages);   
							}
							foreach($priceinfo as $priceinfoObj){?>	  
									<option value="<?php echo $priceinfoObj->pid; ?>" <?php if (in_array($priceinfoObj->pid, $pricearr)){ echo 'selected="selected"';}?>><?php echo '#'.$priceinfoObj->pid.': '.$priceinfoObj->title;?></option>
							<?php }  ?>
					</select>
					
					<br />    <span><?php _e('Want to show only on these price packages ? (Select multiple price packages by holding down "Ctrl" key.)', GEODIRPAYMENT_TEXTDOMAIN);?></span>
					
			</td>
	</tr>
	<?php
}




add_filter('geodir_add_custom_sort_options', 'geodir_package_add_custom_sort_options', 2, 2);

function geodir_package_add_custom_sort_options($fields, $post_type){
	
	$fields[] = array(
										'post_type' => $post_type,
										'data_type' => '',
										'field_type' => 'enum',
										'site_title' => 'Featured',
										'htmlvar_name' => 'is_featured'
								);
	
	return $fields;
}


/* ----------- Updated package table(new field add sendtofriend in package table) */

add_action('wp', 'geodir_changes_in_package_table');
add_action('wp_admin', 'geodir_changes_in_package_table');

function geodir_changes_in_package_table(){
	
	global $wpdb,$plugin_prefix;
	
	if(!get_option('geodir_changes_in_package_table')){
		
		if(!$wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_PRICE_TABLE." WHERE field = 'sendtofriend'")){
			$wpdb->query("ALTER TABLE `".GEODIR_PRICE_TABLE."` ADD `sendtofriend` TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER `google_analytics`");
		}
		
		update_option('geodir_changes_in_package_table', '1');
		
	}
	
}


// add a row for diagnostic too 
add_action('geodir_diagnostic_tool' , 'geodir_add_payment_diagnostic_tool' , 1);
function geodir_add_payment_diagnostic_tool()
{
?>
	<tr valign="top" >
                           
        <td class="forminp"><?php _e('Geodirectory payment method\'s options diagnosis',GEODIRPAYMENT_TEXTDOMAIN);?>
        <input type="button" value="<?php _e('Run',GEODIRPAYMENT_TEXTDOMAIN);?>" class="geodir_diagnosis_button" data-diagnose="payment_method_options" />
        <div class="geodir_diagnostic_result"></div>        
        </td>
        
    </tr>
<?php
}

?>
<?php

add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_payment_manager', 10,1); 
function geodir_diagnose_multisite_conversion_payment_manager($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_invoice'] = __('Invoice',GEODIRPAYMENT_TEXTDOMAIN);
	$table_arr['geodir_price'] = __('Price',GEODIRPAYMENT_TEXTDOMAIN);
	$table_arr['geodir_coupons'] = __('Coupons',GEODIRPAYMENT_TEXTDOMAIN);
	return $table_arr;
}

//==========GEODIR PAYMENT MODULE ACTIVATION CODE===============

function geodir_payment_activation()
{
	// First check if geodir main pluing is active or not.
	if (get_option('geodir_installed')) {
		
		geodir_payment_activation_script();
	
		geodir_update_options(geodir_payment_notifications(), true);
		
		geodir_update_options(geodir_payment_general_options(), true);
		
		add_option('geodir_payment_manager_activation_redirect', 1);
		
	}
	
}


// This function is used to create geodirteory payment manager navigation 
function geodir_payment_manager_tabs($tabs){
	$tabs['paymentmanager_fields'] = array( 'label' =>__( 'Prices and Payments', GEODIRPAYMENT_TEXTDOMAIN ),
									'subtabs' => array(
													array('subtab' => 'geodir_payment_general_options',
																'label' =>__( 'General', GEODIRPAYMENT_TEXTDOMAIN),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_payment_manager',
																'label' =>__( 'Prices', GEODIRPAYMENT_TEXTDOMAIN),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_payment_options',
																'label' =>__( 'Payments', GEODIRPAYMENT_TEXTDOMAIN),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_invoice_list',
																'label' =>__( 'Invoices', GEODIRPAYMENT_TEXTDOMAIN),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_coupon_manager',
																'label' =>__( 'Coupons', GEODIRPAYMENT_TEXTDOMAIN),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'payment_notifications',
																'label' =>__( 'Notifications', GEODIRPAYMENT_TEXTDOMAIN),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax'))
													
													)
				);
	return $tabs; 
}


function geodir_payment_general_options_form($current_tab)
{
	$current_tab = $_REQUEST['subtab'];
	geodir_payment_option_form($current_tab); // function in geodir_payment_template_functions.php
}


function geodir_get_payment_notifications_form($current_tab)
{
	$current_tab = $_REQUEST['subtab'];
	geodir_payment_option_form($current_tab); // function in geodir_payment_template_functions.php
}


function geodir_payment_manager_tab_content()
{
	global $wpdb;
?>
	
	<?php
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_general_options')
	{	
		add_action('geodir_admin_option_form', 'geodir_payment_general_options_form');
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_manager')
	{
		if(isset($_REQUEST['gd_pagetype']) && $_REQUEST['gd_pagetype']=='addeditprice')
		{
			geodir_package_price_form();
		}
		else
		{
			geodir_package_price_list();
		}
	}	
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_options')
	{	
		if(isset($_REQUEST['gd_payact']) && $_REQUEST['gd_payact']=='gd_setting')
		{
			geodir_payment_gateway_setting_form();
		}
		else
		{
			geodir_payment_gateways_list();
		}
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_invoice_list')
	{
		geodir_payment_invoice_list();
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_coupon_manager')
	{
		if(isset($_REQUEST['gd_pagetype']) && $_REQUEST['gd_pagetype']=='addeditcoupon')
		{
			geodir_payment_coupon_form();
		}
		else
		{
			geodir_payment_coupon_list();
		}
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'payment_notifications')
	{
		add_action('geodir_admin_option_form', 'geodir_get_payment_notifications_form');	
	}	
		
}

//=========ADD BACKEND FORM CSS FUNCTION===========
function geodir_admincss_payment_manager(){
	global $pagenow;
	
	if($pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'paymentmanager_fields'){
	//Style
	wp_register_style('payment-plugin-style', plugins_url('',__FILE__).'/css/geodir-payment-manager.css');
	wp_enqueue_style('payment-plugin-style');
	
	}
}

function geodir_payment_admin_scripts(){	
	global $pagenow;
	if($pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'paymentmanager_fields'))
	
	wp_register_script( 'geodirectory-payment-admin-script', plugins_url('/js/payment-script.js',__FILE__));
	wp_enqueue_script( 'geodirectory-payment-admin-script');
	
}

function geodir_payment_create_new_post_type($post_type = ''){
	
	global $wpdb, $plugin_prefix;
	
	if($post_type != ''){
	
		$all_postypes = geodir_get_posttypes();
	
		if(!in_array($post_type, $all_postypes))
			return false;
		
		$package_info = geodir_get_post_package_info_on_listing('', '', $post_type);
		
		$package_id = $package_info->pid;
		
		$table = $plugin_prefix.$post_type.'_detail';
		
		$wpdb->query($wpdb->prepare("UPDATE ".$table." SET package_id=%d WHERE package_id=0",array($package_id)));
		
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages=%d WHERE post_type=%s AND (packages='0' || packages='')",
				array($package_id,$post_type)
			)
		);
		
		$meta_field_add = " ENUM( 'false', 'true' ) NOT NULL ";
		geodir_add_column_if_not_exist( $table, "expire_notification", $meta_field_add );
		
	}
}


function geodir_payment_delete_post_type($post_type = ''){

	global $wpdb, $plugin_prefix;
	
	if($post_type != ''){
		
		$all_postypes = geodir_get_posttypes();
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_PRICE_TABLE." WHERE post_type=%s", array($post_type)));
		
		$coupon_data = $wpdb->get_results($wpdb->prepare("SELECT cid, post_types FROM ".COUPON_TABLE." WHERE FIND_IN_SET(%s, post_types)", array($post_type)));
		
		if(!empty($coupon_data)){
			
			foreach($coupon_data as $key => $coupon){
			
				$coupons = explode(",",$coupon->post_types);
				
				if(($del_key = array_search($post_type, $coupons)) !== false)
					unset($coupons[$del_key]);
				
				if(!empty($coupons)){
					
					$coupons = implode(',',$coupons);
					
					$wpdb->query($wpdb->prepare("UPDATE ".COUPON_TABLE." SET post_types=%s WHERE cid=%d",array($coupons,$coupon->cid)));
					
				}else{
					
					$wpdb->query($wpdb->prepare("DELETE FROM ".COUPON_TABLE." WHERE cid=%d", array($coupon->cid)));
					
				}
					
			}
			
		}
	
	}
}

function geodir_payment_activation_redirect(){
	if (get_option('geodir_payment_manager_activation_redirect', false))
	{
		delete_option('geodir_payment_manager_activation_redirect');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_general_options')); 
	}
}


//==========GEODIR PAYMENT MODULE UNINSTALLATION CODE===============

function geodir_payment_uninstall()
{

if ( ! isset($_REQUEST['verify-delete-adon']) ) 
	{
		$plugins = isset( $_REQUEST['checked'] ) ? (array) $_REQUEST['checked'] : array();
			//$_POST = from the plugin form; $_GET = from the FTP details screen.
			
			wp_enqueue_script('jquery');
					require_once(ABSPATH . 'wp-admin/admin-header.php');
					printf( '<h2>%s</h2>' ,__( 'Warning!!' , GEODIRPAYMENT_TEXTDOMAIN) );
					printf( '%s<br/><strong>%s</strong><br /><br />%s <a href="http://wpgeodirectory.com">%s</a>.' , __('You are about to delete a Geodirectory Adon which has important option and custom data associated to it.' ,GEODIRPAYMENT_TEXTDOMAIN) ,__('Deleting this and activating another version, will be treated as a new installation of plugin, so all the data will be lost.', GEODIRPAYMENT_TEXTDOMAIN), __('If you have any problem in upgrading the plugin please contact Geodirectroy', GEODIRPAYMENT_TEXTDOMAIN) , __('support' ,GEODIRPAYMENT_TEXTDOMAIN) ) ;
					
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
						<?php submit_button(  __( 'Delete plugin files only' , GEODIRPAYMENT_TEXTDOMAIN ), 'button', 'submit', false ); ?>
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
						<?php submit_button(  __( 'Delete both plugin files and data' , GEODIRPAYMENT_TEXTDOMAIN) , 'button', 'submit', false ); ?>
					</form>
					
	<?php
		require_once(ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}
	
	
	if ( isset($_REQUEST['verify-delete-adon-data']) ) 
	{	
		global $wpdb,$plugin_prefix;
		
		$post_types = geodir_get_posttypes();
		
		if(!empty($post_types)){
		
			foreach($post_types as $post_type){
				
				$table = $plugin_prefix.$post_type.'_detail';
				
				$wpdb->query("UPDATE ".$table." SET package_id='0',alive_days='0', paymentmethod='0', expire_date='Never', is_featured='0', paid_amount='0'");
				
				
				if($wpdb->get_var("SHOW COLUMNS FROM ".$table." WHERE field = 'expire_notification'"))
					$wpdb->query("ALTER TABLE ".$table." DROP expire_notification");
					
			}	
			
		}
		
		$wpdb->query("UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages='0'"); 
		
		$wpdb->query("DROP TABLE ".$plugin_prefix.'price');
		
		$wpdb->query("DROP TABLE ".$plugin_prefix.'coupons');
		
		$wpdb->query("DROP TABLE ".$plugin_prefix.'invoice');
		
		
		$paymentinfo = $wpdb->get_results($wpdb->prepare("select option_id from ".$wpdb->prefix."options where option_name like %s",array('payment_method_%')));
			
		if(!empty($paymentinfo)){
			
			foreach($paymentinfo as $payment){
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM ".$wpdb->prefix."options WHERE option_id=%d ",
						array($payment->option_id)
					)
				);
			}
			
		}
		
		/* --- delete coupon options --- */
		delete_option('geodir_allow_coupon_code', '');
		
		/* --- delete notification options --- */
		
		$notifications = geodir_payment_notifications();
		
		if(!empty($notifications)){
			foreach($notifications as $value){
				if(isset($value['id']) && $value['id'] != '')
					delete_option($value['id'], '');
			}
		}
		
		/* --- delete default options --- */
		
		$default_options = geodir_payment_general_options();
		
		if(!empty($default_options)){
			foreach($default_options as $value){
				if(isset($value['id']) && $value['id'] != '')
					delete_option($value['id'], '');
			}
		}
		
		/* --- delete custom sort options --- */
		if (defined('GEODIR_CUSTOM_SORT_FIELDS_TABLE'))	
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." WHERE htmlvar_name=%s",array('is_featured')));
		
	}
	
}


function geodir_payment_deactivation(){
	global $wpdb,$plugin_prefix;
	
	/* --- delete custom sort options --- */
	if (defined('GEODIR_CUSTOM_SORT_FIELDS_TABLE'))	
	$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." WHERE htmlvar_name=%s",array('is_featured')));
	
}


function geodir_get_additional_pay_options($pay_method = ''){
	
	
	$paymenthodinfo = array();
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Merchant Id", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"merchantid",
	"value"			=>	"myaccount@paypal.com",
	"description"	=>	__("Example : myaccount@paypal.com", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Cancel Url", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"cancel_return",
	"value"			=>	home_url()."/?pay_action=cancel&pmethod=paypal",
	"description"	=>	__("Example : http://mydomain.com/cancel_return.php", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Return Url", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"returnUrl",
	"value"			=>	home_url()."/?pay_action=return&pmethod=paypal",
	"description"	=>	__("Example : http://mydomain.com/return.php", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Notify Url", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"notify_url",
	"value"			=>	home_url()."/?pay_action=ipn&pmethod=paypal",
	"description"	=>	__("Example : http://mydomain.com/notifyurl.php", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$paymenthodinfo['paypal'] = array(
	"name" 		=> __('Paypal', GEODIRPAYMENT_TEXTDOMAIN),
	"key" 		=> 'paypal',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'1',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_paypal_options' ,$payOpts),
	);
	
	//////////google checkout start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Merchant Id", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"merchantid",
	"value"			=>	"1234567890",
	"description"	=>	__("Example : 1234567890", GEODIRPAYMENT_TEXTDOMAIN)
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Merchant Secret", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"merchantsecret",
	"value"			=>	"",
	"description"	=>	__("Example : qk7hAPoe4Fr6eECzPRjFKA", GEODIRPAYMENT_TEXTDOMAIN)
	);
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Notify URL", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"merchantipn",
	"value"			=>	home_url()."/?pay_action=ipn&pmethod=googlewallet",
	"description"	=>	__("Important :You MUST set the Postback URL in your Google Wallet settings to: ", GEODIRPAYMENT_TEXTDOMAIN).home_url()."/?pay_action=ipn&pmethod=googlewallet"
	);
	
	$paymenthodinfo['googlechkout'] = array(
	"name" 		=> __('Google Wallet', GEODIRPAYMENT_TEXTDOMAIN),
	"key" 		=> 'googlechkout',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'2',
	"payment_mode"=>'live',
	"payOpts"	=>apply_filters('geodir_payment_googlechkout_options' ,$payOpts) ,
	);
	
			
	//////////authorize.net start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Login ID", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"loginid",
	"value"			=>	"yourname@domain.com",
	"description"	=>	__("Example : yourname@domain.com", GEODIRPAYMENT_TEXTDOMAIN)
	);
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Transaction Key", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"transkey",
	"value"			=>	"1234567890",
	"description"	=>	__("Example : 1234567890", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$paymenthodinfo['authorizenet'] = array(
	"name" 		=> __('Authorize.net', GEODIRPAYMENT_TEXTDOMAIN),
	"key" 		=> 'authorizenet',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'3',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_authorizenet_options' ,$payOpts),
	);
	
	//////////worldpay start////////
	
	$payOpts = array();	

	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Instant Id", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"instId",
	"value"			=>	"123456",
	"description"	=>	__("Example : 123456", GEODIRPAYMENT_TEXTDOMAIN)
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account Id", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"accId1",
	"value"			=>	"12345",
	"description"	=>	__("Example : 12345", GEODIRPAYMENT_TEXTDOMAIN)
	);
	
	$paymenthodinfo['worldpay'] = array(
	"name" 		=> __('Worldpay', GEODIRPAYMENT_TEXTDOMAIN),
	"key" 		=> 'worldpay',
	"isactive"	=>	'1', // 1->display,0->hide\
	"display_order"=>'4',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_worldpay_options' ,$payOpts),
	);
	
	//////////2co start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Vendor ID", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"vendorid",
	"value"			=>	"1303908",
	"description"	=>	__("Enter Vendor ID Example : 1303908", GEODIRPAYMENT_TEXTDOMAIN)
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Notify Url", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"ipnfilepath",
	"value"			=>	home_url()."/?pay_action=notifyurl&pmethod=2co",
	"description"	=>	__("Example : http://mydomain.com/2co_notifyurl.php", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$paymenthodinfo['2co'] = array(
	"name" 		=> __('2CO (2Checkout)', GEODIRPAYMENT_TEXTDOMAIN),
	"key" 		=> '2co',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'5',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_2co_options' ,$payOpts),
	);
	
	//////////pre bank transfer start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account Name", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"bankinfo",
	"value"			=>	"ICICI Bank",
	"description"	=>	__("Enter the bank name to which you want to transfer payment", GEODIRPAYMENT_TEXTDOMAIN)
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account SC", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"bank_accountsc",
	"value"			=>	"11-22-33",
	"description"	=>	__("Enter your bank Account Sort Code", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account No", GEODIRPAYMENT_TEXTDOMAIN),
	"fieldname"		=>	"bank_accountid",
	"value"			=>	"AB1234567890",
	"description"	=>	__("Enter your bank Account Number", GEODIRPAYMENT_TEXTDOMAIN),
	);
	
	$paymenthodinfo['prebanktransfer'] = array(
	"name" 		=> __('Pre Bank Transfer', GEODIRPAYMENT_TEXTDOMAIN),
	"key" 		=> 'prebanktransfer',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'6',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_prebanktransfer_options' ,$payOpts),
	);

	
	if($pay_method != ''){
	
		return isset($paymenthodinfo[$pay_method]) ? $paymenthodinfo[$pay_method] : '';
	
	}
	return $paymenthodinfo;

}


function geodir_payment_activation_script()
{
	global $wpdb,$plugin_prefix;
	
	$wpdb->hide_errors();
	
	if($wpdb->query("SHOW TABLES LIKE 'geodir_price'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_price'")==0){$wpdb->query("RENAME TABLE geodir_price TO ".$wpdb->prefix."geodir_price");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_invoice'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_invoice'")==0){$wpdb->query("RENAME TABLE geodir_invoice TO ".$wpdb->prefix."geodir_invoice");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_coupons'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_coupons'")==0){$wpdb->query("RENAME TABLE geodir_coupons TO ".$wpdb->prefix."geodir_coupons");}
	
	
	$collate = '';
	if($wpdb->has_cap( 'collation' ) ) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}
		
// Table for storing place packages  - these are user defined
	if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_PRICE_TABLE."'") != GEODIR_PRICE_TABLE)
	{
		$price_table = "CREATE TABLE IF NOT EXISTS ".GEODIR_PRICE_TABLE." (
					`pid` int(11) NOT NULL AUTO_INCREMENT,
					`title` varchar(255) NOT NULL,
					`amount` float(12,2) NOT NULL,
					`cat` varchar(255) NOT NULL,
					`status` tinyint(2) NOT NULL DEFAULT '1',
					`days` int(10) NOT NULL,
					`is_default` tinyint(4) NOT NULL DEFAULT '0',
					`is_featured` tinyint(4) NOT NULL DEFAULT '0',
					`title_desc` text NOT NULL,
					`image_limit` varchar(255) NOT NULL,
					`cat_limit` varchar(255) NOT NULL,
					`post_type` varchar(255) NOT NULL,
					`link_business_pkg` varchar(255) NOT NULL,
					`recurring_pkg` varchar(255) NOT NULL,
					`reg_desc_pkg` varchar(255) NOT NULL,
					`reg_fees_pkg` varchar(255) NOT NULL,
					`downgrade_pkg` varchar(255) NOT NULL,
					`sub_active` varchar(255) NOT NULL,
					`display_order` INT( 11 ) NOT NULL DEFAULT '0',
					`sub_units` varchar(255) NOT NULL,
					`sub_units_num` varchar(255) NOT NULL,
					`sub_num_trial_days` varchar(255) NOT NULL,
					`sub_num_trial_units` varchar(1) NOT NULL DEFAULT 'D',
					`sub_units_num_times` varchar(255) NOT NULL,
					`google_analytics` TINYINT( 4 ) NOT NULL DEFAULT '0',
					`sendtofriend` TINYINT( 4 ) NOT NULL DEFAULT '0',
					`use_desc_limit` TINYINT( 1 ) NOT NULL DEFAULT '0',
					`desc_limit` INT( 11 ) NOT NULL DEFAULT '0',
					`use_tag_limit` TINYINT( 1 ) NOT NULL DEFAULT '0',
					`tag_limit` INT( 11 ) NOT NULL DEFAULT '0',
					PRIMARY KEY (`pid`)) $collate";
					
		$price_table = apply_filters('geodir_payment_package_table' , $price_table);	
		$wpdb->query($price_table);
		do_action('geodir_payment_package_table_created' ,$price_table ) ;
		
	}
	
	/* ------- update post detail table start --- */
	$post_types = geodir_get_posttypes();
	
	if(!empty($post_types)){
	
		foreach($post_types as $post_type){
			
			$package_info = geodir_get_post_package_info_on_listing('', '', $post_type);
			
			$package_id = $package_info->pid;
			
			$table = $plugin_prefix.$post_type.'_detail';
			
			$wpdb->query($wpdb->prepare("UPDATE ".$table." SET package_id=%d WHERE package_id=0",array($package_id)));
			
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages=%d WHERE post_type=%s AND (packages='0' || packages='')",
					array($package_id,$post_type)
				)
			);
			
			
			$meta_field_add = " ENUM( 'false', 'true' ) NOT NULL ";
			geodir_add_column_if_not_exist( $table, "expire_notification", $meta_field_add );
			
			
		}	
		
	}	
		
	// Table for storing place packages  - these are user defined
	if($wpdb->get_var("SHOW TABLES LIKE '".INVOICE_TABLE."'") != INVOICE_TABLE)
	{
		$invoice_table = "CREATE  TABLE  IF NOT EXISTS ".INVOICE_TABLE." (  
						 `id` int( 11  )  NOT  NULL  auto_increment ,
						 `type` varchar( 100  )  default NULL ,
						 `post_id` int( 11  )  NOT  NULL ,

						 `post_title` varchar( 255  )  default NULL ,
						 `post_action` varchar( 100  )  default NULL ,
						 `package_id` int( 11  )  NOT  NULL ,
						 `package_title` varchar( 254  )  default NULL ,
						 `amount` float  default NULL ,
						 `alive_days` int( 11  )  default NULL ,
						 `expire_date` datetime  default NULL ,
						 `user_id` int( 11  )  default NULL ,
						 `coupon_code` varchar( 100  )  default NULL ,
						 `discount` float  default NULL ,
						 `paied_amount` float  default NULL ,
						 `paymentmethod` varchar( 100  )  default NULL ,
						 `status` varchar( 100  )  default NULL ,
						 `HTML` text,
						 `is_current` enum(  '1',  '0'  ) default  '0',
						 `date` datetime  default NULL ,
						 PRIMARY  KEY (  `id`  )  ) $collate";
		$invoice_table = apply_filters('geodir_payment_invoice_table' , $invoice_table)		;	
		$wpdb->query($invoice_table);
		do_action('geodir_payment_invoice_table_created' ,$invoice_table ) ;
	}
	
	
	// Table for storing coupon  - these are user defined
	if($wpdb->get_var("SHOW TABLES LIKE '".COUPON_TABLE."'") != COUPON_TABLE)
	{
		$coupon_table = "CREATE TABLE IF NOT EXISTS `".COUPON_TABLE."` (
		`cid` int(11) NOT NULL AUTO_INCREMENT,
		`coupon_code` varchar(100) NOT NULL,
		`post_types` varchar(255) NOT NULL,
		`recurring` varchar(255) NOT NULL DEFAULT '0',
		`discount_type` varchar(5) NOT NULL,
		`discount_amount` float NOT NULL,
		`status` enum('0','1') NOT NULL DEFAULT '0',
		PRIMARY KEY (`cid`  )  ) $collate";
					
		$coupon_table = apply_filters('geodir_payment_coupon_table' , $coupon_table);	
		$wpdb->query($coupon_table);
		do_action('geodir_payment_coupon_table_created' ,$coupon_table ) ;
		
	}
	
	
	
	$meta_field_add = " varchar(255) NOT NULL DEFAULT '0' ";
	geodir_add_column_if_not_exist( COUPON_TABLE, "recurring", $meta_field_add );
	
	
	//////////paypal settings start////////
	
	
	$paymenthodinfo = geodir_get_additional_pay_options();
	
	if(!empty($paymenthodinfo)){
	
		for($i=0;$i<count($paymenthodinfo);$i++)
		foreach($paymenthodinfo as $key => $value)
		{
			
			$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s order by option_id asc", array('payment_method_'.$key));
			
			$paymentinfo = $wpdb->get_results($paymentsql);
			
			if(empty($paymentinfo))
			{
				$value = apply_filters('geodir_payment_'.$key.'_payment_method' ,$value);
				$paymenthodArray = array("option_name"	=>	'payment_method_'.$key, "option_value"	=>	serialize($value),);
				
				$wpdb->insert( $wpdb->options, $paymenthodArray );
			}
		}
		
	}
		
}


function geodir_payment_after_custom_detail_table_create($post_type, $detail_table){
	
	$post_types = geodir_get_posttypes();
	
	if(in_array($post_type, $post_types)){
		$meta_field_add = " ENUM( 'false', 'true' ) NOT NULL ";
		geodir_add_column_if_not_exist( $detail_table, "expire_notification", $meta_field_add );
	}
	
}


function get_payment_options($method)
{
	global $wpdb;
	
	$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s",array('payment_method_'.$method));
	$paymentinfo = $wpdb->get_results($paymentsql);
	
	if($paymentinfo)
	{
		foreach($paymentinfo as $paymentinfoObj)
		{
			$optReturnarr = array();
			$option_value = unserialize($paymentinfoObj->option_value);
			foreach($option_value as $key => $value)
			{
				if($key != 'payOpts')
				{
					$optReturnarr[$key] = $value ;
				}
			}
			
			$paymentOpts = $option_value['payOpts'];
			
			for($i=0;$i<count($paymentOpts);$i++)
			{
				$optReturnarr[$paymentOpts[$i]['fieldname']] = $paymentOpts[$i]['value'];
			}
			return $optReturnarr;
		}
	}
}


function geodir_payment_pre_expiry_notification_days(){
	
	return apply_filters('geodir_pre_expiry_notification_days', array('0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10
	));
	
}


function geodir_payment_general_options($arr=array())
{

	$arr[] = array( 'name' => __( 'General Options', GEODIRPAYMENT_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'payment_emails' );
	
	
	$arr[] = array( 'name' => __( 'Listing expiration settings', GEODIRPAYMENT_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'expiration_settings_options');
	
	$arr[] = array(  
		'name' => __( 'Enable expiry process?', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> sprintf(__( ' Enable expiry process? (untick to disable) If you disable this option, none of the place listings will expire in future.', GEODIRPAYMENT_TEXTDOMAIN )),
		'id' 		=> 'geodir_listing_expiry',
		'std' 		=> '1',
		'type' 		=> 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	
	$arr[] = array(  
		'name' => '',
		'desc' 		=> __( 'Select the listing status after the place listing expires.', GEODIRPAYMENT_TEXTDOMAIN ),
		'tip' 		=> '',
		'id' 		=> 'geodir_listing_ex_status',
		'css' 		=> 'min-width:300px;',
		'std' 		=> 'draft',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique( array( 
			'draft' => __( 'draft', GEODIRPAYMENT_TEXTDOMAIN ),
			'publish' => __( 'publish', GEODIRPAYMENT_TEXTDOMAIN ),
			'trash' => __( 'trash', GEODIRPAYMENT_TEXTDOMAIN ),
			))
	);
	
	$arr[] = array(  
			'name'  => __( 'Enable pre expiry notification to author?', GEODIRPAYMENT_TEXTDOMAIN ),
			'desc' 	=> __('Enable pre expiry notification to author? (untick to disable) If you disable the option, pre expiry email notification will stop.', GEODIRPAYMENT_TEXTDOMAIN ),
			'id' 	=> 'geodir_listing_preexpiry_notice_disable',
			'type' 	=> 'checkbox',
			'std' 	=> '1' ,// Default value to show home top section
			
		);
		
	$arr[] = array(  
			'name' => __( 'Enable pre expiry notice days', GEODIRPAYMENT_TEXTDOMAIN ),
			'desc' 		=> __( 'Select number of days before pre expiry notification email will be sent.', GEODIRPAYMENT_TEXTDOMAIN ),
			'id' 		=> 'geodir_listing_preexpiry_notice_days',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '5',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => geodir_payment_pre_expiry_notification_days()
		);	
		
	$arr[] = array(  
			'name'  => __( 'Display expire date to author in dashboard listings?', GEODIRPAYMENT_TEXTDOMAIN ),
			'desc' 	=> __( 'Display expire date to author in dashboard listings? If you tick the option, listing expire date will be displayed to listing author in dashboard listings.', GEODIRPAYMENT_TEXTDOMAIN ),
			'id' 	=> 'geodir_payment_expire_date_on_listing',
			'type' 	=> 'checkbox',
			'std' 	=> '0' ,// Default value to show home top section
			
		);
	$arr[] = array(  
			'name'  => __( 'Display expire date to author in listing detail sidebar?', GEODIRPAYMENT_TEXTDOMAIN ),
			'desc' 	=> __( 'Display expire date to author in listing detail sidebar? If you tick the option, listing expire date will be displayed to listing author in listing detail sidebar.', GEODIRPAYMENT_TEXTDOMAIN ),
			'id' 	=> 'geodir_payment_expire_date_on_detail',
			'type' 	=> 'checkbox',
			'std' 	=> '0' ,// Default value to show home top section
			
		);
		

	$arr[] = array( 'type' => 'sectionend', 'id' => 'expiration_settings_options');
	
	
	$arr[] = array( 'name' => __( 'Geo Directory Manage Currency', GEODIRPAYMENT_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'payment_general_options');
	
	$arr[] = array(  
		'name' => __( 'Default Currency (Ex.: USD)', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_currency',
		'type' 		=> 'text',
		'css' 		=> 'min-width:200px;',
		'std' 		=> __('USD', GEODIRPAYMENT_TEXTDOMAIN)
		);
		
	$arr[] = array(  
		'name' => __( 'Default Currency Symbol (Ex.: $)', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_currencysym',
		'type' 		=> 'text',
		'css' 		=> 'min-width:200px;',
		'std' 		=> __('$', GEODIRPAYMENT_TEXTDOMAIN)
		);	
		

	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_general_options');
	
	$arr = apply_filters('geodir_payment_general_options' ,$arr );
	
	
	return $arr;
}


function geodir_payment_notifications($arr=array())
{

	$arr[] = array( 'name' => __( 'Payment Emails', GEODIRPAYMENT_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'payment_emails' );
	
	$arr[] = array( 'name' => __( 'Client Emails', GEODIRPAYMENT_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'payment_client_emails');
	
	$arr[] = array(  
		'name' => __( 'Payment success to client email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_client_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Acknowledgment for your Payment', GEODIRPAYMENT_TEXTDOMAIN)
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_client_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],</p><p>Payment has been successfully received. Your details are below</p><p>[#transaction_details#]</p><br><p>We hope you enjoy. Thanks!</p><p>[#site_name#]</p>", GEODIRPAYMENT_TEXTDOMAIN)
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post renew success to client email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Renewal of listing ID:#[#post_id#]', GEODIRPAYMENT_TEXTDOMAIN) // Default value for the page title - changed in settings
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __('<p>Dear [#client_name#],</p><p>Your listing [#listing_link#] has been renewed.</p><p>NOTE: If your listing is not active yet your payment may be being checked by an admin and it will be activated shortly.</p><br><p>[#site_name#]</p>', GEODIRPAYMENT_TEXTDOMAIN)
		);
		
		
	$arr[] = array(  
		'name' => __( 'Listing expiration email', GEODIRECTORY_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_renew_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> 'Place listing expiration Notification' // Default value for the page title - changed in settings
	);
	$arr[] = array(  
			'name' => '',
			'desc' 		=> '',
			'id' 		=> 'geodir_renew_email_content',
			'css' 		=> 'width:500px; height: 150px;',
			'type' 		=> 'textarea',
			'std' 		=>  "<p>Dear [#client_name#],<p><p>Your listing - [#listing_link#] posted on  <u>[#posted_date#]</u> for [#number_of_days#] days.</p><p>It's going to expiry after [#number_of_grace_days#] day(s). If the listing expire, it will no longer appear on the site.</p><p> If you want to renew, Please login to your member area of our site and renew it as soon as it expire.</p><p>You may like to login the site from [#login_url#].</p><p>Your login ID is <b>[#username#]</b> and Email ID is <b>[#user_email#]</b>.</p><p>Thank you,<br />[#site_name_url#].</p>"
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post Upgrade Success to Client Email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Upgrade of listing ID:#[#post_id#]', GEODIRPAYMENT_TEXTDOMAIN)
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],</p><p>Your listing [#listing_link#] has been upgraded.</p><p>NOTE: If your listing is not active yet your payment may be being checked by an admin and it will be activated shortly.</p><br><p>[#site_name#]</p>", GEODIRPAYMENT_TEXTDOMAIN)
		);
	
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_client_emails');

	$arr[] = array( 'name' => __( 'Admin Emails', GEODIRPAYMENT_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'payment_admin_emails');
	
	
	$arr[] = array(  
		'name' => __( 'Payment success to admin email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_admin_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Payment received successfully', GEODIRPAYMENT_TEXTDOMAIN)
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_admin_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,</p><p>Payment has been received. Below are the transaction details.</p><p>[#transaction_details#]</p><br><p>[#site_name#]</p>", GEODIRPAYMENT_TEXTDOMAIN)
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post renewal success to admin email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_subject_admin',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Renewal of listing ID:#[#post_id#]', GEODIRPAYMENT_TEXTDOMAIN) // Default value for the page title - changed in settings
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_content_admin',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __('<p>Dear Admin,</p><p>Listing [#listing_link#] has been renewed. Please confirm payment and then update the listings published date to todays date. </p><p>NOTE: If payment was made by paypal the "published date" should be updated automatically. </p><br><p>[#site_name#]</p>', GEODIRPAYMENT_TEXTDOMAIN)
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post upgrade success to admin email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_subject_admin',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Upgrade of listing ID:#[#post_id#]', GEODIRPAYMENT_TEXTDOMAIN)
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_content_admin',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,</p><p>Listing [#listing_link#] has been upgraded. Please confirm payment and then update the listings published date to todays date. </p><p>NOTE: If payment was made by paypal the \"published date\" should be updated automatically. </p><br><p>[#site_name#]</p>", GEODIRPAYMENT_TEXTDOMAIN)
		);
	
	
	$arr[] = array(  
		'name' => __( 'Payment fail to admin email', GEODIRPAYMENT_TEXTDOMAIN ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_fail_admin_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('IPN INVALID - Place Listing Submitted', GEODIRPAYMENT_TEXTDOMAIN)
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_fail_admin_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,</p><p>Paypal IPN Invaid for listing ID: #[#post_id#]</p><p>Please manually check your paypal logs, and if payment was received manually publish the listing.</p><p>[#listing_link#]</p><br><p>[#site_name#]</p>", GEODIRPAYMENT_TEXTDOMAIN)
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_admin_emails');
	
	$arr = apply_filters('geodir_payment_notifications' ,$arr );
	
	return $arr;
}


function geodir_enable_editor_on_payment_notifications($notification){
	
	if(!empty($notification) && get_option('geodir_tiny_editor')=='1'){
		
		foreach($notification as $key => $value){
			if($value['type'] == 'textarea')
				$notification[$key]['type'] = 'editor';
		}
		
	}
	
	return $notification;
}



function geodir_get_currency_sym()
{
	global $wpdb;

	if(get_option('geodir_currencysym'))
		return stripslashes(get_option('geodir_currencysym'));
	else
		return '$';
}

function geodir_get_currency_type()
{
	global $wpdb;

	if(get_option('geodir_currency'))
		return stripslashes(get_option('geodir_currency'));
	else
		return 'USD';
}

function geodir_package_list_info($post_type = '', $pkgid = '')
{

	global $wpdb;
	
	$subsql = '';
	
	if($pkgid)
		$subsql .= " and pid = '$pkgid' ";	
		
	if($post_type)
		$subsql .= " and post_type = '$post_type'";		

	$pricesql = "select * from ".GEODIR_PRICE_TABLE." where status=1 $subsql ORDER BY `display_order` ASC, `amount` ASC";
	
	$pricesql = apply_filters('geodir_package_list_query' ,$pricesql ) ;
	return $priceinfo = $wpdb->get_results($pricesql);

}

function geodir_get_post_package_info( $pkg_id = '', $pid = '' ) {	
	global $wpdb;
	
	$post_type = '';
	if( $pkg_id == '' && $pid != '' ) {
		$gd_post_info = geodir_get_post_info( $pid );
		if ( !empty( $gd_post_info ) && isset( $gd_post_info->package_id ) && $gd_post_info->package_id > 0 ) {
			$pkg_id = $gd_post_info->package_id;
			$post_type = $gd_post_info->post_type;
		}
	}
	
	// get price package info
	$priceinfo = geodir_get_package_info( $pkg_id );
	
	if ( !empty($priceinfo) && is_array( $priceinfo ) ) {
		$priceinfo = (object)$priceinfo;
	}
		
	$info = array();
	if( !empty( $priceinfo ) ) {
		$priceinfoObj = $priceinfo;
		$info['pid'] = $priceinfoObj->pid;
		$info['title'] = $priceinfoObj->title;
		$info['amount'] = $priceinfoObj->amount;
		$info['cat'] =$priceinfoObj->cat;
		$info['status'] = $priceinfoObj->status;
		$info['days'] = $priceinfoObj->days;	
		$info['is_default'] = $priceinfoObj->is_default;	
		$info['is_featured'] = $priceinfoObj->is_featured;
		$info['title_desc'] = $priceinfoObj->title_desc;
		$info['image_limit'] = $priceinfoObj->image_limit;
		$info['cat_limit'] = $priceinfoObj->cat_limit;
		$info['post_type'] = $priceinfoObj->post_type;
		$info['link_business_pkg'] = $priceinfoObj->link_business_pkg;
		$info['recurring_pkg'] = $priceinfoObj->recurring_pkg;
		$info['reg_desc_pkg'] = $priceinfoObj->reg_desc_pkg;
		$info['reg_fees_pkg'] = $priceinfoObj->reg_fees_pkg;
		$info['downgrade_pkg'] = $priceinfoObj->downgrade_pkg;
		$info['sub_active'] = $priceinfoObj->sub_active;
		$info['sub_units'] = $priceinfoObj->sub_units;
		$info['sub_units_num'] = $priceinfoObj->sub_units_num;
		$info['sub_num_trial_days'] = $priceinfoObj->sub_num_trial_days;
		$info['sub_num_trial_units'] = isset( $priceinfoObj->sub_num_trial_units ) && !empty( $priceinfoObj->sub_num_trial_units ) ? $priceinfoObj->sub_num_trial_units : 'D';
		$info['sub_units_num_times'] = $priceinfoObj->sub_units_num_times;
		$info['google_analytics'] = $priceinfoObj->google_analytics;
		$info['sendtofriend'] = $priceinfoObj->sendtofriend;
		$info['use_desc_limit'] = $priceinfoObj->use_desc_limit;
		$info['desc_limit'] = $priceinfoObj->desc_limit;
		$info['use_tag_limit'] = $priceinfoObj->use_tag_limit;
		$info['tag_limit'] = $priceinfoObj->tag_limit;
	}
	return $info;
}

function geodir_get_package_info_by_id( $pid, $status = '1' ) {
	global $wpdb;
	
	if ( !$pid > 0 ) {
		return NULL;
	}
	
	$where = '';
	if ( $status == '1' ) {
		$where = "AND status = '1'";
	} else if ( $status == '0' ) {
		$where = "AND status != '1'";
	}
	
	$query = $wpdb->prepare( "SELECT * FROM " . GEODIR_PRICE_TABLE . " WHERE pid = %d " . $where, array( $pid ) );
	$row = $wpdb->get_row( $query );
	
	return $row;	
}

function geodir_get_package_info( $package_id ) {
	global $wpdb, $geodir_get_package_info_cache;
	if(!$package_id){return;}
	if ( is_numeric( $package_id ) && is_array( $geodir_get_package_info_cache ) && !empty( $geodir_get_package_info_cache ) && isset( $geodir_get_package_info_cache[$package_id] ) ) {
		return $geodir_get_package_info_cache[$package_id];
	}
	
	// get price package info
	$priceinfo = geodir_get_package_info_by_id( $package_id );
	
	if ( $priceinfo && !is_wp_error( $priceinfo ) ) {
		$info = apply_filters( 'geodir_package_info', $priceinfo, $package_id );
		$geodir_get_package_info_cache[$package_id] = $info;
		return $info;
	} else {
		return false;
	}
}

function geodir_get_default_package($post_type){
	global $wpdb;
	
	$post_types = geodir_get_posttypes();
	
	if(!$wpdb->get_var($wpdb->prepare("SELECT pid FROM ".GEODIR_PRICE_TABLE." WHERE post_type=%s", array($post_type))) && in_array($post_type, $post_types))
	{			
					
		$price_insert = "INSERT INTO ".GEODIR_PRICE_TABLE." (`title`, `amount`, `days`, `status`, `is_default`, `cat`, `is_featured`, `title_desc`, `image_limit`, `cat_limit`, `google_analytics`, `sendtofriend`, `post_type`, `link_business_pkg`, `recurring_pkg`, `reg_desc_pkg`, `reg_fees_pkg`, `downgrade_pkg`) VALUES ('".__('Free', GEODIRPAYMENT_TEXTDOMAIN)."', 0.00, 0, 1, 1, '', 0, '".__('Free: number of publish days are unlimited (0.00 '.geodir_get_currency_type().')', GEODIRPAYMENT_TEXTDOMAIN)."', '', 0, 0, 1, '".$post_type."', 0, 0, 0, 0, '')";
		
		$wpdb->query($price_insert);
		
	}
	
	$pricesql = $wpdb->prepare("SELECT * FROM ".GEODIR_PRICE_TABLE." WHERE status = '1' AND is_default = '1' AND post_type = %s", array($post_type));
	
	$priceinfo = $wpdb->get_row($pricesql);
	
	if($priceinfo && !is_wp_error($priceinfo) )
		return apply_filters('geodir_default_package_info' , $priceinfo);
	else
		return false;	
}


function geodir_get_post_package_info_on_listing($info, $post, $post_type = ''){
	//  if post is array convert to object
	 if(!is_object($post) && isset($post['ID'])){  $post = json_decode(json_encode($post), FALSE);}
		  
	$listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';
	$package_id = '';

	if(!is_object($post) && isset($post['post_type']) && $post['post_type'] != '')
		$listing_type = $post['post_type'];
	
	if(is_object($post) && isset($post->ID) && isset($post->package_id)){
		$package_id = $post->package_id;
	}
		
	if(isset($_REQUEST['package_id'])){
		
		$package_id = $_REQUEST['package_id'];
		
	}elseif(isset($post->package_id) || ((isset($_REQUEST['post_type']) || isset($post->post_type)) && $package_id ) || (isset($post->ID) && $package_id = geodir_get_post_meta($post->ID,'package_id')) ){
		
		$listing_type = $post->post_type;
		$package_id = isset($post->package_id) ? $post->package_id : $package_id;
		
	}elseif(($listing_type != '' && isset($post->pid) && $post->pid != '') || (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '' && !isset($_REQUEST['post_type']))){
		
		$post_id = isset($post->pid) ? $post->pid : $_REQUEST['pid'];
		
		$package_id = geodir_get_post_meta($post_id,'package_id');
		
	}
	
	if(empty($package_id)){
		
		if(empty($listing_type))
			$listing_type = isset($post->post_type) ? $post->post_type : '';
		
		$all_postypes = geodir_get_posttypes();
		
		if($post_type != '' && in_array($post_type, $all_postypes))
			$listing_type = $post_type;
		
		$default_package = geodir_get_default_package($listing_type);
		
		if(!empty($default_package))
			$package_id = $default_package->pid;
		
	}
	
	return $info = geodir_get_package_info($package_id);

}


function geodir_create_invoice($post_id = '', $pkg_id = '', $paymentmethod = '', $coupon_code = '', $post_action = 'add' )
{
	global $wpdb,$current_user;
	
	$user_id = $current_user->data->ID;
	$post_title = get_the_title($post_id);
	
	$package_info = geodir_get_package_info($pkg_id); /// Get package info
	
	$package_pid = $package_info->pid;
	$package_title = $package_info->title;
	$alive_days = $package_info->days;
	
	$sub_units = $package_info->sub_units;
	$sub_units_num = $package_info->sub_units_num;
	
	if($package_info->sub_active){
		$sub_units_num_var = $package_info->sub_units_num;
		$sub_units_var = $package_info->sub_units;
		$alive_days = geodir_payment_get_units_to_days( $sub_units_num_var, $sub_units_var );
		$sub_num_trial_days_var = $package_info->sub_num_trial_days;
		$sub_num_trial_units = isset( $package_info->sub_num_trial_units ) ? $package_info->sub_num_trial_units : 'D';
		$sub_num_trial_days_var = geodir_payment_get_units_to_days( $sub_num_trial_days_var, $sub_num_trial_units );
		$sub_units_num_times_var = $package_info->sub_units_num_times;
		if ( $package_info->sub_num_trial_days > 0 ) {
			$alive_days = $sub_num_trial_days_var;
		}
	}	
	
	$amount = ($package_info->amount) ? $package_info->amount : 0 ;
	
	$expire_date = '';
	if($alive_days){
		$expire_date = date('Y-m-d', strtotime(date('Y-m-d')."+".(int)$alive_days." days"));
	}
	
	$geodir_post_type =  get_post_type( $post_id ); 
	
	$discount = '';
	if($coupon_code != '' && geodir_is_valid_coupon($geodir_post_type, $coupon_code)){
		$discount = geodir_get_discount_amount($coupon_code,$amount);
		$paied_amount = geodir_get_payable_amount_with_coupon($amount,$coupon_code);
	}else{
	
		$paied_amount = $amount;
	}
	
	$status = 'pending';
	
	if($paied_amount > 0 )
		$type = 'paid';
	else
		$type = 'free';	
	
	
	$is_current = 1;
	
	$date = date('Y-m-d H:i:s', time());
	
	$insert_invoice_query = "INSERT INTO ".INVOICE_TABLE." SET "; 
	$arr_invoice_params = array(
		'type' => $type,
		'post_id' => $post_id,
		'post_title' => $post_title,
		'post_action' => $post_action,
		'package_id' => $package_pid,
		'package_title' => $package_title,
		'amount' => $amount,
		'alive_days' => $alive_days,
		'expire_date' => $expire_date,
		'user_id' => $user_id,
		'coupon_code' => $coupon_code,
		'discount' => $discount,
		'paied_amount' => $paied_amount,
		'paymentmethod' => $paymentmethod,
		'status' => $status,
		'is_current' => $is_current,
		'date' => $date
	);
	$arr_invoice_params = apply_filters('geodir_payment_invoice_params'  , $arr_invoice_params) ;
	foreach($arr_invoice_params as $invocie_col => $invoice_value)
	{
		$insert_invoice_query .= " " . $invocie_col . " = '" . $invoice_value . "' , " ;
	}
	$insert_invoice_query = trim($insert_invoice_query,", ");
	$insert_invoice_query ;
	$wpdb->query($insert_invoice_query);
							 
	do_action('geodir_payment_invoice_created'  , $wpdb->insert_id);
	return $wpdb->insert_id;			
} 

function geodir_update_invoice($id = '', $invoice_info = array()){
	global $wpdb;
	
	$date = date('Y-m-d H:i:s', time());
	$arr_invoice_params = apply_filters('geodir_payment_invoice_params'  , $invoice_info,$id) ;
	$set_query = '';
	foreach($arr_invoice_params as $field=>$value){
		if($value != '')
				$set_query .= $field." = '".$value."', ";
	}
	
	
	$set_query = trim($set_query,", ");
	
	
	$wpdb->query($wpdb->prepare("UPDATE ".INVOICE_TABLE." SET ".$set_query." WHERE  id = %d",array($id)));
	do_action('geodir_payment_invoice_created'  , $wpdb->insert_id);
	return $wpdb->insert_id;			
} 

function geodir_get_invoice($id = ''){
	global $wpdb;
	
	
	$invoice = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".INVOICE_TABLE." WHERE id = %d ", array($id)));

	if(!empty($invoice))
		return $invoice;
	else
		return false;	
}

function geodir_update_invoice_status($id = '', $status = ''){
	global $wpdb;
	
	if($id != '' && $status != '')
	{	
		$wpdb->query($wpdb->prepare("UPDATE ".INVOICE_TABLE." SET `status` = %s WHERE id = %d ",array($status,$id)));
		do_action('geodir_payment_invoice_status_changed'  , $wpdb->insert_id);
		return $wpdb->insert_id;
	}else
		return false;	
}

function geodir_downgrade_packages_list($post_type='')
{
	global $wpdb;
	$subsql = '';
	if(isset($pro_type) && !empty($pro_type))
	{
		$subsql = " and post_type='$post_type'";	
	}
	$pricesql = "select * from ".GEODIR_PRICE_TABLE." where status=1 $subsql";
	$priceinfo = $wpdb->get_results($pricesql);
	return $priceinfo;
}

// TEMP CHANGE ADMINEMAIL FUNCTION NAME --------
//if (!function_exists('geodir_geodir_payment_adminEmail')) {
function geodir_payment_adminEmail($post_id,$user_id,$message_type,$extra='')
{
	$login_details ='';
					
	if($message_type=='payment_success'){
		$subject = stripslashes(get_option('geodir_post_payment_success_admin_email_subject')); 
		$message = stripslashes(get_option('geodir_post_payment_success_admin_email_content')); 
	}elseif($message_type=='payment_fail'){
		$subject = stripslashes(get_option('geodir_post_payment_fail_admin_email_subject')); 
		$message = stripslashes(get_option('geodir_post_payment_fail_admin_email_content'));
	}
	
	$user_info = get_userdata($user_id);
	
	//$fromEmail = $user_info->user_email;
	//$fromEmailName = $user_info->display_name;
	
	$toEmail =  get_option('site_email');// $user_info->user_email;
	$toEmailName = get_site_emailName();
	
	
	$to_message = nl2br($to_message);
	
	$productlink = get_permalink($post_id);
	
	$post_info = get_post($post_id);
	
	$posted_date = $post_info->post_date;
	$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
	$siteurl = home_url();
	$siteurl_link = '<a href="'.$siteurl.'">'.$siteurl.'</a>';
	$loginurl = home_url().'/?geodir_signup=true';
	$loginurl_link = '<a href="'.$loginurl.'">login</a>';
	
	if($fromEmail==''){$fromEmail = get_option('site_email');}
	
	if($fromEmailName==''){$fromEmailName = get_option('site_email_name');}
	
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#comments#]','[#login_url#]','[#login_details#]','[#client_name#]', '[#posted_date#]', '[#transaction_details#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$to_message,$loginurl_link,$login_details,$toEmailName, $posted_date, $extra);
	$message = str_replace($search_array,$replace_array,$message);
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]', '[#posted_date#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName, $posted_date);
	$subject = str_replace($search_array,$replace_array,$subject);
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	//$headers .= 'To: '.$toEmail.' <'.$toEmailName.'>' . "\r\n";
	$headers .= 'From: '.$fromEmailName.' <'.$fromEmail.'>' . "\r\n";
	@wp_mail($toEmail , $subject, $message, $headers);
	
	
}//}

if (!function_exists('geodir_get_post_meta')) {
	function geodir_get_post_meta( $post_id, $meta_key, $single = false ) {
		if (!$post_id) {
			return false;
		}
		global $wpdb,$plugin_prefix;
		
		$all_postypes = geodir_get_posttypes();
		
		$post_type = get_post_type( $post_id );
		
		if (!in_array($post_type, $all_postypes)) {
			return false;
		}
		
		$table = $plugin_prefix . $post_type . '_detail';
		
		if ($wpdb->get_var("SHOW COLUMNS FROM ".$table." WHERE field = '".$meta_key."'"  ) != '') {
			if ($meta_value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta_key." from ".$table." where post_id = %d",array($post_id)))) {
				$meta_value = maybe_serialize( $meta_value );
				return $meta_value;
			} else {
				return false;
			}			
		} else {
			return false;
		}
	}
}

//if (!function_exists('geodir_clientEmail')) {
function geodir_payment_clientEmail($post_id,$user_id,$message_type,$extra='')
{
	$login_details ='';
	$to_message = '';
	$to_subject = '';
	
	if($message_type=='payment_success'){
		$subject = stripslashes(get_option('geodir_post_payment_success_client_email_subject')); 
		$message = stripslashes(get_option('geodir_post_payment_success_client_email_content')); 
	}
	
	if($message_type=='expiration'){
		$subject = stripslashes(get_option('geodir_renew_email_subject')); 
		$message = stripslashes(get_option('geodir_renew_email_content')); 
	}
	
	$number_of_grace_days = get_option('geodir_listing_preexpiry_notice_days');
	if($number_of_grace_days==''){$number_of_grace_days=1;}
	
	$alivedays = geodir_get_post_meta($post_id,'alive_days',true);
	
	$user_info = get_userdata($user_id);
	$toEmail = $user_info->user_email;
	$toEmailName = $user_info->display_name;
	$user_login = $user_info->user_login;
	$user_email = $user_info->user_email;
	
	$to_message = nl2br($to_message);
	$sitefromEmail = get_option('site_email');
	$sitefromEmailName = get_site_emailName();
	$productlink = get_permalink($post_id);
	
	$post_info = get_post($post_id);
	
	$posted_date = $post_info->post_date;
	$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
	$siteurl = home_url();
	$siteurl_link = '<a href="'.$siteurl.'">'.$siteurl.'</a>';
	$loginurl = home_url().'/?geodir_signup=true';
	$loginurl_link = '<a href="'.$loginurl.'">login</a>';
	
	if($fromEmail==''){$fromEmail = get_option('site_email');}
	
	if($fromEmailName==''){$fromEmailName = get_option('site_email_name');}
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#comments#]','[#login_url#]','[#login_details#]','[#client_name#]', '[#posted_date#]', '[#transaction_details#]', '[#number_of_grace_days#]', '[#number_of_days#]', '[#username#]', '[#user_email#]' );
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$to_message,$loginurl_link,$login_details,$toEmailName, $posted_date, $extra, $number_of_grace_days, $alivedays, $user_login, $user_email);
	$message = str_replace($search_array,$replace_array,$message);
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]', '[#posted_date#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName, $posted_date);
	$subject = str_replace($search_array,$replace_array,$subject);
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= "Reply-To: ".$fromEmail. "\r\n";
	//$headers .= 'To: '.$toEmailName.' <'.$toEmail.'>' . "\r\n";
	$headers .= 'From: '.$sitefromEmailName.' <'.$sitefromEmail.'>' . "\r\n";
	
	@wp_mail($toEmail, $subject, $message, $headers);
	
	// send bcc to admin for expired listing
	if ($message_type=='expiration' && get_option('geodir_bcc_expire')) {
		$adminEmail = get_bloginfo('admin_email');
		$subject .= ' - ADMIN BCC COPY';
		@wp_mail($adminEmail, $subject, $message, $headers);
	}
	
}//}

// Payment module related Post Metabox function 
function geodir_payment_metabox_add()
{
	
	global $post;
	
	$geodir_post_types = geodir_get_posttypes('array');
	$geodir_posttypes = array_keys($geodir_post_types);

	if( isset($post->post_type) && in_array($post->post_type,$geodir_posttypes) ):
	
		$geodir_posttype = $post->post_type;
		$post_typename = ucwords($geodir_post_types[$geodir_posttype]['labels']['singular_name']);
		
		add_meta_box( 'geodir_listing_transaction', 'Listing Transactions', 'geodir_listing_transaction', $geodir_posttype,'normal', 'high' );
	
	endif;
	
	
	
}


function geodir_listing_transaction(){ 
	global $post, $wpdb;
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_listing_transaction_noncename' ); 
	
	$pid_sql = $wpdb->prepare("SELECT * FROM ".INVOICE_TABLE." WHERE post_id = %d ORDER BY date desc", array($post->ID));
	
	$transactions = $wpdb->get_results($pid_sql);
	?>
    <table cellpadding="3" cellspacing="3" class="widefat post fixed" >
        <thead>
            
              <th><?php _e('Type', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Package Information', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Coupon', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Discount', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Payable Amount', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Payment Method', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Date', GEODIRPAYMENT_TEXTDOMAIN);?></th>
              <th><?php _e('Status', GEODIRPAYMENT_TEXTDOMAIN);?></th>
            
        </thead>
        <tbody>
				
		<?php
        $total = 0;
				$cur_sym = '';
        foreach($transactions as $invoice){
            $cur_sym = geodir_get_currency_sym();
            $type = ucfirst($invoice->type);
			$status = $invoice->status;
            $paid_amt ='';
            $paid_amt = $invoice->paied_amount;
            if( ($type=='Paid' || $type=='Subscription-Payment') && $status == 'paid' )
			{$total = $total + $paid_amt; }
        ?>
       		<tr>
              <td><?php _e($type, GEODIRPAYMENT_TEXTDOMAIN);?></td>
              <td>	
              		<label><?php _e('ID:', GEODIRPAYMENT_TEXTDOMAIN);?>&nbsp;</label><?php echo $invoice->package_id;?>
                    <?php //_e('('.$invoice->package_title.')');?><br/>
                    <label><?php _e('Amount:', GEODIRPAYMENT_TEXTDOMAIN);?>&nbsp;</label><?php echo $cur_sym.$invoice->amount;?><br/>
                    <label><?php _e('Alive Days:', GEODIRPAYMENT_TEXTDOMAIN);?>&nbsp;</label><?php echo $invoice->alive_days;?>
                    
              </td>
              <td><?php echo ($invoice->coupon_code) ? $invoice->coupon_code : __('No', GEODIRPAYMENT_TEXTDOMAIN);?></td>
          	  <td><?php echo ($invoice->discount) ? $cur_sym.$invoice->discount : $cur_sym.'0';?></td>
              <td><?php echo ($paid_amt) ? $cur_sym.$paid_amt : '0'; ?></td>
              <td><?php echo ($invoice->paymentmethod) ? $invoice->paymentmethod : __('No', GEODIRPAYMENT_TEXTDOMAIN);?></td>
              <td><?php echo $invoice->date;?></td>
              <td>
			  		<select id="status" name="invoice_listing_status[]">
                    	<option value="<?php echo $invoice->id;?>,paid" <?php if($status == 'paid') echo 'selected="selected"' ;?> ><?php _e('Paid', GEODIRPAYMENT_TEXTDOMAIN);?></option>
                        <option value="<?php echo $invoice->id;?>,unpaid" <?php if($status == 'unpaid' || $status == 'pending') echo 'selected="selected"' ;?> ><?php _e('UnPaid', GEODIRPAYMENT_TEXTDOMAIN);?></option>
                        <option value="<?php echo $invoice->id;?>,free" <?php if($status == 'free') echo 'selected="selected"' ;?> ><?php _e('Free', GEODIRPAYMENT_TEXTDOMAIN);?></option>
                    </select>
			  </td>
            </tr>
		<?php } ?>
        </tbody>
	</table><br />
    
	<?php  echo __('<b>Total Received: </b>', GEODIRPAYMENT_TEXTDOMAIN).$cur_sym.$total;
}

function geodir_post_transaction_save( $post_id )  
{
	global $wpdb,$current_user;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	return;
	
	if ( !isset($_POST['geodir_listing_transaction_noncename']) || !wp_verify_nonce( $_POST['geodir_listing_transaction_noncename'], plugin_basename( __FILE__ ) ) )
	return;
	
	if(isset($_REQUEST['invoice_listing_status']) && !empty($_REQUEST['invoice_listing_status'])){
	
		foreach($_REQUEST['invoice_listing_status'] as $invoice){
			
			$invoice_info = explode(',', $invoice);
			
			$invoice_id = $invoice_info[0]; 
			$invoice_status = $invoice_info[1];
			
			geodir_update_invoice_status($invoice_id,$invoice_status);
			
		}
		
	}
		
}

//// Ajax related function 
/*-----------------------------------------*/
// MAIN AJAX FUNCTION  ////////////////

function geodir_payment_manager_ajax()
{
	if(isset($_REQUEST['gd_add_price']) && $_REQUEST['gd_add_price'] == 'addprice')
	{
		geodir_add_edit_price();	
	}
	
	if(isset($_REQUEST['action_del']) && $_REQUEST['action_del'] == 'true')
	{
		geodir_del_price();
	}
	
	if(isset($_REQUEST['paymentsetting']) && $_REQUEST['paymentsetting'] == 'update_setting')
	{
		geodir_change_payment_method_setting();
	}
	
	if(isset($_REQUEST['gdaction']) && $_REQUEST['gdaction'] == 'change_status')
	{
		geodir_change_payment_method_status();
	}
	
	if(isset($_REQUEST['invoice_action']) && $_REQUEST['invoice_action'] == 'invoice')
	{
		geodir_change_invoice_status();
	}
	
	if(isset($_REQUEST['gd_add_coupon']) && $_REQUEST['gd_add_coupon'] == 'addprice')
	{
		geodir_add_edit_coupon();	
	}
	
	if(isset($_REQUEST['coupon_del']) && $_REQUEST['coupon_del'] == 'true')
	{
		geodir_del_coupon();
	}
	
	if(isset($_REQUEST['allow_coupon']) && $_REQUEST['allow_coupon'] == 'true')
	{
		geodir_allow_coupon_code();
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_general_options')
	{
		
		geodir_update_options(geodir_payment_general_options());
		
		$msg = 'Your settings have been saved.';
		
		$msg = urlencode($msg);
		
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_general_options&success_msg=".$msg;
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'payment_notifications')
	{
		
		geodir_update_options(geodir_payment_notifications());
		
		$msg = 'Notifications updated successfully.';
		
		$msg = urlencode($msg);
		
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=payment_notifications&success_msg=".$msg;
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['payment_ajax_data']) && $_REQUEST['payment_ajax_data'] != '')
	{
		geodir_fields_list_by_posttype($_REQUEST['post_type'], $_REQUEST['pkg_id'], $_REQUEST['cats'], $_REQUEST['payment_ajax_data']);
		exit;
	}
	
	
	if(isset($_REQUEST['payaction']) && $_REQUEST['payaction'] == 'trouble_shoot'){
		
		geodir_payment_method_update();
	
	}

}

function geodir_payment_manager_ajaxurl(){
	return admin_url('admin-ajax.php?action=geodir_payment_manager_ajax');
}

function geodir_fields_list_by_posttype($post_type = '', $pkg_id = '', $cats='', $ajax_data='')
{
	global $wpdb,$cat_display,$post_cat;
	if($post_type)
	{
		$custom_fields = geodir_post_custom_fields('','all',$post_type);
		$html = '<select style="min-width:200px;" multiple="multiple" name="pay_custom_fields[]">';
		if(!empty($custom_fields)){
			foreach($custom_fields as $key=>$val)
			{
				$id =  $val['id'];
				$label =  $val['label'];
				$is_default =  $val['is_default'];
				$is_admin =  $val['is_admin'];
				$field_type =  $val['field_type'];
				
				if(!($field_type == 'address' && $is_admin == '1') && !($field_type == 'taxonomy' && $is_admin == '1'))
				{
					$selected = '';
					if($pkg_id != '' && $wpdb->get_var($wpdb->prepare("SELECT id FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages) AND id='".$id."'", array($pkg_id))))
					{
						$selected = 'selected="selected"';
					}
					$html .= '<option '.$selected.' value="'.$id.'">'.$label.'</option>';
				}
				
			}
		}
			
			$cat_display = 'multiselect';
			$post_cat = $cats;
			$html_cat = '<select style="min-width:200px;" name="gd_cat[]" multiple="multiple" style="height: 100px;" >';	
			$html_cat .= geodir_custom_taxonomy_walker($post_type.'category');
			
			$html_cat .= '</select>';
			
			
			$downgrade = '<select style="min-width:200px;" name="gd_downgrade_pkg" style="height: 100px;" >';	
			$downgrade .= '<option value="0">'.__("Expire", GEODIRPAYMENT_TEXTDOMAIN).'</option>';
				$priceinfo = geodir_package_list_info($post_type);
				$pricearr = array();
				foreach($priceinfo as $priceinfoObj){ 
					$selected = ''; 
					if ($priceinfoObj->pid == $cats)
						$selected = 'selected="selected"';
						
					$downgrade .= '<option value="'.$priceinfoObj->pid.'" '.$selected.'>'.$priceinfoObj->title.' - '.$priceinfoObj->post_type.'</option>';
				} 
			
			$downgrade .= '</select>';
			
		
		if($ajax_data == ''){
	
			$htmls['html_cat'] = isset($html_cat) ? $html_cat : '';
			$htmls['posttype'] = isset($html) ? $html : '';
			$htmls['downgrade'] = isset($downgrade) ? $downgrade : '';
			
			return $htmls;
			
			
		
		}else{
			$htmls['html_cat'] = $html_cat;
			$htmls['posttype'] = $html;
			$htmls['downgrade'] = $downgrade;
			 echo json_encode($htmls);
			//echo $htmls = '{"posttypes":"$html","html_cat":"$html_cat"}';
		}
	}
}

// Ajax function to add edit price package
function geodir_add_edit_price()
{
	global $wpdb,$plugin_prefix;
	
	if(current_user_can( 'manage_options' )){
	
		if($_POST['gd_add_price'] == 'addprice' && isset($_REQUEST['package_add_update_nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['package_add_update_nonce'], 'package_add_update' ) )
				return;
			
			$id = $_POST['gd_id'];
			
			$title = $_POST['gd_title'];
			
			$amount = $_POST['gd_amount'];
			
			$days = $_POST['gd_days'];
			
			$status = $_POST['gd_status'];
			
			$is_default = $_POST['gd_is_default']; //COMP5
			
			$cat = isset($_POST['gd_cat']) ? $_POST['gd_cat'] : '';
			
			$is_featured = $_POST['gd_is_featured'];
			
			$title_desc = $_POST['gd_title_desc'];
			
			$image_limit = $_POST['gd_image_limit'];
			
			$cat_limit = $_POST['gd_cat_limit'];
			
			$google_analytics = $_POST['google_analytics'];
			
			$sendtofriend = $_POST['geodir_sendtofriend'];
			
			$post_type = $_POST['gd_posting_type'];
			
			$link_business_pkg = isset($_POST['gd_link_business_pkg']) ? $_POST['gd_link_business_pkg'] : '';
			
			$recurring_pkg = isset($_POST['gd_recurring_pkg']) ? $_POST['gd_recurring_pkg'] : '';
			
			$reg_desc_pkg = isset($_POST['gd_reg_desc_pkg']) ? $_POST['gd_reg_desc_pkg'] : '';
			
			$reg_fees_pkg = isset($_POST['gd_reg_fees_pkg']) ? $_POST['gd_reg_fees_pkg'] : '';
			
			$downgrade_pkg = $_POST['gd_downgrade_pkg'];
			
			$sub_active = isset($_POST['gd_sub_active']) ? $_POST['gd_sub_active'] : '';
			
			$display_order = isset($_POST['gd_display_order']) ? (int)$_POST['gd_display_order'] : '';
			
			$sub_units = $_POST['gd_sub_units'];
			
			$sub_units_num = $_POST['gd_sub_units_num'];
			$sub_num_trial_days = $_POST['sub_num_trial_days'];
			$sub_num_trial_units = !empty( $_POST['gd_sub_num_trial_units'] ) && in_array( $_POST['gd_sub_num_trial_units'], array( 'D', 'W', 'M', 'Y' ) ) ? $_POST['gd_sub_num_trial_units'] : 'D';
			$sub_units_num_times = $_POST['sub_units_num_times'];
			
			$use_desc_limit = $_POST['gd_use_desc_limit'];
			$desc_limit = $_POST['gd_desc_limit'];
			$use_tag_limit = $_POST['gd_use_tag_limit'];
			$tag_limit = $_POST['gd_tag_limit'];
			
			if($sub_active){
				if($sub_units=='D'){$mult = 1;}
				if($sub_units=='W'){$mult = 7;}
				if($sub_units=='M'){$mult = 30;}
				if($sub_units=='Y'){$mult = 365;}
				$days = $mult;
			}
	
	
	
	if($id != '')
			{
				
				$get_oldposttype = $wpdb->get_row($wpdb->prepare("SELECT post_type, is_default FROM ".$plugin_prefix."price WHERE pid=%d", array($id)));
							
				$get_oldpricedata = $wpdb->get_results($wpdb->prepare("SELECT post_type FROM ".$plugin_prefix."price WHERE post_type=%s",array($get_oldposttype->post_type)));
	
				
				if(count($get_oldpricedata) > 1)
				{
					
					if($is_default && $get_oldposttype->post_type == $post_type){
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='0' WHERE pid!=%d AND post_type=%s",array($id,$post_type)));
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='1' WHERE pid=%d",array($id)));
						
					}elseif(!$get_oldposttype->is_default && $get_oldposttype->post_type != $post_type ){
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET post_type=%s WHERE pid=%d",array($post_type,$id)));
						
						if($is_default)
						{
							$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='0' WHERE pid!=%d AND post_type=%s",array($id,$post_type)));
							
							$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='1' WHERE pid=%d",array($id)));
							
						}
						
					}elseif($get_oldposttype->is_default){
						
						$error = __("You have not change this package because its default package for ".$get_oldposttype->post_type.".", GEODIRPAYMENT_TEXTDOMAIN);
							
					}
					
				}
				else
				{
					
					if($get_oldposttype->is_default != $is_default || $get_oldposttype->post_type != $post_type)
					{
						$error = __("You have not change this package because its default package for ".$get_oldposttype->post_type.".", GEODIRPAYMENT_TEXTDOMAIN);
					}
					
				}
				
			}
			
			
			
			if(empty($error))
			{
			
				if($cat)
				{
					$cat = implode(',',$cat);
				}
				
				if(!$title_desc)
				{
					$title_desc = $title.' : number of publish days are '.$days.' (<span id="'.str_replace(' ','_',$title).'">'.$amount.' '.geodir_get_currency_type().'</span>)';
				}
				
				//$title_desc = addslashes($title_desc);
				
				do_action('geodir_before_save_package');
				
				if($id)
				{	
				
								$wpdb->query(
									$wpdb->prepare(
										"update ".GEODIR_PRICE_TABLE." set 
										title=%s, 
										amount=%f,
										days=%d,
										status=%d,
										cat=%s,
										is_featured=%d,
										title_desc=%s, 
										image_limit=%s, 
										cat_limit=%s, 
										google_analytics = %d, 
										sendtofriend = %d, 
										post_type=%s, 
										link_business_pkg=%s, 
										recurring_pkg=%s, 
										reg_desc_pkg=%s, 
										reg_fees_pkg=%s, 
										downgrade_pkg=%s, 
										sub_active=%s,
										display_order=%d,
										sub_units=%s,
										sub_units_num=%s,
										sub_num_trial_days=%s, 
										sub_num_trial_units=%s, 
										sub_units_num_times=%s,
										use_desc_limit=%d,
										desc_limit=%d,
										use_tag_limit=%d,
										tag_limit=%d where pid=%d",
										
										array($title,$amount,$days,$status,$cat,$is_featured,$title_desc,$image_limit,$cat_limit,$google_analytics,$sendtofriend,$post_type,$link_business_pkg,$recurring_pkg,$reg_desc_pkg,$reg_fees_pkg,$downgrade_pkg,$sub_active,$display_order,$sub_units,$sub_units_num,$sub_num_trial_days,$sub_num_trial_units,$sub_units_num_times, $use_desc_limit, $desc_limit, $use_tag_limit, $tag_limit,$id)
									)
							);		
										$msg = 'Price updated successfully.';
				}
				else
				{
					
									 
					$wpdb->query(
						$wpdb->prepare(
							"insert into ".GEODIR_PRICE_TABLE." set
							title=%s, 
							amount=%f, 
							days=%d, 
							status=%d,
							is_default=%d,
							cat=%s,
							is_featured=%d,
							title_desc=%s, 
							image_limit=%s, 
							cat_limit=%s, 
							google_analytics = %d,
							sendtofriend = %d,
							post_type=%s, 
							link_business_pkg=%s, 
							recurring_pkg=%s, 
							reg_desc_pkg=%s, 
							reg_fees_pkg=%s, 
							downgrade_pkg=%s, 
							sub_active=%s,
							display_order=%d,
							sub_units=%s,
							sub_units_num=%s,
							sub_num_trial_days=%s,
							sub_num_trial_units=%s,  
							sub_units_num_times= %s,
							use_desc_limit=%d,
							desc_limit=%d,
							use_tag_limit=%d,
							tag_limit=%d",
							array($title, $amount, $days, $status,$is_default,$cat,$is_featured,$title_desc,$image_limit,$cat_limit,$google_analytics,$sendtofriend,$post_type,$link_business_pkg,$recurring_pkg,$reg_desc_pkg,$reg_fees_pkg,$downgrade_pkg,$sub_active,$display_order,$sub_units,$sub_units_num,$sub_num_trial_days,$sub_num_trial_units,$sub_units_num_times, $use_desc_limit, $desc_limit, $use_tag_limit, $tag_limit)
						)
					
					);
					
					
					$id = $wpdb->insert_id;
					
					$msg = __('Price created successfully.', GEODIRPAYMENT_TEXTDOMAIN);
					
					if($is_default)
					{
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='0' WHERE pid!=%d AND post_type=%s",array($id,$post_type)));
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='1' WHERE pid=%d",array($id)));
					}
				}
				
				do_action('geodir_after_save_package', $id);
				
				$post_fields = isset($_REQUEST['pay_custom_fields']) ? $_REQUEST['pay_custom_fields'] : '';
				
				$all_packages = $wpdb->get_results($wpdb->prepare("SELECT pid FROM ".$plugin_prefix."price WHERE post_type=%s",array($post_type)));
				
				$packages_default_field = '';
				if($all_packages){
						
						foreach($all_packages as $pkg){
							$packages_default_field .= ','.$pkg->pid;
						}
						
				}
				
				/* --- start posts default fields --- */
				$default_address_field = $wpdb->get_row($wpdb->prepare("select id from ".GEODIR_CUSTOM_FIELDS_TABLE." where is_admin='1' and field_type='address' and post_type=%s", array($post_type)));
				
				$post_fields[] = $default_address_field->id;
				$post_default_fields[] = $default_address_field->id;
				
				
				$default_taxonomy_field =	$wpdb->get_row($wpdb->prepare("select id from ".GEODIR_CUSTOM_FIELDS_TABLE." where is_admin='1' and field_type='taxonomy' and post_type=%s",array($post_type)));
				
				$post_fields[] = $default_taxonomy_field->id;
				$post_default_fields[] = $default_taxonomy_field->id;
				
				/* --- end posts default fields --- */
				
				if(!empty($post_fields))
				{
					$post_fields_main_array = array($id,$post_type);
					
					$post_fields_length = count($post_fields);
					$post_fields_format = array_fill(0, $post_fields_length, '%d');
					$post_fields_format = implode(',', $post_fields_format);
					
					$post_fields_main_array = array_merge($post_fields_main_array,$post_fields);
					
					$post_default_main_array = array($post_type);
					$post_default_fields_length = count($post_default_fields);
					$post_default_format = array_fill(0, $post_default_fields_length, '%d');
					$post_default_format = implode(',', $post_default_format);	
					$post_default_main_array = array_merge($post_default_main_array,$post_default_fields);
					
					$old_package_change = $wpdb->get_results($wpdb->prepare("SELECT id, packages from ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages)",array($id)));
					
					if(!empty($old_package_change))
					{
						
						foreach($old_package_change as $key){
							
							$pck_array = explode(',', $key->packages);
							
							$packages = '';
							$comma = '';
							foreach($pck_array as $pck_key)	
							{
								if($pck_key != $id && $pck_key != '')
								{
									$packages .= $comma.$pck_key;
									$comma = ',';
								}
							}
							
							$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = %s WHERE id=%d",array($packages,$key->id)));
						}
						
					}
					
					
					$wpdb->query(
						$wpdb->prepare(
						"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = CONCAT('',TRIM(BOTH ',' FROM packages),',%d,') WHERE post_type = %s AND id IN ($post_fields_format)",
						$post_fields_main_array	
						)
					);
	
	
	
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = '".$packages_default_field."' WHERE post_type = %s AND id IN ($post_default_format)",
						$post_default_main_array
					)
				);
			
			}
					
				$msg = urlencode($msg);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&success_msg=".$msg;
				wp_redirect($location);
				exit;
				
			}
			else
			{
				$error = urlencode($error);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&error_msg=".$error;
				wp_redirect($location);
				exit;
			}
		}
	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
	
	
}


//============ AJAX FUNCTION FOR ADD/EDIT COUPON ============
function geodir_add_edit_coupon()
{
	global $wpdb;
	
	if(current_user_can( 'manage_options' )){
	
		if($_POST['gd_add_coupon'] == 'addprice' && isset($_REQUEST['coupon_add_update_nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['coupon_add_update_nonce'], 'coupon_add_update' ) )
					return;
					
			
			$id = $_POST['gd_id'];
			
			$coupon_code  = $_POST['coupon_code'];
			
			$post_type = $_POST['post_type'];
			
			$discount_type = $_POST['discount_type'];
			
			$discount_amount = $_POST['discount_amount'];
			
			$gd_status = $_POST['gd_status'];
			
			$recurring = $_POST['gd_recurring'];
			
			if(!empty($post_type)){
				
				$post_type = implode(',', $post_type);
				
			}
			
			
			$error = '';
			$extra_query = '';
			if($id != ''){
				$extra_query = " AND cid != '".$id."'";	
			}
			
			
			$duplicate =	$wpdb->get_var($wpdb->prepare("SELECT cid FROM ".COUPON_TABLE." WHERE coupon_code = %s".$extra_query,array($coupon_code)));
			
			if($duplicate){
				
				$error = __("Coupon code already exists.", GEODIRPAYMENT_TEXTDOMAIN);
			
			}
			
			if(empty($error)){
			
				if($id != ''){
					
					$wpdb->query($wpdb->prepare(
					"UPDATE ".COUPON_TABLE." SET coupon_code=%s, post_types=%s, discount_type=%s, discount_amount=%f, status=%s, recurring=%s WHERE cid=%d",array($coupon_code,$post_type,$discount_type,$discount_amount,$gd_status,$recurring,$id)));
					
					$msg = __('Coupon code updated successfully.', GEODIRPAYMENT_TEXTDOMAIN);
					
				}else{
					
				
					$wpdb->query(
					$wpdb->prepare(
					"INSERT INTO ".COUPON_TABLE." SET coupon_code=%s, post_types=%s, discount_type=%s, discount_amount=%f, status=%s, recurring=%s",
					array($coupon_code,$post_type,$discount_type,$discount_amount,$gd_status,$recurring)
					)
					);
					
					$msg = __('Coupon code submitted successfully.', GEODIRPAYMENT_TEXTDOMAIN);
					
				}
				
				$msg = urlencode($msg);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&success_msg=".$msg;
				wp_redirect($location);
				exit;
				
			}else{
				
				$error = urlencode($error);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&error_msg=".$error;
				wp_redirect($location);
				exit;
				
			}
		
		}
	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
	
}

//============ AJAX FUNCTION FOR DELETE COUPON ============
function geodir_del_coupon()
{
	global $wpdb, $price_db_table_name;
	
	if(current_user_can( 'manage_options' )){

		if($_REQUEST['pagetype'] == 'delete' && $_REQUEST['id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'coupon_code_delete_'.$_REQUEST['id'] ) )
						return;
			
						
			$cid = $_REQUEST['id'];
			
			$wpdb->query($wpdb->prepare("delete from ".COUPON_TABLE." where cid=%d",array($cid)));
			
			$msg = __('Coupon deleted successfully.', GEODIRPAYMENT_TEXTDOMAIN);
			
			$msg = urlencode($msg);
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&success_msg=success&success_msg=".$msg;
			wp_redirect($location);
			exit;
		}
	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	}

}

//============ AJAX FUNCTION FOR DELETE COUPON ============
function geodir_allow_coupon_code()
{
		
		if(current_user_can( 'manage_options' )){
		
			if(isset($_REQUEST['_wpnonce'])){
			
				if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'allow_coupon_code_nonce' ) )
					return;
					
				update_option('geodir_allow_coupon_code', $_REQUEST['value']);
				
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager";
				wp_redirect($location);
				exit;
			
			}
		
		}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
		
}

//============AJAX FUNCTION FOR UPDATE PAYMENT METHOD ============
function geodir_payment_method_update()
{
	global $wpdb;
	
	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['payaction'] == 'trouble_shoot' && $_REQUEST['pay_method'] != '' && isset($_REQUEST['nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['nonce'], 'payment_trouble_shoot'.$_REQUEST['pay_method'] ) )
				return;
			
			
			$pay_method = str_replace('payment_method_', '', $_REQUEST['pay_method']);
			
			$paymenthodinfo = array();
			
			if($pay_method != '')
				$paymenthodinfo = geodir_get_additional_pay_options($pay_method);
			
			
			if(is_array($paymenthodinfo) && !empty($paymenthodinfo)){
			
				$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s order by option_id asc", array('payment_method_'.$pay_method));
			
				$paymentinfo = $wpdb->get_row($paymentsql);
				
				if($paymentinfo->option_id){
				
					$wpdb->query($wpdb->prepare("update $wpdb->options set option_value=%s where option_id=%d",array(serialize($paymenthodinfo),$paymentinfo->option_id)));
					
				}
			
			}
			
			$msg = __('Price method updated successfully.', GEODIRPAYMENT_TEXTDOMAIN);
			$msg = urlencode($msg);
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&success_msg=".$msg;
			wp_redirect($location);
			exit;
			
		}
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}

}


//============AJAX FUNCTION FOR DELETE PRICE============
function geodir_del_price()
{
	global $wpdb, $price_db_table_name,$plugin_prefix;
	
	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['pagetype'] == 'delete' && $_REQUEST['id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'package_action_'.$_REQUEST['id'] ) )
			return;
			
			$pid = $_REQUEST['id'];
			
			/* --- delete package detail default package set --- */
			$post_type = $wpdb->get_var($wpdb->prepare("select post_type from ".GEODIR_PRICE_TABLE." where pid=%d",array($pid)));
			
			$table = $plugin_prefix.$post_type.'_detail';
			
			$default_package = geodir_get_default_package($post_type);
			$package_id = $default_package->pid;
			
			$wpdb->query($wpdb->prepare("update ".$table." set package_id=%d where package_id=%d",array($package_id,$pid)));
		
			$wpdb->query($wpdb->prepare("delete from ".GEODIR_PRICE_TABLE." where pid=%d",array($pid)));
		
			
			$old_package_change =	$wpdb->get_results($wpdb->prepare("SELECT id, packages from geodir_custom_fields WHERE FIND_IN_SET(%s, packages)",array($pid)));
			
					
					if(!empty($old_package_change))
					{				
						foreach($old_package_change as $key){
						 $pck_array = explode(',', $key->packages);
							$packages = '';
							$comma = '';
							foreach($pck_array as $pck_key)	
							{
								if($pck_key != $pid && $pck_key != '')
								{
									$packages .= $comma.$pck_key;
									$comma = ',';
								}
							}
							$wpdb->query($wpdb->prepare("UPDATE geodir_custom_fields SET packages = %s WHERE id=%d",array($packages,$key->id)));
						}
						
					}
			
			$msg = __('Price deleted successfully.', GEODIRPAYMENT_TEXTDOMAIN);
			$msg = urlencode($msg);
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&success_msg=".$msg;
			wp_redirect($location);
			exit;
		}
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}

}

//============AJAX FUNCTION FOR CHANGE PAYMENT METHOD SETTING============
function geodir_change_payment_method_setting()
{
	global $wpdb;
	
	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['paymentsetting'] && isset($_REQUEST['update_payment_settings_nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['update_payment_settings_nonce'], 'payment_options_status_update_'.$_REQUEST['id'] ) )
				return;
				
			
			$paymentupdsql = $wpdb->prepare("select option_value from $wpdb->options where option_id=%d",array($_REQUEST['id']));
			
			$paymentupdinfo = $wpdb->get_results($paymentupdsql);
			if($paymentupdinfo)
			{
				foreach($paymentupdinfo as $paymentupdinfoObj)
				{
					$option_value = unserialize($paymentupdinfoObj->option_value);
					$payment_method = trim($_POST['payment_method']);
					$display_order = trim($_POST['display_order']);
					$payment_isactive = $_POST['payment_isactive'];
					$payment_mode = $_POST['payment_mode'];
					if($payment_method)
					{
						$option_value['name'] = $payment_method;
					}
					$option_value['display_order'] = $display_order;
					$option_value['isactive'] = $payment_isactive;
					$option_value['payment_mode'] = $payment_mode;
					$paymentOpts = $option_value['payOpts'];
					for($o=0;$o<count($paymentOpts);$o++)
					{
						$paymentOpts[$o]['value'] = $_POST[$paymentOpts[$o]['fieldname']];
					}
					$option_value['payOpts'] = $paymentOpts;
					$option_value_str = serialize($option_value);	
				}
			}
			
			$updatestatus = $wpdb->prepare("update $wpdb->options set option_value= %s where option_id=%d",array($option_value_str,$_REQUEST['id']));
			
			$wpdb->query($updatestatus);
			
			$msg = __('Payment Method Updated Succesfully.', GEODIRPAYMENT_TEXTDOMAIN);
			$msg = urlencode($msg);
			
			wp_redirect(admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&success_msg=".$msg);
			
			exit;
		}
	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}

} 


//============AJAX FUNCTION FOR CHANGE PAYMENT METHOD STATUS============
function geodir_change_payment_method_status()
{
		global $wpdb;
		
		if(current_user_can( 'manage_options' )){
		
		if($_GET['status']!='' && $_GET['id']!='' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'payment_options_status_update_'.$_GET['id'] ) )
			return;
			
			$paymentupdsql = $wpdb->prepare("select option_value from $wpdb->options where option_id=%d",array($_GET['id']));
			
			$paymentupdinfo = $wpdb->get_results($paymentupdsql);
			
			if($paymentupdinfo)
			{
				foreach($paymentupdinfo as $paymentupdinfoObj)
				{
					$option_value = unserialize($paymentupdinfoObj->option_value);
					
					$option_value['isactive'] = $_GET['status'];
					
					$option_value_str = serialize($option_value);
					
				}
			}
			
			$updatestatus = $wpdb->prepare("update $wpdb->options set option_value= %s where option_id=%d",array($option_value_str,$_GET['id']));
			
			$wpdb->query($updatestatus);
		}
		
		$msg = 'Payment Method Status Updated Successfully.';
		$msg = urlencode($msg);
		wp_redirect(admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&success_msg=".$msg);
		
		exit;
		
		}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
}

//============AJAX FUNCTION FOR CHANGE INVOICE STATUS============
function geodir_change_invoice_status()
{
	global $wpdb;

	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['postid']!='' && $_REQUEST['postid']!='' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'invoice_status_update_nonce' ) )
				return;
				
			$id = $_REQUEST['invoiceid'];
			
			$invoice_info = geodir_get_invoice($id);
			$invoice_package_id = '';
			if (!empty($invoice_info) && isset($invoice_info->package_id) ) {
				$invoice_package_id = $invoice_info->package_id;
				$invoice_alive_days = $invoice_info->alive_days;
				$invoice_status = $invoice_info->status;
			}
					
			$my_post = array();
			$my_post['ID'] = $_REQUEST['postid'];
			$status = $_REQUEST['inv_status'];
			
			if($status=='paid') {
				$my_post['post_status'] = 'publish';
				
				$post_id = $_REQUEST['postid'];
				$post_info = get_post($post_id);
				$geodir_post_info = geodir_get_post_info($post_id);
				
				if (!empty($geodir_post_info) && !empty($post_info) && isset($post_info->post_status)) {
					$post_package_id = $geodir_post_info->package_id;
					$post_expire_date = $geodir_post_info->expire_date;
					
					if (strtolower($invoice_status)!='paid' && !empty($invoice_package_id) && $invoice_alive_days>0 && $invoice_package_id==$post_package_id && strtolower($post_expire_date)!='never' && strtotime($post_expire_date) >= strtotime(date('Y-m-d')) && $post_info->post_status=='publish') {
						$expire_date = date('Y-m-d', strtotime($post_expire_date."+".$invoice_alive_days." days"));
						geodir_save_post_meta($post_id, 'alive_days', ($geodir_post_info->alive_days + $invoice_alive_days));
						geodir_save_post_meta($post_id, 'expire_date', $expire_date);
					} else {
						if ($post_info->post_status!='publish') {
							$my_post['post_date'] = current_time( 'mysql' );
							$my_post['post_date_gmt'] = current_time( 'mysql', 1 );
							
							$alive_days = (int)$geodir_post_info->alive_days;
							$expire_date = $alive_days>0 ? date('Y-m-d', strtotime(date('Y-m-d')."+".$alive_days." days")) : 'Never';
		
							geodir_save_post_meta($post_id, 'expire_date', $expire_date);
						}
					}
				}
			} else {
				$my_post['post_status'] = 'draft';
			}		

			// Update the post into the database
			wp_update_post( $my_post );
			
			// Update invoice status
			geodir_update_invoice_status($id, $status);
			
			$msg = urlencode(GD_INVOICE_MSG);
		
		wp_redirect(admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_invoice_list&success_msg=".$msg);
		exit;
		
		}
	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
		
	
}


//============ CHECK COUPON CODE BY LISTING TYPE ============


function geodir_build_payment_list(){
	
	global $post, $package_id;
	
	$listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';
	
	if(empty($listing_type)){
	
		$listing_type = $post->post_type;
	}
	
	if(isset($_REQUEST['package_id'])){
		
		$package_id = $_REQUEST['package_id'];
		
	}elseif(isset($post->package_id) && $post->package_id != ''){
		
		$listing_type = $post->post_type;
		$package_id = $post->package_id;
		
	}else{
		
		$default_package = geodir_get_default_package($listing_type);
		$package_id = $default_package->pid;
		
	}
	
	$package_info = geodir_get_package_info($package_id);
	
	$package_list_info = geodir_package_list_info($listing_type);
		
	
	$postlink = get_permalink( get_option('geodir_add_listing_page') );
  $postlink = geodir_getlink($postlink,array('listing_type'=>$listing_type),false);
	
	if(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
		$postlink = geodir_getlink($postlink,array('pid'=>$_REQUEST['pid']),false);
	}
	
	echo '<div class="geodir_price_package_row geodir_form_row clearfix ">';
	
	
	if(isset($_REQUEST['package_id']) || (!isset($_REQUEST['pid']) || $_REQUEST['pid'] == '')){
		
			
	echo '<h5>'.SELECT_PACKAGE_TEXT.'</h5>';


	foreach($package_list_info as $pkg){ 
		
		$alive_days = 'unlimited';
		$post_pkg_link = '';
		if($pkg->days)
		$alive_days = $pkg->days;
		
		$post_pkg_link = geodir_getlink($postlink,array('package_id'=>$pkg->pid),false);
		
		?>
			<div id="geodir_price_package_<?php echo $pkg->pid; ?>" class="geodir_package">
			<input name="package_id" type="radio" value="<?php echo $pkg->pid;?>"  <?php if($package_id == $pkg->pid) echo 'checked="checked"';?> onclick="window.location.href='<?php echo $post_pkg_link;?>'">&nbsp;
			
			<?php 
			
			_e(stripslashes($pkg->title_desc), GEODIRPAYMENT_TEXTDOMAIN);
			
			?>
			</div>
		
		<?php }
	}
	
	echo '</div>';
	
	
		
}


function geodir_build_coupon(){

	global $post;
	
	$listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';
	
	if(empty($listing_type) && (isset($_REQUEST['pid']) || $_REQUEST['pid'] != '') )
		$listing_type = get_post_type( $_REQUEST['pid'] );
	
	
	if(get_option('geodir_allow_coupon_code') && geodir_is_valid_coupon($listing_type) && ((!isset($_REQUEST['pid']) || $_REQUEST['pid'] == '') || (isset($_REQUEST['package_id']) && $_REQUEST['package_id']!= ''))){
			
			$coupon_code = isset($post->coupon_code) ? $post->coupon_code : '';
			
		?>
		<h5><?php echo COUPON_CODE_TITLE_TEXT;?></h5>
		<div id="geodir_coupon_code_row" class="geodir_form_row clearfix" >
				<label><?php echo PRO_ADD_COUPON_TEXT;?></label>
				 <input name="coupon_code" id="coupon_code" value="<?php echo esc_attr(stripslashes($coupon_code)); ?>" type="text" class="geodir_textfield" maxlength="100"  />
				 <span class="geodir_message_note"><?php echo COUPON_NOTE_TEXT; ?></span>
		</div><?php
	}

}


function geodir_get_payable_amount_with_coupon( $total_amt, $coupon_code ) {
	$discount_amt = geodir_get_discount_amount( $coupon_code, $total_amt );
	
	$discount_amt = apply_filters( 'geodir_payment_filter_payable_amount_with_coupon', $discount_amt, $coupon_code, $total_amt );

	if ( $discount_amt > 0 ) {
		return $total_amt - $discount_amt;
	} else {
		return $total_amt;
	}
}

function geodir_get_discount_amount($coupon,$amount)
{

	global $wpdb;

	if($coupon!='' && $amount>0)
	{
		
		$couponinfo =	$wpdb->get_row($wpdb->prepare("SELECT * FROM ".COUPON_TABLE." WHERE coupon_code=%s",array($coupon)));
		
		if($couponinfo)
		{
			
			if($couponinfo->discount_type=='per')
			{
			
				$discount_amt = ($amount*$couponinfo->discount_amount)/100;
				
			}elseif($couponinfo->discount_type=='amt')
			{
			
				$discount_amt = $couponinfo->discount_amount;
			
			}
			
			return number_format($discount_amt, 2, '.', '');
			
		}

	}

	return '0';			

}

function geodir_is_valid_coupon($post_type, $coupon='')
{

	global $wpdb;
	
	$query = '';
	if($coupon)
		$query = " AND coupon_code = '".$coupon."'";
	
	
	$couponinfo =	$wpdb->get_var($wpdb->prepare("SELECT cid FROM ".COUPON_TABLE." WHERE FIND_IN_SET(%s, post_types) AND status='1' ".$query,array($post_type)));
	
	
	if($couponinfo)
	{
		return true;
	}

	return false;

}

function geodir_display_payment_messages(){

	if(isset($_REQUEST['success_msg']) && $_REQUEST['success_msg'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' . $_REQUEST['success_msg'] . '</strong></p></div>';			
				
	}
	
	if(isset($_REQUEST['error_msg']) && $_REQUEST['error_msg'] != '')
	{
			echo '<div id="payment_message_error" class="updated fade"><p><strong>' . $_REQUEST['error_msg'] . '</strong></p></div>';			
				
	}
	
}


function geodir_display_post_upgrade_link(){
	
	global $post,$preview; 
	
	if(!$preview){
	
		if(is_user_logged_in() && $post->post_author == get_current_user_id()){
								
				$post_id = $post->ID;
				$post_package_id = isset($post->package_id) ? $post->package_id : '';
				if(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
					$post_id = $_REQUEST['pid'];
					
					if(empty($post_package_id))
						$post_package_id = geodir_get_post_meta($post_id,'package_id',true);
				}
				
				$postlink = get_permalink( get_option('geodir_add_listing_page') );
				$editlink = geodir_getlink($postlink,array('pid'=>$post_id),false);
				
				
				$post_status = get_post_status( $post_id );
				
				// show renew link before pre expiry days * 2
				$post_expire_date = isset($post->expire_date) ? $post->expire_date : '';
				$preexpiry_notice = false;
				if (get_option('geodir_listing_preexpiry_notice_disable') && get_option('geodir_listing_preexpiry_notice_days')!='' && $post_expire_date != '0000-00-00' && $post_expire_date != '' && strtolower($post_expire_date) != 'never' && strtotime($post_expire_date) > strtotime(date('01-01-1970')))  {
					$preexpiry_date = strtotime($post_expire_date) - (DAY_IN_SECONDS * get_option('geodir_listing_preexpiry_notice_days') * 2);
					$preexpiry_notice = $preexpiry_date <= strtotime(date('Y-m-d')) ? true : false;
				}
				
				$action_link = __('Upgrade Listing', GEODIRPAYMENT_TEXTDOMAIN);
				if($post_status == 'draft' || $preexpiry_notice){
					$action_link = __('Renew Listing', GEODIRPAYMENT_TEXTDOMAIN);
				}
		
				$upgradelink = geodir_getlink($editlink,array('package_id'=>$post_package_id),false);  
				
				echo '<p class="geodir_upgrade_link"><i class="fa fa-chevron-circle-up"></i> <a href="'.$upgradelink.'">'.$action_link.'</a></p>';
		}
		
	}
	
}

function geodir_display_post_upgrade_link_on_listing(){
	
	global $post;
	
	$addplacelink = get_permalink( get_option('geodir_add_listing_page') );
	$editlink = geodir_getlink($addplacelink,array('pid'=>$post->ID),false);
	
	$upgradelink = geodir_getlink($editlink,array('package_id'=>$post->package_id),false); 
	
	$post_status = get_post_status( $post->ID );
	
	// show renew link before pre expiry days * 2
	$post_expire_date = isset($post->expire_date) ? $post->expire_date : '';
	$preexpiry_notice = false;
	if (get_option('geodir_listing_preexpiry_notice_disable') && get_option('geodir_listing_preexpiry_notice_days')!='' && $post_expire_date != '0000-00-00' && $post_expire_date != '' && strtolower($post_expire_date) != 'never' && strtotime($post_expire_date) > strtotime(date('01-01-1970')))  {
		$preexpiry_date = strtotime($post_expire_date) - (DAY_IN_SECONDS * get_option('geodir_listing_preexpiry_notice_days') * 2);
		$preexpiry_notice = $preexpiry_date <= strtotime(date('Y-m-d')) ? true : false;
	}
				
	$action_link = __('Upgrade Listing', GEODIRPAYMENT_TEXTDOMAIN);
	if($post_status == 'draft' || $preexpiry_notice){
		$action_link = __('Renew Listing', GEODIRPAYMENT_TEXTDOMAIN);
	}
	
	 echo '<a href="'.$upgradelink.'" class="geodir-upgrade" title="'. $action_link.'" >'.$action_link.'</a>'; 


}


function geodir_expire_check() {
	global $wpdb, $plugin_prefix;
	$current_date = date('Y-m-d');
	
	$geodir_postypes = geodir_get_posttypes();
	
	$upload_dir = wp_upload_dir();
	$upload_basedir = $upload_dir['basedir'];	
	
	if (get_option('geodir_listing_expiry')) {
		foreach ($geodir_postypes as $post) {
			$table = $plugin_prefix.$post.'_detail';
			if (get_option('geodir_listing_preexpiry_notice_disable')) {
				$number_of_grace_days = get_option('geodir_listing_preexpiry_notice_days');
				if ($number_of_grace_days=='') {
					$number_of_grace_days = 1;	
				}
				
				$today = date('Y-m-d', strtotime(date('Y-m-d')."+".(int)$number_of_grace_days." days"));
				$strtoday = $wpdb->get_var(("SELECT UNIX_TIMESTAMP( STR_TO_DATE( '".$today."','%Y-%m-%d'))"));
				
				$postid_str = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT p.ID, p.post_author, p.post_title from ".$table." detail, ".$wpdb->posts." p WHERE p.ID=detail.post_id AND detail.expire_date!='Never' AND detail.expire_date!='' AND detail.expire_notification='false' AND unix_timestamp(detail.expire_date)<=%s",
						array($strtoday)
					)
				);
				
				if (!empty($postid_str)) {
					foreach ($postid_str as $postid_str_obj) {
						geodir_payment_clientEmail($postid_str_obj->ID,$postid_str_obj->post_author,'expiration');
						$wpdb->query(
							$wpdb->prepare(
								"update ".$table." set expire_notification='true' where post_id=%d",
								array($postid_str_obj->ID)
							)
						);
					}
				}	
			}			
			
			$strcurrent = $wpdb->get_var(("SELECT UNIX_TIMESTAMP( STR_TO_DATE( '".$current_date."','%Y-%m-%d'))"));

			$postid_str = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, p.post_author, p.post_title, detail.package_id from ".$table." detail, ".$wpdb->posts." p WHERE p.ID=detail.post_id AND detail.expire_date!='Never' AND detail.expire_date!='' AND unix_timestamp(detail.expire_date)<=%s",
					array($strcurrent)
				)
			);

			if (!empty($postid_str)) {
				foreach ($postid_str as $postid_str_obj) {
					$post_id = $postid_str_obj->ID;
					$package_id = $postid_str_obj->package_id;
					
					$old_package_info = geodir_get_package_info($package_id);
					$old_image_limit = empty($old_package_info->image_limit) ? 0 : $old_package_info->image_limit;
					$old_cat_limit = empty($old_package_info->cat_limit) ? 0 : $old_package_info->cat_limit;
					$downgrade_pkg = $old_package_info->downgrade_pkg;
					$package_info = (int)$downgrade_pkg>0 ? geodir_get_package_info($downgrade_pkg) : array();

					if ((int)$downgrade_pkg>0 && $downgrade_pkg != '' && !empty($package_info)) {
						$featured = $package_info->is_featured;
						$image_limit = empty($package_info->image_limit) ? 0 : $package_info->image_limit;
						$cat_limit = empty($package_info->cat_limit) ? 0 : $package_info->cat_limit;
						$days = $package_info->days;
						$exclude_cats = $package_info->cat;
						
						if ($cat_limit != 0 && $cat_limit < $old_cat_limit) {
							
							$terms = wp_get_post_terms($post_id, $post.'category', array("fields" => "all"));
							
							$term_ids = array();
							foreach ($terms as $termsObj) {
								if ($termsObj->parent==0) {
									$term_ids[] = $termsObj->term_id;
								}
							}
							
							$cat_arr = array_slice($term_ids, 0, $cat_limit);
							$term_ids = implode(",", $cat_arr);
							
							wp_set_object_terms($post_id, $cat_arr, $post.'category'  );
							
							$post_default_category = geodir_get_post_meta($post_id,'default_category');
							
							if ($post_default_category != '' && !in_array($post_default_category, $cat_arr)) {
								$post_default_category = $cat_arr[0];
								geodir_save_post_meta($post_id, 'default_category', $post_default_category);
							}
							
							geodir_set_postcat_structure($post_id,$post.'category',$post_default_category,'');
						}
						
						$post_images  = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT * FROM ".GEODIR_ATTACHMENT_TABLE." WHERE `post_id`=%d order by menu_order asc",
								array($post_id)
							)
						);
						
						$count_post_images = count($post_images);
						
						if ($image_limit != 0 && $image_limit < $old_image_limit && $count_post_images > $image_limit) {
							
							$post_images_arr = array_slice($post_images, $image_limit, $image_limit);
							
							foreach ($post_images_arr as $perimage) {
								
								if (file_exists($upload_basedir.$perimage->file)) {
									unlink($upload_basedir.$perimage->file);
								}
								
								$wpdb->query(
									$wpdb->prepare(
										"DELETE FROM ".GEODIR_ATTACHMENT_TABLE." WHERE ID=%d",
										array($perimage->ID)
									)
								);
							}
						}
						
						$expire_date = 'Never';
						if((int)$days > 0 && $days != '') {
							$expire_date = date('Y-m-d', strtotime(date('Y-m-d')."+".(int)$days." days"));
						}						
						geodir_save_post_meta($post_id, 'is_featured', $featured);
						geodir_save_post_meta($post_id, 'package_id', $downgrade_pkg); 
						geodir_save_post_meta($post_id, 'paid_amount', ''); 
						geodir_save_post_meta($post_id, 'paymentmethod', ''); 
						geodir_save_post_meta($post_id, 'alive_days', $days); 
						geodir_save_post_meta($post_id, 'expire_date', $expire_date); 
						geodir_save_post_meta($post_id, 'expire_notification', 'false');
						
						$post_info = get_post($post_id);
						if (!empty($post_info) && isset($post_info->post_status) && $post_info->post_status!='publish') {
							$update_post = array();
							$update_post['post_status'] = 'publish'; 
							$update_post['ID'] = $post_id;
							wp_update_post($update_post);
						}
					} else {
						$post_info = get_post($post_id);
						$post_ex_status = get_option('geodir_listing_ex_status');
						if (!empty($post_info) && isset($post_info->post_status) && $post_info->post_status!=$post_ex_status) {
							$expire_post = array();
							$expire_post['post_status'] = $post_ex_status; 
							$expire_post['ID'] = $post_id;
							
							wp_update_post($expire_post); // update post expiry status
						}
					}					
				}				
			}			
		}
	}
}


// run the expiry check twice a day
if ( !wp_next_scheduled('geodir_task_hook') ) {
	wp_schedule_event( time(), 'twicedaily', 'geodir_task_hook' ); // hourly, daily and twicedaily
}

if (!function_exists('geodir_expire_check_cron')) {
	function geodir_expire_check_cron() {
		geodir_expire_check();
		//checkForUpdates();
	}
}

add_action('geodir_task_hook', 'geodir_expire_check_cron');


/* Payment Method Options diagnostic function */

function geodir_diagnose_payment_method_options()
{
	
	echo "Coming Soon..." ;

}

function geodir_pm_substr($string, $limit) {
	$max_length = (int)$limit;
	$text = trim($string);
	$tags   = array();
    $result = "";

    $is_open   = false;
    $grab_open = false;
    $is_close  = false;
    $in_double_quotes = false;
    $in_single_quotes = false;
    $tag = "";

    $i = 0;
    $stripped = 0;

    $stripped_text = strip_tags($text);

    while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
    {
        $symbol  = $text{$i};
        $result .= $symbol;

        switch ($symbol)
        {
           case '<':
                $is_open   = true;
                $grab_open = true;
                break;

           case '"':
               if ($in_double_quotes)
                   $in_double_quotes = false;
               else
                   $in_double_quotes = true;

            break;

            case "'":
              if ($in_single_quotes)
                  $in_single_quotes = false;
              else
                  $in_single_quotes = true;

            break;

            case '/':
                if ($is_open && !$in_double_quotes && !$in_single_quotes)
                {
                    $is_close  = true;
                    $is_open   = false;
                    $grab_open = false;
                }

                break;

            case ' ':
                if ($is_open)
                    $grab_open = false;
                else
                    $stripped++;

                break;

            case '>':
                if ($is_open)
                {
                    $is_open   = false;
                    $grab_open = false;
                    array_push($tags, $tag);
                    $tag = "";
                }
                else if ($is_close)
                {
                    $is_close = false;
                    array_pop($tags);
                    $tag = "";
                }

                break;

            default:
                if ($grab_open || $is_close)
                    $tag .= $symbol;

                if (!$is_open && !$is_close)
                    $stripped++;
        }

        $i++;
    }

    while ($tags)
        $result .= "</".array_pop($tags).">";

    return $result;
}

function geodir_payment_notification_add_bcc_option($settings) {
	if (!empty($settings)) {
		$new_settings = array();
		foreach ($settings as $setting) {
			if (isset($setting['id']) && $setting['id']=='site_bcc_options' && isset($setting['type']) && $setting['type']=='sectionend') {
				
				$geodir_bcc_expire_yes = array(  
											'name' => __( 'Expire listings', GEODIRPAYMENT_TEXTDOMAIN ),
											'desc' => __( 'Yes', GEODIRPAYMENT_TEXTDOMAIN ),
											'id' => 'geodir_bcc_expire',
											'std' => 'yes',
											'type' => 'radio',
											'value' => '1',
											'radiogroup' => 'start'
										);
				
				$geodir_bcc_expire_no = array(  
											'name' => __( 'Expire listings', GEODIRPAYMENT_TEXTDOMAIN ),
											'desc' => __( 'No', GEODIRPAYMENT_TEXTDOMAIN ),
											'id' => 'geodir_bcc_expire',
											'std' => 'yes',
											'type' => 'radio',
											'value' => '0',
											'radiogroup' => 'end'
										);
				
				$new_settings[] = $geodir_bcc_expire_yes;
				$new_settings[] = $geodir_bcc_expire_no;
			}
			$new_settings[] = $setting;
		}
		$settings = $new_settings;
	}
		
	return $settings;
}

function geodir_payment_post_view_extra_class( $class , $all_postypes='') {
	global $post;
	
	if(!$all_postypes){$all_postypes = geodir_get_posttypes();}
	
	$gdp_post_id = !empty( $post ) && isset( $post->ID ) ? $post->ID : NULL;
	$gdp_post_type = $gdp_post_id > 0 && isset( $post->post_type ) ? $post->post_type : NULL;
	$gdp_post_type = $gdp_post_type != '' && !empty( $all_postypes ) && in_array( $gdp_post_type, $all_postypes ) ? $gdp_post_type : NULL;
		
	if ( $gdp_post_id && $gdp_post_type ) {
		//$default_package = geodir_get_default_package( $gdp_post_type );
		
		$gdp_package_id = isset( $post->package_id ) ? $post->package_id : 0;
		//$gdp_package_id = !$gdp_package_id > 0 && !empty( $default_package ) && !empty( $default_package->pid ) ? $default_package->pid : $gdp_package_id;
		
		//$gdp_package_info = $gdp_package_id > 0 ? geodir_get_package_info( $gdp_package_id ) : NULL;
		
		$append_class = $gdp_package_id > 0 ? 'gd-post-pkg-' . $gdp_package_id : '';
		
		/*if ( !empty( $gdp_package_info ) ) {
			$append_class .= isset( $gdp_package_info->amount ) && (float)$gdp_package_info->amount > 0 ? ' gd-post-pkg-paid' : ' gd-post-pkg-free';			
		}*/
		
		if ( isset($post->paid_amountt) ) {
			$append_class .= isset( $gdp_package_info->amount ) && (float)$gdp_package_info->amount > 0 ? ' gd-post-pkg-paid' : ' gd-post-pkg-free';			
		}
		
		if ( $append_class != '' ) {	
			$class = $class != '' ? $class . ' ' . trim( $append_class ) : trim( $append_class );
		}
	}
	
	return $class;
}

function geodir_payment_get_units_to_days( $value, $unit ) {
	switch ( $unit ) {
		case 'W':
			$value = $value * 7;
		break;
		case 'M':
			$value = $value * 30;
		break;
		case 'Y':
			$value = $value * 365;
		break;
	}
	
	return $value;
}

/* display listing expire date on author dashboard listing*/
function geodir_payment_display_expire_date_on_listing() {
	global $post;
	
	$show_expire_date = get_option( 'geodir_listing_expiry' ) && get_option( 'geodir_payment_expire_date_on_listing' );
	
	$html = '';
	if ( $show_expire_date && get_current_user_id() ) {
		if ( geodir_is_page('author') && !empty( $post ) && isset( $post->post_author ) && $post->post_author == get_current_user_id() ) {
			$post_expire_date = geodir_get_post_meta( $post->ID, 'expire_date', true);
			$post_expire_time = strtotime( $post_expire_date );
			$current_date = date( 'Y-m-d' );
			
			$expire_date_text = __( 'Unknown', GEODIRPAYMENT_TEXTDOMAIN );
			$expire_date_class = 'geodir-expire-unknown';
			if ( $post_expire_date != '0000-00-00' && $post_expire_date != '' && ( strtolower( $post_expire_date ) == 'never' || $post_expire_time > strtotime( date( '01-01-1970' ) ) ) )  {
				if ( strtolower( $post_expire_date ) == 'never' ) {
					$expire_date_text = __( 'Never', GEODIRPAYMENT_TEXTDOMAIN );
					$expire_date_class = 'geodir-expire-never';
				} else {
					$expire_date_text = geodir_payment_time_diff( $post_expire_time );
					$expire_date_text = $post_expire_date . '(<font class="geodir-expire-diff">'.$expire_date_text.'</font>)';
					
					$expire_date_class = $post_expire_time >= strtotime( date( 'Y-m-d' ) ) ? 'geodir-expire-left' : 'geodir-expire-overdue';
				}
			}
			
			$html = '<span class="geodir-expire-date ' . $expire_date_class . '"><i class="fa fa-calendar"></i> <font class="geodir-expire-label">' . __( 'Expire date: ', GEODIRPAYMENT_TEXTDOMAIN ) . '</font><font class="geodir-expire-text">' . $expire_date_text . '</font></span>';
		}
	}
	
	if ( $html != '' ) {
		echo apply_filters( 'geodir_payment_filter_expire_date_on_listing', $html );
	}
}

/* display listing expire date on author listing detail*/
function geodir_payment_display_expire_date_on_detail() {
	global $post;
	
	$show_expire_date = get_option( 'geodir_listing_expiry' ) && get_option( 'geodir_payment_expire_date_on_detail' );
	
	$html = '';
	if ( $show_expire_date && get_current_user_id() ) {
		if ( geodir_is_page('detail') && !empty( $post ) && isset( $post->post_author ) && $post->post_author == get_current_user_id() ) {
			$post_expire_date = geodir_get_post_meta( $post->ID, 'expire_date', true);
			$post_expire_time = strtotime( $post_expire_date );
			$current_date = date( 'Y-m-d' );
			
			$expire_date_text = __( 'Unknown', GEODIRPAYMENT_TEXTDOMAIN );
			$expire_date_class = 'geodir-expire-unknown';
			if ( $post_expire_date != '0000-00-00' && $post_expire_date != '' && ( strtolower( $post_expire_date ) == 'never' || $post_expire_time > strtotime( date( '01-01-1970' ) ) ) )  {
				if ( strtolower( $post_expire_date ) == 'never' ) {
					$expire_date_text = __( 'Never', GEODIRPAYMENT_TEXTDOMAIN );
					$expire_date_class = 'geodir-expire-never';
				} else {
					$expire_date_text = geodir_payment_time_diff( $post_expire_time );
					$expire_date_text = $post_expire_date . '(<font class="geodir-expire-diff">'.$expire_date_text.'</font>)';
					
					$expire_date_class = $post_expire_time >= strtotime( date( 'Y-m-d' ) ) ? 'geodir-expire-left' : 'geodir-expire-overdue';
				}
			}
			
			$html = '<span class="geodir-expire-date ' . $expire_date_class . '"><font class="geodir-expire-label">' . __( 'Expire date: ', GEODIRPAYMENT_TEXTDOMAIN ) . '</font><font class="geodir-expire-text">' . $expire_date_text . '</font></span>';
			$html = '<p class="geodir_expire_date"><i class="fa fa-calendar"></i> ' . $html . '</p>';
		}
	}
	
	if ( $html != '' ) {
		echo apply_filters( 'geodir_payment_filter_expire_date_on_detail', $html );
	}
}

function geodir_payment_time_diff( $from, $to = '' ) {
	if ( empty( $to ) ) {
		$to = strtotime( date('Y-m-d') );
	}

	$diff = (int) abs( $to - $from );

	if ( $diff >= YEAR_IN_SECONDS ) {
		$years = round( $diff / YEAR_IN_SECONDS );
		
		if ( $years <= 1 ) {
			$years = 1;
		}
		
		if ( $to <= $from ) {
			$since = sprintf( _n( '%s year left', '%s years left', $years, GEODIRPAYMENT_TEXTDOMAIN ), $years );
		} else {
			$since = sprintf( _n( '%s year overdue', '%s years overdue', $years, GEODIRPAYMENT_TEXTDOMAIN ), $years );
		}
	} else {
		$days = round( $diff / DAY_IN_SECONDS );
		
		if ( $days <= 1 ) {
			$days = 1;
		}
		
		if ( $to == $from ) {
			$since = __( 'today', GEODIRPAYMENT_TEXTDOMAIN );
		} elseif ( $to < $from ) {
			$since = sprintf( _n( '%s day left', '%s days left', $days, GEODIRPAYMENT_TEXTDOMAIN ), $days );
		} else {
			$since = sprintf( _n( '%s day overdue', '%s days overdue', $days, GEODIRPAYMENT_TEXTDOMAIN ), $days );
		}
	}

	return $since;
}

// add fields on update listing form
add_action('geodir_after_main_form_fields', 'geodir_payment_after_main_form_fields', 1);
function geodir_payment_after_main_form_fields() {
	if (!empty($_REQUEST['pid']) && !empty($_REQUEST['package_id'])) {
		$post_type = get_post_type($_REQUEST['pid']);
		$prev_package_id = geodir_get_post_meta($_REQUEST['pid'], 'package_id', true);
		$prev_expire_date = geodir_get_post_meta($_REQUEST['pid'], 'expire_date', true);
		
		if ($post_type != '' && in_array($post_type, geodir_get_posttypes()) && $prev_package_id ==$_REQUEST['package_id'] && $prev_expire_date != '' && strtolower($prev_expire_date) != 'never' && strtotime($prev_expire_date) >= strtotime(date('Y-m-d'))) {
			echo '<input type="hidden" id="geodir_prev_package_id" name="geodir_prev_package_id" value="'.$prev_package_id.'" /><input type="hidden" id="geodir_prev_expire_date" name="geodir_prev_expire_date" value="'.$prev_expire_date.'" />';
		}
	}
}

// delete invoice
if ( is_admin() ) {
add_action( 'wp_ajax_geodir_del_invoice', 'geodir_del_invoice' );
}
function geodir_del_invoice($id='') {
	global $wpdb; 
if($id){$invoice_id = $id;}
	else{$invoice_id = intval( $_POST['invoice_id'] );}
if(!$invoice_id ){return;}
	
      $del = $wpdb->query($wpdb->prepare("DELETE FROM ".INVOICE_TABLE." WHERE id=%d", array($invoice_id)));
	if($del){echo 1;}else{echo 0;}
	die(); 
}

// delete invoice when post is deleted
add_action( 'admin_init', 'geodir_del_invoice_from_post_init' );
function geodir_del_invoice_from_post_init() {
    if ( current_user_can( 'delete_posts' ) )
        add_action( 'delete_post', 'geodir_del_invoice_from_post', 10 );
}

function geodir_del_invoice_from_post( $pid ) {
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM ".INVOICE_TABLE." WHERE post_id=%d", array($pid)));
    return true;
}

function geodir_diagnose_run_expire()
{	global $wpdb,$plugin_prefix;
	
	$is_error_during_diagnose = false;
	$output_str = '';
	
	geodir_expire_check();
	$output_str .= "<li>".__('Done' , GEODIRPAYMENT_TEXTDOMAIN )."</li>" ;



if($is_error_during_diagnose)
	{
		$info_div_class =  "geodir_problem_info" ;
		$fix_button_txt = "<input type='button' value='".__('Fix' , GEODIRPAYMENT_TEXTDOMAIN )."' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='ratings' />";
	}
	else
	{
		$info_div_class =  "geodir_noproblem_info" ;
		$fix_button_txt = '';
	}
	echo "<ul class='$info_div_class'>" ;
	echo $output_str ;
	echo  $fix_button_txt;
	echo "</ul>" ;
	
}
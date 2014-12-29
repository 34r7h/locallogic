<?php get_header(); 
###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'success-page', 'geodir-wrapper','');

	###### TOP CONTENT ######
	// action called before the main content
	do_action('geodir_before_main_content');
	// action called before the main content for setails page, this adds the sidebar top sectiona dn breadcrums
	do_action('geodir_success_before_main_content');
	
			###### MAIN CONTENT WRAPPERS OPEN ######
			// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
			do_action( 'geodir_wrapper_content_open', 'success-page', 'geodir-wrapper-content','');
			
			
			
if(isset($_REQUEST['renew']))
{
	$title = RENEW_SUCCESS_TITLE;
}else
{
	$title = POSTED_SUCCESS_TITLE;
}
?>
<?php
if(isset($postid) && $postid != ''){$_REQUEST['pid']=$postid;}
$paymentmethod = geodir_get_post_meta($_REQUEST['pid'],'paymentmethod',true);
$paid_amount = geodir_get_currency_sym().geodir_get_post_meta($_REQUEST['pid'],'paid_amount',true);
global $upload_folder_path;
if($paymentmethod == 'prebanktransfer')
{
	$filecontent = stripslashes(get_option('post_pre_bank_trasfer_msg_content'));
	if(!$filecontent)
	{
		$filecontent = POSTED_SUCCESS_PREBANK_MSG;
	}
}else
{
	$filecontent = stripslashes(get_option('post_added_success_msg_content'));
	if(!$filecontent)
	{
		$filecontent = POSTED_SUCCESS_MSG;
	}
}
if(!$_REQUEST['pid']){
	$title = PAYMENT_FAIL_TITLE;
	$filecontent = PAYMENT_FAIL_MSG;
}
?>
<div class="geodir_preview_section" >
    <h1><?php echo $title;?></h1>   
<?php
$store_name = get_option('blogname');
$siteurl = home_url();
$store_name_url = '<a href="'.$siteurl.'">'.$store_name.'</a>';
if($paymentmethod == 'prebanktransfer')
{
	/*$paymentupdsql = "select option_value from $wpdb->options where option_name='payment_method_".$paymentmethod."'";*/
	$paymentupdsql = $wpdb->prepare("select option_value from $wpdb->options where option_name=%s", array('payment_method_'.$paymentmethod));
	
	$paymentupdinfo = $wpdb->get_results($paymentupdsql);
	$paymentInfo = unserialize($paymentupdinfo[0]->option_value);
	$payOpts = $paymentInfo['payOpts'];
	$bankInfo = $payOpts[0]['value'];
	$accountinfo = $payOpts[1]['value'];
	$accountinfo2 = $payOpts[2]['value'];
}
$order_id = $_REQUEST['pid'];
//if(get_post_type($order_id)=='event')
//{
//	$post_link = home_url().'/?ptype=preview_event&alook=1&pid='.$_REQUEST['pid'];
//}else
//{
	$post_link =  get_permalink($_REQUEST['pid']); // home_url().'/?ptype=preview&alook=1&pid='.$_REQUEST['pid'];	
//}
$orderId = $_REQUEST['pid'];
$search_array = array('[#order_amt#]','[#bank_name#]','[#account_sortcode#]','[#account_number#]','[#orderId#]','[#site_name#]','[#submitted_information_link#]','[#submited_information_link#]','[#site_name_url#]');
$replace_array = array($paid_amount,$bankInfo,$accountinfo,$accountinfo2,$order_id,$store_name,$post_link,$post_link,$store_name_url);
$filecontent = str_replace($search_array,$replace_array,$filecontent);
echo $filecontent;
?> 
</div> <!-- geodir_preview_section #end -->

<?php 

			###### MAIN CONTENT WRAPPERS CLOSE ######
			// action called after the main content
			do_action('geodir_after_main_content');
			// this adds the closing html tags to the wrapper_content div :: ($type='')
			do_action( 'geodir_wrapper_content_close', 'details-page');
			
			
    
        ###### SIDEBAR ######
		do_action('geodir_detail_sidebar');
		
    
###### WRAPPER CLOSE ######	
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'success-page');
get_footer(); ?>
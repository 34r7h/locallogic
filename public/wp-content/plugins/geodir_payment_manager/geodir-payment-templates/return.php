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

$title = PAYMENT_SUCCESS_TITLE;?>
<div class="geodir_preview_section" >
<h1><?php echo $title;?></h1>   

<?php 
$filecontent = stripslashes(get_option('post_payment_success_msg_content'));
if(!$filecontent)
{
	$filecontent = PAYMENT_SUCCESS_MSG;
}
$store_name = get_option('blogname');
$order_id = $_REQUEST['pid'];
/*if(get_post_type($order_id)=='event')
{
	$post_link = home_url().'/?ptype=preview_event&alook=1&pid='.$_REQUEST['pid'];
}else
{
$post_link = home_url().'/?ptype=preview&alook=1&pid='.$_REQUEST['pid'];	
}*/

$post_link =  get_permalink($_REQUEST['pid']);

$search_array = array('[#site_name#]','[#submited_information_link#]');
$replace_array = array($store_name,$post_link);

$filecontent = str_replace($search_array,$replace_array,$filecontent);
echo $filecontent;
?>
</div> <!-- content #end -->
<?php 			###### MAIN CONTENT WRAPPERS CLOSE ######
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
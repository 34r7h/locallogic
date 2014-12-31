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

$title = PAY_CANCELATION_TITLE;?>
<div class="geodir_preview_section" >
<h1><?php echo $title;?></h1>   

<?php 
if(isset($_REQUEST['err_msg']) && $_REQUEST['err_msg']){
echo "<h3>".$_REQUEST['err_msg']."</h3>";
echo "<h3>".__('Your post has been saved, please contact support to arrange for it to be published.', GEODIRPAYMENT_TEXTDOMAIN)."</h3>";
}
$filecontent = stripslashes(get_option('post_payment_cancel_msg_content'));
if(!$filecontent)
{
	$filecontent = PAY_CANCEL_MSG;
}
$store_name = get_option('blogname');
$search_array = array('[#site_name#]');
$replace_array = array($store_name);
$filecontent = str_replace($search_array,$replace_array,$filecontent);
echo $filecontent;
?> 
</div> <!-- content #end -->
<?php 		###### MAIN CONTENT WRAPPERS CLOSE ######
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
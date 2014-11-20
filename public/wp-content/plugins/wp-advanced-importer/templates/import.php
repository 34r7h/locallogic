<?php
require_once(WP_CONST_ADVANCED_XML_IMP_DIRECTORY.'lib/skinnymvc/core/base/SkinnyBaseActions.php');
require_once(WP_CONST_ADVANCED_XML_IMP_DIRECTORY.'lib/skinnymvc/core/SkinnyActions.php');
$skinnyObj = new CallWPAdvImporterObj();
$all_arr = array();
$postcount = $authorcount = $total = $get_file = $new_user = $ex_user = $attach_id = $file_name = $type = $attach = $implimit = $duptitle = $dupcontent =  '';
$get_file    =  $_POST['postdata'][0]['get_file'];
$postcount   =  $_POST['postdata'][0]['postcount'];
$authorcount =  $_POST['postdata'][0]['authorcount'];
$total       =  $_POST['postdata'][0]['total'];
$ex_user     =  $_POST['postdata'][0]['ex_user'];
$authcnt     =  $_POST['postdata'][0]['authcnt'];
$implimit    =  $_POST['postdata'][0]['implimit'];
$attach      =  $_POST['postdata'][0]['attach'];
$duptitle    =  $_POST['postdata'][0]['duptitle'];
$dupcontent  =  $_POST['postdata'][0]['dupcontent'];
$type        =  $_POST['postdata'][0]['type'];
$get_file = trim($get_file);
$attach   = trim($attach);

$all_arr = $skinnyObj->get_xml_details($get_file);
for($j=1; $j<=$authcnt; $j++) {
$user_id  =  $skinnyObj->processAuthor($all_arr , $j , $type , $ex_user);
  foreach($skinnyObj->detailedLog as $logKey => $logVal) {
                                echo " </p>" . $logVal['verify_here'] . "</p>";
                                unset($skinnyObj->detailedLog[$logKey]); 
         }
}
$user_id = $skinnyObj->get_user_id;
$res = $skinnyObj->processDataInWP($all_arr,$implimit,$user_id,$attach,$duptitle,$dupcontent,$type);
foreach($skinnyObj->detailedLog as $logKey => $logVal) {
                           print_r  ("</p>" . $logVal['verify_here'] . "</p>");
                        }
$total = $total - 1;
     if($implimit == $total ) {
	     foreach($_SESSION['post_id'] as $key => $value )  { 
		     $post_id = explode('|',$value);
		     foreach($_SESSION['attach_id'] as $akey => $aval ) {
			     $attach_id = explode('|',$aval); 
			     if($attach_id[1] == $post_id[1]) {
				     global $wpdb;
				     $file_name = $wpdb->get_results("select guid from " . $wpdb->posts . " where ID  = {$attach_id[0]} ");
				     $update = $wpdb->get_results("update $wpdb->posts set post_parent = {$post_id[0]} where ID = {$attach_id[0]}");
				     set_post_thumbnail($post_id[0], $attach_id[0]);
			     }     
		     }
	     }                   
                               if($attach == 'no') {
				     $uploaddir =  $_SESSION['img_path'];
				     $skinnyObj->deletexmlprocesscomplete($uploaddir);
                                  }
				     delete_option('to_import');  
				     delete_option('exclude_keys');
                                     unset($_SESSION['post_id']);
                                     unset($_SESSION['attach_id']); 
     }  
?>

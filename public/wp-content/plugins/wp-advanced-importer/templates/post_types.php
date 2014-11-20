<?php
global $wpdb;
$post = $page = $custom =  $file = '';
$get_details = $get_custom_info = $res =  $eliminate = $default = array() ;
$eliminate = (array)('attachmant');
$default[] = $_POST['postdata'][0]['post'];
$default[]   = $_POST['postdata'][0]['page'];
$custom = $_POST['postdata'][0]['custom'];
if(!empty($custom) && $custom == 'customposts') {
	$file = $_SESSION['xml_values']['uploadfilename'];
	$impCE = new WPAdvImporter_includes_helper();
	$get_details = $impCE->get_xml_details($file);
	$get_custom_info  = $impCE->get_custom_details($get_details);
	if(isset($get_custom_info)) {
		$i =0; 
		foreach($get_custom_info as $get_key => $get_val ) {
			foreach($get_val as $key  => $val) {
				if($key == 'post_type') {
					if((!in_array($val,$eliminate,TRUE)) && (!in_array($val,$res,TRUE))) {
						$res[] = $val;
						$i++;
					}
				}

			}

		}
	}
	$res[] = $_POST['postdata'][0]['post'];
	$res[] = $_POST['postdata'][0]['page'];
	update_option('to_import',$res);

}

print_r(json_encode($res));die; 

?>

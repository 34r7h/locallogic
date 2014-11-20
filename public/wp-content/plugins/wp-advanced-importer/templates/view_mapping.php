<?php
$post_type = $res = '';
global $wpdb;
$post_type = $_POST['postdata'][0]['post_type'];
$res = edit_mapping($post_type); 
function edit_mapping($post_type) {
	global $wpdb;
	$module = '';
	$impCE = new WPAdvImporter_includes_helper();
	$headers = array();
	$module = $post_type;
	$sample = array('guid','is_normal_post','is_page','is_custom_post','post_content_filtered','postmeta','is_sticky','post_date_gmt','ping_status', 'post_name','post_type','product_type');
	$file   = $_SESSION['xml_values']['uploadfilename'];
	$filename = $impCE->convert_string2hash_key($file);
	$extension = 'xml';
	$headers = $impCE->xml_file_data($filename,$extension,$module);
	$html = '';
	$html .= '<h4 class="textalign">' . $module. ' mapping </h4>';
	$html .= '<a href="#" id = "checkall" onclick = "check_mapping(this.id);"> Check All </a>/';
	$html .= '<a href="#" id = "uncheckall" onclick = "uncheck_mapping(this.id);">Uncheck All </a>';

	$html .= '<form action ="" id = "mapping_fields"  />';
	$html .= '<input type = "hidden" name = "save_type"  id = "save_type" value ='.$module.'>';
	$html .= '<table style="width:80%;margin:15px 0px 25px 52px;"> <tr style="height:38px"><td class="left_align textalign" style="width:161px">  Check/Uncheck  </td><td class="textalign" style="width:161px;"> XML FIELDS </td><td class="textalign"> WP FIELDS  </td></tr>';   $headers['post_tag'] = 'post_tag';
	$headers['post_category'] = 'post_category';
              $i = 0;
	foreach($headers as $hkey => $hval) {
 
		if(!in_array($hval,$sample,TRUE )) {
			$html .= '<tr style="height:38px;"><td style="width:50px"> <input type = "checkbox" name = "map_arr[]" id ="'.$hval.'" value = "'.$hval.'" checked   /> </td>
				<td> '. $hval. '  </td>';   
			$html .= '<td> <select style="width:155px;" id = "mapping'.$i.'">';
			foreach($impCE->defCols as $wp_key => $wp_val ) {
				$html .= '<option value = '.$wp_key; 
                                               if($hval == $wp_key) { 
                                $html .= ' selected = "selected" > '.$wp_key.' </option>';
                                  }
			}
		$i++;
			$html .= '</td></tr>';
		} }
	$html .= '<tr><td></td><td></td><td><input type = "button" value = "save mapping" class = "btn btn-primary"  onclick= "save_mapping();" style="float:right;"></td></tr>';
	$html .= '</table>';
	$html .= '</form>';

	return $html;

}

print_r(json_encode($res)); die;


?>

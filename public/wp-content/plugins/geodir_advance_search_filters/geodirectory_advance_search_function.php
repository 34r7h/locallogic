<?php

function geodir_advance_search_filters_activation(){

	if (get_option('geodir_installed')) {  
		
		geodir_advance_search_field();
		add_option('geodir_advance_search_activation_redirect_opt', 1);
		
	}

}


function geodir_advance_search_activation_redirect(){
	if (get_option('geodir_advance_search_activation_redirect_opt', false))
	{
	
		delete_option('geodir_advance_search_activation_redirect_opt');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=advance_search&listing_type=gd_place')); 
		
	}
}


function geodir_advace_search_manager_tabs($tabs){

$geodir_post_types = get_option( 'geodir_post_types' );

	foreach($geodir_post_types as $geodir_post_type => $geodir_posttype_info){
		
		$originalKey = $geodir_post_type.'_fields_settings';
		
		if(array_key_exists($originalKey, $tabs)){
			
			if(array_key_exists('subtabs', $tabs[$originalKey])){
				
				$insertValue = array('subtab' => 'advance_search',
												'label' =>__( 'Advance Search', GEODIRADVANCESEARCH_TEXTDOMAIN),
												'request' => array('listing_type'=>$geodir_post_type)
											);
				
				$new_array = array();							
				foreach($tabs[$originalKey]['subtabs'] as $key => $val){
					
					$new_array[] = $val;
					
					if($val['subtab'] == 'custom_fields')
						$new_array[] = $insertValue;
					
				}
				
				$tabs[$originalKey]['subtabs'] = $new_array;
				
			}
			
		}
		
	}
	
	return $tabs;
	
}


function geodir_manage_advace_search_available_fields($sub_tab){
	
	switch($sub_tab)
	{
		case 'advance_search':
			geodir_advance_search_available_fields();
		break;
	}
}


function geodir_manage_advace_search_selected_fields($sub_tab){
	
	switch($sub_tab)
	{
		case 'advance_search':
			geodir_advace_search_selected_fields();
		break;
	}
}


function geodir_advance_admin_custom_fields($field_info){
	
	?>
	<tr>
		<td><?php _e('Include this field in filter',GEODIRADVANCESEARCH_TEXTDOMAIN);?></td>
		<td>:
			<input type="checkbox"  name="cat_filter[]" id="cat_filter"  value="1" <?php if(isset($field_info->cat_filter[0])=='1'){ echo 'checked="checked"';}?>/>
			<span><?php _e('Select if you want to show option in filter.',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
		</td>
	</tr>
	<?php
}


function geodir_get_cat_sort_fields($sort_fields){
	global $wpdb;
	
	$post_type = geodir_get_current_posttype();
	
	
	$custom_sort_fields = array();
	
	if($custom_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE cat_sort <> '' AND field_type NOT IN ('html','multiselect','file','textarea') AND post_type = %s ORDER BY sort_order",array($post_type))))
	{
		foreach($custom_fields as $custom_field){
			switch($custom_field->field_type):
				case 'address':
				$custom_sort_fields[$custom_field->htmlvar_name.'_address'] = __($custom_field->site_title);
				break;
				default:
				$custom_sort_fields[$custom_field->htmlvar_name] = __($custom_field->site_title);	
				break;
			endswitch;	
		}
	}	
	
	return array_merge($sort_fields,$custom_sort_fields);
		
}


function geodir_advance_search_filter() { 
	global $wp_query;
		
	if( ( is_search() && isset( $wp_query->query_vars['is_geodir_loop'] ) && $wp_query->query_vars['is_geodir_loop'] && isset( $_REQUEST['geodir_search'] ) && $_REQUEST['geodir_search'] ) ) {  
		add_filter( 'posts_where', 'geodir_advance_search_where' );
	}
}


function geodirectory_advance_search_fields($listing_type){
	
	$fields = array();
	$fields[]= array('field_type'=>'text','site_title'=>'Search By Distance','htmlvar_name'=>'dist','data_type'=>'FLOAT');
	return apply_filters('geodir_show_filters',$fields,$listing_type); 
}


function geodirectory_advance_search_custom_fields($fields,$listing_type){

	global $wpdb;
	$records =	$wpdb->get_results( $wpdb->prepare("select id,field_type,data_type,site_title,htmlvar_name from ".GEODIR_CUSTOM_FIELDS_TABLE." where post_type = %s and cat_filter=%s order by sort_order asc",array($listing_type, '1')));
	
	foreach($records as $row){ 
		$field_type = $row->field_type;
		if($row->field_type =='taxonomy'){$field_type ='taxonomy';}
		$fields[]= array('field_type'=>$field_type,'site_title'=>$row->site_title,'htmlvar_name'=>$row->htmlvar_name,'data_type'=>$row->data_type);
	}
	return $fields;	 
}

function geodir_is_geodir_search( $where ) {
	global $wpdb;
	
	$return = true;
	
	if( $where != '' ) {
		$match_where = strtolower( "and" . $wpdb->posts . ".post_type='post'" );
		$check_where = strtolower( $where );
		$check_where = preg_replace( '/\s/', '', $check_where );
		
		if( strpos( $check_where, $match_where ) !== false ) {
			$return = false;
		}
	}
	
	return $return;
}

function geodir_advance_search_where( $where ) {  
	global $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $mylat, $mylon, $s, $snear, $s, $s_A, $s_SA, $search_term;

	if( isset( $_REQUEST['stype'] ) ) {
		$post_types = $_REQUEST['stype'];
	} else {
		$post_types = 'gd_place';
	}
	
	/* check for post type other then geodir post types */
	if( !geodir_is_geodir_search( $where ) ) {
		return $where;
	}
	
	/* Add categories filters */
	$category_filter = false;	
	$category_search_query = '';
	$geodir_custom_search = '';
	$category_search_range = ''; 

	$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type = %s ORDER BY sort_order", array( $post_types ) );
	$taxonomies = $wpdb->get_results( $sql );

	if( !empty( $taxonomies ) ) {
		foreach( $taxonomies as $taxonomy_obj ) {
			switch( $taxonomy_obj->field_input_type ) {
				case 'RANGE':
					// SEARCHING BY RANGE FILTER 
					switch( $taxonomy_obj->search_condition ) {
						case 'SINGLE':
							$value = $_REQUEST['s' . $taxonomy_obj->site_htmlvar_name];
							
							if( !empty( $value ) ) {
								$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." = $value) "; 
							}
						break;
						
						case 'FROM':
							$minvalue = @$_REQUEST['smin'.$taxonomy_obj->site_htmlvar_name]; 
							$smaxvalue = @$_REQUEST['smax'.$taxonomy_obj->site_htmlvar_name];
							
							if( !empty( $minvalue ) ) { 
								$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." >= '".$minvalue."') "; 
							}
								
							if( !empty( $smaxvalue ) ) {
								$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." <= '".$smaxvalue."') ";
							}			
						break;
						
						case 'RADIO':
							// This code in main geodirectory listing filter 
						break;
						
						default :
							if( isset( $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] ) && $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] != '' ) {
								$serchlist =  explode( "-", $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] );
								$first_value  = @$serchlist[0];//100 200
								$second_value = @trim( $serchlist[1], ' ' );
								$rest = substr( $second_value, 0, 4 ); 
								 
								if( $rest == 'Less' ) {
									$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." <= $first_value ) "; 
									
								} else if ( $rest == 'More' ) {
									$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." >= $first_value) ";
									
								} else if( $second_value != '' ) {
									$category_search_range  .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." between $first_value and $second_value ) ";
								}
							}
						break;
					}
					// END SEARCHING BY RANGE FILTER  
				break;
				
				case 'DATE' :
					$single = '';
					$value = @$_REQUEST['s'.$taxonomy_obj->site_htmlvar_name];
					if(	isset( $value ) &&!empty( $value ) ) {
						$minvalue = $value;
						$maxvalue = '';
						$single = '1';
					} else {
						$minvalue = @$_REQUEST['smin'.$taxonomy_obj->site_htmlvar_name]; 
						$maxvalue = @$_REQUEST['smax'.$taxonomy_obj->site_htmlvar_name];
					}
				
					if( $taxonomy_obj->site_htmlvar_name == 'event' ) {
						$category_search_range .= " ";
					} else if( $taxonomy_obj->field_data_type == 'DATE' ) {
						$start_date = date( 'Y-m-d', strtotime( $minvalue ) );
						$start_end = date( 'Y-m-d', strtotime( $maxvalue ) );
						
						$minvalue = $wpdb->get_var( "SELECT UNIX_TIMESTAMP( STR_TO_DATE( '".$start_date."','%Y-%m-%d'))" );
						$maxvalue = $wpdb->get_var( "SELECT UNIX_TIMESTAMP( STR_TO_DATE( '".$start_end."','%Y-%m-%d'))" );
							
						if( $single == '1' ) {
							$category_search_range .= " AND ( unix_timestamp(".$table.'.'.$taxonomy_obj->site_htmlvar_name.") = '".$minvalue."' )";
						} else {
							if( !empty( $minvalue ) ) {
								$category_search_range .= " AND ( unix_timestamp(".$table.'.'.$taxonomy_obj->site_htmlvar_name.") >= '".$minvalue."' )";
							}
							if( !empty( $maxvalue ) ) {
								$category_search_range .= " AND ( unix_timestamp(".$table.'.'.$taxonomy_obj->site_htmlvar_name.") <= '".$maxvalue."' )";
							}
						}		
					} else if( $taxonomy_obj->field_data_type == 'TIME' ) {
						if( $single == '1' ) {
							 $category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." = '".$minvalue.":00' )";  
						} else {
							if( !empty( $minvalue ) ) {
								$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." >= '".$minvalue.":00' )"; 
							}
							if( !empty( $maxvalue ) ) {
								$category_search_range .= " AND ( ".$table.'.'.$taxonomy_obj->site_htmlvar_name." <= '".$maxvalue.":00' )";
							}
						}
					}
				break;
				default:
					$category_search = ''; 
					if( isset( $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] ) && is_array( $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] ) ) {
						$i = 0;
						$add_operator = ''; 
						foreach( $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] as $val ) {
							if( $val != '' ) {
								if( $i != 0 ) {
									$add_operator = $search_term;
								}
								
								$category_search .= $add_operator." FIND_IN_SET('{$val}', ".$table.".".$taxonomy_obj->site_htmlvar_name." ) ";
								$i++; 
							} 
						}
						
						if( !empty( $category_search ) ) {
							$geodir_custom_search .= " AND (".$category_search.")";
						}
					} else if( isset($_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] ) ) {
						$site_htmlvar_name = $taxonomy_obj->site_htmlvar_name;
							
						if( $site_htmlvar_name == 'post' ) {
							$site_htmlvar_name = $site_htmlvar_name.'_address';
						}
							
						if( $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name] ) {
							$geodir_custom_search .= " AND ".$table.".".$site_htmlvar_name." LIKE '%".$_REQUEST['s'.$taxonomy_obj->site_htmlvar_name]."%' "; 
						}
					}
				break;
			}
		} 
	}
	if( !empty( $geodir_custom_search ) ) {
		$where .= $geodir_custom_search;
	}
	if( !empty( $category_search_range ) ) {
		$where .= $category_search_range;
	}
	
	$where =  apply_filters( 'advance_search_where_query', $where );
	
	return $where;
} 


function geodir_advance_search_available_fields(){

	global $wpdb;
	$listing_type	= ($_REQUEST['listing_type'] != '') ? $_REQUEST['listing_type'] : 'gd_place';
	
	$allready_add_fields =	$wpdb->get_results("select site_htmlvar_name from ".GEODIR_ADVANCE_SEARCH_TABLE."     where post_type ='".$listing_type."'");

	$allready_add_fields_ids = array();				
	if(!empty($allready_add_fields))
	{
		foreach($allready_add_fields as $allready_add_field)
		{
			$allready_add_fields_ids[] = $allready_add_field->site_htmlvar_name;
		}
	}	
	?>
	<input type="hidden" name="listing_type" id="new_post_type" value="<?php echo $listing_type;?>"  />
	<input type="hidden" name="manage_field_type" class="manage_field_type" value="<?php echo $_REQUEST['subtab']; ?>" />
	<ul><?php 
			
		$fields = geodirectory_advance_search_fields($listing_type);
		
		if(!empty($fields))
		{
			foreach($fields as $field)
			{ 
				$display = '';
				if(in_array($field['htmlvar_name'],$allready_add_fields_ids))
					$display = 'style="display:none;"';
			?> 
				 <li <?php echo $display;?> ><a id="gt-<?php echo $field['htmlvar_name'];?>" class="gt-draggable-form-items gt-<?php echo $field['field_type'];?>" href="javascript:void(0);"><b></b><?php echo $field['site_title'];?></a></li> 
			<?php 
			}
		}
		?>
		
	</ul>
		
	<?php						
}


function geodir_advace_search_selected_fields(){
	
	global $wpdb;
	$listing_type	= ($_REQUEST['listing_type'] != '') ? $_REQUEST['listing_type'] : 'gd_place';
	
	?>
	<input type="hidden" name="manage_field_type" class="manage_field_type" value="<?php echo $_REQUEST['subtab']; ?>" />
	<ul class="advance"><?php 
							
		$fields =	$wpdb->get_results(
								$wpdb->prepare(
									"select * from  ".GEODIR_ADVANCE_SEARCH_TABLE." where post_type = %s order by sort_order asc",
									array($listing_type)
								)
							);
		
		if(!empty($fields))
		{
			foreach($fields as $field)
			{
				//$result_str = $field->id;
				$result_str =$field;
				$field_type = $field->field_site_type;
				$field_ins_upd = 'display';
				
				 $default = false;
				
				geodir_custom_advance_search_field_adminhtml($field_type, $result_str, $field_ins_upd, $default);
			}
		}?>
		
		</ul>
	<?php
}


function geodir_custom_advance_search_field_adminhtml($field_type , $result_str, $field_ins_upd = '', $default = false)
{
	
	global $wpdb;
	
	$cf = $result_str;
	if(!is_object($cf))
	{
		
		$field_info =	$wpdb->get_row($wpdb->prepare("select * from ".GEODIR_ADVANCE_SEARCH_TABLE." where id= %d",array($cf)));
		
	}
	else
	{
		$field_info = $cf;
		$result_str = $cf->id;
	}

	include('advance_search_admin/custom_advance_search_field_html.php'); 
}


if (!function_exists('geodir_custom_advance_search_field_save')) {
function geodir_custom_advance_search_field_save( $request_field = array() , $default = false ){
	
	global $wpdb, $plugin_prefix;
	
	$old_html_variable = '';
	
	$data_type = trim($request_field['data_type']);
	
	$result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';
	
	$cf = trim($result_str, '_');
	
	/*-------- check dublicate validation --------*/
	
	$site_htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
	$post_type = $request_field['listing_type'];
	
	$check_html_variable  = 	$wpdb->get_var($wpdb->prepare("select site_htmlvar_name from ".GEODIR_ADVANCE_SEARCH_TABLE." where id <> %d and site_htmlvar_name = %s and post_type = %s ",
array($cf, $site_htmlvar_name, $post_type)));
	
	
	
	if(!$check_html_variable){
		
		if($cf != ''){
			
			$post_meta_info =	$wpdb->get_row(
													$wpdb->prepare(
														"select * from ".GEODIR_ADVANCE_SEARCH_TABLE." where id = %d",
														array($cf)
													)
												);
			
		}
		
		if($post_type == '') $post_type = 'gd_place';
		
		
		$detail_table = $plugin_prefix . $post_type . '_detail' ;
		
		$field_title = $request_field['field_title'];
		$field_type = $request_field['field_type'];
		$field_site_type = $request_field['field_type'];
		$site_field_title = $request_field['site_field_title'];
		$site_htmlvar_name = $request_field['site_htmlvar_name'];
		$data_type = $request_field['data_type'];
		$field_desc = $request_field['field_desc'];
		$field_data_type = $request_field['field_data_type'];
		$field_id = str_replace('new','',$request_field['field_id']);
		
		$expand_custom_value = $request_field['expand_custom_value'];
		
		
		$searching_range_mode = isset($request_field['searching_range_mode']) ? $request_field['searching_range_mode'] : '';
		$expand_search = isset($request_field['expand_search']) ? $request_field['expand_search'] : '';
		
		$front_search_title = isset($request_field['front_search_title']) ? $request_field['front_search_title'] : '';
		
		$first_search_value = isset($request_field['first_search_value']) ? $request_field['first_search_value'] : '';
		
		$first_search_text = isset($request_field['first_search_text']) ? $request_field['first_search_text'] : '';
		$last_search_text = isset($request_field['last_search_text']) ? $request_field['last_search_text'] : '';
		$search_condition = isset($request_field['search_condition']) ? $request_field['search_condition'] : '';
		$search_min_value = isset($request_field['search_min_value']) ? $request_field['search_min_value'] : '';
		$search_max_value = isset($request_field['search_max_value']) ? $request_field['search_max_value'] : '';
		$search_diff_value = isset($request_field['search_diff_value']) ? $request_field['search_diff_value'] : '';
		
	
		$extra_fields = '';
		if(isset($request_field['search_asc_title'])){
			$arrays_sorting = array();
			$arrays_sorting['is_sort'] = isset($request_field['geodir_distance_sorting']) ? $request_field['geodir_distance_sorting'] : '';
			$arrays_sorting['asc'] = isset($request_field['search_asc']) ? $request_field['search_asc'] : '';
			$arrays_sorting['asc_title'] = isset($request_field['search_asc_title']) ? $request_field['search_asc_title'] : '';
			$arrays_sorting['desc'] = isset($request_field['search_desc']) ? $request_field['search_desc'] : '';
			$arrays_sorting['desc_title'] = isset($request_field['search_desc_title']) ? $request_field['search_desc_title'] : '';
			
			$extra_fields = serialize($arrays_sorting);
		}
		
		if($search_diff_value!=1){$searching_range_mode =0;}
		if($site_htmlvar_name=='dist'){$data_type = 'RANGE'; $search_condition='RADIO';}
		
		$data_type_change = isset($request_field['data_type_change']) ? $request_field['data_type_change'] : ''; 
		
		if($data_type_change == 'SELECT')
			$data_type = 'RANGE';
			
		if(!empty($post_meta_info))
		{
			
			$extra_field_query = '';
			if(!empty($extra_fields)){ $extra_field_query = serialize( $extra_fields ) ;  }
			$wpdb->query(
				$wpdb->prepare(
				"update ".GEODIR_ADVANCE_SEARCH_TABLE." set 
					post_type = %s,
					field_site_name = %s,
					field_site_type = %s,
					site_htmlvar_name = %s,
					field_input_type = %s,
					field_data_type = %s,
					sort_order = %s,
					field_desc = %s,
					expand_custom_value=%d,
					searching_range_mode=%d,
					expand_search=%d,
					front_search_title=%s,
					first_search_value=%d,
					first_search_text=%s,
					last_search_text=%s,
					search_condition = %s,
					search_min_value = %d,
					search_max_value = %d,
					search_diff_value = %d,
					extra_fields = %s
					where id = %d",
					array($post_type,$site_field_title,$field_site_type,$site_htmlvar_name,$data_type,$field_data_type,$field_id,$field_desc,$expand_custom_value,$searching_range_mode,$expand_search,$front_search_title,$first_search_value,$first_search_text,$last_search_text,$search_condition,$search_min_value,$search_max_value,$search_diff_value,$extra_fields,$cf)
					
				)
				
			);
			
			$lastid = trim($cf);
			
			
		}else{
		
			$extra_field_query = '';
			if(!empty($extra_fields)){ $extra_field_query = serialize($extra_fields);  }
						
			
			$wpdb->query(
			$wpdb->prepare( 
								
					"insert into ".GEODIR_ADVANCE_SEARCH_TABLE." set 
					post_type = %s,
					field_site_name = %s,
					field_site_type = %s,
					site_htmlvar_name = %s,
					field_input_type = %s,
					field_data_type = %s,
					sort_order = %s,
					field_desc = %s,
					expand_custom_value=%d,
					searching_range_mode=%d,
					expand_search=%d,
					front_search_title=%s,
					first_search_value=%d,
					first_search_text=%s,
					last_search_text=%s,
					search_condition = %s,
					search_min_value = %d,
					search_max_value = %d,
					search_diff_value = %d,
					extra_fields = %s
					 ",
					array($post_type,$site_field_title,$field_site_type,$site_htmlvar_name,$data_type,$field_data_type,$field_id,$field_desc,$expand_custom_value,$searching_range_mode,
					$expand_search,$front_search_title,$first_search_value,$first_search_text,$last_search_text,$search_condition,$search_min_value,$search_max_value,$search_diff_value,$extra_fields)
				)
			);
			$lastid = $wpdb->insert_id; 
			$lastid = trim($lastid); 
		}
		
		return (int)$lastid;
		
	
	}else{
		return 'HTML Variable Name should be a unique name';
	}

}
}


function godir_set_advance_search_field_order($field_ids = array()){
	
	global $wpdb;	
	
	$count = 0;
	if( !empty( $field_ids ) ):
		foreach ($field_ids as $id) {
		
			$cf = trim($id, '_');
		
		$post_meta_info = $wpdb->query(
														$wpdb->prepare( 
															"update ".GEODIR_ADVANCE_SEARCH_TABLE." set 
															sort_order=%d 
															where id= %d",
															array($count, $cf)
														)
												);
			$count ++;	
		}
		
		return $field_ids;
	else:
		return false;
	endif;
}


if (!function_exists('geodir_custom_advance_search_field_delete')) {
function geodir_custom_advance_search_field_delete( $field_id = '' ){
	
	global $wpdb, $plugin_prefix;
	if($field_id != ''){
		$cf = trim($field_id, '_');
		
			$wpdb->query($wpdb->prepare("delete from ".GEODIR_ADVANCE_SEARCH_TABLE." where id= %d ",array($cf)));
			
			return $field_id;
			
		}else
			return 0;	
		
			
}
}

//---------advance search ajex-----
function geodir_advance_search_ajax_handler(){
	if(isset($_REQUEST['create_field'])){
		$plugins_path =  WP_PLUGIN_DIR.'/geodir_advance_search_filters/'; 
		include_once($plugins_path.'/advance_search_admin/create_advance_search_field.php'); die; 
	}
}

//-----------create advance search field table----------
function geodir_advance_search_field(){
	global $plugin_prefix, $wpdb;
	
	$collate = '';
	if($wpdb->has_cap( 'collation' )) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}
	$advance_search_table = "CREATE TABLE IF NOT EXISTS ".GEODIR_ADVANCE_SEARCH_TABLE." (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `post_type` varchar(255) NOT NULL,
									  `field_site_name` varchar(255) NOT NULL,
									  `field_site_type` varchar(255) NOT NULL,
									  `site_htmlvar_name` varchar(255) NOT NULL,
									  `expand_custom_value` int(11) NOT NULL,
									  `searching_range_mode` int(11) NOT NULL,
									  `expand_search` int(11) NOT NULL,
									  `front_search_title` varchar(255) CHARACTER SET utf8 NOT NULL,
									  `first_search_value` int(11) NOT NULL,
									  `first_search_text` varchar(255) CHARACTER SET utf8 NOT NULL,
									  `last_search_text` varchar(255) CHARACTER SET utf8 NOT NULL,
									  `search_min_value` int(11) NOT NULL,
									  `search_max_value` int(11) NOT NULL,
									  `search_diff_value` int(11) NOT NULL DEFAULT '0',
									  `search_condition` varchar(100) NOT NULL,
									  `field_input_type` varchar(255) NOT NULL,
									  `field_data_type` varchar(255) NOT NULL,
									  `sort_order` int(11) NOT NULL,
									  `field_desc` varchar(255) NOT NULL,
										`extra_fields` TEXT NOT NULL,
									  PRIMARY KEY (`id`)
									) $collate AUTO_INCREMENT=1 ;";
						
	$wpdb->query($advance_search_table);
}
//-----------------------------------------------------

function geodir_advance_search_filters_uninstall(){
	if ( ! isset($_REQUEST['verify-delete-adon']) ) 
	{
		$plugins = isset( $_REQUEST['checked'] ) ? (array) $_REQUEST['checked'] : array();
			//$_POST = from the plugin form; $_GET = from the FTP details screen.
			
			wp_enqueue_script('jquery');
					require_once(ABSPATH . 'wp-admin/admin-header.php');
					printf( '<h2>%s</h2>' ,__( 'Warning!!' , GEODIRADVANCESEARCH_TEXTDOMAIN) );
					printf( '%s<br/><strong>%s</strong><br /><br />%s <a href="http://wpgeodirectory.com">%s</a>.' , __('You are about to delete a Geodirectory Adon which has important option and custom data associated to it.' ,GEODIRADVANCESEARCH_TEXTDOMAIN) ,__('Deleting this and activating another version, will be treated as a new installation of plugin, so all the data will be lost.', GEODIRADVANCESEARCH_TEXTDOMAIN), __('If you have any problem in upgrading the plugin please contact Geodirectroy', GEODIRADVANCESEARCH_TEXTDOMAIN) , __('support' ,GEODIRADVANCESEARCH_TEXTDOMAIN) ) ;
					
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
						<?php submit_button(  __( 'Delete plugin files only' , GEODIRADVANCESEARCH_TEXTDOMAIN ), 'button', 'submit', false ); ?>
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
						<?php submit_button(  __( 'Delete both plugin files and data' , GEODIRADVANCESEARCH_TEXTDOMAIN) , 'button', 'submit', false ); ?>
					</form>
					
	<?php
		require_once(ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}
	
	
	if ( isset($_REQUEST['verify-delete-adon-data']) ) 
	{
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("DROP TABLE ".GEODIR_ADVANCE_SEARCH_TABLE, array()));
	}
}


function geodir_show_filters_fields( $post_type ){
	global $wpdb;
	if($post_type == '')
		$post_type = 'gd_place';	
		$geodir_list_date_type = 'yy-mm-dd';
		$datepicker_formate = $wpdb->get_var("SELECT `extra_fields`  FROM `geodir_custom_fields` WHERE `post_type` = '".$post_type."' AND data_type ='DATE'");
		$datepicker_formate_arr =  unserialize($datepicker_formate);
		if($datepicker_formate_arr['date_format'])
			$geodir_list_date_type =$datepicker_formate_arr['date_format'];
						
		$geodir_search_field_selected = false ;
		$geodir_search_field_selected_str = '' ; 
		$geodir_search_field_begin = '' ;
		$geodir_search_field_end = '' ;
		$geodir_search_custom_value_str = '' ;
		?>
		<script language="javascript">
            jQuery(function($) {
                $( "#event_start" ).datepicker({
                dateFormat:'<?php echo $geodir_list_date_type ?>',changeMonth: true,	changeYear: true,
                onClose: function( selectedDate ) {
                $( "#event_end" ).datepicker( "option", "minDate", selectedDate );
                }
                });
                $( "#event_end" ).datepicker({changeMonth: true,	changeYear: true,dateFormat:'<?php echo $geodir_list_date_type ?>'});
            });
        </script>
		<?php
		$taxonomies =	$wpdb->get_results(
							$wpdb->prepare("SELECT * FROM ".GEODIR_ADVANCE_SEARCH_TABLE." WHERE post_type = %s  ORDER BY sort_order",array($post_type)));
	ob_start();
	if(!empty($taxonomies)):
	foreach($taxonomies as $taxonomy_obj):
	
			
			
			
	
		if( !stristr($taxonomy_obj->site_htmlvar_name, 'tag') ){ 
			echo '<div class="geodir-filter-cat">'; ?>
					<span><?php if($taxonomy_obj->front_search_title){echo $taxonomy_obj->front_search_title;}else{echo $taxonomy_obj->field_site_name;}  ?> </span>
					<?php 
					$geodir_search_field_begin = '';
					$geodir_search_field_end = '';
						if($taxonomy_obj->field_input_type=='SELECT'){
							$geodir_search_field_begin = '<select name="s'.$taxonomy_obj->site_htmlvar_name.'[]' .'" class="cat_select"> <option value="" >'.__('Select option',GEODIRADVANCESEARCH_TEXTDOMAIN).'</option>';
								//$geodir_search_field_selected_str = ' selected="selected" ';
							$geodir_search_field_end ='</select>';
					}
				
					######### FETCH SEARCH OPTIONS AND DATE TIME SCRIPT #####
					
					switch($taxonomy_obj->field_site_type){
					case 'taxonomy':
					$args = array(	'orderby' => 'count', 'order' => 'DESC','hide_empty'    => true); 
					$args = array(	'orderby' => 'count', 'order' => 'DESC','hide_empty'    => true);
					$terms = apply_filters('geodir_filter_terms',get_terms( $taxonomy_obj->site_htmlvar_name, $args )); 
					
					
					// let's order the child categories below the parent.
					$terms_temp = array();
					
					foreach($terms as $term){
						
						if($term->parent=='0'){
							$terms_temp[] = $term;
							foreach($terms as $temps){
									if($temps->parent!='0' && $temps->parent==$term->term_id){
										$temps->name = '- '.$temps->name;
										$terms_temp[] =$temps;
									}
								
								}
							
						}
						
					}
					$terms=array();
					$terms = $terms_temp;
					
					
					break;
					case 'datepicker':
						?>
							<script type="text/javascript" language="javascript">
                            
                             jQuery(document).ready(function(){
                                
                                jQuery( "#s<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).datepicker({changeMonth: true,	changeYear: true,dateFormat:'<?php echo $geodir_list_date_type;?>'});
                                
                                jQuery( "#smin<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).datepicker({changeMonth: true,	changeYear: true,dateFormat:'<?php echo $geodir_list_date_type;?>',onClose: function( selectedDate ) {
								jQuery( "#smax<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).datepicker( "option", "minDate", selectedDate );
								}
							});
                                
                                jQuery( "#smax<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).datepicker({changeMonth: true,	changeYear: true,dateFormat:'<?php echo $geodir_list_date_type;?>',});
                                
                                });
                            
                       </script>
                   		 <?php
						 $terms =array(1);
					break;
					
					case 'time':
						?>
							<script type="text/javascript" language="javascript">
                       jQuery(document).ready(function(){
			
							jQuery( "#s<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).timepicker({
									showPeriod: true,
									showLeadingZero: true,
									showPeriod: true,
							});
							
							jQuery( "#smin<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).timepicker({
									showPeriod: true,
									showLeadingZero: true,
									showPeriod: true,
									onClose: function( selectedTime ) {
										jQuery( "#smax<?php echo $taxonomy_obj->site_htmlvar_name;?>").timepicker( "option", "minTime", selectedTime );
								}
									
							});
							
							jQuery( "#smax<?php echo $taxonomy_obj->site_htmlvar_name;?>" ).timepicker({
									showPeriod: true,
									showLeadingZero: true,
									showPeriod: true,
									
							});
						});
                   </script>
						<?php
						$terms =array(1);
					break;
					
					case 'select':
					case 'radio':
					case 'multiselect':
						$select_fields_result =	$wpdb->get_row( $wpdb->prepare("SELECT option_values  FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order",array($post_type,$taxonomy_obj->site_htmlvar_name)));
						$terms = explode(',',$select_fields_result->option_values);
					 
					break;
						
					default:
						$terms =array(1);
						break;
				}
				
					######### END  #####
				
					if(!empty($terms)){
					
					$expandbutton ='';
					$expand_custom_value = $taxonomy_obj->expand_custom_value;
					$expand_search = $taxonomy_obj->expand_search;
					$moreoption = '';
					if(!empty($expand_search) && $expand_search>0){
						if($expand_custom_value){
								$moreoption = $expand_custom_value;
						}else{
								$moreoption = 5;
						}
					}
					$ulid ='';
					if($taxonomy_obj->search_condition=="RADIO"){
						$ulid = ' id="sdist"';
						
						if($taxonomy_obj->site_htmlvar_name == 'dist' && $taxonomy_obj->extra_fields != ''){
							
							$extra_fields = unserialize($taxonomy_obj->extra_fields);
							
							$sort_options = '';
							
							if($extra_fields['is_sort'] == '1'){
								
								if($extra_fields['asc'] == '1'){
									
									$name = (!empty($extra_fields['asc_title'])) ? $extra_fields['asc_title'] : 'Nearest';
									$selected = '';
									if(isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'nearest')
										$selected = 'selected="selected"';
									
									$sort_options .= '<option '.$selected.' value="nearest">'.$name.'</option>';
								}
								
								if($extra_fields['desc'] == '1'){
									$name = (!empty($extra_fields['desc_title'])) ? $extra_fields['desc_title'] : 'Farthest';
									$selected = '';
									if(isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'farthest')
										$selected = 'selected="selected"'; 
									
									$sort_options .= '<option '.$selected.' value="farthest">'.$name.'</option>';
								}
								
							}
							
							if($sort_options != ''){
								echo '<ul><select id="" class="cat_select" name="sort_by">';
								echo '<option value="">'.__('Select Option', GEODIRADVANCESEARCH_TEXTDOMAIN).'</option>';
								echo $sort_options;
								echo '</select></ul>';
							}
						}
					}
					
					echo "<ul $ulid>";
					$classname = '';	
					$increment =1;		 
					echo $geodir_search_field_begin ;
					
					foreach($terms as $term) :
					
						if($increment>$moreoption && !empty($moreoption))
								$classname =  'class="more"';
					
						if($taxonomy_obj->field_site_type!='taxonomy'){
							$select_arr =array();
							if(isset($term) && !empty($term))							
								$select_arr = explode('/', $term);
								
							$value = $term;
							$term = (object)$term ;
							$term->term_id = $value;
							$term->name = $value;
							
							if(isset($select_arr[0])&& $select_arr[0]!='' &&  isset($select_arr[1]) && $select_arr[1]!=''){
								$term->term_id = $select_arr[1];
								$term->name    = $select_arr[0];
							
							}
						}
						
						$geodir_search_field_selected = false; 
						$geodir_search_field_selected_str = '' ; 
						$geodir_search_custom_value_str = '';
						if(isset($_REQUEST['s'.$taxonomy_obj->site_htmlvar_name]) && is_array($_REQUEST['s'.$taxonomy_obj->site_htmlvar_name]) && in_array($term->term_id, $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name]) )
							$geodir_search_field_selected = true;
						if(isset($_REQUEST['s'.$taxonomy_obj->site_htmlvar_name]) && $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name]!=''){
						$geodir_search_custom_value_str = $_REQUEST['s'.$taxonomy_obj->site_htmlvar_name];
						}	
						switch($taxonomy_obj->field_input_type)
						{	
							case 'CHECK' :
							
							if($geodir_search_field_selected)
								$geodir_search_field_selected_str  = ' checked="checked" ';	
									echo '<li '.$classname.'><input type="checkbox" class="cat_check" name="s'.$taxonomy_obj->site_htmlvar_name.'[]" '.$geodir_search_field_selected_str.' value="'.$term->term_id.'" />' . $term->name .'</li>';							$increment++;
								break ;
							case 'SELECT' :
							if($geodir_search_field_selected)
								$geodir_search_field_selected_str = ' selected="selected" ';
									echo '<option value="'. $term->term_id .'" '. $geodir_search_field_selected_str.' >'. $term->name.'</option>';
									$increment++;
								break ;
							case 'LINK' :
									echo '<li '.$classname.'><a href="'.home_url().'?geodir_search=1&stype='.$post_type.'&s=+&s'.$taxonomy_obj->site_htmlvar_name.'[]='.$term->term_id.'">'.$term->name .'</a></li>';
									$increment++;
								break;
						case 'RANGE':
						############# RANGE VARIABLES ##########
						
							 {
								$search_starting_value_f = $taxonomy_obj->search_min_value;
								$search_starting_value = $taxonomy_obj->search_min_value;
								$search_maximum_value = $taxonomy_obj->search_max_value;
								$search_diffrence = $taxonomy_obj->search_diff_value;
									
								if(empty($search_starting_value))
									$search_starting_value=10;
								if(empty($search_maximum_value))
									$search_maximum_value=50;
								if(empty($search_diffrence))
									$search_diffrence=10;	
							
								$first_search_text = $taxonomy_obj->first_search_text;
								$last_search_text = $taxonomy_obj->last_search_text;
								$first_search_value = $taxonomy_obj->first_search_value;
								
								$first_search_text = $taxonomy_obj->first_search_text;
								$last_search_text = $taxonomy_obj->last_search_text;
								$first_search_value = $taxonomy_obj->first_search_value;
								
								if(!empty($first_search_value)){
									$search_starting_value = $first_search_value;
								 }else{
									 $search_starting_value = $search_starting_value;
								 }
								if(empty($first_search_text)){
									$first_search_text =' Less Than ';
								}
								if(empty($last_search_text)){
									$last_search_text =' More Than ';
								}
								$j = $search_starting_value_f;
								$k = 0;
								$set_maximum = 0;
								$i=$search_starting_value_f;
								$moreoption ='';
								$expand_custom_value = $taxonomy_obj->expand_custom_value;
								$expand_search = $taxonomy_obj->expand_search;
								if(!empty($expand_search) && $expand_search>0){
									if($expand_custom_value)
										$moreoption = $expand_custom_value;
									else
										$moreoption = 5;
								}
								
								switch($taxonomy_obj->search_condition){
								
											case 'SINGLE':
											$custom_value = @$_REQUEST['s'.$taxonomy_obj->site_htmlvar_name];
											?>
												<input type="text" class="cat_input" name="s<?php echo $taxonomy_obj->site_htmlvar_name;?>"  value="<?php echo $custom_value;?>" /> <?php
											break;
								
											case 'FROM':
											$smincustom_value = @$_REQUEST['smin'.$taxonomy_obj->site_htmlvar_name];
											$smaxcustom_value = @$_REQUEST['smax'.$taxonomy_obj->site_htmlvar_name];
											?>
												<div class='from-to'>
													<input type='text' class='cat_input <?php echo $taxonomy_obj->site_htmlvar_name;?>' placeholder='Start  Search value' name='smin<?php echo $taxonomy_obj->site_htmlvar_name;?>'  value='<?php echo $smincustom_value;?>'>
													<input type='text' class='cat_input <?php echo $taxonomy_obj->site_htmlvar_name;?>' placeholder='End  Search value' name='smax<?php echo $taxonomy_obj->site_htmlvar_name;?>' value='<?php echo $smaxcustom_value;?>'>
												</div><?php 
											break ;
											case 'LINK':
										
												$link_serach_value = @$_REQUEST['s'.$taxonomy_obj->site_htmlvar_name];
												$increment =1;
												while($i<=$search_maximum_value){
												if($k==0)
												{
													$value = $search_starting_value.'-Less';
													?>  <li class=" <?php if($link_serach_value ==$value){echo 'active';} ?><?php if($increment>$moreoption && !empty($moreoption)){echo 'more';} ?>"><a href="<?php echo home_url();?>?geodir_search=1&stype=<?php echo $post_type;?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name;?>=<?php echo $value;?>"><?php echo $first_search_text.' '. $search_starting_value;?></a></li>
													<?php
													$k++;
												}else{	
														if($i<=$search_maximum_value)
														{
															$value = $j.'-'.$i;
																if($search_diffrence==1 && $taxonomy_obj->searching_range_mode==1){
																	$display_value=$j;
																	$value = $j.'-Less';
																}else{
																	$display_value='';	
																}
															?>  <li class=" <?php if($link_serach_value ==$value){echo 'active';} ?><?php if($increment>$moreoption && !empty($moreoption)){echo 'more';} ?>" ><a href="<?php echo home_url();?>?geodir_search=1&stype=<?php echo $post_type;?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name;?>=<?php echo $value;?>"><?php if($display_value){ echo $display_value;}else{ echo $value;}?></a></li> 
														<?php
														}	
														else
														{ 
														
														
															$value= $j.'-'.$i;
															if($search_diffrence==1 && $taxonomy_obj->searching_range_mode==1){
																$display_value=$j;
																$value = $j.'-Less';
																}else{
																$display_value='';	
																}
					
															?>    <li class=" <?php if($link_serach_value ==$value){echo 'active';} ?><?php if($increment>$moreoption && !empty($moreoption)){echo 'more';} ?>"><a href="<?php echo home_url();?>?geodir_search=1&stype=<?php echo $post_type;?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name;?>=<?php echo $value;?>"><?php if($display_value){ echo $display_value;}else{ echo $value;}?></a>
					</li> 
															<?php
														}
														$j = $i;
												}	
												
												$i=$i+$search_diffrence;
												
												if($i>$search_maximum_value)
												{
													if($j!=$search_maximum_value){
														$value = $j.'-'.$search_maximum_value;
														?>   <li class=" <?php if($link_serach_value ==$value){echo 'active';} ?><?php if($increment>$moreoption && !empty($moreoption)){echo 'more';} ?>" ><a href="<?php echo home_url();?>?geodir_search=1&stype=<?php echo $post_type;?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name;?>=<?php echo $value;?>"><?php echo $value;?></a>
					</li><?php }
														if($search_diffrence==1 && $taxonomy_obj->searching_range_mode==1 && $j==$search_maximum_value){
														$display_value=$j;
														$value = $j.'-Less';
														?>    <li class=" <?php if($link_serach_value ==$value){echo 'active';} ?><?php if($increment>$moreoption && !empty($moreoption)){echo 'more';} ?>"><a href="<?php echo home_url();?>?geodir_search=1&stype=<?php echo $post_type;?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name;?>=<?php echo $value;?>"><?php if($display_value){ echo $display_value;}else{ echo $value;}?></a>
														</li> 
														<?php
														}
														
														$value = $search_maximum_value.'-More';
														
														?> 
														  <li class=" <?php if($link_serach_value ==$value){echo 'active';} ?><?php if($increment>$moreoption && !empty($moreoption)){echo 'more';} ?>"><a href="<?php echo home_url();?>?geodir_search=1&stype=<?php echo $post_type;?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name;?>=<?php echo $value;?>"><?php echo $last_search_text.' '.$search_maximum_value;?></a>
									  
														  </li>
														
														<?php 
												}
													
												$increment++;
												
											}
											break;
											case 'SELECT':
												$custom_search_value = @$_REQUEST['s'.$taxonomy_obj->site_htmlvar_name];
												?>
												 <select name="s<?php echo $taxonomy_obj->site_htmlvar_name;?>" class="cat_select" id="">
												<option value="">Select option</option><?php
												if($search_maximum_value > 0){
											while($i<=$search_maximum_value){
												if($k==0)
												{
													$value = $search_starting_value.'-Less';
													?>  <option value="<?php echo $value;?>" <?php if($custom_search_value==$value){ echo 'selected="selected"';}?> ><?php echo $first_search_text.' '.$search_starting_value;?></option>
													<?php
													$k++;
											}	
											else{
													if($i<=$search_maximum_value)
													{
														$value = $j.'-'.$i;
														if($search_diffrence==1 && $taxonomy_obj->searching_range_mode==1){
														$display_value=$j;
														$value = $j.'-Less';
														}else{
														$display_value='';	
														}
														?>  <option value="<?php echo $value;?>" <?php if($custom_search_value==$value){ echo 'selected="selected"';}?> ><?php if($display_value){ echo $display_value;}else{ echo $value;}?></option>
														<?php
													}	
													else
													{ 
														$value= $j.'-'.$i;
														if($search_diffrence==1 && $taxonomy_obj->searching_range_mode==1){
															$display_value=$j;
															$value = $j.'-Less';
														}else{
															$display_value='';	
														}
														?>  <option value="<?php echo $value;?>" <?php if($custom_search_value==$value){ echo 'selected="selected"';}?> ><?php if($display_value){ echo $display_value;}else{ echo $value;}?></option>
														<?php
													}
													$j = $i;
											}	
											$i=$i+$search_diffrence;
											
											if($i>$search_maximum_value)
											{
												if($j!=$search_maximum_value){
													$value = $j.'-'.$search_maximum_value;
													?>  <option value="<?php echo $value;?>" <?php if($custom_search_value==$value){ echo 'selected="selected"';}?> ><?php echo $value;?></option>
													<?php
													}
													if($search_diffrence==1 && $taxonomy_obj->searching_range_mode==1 && $j==$search_maximum_value){
												$display_value=$j;
												$value = $j.'-Less';
												?> <option value="<?php echo $value;?>" <?php if($custom_search_value==$value){ echo 'selected="selected"';}?> ><?php if($display_value){ echo $display_value;}else{ echo $value;}?></option>
												<?php
												}
													$value = $search_maximum_value.'-More';
													
													?>  <option value="<?php echo $value;?>" <?php if($custom_search_value==$value){ echo 'selected="selected"';}?> ><?php echo $last_search_text.' '.$search_maximum_value;?></option>
													<?php 
											}	
											
										}}
											echo '</select>';
											break;
											case 'RADIO':
												
												
												$umo = get_option('geodir_search_dist_1');	
												$dist_dif= $search_diffrence;
												
												for($i = $dist_dif; $i <= $search_maximum_value; $i = $i+$dist_dif) :
												$checked = '';
												if( isset($_REQUEST['sdist']) && $_REQUEST['sdist'] == $i ) 
												{ $checked = 'checked="checked"'; }
													if($increment>$moreoption && !empty($moreoption))
															$classname =  'class="more"';			
																echo '<li '.$classname. '><input type="radio" class="cat_check" name="sdist" '.$checked.' value="'.$i.'" />' . __('Within',GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$i.' '.$umo . '</li>';		
												$increment++;				   
												endfor;
												
												
												
												//echo "<pre>"; print_r($taxonomy_obj);
												
												
												break;
				
					
						}
							}
						#############Range search###############
							break;
							
						case "DATE":
					
							if($taxonomy_obj->search_condition=='SINGLE' && $taxonomy_obj->site_htmlvar_name!='event'){ 
							$custom_value = @$_REQUEST['s'.$taxonomy_obj->site_htmlvar_name];
							?>
							<input  type="text" class="cat_input <?php echo $taxonomy_obj->site_htmlvar_name;?>" name="s<?php echo $taxonomy_obj->site_htmlvar_name;?>" id="s<?php echo $taxonomy_obj->site_htmlvar_name;?>" value="<?php echo $custom_value;?>" />     <?php
							
							}elseif($taxonomy_obj->search_condition=='FROM' && $taxonomy_obj->site_htmlvar_name!='event'){
							$smincustom_value  = @$_REQUEST['smin'.$taxonomy_obj->site_htmlvar_name]; 
							$smaxcustom_value  = @$_REQUEST['smax'.$taxonomy_obj->site_htmlvar_name]; 
							?>
							<div class='from-to'>  
							<input  type='text' class='cat_input' placeholder='Start  Search value' id="smin<?php echo $taxonomy_obj->site_htmlvar_name;?>" name='smin<?php echo $taxonomy_obj->site_htmlvar_name;?>'  value='<?php echo $smincustom_value;?>'>       
							<input  type='text' class='cat_input' placeholder='End  Search value' id="smax<?php echo $taxonomy_obj->site_htmlvar_name;?>" name='smax<?php echo $taxonomy_obj->site_htmlvar_name;?>' value='<?php echo $smaxcustom_value;?>'>        
							</div><?php 
							}elseif($taxonomy_obj->search_condition=='SINGLE' &&$taxonomy_obj->site_htmlvar_name=='event'){
							$smincustom_value = @$_REQUEST[$taxonomy_obj->site_htmlvar_name.'_start']; 
							?>
							<div class='from-to'>         
							<input type="text" value="<?php echo $smincustom_value; ?>" placeholder='' class='cat_input' id="<?php echo $taxonomy_obj->site_htmlvar_name; ?>_start" name="<?php echo $taxonomy_obj->site_htmlvar_name; ?>_start" field_type="text" />  
							</div>  
							<?php
							}elseif($taxonomy_obj->search_condition=='FROM' &&$taxonomy_obj->site_htmlvar_name=='event'){ 
							$smincustom_value = @$_REQUEST[$taxonomy_obj->site_htmlvar_name.'_start'];
						 	$smaxcustom_value = @$_REQUEST[$taxonomy_obj->site_htmlvar_name.'_end'];  
							?>
							
							<div class='from-to'>         
							<input type="text" value="<?php echo $smincustom_value; ?>" placeholder='Start Search  date' class='cat_input' id="<?php echo $taxonomy_obj->site_htmlvar_name; ?>_start" name="<?php echo $taxonomy_obj->site_htmlvar_name; ?>_start" field_type="text" />   
							<input type="text" value="<?php echo $smaxcustom_value; ?>" placeholder='End Search date' class='cat_input' id="<?php echo $taxonomy_obj->site_htmlvar_name; ?>_end" name="<?php echo $taxonomy_obj->site_htmlvar_name; ?>_end" field_type="text" />    
							</div> 
							<?php
							}
							break;
							
						default:
							
						if(isset($taxonomy_obj->field_site_type) && ($taxonomy_obj->field_site_type == 'checkbox')){
							
							$checked = '';
							if($geodir_search_custom_value_str == '1')
								$checked = 'checked="checked"';
								
								echo '<li><input '.$checked.' type="'.$taxonomy_obj->field_site_type.'" class="cat_input" name="s'.$taxonomy_obj->site_htmlvar_name.'"  value="1" />'.__('Yes', GEODIRADVANCESEARCH_TEXTDOMAIN).'</li>';
							
						}else{
							echo '<li><input type="'.$taxonomy_obj->field_input_type.'" class="cat_input" name="s'.$taxonomy_obj->site_htmlvar_name.'"  value="'.$geodir_search_custom_value_str.'" /></li>';	
						}
						}
						
					endforeach;		
					echo $geodir_search_field_end ;
					
					 if(($increment-1) >$moreoption && !empty($moreoption)){ 
						echo '<li class="bordernone"><span class="expandmore">More</span></li>';}
					echo '</ul>';	
					
					if(!empty($taxonomy_obj->field_desc))
								echo "<ul><li>{$taxonomy_obj->field_desc}</li></ul>";
				}	
				
		  echo  '</div>';
		}  
	endforeach;
	endif;
	echo $html = ob_get_clean();
}


function geodir_advance_search_button(){
	global $wpdb; 
	
	$stype = geodir_get_current_posttype();	
	if(empty($stype))
		$stype ='gd_place';
		
	$rows = $wpdb->get_var("SELECT count(id) as rows FROM ".GEODIR_ADVANCE_SEARCH_TABLE." where post_type= '".$stype."'");
	if($rows>0){
			echo '<input type="button" value="'.__('Customize My Search',GEODIRADVANCESEARCH_TEXTDOMAIN).'"  class="showFilters" onclick="gdShowFilters(this);">';

	add_filter('body_class', 'geodir_advance_search_body_class'); // let's add a class to the body so we can style the new addition to the search
	}

} 

function geodir_advance_search_body_class($classes) {
	global $wpdb; 
	
	$stype = geodir_get_current_posttype();	
	if(empty($stype))
		$stype ='gd_place';
		
	$rows = $wpdb->get_var("SELECT count(id) as rows FROM ".GEODIR_ADVANCE_SEARCH_TABLE." where post_type= '".$stype."'");
	if($rows>0){
    $classes[] = 'geodir_advance_search';
	}
    return $classes;
}
add_filter('body_class', 'geodir_advance_search_body_class'); // let's add a class to the body so we can style the new addition to the search


function geodir_advance_search_form(){?>
<script type="text/javascript">
	jQuery(document).ready(function(){
			jQuery('.expandmore').click(function(){
					var moretext = jQuery.trim(jQuery(this).text());
					
					jQuery(this).closest('ul').find('.more').toggle('slow')
					
					if(moretext=='More')
						jQuery(this).text('Less');
					else
						jQuery(this).text('More');
			});
	});
	

if (typeof window.gdShowFilters === 'undefined') {
    window.gdShowFilters = function(fbutton) {
		var $form = jQuery(fbutton).closest('form');
		jQuery(".customize_filter",$form).slideToggle("slow",function(){
			if(jQuery('.geodir_submit_search:first',$form).css('visibility') == 'visible')													
				jQuery('.geodir_submit_search:first',$form).css({'visibility':'hidden'});
			else
				jQuery('.geodir_submit_search:first',$form).css({'visibility':'visible'});	
		});
	
    }
}


	
	/* Show Hide Filters Start
jQuery(document).ready(function(){
	
	jQuery(".showFilters").click(function () { //alert(1);
		var $form = jQuery(this).closest('form');
		jQuery(".customize_filter",$form).slideToggle("slow",function(){
			if(jQuery('.geodir_submit_search:first',$form).css('visibility') == 'visible')													
				jQuery('.geodir_submit_search:first',$form).css({'visibility':'hidden'});
			else
				jQuery('.geodir_submit_search:first',$form).css({'visibility':'visible'});	
		});
	});
	
});*/

</script>  
<style type="text/css">    
	li.more  { display:none;}
	span.expandmore { cursor:pointer;}
	.bordernone { border:none!important;}
</style>  
<?php
	global $current_term;
	if(isset($_REQUEST['stype']))
		$stype = $_REQUEST['stype'];	
	else
		$stype = geodir_get_current_posttype();	
	
	if( !empty($current_term) )
		$_REQUEST['scat'][] = $current_term->term_id;
		
	
	if(get_option('geodir_search_dist')!=''){$dist = get_option('geodir_search_dist');}else{$dist = 500;}
	
	$dist_dif = 1000;
	
	if($dist <= 5000) $dist_dif = 1000;
	if($dist <= 1000) $dist_dif = 200;
	if($dist <= 500) $dist_dif = 100;
	if($dist <= 100) $dist_dif = 20;
	if($dist <= 50) $dist_dif = 10;
	?>
	<div class="geodir-filter-container">         
		<div class="customize_filter customize_filter-in clearfix" style="display:none;">        
			<div id="customize_filter_inner">                                     
				<div class="clearfix">           
					<?php do_action('geodir_search_fields_before',$stype);?>
					<?php do_action('geodir_search_fields',$stype);?>
                    <?php do_action('geodir_search_fields_after',$stype);?>
				</div>       
			</div>      
			<div class="geodir-advance-search">       
			<input type="button" value="<?php _e('Search',GEODIRADVANCESEARCH_TEXTDOMAIN);?>" class="geodir_submit_search" />       
			</div>    
		</div>                
	</div>     
	<?php
}


function geodir_advance_search_after_post_type_deleted($post_type = ''){
	
	global $wpdb;
	if($post_type != ''){
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_ADVANCE_SEARCH_TABLE." WHERE post_type=%s", array($post_type)));
		
	}
}


function geodir_advance_search_after_custom_field_deleted($id, $site_htmlvar_name, $post_type){
	
	global $wpdb;
	
	if($site_htmlvar_name!= '' && $post_type != ''){
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_ADVANCE_SEARCH_TABLE." WHERE site_htmlvar_name=%s AND  post_type=%s", array($site_htmlvar_name, $post_type)));
		
	}
}
?>
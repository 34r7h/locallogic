<?php

function geodir_advance_search_filters_activation(){

	if (get_option('geodir_installed')) {  
	
		geodir_update_options( geodir_autocompleter_options(), true );
		
		update_option('geodir_autocompleter_matches_label', 's');
		
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
	
	
	// rename tables if we need to
	if($wpdb->query("SHOW TABLES LIKE 'geodir_custom_advance_search_fields'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_custom_advance_search_fields'")==0){$wpdb->query("RENAME TABLE geodir_custom_advance_search_fields TO ".$wpdb->prefix."geodir_custom_advance_search_fields");}
	
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
		
		$default_options = geodir_autocompleter_options();
	
		if(!empty($default_options)){
			foreach($default_options as $value){
				if(isset($value['id']) && $value['id'] != '')
					delete_option($value['id'], '');
			}
		}
		
		delete_option('geodir_autocompleter_matches_label', '');
	}
}


function geodir_show_filters_fields( $post_type ){
	global $wpdb;
	if($post_type == '')
		$post_type = 'gd_place';	
		$geodir_list_date_type = 'yy-mm-dd';
		$datepicker_formate = $wpdb->get_var("SELECT `extra_fields`  FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE `post_type` = '".$post_type."' AND data_type ='DATE'");
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
                var gd_datepicker_loaded = $('body').hasClass('gd-multi-datepicker') ? true : false;
				console.log('is_multiple : ' + gd_datepicker_loaded);
				var gdcnt = 0;
				$('.geodir-listing-search #event_start').each(function(){
					gdcnt++;
					$(this).attr('id', 'event_start'+gdcnt);
					$(this).addClass('gd-datepicker-event-start');
				});
				
				var gdcnt = 0;
				$('.geodir-listing-search #event_end').each(function(){
					gdcnt++;
					$(this).attr('id', 'event_end'+gdcnt);
					$(this).addClass('gd-datepicker-event-end');
				});
				
				if(!gd_datepicker_loaded){
					$('body').addClass('gd-multi-datepicker');
					
					$('.gd-datepicker-event-start').each(function(){
						var $this = this;
						$($this).datepicker({
							dateFormat:'<?php echo $geodir_list_date_type ?>',changeMonth: true, changeYear: true,
							onClose: function( selectedDate ) {
								$($this).closest('div').find('.gd-datepicker-event-end').datepicker( "option", "minDate", selectedDate );
							}
						});
					});
					
					$('.gd-datepicker-event-end').each(function(){
						$(this).datepicker({changeMonth: true, changeYear: true,dateFormat:'<?php echo $geodir_list_date_type ?>'});
					});
				}
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
												
												
												$uom = get_option('geodir_search_dist_1');	
												$dist_dif= $search_diffrence;
												
												for($i = $dist_dif; $i <= $search_maximum_value; $i = $i+$dist_dif) :
												$checked = '';
												if( isset($_REQUEST['sdist']) && $_REQUEST['sdist'] == $i ) 
												{ $checked = 'checked="checked"'; }
													if($increment>$moreoption && !empty($moreoption))
															$classname =  'class="more"';			
																echo '<li '.$classname. '><input type="radio" class="cat_check" name="sdist" '.$checked.' value="'.$i.'" />' . __('Within',GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$i.' '.__($uom, GEODIRECTORY_TEXTDOMAIN). '</li>';		
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
if(isset($_POST['action']) && $_POST['action'] && isset($_POST['stype']) && $_POST['stype']){$stype=$_POST['stype'];$ajax=true;}else{$stype=false;$ajax=false;}
	if($stype){}
	else{$stype = geodir_get_current_posttype();}	
	if(empty($stype))
		$stype ='gd_place';
		
	$rows = $wpdb->get_var("SELECT count(id) as rows FROM ".GEODIR_ADVANCE_SEARCH_TABLE." where post_type= '".$stype."'");
	if($rows>0){
			echo '<input type="button" value="'.__('Customize My Search',GEODIRADVANCESEARCH_TEXTDOMAIN).'"  class="showFilters" onclick="gdShowFilters(this);">';
			//echo '<input type="button" value="&#xf013;"  class="showFilters" onclick="gdShowFilters(this);" style="font-family: FontAwesome;">';
			//echo '<i class="fa fa-cog showFilters" title="'.__('Customize My Search',GEODIRADVANCESEARCH_TEXTDOMAIN).'"  onclick="gdShowFilters(this);"></i>';

	add_filter('body_class', 'geodir_advance_search_body_class'); // let's add a class to the body so we can style the new addition to the search
	}
if($ajax){exit;}	
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



function geodir_advance_search_form(){
if(isset($_POST['action']) && $_POST['action'] && isset($_POST['stype']) && $_POST['stype']){$stype=$_POST['stype'];$ajax=true;}else{$stype=false;$ajax=false;}
//if($_POST){	echo '###';print_r($_POST);exit;}
if(!$ajax){	
?>
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
}
	global $current_term;
	if($stype){}
	elseif(isset($_REQUEST['stype']))
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
	
	if(!$ajax){?><div class="geodir-filter-container"> <?php }?>      
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
	<?php if(!$ajax){?></div> <?php }?>   
	<?php if($ajax){exit;}	
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

function geodir_advance_search_get_advance_search_fields($post_type) {
	global $wpdb;
	
	$post_type = $post_type!='' ? $post_type : 'gd_place';
	
	$sql = $wpdb->prepare("SELECT * FROM ".GEODIR_ADVANCE_SEARCH_TABLE." WHERE post_type = %s ORDER BY sort_order ASC", array($post_type));
	$fields = $wpdb->get_results($sql);
	return $fields;
}

function geodir_advance_search_field_option_values($post_type, $htmlvar_name) {
	global $wpdb;
	
	$post_type = $post_type!='' ? $post_type : 'gd_place';
	
	$sql = $wpdb->prepare("SELECT option_values  FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order",array($post_type, $htmlvar_name));
	
	$option_values = $wpdb->get_var($sql);
	$option_values = $option_values != '' && strstr($option_values,'/') ? explode(',', $option_values) : array();
	
	return $option_values;
}

function geodir_set_near_me_range(){
	
	$_SESSION['near_me_range']=$_POST['range'];
	//print_r($_POST);
}


###########################################################
############# SHARE LOCATION FUNCTIONS START ##############
###########################################################


function geodir_get_request_param(){
	global $current_term,$wp_query;
	
	$request_param = array();
	
	if ( is_tax() && geodir_get_taxonomy_posttype() && is_object($current_term) ){
		
		$request_param['geo_url'] = 'is_term';
		$request_param['geo_term_id'] = $current_term->term_id;
		$request_param['geo_taxonomy'] = $current_term->taxonomy;
		
	}elseif ( is_post_type_archive() && in_array(get_query_var('post_type'),geodir_get_posttypes()) ){
	
		$request_param['geo_url'] = 'is_archive';
		$request_param['geo_posttype'] = get_query_var('post_type');
	
	}elseif( is_author() && isset($_REQUEST['geodir_dashbord'] ) ){
		$request_param['geo_url'] = 'is_author';
		$request_param['geo_posttype'] = $_REQUEST['stype'];
	}elseif( is_search() && isset($_REQUEST['geodir_search']) ){
		$request_param['geo_url'] = 'is_search';
		$request_param['geo_request_uri'] = $_SERVER['QUERY_STRING'];
	}else{
		$request_param['geo_url'] = 'is_location';
	}
	
	return json_encode($request_param);
} 

function geodir_localize_all_share_location_js_msg()
{	global $geodir_addon_list;
	
	$arr_alert_msg = array(
							'geodir_advanced_search_plugin_url' => GEODIRADVANCESEARCH_PLUGIN_URL,
							'geodir_plugin_url' => geodir_plugin_url(),
							'geodir_admin_ajax_url' => admin_url('admin-ajax.php'),
							'request_param' =>  geodir_get_request_param(),
							'msg_Near' =>  __("Near:",GEODIRADVANCESEARCH_TEXTDOMAIN),
							'msg_Me' =>  __("Me",GEODIRADVANCESEARCH_TEXTDOMAIN),
							'autocomplete_field_name' => get_option('geodir_autocompleter_matches_label'),
							'geodir_enable_autocompleter_near' => get_option('geodir_enable_autocompleter_near'),
							'geodir_enable_autocompleter' => get_option('geodir_enable_autocompleter'),
							'geodir_autocompleter_autosubmit_near' => get_option('geodir_autocompleter_autosubmit_near'),
							'geodir_autocompleter_autosubmit' => get_option('geodir_autocompleter_autosubmit'),
							'geodir_location_manager_active' => (isset($geodir_addon_list['geodir_location_manager'])) ? '1' : '0',
							'msg_User_defined' =>  __("User defined",GEODIRADVANCESEARCH_TEXTDOMAIN),
							'ask_for_share_location' => apply_filters('geodir_ask_for_share_location' , false ) ,
							'geodir_autolocate_disable' => get_option('geodir_autolocate_disable') ,
							'geodir_autolocate_ask' => get_option('geodir_autolocate_ask') ,
							'geodir_autolocate_ask_msg' =>__('Do you wish to be geolocated to listings near you?',GEODIRADVANCESEARCH_TEXTDOMAIN) ,
							
							'UNKNOWN_ERROR' =>__('Unable to find your location.',GEODIRADVANCESEARCH_TEXTDOMAIN),
						
							'PERMISSION_DENINED' =>	__('Permission denied in finding your location.',GEODIRADVANCESEARCH_TEXTDOMAIN),
						
							'POSITION_UNAVAILABLE' =>	__('Your location is currently unknown.',GEODIRADVANCESEARCH_TEXTDOMAIN),	
						
							'BREAK' =>	__('Attempt to find location took too long.',GEODIRADVANCESEARCH_TEXTDOMAIN),
						
						//start not show alert msg
						
							'DEFAUTL_ERROR' =>	__('Browser unable to find your location.',GEODIRADVANCESEARCH_TEXTDOMAIN)
							// end not show alert msg
							
							
						);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}

	$script = "var geodir_advanced_search_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>'	;
}



function geodir_share_location()
{
	echo apply_filters('geodir_share_location' , home_url() ) ;
	die;
}

function geodir_do_not_share_location()
{
	$_SESSION['gd_location_shared']=1;
	die;
}






###########################################################
############# SHARE LOCATION FUNCTIONS END ################
###########################################################


###########################################################
############# AUTOCOMPLETE FUNCTIONS START ################
###########################################################
function geodir_autocompleter_options($arr = array())
{	global $geodir_addon_list;
	
	$arr[] = array( 'name' => __( 'Autocompleter for GeoDirectory', GEODIRADVANCESEARCH_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'geodir_autocompleter_options' );
	
	
	$arr[] = array( 'name' => __( 'Search Autocompleter Settings', GEODIRADVANCESEARCH_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'geodir_ajax_autocompleter_alert_options');
	
	$arr[] = array(  
			'name' => __( 'Enable Search autocompleter:', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, the autocompleter for Search is enabled.', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_autocompleter',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '1'
		);
	
	$arr[] = array(  
			'name' => __( 'Autosubmit the form on select a Search option:', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, the search form automatically is triggered when selecting a Search option.', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_autocompleter_autosubmit',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '1'
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_ajax_autocompleter_alert_options');
	
	if(isset($geodir_addon_list['geodir_location_manager'])){
	$arr[] = array( 'name' => __( 'Near Autocompleter Settings', GEODIRADVANCESEARCH_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'geodir_autocompleter_options_near');
	
	$arr[] = array(  
			'name' => __( 'Enable Near autocompleter:', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, the autocompleter for Near is enabled.', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_autocompleter_near',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '1'
		);
	
	$arr[] = array(  
			'name' => __( 'Autosubmit the form on select a Near option:', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, the search form automatically is triggered when selecting a Near option.', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_autocompleter_autosubmit_near',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '0'
		);
	

	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_autocompleter_options_near');
	}
	
	$arr[] = array( 'name' => __( 'GeoLocation Settings', GEODIRADVANCESEARCH_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'geodir_ajax_geolocation_options');
	
	
	$arr[] = array(  
			'name' => __( 'Disable geolocate on first load:', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, users will not be auto geolocated on first load.', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_autolocate_disable',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '0'
		);
	
	
	
	
	
	$arr[] = array(  
			'name' => __( 'Default Near Me miles limit (1-200)', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'Enter whole number only ex. 40 (Tokyo is largest city in the world @40 sq miles) LEAVE BLANK FOR NO DISTANCE LIMIT', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_near_me_dist',
			'type' 		=> 'text',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '40' // Default value for the page title - changed in settings
		);
	
	$arr[] = array(  
			'name' => __( 'Ask user if they wish to be geolocated', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, users will not be auto geolocated on first load.', GEODIRADVANCESEARCH_TEXTDOMAIN ),
			'id' 		=> 'geodir_autolocate_ask',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '0'
		);
	
	
	
	/*
	$i=15;
	$compass_arr=array();
	$compass_arr['']=__( 'Default (36)', GEODIRECTORY_TEXTDOMAIN );
	while($i<=55){
		$compass_arr[$i] = $i;
		$i++;
	}
	
	$arr[] = array(  
			'name' => __( 'Compass size', GEODIRECTORY_TEXTDOMAIN ),
			'desc' 		=> __( 'The size of the compass that is inside the near field of search bar.', GEODIRECTORY_TEXTDOMAIN ),
			'id' 		=> 'geodir_geo_compass_size',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( $compass_arr)
		);
	
	$i=-100;
	$compass_margin_left_arr=array();
	$compass_margin_left_arr['']=__( 'Default (-45)', GEODIRECTORY_TEXTDOMAIN );
	while($i<=100){
		$compass_margin_left_arr[$i] = $i;
		$i++;
	}
	$arr[] = array(  
			'name' => __( 'Compass margin left', GEODIRECTORY_TEXTDOMAIN ),
			'desc' 		=> __( 'The horizontal position of the icon', GEODIRECTORY_TEXTDOMAIN ),
			'id' 		=> 'geodir_geo_compass_margin_left',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( $compass_margin_left_arr)
		);
	
	$i=-100;
	$compass_margin_top_arr=array();
	$compass_margin_top_arr['']=__( 'Default (8)', GEODIRECTORY_TEXTDOMAIN );
	while($i<=100){
		$compass_margin_top_arr[$i] = $i;
		$i++;
	}
	$arr[] = array(  
			'name' => __( 'Compass margin top', GEODIRECTORY_TEXTDOMAIN ),
			'desc' 		=> __( 'The vertical position of the icon', GEODIRECTORY_TEXTDOMAIN ),
			'id' 		=> 'geodir_geo_compass_margin_top',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( $compass_margin_top_arr)
		);*/

	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_autocompleter_options');

	$arr = apply_filters('geodir_ajax_geolocation_options' ,$arr );
	
	return $arr;
}

function geodir_adminpage_advanced_search($tabs){
	
	$tabs['advanced_search_fields'] = array( 'label' =>__( 'Advanced Search', GEODIRADVANCESEARCH_TEXTDOMAIN ));
	
	return $tabs; 
}


function geodir_autocompleter_options_form($tab){
switch($tab){
		case 'advanced_search_fields':
			geodir_admin_fields( geodir_autocompleter_options() ); ?>
			<p class="submit">
        <input class="button-primary" type="submit" name="geodir_autocompleter_save"  value="<?php _e('Save changes', GEODIRADVANCESEARCH_TEXTDOMAIN);?>">
        </p>
			</div> <?php
		break;
		
		case 'geolocation_fields':
			geodir_admin_fields( geodir_autocompleter_options() ); ?>
			<p class="submit">
        <input class="button-primary" type="submit" name="geodir_autocompleter_save"  value="<?php _e('Save changes', GEODIRADVANCESEARCH_TEXTDOMAIN);?>">
        </p>
			</div> <?php
		break;
	}
}


function geodir_autocompleter_adminmenu(){
	add_options_page('Autocompleter Options', 'Autocompleter', 8, __FILE__, 'geodir_autocompleter_options');
}

function geodir_autocompleter_ajax_actions(){
	global $autocompleter_post_type;
	
	
	if(isset($_REQUEST['q']) && $_REQUEST['q'] && isset($_REQUEST['post_type']))
	{
		autocompleters();
	}

	exit;
	
}

function geodir_autocompleter_near_ajax_actions(){
	global $autocompleter_post_type;
	
	
	if(isset($_REQUEST['q']) && $_REQUEST['q'])
	{
		autocompleters_near();
	}

	exit;
	
}


function autocompleters()
{
	global $wpdb;
	
	$geodir_terms_autocomplete = "''";
	$gt_posttypes_autocomplete = "''";

	$post_types = geodir_get_posttypes('array');
	
	$post_type_array = array();
	$post_type_tax = array();
	
	$gd_post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'gd_place';
	
	if(!empty($post_types) && is_array($post_types) && array_key_exists($gd_post_type ,$post_types ) )
	{
			if(!empty($post_types[$gd_post_type]) && is_array($post_types[$gd_post_type]) && array_key_exists('taxonomies' , $post_types[$gd_post_type]  ))
			{
				foreach($post_types[$gd_post_type]['taxonomies'] as $geodir_taxonomy)
				{
					$post_type_tax[] = $geodir_taxonomy;
				}
			}
	}
	
	
	if(!empty($post_type_tax))
		$geodir_terms_autocomplete = "'".implode("','", $post_type_tax)."'";
	
		$gt_posttypes_autocomplete = "'". $gd_post_type."'";
	
	$results = (get_option('autocompleter_results')!= false)?get_option('autocompleter_results'):1;
	
	$search = isset($_GET['q']) ? $_GET['q'] : '';
	
	if(strlen($search)){
		switch($results){
			case 1: 
				
				$words1 = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT concat( name, '|', sum( count ) ) name, sum( count ) cnt FROM ".$wpdb->prefix."terms t, ".$wpdb->prefix."term_taxonomy tt WHERE t.term_id = tt.term_id AND t.name LIKE %s AND tt.taxonomy in (".$geodir_terms_autocomplete.") GROUP BY t.term_id ORDER BY cnt DESC",
						array($search.'%')
					)				
				);
				
				
				$words2 = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_title as name FROM $wpdb->posts where post_status='publish' and post_type in (".$gt_posttypes_autocomplete.") and post_date < NOW() and post_title LIKE %s ORDER BY post_title",
						array('%'.$search.'%')
					)
				);  
				
 				$words = array_merge((array)$words1 ,(array)$words2 ); 
				asort($words);
				break;
		} 
		
		foreach ($words as $word){
			if($results > 0){
				$id = isset($word->ID) ? $word->ID : '';
				echo $word->name."|".get_permalink($id)."\n";
			}else{
				echo $word->name."\n";
				}
		}
	}
}


if(isset($_REQUEST['set_location_type']) && isset($_REQUEST['set_location_val'])){
//clear user location
$_SESSION['user_lat']= '';
$_SESSION['user_lon']='';
$_SESSION['my_location']=0;	
add_filter('parse_request', 'geodir_set_location_var_in_session_autocompleter',99);
}

function geodir_set_location_var_in_session_autocompleter($wp){
global $wpdb;
		//$wp->query_vars['page_id'] = get_option('geodir_location_page'); // set page id as location page id
		
		$nLoc = $wpdb->get_row(
							$wpdb->prepare(
								"SELECT * FROM ".POST_LOCATION_TABLE." WHERE location_id= %d LIMIT 1",
								$_REQUEST['set_location_val']
							)				
						);
		if(is_object($nLoc)){
		
			if($_REQUEST['set_location_type']=='1'){// country
				$wp->query_vars['gd_country']	= $nLoc->country_slug;
				$wp->query_vars['gd_region']	= '';
				$wp->query_vars['gd_city']		= '';
				
			}
			elseif($_REQUEST['set_location_type']=='2'){// country
				$wp->query_vars['gd_country']	= $nLoc->country_slug;
				$wp->query_vars['gd_region']	= $nLoc->region_slug;
				$wp->query_vars['gd_city']		= '';
				
			}
			elseif($_REQUEST['set_location_type']=='3'){// country
				$wp->query_vars['gd_country']	= $nLoc->country_slug;
				$wp->query_vars['gd_region']	= $nLoc->region_slug;
				$wp->query_vars['gd_city']		= $nLoc->city_slug;
				
			}
			
			
			
		}
		//print_r($wp->query_vars);
		//print_r($nLoc);//exit;	
		//print_r($_SESSION);exit;
		return $wp;
}






function autocompleters_near()
{
	global $wpdb;
	//print_r($_REQUEST);exit;
	
	
	//if (defined('POST_LOCATION_TABLE')){echo '#############';}else{echo '@@@@@@@@@@@';}exit;
	$search = isset($_GET['q']) ? $_GET['q'] : '';
	
	if(!$search){return;}
	$loc_list = array();
	$countries = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM ".POST_LOCATION_TABLE." WHERE country like %s GROUP BY country LIMIT 3",
						array($search.'%')
					)				
				);
	
	if(!empty($countries)){
		foreach($countries as $country){
			echo   $country->country." <small class='gd-small-country'>(Country)</small> |".$country->country."|".$country->location_id."|1 \n";
		}
	}
	
	$regions = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM ".POST_LOCATION_TABLE." WHERE region like %s GROUP BY region LIMIT 3",
						array($search.'%')
					)				
				);
	//print_r($regions);
	if(!empty($regions)){
		foreach($regions as $region){
			echo   $region->region." <small class='gd-small-region'>(Region)</small> |".$region->region."|".$region->location_id."|2 \n";
		}
	}
	
	$cities = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM ".POST_LOCATION_TABLE." WHERE city like %s GROUP BY city LIMIT 3",
						array($search.'%')
					)				
				);
	
	if(!empty($cities)){
		foreach($cities as $city){
			echo   $city->city." <small class='gd-small-city'>(City)</small> |".$city->city."|".$city->location_id."|3 \n";
		}
	}
	exit;
	//print_r($countries);exit;
	
	
	
	if(strlen($search)){
		switch($results){
			case 1: 
				
				$words1 = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT concat( name, '|', sum( count ) ) name, sum( count ) cnt FROM ".$wpdb->prefix."terms t, ".$wpdb->prefix."term_taxonomy tt WHERE t.term_id = tt.term_id AND t.name LIKE %s AND tt.taxonomy in (".$geodir_terms_autocomplete.") GROUP BY t.term_id ORDER BY cnt DESC",
						array($search.'%')
					)				
				);
				
				
				$words2 = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_title as name FROM $wpdb->posts where post_status='publish' and post_type in (".$gt_posttypes_autocomplete.") and post_date < NOW() and post_title LIKE %s ORDER BY post_title",
						array('%'.$search.'%')
					)
				);  
				
 				$words = array_merge((array)$words1 ,(array)$words2 ); 
				asort($words);
				break;
		} 
		
		foreach ($words as $word){
			if($results > 0){
				$id = isset($word->ID) ? $word->ID : '';
				echo $word->name."|".get_permalink($id)."\n";
			}else{
				echo $word->name."\n";
				}
		}
	}
}




function geodir_autocompleter_init_script() {

	$autocomplete_field_name = get_option('geodir_autocompleter_matches_label');

	if($autocomplete_field_name == '') {
		$autocomplete_field_name = 's';
	}
	
	$default_near_text = NEAR_TEXT;
	if (get_option('geodir_near_field_default_text')) {
		$default_near_text = __(get_option('geodir_near_field_default_text'), GEODIRECTORY_TEXTDOMAIN);
	}
	
	
	$results = (get_option('autocompleter_results')!='')?get_option('autocompleter_results'):1;
	
}

add_action('wp_footer', 'geodir_autocompleter_init_script');


function geodir_autocompleter_from_submit_handler(){
	
	if(isset($_REQUEST['geodir_autocompleter_save']))
		geodir_update_options(geodir_autocompleter_options());
}


function geodir_autocompleter_taxonomies()
{

	$taxonomies_array = array();
	$args = array(
	'public'   => true,
	'_builtin' => false
	); 
	$output = 'names'; // or objects
	$operator = 'or'; // 'and' or 'or'
	$taxonomies = get_taxonomies( $args, $output, $operator ); 
	
	if(!empty($taxonomies)):
	 foreach($taxonomies as $term_que):
	 $taxonomies_array[$term_que] = $term_que;
	 endforeach;
	endif;
	
	return $taxonomies_array;

}

function geodir_autocompleter_post_types()
{
	$post_type_arr = array();
	
	$post_types = geodir_get_posttypes('object');
	
	foreach($post_types as $key => $post_types_obj)
	{
		$post_type_arr[$key] = $post_types_obj->labels->singular_name;
	}
	return 	$post_type_arr;
}


function geodir_autocompleter_script(){
	
	wp_register_script('geodir-autocompleter-js',GEODIRADVANCESEARCH_PLUGIN_URL.'/js/jquery.autocomplete.js',array('jquery'),GEODIRADVANCESEARCH_VERSION);
	wp_enqueue_script( 'geodir-autocompleter-js' );

}



function geodir_autocompleter_admin_script(){
	
	if(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'advanced_search_fields'){
		wp_register_script('geodir-autocompleter-admin-js',GEODIRADVANCESEARCH_PLUGIN_URL.'/js/autocomplete-admin.js',array('jquery'),GEODIRADVANCESEARCH_VERSION);
		wp_enqueue_script( 'geodir-autocompleter-admin-js' );
	}

}



function geodir_autocompleter_ajax_url($type='',$near=false){
	
	$gd_post_type = geodir_get_current_posttype();
	
	if($gd_post_type == '')
		$gd_post_type = 'gd_place';
	
	if($near){
	return admin_url('admin-ajax.php?action=geodir_autocompleter_near_ajax_action');
	}else{
	return admin_url('admin-ajax.php?action=geodir_autocompleter_ajax_action');
	}
}


add_action('geodir_search_near_text','geodir_set_search_near_text',10,2);

function geodir_set_search_near_text($near, $default_near_text){
	
	if(isset($_SESSION['gd_country']) && $_SESSION['gd_country']){
		global $wpdb;	
		
		
		if($_SESSION['gd_country'] && $_SESSION['gd_region'] && $_SESSION['gd_city']){
		$loc_arr = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".POST_LOCATION_TABLE." WHERE city_slug=%s",$_SESSION['gd_city']));
		return __('In:',GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$loc_arr->city.' ('.__('City',GEODIRADVANCESEARCH_TEXTDOMAIN).')';
		}elseif($_SESSION['gd_country'] && $_SESSION['gd_region']){
		$loc_arr = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".POST_LOCATION_TABLE." WHERE region_slug=%s",$_SESSION['gd_region']));
		return __('In:',GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$loc_arr->region.' ('.__('Region',GEODIRADVANCESEARCH_TEXTDOMAIN).')';
		}elseif($_SESSION['gd_country']){
		$loc_arr = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".POST_LOCATION_TABLE." WHERE country_slug=%s",$_SESSION['gd_country']));
		return __('In:',GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$loc_arr->country.' ('.__('Country',GEODIRADVANCESEARCH_TEXTDOMAIN).')';
		}

		
	}
	return $near;
	
}

add_action('geodir_search_near_class','geodir_set_search_near_class',10,1);

function geodir_set_search_near_class($class){
	if(isset($_SESSION['gd_country']) && $_SESSION['gd_country'] && (!isset($_SESSION['user_lat']) || $_SESSION['user_lat']=='')){
		global $wpdb;	
		
		
		if($_SESSION['gd_country'] && $_SESSION['gd_region'] && $_SESSION['gd_city']){
		return $class.' near-city';
		}elseif($_SESSION['gd_country'] && $_SESSION['gd_region']){
		return $class.' near-region';
		}elseif($_SESSION['gd_country']){
		return $class.' near-country';
		}

		
	}
	return $class;
}
###########################################################
############# AUTOCOMPLETE FUNCTIONS END ##################
###########################################################

?>
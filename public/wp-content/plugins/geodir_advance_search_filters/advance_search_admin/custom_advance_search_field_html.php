<?php /* ====== Custom fields form  ======*/
global $post_type;
$field_admin_title = '';
$field_site_type = '';
$htmlvar_name = '';
$field_input_type = '';
$field_type = '';

if(!isset($field_info->post_type)){
	$post_type = $_REQUEST['listing_type'];
}else
	$post_type = $field_info->post_type;

$nonce = wp_create_nonce( 'custom_advance_search_fields_'.$result_str );


if(isset($field_info->admin_title))
	$field_admin_title = $field_info->admin_title;
	


if(isset($field_info->field_site_name ))
	$field_site_name  = $field_info->field_site_name ;
else
	$field_site_name = $_REQUEST['site_field_title'];	


if(isset($_REQUEST['htmlvar_name']) && $_REQUEST['htmlvar_name']!='')
		$htmlvar_name = $_REQUEST['htmlvar_name'];
	else
		$htmlvar_name = $field_info->site_htmlvar_name ;
		
if(isset($_REQUEST['field_type'])&& !empty($_REQUEST['field_type'])){
	$field_type = $_REQUEST['field_type'];
}

$search_condition = "SINGLE";
$field_input_type = "SINGLE";
$field_data_type  = "VARCHAR";

if(isset($_REQUEST['field_data_type']) && $_REQUEST['field_data_type']){
			$field_data_type = $_REQUEST['field_data_type'];
			
	if($field_data_type=='DATE' || $field_data_type=='TIME'){
			$search_condition = "SINGLE";
			$field_input_type = "DATE";
	}elseif($field_data_type=='INT'){
			$search_condition = "SELECT";
			$field_input_type = "RANGE";
	}elseif($field_data_type=='taxonomy' || $field_data_type=='select' ){
			$search_condition = "SINGLE";
			$field_input_type = "SELECT";
			$field_data_type  = "VARCHAR";
	}
	
}
if(isset($field_info->search_condition) && !empty($field_info->search_condition))
		$search_condition = $field_info->search_condition;
		
if(isset($field_info->field_data_type) && !empty($field_info->field_data_type))
		$field_data_type = $field_info->field_data_type;

if(isset($field_info->field_input_type) && !empty($field_info->field_input_type))
		$field_input_type = $field_info->field_input_type;
		
if(isset($field_info->field_site_type) && !empty($field_info->field_site_type))
			$field_type = $field_info->field_site_type;
		

 

?>
<li class="text" id="licontainer_<?php echo $result_str;?>">
    <div class="title title<?php echo $result_str;?> gt-fieldset"   title="<?php _e('Double Click to toggle and drag-drop to sort',GEODIRADVANCESEARCH_TEXTDOMAIN);?>" ondblclick="show_hide_advance_search('field_frm<?php echo $result_str;?>')">
 <?php
 	
 	$nonce = wp_create_nonce( 'custom_advance_search_fields_'.$result_str );
 ?>    
 
        <?php if($default):?>
        	<div title="<?php _e('Drag and drop to sort',GEODIRADVANCESEARCH_TEXTDOMAIN);?>" onclick="delete_advance_search_field('<?php echo $result_str;?>', '<?php echo $nonce;?>','<?php echo $htmlvar_name;?>')"  class="handlediv close"></div>
        <?php else: ?>    
        	<div title="<?php _e('Click to remove field',GEODIRADVANCESEARCH_TEXTDOMAIN);?>" onclick="delete_advance_search_field('<?php echo $result_str;?>', '<?php echo $nonce;?>','<?php echo $htmlvar_name ;?>')" class="handlediv close"></div>
     	<?php endif;?>
         <b style="cursor:pointer;" onclick="show_hide_advance_search('field_frm<?php echo $result_str;?>')"><?php echo ucwords(__('Field:',GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$field_site_name);?></b>
       
    </div>
	
    <div id="field_frm<?php echo $result_str;?>" class="field_frm" style="display:<?php if($field_ins_upd == 'submit'){echo 'block;';}else{echo 'none;';} ?>">
        <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
        <input type="hidden" name="listing_type" id="listing_type" value="<?php echo $post_type;?>" />
        <input type="hidden" name="field_type" id="field_type" value="<?php echo $field_type;?>" />
        <input type="hidden" name="field_id" id="field_id" value="<?php echo $result_str;?>" />
    	<input type="hidden" name="data_type" id="data_type" value="<?php echo $field_input_type; ?>" />
        <input type="hidden" name="is_active" id="is_active" value="1" />
         <input type="hidden" name="site_field_title" id="site_field_title" value="<?php echo $field_site_name;?>" />
       
          <input type="hidden" name="field_data_type" id="field_data_type" value="<?php echo $field_data_type;?>" />
       
        <table class="widefat post fixed" border="0" style="width:100%;">
        <?php
				
			
				
		 if($field_type == 'taxonomy' || $field_type == 'select' || $field_type == 'radio'  || $field_type == 'multiselect' ){?>
        
            <tr>
            	<td width="45%"><strong><?php _e('Field Data Type ? :',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left">
                   
                    <select name="data_type" id="data_type" style="width:90%"  onchange="select_search_custom(this.value,'<?php echo $result_str;?>');">
                        <option value="SELECT" <?php if(isset($field_info->field_input_type) && $field_info->field_input_type=='SELECT'){ echo 'selected="selected"';}?>><?php _e('SELECT',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                        <option value="CHECK" <?php if(isset($field_info->field_input_type) && $field_info->field_input_type=='CHECK'){ echo 'selected="selected"';}?>><?php _e('CHECK',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                        <option value="LINK" <?php if(isset($field_info->field_input_type) && $field_info->field_input_type=='LINK'){ echo 'selected="selected"';}?>><?php _e('LINK',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                    </select>
               		<br /> <span><?php _e('Select Custom Field type',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
                    
                </td>
            </tr> 	
        
        <?php }else if($field_data_type == 'INT' || $field_data_type == 'FLOAT'){
		if($htmlvar_name != 'dist'){?> 
                  <tr>
                        <td width="45%"><strong><?php _e('Field Data Type ?',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                        <td align="left">                   
                            <select name="data_type_change" id="data_type_change" onchange="select_search_type(this.value,'<?php echo $result_str;?>');" style="width:100%">
                                 <option value="SELECT" <?php if(!empty($field_info->search_condition) && $field_info->search_condition =='SELECT'){ echo 'selected="selected"';}?>><?php _e('Range in SELECT',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                                <option value="LINK" <?php if(!empty($field_info->search_condition)&& $field_info->search_condition =='LINK'){ echo 'selected="selected"';}?>><?php _e('Range in LINK',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                                 <option value="TEXT" <?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'selected="selected"';}?>><?php _e('Range in TEXT',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                            </select>
                            <br /> <span><?php _e('Select Custom Field type',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
                            
                        </td>
                    </tr> 
          <?php }?> 	
            <tr class="search_type_text" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'table-row';}else{echo 'none';}?>" >
            	<td width="45%"><strong><?php _e('Searching Type:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left">
                        <select id="search_condition_select"  name="search_condition_select" onchange="select_range_option(this.value,'<?php echo $result_str;?>');" style="width:100%">
                         <option value="SINGLE" <?php if(isset($field_info->search_condition) && $field_info->search_condition=='SINGLE'){ echo 'selected="selected"';}?>><?php _e('Range single',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                          <option value="FROM" <?php if(isset($field_info->search_condition) && $field_info->search_condition=='FROM'){ echo 'selected="selected"';}?>><?php _e('Range from',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                        </select>
                </td>
              <?php  if($htmlvar_name != 'dist'){?> 
                </tr> 	
                <tr class="search_type_drop" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'none';}else{echo 'table-row';}?>">
                <td width="45%" > <strong><?php _e('Starting Search Range:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input placeholder="<?php _e('Starting Search Range', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" type="text" name="search_min_value" value="<?php if(isset($field_info->search_min_value)){echo $field_info->search_min_value;}?>" /></td>
                </tr> 
                <?php }?>	
              <tr class="search_type_drop" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'none';}else{echo 'table-row';}?>">
                
                <td width="45%"><strong><?php _e('Maximum Search Range:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input type="text" name="search_max_value" placeholder="<?php _e('Maximum Search Range', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" value="<?php if(isset($field_info->search_max_value)){ echo $field_info->search_max_value;}?>" /></td>
                </tr> 	
               <tr class="search_type_drop" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'none';}else{echo 'table-row';}?>">
           		<td width="45%"><strong><?php _e('Difference in Search Range:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input type="text" placeholder="<?php _e('Difference in Search Range', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" name="search_diff_value"  value="<?php if(isset($field_info->search_diff_value)){ echo $field_info->search_diff_value;}?>" <?php if($htmlvar_name != 'dist'){ ?> onkeyup="search_difference_value(this.value);" onchange="search_difference_value(this.value);" <?php } ?> />
                <span class="search_diff_value" style="display: <?php if(isset($field_info->search_diff_value) && $field_info->search_diff_value==1){echo 'block';}else{ echo 'none';} ?>;"> <input type="checkbox" name="searching_range_mode" value="1" <?php if( isset($field_info->searching_range_mode) && $field_info->searching_range_mode ==1){echo 'checked="checked"';}?>  /><?php _e('You want to searching with single range',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
                </td>
                </tr> 
                
            <?php  if($htmlvar_name != 'dist'){?> 
                	
               <tr class="search_type_drop" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'none';}else{echo 'table-row';}?>">
           		<td width="45%"><strong><?php _e('First Search Range:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input placeholder="<?php _e('First Search Range', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" type="text" name="first_search_value"  value="<?php if(isset($field_info->first_search_value)){ echo $field_info->first_search_value;}?>" /> 
                </td>
                </tr> 	
               <tr class="search_type_drop" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'none';}else{echo 'table-row';}?>">
           		<td width="45%"><strong><?php _e('First Search Range Text:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input placeholder="<?php _e('First Search Range Text', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" type="text" name="first_search_text"  value="<?php if(isset($field_info->first_search_text)){ echo $field_info->first_search_text;}?>" />  <br /><span><?php _e('Less than',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
                </td>
                </tr> 	
            <tr class="search_type_drop" style="display:<?php if(!empty($field_info->search_condition) && ($field_info->search_condition=='SINGLE' || $field_info->search_condition=='FROM')){ echo 'none';}else{echo 'table-row';}?>">
           		<td width="45%"><strong><?php _e('Last Search Range Text:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input placeholder="<?php _e('Last Search Range Text', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" type="text" name="last_search_text"  value="<?php if(isset($field_info->last_search_text)){ echo $field_info->last_search_text;}?>" /><br /><span><?php _e('More Than',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
                </td>
                </tr>
                  	
			<?php
			}
        }elseif($field_input_type=='DATE'){?>
        <tr>
            <td width="45%"><strong><?php _e('Searching Type:',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
            <td align="left">
             <select id="search_condition_select"  name="search_condition_select" onchange="select_range_option(this.value,'<?php echo $result_str;?>');" style="width:100%">
                    <option value="SINGLE" <?php if(isset($field_info->search_condition) && $field_info->search_condition=='SINGLE'){ echo 'selected="selected"';}?>><?php _e('Range in single',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
                    <option value="FROM" <?php if(isset($field_info->search_condition) && $field_info->search_condition=='FROM'){ echo 'selected="selected"';}?>><?php _e('Range in from',GEODIRADVANCESEARCH_TEXTDOMAIN);?></option>
             </select>
             </td>
         </tr>
        <?php
		}?>
            <?php
            $serach_field_name = '';
			
            if(isset($htmlvar_name) && $htmlvar_name =='post'){
           		 $serach_field_name = $htmlvar_name.'_'.$field_type;
				 
            }else if(isset($htmlvar_name) && $htmlvar_name==$post_type.'category'){
				 $serach_field_name = $post_type.'category';
				 
            }else{
            	$serach_field_name =  $htmlvar_name;
            }
            
            ?>
             <input type="hidden" name="search_condition" id="search_condition" value="<?php if(isset($search_condition)){ echo $search_condition;}?>" />
            <input type="hidden" name="site_htmlvar_name" value="<?php echo $htmlvar_name?>" />
            <input type="hidden" name="field_title" id="field_title" value="<?php if(isset($serach_field_name)){ echo $serach_field_name;}?>" size="50" />&nbsp;
            <tr class="expand_custom_area" style="display:<?php if((isset($search_condition) && $search_condition=="LINK") || $field_input_type=="LINK" || $field_input_type =="CHECK" || $htmlvar_name == 'dist'){ echo 'table-row';}else{echo 'none';}?>">
           		<td width="45%"><strong><?php _e('Expand Search Range :',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
                <td align="left"><input placeholder="<?php _e('Expand Search Range', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" width="35px" type="text" name="expand_custom_value" id="expand_custom_value"  value="<?php if(!empty($field_info->expand_custom_value))
										echo $field_info->expand_custom_value; ?>" /><br/>
                <input type="checkbox" name="expand_search" id="expand_search" value="1" <?php if(!empty($field_info->expand_search)) echo 'checked="checked"';  ?>  /><?php _e('Please check to expand Search Range',GEODIRADVANCESEARCH_TEXTDOMAIN);?>
                </td>
                </tr> 	
						
						<?php
						if(isset($htmlvar_name) && $htmlvar_name == 'dist'){
							
							$extra_fields = '';
							if(isset($field_info->extra_fields) && $field_info->extra_fields != '')
								$extra_fields = unserialize($field_info->extra_fields);
							
							//echo "<pre>"; print_r($extra_fields);
							$geodir_distance_sorting = isset($extra_fields['is_sort']) ? $extra_fields['is_sort'] : '';
							$search_asc = isset($extra_fields['asc']) ? $extra_fields['asc'] : '';
							$search_asc_title = isset($extra_fields['asc_title']) ? $extra_fields['asc_title'] : '';
							$search_desc = isset($extra_fields['desc']) ? $extra_fields['desc'] : '';
							$search_desc_title = isset($extra_fields['desc_title']) ? $extra_fields['desc_title'] : '';
							
							
						?>
							
							<tr>
								<td><strong>Show distance sorting</strong></td>
								<td><input type="checkbox" name="geodir_distance_sorting" id="geodir_distance_sorting" value="1" <?php if(isset($geodir_distance_sorting) && $geodir_distance_sorting == '1') echo 'checked="checked"';  ?>  />
									<span><?php _e('Select if you want to show option in distance sort.',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
									</td>
							</tr>
							
							<?php
							$show_sort_fields = ' style="display:none ;"';
							
							if(isset($geodir_distance_sorting) && $geodir_distance_sorting == '1')
								$show_sort_fields = '';
								
							?>
							
							<tr class="geodir_distance_sort_options" <?php echo $show_sort_fields;?>>
								<td>Select Nearest</td>
									<td>
									 <input type="checkbox" name="search_asc" id="search_asc"  value="1" <?php if(isset($search_asc) && $search_asc == '1'){ echo 'checked="checked"';}?>/>
									
									 <img src="<?php echo geodir_plugin_url();?>/geodirectory-assets/images/arrow18x11.png" class="field_sort_icon"/>
									 <input type="text" name="search_asc_title" id="search_asc_title" value="<?php if(isset($search_asc_title)){ echo $search_asc_title;}?>" style="width:75%;" placeholder="<?php _e('Ascending title', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" />
								
								</td>
							</tr>
							
							<tr class="geodir_distance_sort_options" <?php echo $show_sort_fields;?>>
							<td>Select Farthest</td>
								<td>
								 <input type="checkbox"  name="search_desc" id="search_desc"  value="1" <?php if(isset($search_desc) && $search_desc=='1'){ echo 'checked="checked"';}?>/>
								
								 <img src="<?php echo geodir_plugin_url();?>/geodirectory-assets/images/down-arrow18x11.png" class="field_sort_icon"/>
								 <input type="text" name="search_desc_title" id="search_desc_title" value="<?php if(isset($search_desc_title)){ echo $search_desc_title;}?>" style="width:75%;" placeholder="<?php _e('Descending title', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" />
								 <br />
									<span><?php _e('Select if you want to show option in distance sort.',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
								</td>
							</tr>
							
						<?php }?>
						
            <tr>
            <td ><strong><?php _e('Frontend  title :',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
            <td align="left">
            <input type="text" placeholder="<?php _e('Frontend  title', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" name="front_search_title" id="front_search_title" value="<?php if(isset($field_info->field_desc)){ echo $field_info->front_search_title ;}?>" />
           
            </td>
            </tr>
            <tr>
            <td ><strong><?php _e('Frontend description :',GEODIRADVANCESEARCH_TEXTDOMAIN);?></strong></td>
            <td align="left">
            <input type="text" placeholder="<?php _e('Frontend description', GEODIRADVANCESEARCH_TEXTDOMAIN); ?>" name="field_desc" id="field_desc" value="<?php if(isset($field_info->field_desc)){ echo $field_info->field_desc ;}?>" />
            <br /><span><?php _e('Section description which will appear in frontend',GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
            </td>
            </tr>
            <tr>
                <td >&nbsp;</td>
                <td align="left">
                
                <input type="button" class="button" name="save" id="save" value="Save" onclick="save_advance_search_field('<?php echo $result_str;?>')" /> 
               <input type="button" name="delete" value="Delete" onclick="delete_advance_search_field('<?php echo $result_str;?>', '<?php echo $nonce;?>','<?php echo $htmlvar_name?>')" class="button" />
                
                </td>
            </tr>
        </table>
    
    </div>
</li>
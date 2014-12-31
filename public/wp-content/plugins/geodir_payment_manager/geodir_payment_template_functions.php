<?php
function geodir_package_price_list()
{
?>
<?php global $wpdb; ?>

<div class="gd-content-heading active">
	
<h3><?php _e('Geo Directory Manage Price', GEODIRPAYMENT_TEXTDOMAIN); ?></h3>                                  
	<p style="padding-left:15px;"><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&gd_pagetype=addeditprice'?>"><strong><?php _e('Add Price', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></a> </p>
<table style=" width:100%" cellpadding="5" class="widefat post fixed" id="gd_price_table" >
  <thead>
    <tr>
      <th width="80" align="left" id="gdtable_package_id" style="min-width:90px; cursor: pointer;" char-type="int"><strong><?php _e('Package ID', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="135" align="left" id="gdtable_title" style="cursor: pointer;"><strong><?php _e('Title', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="135" align="left" id="gdtable_post_type" style="cursor: pointer;"><strong><?php _e('Post Type', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="75" align="left"><strong><?php _e('Price', GEODIRPAYMENT_TEXTDOMAIN); ?> (<?php if(get_option('geodir_currencysym')){echo  stripslashes(get_option('geodir_currencysym'));}else{echo '$';}?>)</strong></th>
      <th width="75" align="left"><strong><?php _e('Number Of Days', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="75" align="left"><strong><?php _e('Status', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="75" align="center" id="gdtable_display_order" style="cursor:pointer;" char-type="int"><strong><?php _e('Display Order', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="90" align="left"><strong><?php _e('Is Featured', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th width="60" align="left"><strong><?php _e('Action', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
      <th align="left">&nbsp;</th>
    </tr>
	<?php
	$post_types = geodir_get_posttypes();
	$post_types_length = count($post_types);
	$format = array_fill(0, $post_types_length, '%s');
	$format = implode(',', $format);
	
	$pricesql = $wpdb->prepare("select * from ".GEODIR_PRICE_TABLE." WHERE post_type IN ($format)", $post_types);
	$priceinfo = $wpdb->get_results($pricesql);
	
	if( $priceinfo ) {	
		foreach( $priceinfo as $priceinfoObj ) {
			$number_of_days = $priceinfoObj->days;
			if ( $priceinfoObj->sub_active ) {
				$sub_num_trial_days = $priceinfoObj->sub_num_trial_days;
				$sub_num_trial_units = isset( $priceinfoObj->sub_num_trial_units ) && in_array( $priceinfoObj->sub_num_trial_units, array( 'D', 'W', 'M', 'Y' ) ) ? $priceinfoObj->sub_num_trial_units : 'D';
				
				$number_of_days = $sub_num_trial_days > 0 ? $sub_num_trial_days .' '. $sub_num_trial_units . '(r)' : $priceinfoObj->sub_units_num .' '. $priceinfoObj->sub_units . '(r)';
			}
	?>
    <tr>
      <td><?php echo $priceinfoObj->pid;?></td>
      <td><?php echo $priceinfoObj->title;?></td>
      <td><?php echo $priceinfoObj->post_type;?></td>
      <td><?php echo $priceinfoObj->amount;?></td>
      <td><?php echo $number_of_days;?></td>
      <td><?php if($priceinfoObj->status==1) _e("Active", GEODIRPAYMENT_TEXTDOMAIN); else _e("Inactive", GEODIRPAYMENT_TEXTDOMAIN);?></td>
      <td align="center"><?php echo (isset($priceinfoObj->display_order) ? (int)$priceinfoObj->display_order : 0);?></td>
      <td><?php if($priceinfoObj->is_featured==1) _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?></td>
      <td><?php $nonce = wp_create_nonce( 'package_action_'.$priceinfoObj->pid ); ?>
        <a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&gd_pagetype=addeditprice&id='.$priceinfoObj->pid;?>"> <img src="<?php echo plugins_url('',__FILE__); ?>/images/edit.png" alt="<?php _e('Edit Location', GEODIRPAYMENT_TEXTDOMAIN); ?>" title="<?php _e('Edit Package', GEODIRPAYMENT_TEXTDOMAIN); ?>"/> </a> &nbsp;&nbsp;
        <?php if( !$priceinfoObj->is_default ) { ?>
        <a class="delete_package" nonce="<?php echo $nonce;?>" package_id="<?php echo $priceinfoObj->pid;?>" href="javascript:void(0);"><img src="<?php echo plugins_url('',__FILE__); ?>/images/delete.png" alt="<?php _e('Delete Location', GEODIRPAYMENT_TEXTDOMAIN); ?>" title="<?php _e('Delete Package', GEODIRPAYMENT_TEXTDOMAIN); ?>" /></a>
        <?php } ?>
      </td>
      <td>&nbsp;</td>
    </tr>
    <?php
		}
	}
	?>
  </thead>
</table>
<script>
var table = jQuery('#gd_price_table');
jQuery('#gdtable_package_id strong, #gdtable_title strong, #gdtable_post_type strong, #gdtable_display_order strong').append(' <i class="fa fa-sort"></i>');

jQuery('#gdtable_package_id, #gdtable_title, #gdtable_post_type, #gdtable_display_order')
    .wrapInner('<span title="sort this column"/>')
    .each(function(){

        var th = jQuery(this),
            thIndex = th.index(),
            inverse = false;

        th.click(function(){

            table.find('td').filter(function(){

                return jQuery(this).index() === thIndex;

            }).sortElements(function(a, b){

                if( jQuery.text([a]) == jQuery.text([b]) )
                    return 0;
				
				var charType = jQuery(th).attr('char-type');	
				if (charType=='int') {
					var aa = parseInt(jQuery.text([a]));
					var bb = parseInt($.text([b]));
					return aa > bb ?
						inverse ? -1 : 1
						: inverse ? 1 : -1;
				}
				
				return jQuery.text([a]) > $.text([b]) ?
                    inverse ? -1 : 1
                    : inverse ? 1 : -1;

            }, function(){

                // parentNode is the element we want to move
                return this.parentNode; 

            });

            inverse = !inverse;

        });

    });
</script>
											
</div>

<?php
}
/* END Of Package Price table in backend */

function geodir_payment_get_sub_num_trial_units( $default = 'D', $options_html = true  ) {
	$options = array();
	$options['D'] = __( 'Day(s)', GEODIRPAYMENT_TEXTDOMAIN );
	$options['W'] = __( 'Week(s)', GEODIRPAYMENT_TEXTDOMAIN );
	$options['M'] = __( 'Month(s)', GEODIRPAYMENT_TEXTDOMAIN );
	$options['Y'] = __( 'Years(s)', GEODIRPAYMENT_TEXTDOMAIN );
	
	$return = $options;
	if ( $options_html ) {
		$return = '';
		foreach ( $options as $value => $label ) {
			$selected = $value == $default ? 'selected="selected"' : '';
			$return .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
		}
	}
	
	return $return;
}
/* Start of Package Price add/edit form */
function geodir_package_price_form()
{
	global $wpdb, $price_db_table_name;
	$priceinfo = array();
	if(isset($_REQUEST['id']) && $_REQUEST['id']!='')
	{
		$pid = $_REQUEST['id'];

		$pricesql = $wpdb->prepare("select * from ".GEODIR_PRICE_TABLE." where pid=%d",array($pid));
		$priceinfo = $wpdb->get_results($pricesql);
	
	}
	
	$sub_num_trial_units = isset( $priceinfo[0]->sub_num_trial_units ) && !empty( $priceinfo[0]->sub_num_trial_units ) ? $priceinfo[0]->sub_num_trial_units : 'D';
	$sub_num_trial_units = in_array( $sub_num_trial_units, array( 'D', 'W', 'M', 'Y' ) ) ? $sub_num_trial_units : 'D';
	$sub_num_trial_units_options = geodir_payment_get_sub_num_trial_units( $sub_num_trial_units );
	?>

<div class="gd-content-heading active">
<h3>
  <?php if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){ _e('Edit Price', GEODIRPAYMENT_TEXTDOMAIN); }else{ _e('Add Price', GEODIRPAYMENT_TEXTDOMAIN); }?>
</h3>

<?php
	$nonce = wp_create_nonce( 'package_add_update' );
?>

<input type="hidden" name="package_add_update_nonce" value="<?php echo $nonce; ?>" />
<input type="hidden" name="gd_add_price" value="addprice">
<input type="hidden" name="gd_id" value="<?php if(isset($_REQUEST['id'])){ echo $_REQUEST['id'];}?>">
<input type="hidden" name="gd_exc_package_cat" value="<?php if(isset($priceinfo[0]->cat)) { echo $priceinfo[0]->cat;}else{ echo '';} ?>">
<table class="form-table">
  <tbody>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Price title', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <input type="text" style="min-width:200px;" name="gd_title" id="title" value="<?php if(isset($priceinfo[0]->title)){ echo $priceinfo[0]->title;}?>">
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Post type', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" class="payment_gd_posting_type" name="gd_posting_type" >
            <?php
																$post_types = geodir_get_posttypes();
																
																if(!empty($post_types))
																{
																	foreach($post_types as $post_type)
																	{
																		?>
            <option value="<?php echo $post_type;?>" <?php if(isset($priceinfo[0]->post_type) && $priceinfo[0]->post_type == $post_type){ echo 'selected="selected"';}?> ><?php echo $post_type;?></option>
            <?php																	
																	}
																}
?>
          </select>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Post fields', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield" id="show_fields">
          <?php 
				isset($priceinfo[0]->post_type) ? $post_type = $priceinfo[0]->post_type : $post_type='gd_place';
								
								$request_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
								
								$post_type_array = geodir_fields_list_by_posttype($post_type, $request_id);
								echo $post_type_array['posttype'];
							?>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Price amount', GEODIRPAYMENT_TEXTDOMAIN);?>
        (
        <?php if(get_option('geodir_currencysym')){
					echo  stripslashes(get_option('geodir_currencysym'));}else{echo '$';}?>
        )</th>
      <td class="forminp"><div class="gtd-formfield">
          <input style="min-width:200px;" type="text" name="gd_amount" value="<?php if(isset($priceinfo[0]->amount)){ echo $priceinfo[0]->amount;}?>">
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Recurring payment?', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp">
         <div class="gtd-formfield">
          <input type="checkbox" name="gd_sub_active" id="payment_sub_active" value="1" <?php if(isset($priceinfo[0]->sub_active) && $priceinfo[0]->sub_active != ''){echo 'checked="checked"';}?>>
          <label>
          <?php 
		  $rec_pay_arr = apply_filters( 'geodir_subscription_supported_by', array('PayPal') );
		  if(count($rec_pay_arr)>1){
		  $last_element = array_pop($rec_pay_arr);
		  $rec_pay = implode(',',$rec_pay_arr). __(' and ', GEODIRPAYMENT_TEXTDOMAIN).$last_element;
		  }else{
			$rec_pay  =$rec_pay_arr[0];
		  }
		  
		  echo sprintf(__('(Only supported by %s)', GEODIRPAYMENT_TEXTDOMAIN),$rec_pay);?>
          </label>
        </div>
      </td>
    </tr>
    <tr valign="top" class="show_num_days single_select_page" <?php if(isset($priceinfo[0]->sub_active) && $priceinfo[0]->sub_active != ''){echo 'style="display:none"';}?> > 
      <th class="titledesc" scope="row"><?php _e('Number of Days', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp">
        <div class=" single_select_page"  >
         <input type="text" name="gd_days"  id="days" value="<?php if(isset($priceinfo[0]->days)){ echo $priceinfo[0]->days;}?>">
         					<br /><?php _e('(set to 0 to never expire)', GEODIRPAYMENT_TEXTDOMAIN);?>
        </div>
      </td>
    </tr>
    <tr valign="top" class="show_recuring single_select_page" <?php if(!isset($priceinfo[0]->sub_active) || $priceinfo[0]->sub_active == ''){echo 'style="display:none"';}?> > 
      <th class="titledesc" scope="row"></th>
      <td class="forminp">
         
         <div class=" single_select_page"  >
		   <table cellspacing="0px" cellpadding="0px" style=" border:1px solid #ccc;">  
                   
             <tr valign="top" class="show_recuring single_select_page" <?php if(!isset($priceinfo[0]->sub_active) || $priceinfo[0]->sub_active == ''){echo 'style="display:none"';}?>>
                    <th style="border-bottom:solid 1px #CCCCCC;border-right:solid 1px #CCCCCC;"><b> <?php _e('Offer free trial', GEODIRPAYMENT_TEXTDOMAIN);?></b></th>
                    <th style="border-bottom:solid 1px #CCCCCC;"><b><?php _e('Recuring payment option', GEODIRPAYMENT_TEXTDOMAIN);?></b></th>
                    </tr>
             <tr  valign="top" class="show_recuring single_select_page" <?php if(!isset($priceinfo[0]->sub_active) || $priceinfo[0]->sub_active = ''){echo 'style="display:none"';}?>>
                    <td  style="border-right:solid 1px #CCCCCC;" ><input type="checkbox" name="fordaysckbox" id="active_offer" value="1" <?php if(isset($priceinfo[0]->sub_num_trial_days) && $priceinfo[0]->sub_num_trial_days >0){echo 'checked="checked"';}?> >
          
<?php _e('Offer free trail for', GEODIRPAYMENT_TEXTDOMAIN);?> <input type="text"  style="width:27px;" palceholder="0" name="sub_num_trial_days"  id="sub_num_trial_days"   value="<?php if(isset($priceinfo[0]->sub_num_trial_days)){ echo $priceinfo[0]->sub_num_trial_days;}?>" /> <select id="gd_sub_num_trial_units" name="gd_sub_num_trial_units" ><?php echo $sub_num_trial_units_options; ?></select><div class="clear"></div><?php _e( '(Allowed Range: Days range 1-90 || Weeks range 1-52 || Months range 1-24 || Years range 1-5)', GEODIRPAYMENT_TEXTDOMAIN ); ?>
					</td> <td  width="550"> <?php _e('Renew', GEODIRPAYMENT_TEXTDOMAIN);?>   <select id="recurring_range" name="gd_sub_units" >
		
			<option value="D" <?php if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='D'){ echo 'selected="selected"';}?> ><?php _e("Daily", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			
			<option value="W" <?php if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='W'){ echo 'selected="selected"';}?> ><?php _e("Weekly", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			
			<option value="M" <?php if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='M'){ echo 'selected="selected"';}?> ><?php _e("Monthly", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			
			<option value="Y" <?php if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='Y'){ echo 'selected="selected"';}?> ><?php _e("Yearly", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			
			</select>
                    <br /><?php _e("Every", GEODIRPAYMENT_TEXTDOMAIN);?> &nbsp; 
<select id="rangenumber"  name="gd_sub_units_num">
<option value="0">0</option><?php  $i=0;while($i<91){$i++;?>
<option value="<?php echo $i;?>" <?php if(isset($priceinfo[0]->sub_units_num) && $priceinfo[0]->sub_units_num==$i){ echo 'selected="selected"';}?> ><?php echo $i;?></option><?php }  ?></select><br />
<samp style="width:10px; height:10px;" id="subscription"> <?php
									if(!isset($priceinfo[0]->sub_units)){ echo ' <b>Day(s)</b>';}
                if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='D'){ echo '<b>'.__("Day(s)", GEODIRPAYMENT_TEXTDOMAIN).'</b>';}
                 if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='W'){ echo '<b>'.__("Week(s)", GEODIRPAYMENT_TEXTDOMAIN).'</b>';}
                 if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='M'){ echo '<b>'.__("Month(s)", GEODIRPAYMENT_TEXTDOMAIN).'</b>';}
                 if(isset($priceinfo[0]->sub_units) && $priceinfo[0]->sub_units=='Y'){ echo '<b>'.__("year(s)", GEODIRPAYMENT_TEXTDOMAIN).'</b>';}
 ?>
</samp> 
&nbsp; for <input style="width:40px;"  type="text" name="sub_units_num_times"  id="sub_units_num_times" value="<?php if(isset($priceinfo[0]->sub_units_num_times)){ echo $priceinfo[0]->sub_units_num_times;}?>"  />&nbsp; <?php _e('time(s)', GEODIRPAYMENT_TEXTDOMAIN);?> <br />
            <?php _e('(Allowed Range: Days range 1-90 || Weeks range 1-52 || Months range 1-24 || Years range 1-5)', GEODIRPAYMENT_TEXTDOMAIN);?> </td></tr>
           
        </table> 
		 </div>		
			 
	</td>
  </tr>
  
 </tbody>
</table>
      
<table class="form-table"><tbody><tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Status', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="gd_status" >
            <option value="1" <?php if(isset($priceinfo[0]->status) && $priceinfo[0]->status=='1'){ echo 'selected="selected"';}?> >
            <?php _e("Active", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="0" <?php if(!isset($priceinfo[0]->status) || $priceinfo[0]->status=='0'){ echo 'selected="selected"';}?> >
            <?php _e("Inactive", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
          </select>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Is featured', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="gd_is_featured" >
            <option value="0" <?php if(!isset($priceinfo[0]->is_featured) || $priceinfo[0]->is_featured=='0'){ echo 'selected="selected"';}?> >
            <?php _e("No", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="1" <?php if(isset($priceinfo[0]->is_featured) && $priceinfo[0]->is_featured=='1'){ echo 'selected="selected"';}?> >
            <?php _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
          </select>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Is default', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="gd_is_default" >
            <option value="0" <?php if(!isset($priceinfo[0]->is_default) || $priceinfo[0]->is_default=='0'){ echo 'selected="selected"';}?> >
            <?php _e("No", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="1" <?php if(isset($priceinfo[0]->is_default) && $priceinfo[0]->is_default=='1'){ echo 'selected="selected"';}?> >
            <?php _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
          </select>
        </div></td>
    </tr>
	<tr valign="top" class="show_ordering single_select_page"> 
      <th class="titledesc" scope="row"><?php _e('Display Order', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp">
        <div class="single_select_page">
         <input type="text" name="gd_display_order"  id="display_order" value="<?php if(isset($priceinfo[0]->display_order)) { echo (int)$priceinfo[0]->display_order; } else  { echo 0;}?>">
		 <br /><?php _e('(display sort order on front end package listing)', GEODIRPAYMENT_TEXTDOMAIN);?>
        </div>
      </td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Exclude categories', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <?php
              if(!isset($priceinfo[0]->post_type) || $priceinfo[0]->post_type=='')
							{ 
								_e('You can only exclude categories once saved.', GEODIRPAYMENT_TEXTDOMAIN);
							}
							else
							{
								/*if($priceinfo[0]->cat)
								{
									$catarr = explode(',',$priceinfo[0]->cat);   
								}*/
									?>
          <div id="show_categories">
            <?php
									$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
									$post_type_array = geodir_fields_list_by_posttype($post_type, $_REQUEST['id'], $priceinfo[0]->cat);
									echo $post_type_array['html_cat'];
									?>
          </div>
          <br />
          <?php _e('Select multiple categories to exclude by holding down "Ctrl" key. <br />(if removing a parent category, you should remove its child categories.', GEODIRPAYMENT_TEXTDOMAIN);?>
          <br />
          <b>
          <?php _e('  (It is not recommended to exclude categories from live <br /> packages as users will not be able to remove that category from the frontend.)', GEODIRPAYMENT_TEXTDOMAIN);?>
          </b>
          <?php 
							} 
							?>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Expire, Downgrade to', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield" id="gd_downgrade_pkg">
			<?php
				$request_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
				$select_dpkg = isset($priceinfo[0]->downgrade_pkg) ? $priceinfo[0]->downgrade_pkg : '';
				$post_type_array = geodir_fields_list_by_posttype($post_type, $request_id, $select_dpkg);
				echo $post_type_array['downgrade'];
			?>
          
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Title to be display while add listing', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <textarea name="gd_title_desc" cols="40" rows="5" id="title_desc"><?php if(isset($priceinfo[0]->title_desc)){ echo stripslashes($priceinfo[0]->title_desc);}?></textarea>
          <br />
          <?php _e('Keep blank to reset default content.', GEODIRPAYMENT_TEXTDOMAIN);?>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Image limit', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <input style="min-width:200px;" type="text" name="gd_image_limit" value="<?php if(isset($priceinfo[0]->image_limit)){ echo $priceinfo[0]->image_limit;}?>">
          <br />
          <?php _e('(Leave blank for unlimited)', GEODIRPAYMENT_TEXTDOMAIN);?>
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Category limit', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <input style="min-width:200px;" type="text" name="gd_cat_limit" value="<?php if(isset($priceinfo[0]->cat_limit)){echo $priceinfo[0]->cat_limit;}?>">
          <br />
          <?php _e('(Leave blank for unlimited)', GEODIRPAYMENT_TEXTDOMAIN);?>
        </div></td>
    </tr>
	<?php
	$use_desc_limit = isset($priceinfo[0]->use_desc_limit) && $priceinfo[0]->use_desc_limit==1 ? 1 : 0;
	$desc_limit = isset($priceinfo[0]->desc_limit) && (int)$priceinfo[0]->desc_limit>0 ? (int)$priceinfo[0]->desc_limit : 0;
	$use_tag_limit = isset($priceinfo[0]->use_tag_limit) && $priceinfo[0]->use_tag_limit==1 ? 1 : 0;
	$tag_limit = isset($priceinfo[0]->tag_limit) && (int)$priceinfo[0]->tag_limit>0 ? (int)$priceinfo[0]->tag_limit : 0;
	?>
	<tr valign="top" class="single_select_page">
	  <th class="titledesc" scope="row"><?php _e('Apply description limit?', GEODIRPAYMENT_TEXTDOMAIN);?></th>
	  <td class="forminp">
		<div class="gtd-formfield">
			<select style="min-width:100px;" name="gd_use_desc_limit" id="gd_use_desc_limit">
			  <option value="0" <?php if($use_desc_limit!='1'){ echo 'selected="selected"';}?>><?php _e("No", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			  <option value="1" <?php if($use_desc_limit=='1'){ echo 'selected="selected"';}?>><?php _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			</select>
			<br /><?php _e('("Yes" to apply description limit)', GEODIRPAYMENT_TEXTDOMAIN);?>
		</div>
	</td>
	</tr>
	<tr valign="top" class="single_select_page" id="use_desc_limit_on" <?php if($use_desc_limit!='1'){ echo 'style="display:none"';}?>>
	  <th class="titledesc" scope="row" style="padding-top:1px"><?php _e('Description limit', GEODIRPAYMENT_TEXTDOMAIN);?></th>
	  <td class="forminp" style="padding-top:1px">
		<div class="gtd-formfield">
			<input style="max-width:100px;" type="text" name="gd_desc_limit" value="<?php echo (int)$desc_limit;?>" />
			<br /><?php _e('(Characters limit for listing description, ex: 140)', GEODIRPAYMENT_TEXTDOMAIN);?>
		</div>
	  </td>
	</tr>
	<tr valign="top" class="single_select_page">
	  <th class="titledesc" scope="row"><?php _e('Apply tags limit?', GEODIRPAYMENT_TEXTDOMAIN);?></th>
	  <td class="forminp">
		<div class="gtd-formfield">
			<select style="min-width:100px;" name="gd_use_tag_limit" id="gd_use_tag_limit">
			  <option value="0" <?php if($use_tag_limit!='1'){ echo 'selected="selected"';}?>><?php _e("No", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			  <option value="1" <?php if($use_tag_limit=='1'){ echo 'selected="selected"';}?>><?php _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?></option>
			</select><br /><?php _e('("Yes" to apply tags limit)', GEODIRPAYMENT_TEXTDOMAIN);?>
		</div>
	</td>
	</tr>
	<tr valign="top" class="single_select_page" id="use_tag_limit_on" <?php if($use_tag_limit!='1'){ echo 'style="display:none"';}?>>
	  <th class="titledesc" scope="row" style="padding-top:1px"><?php _e('Tags limit', GEODIRPAYMENT_TEXTDOMAIN);?></th>
	  <td class="forminp" style="padding-top:1px">
		<div class="gtd-formfield" style="display:inline-block">
			<input style="max-width:100px;" type="text" name="gd_tag_limit" value="<?php echo (int)$tag_limit;?>" />
		<br /><?php _e('(Characters limit for listing tags, ex: 40)', GEODIRPAYMENT_TEXTDOMAIN);?>
		</div>
	  </td>
	</tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Google analytics', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="google_analytics" >
            <option value="0" <?php if(!isset($priceinfo[0]->google_analytics) || $priceinfo[0]->google_analytics=='0'){ echo 'selected="selected"';}?> >
            <?php _e("No", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="1" <?php if(isset($priceinfo[0]->google_analytics) && $priceinfo[0]->google_analytics=='1'){ echo 'selected="selected"';}?> >
            <?php _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
          </select>
        </div></td>
    </tr>
		
		 <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php echo SEND_TO_FRIEND;?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="geodir_sendtofriend" >
            <option value="0" <?php if(!isset($priceinfo[0]->sendtofriend) || $priceinfo[0]->sendtofriend=='0'){ echo 'selected="selected"';}?> >
            <?php _e("No", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="1" <?php if(isset($priceinfo[0]->sendtofriend) && $priceinfo[0]->sendtofriend=='1'){ echo 'selected="selected"';}?> >
            <?php _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
          </select>
        </div></td>
    </tr>
		
		<?php do_action('geodir_payment_package_extra_fields', $priceinfo); /* EVENT-MANAGER */ ?>
		
  </tbody>
</table>
<script type="text/javascript">
jQuery(function(){
	jQuery('#gd_use_desc_limit').change(function(){
		if (jQuery(this).val()=='1') {
			jQuery('#use_desc_limit_on').fadeIn();
		} else {
			jQuery('#use_desc_limit_on').fadeOut();
		}
	});
	jQuery('#gd_use_tag_limit').change(function(){
		if (jQuery(this).val()=='1') {
			jQuery('#use_tag_limit_on').fadeIn();
		} else {
			jQuery('#use_tag_limit_on').fadeOut();
		}
	});
})
</script>
<p class="submit" style="margin-top:10px; padding-left:15px;">
  <input type="submit" class="button-primary" name="submit" value="<?php _e('Submit', GEODIRPAYMENT_TEXTDOMAIN);?>" onclick="return check_frm();">
  &nbsp;
  <input type="button" class="button-primary" name="gd_cancel" value="<?php _e('Cancel', GEODIRPAYMENT_TEXTDOMAIN);?>" onClick="window.location.href='<?php echo admin_url()?>admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager'" >
</p>
</form>
</div>

<?php
}
/* end of package price add/edit form in backend*/

function geodir_payment_gateways_list()
{
global $wpdb;


// UPDATE FOR GOOGLE WALLET
if(!get_option( 'geodir_google_wallet_3' )){
	delete_option('payment_method_googlechkout');
	geodir_payment_activation_script();
	update_option('geodir_google_wallet_3',1);
}

$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s",array('payment_method_%'));

$paymentinfo = $wpdb->get_results($paymentsql);
?>
 <div class="gd-content-heading active">  
     
	<h3><?php _e('Geo Directory Manage Payment Options', GEODIRPAYMENT_TEXTDOMAIN)?></h3>
         
	<table style=" width:100%"  class="widefat post fixed" >
			<thead>
					<tr>
							<th width="250"><strong><?php _e('Method Name', GEODIRPAYMENT_TEXTDOMAIN);?></strong></th>
							
							<th width="130"><strong><?php _e('Is Active', GEODIRPAYMENT_TEXTDOMAIN);?></strong></th>
							
							<th width="130" align="center"><strong><?php _e('Sort Order', GEODIRPAYMENT_TEXTDOMAIN);?></strong></th>
							
							<th width="130" align="center"><strong><?php _e('Action', GEODIRPAYMENT_TEXTDOMAIN);?></strong></th>
							
							<th width="120" align="center"><strong><?php _e('Settings', GEODIRPAYMENT_TEXTDOMAIN);?></strong></th>
							
							<th>&nbsp;</th>
					</tr>
			<?php
			if($paymentinfo)
			{
foreach($paymentinfo as $paymentinfoObj)
{
$paymentInfo = unserialize($paymentinfoObj->option_value);

$option_id = $paymentinfoObj->option_id;

$paymentInfo['option_id'] = $option_id;

$paymentOptionArray[$paymentInfo['display_order']][] = $paymentInfo;
}
ksort($paymentOptionArray);

foreach($paymentOptionArray as $key=>$paymentInfoval)
{
for($i=0;$i<count($paymentInfoval);$i++)
{
$paymentInfo = $paymentInfoval[$i];

$option_id = $paymentInfo['option_id'];

$nonce = wp_create_nonce( 'payment_options_status_update_'.$option_id );

?>
<tr>
											<td><?php echo $paymentInfo['name'];?></td>
											
											<td><?php if($paymentInfo['isactive']){ _e("Yes", GEODIRPAYMENT_TEXTDOMAIN);}else{	_e("No", GEODIRPAYMENT_TEXTDOMAIN);}?></td>
											
											<td><?php echo $paymentInfo['display_order'];?></td>
											
											<td><?php if($paymentInfo['isactive']==1)
											{
											echo '<a href="'.admin_url().'admin-ajax.php?action=geodir_payment_manager_ajax&gdaction=change_status&status=0&id='.$option_id.'&_wpnonce='.$nonce.'">'.__('Deactivate', GEODIRPAYMENT_TEXTDOMAIN).'</a>';
											}else
											{
											echo '<a href="'.admin_url().'admin-ajax.php?action=geodir_payment_manager_ajax&gdaction=change_status&status=1&id='.$option_id.'&_wpnonce='.$nonce.'">'.__('Activate', GEODIRPAYMENT_TEXTDOMAIN).'</a>';
											}
											?></td>
											
											<td><?php
											echo '<a href="'.admin_url().'admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&gd_payact=gd_setting&id='.$option_id.'">'.__('Settings', GEODIRPAYMENT_TEXTDOMAIN).'</a>';
											?></td>
											
											<td>&nbsp;</td>
</tr>
<?php
}
}
			}
			?>
			</thead>
	</table>
</div>
<?php
}
/* end of payment gateways list in backend */

/* Payment gateway setting form  */
function geodir_payment_gateway_setting_form()
{
global $wpdb;

if(isset($_GET['status']) && $_GET['status']!= '')
{
	$option_value['isactive'] = $_GET['status'];
}

	$paymentupdsql = $wpdb->prepare("select option_name, option_value from $wpdb->options where option_id=%d", array($_GET['id']));
	
	$paymentupdinfo = $wpdb->get_results($paymentupdsql);
	if($paymentupdinfo)
	{
		foreach($paymentupdinfo as $paymentupdinfoObj)
		{
			$option_name = $paymentupdinfoObj->option_name;
			$option_value = unserialize($paymentupdinfoObj->option_value);
			$paymentOpts = $option_value['payOpts'];
		}
	}

?>
  
<div class="gd-content-heading active">	
	<h3><?php echo $option_value['name'];?> <?php _e('Settings', GEODIRPAYMENT_TEXTDOMAIN); ?></h3>
       
<?php
	$nonce = wp_create_nonce( 'payment_options_status_update_'.$_REQUEST['id'] );
?>

	<input type="hidden" name="update_payment_settings_nonce" value="<?php echo $nonce; ?>" />
	<input type="hidden" name="id" value="<?php echo $_REQUEST['id']; ?>" />
	<input type="hidden" name="paymentsetting" value="update_setting" />

<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Payment Method', GEODIRPAYMENT_TEXTDOMAIN); ?></th>
					<td class="forminp">
					 <div class="gtd-formfield">
						 <input type="text" name="payment_method" style=" width: 429px;" id="payment_method" value="<?php echo $option_value['name'];?>" size="50" />
					</div>       
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Is Active', GEODIRPAYMENT_TEXTDOMAIN); ?></th>
					<td class="forminp">
					 <div class="gtd-formfield">
						 <select name="payment_isactive" style=" width: 429px;" id="payment_isactive">
									<option value="1" <?php if($option_value['isactive']==1){ echo 'selected="selected"'; } ?>><?php _e('Activate', GEODIRPAYMENT_TEXTDOMAIN);?></option>
									<option value="0" <?php if($option_value['isactive']=='0' || $option_value['isactive']==''){ echo 'selected="selected"'; } ?>><?php _e('Deactivate', GEODIRPAYMENT_TEXTDOMAIN);?></option>
							</select>
					</div>       
					</td>
			</tr>
			
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Display Order', GEODIRPAYMENT_TEXTDOMAIN); ?></th>
					<td class="forminp">
					 <div class="gtd-formfield">
						 <input type="text" name="display_order" style=" width: 429px;" id="display_order" value="<?php echo $option_value['display_order'];?>" size="50"  />
					</div>       
					</td>
			</tr>
			
			<!-- PAYMENT MODE SETTINGS -->
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Mode', GEODIRPAYMENT_TEXTDOMAIN); ?></th>
					<td class="forminp">
					 <div class="gtd-formfield">
						<select id="payment_mode" style=" width: 429px;" name="payment_mode">
							<option value="live" <?php if("live" == $option_value['payment_mode']){echo 'selected="selected"';}?>><?php _e('Live Mode', GEODIRPAYMENT_TEXTDOMAIN);?></option>
							<option value="sandbox" <?php if("sandbox" == $option_value['payment_mode']){echo 'selected="selected"';}?>><?php _e('Test Mode (Sandbox)', GEODIRPAYMENT_TEXTDOMAIN);?></option>
						</select>
					</div>       
					</td>
			</tr>
			
			
			<?php
			for($i=0;$i<count($paymentOpts);$i++)
			{
				$payOpts = $paymentOpts[$i];
	?>
				<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo $payOpts['title'];?></th>
					<td class="forminp">
					 <div class="gtd-formfield">
					 	<?php
						if($payOpts['field_type'] == 'select'){
							
							?>
							<select name="<?php echo $payOpts['fieldname'];?>" style=" width: 429px;" id="<?php echo $payOpts['fieldname'];?>">
								<?php
								foreach($payOpts['option_values'] as $opts => $val){
								
									?><option <?php if($payOpts['value'] == $val){echo 'selected="selected"';}?> value="<?php echo $val;?>"><?php echo $opts;?></option><?php
								}
								?>
							</select>
							<?php
						
						}elseif($payOpts['field_type'] == 'text'){
						
							?>
						 <input type="text" style=" width: 429px;" name="<?php echo $payOpts['fieldname'];?>" id="<?php echo $payOpts['fieldname'];?>" value="<?php echo $payOpts['value'];?>" size="50"  /><br /><?php echo $payOpts['description'];
						
						}
						?>
					</div>       
					</td>
				</tr>
	<?php
			}
			?>
			
	</tbody>
</table>


<p class="submit" style="margin-top:10px; padding-left:15px;">
<input class="button-primary" type="submit" name="submit" value="<?php _e('Submit', GEODIRPAYMENT_TEXTDOMAIN); ?>" onclick="return chk_form();" />&nbsp;
<input class="button-primary" type="button" name="cancel" value="<?php _e('Cancel', GEODIRPAYMENT_TEXTDOMAIN); ?>" onclick="window.location.href='<?php echo admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options"; ?>'"  />

<?php $nonce = wp_create_nonce( 'payment_trouble_shoot'.$option_name ); ?>

<input class="button-primary geodir_payment_trouble_shoot" type="button" name="Trouble Shot" value="<?php _e('Trouble Shoot', GEODIRPAYMENT_TEXTDOMAIN); ?>" onclick="confirm:if (window.confirm('<?php _e('Are you wish to change?', GEODIRPAYMENT_TEXTDOMAIN); ?>')) { window.location.href='<?php echo geodir_payment_manager_ajaxurl(); ?>&payaction=trouble_shoot&nonce=<?php echo $nonce;?>&pay_method=<?php echo $option_name;?>'; }"  />

</p>
	

	</form>
	</div>
<?php
}
/* end of payment gateway setting form in backend */

/* Start Payment Invoice list */
function geodir_payment_invoice_list()
{
global $wpdb; 

?>
<div class="gd-content-heading active">
      
	<h3><?php echo PAYMENT_MANAGE_INVOICE; ?></h3>
           
	<table style=" width:100%" cellpadding="5" class="widefat post fixed" >
				
				<thead>
						<tr>
								<th width="135" align="left"><strong><?php echo GD_INVOICE_LISTING; ?></strong></th>
								
								<th width="60" align="left"><strong><?php echo GD_INVOICE_TYPE; ?></strong></th>
							 
								<th width="200" align="left"><strong><?php echo GD_INVOICE_PKG_INFO ; ?></strong></th>
								
								<th width="60" align="left"><strong><?php echo GD_INVOICE_COUPON ; ?></strong></th>
	
								<th width="200" align="left"><strong><?php echo GD_PAYMENT_INFORMATION; ?></strong></th>
								
								<th width="60" align="left"><strong><?php echo PAYMENT_STATUS; ?></strong></th>
                                
								<th align="left"><strong><i class="fa fa-times"></i></strong></th>
						</tr>
				<?php
				
				$invoicesql = "select * from ".INVOICE_TABLE." ORDER BY id DESC";
				
				$invoiceinfo = $wpdb->get_results($invoicesql);
				
				if($invoiceinfo)
				{	
					foreach($invoiceinfo as $invoiceinfoObj)
					{
					$cur_sym = geodir_get_currency_sym();
					$status = $invoiceinfoObj->status;
					$paid_amt ='';
					$paid_amt = $invoiceinfoObj->paied_amount;
					if( (isset($type) && ($type=='Paid' || $type=='Subscription-Payment')) && $status == 'paid' )
					{$total = $total + $paid_amt; } 
					
					?>
												<tr id='invoiceid-<?php echo $invoiceinfoObj->id ;?>'>
														
														<td><?php echo ucfirst($invoiceinfoObj->post_title); ?><br />
                                                        <a href="<?php echo get_permalink($invoiceinfoObj->post_id); ?>"><?php _e('front', GEODIRPAYMENT_TEXTDOMAIN); ?></a> |  <?php edit_post_link( 'back', '', '', $invoiceinfoObj->post_id ); ?></td>
														
														<td><?php echo ucfirst($invoiceinfoObj->type); ?></td>
														
														<td>
															<label><?php echo GD_INVOICE_DATE; ?>:</label>
															<?php echo $invoiceinfoObj->date;?><br />
															
																	<label><?php echo PAYMENT_META_ID;?></label> 
															<?php echo $invoiceinfoObj->package_id;?><br />
															
																		<label><?php echo PAYMENT_META_AMOUNT ;?></label> 
															<?php echo $cur_sym.$invoiceinfoObj->amount;?><br/>
																										
																		<label><?php echo PAYMENT_META_ALIVE_DAYS;?></label> 
															<?php echo $invoiceinfoObj->alive_days;?>
																
														</td>
														
														<td><?php echo ($invoiceinfoObj->coupon_code) ? $invoiceinfoObj->coupon_code : PAYMENT_META_NA;?></td>
														
														<td>
														<?php echo GD_INVOICE_DISCOUNT ; ?>:&nbsp;
														<?php echo ($invoiceinfoObj->discount) ? $invoiceinfoObj->discount : '0';?><br />
														<?php echo GD_INVOICE_PAY_AMOUNT ; ?>:&nbsp;
														<?php echo ($paid_amt) ? $cur_sym.$paid_amt : '0'; ?><br />
														<?php echo GD_INVOICE_PAY_METHOD ; ?>:&nbsp;
														<?php echo ($invoiceinfoObj->paymentmethod) ? $invoiceinfoObj->paymentmethod : PAYMENT_META_NA;?>
                                                        <br />
                                                        <?php 
														if ($invoiceinfoObj->HTML != '')
														{ ?>
                                                        <a href="javascript:void(0);" class="geodir_invoice_detail_link" data-invoiceid='<?php echo $invoiceinfoObj->id ;?>' ><?php _e('View Invoice', GEODIRPAYMENT_TEXTDOMAIN);?></a>							  														<?php }?>
														</td>
													
														<td>
														
														<?php
														$nonce = wp_create_nonce( 'invoice_status_update_nonce' );
														?>
														
																<select id="status" onchange="window.location.href='<?php echo admin_url().'admin-ajax.php?action=geodir_payment_manager_ajax&invoice_action=invoice&postid='.$invoiceinfoObj->post_id.'&invoiceid='.$invoiceinfoObj->id; ?>&_wpnonce=<?php echo $nonce; ?>&inv_status='+this.value">
																<option value="paid" <?php if($status == 'paid') echo 'selected="selected"' ;?> ><?php echo PAYMENT_META_PAID;?></option>
																<option value="unpaid" <?php if($status == 'unpaid' || $status == 'pending') echo 'selected="selected"' ;?> ><?php echo PAYMENT_META_UNPAID ;?></option>
															 
																</select>
														</td>
														
														<td><span class="geodir_invoice_delete_link" data-invoiceid='<?php echo $invoiceinfoObj->id ;?>' title="<?php _e('Delete Invoice', GEODIRPAYMENT_TEXTDOMAIN);?>" style="color:#F00;cursor: pointer;"><i class="fa fa-times"></i></span></td>
												</tr>
                                                
                                               
                                                
                                                <?php if( $invoiceinfoObj->HTML !='')
												{
												?>
                                                <tr id="geodir_invoice_row_<?php echo $invoiceinfoObj->id ;?>" class="geodir_invoice_row"  style="display:none;">
                                                <td colspan="6"  width="100%">
                                               <p><?php echo $invoiceinfoObj->HTML ; ?></p>
                                                </td>
                                                </tr>
					<?php
												}
					}
							}
				?>
				</thead>
				</table>
</div>
<script>
	jQuery('.geodir_invoice_detail_link').click(function(){
		var invoiceid = jQuery(this).data("invoiceid") ;
		if(jQuery('#geodir_invoice_row_'+invoiceid).is(':visible') )
			jQuery('#geodir_invoice_row_'+invoiceid).hide();
		else
			jQuery('#geodir_invoice_row_'+invoiceid).show();
		
	});
	
	jQuery('.geodir_invoice_delete_link').click(function(){
		var invoiceid = jQuery(this).data("invoiceid") ;
		if(confirm('<?php _e('Are you sure you want to delete this invoice?', GEODIRPAYMENT_TEXTDOMAIN);?>'))
		{
			geodir_del_invoice(invoiceid);
		}
		
	});
	
function geodir_del_invoice(id){
	if(!id){return;}
	
	var data = {
			'action': 'geodir_del_invoice',
			'invoice_id': id
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			if(response){
			jQuery('#invoiceid-'+id).css('background-color', 'red');
			jQuery('#invoiceid-'+id).fadeOut( "slow" );
			}else{alert('<?php _e('Something went wrong.', GEODIRPAYMENT_TEXTDOMAIN);?>');}
		});
	
	
}
</script>
<?php	
}
/* End Payment invoice list in backend */


/* Start Payment Coupon list in backend */
function geodir_payment_coupon_list()
{
?>
<?php global $wpdb; ?>

<div class="gd-content-heading active">

	<h3><?php _e('Geo Directory Manage Coupons', GEODIRPAYMENT_TEXTDOMAIN); ?></h3>   
	
	<p style="padding-left:15px;">Allow coupon option on submit Add Listing page :
	<?php $geodir_allow_coupon_code = get_option('geodir_allow_coupon_code'); ?>
	
	<?php
	$nonce = wp_create_nonce( 'allow_coupon_code_nonce' );
	?>
	<input type="hidden" id="allow_coupon_code_nonce" name="allow_coupon_code_nonce" value="<?php echo $nonce;?>" />
	<input type="radio" class="geodir_allow_coupon_code" name="geodir_allow_coupon_code" value="1" <?php if($geodir_allow_coupon_code){ echo 'checked="checked"';} ?> /><?php _e('Yes', GEODIRPAYMENT_TEXTDOMAIN );?>
	<input type="radio" class="geodir_allow_coupon_code" name="geodir_allow_coupon_code" value="0" <?php if(!$geodir_allow_coupon_code){ echo 'checked="checked"';} ?> /><?php _e('No', GEODIRPAYMENT_TEXTDOMAIN );?> 
	
	<input type="button" id="allow_coupon_code" class="button-primary" name="submit" value="<?php _e('Update', GEODIRPAYMENT_TEXTDOMAIN);?>" >
	</p>
	                               
						<p style="padding-left:15px;"><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&gd_pagetype=addeditcoupon'?>"><strong><?php _e('Add Coupon', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></a> 
						
						
						</p>
	<table style=" width:100%" cellpadding="5" class="widefat post fixed" >
							
							<thead>
									<tr>
											<th width="150" align="left"><strong><?php _e('Coupon Code', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
										 
											<th width="100" align="left"><strong><?php _e('Discount Type', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
											
											<th width="120" align="left"><strong><?php _e('Discount Amount', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
											
											<th width="100" align="left"><strong><?php _e('Post Types', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
                                            
											<th width="80" align="left"><strong><?php _e('Recurring', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
											
											<th width="75" align="left"><strong><?php _e('Status', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
											
											<th width="70" align="left"><strong><?php _e('Action', GEODIRPAYMENT_TEXTDOMAIN); ?></strong></th>
											
											<th align="left">&nbsp;</th>
									</tr>
							
							<?php
							$couponsql = "select * from ".COUPON_TABLE;
							
							$couponinfo = $wpdb->get_results($couponsql);

							if($couponinfo)
							{	
								foreach($couponinfo as $couponinfoObj)
								{ ?>
									<tr>
											<td><?php echo $couponinfoObj->coupon_code;?></td>
											
											<td><?php if($couponinfoObj->discount_type=='per') _e("Percentage", GEODIRPAYMENT_TEXTDOMAIN); else _e("Amount", GEODIRPAYMENT_TEXTDOMAIN);?></td>
											
											<td><?php echo $couponinfoObj->discount_amount;?></td>
											
											<td><?php echo $couponinfoObj->post_types;?></td>
                                        
											<td><?php if($couponinfoObj->recurring=='0'){_e("All payments", GEODIRPAYMENT_TEXTDOMAIN);}
											elseif($couponinfoObj->recurring=='1'){_e("First payment only", GEODIRPAYMENT_TEXTDOMAIN);}
											?></td>
											
											<td><?php if($couponinfoObj->status==1) _e("Active", GEODIRPAYMENT_TEXTDOMAIN); else _e("Inactive", GEODIRPAYMENT_TEXTDOMAIN);?></td>
											
											<td>
											<?php
												$nonce = wp_create_nonce( 'coupon_code_delete_'.$couponinfoObj->cid);
											?>
											
											<a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&gd_pagetype=addeditcoupon&id='.$couponinfoObj->cid;?>">
											<img src="<?php echo plugins_url('',__FILE__); ?>/images/edit.png" alt="<?php _e('Edit Coupon', GEODIRPAYMENT_TEXTDOMAIN); ?>" title="<?php _e('Edit Coupon', GEODIRPAYMENT_TEXTDOMAIN); ?>"/>
											</a> 
											&nbsp;&nbsp;
											<a class="delete_coupon" nonce="<?php echo $nonce;?>" coupon_id="<?php echo $couponinfoObj->cid;?>" href="javascript:void(0);"><img src="<?php echo plugins_url('',__FILE__); ?>/images/delete.png" alt="<?php _e('Delete Coupon', GEODIRPAYMENT_TEXTDOMAIN); ?>" title="<?php _e('Delete Coupon', GEODIRPAYMENT_TEXTDOMAIN); ?>" /></a>
											</td>
											<td>&nbsp;</td>
									</tr>
								<?php
								}
							}
							?>
						</thead>
					</table>
	</div>
	
<?php
}


/* Start Payment Coupon add/edit in backend */
function geodir_payment_coupon_form()
{
	global $wpdb;
	if(isset($_REQUEST['id']) && $_REQUEST['id']!='')
	{
		$cid = $_REQUEST['id'];
		
		$couponsql = $wpdb->prepare("select * from ".COUPON_TABLE." where cid=%d", array($cid));
		$couponinfo = $wpdb->get_row($couponsql);
	}

?>

<div class="gd-content-heading active">

<h3>
  <?php if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){ _e('Edit Coupon', GEODIRPAYMENT_TEXTDOMAIN); }else{ _e('Add Coupon', GEODIRPAYMENT_TEXTDOMAIN); }?>
</h3>

<?php
	$nonce = wp_create_nonce( 'coupon_add_update' );
?>

<input type="hidden" name="coupon_add_update_nonce" value="<?php echo $nonce; ?>" />
<input type="hidden" name="gd_add_coupon" value="addprice">
<input type="hidden" name="gd_id" value="<?php if(isset($_REQUEST['id'])){ echo $_REQUEST['id'];}?>">
<table class="form-table">
  <tbody>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Coupon Code', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <input type="text" style="min-width:200px;" name="coupon_code" id="coupon_code" value="<?php if(isset($couponinfo->coupon_code)){ echo $couponinfo->coupon_code;}?>">
        </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Post type', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
					
					<?php 
					$get_post_types = array();
					
					if(isset($couponinfo->post_types) && $couponinfo->post_types != '')
						$get_post_types = explode(',',$couponinfo->post_types);
					?>
					
          <select multiple="multiple" style="min-width:200px;" id="post_type" name="post_type[]" >
            <?php
							$post_types = geodir_get_posttypes();
							
							if(!empty($post_types))
							{
								foreach($post_types as $post_type)
								{
									?>
									<option value="<?php echo $post_type;?>" <?php if(in_array($post_type,$get_post_types)){ echo 'selected="selected"';}?> ><?php echo $post_type;?></option>
									<?php																	
								}
							}
						?>
          </select>
        </div></td>
    </tr>
    
		<tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Discount Type', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <input type="radio" style="min-width:20px;" name="discount_type" <?php if((isset($couponinfo->discount_type) && $couponinfo->discount_type=='per') || (!isset($couponinfo->discount_type) || $couponinfo->discount_type=='')){ echo 'checked="checked"';}?> id="discount_type" value="per"><?php _e('Percentage(%)', GEODIRPAYMENT_TEXTDOMAIN);?>
					<input type="radio" style="min-width:20px;" name="discount_type" <?php if(isset($couponinfo->discount_type) && $couponinfo->discount_type=='amt'){ echo 'checked="checked"';}?>  id="discount_type" value="amt"><?php _e('Amount', GEODIRPAYMENT_TEXTDOMAIN);?>
        </div></td>
    </tr>
   
	 <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Discount Amount ($)', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <input type="text" style="min-width:200px;" name="discount_amount" id="discount_amount" value="<?php if(isset($couponinfo->discount_amount)){echo $couponinfo->discount_amount;}?>">
        </div></td>
    </tr>
		
        <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Recurring', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="gd_recurring" >
            <option value="0" <?php if(!isset($couponinfo->recurring) || $couponinfo->recurring=='0'){ echo 'selected="selected"';}?> >
            <?php _e("All payments", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="1" <?php if(isset($couponinfo->recurring) && $couponinfo->recurring=='1'){ echo 'selected="selected"';}?> >
            <?php _e("First payment only", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>

          </select><small><?php _e('If applied to a recurring price package, how should it apply.', GEODIRPAYMENT_TEXTDOMAIN);?></small>
        </div></td>
    </tr>
    
    
    
		<tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Status', GEODIRPAYMENT_TEXTDOMAIN);?></th>
      <td class="forminp"><div class="gtd-formfield">
          <select style="min-width:200px;" name="gd_status" >
            <option value="1" <?php if(isset($couponinfo->status) && $couponinfo->status=='1'){ echo 'selected="selected"';}?> >
            <?php _e("Active", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
            <option value="0" <?php if(!isset($couponinfo->status) || $couponinfo->status=='0'){ echo 'selected="selected"';}?> >
            <?php _e("Inactive", GEODIRPAYMENT_TEXTDOMAIN);?>
            </option>
          </select>
        </div></td>
    </tr>
 
 </tbody>
</table>
      
<p class="submit" style="margin-top:10px; padding-left:15px;">
  <input type="submit" id="coupon_submit" class="button-primary" name="submit" value="<?php _e('Submit', GEODIRPAYMENT_TEXTDOMAIN);?>" >
  &nbsp;
  <input type="button" class="button-primary" name="gd_cancel" value="<?php _e('Cancel', GEODIRPAYMENT_TEXTDOMAIN);?>" onClick="window.location.href='<?php echo admin_url()?>admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager'" >
</p>
</form>
</div>

<?php
}


function geodir_payment_option_form($tab_name)
{
	
	switch ($tab_name)
	{
		case 'geodir_payment_general_options' :
		
			geodir_admin_fields( geodir_payment_general_options() );?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRPAYMENT_TEXTDOMAIN ); ?>" />
			<input type="hidden" name="subtab" value="geodir_payment_general_options" id="last_tab" />
			</p>
			</div><?php
			
		break;
		case 'payment_notifications' :
		
			geodir_admin_fields( geodir_payment_notifications() ); ?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRPAYMENT_TEXTDOMAIN ); ?>" />
			<input type="hidden" name="subtab" value="payment_notifications" id="last_tab" />
			</p>
			</div>
			
		<?php break;
		
	}// end of switch
}



?>
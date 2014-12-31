<?php
function geodir_custom_gmaps_get_option_form($tab_name) {
	switch ($tab_name) {
		case 'geodir_custom_gmaps_general_options': {
			geodir_admin_fields( geodir_custom_gmaps_general_options() );
			?>
<p class="submit">
  <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRCUSTOMGMAPS_TEXTDOMAIN ); ?>" />
  <input type="hidden" name="subtab" value="geodir_custom_gmaps_general_options" id="last_tab" />
</p>
</div>
		<?php
		}
		break;		
	}// end of switch
}

function geodir_custom_gmaps_show_styles_list() {
	?>
<div class="gd-content-heading active">
  <h3>
    <?php _e('Custom Maps Manage Styles', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>
  </h3>
  <table cellpadding="5" class="widefat post fixed" id="gd_cgm_table" >
    <thead>
      <tr>
        <th class="lft" id="gd_cgm_style_name" style="cursor:pointer;"><strong>
          <?php _e('Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>
          </strong></th>
        <th width="120" class="cntr"><strong>
          <?php _e('Action', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>
          </strong></th>
      </tr>
    </thead>
    <tbody>
      <tr class="gd-cgm-odd">
        <th class="lft"><strong><?php _e('Home Page Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?></strong></th>
        <th class="cntr"> <?php $nonce = wp_create_nonce('custom_gmap_action_home'); ?>
          <a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles&gd_map=home';?>"><img src="<?php echo plugins_url('',__FILE__); ?>/images/edit.png" alt="<?php _e('Update', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>" title="<?php _e('Update Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>"/></a></th>
      </tr>
	  <tr class="gd-cgm-even">
        <th class="lft"><strong><?php _e('Listing Page Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?></strong></th>
        <th class="cntr"> <?php $nonce = wp_create_nonce('custom_gmap_action_listing'); ?>
          <a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles&gd_map=listing';?>"><img src="<?php echo plugins_url('',__FILE__); ?>/images/edit.png" alt="<?php _e('Update', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>" title="<?php _e('Update Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>"/></a></th>
      </tr>
	  <tr class="gd-cgm-odd">
        <th class="lft"><strong><?php _e('Detail Page Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?></strong></th>
        <th class="cntr"> <?php $nonce = wp_create_nonce('custom_gmap_action_detail'); ?>
          <a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles&gd_map=detail';?>"><img src="<?php echo plugins_url('',__FILE__); ?>/images/edit.png" alt="<?php _e('Update', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>" title="<?php _e('Update Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN); ?>"/></a></th>
      </tr>
    </tbody>
  </table>
</div>
<?php
}

function geodir_custom_gmaps_add_style_form() {
	$gd_map = isset($_REQUEST['gd_map']) ? trim($_REQUEST['gd_map']) : '';
	
	if (!($gd_map=='home' || $gd_map=='listing' || $gd_map=='detail')) {
		wp_redirect(admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles');
		exit;
	}
	
	$feature_type_options = geodir_custom_gmaps_feature_type_options();
	$element_type_options = geodir_custom_gmaps_element_type_options();
	$styler_options = geodir_custom_gmaps_Styler();
	
	$saved_option = get_option('geodir_custom_gmaps_style_'.$gd_map);
	
	$styler_width = count($styler_options) > 0 ? 100 / count($styler_options) : 100;
	
	$title = '';
	if ($gd_map=='home') {
		$title = __('Home Page Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
	} else if ($gd_map=='listing') {
		$title = __('Listing Page Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
	} else if ($gd_map=='detail') {
		$title = __('Detail Page Map Style', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
	}
	?>
<div class="gd-content-heading active">
  <h3>
    <?php _e('Custom Maps Update Style:', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?> <?php echo $title;?>
  </h3>
  <label class="gd-about-styler"><a href="<?php _e('https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyler', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>" target="_blank"><?php _e('Read more about MapTypeStyler Properties.', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?></a></label>
  <h3><?php _e('Map Preview:', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?></h3>
  <div id="gd_cgm_preview_map" class="geodir_map"></div>
  <div class="clear"></div><input id="gd_preview_style" type="button" class="button-primary" value="<?php _e('Preview', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>" />
  <div class="clear"></div>
  <input type="hidden" name="custom_gmaps_update_nonce" value="<?php echo wp_create_nonce('custom_gmaps_update'); ?>" />
  <input type="hidden" name="gd_map" value="<?php echo $gd_map;?>" id="gd_map" />
  <table class="form-table gd-custom-gmaps-style-table">
    <tbody>
	  <tr valign="top" class="gd-custom-gmaps-attrs">
        <td valign="top" class="gd-custom-gmaps-td">
			<table class="form-table gd-custom-gmaps-table">
				<tr>
				  <td colspan="4" style="width:50%"><?php _e('featureType: ', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?><select class="gd-select-medium" id="gd_custom_gmaps_ftype"><?php echo $feature_type_options; ?></select></td>
				  <td colspan="4" style="width:50%"><?php _e('elementType: ', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?><select class="gd-select-medium" id="gd_custom_gmaps_etype"><?php echo $element_type_options; ?></select></td>
				</tr>
				<tr class="stylers-label">
				  <td colspan="8"><label><?php _e('stylers:', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?></label></td>
				</tr>
				<tr>
  <?php 
  	foreach ($styler_options as $styler_option) { 
		$placeholder = $styler_option;
		$class = 'gd-cgm-style gd-styler';
		$extra_attr = '';
		switch ($styler_option) {
			case 'color':
				$placeholder = '#ff0000';
				$extra_attr .= ' type="text" maxlength="7"';
			break;
			case 'gamma':
				$placeholder = '1.0';
				$extra_attr .= ' type="text" min="0.01" max="10" maxlength="4"';
				$class .= ' rgt';
			break;
			case 'hue':
				$placeholder = '#ff0000';
				$extra_attr .= ' type="text" maxlength="7"';
			break;
			case 'lightness':
				$placeholder = '-25';
				$extra_attr .= ' type="text" min="-100" max="100" maxlength="4"';
				$class .= ' rgt';
			break;
			case 'saturation':
				$placeholder = '-100';
				$extra_attr .= ' type="text" min="-100" max="100" maxlength="4"';
				$class .= ' rgt';
			break;
			case 'weight':
				$placeholder = '1';
				$extra_attr .= ' type="text" min="0" max="1000" maxlength="3"';
				$class .= ' rgt';
			break;
			case 'visibility':
			break;
			case 'invert_lightness':
			break;
		}
		$extra_attr .= ' class="'.$class.'" placeholder="'.$placeholder.'"';
	?>
  				<td style="width:<?php echo $styler_width;?>%"><label><?php echo $styler_option;?>:</label><div class="clear"></div><?php if ($styler_option=='visibility') { ?><select data-name="<?php echo $styler_option;?>" <?php echo $extra_attr;?>><option value=""><?php _e('default', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?></option><option value="on">on</option><option value="off">off</option><option value="simplifed">simplifed</option></select><?php } else if ($styler_option=='invert_lightness') { ?><select data-name="<?php echo $styler_option;?>" <?php echo $extra_attr;?>><option value=""><?php _e('default', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?></option><option value="true">true</option></select><?php } else { ?><input value="" data-name="<?php echo $styler_option;?>" <?php echo $extra_attr;?> /><?php } ?></td>
  <?php } ?>
				</tr>
			</table>
		</td>
		<td style="width:30px" class="cntr" valign="middle"><input id="gd_add_style" type="button" class="button-primary" name="gd_add_style" value="<?php _e('Add', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>" /></td>
      </tr>
	  <?php $i = 0; if (!empty($saved_option) && is_array($saved_option)) { ?>
	  <?php 
	  foreach ($saved_option as $option_row) {
	  	$stylers = isset($option_row['stylers']) ? $option_row['stylers'] : array();
		$saved_stylers = array();
		if (!empty($stylers)) {
			foreach ($stylers as $styler) {
				if (!empty($styler) && is_array($styler)) {
					foreach ($styler as $stylerF => $stylerV) {
						$saved_stylers[$stylerF] = $stylerV;
					}
				}
			}
		}
	  ?>
		<tr valign="top" class="gd-style-row">
		  <td valign="top" class="gd-custom-gmaps-td"><table class="form-table gd-custom-gmaps-table">
			  <tbody>
				<tr>
				  <td style="width:50%" colspan="4"><?php _e('featureType: ', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?><font class="cgm-val"><?php echo (isset($option_row['featureType']) ? $option_row['featureType'] : '');?></font>
					<input type="hidden" value="<?php echo (isset($option_row['featureType']) ? $option_row['featureType'] : '');?>" name="gd_gmap_style[<?php echo $i;?>][featureType]" class="stl-featureType"></td>
				  <td style="width:50%" colspan="4"><?php _e('elementType: ', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?><font class="cgm-val"><?php echo (isset($option_row['elementType']) ? $option_row['elementType'] : '');?></font>
					<input type="hidden" value="<?php echo (isset($option_row['elementType']) ? $option_row['elementType'] : '');?>" name="gd_gmap_style[<?php echo $i;?>][elementType]" class="stl-elementType"></td>
				</tr>
				<tr>
				  <?php foreach ($styler_options as $styler_option) { ?>
				  	<td style="<?php echo $styler_width;?>%"><label><?php echo $styler_option;?>:</label><div class="clear"></div><font class="cgm-val"><?php echo (isset($saved_stylers[$styler_option]) ? $saved_stylers[$styler_option] : '');?></font><input type="hidden" value="<?php echo (isset($saved_stylers[$styler_option]) ? $saved_stylers[$styler_option] : '');?>" name="gd_gmap_style[<?php echo $i;?>][stylers][<?php echo $styler_option;?>]" data-name="<?php echo $styler_option;?>" class="stl-styler"></td>
					<?php } ?>
				</tr>
			  </tbody>
			</table></td>
		  <td valign="middle" class="cntr"><input type="button" onclick="jQuery(this).closest('.gd-style-row').remove();" id="gd_remove_style" class="button-primary" value="<?php _e('Remove', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>"></td>
		</tr>
	  <?php  $i++; } ?>
	  <?php } ?>
    </tbody>
  </table>
  <p class="submit" style="margin-top:10px; padding-left:15px;">
    <input type="hidden" id="gd-cgm-index" value="<?php echo $i;?>" />
	<input type="submit" class="button-primary" name="submit" value="<?php _e('Submit', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="button-primary" name="gd_cancel" value="<?php _e('Cancel', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>" onclick="window.location.href='<?php echo admin_url()?>admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles'" />
  </p>
  </form>
</div>
<?php
$default_location = geodir_get_default_location();
$default_lng = isset($default_location->city_longitude) ? $default_location->city_longitude : '';
$default_lat = isset($default_location->city_latitude) ? $default_location->city_latitude : '';
$default_lng = $default_lng ? $default_lng : '39.952484';
$default_lat = $default_lat ? $default_lat : '-75.163786';
?>
<script type="text/javascript">
jQuery(function(){
	jQuery('#gd_add_style').click(function(){
		var ftypeN = 'featureType';
		var etypeN = 'elementType';
		var ftypeV = jQuery('#gd_custom_gmaps_ftype').val();
		ftypeV = ftypeV!='undefined' ? ftypeV : '';
		var etypeV = jQuery('#gd_custom_gmaps_etype').val();
		etypeV = etypeV!='undefined' ? etypeV : '';
		
		var id = parseInt(jQuery('#gd-cgm-index').val());
		var content = '';
		content += '<tr valign="top" class="gd-style-row">';
			content += '<td valign="top" class="gd-custom-gmaps-td">';
				content += '<table class="form-table gd-custom-gmaps-table"><tbody>';
					content += '<tr>';
						content += '<td colspan="4" style="width:50%"><?php _e('featureType: ', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?><font class="cgm-val">'+ftypeV+'</font><input type="hidden" name="gd_gmap_style['+id+'][featureType]" value="'+ftypeV+'" class="stl-featureType" /></td>';
						content += '<td colspan="4" style="width:50%"><?php _e('elementType: ', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?><font class="cgm-val">'+etypeV+'</font><input type="hidden" name="gd_gmap_style['+id+'][elementType]" value="'+etypeV+'" class="stl-elementType" /></td>';
					content += '</tr>';
					content += '<tr>';
						
		jQuery('.gd-custom-gmaps-table .gd-styler').each(function(){
			var $this = this;
			var styName = jQuery($this).attr('data-name');
			var styVal = jQuery($this).val();
			styVal = styVal!='undefined' ? styVal : '';
						content += '<td style="<?php echo $styler_width;?>%"><label>'+styName+':</label><div class="clear"></div><font class="cgm-val">'+styVal+'</font><input type="hidden" name="gd_gmap_style['+id+'][stylers]['+styName+']" value="'+styVal+'" data-name="'+styName+'" class="stl-styler" />';
			jQuery($this).val('');
		});
					content += '</tr>';
				content += '</tbody></table>';
			content += '</td>';
			content += '<td valign="middle" class="cntr"><input type="button" value="<?php _e('Remove', GEODIRCUSTOMGMAPS_TEXTDOMAIN);?>" class="button-primary" id="gd_remove_style" onclick="jQuery(this).closest(\'.gd-style-row\').remove();"></td>';
		content += '</tr>';
		
		jQuery('#gd_custom_gmaps_ftype').val('all');
		jQuery('#gd_custom_gmaps_etype').val('');
		jQuery('#gd-cgm-index').val(id+1);
		jQuery('.gd-custom-gmaps-attrs').after(content);
	});
	var myOptions = {
		zoom: 8,
		center: new google.maps.LatLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
	};
	
	jQuery('#gd_cgm_preview_map').goMap(myOptions);
	<?php if (!empty($saved_option) && (is_array($saved_option) || is_object($saved_option))) { ?>
	try {
		var mapStyles = JSON.parse('<?php echo json_encode($saved_option);?>');
		if (typeof mapStyles == 'object' && mapStyles ) {
			jQuery.goMap.map.setOptions({styles: mapStyles});
		}
	}
	catch(err) {
		console.log(err.message);
	}
	<?php } ?>
	
	jQuery('#gd_preview_style').click(function(){
		var myStyles = [];
		jQuery('.gd-custom-gmaps-style-table .gd-style-row').each(function(){
			var $this = this;
			var fType = jQuery($this).find('.stl-featureType').val();
			var eType = jQuery($this).find('.stl-elementType').val();
			var stylers = [];
			var style = {}; // my object
			var j = 0;
			jQuery($this).find('.stl-styler').each(function(){
				var $sty = this;
				var styV = jQuery($sty).val();
				var style = {};
				if (typeof styV!='undefined' && styV != '') {
					var styN = jQuery($sty).attr('data-name');
					if (style) {
						style[styN] = styV;
						stylers[j] = style;
						j++;
					}
				}
			});
			if (typeof fType!='undefined' && fType != '' && stylers && stylers.length) {
				var myStyle;
				if (typeof eType!='undefined' && eType != '') {
					myStyle = {featureType:fType,elementType:eType,stylers:stylers};
				} else {
					myStyle = {featureType:fType,stylers:stylers};
				}
				myStyles.push(myStyle);
			}
		});
		if (typeof myStyles != 'undefined' && myStyles) {
			try {
				jQuery.goMap.map.setOptions({styles: myStyles});
			}
			catch(err) {
				console.log(err.message);
			}
		}
	});
})
</script>
<?php
}
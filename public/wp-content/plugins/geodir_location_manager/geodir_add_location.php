<?php
global $wpdb;

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$hood_id = isset($_REQUEST['hood_id']) ? $_REQUEST['hood_id'] : '';

$location_result = $wpdb->get_row(
	$wpdb->prepare(
	"SELECT * FROM ".POST_LOCATION_TABLE." WHERE location_id = %d",
	array($id)
	)
);

$country_result = $wpdb->get_results("SELECT * FROM ".COUNTRIES_TABLE);

$hood_result = $wpdb->get_row(
	$wpdb->prepare(
	"SELECT * FROM ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_id = %d",
	array($hood_id)
	)
);



$prefix = 'gd_';

$lat = isset($location_result->city_latitude) ? $location_result->city_latitude : '';
$lng = isset($location_result->city_longitude) ? $location_result->city_longitude : '';
$city = isset($location_result->city) ? $location_result->city : '';
$region = isset($location_result->region) ? $location_result->region : '';
$country = isset($location_result->country) ? $location_result->country : '';

$map_title = GD_LOCATION_SET_MAP;

if(isset($_REQUEST['add_hood']))
{
	$map_title = GD_LOCATION_NEIGHBOURHOOD_SET_ON_MAP;
	
	if(!empty($hood_result)){
		$lat = $hood_result->hood_latitude;
		$lng = $hood_result->hood_longitude;
	}
}






?>

<div class="gd-content-heading">
<h3><?php echo GD_LOCATION_ADD_LOCATION; ?></h3>

<?php if(isset($_REQUEST['add_hood'])){ ?>
<input type="hidden" name="location_ajax_action" value="add_hood">
<input type="hidden" name="update_hood" value="<?php if(isset($hood_result->hood_id)){ echo $hood_result->hood_id;} ?>">

<?php }else{ ?>
<input type="hidden" name="location_ajax_action" value="location">
<?php } ?>


<?php
	$nonce = wp_create_nonce( 'location_add_edit_nonce' );
?>
<input type="hidden" name="location_addedit_nonce" value="<?php echo $nonce;?>" />

<input type="hidden" name="update_city" value="<?php if(isset($location_result->location_id)){echo $location_result->location_id;} ?>">


<table class="form-table geodir_add_location_form" id="gd_option_form">
	<tbody>
    	<?php if(isset($_REQUEST['add_hood'])){ ?>
    	<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php echo GD_HOOD_NAME;?><span style="display:inline; color:#FF0000;">*</span></th>
			<td class="forminp">
			 <div class="gtd-formfeild required">
					<input type="text"  size="80" style="width:440px" id="hood_name" name="hood_name" value="<?php if(isset($hood_result->hood_name)){ echo $hood_result->hood_name;} ?>" />
				 	<div class="gd-location_message_error"> <?php echo GD_LOCATION_FIELD_REQ;?></div>
			</div>
			<span class="description"></span>        
			</td>
		</tr>
    	<?php } ?>
        
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php echo GD_LOCATION_CITY;?><span style="display:inline; color:#FF0000;">*</span></th>
			<td class="forminp">
			 <div class="gtd-formfeild required">
					<input type="text"  size="80" style="width:440px" id="<?php echo $prefix;?>city" name="<?php echo $prefix;?>city" value="<?php if(isset($location_result->city)){ echo $location_result->city;} ?>" />
				 	<div class="gd-location_message_error"> <?php echo GD_LOCATION_FIELD_REQ;?></div>
			</div>
			<span class="description"></span>        
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php echo GD_LOCATION_REGION;?><span style="display:inline; color:#FF0000;">*</span></th>
			<td class="forminp">
			 <div class="gtd-formfeild required">
				<input type="text" id="<?php echo $prefix;?>region" size="80" style="width:440px" name="<?php echo $prefix;?>region" value="<?php if(isset($location_result->region)){ echo $location_result->region;} ?>" />
				 <div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>			</div>
			<span class="description"></span>        
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php echo GD_LOCATION_COUNTRY;?><span style="display:inline; color:#FF0000;">*</span></th>
			<td class="forminp">
			 <div class="gtd-formfeild required">

			 	<?php 
				$location_result_country = isset($location_result->country) ? $location_result->country : '';
				?>
                <select id="<?php echo $prefix ?>country" class="chosen_select"data-location_type="country" name="<?php echo $prefix ?>country"  data-placeholder="<?php _e('Choose a country.', GEODIRLOCATION_TEXTDOMAIN) ;?>" data-addsearchtermonnorecord="1" data-ajaxchosen="0" data-autoredirect="0" data-showeverywhere="0" >
                <?php
				geodir_get_country_dl($location_result_country,$prefix); 
				?> 
				 <div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
			<span class="description"></span>        
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row">&nbsp;</th>
			<td class="forminp">
			 <div class="gtd-formfeild">
			 	<?php 
																
				include( geodir_plugin_path() . "/geodirectory-functions/map-functions/map_on_add_listing_page.php");?>      
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row">
			<?php 
			if(isset($_REQUEST['add_hood'])){
				_e('Neighbourhood Latitude',GEODIRLOCATION_TEXTDOMAIN);
			}else{
				echo GD_LOCATION_LATITUDE;
			}
			?>
			
			<span style="display:inline; color:#FF0000;">*</span></th>
			<td class="forminp">
			 <div class="gtd-formfeild required">
				<input type="text" size="80" style="width:440px" id="<?php echo $prefix;?>latitude" name="<?php echo $prefix;?>latitude" value="<?php 
				
				if(isset($_REQUEST['add_hood'])){ if(isset($hood_result->hood_latitude)){echo $hood_result->hood_latitude;} }elseif(isset($location_result->city_latitude)){ echo $location_result->city_latitude; } ?>" />
 				<div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row">
			<?php 
			if(isset($_REQUEST['add_hood'])){
				_e('Neighbourhood Longitude',GEODIRLOCATION_TEXTDOMAIN);
			}else{
				echo GD_LOCATION_LONGITUDE;
			}
			?>
			
			<span style="display:inline; color:#FF0000;">*</span></th>
			<td class="forminp">
			 <div class="gtd-formfeild required">
				<input type="text"  size="80" style="width:440px" id="<?php echo $prefix;?>longitude" name="<?php echo $prefix;?>longitude" value="<?php if(isset($_REQUEST['add_hood'])){ if(isset($hood_result->hood_longitude)){echo $hood_result->hood_longitude;} }elseif(isset($location_result->city_longitude)){ echo $location_result->city_longitude; } ?>" />
 				<div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php echo GD_LOCATION_CITY_META;?></th>
			<td class="forminp">
			 <div class="gtd-formfeild">
				<textarea style="width:440px;" name="city_meta"><?php if(isset($location_result->city_meta)){ echo stripslashes_deep($location_result->city_meta);} ?></textarea>
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php echo GD_LOCATION_CITY_DESC;?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
				<textarea style="width:440px;" name="city_desc"><?php if(isset($location_result->city_desc)) {echo stripslashes_deep($location_result->city_desc);} ?></textarea>
			</td>
		</tr>
		
		<?php if(isset($_REQUEST['id']) && $_REQUEST['id'] != '' && !isset($_REQUEST['add_hood'])){ ?>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('Action For Listing',GEODIRLOCATION_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild" style="padding-top:10px;">
					<input style="display:none;" type="radio" name="listing_action" checked="checked" value="delete" /> 
					<label><?php _e('Post will be updated if both city and map marker position has been changed.',GEODIRLOCATION_TEXTDOMAIN);?></label> 
				</div>
			</td>
		</tr>
		<?php } ?>   
		
	</tbody>
</table>



<p class="submit" style="margin-top:10px; padding-left:12px;">
<input id="geodir_location_save" class="button-primary" type="submit" name="submit" value="<?php echo GD_LOCATION_SAVE;?>">
</p>
</div>

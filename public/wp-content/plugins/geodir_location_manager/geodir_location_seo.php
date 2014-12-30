<?php
global $wpdb, $table_prefix;

$table_prefix = $wpdb->prefix;

$get_countries = $wpdb->get_results("select * from ".POST_LOCATION_TABLE." GROUP BY country ORDER BY country ASC");
?>
<div class="gd-content-heading">
	<h3><?php _e('Geo Directory Manage SEO', GEODIRLOCATION_TEXTDOMAIN); ?></h3>
	<table id="geodir_location_seo_settings" style="width:100%" cellpadding="5" class="widefat post fixed">
		<thead>
			<tr>
				<th width="5">&nbsp;</th>
				<th colspan="2" width="210" align="left"><strong><?php _e('Location', GEODIRLOCATION_TEXTDOMAIN);?></strong></th>	
				<th width="280" align="left"><strong><?php _e('Meta Description', GEODIRLOCATION_TEXTDOMAIN);?></strong></th>
				<th width="280" align="left"><strong><?php _e('Location Description', GEODIRLOCATION_TEXTDOMAIN);?></strong></th>								
			</tr>
			<?php
			if ($get_countries) {
				foreach ($get_countries as $get_countriesobj) { 
					$nonce = wp_create_nonce( 'geodir_set_location_seo'. $get_countriesobj->country_slug);
					
					$title = $get_countriesobj->country;
					$desc = $get_countriesobj->country;
					
					$slug = $get_countriesobj->country_slug;
					$info = geodir_location_seo_by_slug($slug, 'country');
					if (!empty($info)) {
						$title = $info->seo_title!='' ? $info->seo_title : $title;
						$desc = $info->seo_desc!='' ? $info->seo_desc : $title;
					}
					if ($title=='') {
						$title = __( $get_countriesobj->country, GEODIRECTORY_TEXTDOMAIN );
					}
					?>
			<tr class="geodir_set_location_seo">
				<td><a class="show_div" style="margin-top:2px; margin-left:5px; float:left;width:16px;width:16px;" href="javascript:void(0);"><img src="<?php echo plugins_url('',__FILE__); ?>/images/plus-white-icon.png"/></a></td>
				<td colspan="2">
					<input type="hidden" name="wpnonce" value="<?php echo $nonce;?>" />
					<input type="hidden" class="location_slug" value="<?php echo $get_countriesobj->country_slug;?>" />
					<input type="hidden" class="location_type" value="country" />
					<?php echo __( $get_countriesobj->country, GEODIRECTORY_TEXTDOMAIN );?>
				</td>
				<td><textarea class="geodir_meta_keyword" cols="25"><?php echo stripslashes_deep($title); ?></textarea></td>
				<td><textarea class="geodir_meta_description" cols="25"><?php echo stripslashes_deep($desc); ?></textarea></td>
			</tr>
			<?php
					$get_states = $wpdb->get_results("select * from ".POST_LOCATION_TABLE." WHERE country_slug='".$get_countriesobj->country_slug."' GROUP BY region ORDER BY region ASC");
					
					if (!empty($get_states)) {
						foreach ($get_states as $get_statesobj) { 
							$nonce = wp_create_nonce( 'geodir_set_location_seo'. $get_statesobj->region_slug);
							
							$title = $get_statesobj->region;
							$desc = $get_statesobj->region;
							
							$slug = $get_statesobj->region_slug;
							$country_slug = $get_statesobj->country_slug;
							$info = geodir_location_seo_by_slug($slug, 'region', $country_slug);
							if (!empty($info)) {
								$title = $info->seo_title!='' ? $info->seo_title : $title;
								$desc = $info->seo_desc!='' ? $info->seo_desc : $title;
							}
							if ($title=='') {
								$title = $get_statesobj->region;
							}
							?>
			<tr class="geodir_set_location_seo_region geodir_location_seo<?php echo $get_countriesobj->country_slug;?>" style="display:none;">
				<td>&nbsp;</td>
				<td width="5"><a class="show_div" style="margin-top:2px; margin-left:5px; float:left;width:16px;" href="javascript:void(0);"><img src="<?php echo plugins_url('',__FILE__); ?>/images/plus-white-icon.png"/></a></td>
				<td>
					<input type="hidden" name="wpnonce" value="<?php echo $nonce;?>" />
					<input type="hidden" class="location_slug" value="<?php echo $get_statesobj->region_slug;?>" />
					<input type="hidden" class="country_slug" value="<?php echo $get_statesobj->country_slug; ?>" />
					<input type="hidden" class="location_type" value="region" />
					<?php echo $get_statesobj->region;?>
				</td>
				<td><textarea class="geodir_meta_keyword" cols="25"><?php echo stripslashes_deep($title); ?></textarea></td>
				<td><textarea class="geodir_meta_description" cols="25"><?php echo stripslashes_deep($desc); ?></textarea></td>
			</tr>
							<?php
							$get_cities = $wpdb->get_results("select * from ".POST_LOCATION_TABLE." WHERE region_slug='".$get_statesobj->region_slug."' GROUP BY city ORDER BY city ASC");
							if (!empty($get_cities)) { 
								foreach ($get_cities as $get_citiesobj) { 
									$nonce = wp_create_nonce( 'geodir_set_location_seo'. $get_citiesobj->city_slug);
									
									$title = $get_citiesobj->city_meta;
									$desc = $get_citiesobj->city_desc;
									if ($title=='') {
										$title = $get_citiesobj->city;
									}
									?>
			<tr class="geodir_set_location_seo_city geodir_location_seo<?php echo $get_statesobj->region_slug; ?> geodir_location_city<?php echo $get_countriesobj->country_slug;?>" style="display:none;">
				<td>&nbsp;</td>
				<td width="5">&nbsp;</td>
				<td>
					<input type="hidden" name="wpnonce" value="<?php echo $nonce;?>" />
					<input type="hidden" class="location_slug" value="<?php echo $get_citiesobj->city_slug;?>" />
					<input type="hidden" class="country_slug" value="<?php echo $get_statesobj->country_slug; ?>" />
					<input type="hidden" class="region_slug" value="<?php echo $get_statesobj->region_slug; ?>" />
					<input type="hidden" class="location_type" value="city" />
					<?php echo $get_citiesobj->city;?>
				</td>
				<td><textarea class="geodir_meta_keyword" cols="25"><?php echo stripslashes_deep($title); ?></textarea></td>
				<td><textarea class="geodir_meta_description" cols="25"><?php echo stripslashes_deep($desc); ?></textarea></td>
			</tr>
							<?php	
								}
							}
						}	
					}
				}
			}
			?>
		</thead>
	</table>
</div>
<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRLOCATION_TEXTDOMAIN ); ?>" />
			<input type="hidden" name="location_ajax_action" value="geodir_set_location_seo">
			</p>
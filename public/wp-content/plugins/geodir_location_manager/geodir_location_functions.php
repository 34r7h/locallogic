<?php

function geodir_get_location_by_id($location_result = array() , $id='')
{
	global $wpdb;
	if($id)
	{
		$get_result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM ".POST_LOCATION_TABLE." WHERE location_id = %d",
				array($id)
			)
		);
		if(!empty($get_result))
			$location_result = $get_result;
				
		}
		return $location_result;
}

	
function geodir_get_location_array( $args = null, $switcher = false ) {
	global $wpdb;
	$defaults = array(
					'what' => 'city',
					'city_val' => '', 
					'region_val' => '',
					'country_val' => '' ,
					'country_non_restricted' => '',
					'region_non_restricted' => '',
					'city_non_restricted' => '',
					'filter_by_non_restricted' => true, 
					'compare_operator' => 'like',
					'country_column_name' => 'country',
					'region_column_name' => 'region',
					'city_column_name' => 'city',
					'location_link_part' => true,
					'order_by' => 'asc',
					'no_of_records' => '',
					'spage' => '',
					'format' => array( 
									'type' => 'list',
									'container_wrapper' => 'ul',
									'container_wrapper_attr' => '',
									'item_wrapper' => 'li',
									'item_wrapper_attr' => ''
								)
				);
	
			
	$location_args = wp_parse_args( $args, $defaults );
	$search_query = ''; 
	$location_link_column = '';
	$location_default = geodir_get_default_location();
	
	if( $location_args['filter_by_non_restricted'] ) {
		// Non restricted countries
		if( $location_args['country_non_restricted'] == '' ) {
			if( get_option( 'geodir_enable_country' ) == 'default' ) {
				$country_non_retsricted = isset( $location_default->country ) ? $location_default->country : '';
				$location_args['country_non_restricted']  = $country_non_retsricted;
			} else if( get_option( 'geodir_enable_country' ) == 'selected' ) {
				$country_non_retsricted = get_option( 'geodir_selected_countries' );
				
				if( !empty( $country_non_retsricted ) && is_array( $country_non_retsricted ) ) { 	
					$country_non_retsricted = implode(',' , $country_non_retsricted );
				}
				
				$location_args['country_non_restricted'] = $country_non_retsricted;
			}
			
			$location_args['country_non_restricted'] = geodir_parse_location_list( $location_args['country_non_restricted'] );
		}
		
		//Non restricted Regions
		if( $location_args['region_non_restricted'] == '' ) {
			if( get_option( 'geodir_enable_region' ) == 'default' ) {
				$regoin_non_restricted= isset( $location_default->region ) ? $location_default->region : '';
				$location_args['region_non_restricted']  = $regoin_non_restricted;
			} else if( get_option( 'geodir_enable_region' ) == 'selected' ) {
				$regoin_non_restricted = get_option( 'geodir_selected_regions' );
				if( !empty( $regoin_non_restricted ) && is_array( $regoin_non_restricted ) ) { 	
					$regoin_non_restricted = implode( ',', $regoin_non_restricted );
				}
				
				$location_args['region_non_restricted']  = $regoin_non_restricted;
			}
			
			$location_args['region_non_restricted'] = geodir_parse_location_list( $location_args['region_non_restricted'] );
		}
		
		//Non restricted cities
		if( $location_args['city_non_restricted'] == '' ) {
			if( get_option('geodir_enable_city') == 'default' ) {
				$city_non_retsricted = isset( $location_default->city ) ? $location_default->city : '';
				$location_args['city_non_restricted']  = $city_non_retsricted;
			} else if( get_option( 'geodir_enable_city' ) == 'selected' ) {
				$city_non_restricted = get_option( 'geodir_selected_cities' );
				
				if( !empty( $city_non_restricted ) && is_array( $city_non_restricted ) ) { 	
					$city_non_restricted = implode( ',', $city_non_restricted );
				}
				
				$location_args['city_non_restricted']  = $city_non_restricted;
			}
			$location_args['city_non_restricted'] = geodir_parse_location_list( $location_args['city_non_restricted'] );
		}
	}	
	
	if( $location_args['what'] == '') {
		$location_args['what'] = 'city';
	}
		
	if( $location_args['location_link_part'] ) {
		switch( $location_args['what'] ) {
			case 'country':
				if ( get_option('permalink_structure') != '' ) {
					$location_link_column = ", CONCAT_WS('/', country_slug) AS location_link ";
				} else {
					$location_link_column = ", CONCAT_WS('&gd_country=', '', country_slug) AS location_link ";
				}
				break;
			case 'region':
				if ( get_option('permalink_structure') != '' ) {
					$location_link_column = ", CONCAT_WS('/', country_slug, region_slug) AS location_link ";
				} else {
					$location_link_column = ", CONCAT_WS('&', CONCAT('&gd_country=', country_slug), CONCAT('gd_region=', region_slug) ) AS location_link ";
				}
				break;
			case 'city':
				//if(get_option('geodir_show_location_url')=='all')
				{
					if ( get_option('permalink_structure') != '' ) {
						$location_link_column = ", CONCAT_WS('/', country_slug, region_slug, city_slug) AS location_link ";
					} else {
						$location_link_column = ", CONCAT_WS('&', CONCAT('&gd_country=', country_slug), CONCAT('gd_region=', region_slug) ,CONCAT('gd_city=' , city_slug)) AS location_link ";
					}
				}
				/*else
				{
					if ( get_option('permalink_structure') != '' )
						$location_link_column = " ,   city_slug as location_link ";
					else
						$location_link_column = " , CONCAT_WS('&gd_city=', '',city_slug) as location_link ";	
					
				}*/
				break;		
			/*default:
				if(get_option('geodir_show_location_url')=='all')
				{
					if ( get_option('permalink_structure') != '' )
						$location_link_column = " , CONCAT_WS('/', country_slug, region_slug, city_slug) as location_link ";
					else
						$location_link_column = " , CONCAT_WS('&', CONCAT('&gd_country=' ,country_slug) ,CONCAT('gd_region=' , region_slug) ,CONCAT('gd_city=' , city_slug)) as location_link ";
				}
				else
				{
					if ( get_option('permalink_structure') != '' )
						$location_link_column = " ,   city_slug as location_link ";
					else
						$location_link_column = " , CONCAT_WS('&gd_city=', '',city_slug) as location_link ";	
					
				}
				break;*/
		}
	}
	
	switch( $location_args['compare_operator'] ) {
		case 'like' :
			if( isset( $location_args['country_val'] ) && $location_args['country_val'] != '' ) {
				//$search_query .= " AND lower(".$location_args['country_column_name'].") like  '". mb_strtolower( $location_args['country_val'] )."%' ";
				$countries_search_sql = geodir_countries_search_sql( $location_args['country_val'] );
				$countries_search_sql = $countries_search_sql != '' ? " OR FIND_IN_SET(country, '" . $countries_search_sql . "')" : '';
				$translated_country_val = sanitize_title( trim( wp_unslash( $location_args['country_val'] ) ) );
				$search_query .= " AND ( lower(".$location_args['country_column_name'].") like  '". mb_strtolower( $location_args['country_val'] )."%' OR  lower(country_slug) LIKE '". $translated_country_val ."%' " . $countries_search_sql . " ) ";
			}
			
			if(isset($location_args['region_val']) &&  $location_args['region_val'] !='')
			{
				$search_query .= " AND lower(".$location_args['region_column_name'].") like  '". mb_strtolower($location_args['region_val'])."%' ";
			}
			
			if(isset($location_args['city_val']) && $location_args['city_val'] !='')
			{
				$search_query .= " AND lower(".$location_args['city_column_name'].") like  '". mb_strtolower($location_args['city_val'])."%' ";
			}
			break;
			
		case 'in' :
		
			if(isset($location_args['country_val'])  && $location_args['country_val'] !='')
			{
				$location_args['country_val'] = geodir_parse_location_list($location_args['country_val']) ;
				$search_query .= " AND lower(".$location_args['country_column_name'].") in($location_args[country_val]) ";
			}
			
			if(isset($location_args['region_val']) && $location_args['region_val'] !='' )
			{
				$location_args['region_val'] = geodir_parse_location_list($location_args['region_val']) ;
				$search_query .= " AND lower(".$location_args['region_column_name'].") in($location_args[region_val]) ";
			}
			
			if(isset($location_args['city_val'])  && $location_args['city_val'] !=''  )
			{
				$location_args['city_val'] = geodir_parse_location_list($location_args['city_val']) ;
				$search_query .= " AND lower(".$location_args['city_column_name'].") in($location_args[city_val]) ";
			}
			
			break;
		default :
			if(isset($location_args['country_val']) && $location_args['country_val'] !='' )
			{
				//$search_query .= " AND lower(".$location_args['country_column_name'].") =  '". mb_strtolower($location_args['country_val'])."' ";
				$countries_search_sql = geodir_countries_search_sql( $location_args['country_val'] );
				$countries_search_sql = $countries_search_sql != '' ? " OR FIND_IN_SET(country, '" . $countries_search_sql . "')" : '';
				$translated_country_val = sanitize_title( trim( wp_unslash( $location_args['country_val'] ) ) );
				$search_query .= " AND ( lower(".$location_args['country_column_name'].") =  '". mb_strtolower($location_args['country_val'])."' OR  lower(country_slug) LIKE '". $translated_country_val ."%' " . $countries_search_sql . " ) ";
			}
			
			if(isset($location_args['region_val']) && $location_args['region_val'] !='')
			{
				$search_query .= " AND lower(".$location_args['region_column_name'].") =  '". mb_strtolower($location_args['region_val'])."' ";
			}
			
			if(isset($location_args['city_val']) && $location_args['city_val'] !='' )
			{
				$search_query .= " AND lower(".$location_args['city_column_name'].") =  '". mb_strtolower($location_args['city_val'])."' ";
			}
			break ;
			
	} // end of switch 
	

	if($location_args['country_non_restricted'] != '') {
		$search_query .= " AND LOWER(country) IN ($location_args[country_non_restricted]) ";
	}
	
	if($location_args['region_non_restricted'] != '') {
		if( $location_args['what'] == 'region' || $location_args['what'] == 'city' ) {
			$search_query .= " AND LOWER(region) IN ($location_args[region_non_restricted]) ";
		}
	}
	
	if($location_args['city_non_restricted'] != '') {
		if($location_args['what'] == 'city' ) {
			$search_query .= " AND LOWER(city) IN ($location_args[city_non_restricted]) ";	
		}
	}
	
	
	//page
	if($location_args['no_of_records']){
	$spage = $location_args['no_of_records']*$location_args['spage'];
	}else{
	$spage = "0";
	}
	
	// limit	
	$limit = $location_args['no_of_records'] != '' ? ' LIMIT '.$spage.', ' . (int)$location_args['no_of_records'] . ' ' : '';
	
	// display all locations with same name also
	$search_field = $location_args['what'];
	if( $switcher ) {
		$select = $search_field . $location_link_column;
		$group_by = $search_field;
		$order_by = $search_field;
		if( $search_field == 'city' ) {
			$select .= ', country, region, city, country_slug, region_slug, city_slug';
			$group_by = 'country, region, city';
			$order_by = 'city, region, country';
		} else if( $search_field == 'region' ) {
			$select .= ', country, region, country_slug, region_slug';
			$group_by = 'country, region';
			$order_by = 'region, country';
		} else if( $search_field == 'country' ) {
			$select .= ', country, country_slug';
			$group_by = 'country';
			$order_by = 'country';
		}
		
		$main_location_query = "SELECT " . $select . " FROM " .POST_LOCATION_TABLE." WHERE 1=1 " . $search_query . " GROUP BY " . $group_by . " ORDER BY " . $order_by . " " . $location_args['order_by'] . " " . $limit;
	} else {	
		$main_location_query = "SELECT $location_args[what] $location_link_column FROM " .POST_LOCATION_TABLE." WHERE 1=1 " .  $search_query . " GROUP BY $location_args[what] ORDER BY $location_args[what] $location_args[order_by] $limit";
	}
	
	$locations = $wpdb->get_results( $main_location_query );

	if( $switcher && !empty( $locations ) ) {
		$new_locations = array();
		
		foreach( $locations as $location ) {
			//print_r($location);
			//echo '###'.$search_field;
			$new_location = $location;
			$label = $location->$search_field;
			if( ( $search_field == 'city' || $search_field == 'region' ) && (int)geodir_location_check_duplicate( $search_field, $label ) > 1 ) {
				
				if( $search_field == 'city' ) {
					$label .= ', ' . $location->region;
				} else if( $search_field == 'region' ) {
					$country_iso2 = geodir_location_get_iso2( $location->country );
					$country_iso2 = $country_iso2 != '' ? $country_iso2 : $location->country;
					$label .= $country_iso2 != '' ? ', ' . $country_iso2 : '';
				}
			}
			$new_location->title = $location->$search_field;
			$new_location->$search_field = $label;
			$new_location->label = $label;
			$new_locations[] = $new_location;
		}
		$locations = $new_locations;
	}
	
	$location_as_formated_list = "";
	if(!empty($location_args['format']))
	{
		if($location_args['format']['type']=='array')
			return $locations ;
		elseif($location_args['format']['type']=='jason')
			return json_encode($locations) ;
		else
		{
			$base_location_link = geodir_get_location_link('base');
			$container_wrapper = '' ; 
			$container_wrapper_attr = '' ;
			$item_wrapper = '' ;
			$item_wrapper_attr = '' ;
			
			if(isset($location_args['format']['container_wrapper']) && !empty($location_args['format']['container_wrapper']))
				$container_wrapper = $location_args['format']['container_wrapper'] ; 
			
			if(isset($location_args['format']['container_wrapper_attr']) && !empty($location_args['format']['container_wrapper_attr']))
				$container_wrapper_attr = $location_args['format']['container_wrapper_attr'] ; 
			
			if(isset($location_args['format']['item_wrapper']) && !empty($location_args['format']['item_wrapper']))
				$item_wrapper = $location_args['format']['item_wrapper'] ; 
			
			if(isset($location_args['format']['item_wrapper_attr']) && !empty($location_args['format']['item_wrapper_attr']))
				$item_wrapper_attr = $location_args['format']['item_wrapper_attr'] ; 
				
			
			if(!empty($container_wrapper))	
				$location_as_formated_list = "<" . $container_wrapper . " " . $container_wrapper_attr . " >";
			
			if(!empty($locations))
			{
				foreach($locations as $location)
				{
					if(!empty($item_wrapper))
						$location_as_formated_list .= "<" . $item_wrapper . " " . $item_wrapper_attr . " >";
					if(isset($location->location_link))
					{
						$location_as_formated_list .= "<a href='" . geodir_location_permalink_url( $base_location_link. $location->location_link ). "' ><i class='fa fa-caret-right'></i> ";
					}
					
					$location_as_formated_list .= $location->$location_args['what'] ;
					
					if(isset($location->location_link))
					{
						$location_as_formated_list .= "</a>";
					}
					
					if(!empty($item_wrapper))
						$location_as_formated_list .="</" . $item_wrapper . ">";
				}
			} 
			
			return $location_as_formated_list ;
		}
	}
	return $locations ;
}

function geodir_location_get_iso2( $country ) {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT ISO2 FROM " . GEODIR_COUNTRIES_TABLE . " WHERE Country LIKE %s", $country );
	$result = $wpdb->get_var( $sql );
	return $result;
}

function geodir_location_check_duplicate( $field, $location ) {
	global $wpdb;
	
	$sql = '';
	$result = 0;
	if( $field == 'city' ) {
		$sql = $wpdb->prepare( "SELECT COUNT(*) AS total FROM " . POST_LOCATION_TABLE . " WHERE " . $field . "=%s GROUP BY " . $field, $location, $location );
		$row = $wpdb->get_results( $sql );
		if( !empty( $row ) && isset( $row[0]->total ) ) {
			$result = (int)$row[0]->total;
		}
	} else if( $field == 'region' ) {
		$sql = $wpdb->prepare( "SELECT COUNT(*) AS total FROM " . POST_LOCATION_TABLE . " WHERE " . $field . "=%s GROUP BY country, " . $field, $location, $location );
		$row = $wpdb->get_results( $sql );
		if( !empty( $row ) && count( $row ) > 0 ) {
			$result = (int)count( $row );
		}
	}
	return $result;
}

function geodir_get_countries_array( $from = 'table' ) {
	global $wpdb;
	
	if( $from == 'table' ) {
		$countries = $wpdb->get_col( "SELECT Country FROM " . GEODIR_COUNTRIES_TABLE );
	} else {
		$countries = get_option( 'geodir_selected_countries' );
	}
	$countires_array = '' ;
	foreach( $countries as $key => $country ) {
		$countires_array[$country] = __( $country, GEODIRECTORY_TEXTDOMAIN ) ;	
	}	
	asort($countires_array);
	
	return $countires_array ;
}

function geodir_get_limited_country_dl( $selected_option ) {
	global $wpdb;
	
	$selected = ''; 
	$countries = geodir_get_countries_array( 'saved_option' );
	
	$out_put = '<option ' . $selected . ' value="">' . __( 'Select Country', GEODIRECTORY_TEXTDOMAIN ). '</option>'; 
	$countries_ISO2 = $wpdb->get_results( "SELECT Country, ISO2 FROM " . GEODIR_COUNTRIES_TABLE );
	
	foreach( $countries_ISO2 as $c2 ) {
		$ISO2[$c2->Country] = $c2->ISO2;
	}
	
	foreach( $countries as $country ) {
		$ccode = $ISO2[$country];
		$selected = '';
		if( $selected_option == $country ) {
			$selected = ' selected="selected" ';
		}
			
		$out_put .= '<option ' . $selected . ' value="' . $country . '" data-country_code="' . $ccode . '">' . __( $country, GEODIRECTORY_TEXTDOMAIN ) . '</option>';
    } 
	
	echo $out_put;
}

function geodir_get_limited_location_array($which='country' , $format='array') 
{
	$location_array = '' ;
	$locations = '' ;
	switch($which)
	{
		case 'country':
						$locations =	get_option('geodir_selected_countries');
						break;
		case 'region':
						$locations =	get_option('geodir_selected_regions');
						break;
		case 'city':
						$locations =	get_option('geodir_selected_cities');
						break;
	}
	
	if(!empty($locations) && is_array($locations))
	{
		foreach($locations as $location)
		$location_array[$location] = $location ;
	}
	
	if($format=='object')
		$location_array = (object)$location_array ;
		
	return $location_array ;
}

	
function geodir_location_form_submit_handler()
{
	if(isset($_REQUEST['geodir_location_merge']) && $_REQUEST['geodir_location_merge'] == 'merge')
	{
		include_once('geodir_merge_field.php');
		exit;
	}
	
	if(isset($_REQUEST['location_ajax_action']))
	{
		switch($_REQUEST['location_ajax_action']):
			case 'settings':
				
				geodir_update_options(geodir_location_default_options());
				
				$msg = GD_LOCATION_SETTINGS_SAVED;
				
				$msg = urlencode($msg);
				
				$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_setting&location_success=".$msg;
				wp_redirect($location);
				exit;
				
			break;
			case 'location':
				geodir_add_location();
			break;
			case 'add_hood':
				geodir_add_neighbourhood();
			break;
			case 'set_default':
				geodir_set_default();
			break;
			case 'merge':
				geodir_merge_location();
			break;
			case 'delete':
				geodir_delete_location();
			break;
			case 'delete_hood':
				geodir_delete_hood();
			break;
			case 'merge_cities':
				include_once('geodir_merge_field.php');
				exit();
			break;
			case 'set_region_on_map':
				geodir_get_region_on_map();
			break;
			case 'geodir_set_location_seo':
				geodir_get_location_seo_settings();
			break;
			case 'geodir_save_cat_location':
				geodir_save_cat_location();
			break;
			case 'geodir_change_cat_location':
				geodir_change_cat_location();
			break;
			
			
		endswitch;
	}
}



function geodir_get_location_seo_settings()
{	
	global $wpdb;
	
	if(isset($_REQUEST['wpnonce']) && current_user_can( 'manage_options' ) && isset($_REQUEST['location_slug'])) {
		
		if ( !wp_verify_nonce( $_REQUEST['wpnonce'], 'geodir_set_location_seo'.$_REQUEST['location_slug'] ) ) {
			echo 'FAIL';
			exit;
		}
		
		$field = isset($_REQUEST['field']) && ($_REQUEST['field']=='geodir_meta_keyword' || $_REQUEST['field']=='geodir_meta_description') ? $_REQUEST['field'] : '';
		$seo_value = isset($_REQUEST['field_val']) ? trim($_REQUEST['field_val']) : '';
		
		if ($field=='' || $seo_value=='') {
			echo 'FAIL';
			exit;
		}
		$seo_field = $_REQUEST['field']=='geodir_meta_keyword' ? 'seo_title' : 'seo_desc';
		
		$location_type = isset($_REQUEST['location_type']) ? $_REQUEST['location_type'] : '';
		$country_slug = isset($_REQUEST['country_slug']) ? $_REQUEST['country_slug'] : '';
		$region_slug = isset($_REQUEST['region_slug']) ? $_REQUEST['region_slug'] : '';
		$location_slug = isset($_REQUEST['location_slug']) ? $_REQUEST['location_slug'] : '';
		
		if ($seo_field=='seo_title') {
			$seo_value = substr($seo_value, 0, 140);
		} else {
			$seo_value = substr($seo_value, 0, 100000);
		}
		
		$seo_info = geodir_location_seo_by_slug($location_slug, $location_type, $country_slug, $region_slug);
		
		$date_now = date('Y-m-d H:i:s');
		
		switch($location_type) {
			case 'country': {
				if (!empty($seo_info)) {
					$sql = $wpdb->prepare("UPDATE ".LOCATION_SEO_TABLE." SET ".$seo_field."=%s, date_updated=%s WHERE seo_id=%d", array($seo_value, $date_now, $seo_info->seo_id));
				} else {
					$sql = $wpdb->prepare("INSERT INTO ".LOCATION_SEO_TABLE." SET location_type=%s, country_slug=%s, ".$seo_field."=%s, date_created=%s", array($location_type, $location_slug, $seo_value, $date_now));
				}
				if ($wpdb->query($sql)) {
					echo 'OK';
					exit;
				}
			}
			break;
			case 'region': {
				if (!empty($seo_info)) {
					$sql = $wpdb->prepare("UPDATE ".LOCATION_SEO_TABLE." SET country_slug=%s, ".$seo_field."=%s, date_updated=%s WHERE seo_id=%d", array($country_slug, $seo_value, $date_now, $seo_info->seo_id));
				} else {
					$sql = $wpdb->prepare("INSERT INTO ".LOCATION_SEO_TABLE." SET location_type=%s, country_slug=%s, region_slug=%s, ".$seo_field."=%s, date_created=%s", array($location_type, $country_slug, $location_slug, $seo_value, $date_now));
				}
				if ($wpdb->query($sql)) {
					echo 'OK';
					exit;
				}
			}
			break;
			case 'city': {
				if (!empty($seo_info)) {
					$sql = $wpdb->prepare("UPDATE ".LOCATION_SEO_TABLE." SET country_slug=%s, region_slug=%s, ".$seo_field."=%s, date_updated=%s WHERE seo_id=%d", array($country_slug, $region_slug, $seo_value, $date_now, $seo_info->seo_id));
				} else {
					$sql = $wpdb->prepare("INSERT INTO ".LOCATION_SEO_TABLE." SET location_type=%s, country_slug=%s, region_slug=%s, city_slug=%s, ".$seo_field."=%s, date_created=%s", array($location_type, $country_slug, $region_slug, $location_slug, $seo_value, $date_now));
				}
				if ($wpdb->query($sql)) {
					$info = geodir_city_info_by_slug($location_slug, $country_slug, $region_slug);
					if (!empty($info)) {
						$location_field = $seo_field=='seo_title' ? 'city_meta' : 'city_desc';
						$sql = $wpdb->prepare("UPDATE ".POST_LOCATION_TABLE." SET ".$location_field."=%s WHERE location_id=%d", array($seo_value, $info->location_id));
						$wpdb->query($sql);
					}
					echo 'OK';
					exit;
				}
			}
			break;
		}
	}
				
			$msg = urlencode( __('Location SEO updated successfully.',GEODIRLOCATION_TEXTDOMAIN) );
			
			$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_seo&location_success=".$msg;
			wp_redirect($location);
			exit;
}

function geodir_location_seo_by_slug($slug, $location_type='city', $country_slug='', $region_slug='')
{
	global $wpdb;
	if ($slug=='') {
		return NULL;
	}
	
	$whereField = '1';
	$whereVal = array();
	
	switch($location_type) {
		case 'country': {
			$whereField .= ' AND location_type=%s AND country_slug=%s';
			$whereVal[] = $location_type;
			$whereVal[] = $slug;
		}
		break;
		case 'region': {
			$whereField .= ' AND location_type=%s AND region_slug=%s';
			$whereVal[] = $location_type;
			$whereVal[] = $slug;
			if ($country_slug!='') {
				$whereField .= ' AND country_slug=%s';
				$whereVal[] = $country_slug;
			}
		}
		break;
		case 'city': {
			$whereField .= ' AND location_type=%s AND city_slug=%s';
			$whereVal[] = $location_type;
			$whereVal[] = $slug;
			if ($country_slug!='') {
				$whereField .= ' AND country_slug=%s';
				$whereVal[] = $country_slug;
			}
			if ($region_slug!='') {
				$whereField .= ' AND region_slug=%s';
				$whereVal[] = $region_slug;
			}
		}
		break;
	}
	if (empty($whereVal)) {
		return NULL;
	}
	
	$sql = $wpdb->prepare( "SELECT seo_id, seo_title, seo_desc FROM ".LOCATION_SEO_TABLE." WHERE ".$whereField." ORDER BY seo_id LIMIT 1", $whereVal );
	
	$row = $wpdb->get_row($sql);
	if (is_object($row)) {
		return $row;
	}
	return NULL;
}

function geodir_city_info_by_slug($slug, $country_slug='', $region_slug='')
{
	global $wpdb;
	
	if ($slug=='') {
		return NULL;
	}
	
	$whereVal = array();
	$whereField = 'city_slug=%s';
	$whereVal[] = $slug;
	
	if ($country_slug!='') {
		$whereField .= ' AND country_slug=%s';
		$whereVal[] = $country_slug;
	}
	if ($region_slug!='') {
		$whereField .= ' AND region_slug=%s';
		$whereVal[] = $region_slug;
	}
	
	$row = $wpdb->get_row(
		$wpdb->prepare( "SELECT location_id, country_slug, region_slug, city_slug, country, region, city, city_meta, city_desc FROM ".POST_LOCATION_TABLE." WHERE ".$whereField." ORDER BY location_id LIMIT 1", $whereVal )
	);
	if (is_object($row)) {
		return $row;
	}
	return NULL;
}

function geodir_get_region_on_map()
{
	
	global $wpdb;
	
	if(isset($_REQUEST['country']) && $_REQUEST['country'] != '' && isset($_REQUEST['city']) && $_REQUEST['city'] != ''){
			
		$region = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT region FROM ".POST_LOCATION_TABLE." WHERE country=%s AND city=%s",
				array($_REQUEST['country'],$_REQUEST['city'])
			)
		);
		
		if(!$region)
			$region = $_REQUEST['state'];
		
		echo $region;
	}
	exit;
}





function geodir_add_neighbourhood()
{

	global $wpdb,$plugin_prefix;
	
	if(isset($_REQUEST['location_addedit_nonce']) && current_user_can( 'manage_options' )){
		
		if ( !wp_verify_nonce( $_REQUEST['location_addedit_nonce'], 'location_add_edit_nonce' ) )
		return;
		
		$hood_name = $_REQUEST['hood_name'];
		$gd_latitude = $_REQUEST['gd_latitude'];
		$gd_longitude = $_REQUEST['gd_longitude'];
		$city_id = $_REQUEST['update_city'];
		$hood_id = $_REQUEST['update_hood'];
		$hood_slug = create_location_slug($hood_name);
		
		$countslug = $wpdb->get_var(
			$wpdb->prepare(
			"select COUNT(hood_id) AS total from ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_slug LIKE %d",
			array($hood_slug.'%')
			)
		);
		
		if($countslug!='0'){
			$number = $countslug+1;
			$hood_slug = $hood_slug.'-'.$number;
		}
		
		if($hood_id)
		{
			$duplicate = $wpdb->get_var(
				$wpdb->prepare(
					"select hood_id from ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_location_id = %d AND hood_name=%s AND hood_id!=%d",
					array($city_id,$hood_name,$hood_id)
				)
			); 
			
		}
		else
		{
			
			$duplicate = $wpdb->get_var(
				$wpdb->prepare(
				"select hood_id from ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_location_id = %d AND hood_name=%s",
				array($city_id,$hood_name)
				)
			); 
		
		}
		
		if($duplicate!='')
		{
			$setid = '';
			if($hood_id){ 
			
				$setid = '&hood_id='.$hood_id; 		
			
			}
			
			$msg = GD_NEIGHBOURHOOD_EXITS;
				
			$msg = urlencode($msg);
			
			$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit&add_hood=true&location_error=".$msg."&id=".$city_id.$setid;
			wp_redirect($location);
			exit;
		}
		
		if($_POST['location_ajax_action'] == 'add_hood')
		{
			
			
			if($hood_id)
			{
				$sql = $wpdb->prepare("UPDATE ".POST_NEIGHBOURHOOD_TABLE." SET
				hood_location_id=%d, 
				hood_name=%s,
				hood_latitude=%s,
				hood_longitude=%s,
				hood_slug=%s
				WHERE hood_id = %d",
				array($city_id,$hood_name,$gd_latitude,$gd_longitude,$hood_slug,$hood_id));
				
			$location_hood = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT l.city, n.hood_slug FROM ".POST_LOCATION_TABLE." l, ".POST_NEIGHBOURHOOD_TABLE." n WHERE n.hood_location_id=l.location_id AND hood_id=%d",
					array($hood_id)
				)
			);
			
			$geodir_posttypes = geodir_get_posttypes();
			
			foreach($geodir_posttypes as $geodir_posttype){
				
				$table = $plugin_prefix . $geodir_posttype . '_detail';
				
				if($wpdb->get_var("SHOW COLUMNS FROM ".$table." WHERE field = 'post_neighbourhood'"))
				{
					if(!empty($location_hood)){
						foreach($location_hood as $hood_del){
							
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE ".$table." SET post_neighbourhood=%s WHERE post_city=%s AND post_neighbourhood=%s",
									array($hood_slug,$hood_del->city,$hood_del->hood_slug)
								)
							);
							
						}
					}
					
				}
		 }
				
				$msg = MSG_NEIGHBOURHOOD_UPDATED;	
				
			}
			else
			{
				$sql = $wpdb->prepare("INSERT INTO ".POST_NEIGHBOURHOOD_TABLE." SET
				hood_location_id=%d,
				hood_name=%s,
				hood_slug=%s,
				hood_latitude=%s,
				hood_longitude=%s",
				array($city_id,$hood_name,$hood_slug,$gd_latitude,$gd_longitude));
				
				$msg = MSG_NEIGHBOURHOOD_ADDED;
		
			}
		
			$wpdb->query($sql);
			
			$msg = urlencode($msg);
			
			$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&location_success=".$msg."&city_hood=hoodlist&id=".$city_id;
			wp_redirect($location);
			exit;
		}
	
	}else{		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	}
}

function geodir_get_neighbourhoods_dl($city='', $selected_id='', $echo = true)
{	
	global $wpdb;
	
	
	$neighbourhoods = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM ".POST_NEIGHBOURHOOD_TABLE." hood, ".POST_LOCATION_TABLE." location WHERE hood.hood_location_id = location.location_id AND location.city=%s ORDER BY hood_name ",
			array($city)
		)
	);
	
	$selectoptions = '';
	if(!empty($neighbourhoods)){
		$selectoptions = '<option value="">'._('Select Neighbourhood').'</option>';
		
		foreach($neighbourhoods as $neighbourhood)	
		{
			$selected = '';
			if($neighbourhood->hood_slug == $selected_id)
				$selected = ' selected="selected" ';
				
			$selectoptions.= '<option value="'.$neighbourhood->hood_slug.'" '.$selected.'>'.$neighbourhood->hood_name.'</option>';
		
		}
	}	
	
	if($echo)
		echo $selectoptions;
	else	
		return $selectoptions;
}


function geodir_add_location()
{	
	global $wpdb,$plugin_prefix;
	
	if(isset($_REQUEST['location_addedit_nonce']) && current_user_can( 'manage_options' )){
		
		if ( !wp_verify_nonce( $_REQUEST['location_addedit_nonce'], 'location_add_edit_nonce' ) )
		return;
		
		$gd_city = $_REQUEST['gd_city'];
		$gd_region = $_REQUEST['gd_region'];
		$gd_country = $_REQUEST['gd_country'];
		$gd_latitude = $_REQUEST['gd_latitude'];
		$gd_longitude = $_REQUEST['gd_longitude'];
		$city_meta = $_REQUEST['city_meta'];
		$city_desc = $_REQUEST['city_desc'];
		
		$id = $_REQUEST['update_city'];
		
		if($id)
		{
			$duplicate = $wpdb->get_var(
				$wpdb->prepare(
					"select location_id from ".POST_LOCATION_TABLE." WHERE city = %s AND region=%s AND country=%s AND location_id!=%d",
					array($gd_city,$gd_region,$gd_country,$id)
				)
			); 
			
		}
		else
		{
			
			$duplicate = $wpdb->get_var(
				$wpdb->prepare(
					"select location_id from ".POST_LOCATION_TABLE." WHERE city = %s AND region=%s AND country=%s",
					array($gd_city,$gd_region,$gd_country)
				)
			); 
		
		}
		
		if($duplicate!='')
		{
			$setid = '';
			if($id){ $setid = '&id='.$id; }
			
			$msg = GD_LOCATION_EXITS;
				
			$msg = urlencode($msg);
			
			$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit&location_error=".$msg.$setid;
			wp_redirect($location);
			exit;
		}
		
		if($_POST['location_ajax_action'] == 'location')
		{
		
			$country_slug = create_location_slug($gd_country);
			$region_slug = create_location_slug($gd_region);
			$city_slug = create_location_slug($gd_city);
			
			if($id)
			{
				$old_location = geodir_get_location_by_id('' , $id);
				
				$sql = $wpdb->prepare("UPDATE ".POST_LOCATION_TABLE." SET
					country=%s, 
					region=%s,
					city=%s,
					city_latitude=%s,
					city_longitude=%s,
					country_slug = %s,
					region_slug = %s,
					city_slug = %s,
					city_meta=%s,
					city_desc=%s WHERE location_id = %d",
					array($gd_country,$gd_region,$gd_city,$gd_latitude,$gd_longitude,$country_slug,$region_slug,$city_slug,$city_meta,$city_desc,$id)
					
				);
				
				$wpdb->query($sql);
				
				$geodir_location = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".POST_LOCATION_TABLE." WHERE is_default='1' AND location_id = %d",array($id)), "OBJECT" );
				
				if(!empty($geodir_location))
					update_option('geodir_default_location', $geodir_location); // UPDATE DEFAULT LOCATION OPTION
				
				$msg = GD_LOCATION_UPDATED;
				
				//UPDATE AND DELETE LISTING
				$posttype = geodir_get_posttypes(); 
				if (isset($_REQUEST['listing_action']) && $_REQUEST['listing_action'] == 'delete') {
				
					foreach ($posttype as $posttypeobj) {
						
						/* do not update latitude and longitude otrherwise all listings will be spotted on one point on map
						if ($old_location->city_latitude != $gd_latitude || $old_location->city_longitude != $gd_longitude) {
							
							$del_post_sql = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT post_id from ".$plugin_prefix.$posttypeobj."_detail WHERE post_location_id = %d AND (post_city != %s OR post_region != %s)",
									array($id,$gd_city,$gd_region)
								)
							);
							if (!empty($del_post_sql)) {
								foreach ($del_post_sql as $del_post_info) {
									$postid = (int)$del_post_info->post_id;
									//wp_delete_post($postid); // update post location instead of delete post
									$sql = $wpdb->prepare(
										"UPDATE ".$plugin_prefix.$posttypeobj."_detail SET post_latitude=%s, post_longitude=%s WHERE post_location_id=%d AND post_id=%d", 
										array( $gd_latitude, $gd_longitude, $id, $postid )
									);
									$wpdb->query($sql);
								}
							}
						}
						*/
						
						$post_locations =  '['.$city_slug.'],['.$region_slug.'],['.$country_slug.']'; // set all overall post location
						
						$sql = $wpdb->prepare(
								"UPDATE ".$plugin_prefix.$posttypeobj."_detail SET post_city=%s, post_region=%s, post_country=%s, post_locations=%s
								WHERE post_location_id=%d AND ( post_city!=%s OR post_region!=%s OR post_country!=%s)", 
								array($gd_city,$gd_region,$gd_country,$post_locations,$id,$gd_city,$gd_region,$gd_country)
							);
						$wpdb->query($sql);
					}
				}
				
			}
			else
			{
				
				$location_info = array();
				$location_info['city'] = $gd_city;
				$location_info['region'] = $gd_region;
				$location_info['country'] = $gd_country;
				$location_info['country_slug'] = $country_slug;
				$location_info['region_slug'] = $region_slug;
				$location_info['city_slug'] = $city_slug;
				$location_info['city_latitude'] = $gd_latitude;
				$location_info['city_longitude'] = $gd_longitude;
				$location_info['is_default'] = 0;
				$location_info['city_meta'] = $city_meta;
				$location_info['city_desc'] = $city_desc;
				
				geodir_add_new_location_via_adon($location_info);
				
				$msg = GD_LOCATION_SAVED;
				
			}
			
			$msg = urlencode($msg);
			
			$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&location_success=".$msg;
			wp_redirect($location);
			exit;
		}
		
	}else{		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	}
	
}

function geodir_neighbourhood_delete($hood_id)
{
	
	global $wpdb,$plugin_prefix;
	
	$location_hood = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT l.city, n.hood_slug FROM ".POST_LOCATION_TABLE." l, ".POST_NEIGHBOURHOOD_TABLE." n WHERE n.hood_location_id=l.location_id AND hood_id=%d",
			array($hood_id)
		)
	);
	
	$geodir_posttypes = geodir_get_posttypes();
	
	foreach($geodir_posttypes as $geodir_posttype){
		
		$table = $plugin_prefix . $geodir_posttype . '_detail';
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".$table." WHERE field = 'post_neighbourhood'"))
		{
			if(!empty($location_hood)){
				foreach($location_hood as $hood_del){
					
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE ".$table." SET post_neighbourhood='' WHERE post_city=%s AND post_neighbourhood=%s",
							array($hood_del->city,$hood_del->hood_slug)
						)
					);
					
				}
			}
			
		}
 }
 
 $wpdb->query($wpdb->prepare("DELETE FROM ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_id=%d",array($hood_id)));

}

function geodir_merge_location()
{	
	
	global $wpdb,$plugin_prefix;
	if(isset($_REQUEST['location_merge_nonce']) && current_user_can( 'manage_options' )){
		
		if ( !wp_verify_nonce( $_REQUEST['location_merge_nonce'], 'location_merge_wpnonce' ) )
		exit;
	
		$geodir_location_merge_ids = trim($_REQUEST['geodir_location_merge_ids'], ',');
		
		$gd_merge = $_REQUEST['gd_merge'];
		
		$gd_city = $_REQUEST['gd_city'];
		
		$gd_region = $_REQUEST['gd_region'];
		
		$gd_country = $_REQUEST['gd_country'];
		
		$gd_lat = $_REQUEST['gd_lat'];
		
		$gd_log = $_REQUEST['gd_log'];
		
		$geodir_postlocation_merge_ids = array();
		
		$geodir_merge_ids_array = explode(',',$geodir_location_merge_ids);
		
		$geodir_merge_ids_length = count($geodir_merge_ids_array);
		$format = array_fill(0, $geodir_merge_ids_length, '%d');
		$format = implode(',', $format);
		
		$geodir_postlocation_merge_ids = $geodir_merge_ids_array;
		$geodir_postlocation_merge_ids[] = $gd_merge;
		
		$gd_location_sql = $wpdb->prepare("select * from ".POST_LOCATION_TABLE." WHERE location_id IN ($format) AND location_id!=%d", $geodir_postlocation_merge_ids );
		
		 $gd_locationinfo = $wpdb->get_results($gd_location_sql);
		 
		 $check_default = '';
		 foreach($gd_locationinfo as $gd_locationinfo_obj)
		 {
			
			$locationid = $gd_locationinfo_obj->location_id;
			
			if(!$check_default){
			
				$check_default = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT location_id FROM ".POST_LOCATION_TABLE." WHERE is_default='1' AND location_id = %d",
						array($locationid)
					)
				);
				
			}
			
			
			/*$location_hood = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT hood_id FROM ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_location_id=%d",
					array($locationid)
				)
			);
			
			if(!empty($location_hood)){
				foreach($location_hood as $hood_del){
				
					geodir_neighbourhood_delete($hood_del->hood_id);
					
				}
			}*/
				 
			
			$gd_location_del = $wpdb->prepare("DELETE FROM ".POST_LOCATION_TABLE." WHERE  location_id = %d",array($locationid));
			
			$wpdb->query($gd_location_del);
				
		 }
		 
		 
		 	$country_slug = create_location_slug($gd_country);
			$region_slug = create_location_slug($gd_region);
			$city_slug = create_location_slug($gd_city);
		
		 //FILL SELECTED CITY IN MERGE LOCATIONS POST
		 $geodir_posttypes = geodir_get_posttypes();
		 
		 foreach($geodir_posttypes as $geodir_posttype){
				
			 $table = $plugin_prefix . $geodir_posttype . '_detail';
				
			 $gd_placedetail_sql = $wpdb->prepare(
			 					"select * from ". $table." WHERE post_location_id IN ($format)",
								$geodir_merge_ids_array
								);
			 
			 $gd_placedetailinfo = $wpdb->get_results($gd_placedetail_sql);
			 
			 foreach($gd_placedetailinfo as $gd_placedetailinfo_obj)
			 {
				$postid = $gd_placedetailinfo_obj->post_id;
			 	
				$post_locations =  '['.$city_slug.'],['.$region_slug.'],['.$country_slug.']'; // set all overall post location
				
				 $gd_rep_locationid = $wpdb->prepare("UPDATE ". $table." SET 
										post_location_id=%d,
										post_city	= %s,
										post_region	= %s,
										post_country	= %s,
										post_locations = %s
										WHERE  post_id = %d",
										array($gd_merge,$gd_city,$gd_region,$gd_country,$post_locations,$postid));
				
				$wpdb->query($gd_rep_locationid);
				
			 }
	
		 }
		 
		
		$setdefault = '';
		if(isset($check_default) && $check_default!='')
		{
			$setdefault = ", is_default='1'";	
			
		}
		
		//UPDATE SELECTED LOCATION
		
		$sql = $wpdb->prepare("UPDATE ".POST_LOCATION_TABLE." SET 
				country=%s, 
				region=%s, 
				city=%s, 
				city_latitude=%s, 
				city_longitude=%s, 
				country_slug = %s,
				region_slug = %s,
				city_slug = %s
				".$setdefault." 
				WHERE location_id = %d",
				array($gd_country,$gd_region,$gd_city,$gd_lat,$gd_log,$country_slug,$region_slug,$city_slug,$gd_merge));
			
		$wpdb->query($sql);
		
		if($setdefault != '')
			geodir_location_set_default($gd_merge);
		
		/* ----- update hooks table ---- */
		
		$location_hood_info = $wpdb->query(
			$wpdb->prepare(
				"UPDATE ".POST_NEIGHBOURHOOD_TABLE." SET hood_location_id=".$gd_merge." WHERE hood_location_id IN ($format)",
				$geodir_merge_ids_array
			)
		);
			
			
			$msg = MSG_LOCATION_MERGE_SUCCESS;
			$msg = urlencode($msg);
			
		 $location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&location_success=".$msg;
		
		 wp_redirect($location);
		
		 exit;
		 
		}else{		
			wp_redirect(home_url().'/?geodir_signup=true');
			exit();
		}
}


function geodir_location_set_default($locationid)
{

	global $wpdb;
	
	$wpdb->query("update ".POST_LOCATION_TABLE." set is_default='0'");
		
	$gd_location_default = $wpdb->prepare("UPDATE ".POST_LOCATION_TABLE." SET 
							is_default='1' 
							WHERE  location_id = %d", array($locationid) );
	
	$wpdb->query($gd_location_default);
	
	$geodir_location = $wpdb->get_row("SELECT * FROM ".POST_LOCATION_TABLE." WHERE is_default='1'", "OBJECT" );
	
	update_option('geodir_default_location', $geodir_location); // UPDATE DEFAULT LOCATION OPTION
	
}

function geodir_set_default()
{
	global $wpdb;
	
	if(isset($_REQUEST['_wpnonce']) && isset($_REQUEST['id']) && current_user_can( 'manage_options' )){
	
		$locationid = $_REQUEST['id'];
		
		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'location_action_'.$_REQUEST['id'] ) )
				return;
		
		geodir_location_set_default($locationid);
		
		$msg = MSG_LOCATION_SET_DEFAULT;
		$msg = urlencode($msg);
		
		$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&location_success=".$msg;
		
		wp_redirect($location);
		
		exit;
		
	}else{		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	}
	
}




function geodir_delete_location()
{
	global $wpdb,$plugin_prefix;
	
	
	if(isset($_REQUEST['_wpnonce']) && isset($_REQUEST['id']) && current_user_can( 'manage_options' )){
		
		$locationid = $_REQUEST['id'];
		
		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'location_action_'.$_REQUEST['id'] ) )
				return;
		
		$geodir_posttypes = geodir_get_posttypes();
		
		foreach($geodir_posttypes as $geodir_posttype){
		
			$table = $plugin_prefix . $geodir_posttype . '_detail';
			
			$gd_placedetail_sql = $wpdb->prepare("select * from ".$table." WHERE post_location_id = %d",array($locationid));
			 
			$gd_placedetailinfo = $wpdb->get_results($gd_placedetail_sql);
			
			foreach($gd_placedetailinfo as $gd_placedetailinfo_obj)
			{
				$postid = $gd_placedetailinfo_obj->post_id;
			 
			 	wp_delete_post($postid);
			}
			
		}
		
		
		$gd_hood_del = $wpdb->prepare("DELETE FROM ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_location_id = %d",array($locationid));
			
		$wpdb->query($gd_hood_del);
		
		$delete_city = $wpdb->get_var($wpdb->prepare("select city_slug from ".POST_LOCATION_TABLE." where location_id = %d ",array($locationid)));
		
		$gd_location_del = $wpdb->prepare("DELETE FROM ".POST_LOCATION_TABLE." WHERE location_id = %d",array($locationid));
		
		if(isset($_SESSION['gd_city']) && $delete_city ==  $_SESSION['gd_city']){
			unset(	$_SESSION['gd_multi_location'],
				$_SESSION['gd_city'],
				$_SESSION['gd_region'],
				$_SESSION['gd_country'] );
		}
		
		$wpdb->query($gd_location_del);
		
		$msg = MSG_LOCATION_DELETED;
		$msg = urlencode($msg);
			
		$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&location_success=".$msg;
		
		wp_redirect($location);
		
		exit;
	}else{		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	}
}

//DELETE NEIGHBOURHOOD FUNCTION 
function geodir_delete_hood()
{
	global $wpdb;
	
	if(isset($_REQUEST['_wpnonce']) && isset($_REQUEST['id']) && isset($_REQUEST['city_id']) && current_user_can( 'manage_options' )){
	
	if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'neighbourhood_delete_'.$_REQUEST['id'] ) )
				return;
	
	$hoodid = $_REQUEST['id'];
	$city_id = $_REQUEST['city_id'];
	
	if($hoodid)
	{
		
		geodir_neighbourhood_delete($hoodid);
		
		$msg = MSG_NEIGHBOURHOOD_DELETED;
		$msg = urlencode($msg);
		
		$location = admin_url()."admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&location_success=".$msg."&city_hood=hoodlist&id=".$city_id;
		wp_redirect($location);
		
		exit;
	}
	
	}else{		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	}

}

function geodir_get_neighbourhoods($location = '')
{
	
	global $wpdb;
	
	$neighbourhoods = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_location_id = %d ORDER BY hood_name ", array($location)));	
	
	return (!empty($neighbourhoods)) ?  $neighbourhoods : false;
		
}	


function geodir_location_default_options($arr=array())
{
	
			
	$country_array= geodir_get_countries_array();
	
	$args=array(
					'what'=>'region' , 
					'echo' => false,
					'filter_by_non_restricted'=>false,
					'format'=> array('type'=>'array')
				);
			
	$region_obj= (array)geodir_get_location_array($args);
	$region_array = '' ;
	foreach( $region_obj as $region)
	{
		$region_array[$region->region] = $region->region ;
	}
	
	
	$args=array(
							'what'=>'city' , 
							'echo' => false,
							'filter_by_non_restricted'=>false,
							'format'=> array('type'=>'array')
						);
			
	$city_obj= (array)geodir_get_location_array($args);
	$city_array = '' ;
	foreach( $city_obj as $city)
	{
		$city_array[$city->city] = $city->city ;
	}
	
	$arr[] = array( 'name' => __( 'Location Settings', GEODIRLOCATION_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'location_setting_options' );
	
	$arr[] = array( 'name' => __( 'Main Navigation Settings', GEODIRLOCATION_TEXTDOMAIN), 'type' => 'sectionstart', 'id' => 'location_setting_switcher_options');
	
	$arr[] = array(  
		'name' => __( 'Show location switcher in menu', GEODIRLOCATION_TEXTDOMAIN ),
		'desc' 		=> sprintf(__( 'Show change location navigation in main menu? (untick to disable) If you disable this option, none of the change location link will appear in main navigation.', GEODIRLOCATION_TEXTDOMAIN )),
		'id' 		=> 'geodir_show_changelocation_nave',
		'std' 		=> '',
		'type' 		=> 'checkbox',
		'value' => '1',
	);
	
	
	$arr[] = array(  
		'name' => 	'',
		'desc' 		=> __( 'List drilled-down Regions, Cities.', GEODIRLOCATION_TEXTDOMAIN ),
		'id' 		=> 'geodir_location_switcher_list_mode',
		'std' 		=> '',
		'type' 		=> 'radio',
		'value'		=> 'drill',
		'radiogroup'		=> 'start'
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'List all Countries, Regions, Cities.', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_location_switcher_list_mode',
			'std' 		=> '',
			'type' 		=> 'radio',
			'value'		=> 'all',
			'radiogroup'		=> 'end'
		);
	
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'location_setting_switcher_options');
	
	
	/*$arr[] = array( 'name' => GD_LOCATION_SETTINGS, 'type' => 'sectionstart', 'id' => 'location_setting_default_options');
	
	$arr[] = array(  
			'name'  => GD_LOCATION_MULTICITY,
			'desc' 	=> GD_LOCATION_MULTICITY_DESC,
			'id' 	=> 'location_multicity',
			'std' 		=> '',
			'type' 	=> 'checkbox',
			'std' 	=> 'yes'
		);
	
	$arr[] = array(  
			'name'  => GD_LOCATION_EVERYWHERE,
			'desc' 	=> GD_LOCATION_EVERYWHERE_DESC,
			'id' 	=> 'location_everywhere',
			'std' 		=> '',
			'type' 	=> 'checkbox',
			'std' 	=> 'yes' 
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'location_setting_default_options');*/
	
	
		/* -------- start location settings ----- */
	$arr[] = array( 'name' => GD_LOCATION_SETTINGS, 'type' => 'sectionstart', 'id' => 'geodir_location_setting');
	
	$arr[] = array(  
		'name' => __( 'Home Page Results', GEODIRLOCATION_TEXTDOMAIN ),
		'desc' 		=> __( 'Show default location results on home page (First time only, if geodirectory home page is your site home page and user comes to home page).', GEODIRLOCATION_TEXTDOMAIN ),
		'id' 		=> 'geodir_result_by_location',
		'std' 		=> 'everywhere',
		'type' 		=> 'radio',
		'value'		=> 'default',
		'radiogroup'		=> 'start'
	);
		
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Show everywhere location results on home page (First time only, if geodirectory home page is your site home page and user comes to home page).', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_result_by_location',
			'std' 		=> 'everywhere',
			'type' 		=> 'radio',
			'value'		=> 'everywhere',
			'radiogroup'		=> 'end'
		);
		
	$arr[] = array('name' => '',
	'id' 		=> '',
	'type' => 'field_seperator',
	);
	
	$arr[] = array(  
		'name' => __( 'Country', GEODIRLOCATION_TEXTDOMAIN ),
		'desc' 		=> __( 'Enable default country (country drop-down will not appear on add listing and location switcher).', GEODIRLOCATION_TEXTDOMAIN ),
		'id' 		=> 'geodir_enable_country',
		'std' 		=> 'multi',
		'type' 		=> 'radio',
		'value'		=> 'default',
		'radiogroup'		=> 'start'
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Enable Multi Countries', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_country',
			'std' 		=> 'multi',
			'type' 		=> 'radio',
			'value'		=> 'multi',
			'radiogroup'		=> ''
		);
		
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Enable Selected Countries', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_country',
			'std' 		=> 'multi',
			'type' 		=> 'radio',
			'value'		=> 'selected',
			'radiogroup'		=> 'end'
		);
	
	
	$arr[] = array(  
	'name' => '',
		'desc' 		=> __( 'Only select countries will appear in country drop-down on add listing page and location switcher. Make sure to have default country in your selected countries list for proper site functioning.', GEODIRLOCATION_TEXTDOMAIN ),
		'tip' 		=> '',
		'id' 		=> 'geodir_selected_countries',
		'css' 		=> 'min-width:300px;',
		'std' 		=> array(),
		'type' 		=> 'multiselect',
		'placeholder_text' => __( 'Select Countries', GEODIRLOCATION_TEXTDOMAIN ),
		'class'		=> 'chosen_select',
		'options' =>  $country_array
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Add everywhere option in location switcher country drop-down.', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_everywhere_in_country_dropdown',
			'std' 		=> '1',
			'type' 		=> 'checkbox',
			'value'		=> '1',
		);
	
	$arr[] = array('name' => '',
	'id' 		=> '',
	'type' => 'field_seperator',
	);
	
	/*state*/
	$arr[] = array(  
		'name' => __( 'Region', GEODIRLOCATION_TEXTDOMAIN ),
		'desc' 		=> __( 'Enable default region (region drop-down will not appear on add listing and location switcher).', GEODIRLOCATION_TEXTDOMAIN ),
		'id' 		=> 'geodir_enable_region',
		'std' 		=> 'multi',
		'type' 		=> 'radio',
		'value'		=> 'default',
		'radiogroup'		=> 'start'
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Enable Multi Regions', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_region',
			'std' 		=> 'multi',
			'type' 		=> 'radio',
			'value'		=> 'multi',
			'radiogroup'		=> ''
		);
		
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Enable Selected Regions', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_region',
			'std' 		=> 'multi',
			'type' 		=> 'radio',
			'value'		=> 'selected',
			'radiogroup'		=> 'end'
		);
	
	$arr[] = array(  
	'name' => '',
		'desc' 		=> __( 'Only select regions will appear in region drop-down on add listing page and location switcher. Make sure to have default region in your selected regions list for proper site functioning', GEODIRLOCATION_TEXTDOMAIN ),
		'tip' 		=> '',
		'id' 		=> 'geodir_selected_regions',
		'css' 		=> 'min-width:300px;',
		'std' 		=> array(),
		'type' 		=> 'multiselect',
		'placeholder_text' => __( 'Select Regions', GEODIRLOCATION_TEXTDOMAIN ),
		'class'		=> 'chosen_select',
		'options' => $region_array
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Add everywhere option in location switcher region drop-down.', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_everywhere_in_region_dropdown',
			'std' 		=> '1',
			'type' 		=> 'checkbox',
			'value'		=> '1',
		);
	
	$arr[] = array('name' => '',
	'id' 		=> '',
	'type' => 'field_seperator',
	);
	
	/*city*/
	$arr[] = array(  
		'name' => __( 'City', GEODIRLOCATION_TEXTDOMAIN ),
		'desc' 		=> __( 'Enable default city (City drop-down will not appear on add listing and location switcher).', GEODIRLOCATION_TEXTDOMAIN ),
		'id' 		=> 'geodir_enable_city',
		'std' 		=> 'multi',
		'type' 		=> 'radio',
		'value'		=> 'default',
		'radiogroup'		=> 'start'
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Enable Multicity', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_city',
			'std' 		=> 'multi',
			'type' 		=> 'radio',
			'value'		=> 'multi',
			'radiogroup'		=> ''
		);
		
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Enable Selected City', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_city',
			'std' 		=> 'multi',
			'type' 		=> 'radio',
			'value'		=> 'selected',
			'radiogroup'		=> 'end'
		);
	
	$arr[] = array(  
	'name' => '',
		'desc' 		=> __( 'Only select cities will appear in city drop-down on add listing page and location switcher. Make sure to have default city in your selected cities list for proper site functioning', GEODIRLOCATION_TEXTDOMAIN ),
		'tip' 		=> '',
		'id' 		=> 'geodir_selected_cities',
		'css' 		=> 'min-width:300px;',
		'std' 		=> array(),
		'type' 		=> 'multiselect',
		'placeholder_text' => __( 'Select Cities', GEODIRLOCATION_TEXTDOMAIN ),
		'class'		=> 'chosen_select',
		'options' => $city_array
	);
	
	$arr[] = array(  
			'name' => '',
			'desc' 		=> __( 'Add everywhere option in location switcher city drop-down.', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 		=> 'geodir_everywhere_in_city_dropdown',
			'std' 		=> '1',
			'type' 		=> 'checkbox',
			'value'		=> '1',
		);
	
	$arr[] = array('name' => '',
	'id' 		=> '',
	'type' => 'field_seperator',
	);
	
	$arr[] = array(  
			'name'  => GD_LOCATION_NEIGHBOURHOODS,
			'desc' 	=> GD_LOCATION_NEIGHBOURHOODS_DESC,
			'id' 	=> 'location_neighbourhoods',
			'std' 		=> '',
			'type' 	=> 'checkbox',
			'std' 	=> 'yes' 
		);
	
	
	
	
		
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_location_setting');
	
	$arr[] = array( 'name' => __( 'Add listing form settings', GEODIRLOCATION_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'geodir_location_setting_add_listing');
	
	$arr[] = array(  
			'name'  => __( 'Disable Google address autocomplete?', GEODIRLOCATION_TEXTDOMAIN ),
			'desc' 	=> __( 'This will stop the address sugestions when typing in address box on add listing page.', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 	=> 'location_address_fill',
			'std' 		=> '',
			'type' 	=> 'checkbox',
			'std' 	=> 'yes' 
		);
	
	$arr[] = array(  
			'name'  => __( 'Show all locations in dropdown?', GEODIRLOCATION_TEXTDOMAIN ),
			'desc' 	=> __( 'This is usefull if you have a small directory but can break your site if you have many locations', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 	=> 'location_dropdown_all',
			'std' 		=> '',
			'type' 	=> 'checkbox',
			'std' 	=> 'yes' 
		);
	
	$arr[] = array(  
			'name'  => __( 'Disable set address on map from changing address fields', GEODIRLOCATION_TEXTDOMAIN ),
			'desc' 	=> __( 'This is usefull if you have a small directory and you have custom locations or your locations are not known by the Google API and they break the address. (highly recommended not to enable this)', GEODIRLOCATION_TEXTDOMAIN ),
			'id' 	=> 'location_set_address_disable',
			'std' 		=> '',
			'type' 	=> 'checkbox',
			'std' 	=> 'yes' 
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_location_setting_add_listing');
	
	
	/* -------- end location settings ----- */
	
	
	$arr = apply_filters('geodir_location_default_options' ,$arr );
	
	return $arr;
}


function geodir_get_locations($term = '', $search = '', $single = false)
{
	
	global $wpdb;
	
	$where = $group_by = '';
	
	$where_array = array();
	
	switch($term):
		case 'country':
			if($search !='' ){ 
				$where = $wpdb->prepare(" AND ( country = %s OR country_slug = %s )", array($search,$search));	
			}else{ $group_by = " GROUP BY country ";}
		break;
		case 'region':
			if($search !='' ){ 
				$where = $wpdb->prepare(" AND ( region = %s OR region_slug = %s ) ", array($search,$search));
			}else{ $group_by = " GROUP BY region ";}
		break;
		case 'city':
			if($search !='' ){ 
				$where = $wpdb->prepare(" AND ( city = %s OR city_slug = %s ) ", array($search,$search));
			}else{ $group_by = " GROUP BY city ";}
		break;
	endswitch;
	
	$locations = $wpdb->get_results(
			"SELECT * FROM ".POST_LOCATION_TABLE." WHERE 1=1 ".$where.$group_by." ORDER BY city "
	);	
	
	return (!empty($locations)) ?  $locations : false;
		
}	
/**/

function geodir_location_default_latitude($lat, $is_default)
{
	
	if($is_default == '1' && isset($_SESSION['gd_multi_location']) && !isset($_REQUEST['pid']) && !isset($_REQUEST['backandedit']) && !isset($_SESSION['listing'])){
		
		if(isset($_SESSION['gd_city']) && $_SESSION['gd_city'] != '' )
			$location = geodir_get_locations('city',$_SESSION['gd_city']);
		elseif(isset($_SESSION['gd_region']) && $_SESSION['gd_region'] != '' )
			$location = geodir_get_locations('region',$_SESSION['gd_region']);
		elseif(isset($_SESSION['gd_country']) && $_SESSION['gd_country'] != '' )
			$location = geodir_get_locations('country',$_SESSION['gd_country']);		
		
		if(isset($location) && $location)
			$location = end($location);
			
		$lat = isset($location->city_latitude) ? $location->city_latitude : '';
	}
	
	return $lat;
	
}

function geodir_location_default_longitude($lat, $is_default)
{
	
	if($is_default == '1' && isset($_SESSION['gd_multi_location']) && !isset($_REQUEST['pid']) && !isset($_REQUEST['backandedit']) && !isset($_SESSION['listing'])){
		
		if(isset($_SESSION['gd_city']) && $_SESSION['gd_city'] != '' )
			$location = geodir_get_locations('city',$_SESSION['gd_city']);
		elseif(isset($_SESSION['gd_region']) && $_SESSION['gd_region'] != '' )
			$location = geodir_get_locations('region',$_SESSION['gd_region']);
		elseif(isset($_SESSION['gd_country']) && $_SESSION['gd_country'] != '' )
			$location = geodir_get_locations('country',$_SESSION['gd_country']);		
		
		if(isset($location) && $location)
			$location = end($location);
			
		$lat = isset($location->city_longitude) ? $location->city_longitude : '';
	}
	
	return $lat;
}




function geodir_add_new_location_via_adon($location_info)
{
	
	global $wpdb;
	if(!empty($location_info)){
		
		$get_location_info = $wpdb->get_row($wpdb->prepare("SELECT * from ".POST_LOCATION_TABLE." where city like %s AND region like %s AND country like %s",array($location_info['city'],$location_info['region'],$location_info['country'] )), "OBJECT" );
		
		if(empty($get_location_info)){
			
			$city_meta = isset($location_info['city_meta']) ? $location_info['city_meta'] : '';
			$city_desc = isset($location_info['city_desc']) ? $location_info['city_desc'] : '';
				
			$wpdb->query(
				$wpdb->prepare("INSERT INTO ".POST_LOCATION_TABLE." SET 
					city = %s,
					region = %s,
					country = %s,
					country_slug = %s,
					region_slug = %s,
					city_slug = %s,
					city_latitude = %s,
					city_longitude = %s,
					is_default	=	%s ,
					city_meta = %s,
					city_desc = %s",
					
					array($location_info['city'],$location_info['region'],$location_info['country'],$location_info['country_slug'],$location_info['region_slug'],$location_info['city_slug'],$location_info['city_latitude'],$location_info['city_longitude'],$location_info['is_default'],$city_meta,$city_desc)
					
				)
			);
			
			$last_location_id = $wpdb->insert_id;	
			
			$location_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".POST_LOCATION_TABLE." WHERE location_id=%d",array($last_location_id)), "OBJECT" );
					
		}else{
			$location_info = $get_location_info;
		}
	
	}
	
	return $location_info;
}

function geodir_location_address_extra_admin_fields($address, $field_info)
{
		(isset($field_info->is_admin) && $field_info->is_admin=='1') ? $display_field = 'style="display:none;"' : $display_field = '';?>
		 
			<tr <?php echo $display_field;?> >
					<td ><strong><?php _e('Display City :', GEODIRLOCATION_TEXTDOMAIN);?></strong></td>
					<td align="left">
						<input type="checkbox"  name="extra[show_city]" id="show_city"  value="1" <?php if(isset($address['show_city']) && $address['show_city']=='1'){ echo 'checked="checked"';}?> />
						<span><?php _e('Select if you want to show city field in address section.', GEODIRLOCATION_TEXTDOMAIN);?></span>
					</td>
			</tr>
			
			<tr>
					<td ><strong><?php _e('City label :', GEODIRLOCATION_TEXTDOMAIN);?></strong></td>
					<td align="left">
						<input type="text" name="extra[city_lable]" id="city_lable"  value="<?php if(isset($address['city_lable'])){ echo $address['city_lable'];}?>" />
						<span><?php _e('Enter city field label in address section.', GEODIRLOCATION_TEXTDOMAIN);?></span>
					</td>
			</tr>
		 
		 <?php (isset($field_info->is_admin) && $field_info->is_admin=='1') ? $display_field = 'style="display:none;"' : $display_field = '';?> 
			<tr <?php echo $display_field;?> >
					<td ><strong><?php _e('Display Region :', GEODIRLOCATION_TEXTDOMAIN);?></strong></td>
					<td align="left">
						<input type="checkbox"  name="extra[show_region]" id="show_region"  value="1" <?php if(isset($address['show_region']) && $address['show_region']=='1'){ echo 'checked="checked"';}?>/>
						<span><?php _e('Select if you want to show region field in address section.', GEODIRLOCATION_TEXTDOMAIN);?></span>
					</td>
			</tr>
			
			<tr>
					<td ><strong><?php _e('Region label :', GEODIRLOCATION_TEXTDOMAIN);?></strong></td>
					<td align="left">
						<input type="text" name="extra[region_lable]" id="region_lable"  value="<?php if(isset($address['region_lable'])){ echo $address['region_lable'];}?>" />
						<span><?php _e('Enter region field label in address section.', GEODIRLOCATION_TEXTDOMAIN);?></span>
					</td>
			</tr>
			
		 <?php (isset($field_info->is_admin) && $field_info->is_admin=='1') ? $display_field = 'style="display:none;"' : $display_field = '';?> 
			<tr <?php echo $display_field;?> >
					<td ><strong><?php _e('Display Country :', GEODIRLOCATION_TEXTDOMAIN);?></strong></td>
					<td align="left">
						<input type="checkbox"  name="extra[show_country]" id="show_country"  value="1" <?php if(isset($address['show_country']) && $address['show_country']=='1'){ echo 'checked="checked"';}?>/>
						<span><?php _e('Select if you want to show country field in address section.', GEODIRLOCATION_TEXTDOMAIN);?></span>
					</td>
			</tr>
		
		 <tr>
				<td ><strong><?php _e('Country label :', GEODIRLOCATION_TEXTDOMAIN);?></strong></td>
				<td align="left">
					<input type="text" name="extra[country_lable]" id="country_lable"  value="<?php if(isset($address['country_lable'])) {echo $address['country_lable'];}?>" />
					<span><?php _e('Enter country field label in address section.', GEODIRLOCATION_TEXTDOMAIN);?></span>
				</td>
		</tr>
	<?php
}




//// Location DB requests

function geodir_parse_location_list($list)
{
	$list_for_query ='';
	if(!empty($list))
	{
		$list_array = explode(',' , $list);
		if(!empty($list_array ))
		{
			foreach($list_array as $list_item)
			{
				$list_for_query .= "," . "'".mb_strtolower($list_item )."'" ;
			}
		}
	}
	if(!empty($list_for_query))
		$list_for_query  = trim($list_for_query , ',');
	
	return $list_for_query ;
}

function geodir_what_is_current_location()
{
	$city = geodir_get_current_location(array('what' => 'city' , 'echo'=>false)) ;
	$region = geodir_get_current_location(array('what' => 'region' , 'echo'=>false)) ;
	$country = geodir_get_current_location(array('what' => 'country' , 'echo'=>false)) ;
	
	if(!empty($city))
		return 'city' ;
	
	if(!empty($region))
		return 'region' ;
	
	if(!empty($country))
		return 'country' ;
	
	return '';
		
}

add_filter('geodir_seo_meta_location_description', 'geodir_set_location_meta_desc', 10);
function geodir_set_location_meta_desc( $seo_desc='' ){
	global $wpdb, $wp;
	
	$gd_country = get_query_var( 'gd_country' );
	$gd_region = get_query_var( 'gd_region' );
	$gd_city = get_query_var( 'gd_city' );
	
	if ($gd_city) {
		$info = geodir_city_info_by_slug($gd_city, $gd_country, $gd_region);
		if (!empty($info)) {
			$seo_desc .= $info->city_meta!='' ? $info->city_meta : $info->city_meta;
		}
	} else if (!$gd_city && $gd_region) {
		$info = geodir_location_seo_by_slug($gd_region, 'region', $gd_country);
		if (!empty($info)) {
			$seo_desc .= $info->seo_desc!='' ? $info->seo_desc : $info->seo_title;
		}
	} else if (!$gd_city && !$gd_region && $gd_country) {
		$info = geodir_location_seo_by_slug($gd_country, 'country');
		if (!empty($info)) {
			$seo_desc .= $info->seo_desc!='' ? $info->seo_desc : $info->seo_title;
		}
	}
	$location_desc = $seo_desc;
	if ($location_desc=='') {
		return NULL;
	} else {
		return $location_desc;
	}
	
}

function geodir_save_cat_location() {	
	global $wpdb;
	
	$wpnonce = isset($_REQUEST['wpnonce']) ? $_REQUEST['wpnonce'] : '';
	$locid = isset($_REQUEST['locid']) ? (int)$_REQUEST['locid'] : '';
	$catid = isset($_REQUEST['catid']) ? (int)$_REQUEST['catid'] : '';
	$posttype = isset($_REQUEST['posttype']) ? $_REQUEST['posttype'] : '';
	$content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
	$loc_default = isset($_REQUEST['loc_default']) ? $_REQUEST['loc_default'] : '';
	
	$category_taxonomy = geodir_get_taxonomies($posttype);
	$taxonomy = isset($category_taxonomy[0]) && $category_taxonomy[0] ? $category_taxonomy[0] : 'gd_placecategory';
	
	if(is_admin() && $wpnonce && current_user_can( 'manage_options' ) && $locid>0 && $catid>0 && $posttype) {
		$option = array();
		$option['gd_cat_loc_default'] = (int)$loc_default;
		$option['gd_cat_loc_cat_id'] = $catid;
		$option['gd_cat_loc_post_type'] = $posttype;
		$option['gd_cat_loc_taxonomy'] = $taxonomy;
		$option_name = 'geodir_cat_loc_'.$posttype.'_'.$catid;
		
		update_option($option_name, $option);
					
		$option = array();
		$option['gd_cat_loc_loc_id'] = (int)$locid;
		$option['gd_cat_loc_cat_id'] = (int)$catid;
		$option['gd_cat_loc_post_type'] = $posttype;
		$option['gd_cat_loc_taxonomy'] = $taxonomy;
		$option['gd_cat_loc_desc'] = $content;
		$option_name = 'geodir_cat_loc_'.$posttype.'_'.$catid.'_'.$locid;
		
		update_option($option_name, $option);

		echo 'OK';
		exit;
	}
	echo 'FAIL';
	exit;
}

function geodir_change_cat_location() {	
	global $wpdb;
	
	$wpnonce = isset($_REQUEST['wpnonce']) ? $_REQUEST['wpnonce'] : '';
	$gd_location = isset($_REQUEST['locid']) ? (int)$_REQUEST['locid'] : '';
	$term_id = isset($_REQUEST['catid']) ? (int)$_REQUEST['catid'] : '';
	$post_type = isset($_REQUEST['posttype']) ? $_REQUEST['posttype'] : '';
	
	if(is_admin() && $wpnonce && current_user_can( 'manage_options' ) && $gd_location>0 && $term_id>0 && $post_type) {
		$option_name = 'geodir_cat_loc_'.$post_type.'_'.$term_id.'_'.$gd_location;
		$option = get_option($option_name);
		$gd_cat_loc_desc = !empty($option) && isset($option['gd_cat_loc_desc']) ? $option['gd_cat_loc_desc'] : '';
		echo stripslashes_deep($gd_cat_loc_desc);
		exit;
	}
	echo 'FAIL';
	exit;
}

function get_actual_location_name($type, $term, $translated=false) {
	if ($type=='' || $term=='') {
		return NULL;
	}
	$row = geodir_get_locations($type, $term);
	$value = !empty($row) && !empty($row[0]) && isset($row[0]->$type) ? $row[0]->$type : '';
	if( $translated ) {
		$value = __( $value, GEODIRECTORY_TEXTDOMAIN );
	}
	return $value;
}

function count_listings_by_country( $country, $country_slug='', $with_translated=false ) {
	global $wpdb, $plugin_prefix;
	
	$geodir_posttypes = geodir_get_posttypes();
	
	$total = 0;
	if ( $country == '' ) {
		return $total;
	}
	
	foreach( $geodir_posttypes as $geodir_posttype ) {
		$table = $plugin_prefix . $geodir_posttype . '_detail';
		
		if( $with_translated ) {
			$country_translated = __( $country, GEODIRECTORY_TEXTDOMAIN);
			$sql = "SELECT COUNT(*) FROM " . $table . " WHERE post_country LIKE '".$country."' OR post_country LIKE '".$country_translated."' OR post_locations LIKE '%,[".$country_slug."]'";
		} else {
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM " . $table . " WHERE post_country LIKE %s", array( $country ) );
		}
		$count = (int)$wpdb->get_var( $sql );
		
		$total += $count;
	}
	return $total;	
}

function get_post_location_countries() {
	global $wpdb;
	$sql = "SELECT country, country_slug, count(location_id) AS total FROM " . POST_LOCATION_TABLE . " WHERE country_slug != '' && country != '' GROUP BY country_slug ORDER BY country ASC";
	$rows = $wpdb->get_results( $sql );
	return $rows;	
}

function get_post_country_by_slug( $country_slug ) {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT country FROM " . POST_LOCATION_TABLE . " WHERE country_slug != '' && country_slug = %s GROUP BY country_slug ORDER BY country ASC", array( $country_slug ) );
	$value = $wpdb->get_var( $sql );
	return $value;	
}

function geodir_update_location_translate( $country_slug ) {
	global $wpdb, $plugin_prefix;
	if( $country_slug == '' ) {
		return false;
	}
	
	$country = get_post_country_by_slug( $country_slug );
	if( $country == '' ) {
		return false;
	}
	
	$geodir_posttypes = geodir_get_posttypes();
	
	$country_translated = __( $country, GEODIRECTORY_TEXTDOMAIN );
	$country_translated = trim( wp_unslash( $country_translated ) );
	$country_slug_translated = sanitize_title( $country_translated );
	
	$country_slug = apply_filters( 'geodir_filter_update_location_translate', $country_slug, $country, $country_translated, $country_slug_translated );
	
	do_action( 'geodir_action_update_location_translate', $country_slug, $country, $country_translated, $country_slug_translated );
	
	if( $country_slug == $country_slug_translated ) {
		return false;
	}
	
	$sql = $wpdb->prepare( "SELECT location_id FROM " . POST_LOCATION_TABLE . " WHERE country_slug=%s", array( $country_slug ) );
	$location_ids = $wpdb->get_col( $sql );
	
	/* update in post locations table */
	$update_locations = false;
	//$sql = $wpdb->prepare( "UPDATE " . POST_LOCATION_TABLE . " SET country=%s, country_slug=%s WHERE country_slug=%s", array( $country_translated, $country_slug_translated, $country_slug ) );
	$sql = $wpdb->prepare( "UPDATE " . POST_LOCATION_TABLE . " SET country_slug=%s WHERE country_slug=%s", array( $country_slug_translated, $country_slug ) );
	$update_locations = $wpdb->query($sql);
	
	/* update in post listings table */
	$update_listings = false;
	if( !empty( $location_ids ) ) {
		$location_ids = implode( ",", $location_ids );
		foreach( $geodir_posttypes as $geodir_posttype ) {
			$table = $plugin_prefix . $geodir_posttype . '_detail';
			
			$sql = "SELECT post_id, post_locations, post_location_id FROM " . $table . " WHERE post_location_id IN(" . $location_ids  . ")";
			$listings = $wpdb->get_results( $sql );
			
			if( !empty( $listings ) ) {
				foreach( $listings as $listing ) {
					$post_id = $listing->post_id;
					$location_id = $listing->post_location_id;
					$post_locations = $listing->post_locations;
					if( $post_locations != '' ) {
						$post_locations_arr = explode( ",", $post_locations );
						
						if( isset( $post_locations_arr[2] ) && trim($post_locations_arr[2]) != '[]' ) {
							$post_locations_arr[2] = '[' . $country_slug_translated . ']';
							$post_locations = implode( ",", $post_locations_arr );
						} else {
							$post_locations = '';
						}
					}
					
					if( $post_locations == '' ) {
						$location_info = geodir_get_location_by_id( '', $location_id );
						if( !empty( $location_info ) && isset( $location_info->location_id ) ) {
							$post_locations = '['. $location_info->city_slug .'],['. $location_info->region_slug .'],['. $country_slug_translated .']';
						}
					}
					
					$sql = $wpdb->prepare( "UPDATE " . $table . " SET post_locations=%s, post_country=%s WHERE post_id=%d", array( $post_locations, $country_translated, $post_id ) );
					$update_locations = $wpdb->query($sql);
				}
			}
		}
		$update_locations = true;
	}
	
	/* update in location seo table */
	$update_location_seo = false;
	$sql = $wpdb->prepare( "UPDATE " . LOCATION_SEO_TABLE . " SET country_slug=%s WHERE country_slug=%s", array( $country_slug_translated, $country_slug ) );
	$update_location_seo = $wpdb->query($sql);
	
	if( $update_locations || $update_listings || $update_location_seo ) {
		return true;
	}
	return false;
}

function geodir_countries_search_sql( $search = '', $array = false ) {
	$countries = geodir_get_countries_array();
	$return = $array ? array() : '';
	
	$search = strtolower( trim( $search ) );
	if( $search == '' ) {
		return $return;
	}
	
	if( !empty( $countries ) ) {
		foreach( $countries as $row => $value ) {
			$strfind = strtolower( $value );
			
			if( $row != $value && strpos( $strfind, $search ) === 0 ) {
				$return[] = $row; 
			}
		}
	}
	if( $array ) {
		return $return;
	}
	$return = !empty( $return ) ? implode( ",", $return ) : '';
	return $return;
}

function geodir_location_permalink_url( $url ) {
	if ( $url == '' ) {
		return NULL;
	}
	
	if ( get_option( 'permalink_structure' ) != '' ) {
		$url = trim( $url );
		$url = rtrim( $url, '/' ) . '/';
	}
	
	$url = apply_filters( 'geodir_location_filter_permalink_url', $url );
	
	return $url;
}

add_action( 'wp_ajax_gd_location_manager_set_user_location', 'gd_location_manager_set_user_location' );
add_action( 'wp_ajax_nopriv_gd_location_manager_set_user_location', 'gd_location_manager_set_user_location' );

function gd_location_manager_set_user_location(){
	global $wpdb;
	$_SESSION['user_lat']=$_POST['lat'];
	$_SESSION['user_lon']=$_POST['lon'];
	if(isset($_POST['myloc']) && $_POST['myloc']){
	$_SESSION['my_location']=1;
	}else{
	$_SESSION['my_location']=0;	
	}
	$_SESSION['user_pos_time']=time();
	die();
}
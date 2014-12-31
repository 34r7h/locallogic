<?php
function geodir_custom_gmaps_admin_css(){
	global $pagenow;
	
	if ($pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'custom_gmaps_manager') {
		// Style
		wp_register_style('geodir-custom-gmaps-plugin-style', plugins_url('',__FILE__).'/css/geodir-custom-gmaps-manager.css');
		wp_enqueue_style('geodir-custom-gmaps-plugin-style');
	}
}

function geodir_custom_google_maps_plugin_activated($plugin) {
	if (!get_option('geodir_installed'))  {
		$file = plugin_basename(__FILE__);
		
		if ($file == $plugin) {
			$all_active_plugins = get_option( 'active_plugins', array() );
			
			if (!empty($all_active_plugins) && is_array($all_active_plugins)) {
				foreach ($all_active_plugins as $key => $plugin) {
					if ($plugin ==$file) {
						unset($all_active_plugins[$key]);
					}
				}
			}
			update_option('active_plugins', $all_active_plugins);
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRCUSTOMGMAPS_TEXTDOMAIN));
	}
}

function geodir_custom_gmaps_activation(){
	if (get_option('geodir_installed')) {
		add_option('geodir_custom_gmaps_activation_redirect_opt', 1);
	}
}

function geodir_custom_gmaps_deactivation() {
}

function geodir_custom_gmaps_uninstall() {
}

function geodir_custom_gmaps_activation_redirect(){
	if (get_option('geodir_custom_gmaps_activation_redirect_opt', false)) {
		delete_option('geodir_custom_gmaps_activation_redirect_opt');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_general_options')); 
	}
}

function geodir_custom_gmaps_current_subtab($default='') {
	$subtab = isset($_REQUEST['subtab']) ? $_REQUEST['subtab'] : '';
	
	if ($subtab=='' && $default!='') {
		$subtab = $default;
	}
	
	return $subtab;
}

// This function is used to create geodirteory custom maps manager navigation
function geodir_custom_gmaps_tabs_array($tabs) {
	$custom_gmaps_tabs = array();
	$custom_gmaps_tabs['label'] = __('Custom Google Maps', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
	$custom_gmaps_tabs['subtabs'] = array(
										array(
											'subtab' => 'geodir_custom_gmaps_general_options',
											'label' => __( 'General', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
											'form_action' => admin_url('admin-ajax.php?action=geodir_custom_gmaps_manager_ajax')
										),
										array(
											'subtab' => 'geodir_custom_gmaps_manage_styles',
											'label' => __( 'Manage Styles', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
											'form_action' => admin_url('admin-ajax.php?action=geodir_custom_gmaps_manager_ajax')
										)
									);
	// hook for custom map tabs
	$custom_gmaps_tabs = apply_filters('geodir_custom_gmaps_tabs', $custom_gmaps_tabs);
	
	$tabs['custom_gmaps_manager'] = $custom_gmaps_tabs;
	return $tabs;
}

function geodir_custom_gmaps_general_options( $options = array() ) {
	$options[] = array('name' => __('General Options', GEODIRCUSTOMGMAPS_TEXTDOMAIN), 'type' => 'no_tabs', 'desc' => '', 'id' => 'gmaps_general_options');
	$options[] = array('name' => __('General Settings', GEODIRCUSTOMGMAPS_TEXTDOMAIN), 'type' => 'sectionstart', 'id' => 'custom_gmaps_settings');
	
	$options[] = array(  
		'name' => __('Enable custom style on home page map?', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
		'desc' => __('Enable custom style on home page map.', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
		'id' => 'geodir_custom_gmaps_home',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array(  
		'name' => __('Enable custom style on listing page map?', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
		'desc' => __('Enable custom style on listing page map.', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
		'id' => 'geodir_custom_gmaps_listing',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array(  
		'name' => __('Enable custom style on detail page map?', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
		'desc' => __('Enable custom style on detail page map.', GEODIRCUSTOMGMAPS_TEXTDOMAIN),
		'id' => 'geodir_custom_gmaps_detail',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array( 'type' => 'sectionend', 'id' => 'custom_gmaps_settings');
	
	// hook for custom map general options
	$options = apply_filters('geodir_custom_gmaps_general_options', $options);
	
	return $options;
}

function geodir_custom_gmaps_option_form($current_tab) {
	$current_tab = geodir_custom_gmaps_current_subtab();
	geodir_custom_gmaps_get_option_form($current_tab);
}

function geodir_custom_gmaps_manager_tab_content() {
	global $wpdb;
	
	$subtab = geodir_custom_gmaps_current_subtab();
	
	if ($subtab == 'geodir_custom_gmaps_general_options') {	
		add_action('geodir_admin_option_form', 'geodir_custom_gmaps_option_form');
	} else if ($subtab == 'geodir_custom_gmaps_manage_styles') {	
		$gd_map = isset($_REQUEST['gd_map']) ? trim($_REQUEST['gd_map']) : '';
		
		if ($gd_map=='home' || $gd_map=='listing' || $gd_map=='detail') {
			geodir_custom_gmaps_add_style_form();
		} else {
			geodir_custom_gmaps_show_styles_list();
		}
	}
}

// main ajax function
function geodir_custom_gmaps_manager_ajax() {
	$subtab = geodir_custom_gmaps_current_subtab();
	
	if (isset($_POST['custom_gmaps_update_nonce']) && isset($_POST['gd_map'])) {
		$msg = geodir_custom_gmaps_update_style();
		$msg = urlencode_deep($msg);
		
		wp_redirect(admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles&gd_map='.$_POST['gd_map'].'&success_msg='.$msg);
		exit;
	}
	
	if ($subtab=='geodir_custom_gmaps_general_options') {
		geodir_update_options(geodir_custom_gmaps_general_options());
		
		$msg = __('Settings saved.', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
		$msg = urlencode_deep($msg);
		
		wp_redirect(admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_general_options&success_msg='.$msg);
		exit;
	}
}

function geodir_custom_gmaps_update_style() {
	$msg = __('Map style not saved, please try again!', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
	if (current_user_can('manage_options') && isset($_POST['custom_gmaps_update_nonce'])) {
		$gd_map = isset($_POST['gd_map']) ? trim($_POST['gd_map']) : '';
		$gd_gmap_style = isset($_POST['gd_gmap_style']) ? $_POST['gd_gmap_style'] : '';
		
		if (empty($gd_gmap_style)) {
			$msg = __('Map style not saved, please add atleast one style!', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
			wp_redirect(admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles&gd_map='.$gd_map);
			exit;
		}
		if (wp_verify_nonce($_POST['custom_gmaps_update_nonce'], 'custom_gmaps_update')) {
			$save_params = array();
			
			foreach ($gd_gmap_style as $index => $row) {
				$featureType = isset($row['featureType']) && $row['featureType'] != '' ? $row['featureType'] : '';
				$elementType = isset($row['elementType']) && $row['elementType'] != '' ? $row['elementType'] : '';
				$stylers = isset($row['stylers']) && !empty($row['stylers']) != '' ? $row['stylers'] : '';
				
				$parse_stylers = array();
				foreach ($stylers as $styler => $value) {
					if ($value!='' && strlen($value) > 0) {
						$parse_stylers[][$styler] = $value;
					}
				}
				if ($featureType != '' && !empty($parse_stylers)) {
					$save_param = array();
					$save_param['featureType'] = $featureType;
					if ($elementType!='') {
						$save_param['elementType'] = $elementType;
					}
					$save_param['stylers'] = $parse_stylers;
					$save_params[] = $save_param;
				}
			}
			
			if (empty($save_params)) {
				$msg = __('Map style not saved, please choose atleast one styler!', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
				return $msg;
			}
			
			$return = false;
			switch($gd_map) {
				case 'home': {
					$option_value = get_option('geodir_custom_gmaps_style_home');
					// hook
					$save_params = apply_filters('geodir_custom_gmaps_save_style_home', $save_params);
					update_option('geodir_custom_gmaps_style_home', $save_params);
					$return = true;
				}
				break;
				case 'listing': {
					$option_value = get_option('geodir_custom_gmaps_style_listing');
					// hook
					$save_params = apply_filters('geodir_custom_gmaps_save_style_listing', $save_params);
					update_option('geodir_custom_gmaps_style_listing', $save_params);
					$return = true;
				}
				break;
				case 'detail': {
					$option_value = get_option('geodir_custom_gmaps_style_detail');
					// hook
					$save_params = apply_filters('geodir_custom_gmaps_save_style_detail', $save_params);
					update_option('geodir_custom_gmaps_style_detail', $save_params);
					$return = true;
				}
				break;
			}
			
			if ($return) {
				$msg = __('Map style saved.', GEODIRCUSTOMGMAPS_TEXTDOMAIN);
			}
		}
	}
	return $msg;
}

function geodir_custom_gmaps_StyleFeatureType() {
	// more info - https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyleFeatureType
	$options = array('all', 'administrative', 'administrative.country', 'administrative.land_parcel', 'administrative.locality', 'administrative.neighborhood', 'administrative.province', 'landscape', 'landscape.man_made', 'landscape.natural', 'landscape.natural.landcover', 'landscape.natural.terrain', 'poi', 'poi.attraction', 'poi.business', 'poi.government', 'poi.medical', 'poi.park', 'poi.place_of_worship', 'poi.school', 'poi.sports_complex', 'road', 'road.arterial', 'road.highway', 'road.highway.controlled_access', 'road.local', 'transit', 'transit.line', 'transit.station', 'transit.station.airport', 'transit.station.bus', 'transit.station.rail', 'water', 'administrative');
	
	// hook for feature type
	$options = apply_filters('geodir_custom_gmaps_StyleFeatureType', $options);
	
	$options = is_array($options) && !empty($options) ? array_unique($options) : $options;
	
	return $options;
}

function geodir_custom_gmaps_StyleElementType() {
	// more info - https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyleFeatureType
	$options = array('all', 'geometry', 'geometry.fill', 'geometry.stroke', 'labels', 'labels.icon', 'labels.text', 'labels.text.fill', 'labels.text.stroke');
	
	// hook for element type
	$options = apply_filters('geodir_custom_gmaps_StyleElementType', $options);
	
	$options = is_array($options) && !empty($options) ? array_unique($options) : $options;
	
	return $options;
}

function geodir_custom_gmaps_Styler() {
	// more info - https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyler
	$options = array('color', 'gamma', 'hue', 'invert_lightness', 'lightness', 'saturation', 'visibility', 'weight');
	
	// hook for styler
	$options = apply_filters('geodir_custom_gmaps_Styler', $options);
	
	$options = is_array($options) && !empty($options) ? array_unique($options) : $options;
	
	return $options;
}

function geodir_custom_gmaps_feature_type_options($value='', $select=false) {
	$return = $select ? '<option value="">'.__('Select', GEODIRCUSTOMGMAPS_TEXTDOMAIN).'</option>' : '';
	
	$feature_types = geodir_custom_gmaps_StyleFeatureType();
	
	if (!empty($feature_types)) {
		foreach ($feature_types as $feature_type) {
			$selected = $feature_type == $value ? 'selected="selected"' : '';
			$return .= '<option value="'.$feature_type.'" '.$selected.'>'.$feature_type.'</option>';
		}
	}
	
	return $return;
}

function geodir_custom_gmaps_element_type_options($value='', $select=true) {
	$return = $select ? '<option value="">'.__('Select', GEODIRCUSTOMGMAPS_TEXTDOMAIN).'</option>' : '';
	
	$element_types = geodir_custom_gmaps_StyleElementType();
	
	if (!empty($element_types)) {
		foreach ($element_types as $element_type) {
			$selected = $element_type == $value ? 'selected="selected"' : '';
			$return .= '<option value="'.$element_type.'" '.$selected.'>'.$element_type.'</option>';
		}
	}
	
	return $return;
}

function geodir_custom_gmaps_init_map_style() {
	// filter for home map options
	if (get_option('geodir_custom_gmaps_home')) {
		$map_widgets = get_option('widget_geodir_map_v3_home_map');
		
		if (!empty($map_widgets)) {
			foreach ($map_widgets as $key => $value) {
				add_filter('geodir_map_options_geodir_map_v3_home_map_'.(int)$key, 'geodir_custom_gmaps_home_map_options', 10, 1);
			}
		}
	}
	
	// filter for listing map options
	if (get_option('geodir_custom_gmaps_listing')) {
		$map_widgets = get_option('widget_geodir_map_v3_listing_map');
		
		if (!empty($map_widgets)) {
			foreach ($map_widgets as $key => $value) {
				add_filter('geodir_map_options_geodir_map_v3_listing_map_'.(int)$key, 'geodir_custom_gmaps_listing_map_options', 10, 1);
			}
		}
	}
	
	// filter for detail map options
	if (get_option('geodir_custom_gmaps_detail')) {
		add_filter('geodir_map_options_detail_page_map_canvas', 'geodir_custom_gmaps_detail_map_options', 10, 1);
	}
}

function geodir_custom_gmaps_home_map_options($map_options) {
	$style_option = get_option('geodir_custom_gmaps_style_home');
	
	if (!empty($style_option) && (is_array($style_option) || is_object($style_option))) {
		$map_options['mapStyles'] = json_encode($style_option);
	}
	return $map_options;
}

function geodir_custom_gmaps_listing_map_options($map_options) {
	$style_option = get_option('geodir_custom_gmaps_style_listing');
	
	if (!empty($style_option) && (is_array($style_option) || is_object($style_option))) {
		$map_options['mapStyles'] = json_encode($style_option);
	}
	return $map_options;
}

function geodir_custom_gmaps_detail_map_options($map_options) {
	$style_option = get_option('geodir_custom_gmaps_style_detail');
	
	if (!empty($style_option) && (is_array($style_option) || is_object($style_option))) {
		$map_options['mapStyles'] = json_encode($style_option);
	}
	
	return $map_options;
}
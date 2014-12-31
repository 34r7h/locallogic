<?php
// SHARE LOCATION HOOKS START
add_action('wp_footer','geodir_localize_all_share_location_js_msg');
add_action('wp_ajax_geodir_share_location', "geodir_share_location");
add_action( 'wp_ajax_nopriv_geodir_share_location', 'geodir_share_location' ); // call for not logged in ajax
add_action('wp_ajax_geodir_do_not_share_location', "geodir_do_not_share_location");
add_action( 'wp_ajax_nopriv_geodir_do_not_share_location', 'geodir_do_not_share_location' ); // call for not logged in ajax

// SHARE LOCATION HOOKS END

if(is_admin()){
// AUTOCOMPLERTER START	
add_filter('geodir_settings_tabs_array','geodir_adminpage_advanced_search',5); 
add_action('geodir_admin_option_form' , 'geodir_autocompleter_options_form', 5);
add_action('admin_init', 'geodir_autocompleter_from_submit_handler');
add_action('admin_enqueue_scripts', 'geodir_autocompleter_admin_script');
// AUTOCOMPLERTER END



}

// ADVANCED SEARCH START	
add_action('wp_ajax_gd_advancedsearch_customise', "geodir_advance_search_form",10,2);
add_action( 'wp_ajax_nopriv_gd_advancedsearch_customise', 'geodir_advance_search_form',10,2 ); // call for not logged in ajax
add_action('wp_ajax_geodir_advance_search_button_ajax', "geodir_advance_search_button",10,2);
add_action( 'wp_ajax_nopriv_geodir_advance_search_button_ajax', 'geodir_advance_search_button',10,2 ); // call for not logged in ajax
add_action( 'wp_enqueue_scripts', 'geodir_advanced_search_js_scripts');

// ADVANCED SEARCH END

// AUTOCOMPLERTER START	
add_action('wp_ajax_geodir_autocompleter_ajax_action', "geodir_autocompleter_ajax_actions");
add_action( 'wp_ajax_nopriv_geodir_autocompleter_ajax_action', 'geodir_autocompleter_ajax_actions' );
add_action('wp_ajax_geodir_autocompleter_near_ajax_action', "geodir_autocompleter_near_ajax_actions");
add_action('wp_ajax_nopriv_geodir_autocompleter_near_ajax_action', 'geodir_autocompleter_near_ajax_actions' );

add_action('wp_enqueue_scripts', 'geodir_autocompleter_script');

// AUTOCOMPLERTER END

add_filter('geodir_settings_tabs_array','geodir_advace_search_manager_tabs',100);
add_action('admin_init', 'geodir_advance_search_activation_redirect');
add_action('geodir_manage_selected_fields', 'geodir_manage_advace_search_selected_fields');
add_action('geodir_manage_available_fields', 'geodir_manage_advace_search_available_fields');
add_filter('geodir_sort_options','geodir_get_cat_sort_fields');
add_action('pre_get_posts', 'geodir_advance_search_filter',12);

add_action('wp_ajax_geodir_set_near_me_range', "geodir_set_near_me_range");
add_action('wp_ajax_nopriv_geodir_set_near_me_range', 'geodir_set_near_me_range' );
 

add_action('wp_ajax_geodir_ajax_advance_search_action', "geodir_advance_search_ajax_handler");

add_action( 'wp_ajax_nopriv_geodir_ajax_advance_search_action', 'geodir_advance_search_ajax_handler' );

add_filter('geodir_show_filters','geodirectory_advance_search_custom_fields',0,2);

add_action('geodir_after_search_button', 'geodir_advance_search_button');

add_action('geodir_after_search_form', 'geodir_advance_search_form');

add_action('geodir_search_fields','geodir_show_filters_fields');

add_action('geodir_after_post_type_deleted', 'geodir_advance_search_after_post_type_deleted');

add_action('geodir_after_custom_field_deleted', 'geodir_advance_search_after_custom_field_deleted', 1, 3);

add_action('geodir_advance_custom_fields','geodir_advance_admin_custom_fields');


add_filter('geodir_advance_custom_fields_heading', 'geodir_advance_admin_custom_fields_heading', 1, 2);
function geodir_advance_admin_custom_fields_heading($title, $field_type){
	
	$title = __('Advanced sort & filters options',GEODIRADVANCESEARCH_TEXTDOMAIN);
	return $title;
	
}

add_action( 'wp_ajax_gd_set_user_location', 'gd_set_user_location' );
add_action( 'wp_ajax_nopriv_gd_set_user_location', 'gd_set_user_location' );

function gd_set_user_location(){
	global $wpdb;
	$_SESSION['user_lat']=$_POST['lat'];
	$_SESSION['user_lon']=$_POST['lon'];
	if(isset($_POST['myloc']) && $_POST['myloc']){
	$_SESSION['my_location']=1;
	}else{
	$_SESSION['my_location']=0;	
	}
	$_SESSION['user_pos_time']=time();
	//print_r($_POST);
	//print_r($_SESSION);
	die();
}


add_filter('geodir_custom_fields_panel_head' , 'geodir_advance_search_panel_head' , 10, 3) ;
function geodir_advance_search_panel_head($heading , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$heading =	sprintf(__('Manage advance search options.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
		
	}
	return $heading;
}


add_filter('geodir_cf_panel_available_fields_head' , 'geodir_advance_search_available_fields_head' , 10, 3) ;
function geodir_advance_search_available_fields_head($heading , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$heading =	sprintf( __('Available advance search option for %s listing and search results.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));;
		break;
	}
	return $heading;
}


add_filter('geodir_cf_panel_available_fields_note' , 'geodir_advance_search_available_fields_note' , 10, 3) ;
function geodir_advance_search_available_fields_note($note , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$note =	sprintf(__('Click on any box below to make it appear in advance search form on %s listing and search results.<br />To make a filed available here, go to custom fields tab and expand any field from selected fields panel and tick the checkbox saying \'Include this field in advance search option\'.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
	}
	return $note;
}


add_filter('geodir_cf_panel_selected_fields_head' , 'geodir_advance_search_selected_fields_head' , 10, 3) ;
function geodir_advance_search_selected_fields_head($heading , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$heading =	$heading =	sprintf(__('List of fields those will appear in advance search form on %s listing and search resutls page.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
		
	}
	return $heading;
}


add_filter('geodir_cf_panel_selected_fields_note' , 'geodir_advance_search_selected_fields_note' , 10, 3) ;
function geodir_advance_search_selected_fields_note($note , $sub_tab , $listing_type)
{
	switch($sub_tab)
	{
		case 'advance_search':
			$note =	sprintf(__('Click to expand and view field related settings. You may drag and drop to arrange fields order in advance search form on %s listing and search results page.' , GEODIRECTORY_TEXTDOMAIN),  get_post_type_singular_label($listing_type));
		break;
		
	}
	return $note;
}


function geodir_advance_search_scripts() {
 $plugins_path =  plugins_url('geodir_advance_search_filters');
	wp_enqueue_script(
		'custom_advance_search_fields',
		 $plugins_path.'/advance_search_admin/js/custom_advance_search_fields.js',
		array('jquery'),GEODIRADVANCESEARCH_VERSION
	);
}

function geodir_advance_search_scripts_frontend() {
 $plugins_path =  plugins_url('geodir_advance_search_filters');

	wp_enqueue_script(
		'jquery-dropdown',
		 $plugins_path.'/js/jquery.dropdown.min.js',
		array('jquery'),GEODIRADVANCESEARCH_VERSION
	);
	
	wp_enqueue_style(
		'gd_advance_search_styles',
		 $plugins_path.'/css/style.css',array(),GEODIRADVANCESEARCH_VERSION
	);
}

if(!is_admin()){	add_action( 'wp_enqueue_scripts', 'geodir_advance_search_scripts_frontend' );}

if(isset($_REQUEST['page']) && $_REQUEST['page'] =='geodirectory')
	add_action( 'admin_enqueue_scripts', 'geodir_advance_search_scripts' );



function geodir_advanced_search_js_scripts()
{
	wp_register_script( 'advanced-search-js', GEODIRADVANCESEARCH_PLUGIN_URL.'/js/frontend.js',array('jquery'),GEODIRADVANCESEARCH_VERSION );
	wp_enqueue_script( 'advanced-search-js' );
}


add_filter('wp_footer' , 'geodir_advance_search_form_js' , 10) ;
function geodir_advance_search_form_js()
{
?>
<script>
map_id_arr = [];
gdUmarker='';
my_location = <?php if(isset($_SESSION['my_location']) && $_SESSION['my_location']){echo '1;';}else{echo '0;';}?>
lat = '<?php if(isset($_SESSION['user_lat']) && $_SESSION['user_lat']){echo $_SESSION['user_lat'];}?>';
lon = '<?php if(isset($_SESSION['user_lon']) && $_SESSION['user_lon']){echo $_SESSION['user_lon'];}?>';
gdUmarker = '';
userMarkerActive=false;
gdLocationOptions = {
  enableHighAccuracy: true,
  timeout: 5000,
  maximumAge: 0
};
geodir_insert_compass();
gdSetupUserLoc();


jQuery( "body" ).on( "map_show", function(event,map_id) {
  if(lat && lon){
 setTimeout(function(map_id){
  setusermarker(lat,lon,map_id);//createUserMarker(lat,lon,true);//set marker on map<br />
 }, 1,map_id);
  }
});


</script>
<?php
}



//add_filter('geodir_before_search_button' , 'geodir_advance_search_near_me_button' , 9) ;
function geodir_advance_search_near_me_button(){
	global $wpdb;
	$style='';
	if($size = get_option('geodir_geo_compass_size')){$style .= 'font-size: '.$size.'px;';}
	if($margin_left = get_option('geodir_geo_compass_margin_left')){$style .= 'margin-left: '.$margin_left.'px;';}
	if($margin_top = get_option('geodir_geo_compass_margin_top')){$style .= 'margin-top: '.$margin_top.'px;';}
	
	?>
    <script>
	
	jQuery('.snear').each(function(){
   var $this = jQuery(this);
    
   jQuery('<span class="near-compass" data-dropdown=".gd-near-me-dropdown"></span>').css({
       position:'absolute',
       left: $this.offset().left + $this.outerWidth() - $this.outerHeight()*0.95, //10 for extra spacing from edge
       top: $this.offset().top + ($this.outerHeight()-$this.outerHeight()*0.95)/2,
	   fontSize: $this.outerHeight()*0.95
   }).html('<i class="fa fa-compass"></i>').data('inputEQ', $this.index()).insertAfter($this);
});
	
	
	jQuery(window).resize(function() {
								  //jQuery( ".near-compass" ).remove();
  jQuery('.snear').each(function(){
   var $this = jQuery(this);
    
   jQuery($this ).next('.near-compass').css({
       position:'absolute',
       left: $this.offset().left + $this.outerWidth() - $this.outerHeight()*0.95, //10 for extra spacing from edge
       top: $this.offset().top + ($this.outerHeight()-$this.outerHeight()*0.95)/2,
	   fontSize: $this.outerHeight()*0.95
   });
});
});
	</script>
    <?php
	
	
	//echo '<span class="near-compass" data-dropdown=".gd-near-me-dropdown"><i class="fa fa-compass"></i></span>';
	//echo '<span class="near-compass" data-dropdown=".gd-near-me-dropdown"  style="'.$style.'"><i class="fa fa-compass"></i></span>';
}


add_filter('wp_footer' , 'geodir_advance_search_near_me_form_html' , 10) ;
function geodir_advance_search_near_me_form_html()
{//print_r($_SESSION);global $geodir_addon_list;print_r($geodir_addon_list);
if(isset($_SESSION['near_me_range']) && $_SESSION['near_me_range']){$dist =  $_SESSION['near_me_range'];}
else if($dist = get_option('geodir_near_me_dist')){}else{$dist =  '200';}
?>
<div class="gd-near-me-dropdown dropdown dropdown-tip dropdown-anchor-right">
	<div class="dropdown-panel">
    	<div class="gd-advanced-s-menu-near">
        <span class="gdas-menu-near-me-left gdas-menu-left"><?php echo __('Near me', GEODIRADVANCESEARCH_TEXTDOMAIN);?></span>
        <span class="gdas-menu-near-me-right gdas-menu-right"><input type="checkbox" <?php if((isset($_SESSION['my_location']) && $_SESSION['my_location']) || (isset($_SESSION['user_lat']) && $_SESSION['user_lat'] && isset($_SESSION['user_lon']) && $_SESSION['user_lon'])){echo 'checked="checked"';}?> class="gt_near_me_s" onclick="gdGetLocation(jQuery(this));" /></span>
        </div>
        
        <div class="gd-advanced-s-menu-range">
        <span class="gdas-menu-left"><input type="range" name="gdas-range" class="gdas-range" min="1" max="200" onchange="gdasShowRange(jQuery(this));" value="<?php echo $dist;?>"></span>
        <span class="gdas-menu-right gdas-range-value-out"><?php echo $dist;?></span>
        </div>
    </div>
</div>
<?php
}
// filter for listing page title
add_filter('geodir_listing_page_title', 'geodir_advance_search_listing_page_title', 2, 1);
function geodir_advance_search_listing_page_title($list_title) {
	if (!geodir_is_page('search')) {
		return $list_title;
	}
	$gd_post_type = geodir_get_current_posttype();
	$post_type_info = get_post_type_object($gd_post_type);
		
	if(trim(get_search_query())=='') {
		$list_title = __('Search', GEODIRECTORY_TEXTDOMAIN).' '.__(ucfirst($post_type_info->labels->name), GEODIRECTORY_TEXTDOMAIN ).__(' :',GEODIRECTORY_TEXTDOMAIN);
	}
	
	if (!get_option('geodir_search_display_searched_params')) {
		return $list_title;
	}
	
	$custom_fields = geodir_advance_search_get_advance_search_fields($gd_post_type);
	$search_title = array();
	if (isset($_REQUEST['snear']) && $_REQUEST['snear'] != '') {
		$search_title[] = '<label class="gd-adv-search-label gd-adv-search-near">'.$_REQUEST['snear'].'</label>';
	}
	if (!empty($custom_fields)) {
		foreach($custom_fields as $custom_field) {
			$site_htmlvar_name = $custom_field->site_htmlvar_name;
			$field_site_name = $custom_field->field_site_name;
			$field_site_type = $custom_field->field_site_type;
			$front_search_title = $custom_field->front_search_title!='' ? $custom_field->front_search_title : $field_site_name;
			$field_input_type = $custom_field->field_input_type;
			$search_condition = $custom_field->search_condition;
			$field_data_type = $custom_field->field_data_type;
									
			switch($field_input_type) {
				case 'RANGE': {
					switch($search_condition) {
						case 'SINGLE': {
							if (isset($_REQUEST['s'.$site_htmlvar_name]) && $_REQUEST['s'.$site_htmlvar_name] != '') {
								$search_title[] = '<label class="gd-adv-search-label gd-adv-search-range gd-adv-search-'.$site_htmlvar_name.'">'.$_REQUEST['s' . $site_htmlvar_name].'</label>';
							}
						}
						break;
						case 'FROM': {
							$minvalue = isset($_REQUEST['smin'.$site_htmlvar_name]) && !empty($_REQUEST['smin'.$site_htmlvar_name]) ? $_REQUEST['smin'.$site_htmlvar_name] : ''; 
							$maxvalue = isset($_REQUEST['smax'.$site_htmlvar_name]) && !empty($_REQUEST['smax'.$site_htmlvar_name]) ? $_REQUEST['smax'.$site_htmlvar_name] : '';
							$this_search = '';
							if ($minvalue != '' && $maxvalue != '') {
								$this_search = $minvalue.' - '.$maxvalue;
							} else if ($minvalue != '' && $maxvalue == '') {
								$this_search = __('From:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$minvalue;
							} else if ($minvalue == '' && $maxvalue != '') {
								$this_search = __('To:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$maxvalue;
							}
							
							if ($this_search != '') {
								$search_title[] = '<label class="gd-adv-search-label gd-adv-search-range gd-adv-search-'.$site_htmlvar_name.'">'.$this_search.'</label>';
							}
						}		
						break;
						case 'RADIO': {
							if (isset($_REQUEST['s'.$site_htmlvar_name]) && $_REQUEST['s'.$site_htmlvar_name] != '') {
								$uom = get_option('geodir_search_dist_1');	
								$search_title[] = '<label class="gd-adv-search-label gd-adv-search-range gd-adv-search-'.$site_htmlvar_name.'">'.__('Within', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.(int)$_REQUEST['s'.$site_htmlvar_name].' '.__($uom, GEODIRECTORY_TEXTDOMAIN).'</label>';
							}
						}
						break;
						default : {
							if (isset($_REQUEST['s'.$site_htmlvar_name]) && $_REQUEST['s'.$site_htmlvar_name] != '') {
								$serchlist =  explode("-", $_REQUEST['s'.$site_htmlvar_name]);
								if (!empty($serchlist)) {
									$first_value = $serchlist[0];
									$second_value = isset($serchlist[1]) ? trim($serchlist[1], ' ') : '';
									$rest = substr($second_value, 0, 4); 
									
									$this_search = '';
									if ($rest == 'Less') {
										$this_search = __('To:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$first_value;
									} else if ($rest == 'More') {
										$this_search = __('From:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$first_value;
									} else if ($second_value != '') {
										$this_search = $first_value.' - '.$second_value;
									}
									
									if ($this_search != '') {
										$search_title[] = '<label class="gd-adv-search-label gd-adv-search-range gd-adv-search-'.$site_htmlvar_name.'">'.$this_search.'</label>';
									}
								}
							}
						}
						break;
					}
				}
				break;
				case 'DATE': {
					$search_date = '';
					$single = '';
					$value = isset($_REQUEST['s'.$site_htmlvar_name]) && !empty($_REQUEST['s'.$site_htmlvar_name]) ? $_REQUEST['s'.$site_htmlvar_name] : '';
					if($value) {
						$minvalue = $value;
						$maxvalue = '';
						$single = '1';
					} else {
						$minvalue = isset($_REQUEST['smin'.$site_htmlvar_name]) && !empty($_REQUEST['smin'.$site_htmlvar_name]) ? $_REQUEST['smin'.$site_htmlvar_name] : ''; 
						$maxvalue = isset($_REQUEST['smax'.$site_htmlvar_name]) && !empty($_REQUEST['smax'.$site_htmlvar_name]) ? $_REQUEST['smax'.$site_htmlvar_name] : '';
					}
				
					if ($site_htmlvar_name == 'event') {
						$event_start = isset($_REQUEST['event_start']) && !empty($_REQUEST['event_start']) ? $_REQUEST['event_start'] : ''; 
						$event_end = isset($_REQUEST['event_end']) && !empty($_REQUEST['event_end']) ? $_REQUEST['event_end'] : ''; 
						if ($event_start != '' && $event_end == '') {
							$this_search = __('From:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$event_start;
						} else if ($event_start == '' && $event_end != '') {
							$this_search = __('To:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$event_end;
						} else if ($event_start != '' && $event_end != '') {
							$this_search = $event_start.' - '.$event_end;
						}
						
						if ($this_search != '') {
							$search_title[] = '<label class="gd-adv-search-label gd-adv-search-date gd-adv-search-'.$site_htmlvar_name.'">'.$this_search.'</label>';
						}
					} else if( $field_data_type == 'DATE' || $field_data_type == 'TIME' ) {
						$start_date = date( 'Y-m-d', strtotime( $minvalue ) );
						$start_end = date( 'Y-m-d', strtotime( $maxvalue ) );
						
						if( $single == '1' ) {
							$search_title[] = '<label class="gd-adv-search-label gd-adv-search-date gd-adv-search-'.$site_htmlvar_name.'">'.$minvalue.'</label>';
						} else {
							$this_search = '';
							if ($minvalue != '' && $maxvalue == '') {
								$this_search = __('From:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$minvalue;
							} else if ($minvalue == '' && $maxvalue != '') {
								$this_search = __('To:', GEODIRADVANCESEARCH_TEXTDOMAIN).' '.$maxvalue;
							} else if ($minvalue != '' && $maxvalue != '') {
								$this_search = $minvalue.' - '.$maxvalue;
							}
							
							if ($this_search != '') {
								$search_title[] = '<label class="gd-adv-search-label gd-adv-search-date gd-adv-search-'.$site_htmlvar_name.'">'.$this_search.'</label>';
							}
						}		
					}
				}
				break;
				default: {
					if (isset($_REQUEST['s'.$site_htmlvar_name]) && ((is_array($_REQUEST['s'.$site_htmlvar_name]) && !empty($_REQUEST['s'.$site_htmlvar_name])) || (!is_array($_REQUEST['s'.$site_htmlvar_name]) && $_REQUEST['s'.$site_htmlvar_name]!=''))) {
						$value = $_REQUEST['s'.$site_htmlvar_name];
						if (is_array($_REQUEST['s'.$site_htmlvar_name])) {
							$values = $_REQUEST['s'.$site_htmlvar_name];
							$value = '';
							if ($site_htmlvar_name==$gd_post_type.'category') {
								$value = array();
								foreach ($values as $value_id) {
									$value_term = get_term($value_id, $site_htmlvar_name);
									if (!empty($value_term) && isset($value_term->name)) {
										$value[] = $value_term->name;
									}
								}
								$value = !empty($value) ? implode(', ', $value) : '';
							} else {
								$field_option_values = geodir_advance_search_field_option_values($gd_post_type, $site_htmlvar_name);
								if (!empty($field_option_values)) {
									$value = array();
									foreach ($field_option_values as $option_value) {
										$option_attr = explode("/",$option_value);
										$option_val = isset($option_attr[1]) ? $option_attr[1] : $option_attr[0];
										$option_label = isset($option_attr[0]) ? $option_attr[0] : '';

										if ($option_label != '' && $option_val!='' && in_array($option_val, $_REQUEST['s'.$site_htmlvar_name])) {
											$value[] = __(ucfirst($option_label), GEODIRECTORY_TEXTDOMAIN);
										}
									}
									$value = !empty($value) ? implode(', ', $value) : '';
								} else {
									$value = implode(', ', $values);
								}
							}
						} else {
							if ($site_htmlvar_name==$gd_post_type.'category') {
								$value = '';
								$value_term = get_term($_REQUEST['s'.$site_htmlvar_name], $site_htmlvar_name);
								if (!empty($value_term) && isset($value_term->name)) {
									$value = $value_term->name;
								}
							} else {
								$field_option_values = geodir_advance_search_field_option_values($gd_post_type, $site_htmlvar_name);
								if (!empty($field_option_values)) {
									$value = array();
									foreach ($field_option_values as $option_value) {
										$option_attr = explode("/",$option_value);
										$option_val = isset($option_attr[1]) ? $option_attr[1] : $option_attr[0];
										$option_label = isset($option_attr[0]) ? $option_attr[0] : '';

										if ($option_label != '' && $option_val!='' && $option_val == $_REQUEST['s'.$site_htmlvar_name]) {
											$value[] = __(ucfirst($option_label), GEODIRECTORY_TEXTDOMAIN);
										}
									}
									$value = !empty($value) ? implode(', ', $value) : '';
								}
								
								if ($field_site_type=='checkbox' && (int)$_REQUEST['s'.$site_htmlvar_name]==1) {
									$value = __($front_search_title, GEODIRADVANCESEARCH_TEXTDOMAIN);
								}
							}
						}
						
						if ($value!='') {
							$search_title[] = '<label class="gd-adv-search-label gd-adv-search-default gd-adv-search-'.$site_htmlvar_name.'">'.$value.'</label>';
						}
					}
				}
				break;
			}
		}
	}
	
	if (!empty($search_title)) {
		$search_title = '<div class="gd-adv-search-labels">'.implode($search_title, '').'</div>';
		$search_title = apply_filters('geodir_advance_search_filter_title', $search_title);
		$list_title .= $search_title;
	}
		
	return $list_title;
}

add_action('wp_footer', 'geodir_advance_search_init_script');
function geodir_advance_search_init_script() {
	if (geodir_is_page('search') && get_option('geodir_search_display_searched_params')) {
	?>
<style>
.gd-adv-search-labels{
display:inline-block;
margin-left:10px;
font-weight:normal;
line-height:normal;
vertical-align:middle;
position:relative
}
.gd-adv-search-labels label.gd-adv-search-label{
font-size:57%;
padding:0 5px 1px 5px;
min-width:20px;
display:inline-block;
text-align:center;
-moz-border-radius:3px;
-webkit-border-radius:3px;
border-radius:3px;
vertical-align:middle;
border:1px solid #aaa;;
background-color:#e4e4e4;
background-image:-webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(20%, #f4f4f4), color-stop(50%, #f0f0f0), color-stop(52%, #e8e8e8), color-stop(100%, #eeeeee));
background-image:-webkit-linear-gradient(#f4f4f4 20%, #f0f0f0 50%, #e8e8e8 52%, #eeeeee 100%);
background-image:-moz-linear-gradient(#f4f4f4 20%, #f0f0f0 50%, #e8e8e8 52%, #eeeeee 100%);
background-image:-o-linear-gradient(#f4f4f4 20%, #f0f0f0 50%, #e8e8e8 52%, #eeeeee 100%);
background-image:linear-gradient(#f4f4f4 20%, #f0f0f0 50%, #e8e8e8 52%, #eeeeee 100%);
background-clip:padding-box;
box-shadow:0 0 2px white inset, 0 1px 0 rgba(0, 0, 0, 0.05);
color:#333;
line-height:normal;
margin-right:7px;
}
</style>
	<?php
	}
}

add_filter('geodir_design_settings', 'geodir_advance_search_design_settings', 1);
function geodir_advance_search_design_settings($design_settings) {
	if (!empty($design_settings)) {
		$new_settings = array();
		foreach ($design_settings as $setting) {
			if (isset($setting['id']) && $setting['id']=='geodir_search_layout' && isset($setting['type']) && $setting['type']=='sectionend') {
				$extra_setting = array(  
										'name' 	=> __('Display searched parameters with title', GEODIRECTORY_TEXTDOMAIN),
										'desc' 	=> __('Enable to display searched parameters with title when searching for a custom field.'),
										'id' 	=> 'geodir_search_display_searched_params',
										'type' 	=> 'checkbox',
										'std' 	=> '0'
									);
				
				$new_settings[] = $extra_setting;
			}
			$new_settings[] = $setting;
		}
		$design_settings = $new_settings;
	}
	
	return $design_settings;
}

if(isset($_REQUEST['geodir_search']) && $_REQUEST['geodir_search']){
add_filter('init' , 'geodir_change_loc_on_search' , 10);
}

function geodir_change_loc_on_search(){
	global $wpdb;
	if(isset($_REQUEST['set_location_type']) && $_REQUEST['set_location_type'] && isset($_REQUEST['set_location_val']) && $_REQUEST['set_location_val']){
	$location_type = $_REQUEST['set_location_type'];
	$location_val = $_REQUEST['set_location_val'];
	$_SESSION['my_location']=0;// we are not suing users location anymore
	
		$loc_arr = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".POST_LOCATION_TABLE." WHERE location_id=%d",$location_val));
		//print_r($_SESSION);
		//print_r($loc_arr );
		if($location_type==1){// country
		$_SESSION['gd_country'] = $loc_arr->country_slug;
		$_SESSION['gd_region'] = '' ;
		$_SESSION['gd_city'] = '' ;	
		}
		elseif($location_type==2){// region
		$_SESSION['gd_country'] = $loc_arr->country_slug ;
		$_SESSION['gd_region'] = $loc_arr->region_slug ;
		$_SESSION['gd_city'] = '' ;
		}
		elseif($location_type==3){// city
		$_SESSION['gd_country'] = $loc_arr->country_slug ;
		$_SESSION['gd_region'] = $loc_arr->region_slug ;
		$_SESSION['gd_city'] = $loc_arr->city_slug ;
		}

		
	}elseif(isset($_REQUEST['geodir_search']) && $_REQUEST['geodir_search'] && isset($_REQUEST['snear']) && strpos($_REQUEST['snear'],'Near:') !== false ){
		// if using user GPS then blank location
		$_SESSION['gd_country'] = '';
		$_SESSION['gd_region'] = '' ;
		$_SESSION['gd_city'] = '' ;	
		
	}
	

}

// add db check to GD Tools
add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_advance_search_fields', 10,1); 

function geodir_diagnose_multisite_conversion_advance_search_fields($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_custom_advance_search_fields'] = __('Custom advanced search fields',GEODIRCLAIM_TEXTDOMAIN);
	return $table_arr;
}











?>
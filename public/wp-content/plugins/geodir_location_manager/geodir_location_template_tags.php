<?php
	
function geodir_location_default_option_form($tab_name)
{
	switch ($tab_name)
	{
		
		case 'geodir_location_setting' :
			
			geodir_admin_fields( geodir_location_default_options() );?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRLOCATION_TEXTDOMAIN ); ?>" />
			<input type="hidden" name="location_ajax_action" value="settings">
			</p>
			</div>
			
			<?php
			
		break;
	
	}// end of switch
}

//////////// Location new template tag function ///

function geodir_location_breadcrumb( $breadcrumb, $saprator, $echo= false ) {
	global $wp; 
	
	if ( geodir_is_page( 'location' ) ) {
		$saprator = str_replace( ' ', '&nbsp;', $saprator );
		$location_link = geodir_get_location_link('base');
		$location_prefix = get_option('geodir_location_prefix');
		
		$breadcrumb = '';	
		$breadcrumb .= '<div class="geodir-breadcrumb clearfix"><ul id="breadcrumbs">';
     	$breadcrumb .= '<li><a href="'.get_option('home').'">' . __( 'Home', GEODIRLOCATION_TEXTDOMAIN ) . '</a></li>';
		$breadcrumb .= '<li>'.$saprator;
		$breadcrumb .= '<a href="' . $location_link . '">' . GD_LOCATION . '</a>';
		$breadcrumb .= '</li>';
			
		/*
		$gd_country = (isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country'] !='') ? $wp->query_vars['gd_country'] : '' ;	
		$gd_region = (isset($wp->query_vars['gd_region']) && $wp->query_vars['gd_region'] !='') ? $wp->query_vars['gd_region'] : '' ;
		$gd_city = (isset($wp->query_vars['gd_city']) && $wp->query_vars['gd_city'] !='') ? $wp->query_vars['gd_city'] : '' ;
		*/	
		$locations = geodir_get_current_location_terms();
		
		$breadcrumb .= '<li>';
			
		foreach ( $locations as $key => $location ) {
			if ( get_option('permalink_structure') != '' ) {
				$location_link .= $location;
			}
			else {
				$location_link .= '&'.$key.'='.$location;
			}
			
			$location_link = geodir_location_permalink_url( $location_link );
			
			$location = urldecode( $location );
			
			$location_actual_text = '';
			if ($key=='gd_country' && $location_actual = get_actual_location_name('country', $location)) {
				$location_actual_text = get_actual_location_name('country', $location, true);
			} else if ($key=='gd_region' && $location_actual = get_actual_location_name('region', $location)) {
				$location_actual_text = get_actual_location_name('region', $location, true);
			} else if ($key=='gd_city' && $location_actual = get_actual_location_name('city', $location)) {
				$location_actual_text = get_actual_location_name('city', $location, true);
			}
				
			if ( $location != end($locations ) ) {	
				$location = preg_replace('/-(\d+)$/', '',  $location);
				$location = preg_replace('/[_-]/', ' ', $location);
				$location = ucwords( $location );
				$location = __( $location, GEODIRECTORY_TEXTDOMAIN );
				$location_text = $location_actual_text!='' ? $location_actual_text : $location;
				$breadcrumb .= $saprator.'<a href="'.$location_link.'">' . $location_text .'</a>';
			} else {
				$location = preg_replace('/-(\d+)$/', '',  $location);
				$location = preg_replace('/[_-]/', ' ', $location);
				$location = ucwords( $location );
				$location = __( $location, GEODIRECTORY_TEXTDOMAIN );
				$location_text = $location_actual_text!='' ? $location_actual_text : $location;
				$breadcrumb .= $saprator. $location_text ;
			}
		}
		
		$breadcrumb .= '</li>';
		$breadcrumb .=  '</ul></div>';
	}
	
	if ( $echo ) {
		echo $breadcrumb;
	} else {
		return $breadcrumb;
	}
}


// New functions added from - 23rd may

function geodir_get_current_location($args = null)
{
	$defaults = array(
		'what' => '',
		'location_text' => '',
		'blank_location_text' => '', 
		'with_link' => false, 
		'link_traget' => '',
		'container' => '' , 
		'container_class' => '' ,
		'switcher_link' => false,
		'echo'=> true
		 
	);
	
	// location picker config arguments
	$c_l_config = wp_parse_args( $args, $defaults );
	

	global $wpdb ;
	$order_by = '';
	$location = '';
	$what_lower = strtolower($c_l_config['what']) ;
	
	if( empty($location) && $c_l_config['what']=='')
	{
	
		if( empty($location) && isset($_SESSION['gd_multi_location']) )
		{
			if(isset($_SESSION['gd_city']) && $_SESSION['gd_city'] != '')
			{
				$gd_city = $_SESSION['gd_city']; 
				$location = $wpdb->get_var($wpdb->prepare("select city from ".POST_LOCATION_TABLE." where city_slug=%s",array($gd_city)));
				if($c_l_config['what']=='')
					$what_lower = 'city' ;
					
			}
			elseif(isset($_SESSION['gd_region']) && $_SESSION['gd_region'])
			{
				$gd_region = $_SESSION['gd_region']; 
				$location = $wpdb->get_var($wpdb->prepare("select region from ".POST_LOCATION_TABLE." where region_slug=%s ",array($gd_region)));
				if($c_l_config['what']=='')
					$what_lower = 'region' ;
			}
			elseif(isset($_SESSION['gd_country']) && $_SESSION['gd_country'])
			{
				$gd_country = $_SESSION['gd_country']; 
				$location = $wpdb->get_var($wpdb->prepare("select country from ".POST_LOCATION_TABLE." where country_slug=%s ",array($gd_country)));
				if($c_l_config['what']=='')
					$what_lower = 'country' ;
			}
		
		}	
	}
	
	if( empty($location) && isset($_SESSION['gd_multi_location']) )
	{
		if(isset($_SESSION['gd_' .$what_lower ]) && $_SESSION['gd_'.$what_lower ] != '')
		{
			$gd_location = $_SESSION['gd_' .$what_lower ]; 
			$location = $wpdb->get_var($wpdb->prepare("select $what_lower from ".POST_LOCATION_TABLE." where ".$what_lower."_slug=%s",array($gd_location)));
		}
	}	
	
	
	if($location!='' && $c_l_config['location_text'] != '')
		$location = $c_l_config['location_text'] ; 
	else if($location=='')
		$location = $c_l_config['blank_location_text'] ;
	
	
	
	$location_link = '' ;
	$link_a_tag_start = '' ; 
	$link_a_tag_end = '';
	$base_location= geodir_get_location_link('base');
	if($c_l_config['with_link'])		
	{
		$location_link = $base_location;
		$locations = array();
		if($what_lower=='city')
		{
			//if(get_option('geodir_show_location_url') == 'all')
			{		
				if(isset($_SESSION['gd_country']) && $_SESSION['gd_country']!='')
					$locations['gd_country'] = $_SESSION['gd_country'];
				if(isset($_SESSION['gd_region']) && $_SESSION['gd_region']!='')
					$locations['gd_region'] =  $_SESSION['gd_region'];
							
			}
			
			if(isset($_SESSION['gd_city'])  && $_SESSION['gd_city']!='')
				$locations['gd_city'] = $_SESSION['gd_city'];
		}
		
		if($what_lower=='region' && get_option('geodir_show_location_url') == 'all' )
		{
				if(isset($_SESSION['gd_country']) &&  $_SESSION['gd_country']!='')
					$locations['gd_country'] =  $_SESSION['gd_country'];
				if(isset($_SESSION['gd_region'])&& $_SESSION['gd_region']!='')
					$locations['gd_region'] = $_SESSION['gd_region'];
		}
		
		if($what_lower=='country' && get_option('geodir_show_location_url') == 'all')
		{
				if(isset($_SESSION['gd_country']) && $_SESSION['gd_country']!='')
					$locations['gd_country'] =  $_SESSION['gd_country'];
		}
		
		//print_r($locations) ;
		foreach($locations as $key => $location)
		{
					
			if ( get_option('permalink_structure') != '' )
				$location_link .= $location;
			else	
				$location_link .= '&'.$key.'='.$location;
		}
		
		$location_link = geodir_location_permalink_url( $location_link );	
		
		if($c_l_config['link_traget'] != '')
			$link_traget = " target=\"".$link_traget."\" " ;
		$link_a_tag_start = "<a href=\"".$location_link  ."\"  >" ; 
		$link_a_tag_end = "</a>" ;
	}
	
	
	if($location!='')	
		$location_with_link  =  $link_a_tag_start.$location.$link_a_tag_end;
	else
		$location_with_link  = '';
		
	if($c_l_config['container'] != '')
		$location_with_link = "<" . $c_l_config['container'] . " class='" .   $c_l_config['container_class'] . "' >". $location_with_link ;
		 
	if($c_l_config['switcher_link'])
	{
		$location_with_link .= "<a href=\"$base_location\"><span class=\"geodir_switcher\" title=\"". __('Click to change location' ,   GEODIRLOCATION_TEXTDOMAIN) ."\">&nbsp;</span></a>";
	}
	
	if($c_l_config['container'] != '')
		$location_with_link .= "</" . $c_l_config['container'] . ">" ;
		
	if($c_l_config['echo'])
		echo $location_with_link ;
	else
		return  $location_with_link ;
		
}


function geodir_get_location_switcher($args = null)
{
	$defaults = array(
		'country_default_list' => '',
		'country_text_filter' => true,
		'country_column' => true, 
		'region_default_list' => '', 
		'region_text_filter' => true,
		'region_column' => true,
		'city_default_list' => '', 
		'city_text_filter' => true,
		'city_column' => true, 
		 
	);
	
	// location picker config arguments
	$l_p_config = wp_parse_args( $args, $defaults );
	
	if(get_option('geodir_enable_country') =='default')
		$l_p_config['country_column'] = false ; 
	
	if(get_option('geodir_enable_region') =='default')
		$l_p_config['region_column'] = false ; 
			
	$base_location_link = geodir_get_location_link('base');
?>
	<div class="geodir_locListing_main">
    	<div class="geodir-common geodir_loc_clearfix">
			<div class="geodir-locListing_column" style="display:<?php echo  ( $l_p_config['country_column']  ? ''  :  'none' ) ?>;" >                        
                         <h2><?php _e('Country' , GEODIRLOCATION_TEXTDOMAIN);?></h2>
                              <input name="loc_pick_country_filter" type="text"  style="display:<?php echo  ( $l_p_config['country_text_filter']  ? ''  :  'none' ) ?>;" />
                         <ul class="geodir_country_column">
                         	<?php 
								$country_args = array('what' => 'country' ,
								'city_val' => '', 
								'region_val' => '',
								'country_val' => '' ,
								'compare_operator' =>'in' ,
								'country_column_name' => 'country' ,
								'region_column_name' => 'region' ,
								'city_column_name' => 'city' ,
								'location_link_part' => true ,
								'order_by' => ' asc ',
								'no_of_records' => '',
								'format' => array('type' => 'array')
								) ;
								$country_loc_array = geodir_get_location_array($country_args);
								
								if(!empty($country_loc_array))
								{
									foreach($country_loc_array as $country_item )
									{
								?>
                              <li class="geodir_loc_clearfix">
                                <a href="<?php echo geodir_location_permalink_url( $base_location_link . $country_item->location_link );?>"><?php echo __( $country_item->country, GEODIRECTORY_TEXTDOMAIN ) ;?></a>
                                <span class="geodir_loc_arrow"><a href="javascript:void(0);">&nbsp;</a></span>
                              </li>
                              	<?php
                                	} // end of foreach
								}//end of if
								?>
                         </ul>
                        
                      </div>
                      <div class="geodir-locListing_column" style="display:<?php echo  ( $l_p_config['region_column']  ? ''  :  'none' ) ?>;">
                         <h2><?php _e('Region' , GEODIRLOCATION_TEXTDOMAIN);?></h2>
                              <input name="loc_pick_region_filter"  type="text"  style="display:<?php echo  ( $l_p_config['region_text_filter']  ? ''  :  'none' ) ?>;" />
                         <ul class="geodir_region_column">
                              <?php 
								$region_args = array('what' => 'region' ,
								'city_val' => '', 
								'region_val' => '',
								'country_val' => '' ,
								'compare_operator' =>'in' ,
								'country_column_name' => 'country' ,
								'region_column_name' => 'region' ,
								'city_column_name' => 'city' ,
								'location_link_part' => true ,
								'order_by' => ' asc ',
								'no_of_records' => '',
								'format' => array('type' => 'array')
								) ;
								$region_loc_array = geodir_get_location_array($region_args);
								if(!empty($region_loc_array))
								{
									foreach($region_loc_array as $region_item )
									{
								?>
                              <li class="geodir_loc_clearfix">
                                <a href="<?php echo geodir_location_permalink_url( $base_location_link . $region_item->location_link );?>"><?php echo __( $region_item->region, GEODIRECTORY_TEXTDOMAIN ) ;?></a>
                                <span class="geodir_loc_arrow"><a href="javascript:void(0);">&nbsp;</a></span>
                              </li>
                              	<?php
                                	} // end of foreach
								}//end of if
								?>
                         </ul>
                      </div>
                      <div class="geodir-locListing_column geodir-locListing_column_last" style="display:<?php echo  ( $l_p_config['city_column']  ? ''  :  'none' ) ?>;">
                         <h2><?php _e('City' , GEODIRLOCATION_TEXTDOMAIN);?></h2>
                              <input  name="loc_pick_city_filter"  type="text"  style="display:<?php echo  ( $l_p_config['city_text_filter']  ? ''  :  'none' ) ?>;" />
                         <ul class="geodir_city_column">
                         
                             <?php 
								$city_args = array('what' => 'city' ,
								'city_val' => '', 
								'region_val' => '',
								'country_val' => '' ,
								'compare_operator' =>'in' ,
								'country_column_name' => 'country' ,
								'region_column_name' => 'region' ,
								'city_column_name' => 'city' ,
								'location_link_part' => true ,
								'order_by' => ' asc ',
								'no_of_records' => '',
								'format' => array('type' => 'array')
								) ;
								$city_loc_array = geodir_get_location_array($city_args);
								if(!empty($city_loc_array))
								{
									foreach($city_loc_array as $city_item )
									{
								?>
                              <li class="geodir_loc_clearfix">
                                <a href="<?php echo geodir_location_permalink_url(  $base_location_link . $city_item->location_link );?>"><?php echo __( $city_item->city, GEODIRECTORY_TEXTDOMAIN ) ;?></a> </li>
                              	<?php
                                	} // end of foreach
								}//end of if
								?>
                         </ul>
                      </div>
        </div>
        
     </div>
     <span><?php _e('Click on a link to filter results or on arrow to drilldown.' , GEODIRLOCATION_TEXTDOMAIN )?></span>
<?php
	
} 

function geodir_get_location_list($args=null)
{
	$base_location = geodir_get_location_link('base') ;
	$country_list = geodir_get_location_array(array('what'=> 'country', 'format'=>array('type'=> 'array')));
	if(!empty($country_list))
	{
?>
		<ul class="geodir_all_location">
<?php 
		foreach($country_list as $country)
		{
	?>
   		 	<li>
         		<h2><a href="<?php echo geodir_location_permalink_url( $base_location . $country->location_link );?>"><?php echo $country->country; ?></a></h2>
              	<?php $region_list = geodir_get_location_array(array('what'=> 'region', 'country_val' => $country->country, 'format'=>array('type'=> 'array')));
					if(!empty($region_list))
					{
				?>		<ul class="geodir_states">	
                		<?php 
							foreach($region_list as $region)
							{
							?>
                            	<li class="geodir_region">
                                	 <h3><a href="<?php echo geodir_location_permalink_url( $base_location . $region->location_link )?>"><?php echo $region->region; ?></a></h3>
                           		<?php	$city_list = geodir_get_location_array(array('what'=> 'city', 'country_val' => $country->country,'region_val'=> $region->region, 'format'=>array('type'=> 'array')));
										if(!empty($city_list))
										{
										?>	
                                        	 <ul class="geodir_cities clearfix">          
                               		      	<?php 
											foreach($city_list as $city)
											{
											?>
                                            	 <li><a href="<?php echo geodir_location_permalink_url( $base_location . $city->location_link )?>"><?php echo $city->city; ?></a></li>
                                            <?php
                                            } // end of city list foreach
											?>
                                         	</ul> 	 	
                                     	<?php
										}// end of city list if
                                        ?>
								</li><?php // end of state list item?>
                        <?php 
							} // end of state foreach
					}// end of region list if
						?>
                 
            </li><?php // end of country list item?>
    <?php
		} // end of country foreach
?>		</ul>
<?php	
	}// end of country list if
	 
}

function geodir_location_tab_switcher($args = null)
{
	$switcher = !empty($args) && isset( $args['addSearchTermOnNorecord'] ) ? true : false;
	
	if(get_option('geodir_enable_country') !='default'  || get_option('geodir_enable_region') !='default' || get_option('geodir_enable_city') !='default')
	{
		$defaults = array('echo' => true, 'addSearchTermOnNorecord' => 0, 'autoredirect'=> false);
		$args = wp_parse_args( $args, $defaults );
		
		global $wpdb;
		
		// Options
		$echo = $args['echo'];
		$addSearchTermOnNorecord = $args['addSearchTermOnNorecord'];
		$autoredirect = $args['autoredirect'];
		
		$output = '';
		$selected = '';
		$location_list = '';
		$country_div = '';
		$region_div = '';
		$city_div = '';
		$onchange ='';
		
		$what_is_current_location = geodir_what_is_current_location();
		$what_is_current_location_div = ($what_is_current_location.'_div');
		if($what_is_current_location!='')
			$$what_is_current_location_div = 'gd-tab-active' ;
		else
		{
			$what_is_current_location = 'city';
			$city_div = 'gd-tab-active';
		}
		$location_value = '';
		if($autoredirect==='0'){}
		else{
			$location_value = geodir_get_location_link('base');
			$onchange = ' onchange="window.location.href=this.value" ';
			$autoredirect = '1';
		}
		
			
		
		
		$base_location = geodir_get_location_link('base') ;
		$current_location_array;
		$selected = '' ; 
		$country_val = '';
		$region_val = '';
		$city_val = '';
		$country_val=geodir_get_current_location(array('what'=>'country', 'echo'=>false)) ;
		$region_val=geodir_get_current_location(array('what'=>'region', 'echo'=>false)) ;
		$city_val = geodir_get_current_location(array('what'=>'city', 'echo'=>false)) ;;
		$item_set_selected = false ;
		
		$output.= '<div class="geodir_location_tab_container" >';
		$output.= '<dl class="geodir_location_tabs_head">';
	
		if(get_option('geodir_enable_country') !='default' ):
			$output.= '<dd data-location="country" class="geodir_location_tabs '.$country_div.'" ><a href="javascript:void(0)">'. __('Country',GEODIRLOCATION_TEXTDOMAIN).'</a></dd>';
		endif;
		
		if(get_option('geodir_enable_region') !='default' ):
			$output.= '<dd data-location="region" class="geodir_location_tabs '.$region_div.'" ><a href="javascript:void(0)">'. __('Region',GEODIRLOCATION_TEXTDOMAIN).'</a></dd>';
		endif;
		if(get_option('geodir_enable_city') !='default' ):
			$output.= '<dd data-location="city" class="geodir_location_tabs '.$city_div.' " ><a href="javascript:void(0)">'. __('City',GEODIRLOCATION_TEXTDOMAIN).'</a></dd>';
			$output.= '</dl>';
		endif;
		$output.= '<input type="hidden" class="selected_location" value="city" /><div style="clear:both;"></div>';
		$output.= '<div class="geodir_location_sugestion">';
		$output.= '<select class="geodir_location_switcher_chosen" name="gd_location" data-placeholder="'.__('Please wait..&hellip;', GEODIRLOCATION_TEXTDOMAIN).'" data-addSearchTermOnNorecord="'.$addSearchTermOnNorecord.'" data-autoredirect="'.$autoredirect.'" '.$onchange.' data-showeverywhere="1" >'; 
				
				$location_switcher_list_mode = get_option('geodir_location_switcher_list_mode');
				if(empty($location_switcher_list_mode))
					$location_switcher_list_mode='drill' ;
				
				if($location_switcher_list_mode=='drill')
				{	
					$args=array(
									'what'=>$what_is_current_location , 
									'country_val' => (strtolower($what_is_current_location)=='region' || strtolower($what_is_current_location)=='city') ? $country_val : '',
									'region_val' =>(strtolower($what_is_current_location)=='city') ? $region_val : '',
									'echo' => false,
									'no_of_records' => '5',
									'format'=> array('type'=>'array')
								);
				}
				else
				{
					$args=array(
									'what'=>$what_is_current_location , 
									'echo' => false,
									'no_of_records' => '5',
									'format'=> array('type'=>'array')
								);
				}
				
				
				if($what_is_current_location=='country' && $country_val!='')
				{
					$args_current_location =array(
							'what'=>$what_is_current_location , 
							'country_val' => $country_val ,
							'compare_operator' =>'=' ,
							'no_of_records' => '1',
							'echo' => false,
							'format'=> array('type'=>'array')
							);
					$current_location_array= geodir_get_location_array($args_current_location, $switcher);
				}
					
				if($what_is_current_location =='region' && $region_val!='')
				{
					$args_current_location =array(
							'what'=>$what_is_current_location  , 
							'country_val' => $country_val ,
							'region_val' =>$region_val,
							'compare_operator' =>'=' ,
							'no_of_records' => '1',
							'echo' => false,
							'format'=> array('type'=>'array')
							);
						
					$current_location_array= geodir_get_location_array($args_current_location, $switcher);
				}
				if($what_is_current_location =='city' && $city_val !='')
				{
					$args_current_location =array(
							'what'=>$what_is_current_location  , 
							'country_val' => $country_val ,
							'region_val' =>$region_val,
							'city_val' => $city_val,
							'compare_operator' =>'=' ,
							'no_of_records' => '1',
							'echo' => false,
							'format'=> array('type'=>'array')
							);
					$current_location_array= geodir_get_location_array($args_current_location, $switcher);
				
				}				
				$location_array= geodir_get_location_array($args, $switcher);
				// get country val in case of country search to get selected option
				
				
				if( get_option( 'geodir_everywhere_in_' . $what_is_current_location . '_dropdown' ) ) {
					$output .= '<option value="'.$base_location.'">' . __( 'Everywhere', GEODIRLOCATION_TEXTDOMAIN ) . '</option>';	
				}
				
				$selected = '' ; 
				if( !empty( $location_array ) ) {
					foreach( $location_array as $locations ) {
						$selected = '' ; 
						$with_parent = isset( $locations->label ) ? true : false;
						switch( $what_is_current_location ) {
							case 'country':
								if( strtolower( $country_val ) == strtolower( $locations->country ) ) {
									$selected = 'selected="selected"';
								}
								$locations->country = __( $locations->country, GEODIRECTORY_TEXTDOMAIN );
							break;
							case 'region':
								$country_iso2 = geodir_location_get_iso2( $country_val );
								$country_iso2 = $country_iso2 != '' ? $country_iso2 : $country_val;
								$with_parent = $with_parent && strtolower( $region_val . ', ' . $country_iso2 ) == strtolower( $locations->label ) ? true : false;
								if( strtolower( $region_val ) == strtolower( $locations->region ) || $with_parent ) {
									$selected = 'selected="selected"';
								}
							break;
							case 'city':
								$with_parent = $with_parent && strtolower( $city_val . ', ' . $region_val ) == strtolower( $locations->label ) ? true : false;
								if( strtolower( $city_val ) == strtolower( $locations->city ) || $with_parent ) {
									$selected = 'selected="selected"';
								}
							break;		
						}
						
						$output .= '<option value="' . geodir_location_permalink_url( $base_location . $locations->location_link ) . '" ' . $selected . '>' . ucwords( $locations->$what_is_current_location ) . '</option>';
						
						if( !$item_set_selected && $selected != '' ) {
							$item_set_selected = true;
						}
					}
				}			
				
				
				if( !empty( $current_location_array ) && !$item_set_selected ) {
					foreach( $current_location_array as $current_location ) {
						$selected = '' ; 
						$with_parent = isset( $current_location->label ) ? true : false;
						switch( $what_is_current_location ) {
							case 'country':
								if( strtolower( $country_val ) == strtolower( $current_location->country ) ) {
									$selected = 'selected="selected"';
								}
								$current_location->country = __( $current_location->country, GEODIRECTORY_TEXTDOMAIN );
							break;
							case 'region':
								$country_iso2 = geodir_location_get_iso2( $country_val );
								$country_iso2 = $country_iso2 != '' ? $country_iso2 : $country_val;
								$with_parent = $with_parent && strtolower( $region_val . ', ' . $country_iso2 ) == strtolower( $current_location->label ) ? true : false;
								if( strtolower( $region_val ) == strtolower( $current_location->region ) || $with_parent ) {
									$selected = 'selected="selected"';
								}
							break;
							case 'city':
								$with_parent = $with_parent && strtolower( $city_val . ', ' . $region_val ) == strtolower( $current_location->label ) ? true : false;
								if( strtolower( $city_val ) == strtolower( $current_location->city ) || $with_parent ) {
									$selected = 'selected="selected"';
								}
							break;			
						}
						
						$output .= '<option value="' . geodir_location_permalink_url( $base_location . $current_location->location_link ) . '" ' . $selected . '>' . ucwords( $current_location->$what_is_current_location ) . '</option>';
					}
				}
						
		$output .= '</select>'; 
		$output.= "</div>";
		$output.= '</div>';
		
		if($echo)
			echo $output;	
		else		
			return $output;	
	}
}

?>
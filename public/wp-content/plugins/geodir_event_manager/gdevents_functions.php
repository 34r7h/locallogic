<?php  


function geodir_event_manager_ajaxurl(){
	return admin_url('admin-ajax.php?action=geodir_event_manager_ajax');
}


function geodir_event_plugin_url() { 
	
	if (is_ssl()) : 
		return str_replace('http://', 'https://', WP_PLUGIN_URL) . "/" . plugin_basename( dirname(__FILE__)); 
	else :
		return WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)); 
	endif;
}


function geodir_event_plugin_path() {
	return WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__)); 
}


function geodir_event_add_listing_settings($arr){
	
	$event_array = array();
	foreach($arr as $key => $val){
		
		$event_array[] = $val;
		
		if($val['id'] == 'geodir_tiny_editor_on_add_listing'){
		
			$event_array[] = array(  
											'name' => __( 'Show event \'registration description\' field as editor', GEODIREVENTS_TEXTDOMAIN ),
												'desc' 		=> __( 'Select the option to show advanced editor on add listing page.', GEODIREVENTS_TEXTDOMAIN ),
												'tip' 		=> '',
												'id' 		=> 'geodir_tiny_editor_event_reg_on_add_listing',
												'css' 		=> 'min-width:300px;',
												'std' 		=> '',
												'type' 		=> 'select',
												'class'		=> 'chosen_select',
												'options' => array_unique( array( 
																			'' => __( 'Select', GEODIREVENTS_TEXTDOMAIN ),
																			'yes' => __( 'Yes', GEODIREVENTS_TEXTDOMAIN ),
																			'no' => __( 'No', GEODIREVENTS_TEXTDOMAIN ),
																		))
											);
	
		}
	}
	
	return $event_array;
}


function geodir_event_detail_page_sitebar_content($arr){
	
	$geodir_count = count($arr);
	
	$schedule_array = array();
	
	if(!empty($arr)){
		foreach($arr as $key => $val){
			
			if($geodir_count > 4){
				if( $key == 4)
					$schedule_array[] = 'geodir_event_show_shedule_date';
			}else{
				if( $key == $geodir_count)
					$schedule_array[] = 'geodir_event_show_shedule_date';
			}
			
			$schedule_array[] = $val;
		
		}
	}else{
		$schedule_array[] = 'geodir_event_show_shedule_date';
	}
	
	return $schedule_array;
}


function geodir_event_calender_search_page_title($title){
		
	global $condition_date;	
	
	if(isset($_REQUEST['event_calendar']) && !empty($_REQUEST['event_calendar']) && geodir_is_page('search'))
		$title = apply_filters('geodir_calendar_search_page_title', __(' Browsing Day', GEODIREVENTS_TEXTDOMAIN).'" '.date('F  d, Y',strtotime($condition_date)).'"');
	
	return $title;
}


function geodir_event_schedule_date_fields(){
	
	global $post;
	
	$recuring_data = array();
	
	if(isset($_REQUEST['backandedit'])){
		$post = (array)$post; 
		$recuring_data['Recurring'] = isset($post['Recurring']) ? $post['Recurring'] : '';
		$recuring_data['event_day'] = isset($post['event_day']) ? $post['event_day'] : '';
		$recuring_data['event_week'] = isset($post['event_week']) ? $post['event_week'] : '';
		$recuring_data['event_month'] = isset($post['event_month']) ? $post['event_month'] : '';
		$recuring_data['event_year'] = isset($post['event_year']) ? $post['event_year'] : '';
		$recuring_data['event_start'] = isset($post['event_start']) ? $post['event_start'] : '';
		$recuring_data['event_end'] = isset($post['event_end']) ? $post['event_end'] : '';
		
		$recuring_data['event_recurring_dates']	=isset($post['event_recurring_dates']) ? $post['event_recurring_dates'] : '';
		$recuring_data['different_times'] = isset($post['different_times']) ? $post['different_times'] : '';
		$recuring_data['starttime'] = isset($post['starttime']) ?$post['starttime'] : '';
		$recuring_data['endtime']	= isset($post['endtime']) ? $post['endtime'] : '';
		$recuring_data['starttimes']	= isset($post['starttimes']) ? $post['starttimes'] : '';
		$recuring_data['endtimes']	= isset($post['endtimes']) ? $post['endtimes'] : '' ;
		
	}else{
		
		$recuring_data = unserialize($post->recurring_dates); 
	}	
	
	geodir_event_show_event_fields_html($recuring_data);
 	
}


function geodir_event_save_data($post_id = '',$request_info){
	
	global $wpdb,$current_user; 
	
	$gd_post_info = array();
	
	$last_post_id = $post_id;	
	
	$post_type = get_post_type( $post_id );
	
	if($post_type != 'gd_event')
		return false;
		
	$gd_post_info['event_reg_desc']= isset($request_info['event_reg_desc']) ? $request_info['event_reg_desc'] : '';
	$gd_post_info['event_reg_fees']= isset($request_info['event_reg_fees']) ? $request_info['event_reg_fees'] : '';
	
	if(isset($request_info['event_date']) && $request_info['event_date'] != '')
		$request_info['event_recurring_dates'] = $request_info['event_date'];
		
	$event_shedule_info = array(
				"Recurring"	=> isset($request_info['Recurring']) ? $request_info['Recurring'] : '',
				"event_recurring_dates"		=> isset($request_info['event_recurring_dates']) ? $request_info['event_recurring_dates'] : '',
				"different_times"		=> isset($request_info['different_times']) ? $request_info['different_times'] : '',
				"starttime"	=> isset($request_info['starttime']) ?$request_info['starttime'] : '',
				"endtime"	=> isset($request_info['endtime']) ? $request_info['endtime'] : '',
				"starttimes"	=> isset($request_info['starttimes']) ? $request_info['starttimes'] : '',
				"endtimes"	=> isset($request_info['endtimes']) ? $request_info['endtimes'] : '' 
		);
	
	$gd_post_info['recurring_dates'] = serialize($event_shedule_info);
	
	geodir_save_event_schedule($event_shedule_info, $last_post_id); // to create event-schedule dates
	
	/* --- save businesses --- */
	if(isset($request_info['geodir_link_business'])){
		$gd_post_info['geodir_link_business'] = $request_info['geodir_link_business'];
	}
	
	geodir_save_post_info($last_post_id, $gd_post_info);
	
	return $last_post_id;
}	


function geodir_getDays($year, $startMonth=1, $startDay=1, $dayOfWeek='', $week_e = '', $check_full_start_year='', $check_full_end_year='') {
	
	$start = new DateTime(
			sprintf('%04d-%02d-%02d', $year, $startMonth, $startDay)
	);
	$start->modify($dayOfWeek);
	$end   = new DateTime(
			sprintf('%04d-12-31', $year)
	);
	$end->modify( '+1 day' );
	
	$interval = new DateInterval('P1W');
	$period   = new DatePeriod($start, $interval, $end);
	
	$dates_array = array();
	
	foreach ($period as $dt) {
		
		$date_explode = explode('-', $dt->format("Y-m-d"));
		
		$get_year = $date_explode[0];
		$get_month = $date_explode[1];
		$get_date = $date_explode[2];
		
		$check_get_date = date('Y-m-d', strtotime($get_year.'-'.$get_month.'-'.$get_date));
		
		if($get_month <= $startMonth)
		{
			if($week_e == '')
			{
				if($check_get_date <= $check_full_end_year && $check_get_date >= $check_full_start_year)
					$dates_array[] = $dt->format("Y-m-d");
			}
			
			
			$monthName = date("F", mktime(0, 0, 0, $get_month, 10));
			
			if($week_e != '')
			{
				$date_check = date("Y-m-d", strtotime("$week_e $dayOfWeek of $monthName $get_year"));
				
				if($date_check <= $check_full_end_year && $date_check >= $check_full_start_year)
					$dates_array[] = $date_check;
				
			}
		}
	
	}
	
	return $result = array_unique($dates_array);
}


function geodir_save_event_schedule($event_shedule_info = array(), $last_post_id=''){
		
		global $wpdb;
		
		if( empty($event_shedule_info) || $last_post_id=='')
			return false;
		
		if($event_shedule_info['Recurring'] == '1'){
		
			$start_year = date('Y', strtotime($event_shedule_info['event_start']));
			
			$end_year = date('Y', strtotime($event_shedule_info['event_end']));
			
			$event_week	= $event_shedule_info['event_week'];
			
			$event_day	= $event_shedule_info['event_day'];
			
			/* ------------- START GET YEARS ------------*/
			
			$all_years = $event_shedule_info['event_year'];
			
			$years_array = array();
			
			if(!empty($all_years) && !in_array("*", $all_years)) {
				foreach($all_years as $year)
				{ if($year >= $start_year && $year <= $end_year) $years_array[] = $year; }
			}else{ 
				for($i = $start_year; $i<=$end_year; $i++)
				{	$years_array[] = $i; }
			}
			
			/* ------------- END GET YEARS ------------*/
			
			/* ------------- START GET MONTHS ------------*/
			
			$event_months = $event_shedule_info['event_month'];
			
			$event_months_array = array();
			
			if(!empty($event_months) && !in_array("*",$event_months))
			{
					foreach($event_months as $evn_mnt)
					{ $event_months_array[] = $evn_mnt;  }
			}else
				$event_months_array = range(1,12);
			
			/* ------------- END GET MONTHS ------------*/
			$every_day_event = 1;
			
			if(!empty($event_day) && !in_array("*",$event_day))
				$every_day_event = 0;
			
			
			$check_start_year = date('Y-m', strtotime($event_shedule_info['event_start'])); //check start year and month
			
			$check_end_year = date('Y-m', strtotime($event_shedule_info['event_end'])); //check end year and month
			
			$check_full_start_year = date('Y-m-d', strtotime($event_shedule_info['event_start']));
			
			$check_full_end_year = date('Y-m-d', strtotime($event_shedule_info['event_end']));
			
			
			if(!empty($years_array)):
			
				foreach($years_array as $years):
					
					foreach($event_months_array as $months):
						
						$get_month = date('Y-m', strtotime($years.'-'.$months));
						
						if($get_month >= $check_start_year && $get_month <= $check_end_year) {
						
							if(!empty($event_week)) {
								foreach($event_week as $key => $week_d) {
									if($key == 'every')
										foreach($week_d as $w_day)
										{ $total_dates[] = geodir_getDays($years, $months, 1, $w_day, '', $check_full_start_year, $check_full_end_year); }
									else
										foreach($week_d as $w_day)
										{ $total_dates[] = geodir_getDays($years, $months, 1, $w_day, $key, $check_full_start_year, $check_full_end_year); }
									
								}
							}else{
								
								if($every_day_event == 1){
									for($i = 1; $i <= 31; $i++){
									
										$input = $years.'-'.$months.'-'.$i;
										
										$check_get_date = date('Y-m-d', strtotime($years.'-'.$months.'-'.$i));	
										
										if(checkdate($months, $i, $years) && $check_get_date <= $check_full_end_year && $check_get_date >= $check_full_start_year)
											$total_dates[][] = $input;
									}
								}else{
									
									if(!empty($event_day)){
										
										foreach($event_day as $e_days):
										
											$input = $years.'-'.$months.'-'.$e_days;
											$check_get_date = date('Y-m-d', strtotime($years.'-'.$months.'-'.$e_days));	
											if(checkdate($months, $e_days, $years) && $check_get_date <= $check_full_end_year && $check_get_date >= $check_full_start_year)
												$total_dates[][] = $input;
										endforeach;
									}//endif
								}//end elseif
							}//end elseif
						}//end if
					endforeach;	
				endforeach;	
			endif;
	
		/* ---------------- START EVENT SCHEDULE DATES ADD --------- */
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".EVENT_SCHEDULE." WHERE event_id=%d", array($last_post_id)));
	
		if(!empty($total_dates)){
			
			$unique_array = array();
			foreach($total_dates as $key => $dates):	
				if(is_array($dates) && !empty($dates)){
					foreach($dates as $dt)
						$unique_array[] = $dt;	
				}
			endforeach;
			
			
			if(!empty($unique_array)){
				$unique_array = array_unique($unique_array);
				
				foreach($unique_array as $u_dates){$wpdb->query($wpdb->prepare("INSERT INTO  ".EVENT_SCHEDULE." (event_id, event_date) VALUES (%d, %s)",array($last_post_id, $u_dates)));
				}
			}
			
		}
		
	}else{
	
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".EVENT_SCHEDULE." WHERE event_id=%d", array($last_post_id)));
		
		if(isset($event_shedule_info['event_recurring_dates']) && $event_shedule_info['event_recurring_dates'] != ''){
			
			$event_recurring_dates = explode(',', $event_shedule_info['event_recurring_dates']);
			
			$starttimes = isset($event_shedule_info['starttime']) ? $event_shedule_info['starttime'] : '';
			$endtimes = isset($event_shedule_info['endtime']) ? $event_shedule_info['endtime'] : '';
					
			foreach($event_recurring_dates as $key => $date){
				
				if($date != '')
					$date = date('y-m-d', strtotime($date));
				
				if(isset($event_shedule_info['different_times']) && $event_shedule_info['different_times'] == '1'){
					$starttimes = isset($event_shedule_info['starttimes'][$key]) ? $event_shedule_info['starttimes'][$key] : '';
					$endtimes = isset($event_shedule_info['endtimes'][$key]) ? $event_shedule_info['endtimes'][$key] : '';
				}
				
				$wpdb->query($wpdb->prepare("INSERT INTO  ".EVENT_SCHEDULE." (event_id, event_date, event_starttime, event_endtime) VALUES (%d, %s, %s, %s)",array($last_post_id, $date, $starttimes, $endtimes)));

				
			}
			
		}
		
	}
	
	
}

function geodir_event_delete_schedule($post_id){

	global $wpdb, $plugin_prefix;
	
	$post_type = get_post_type( $post_id );
	
	$all_postypes = geodir_get_posttypes();
	
	if(!in_array($post_type, $all_postypes))
		return false;
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".EVENT_SCHEDULE." WHERE event_id=%d", array($post_id)));
	
	$table_name = $plugin_prefix.'gd_event_detail';
	
	$wpdb->query($wpdb->prepare("UPDATE ".$table_name." SET geodir_link_business='' WHERE geodir_link_business=%s",array($post_id)));

}

function geodir_event_remove_illegal_htmltags($tags,$pkey){

	if($pkey == 'event_reg_desc'){
		$tags= '<p><a><b><i><em><h1><h2><h3><h4><h5><ul><ol><li><img><div><del><ins><span><cite><code><strike><strong><blockquote>';
	}
		
	return $tags;
}


function geodir_event_loop_filter_where($where) {
	global $wp_query,$query,$wpdb,$geodir_post_type,$table,$condition_date;
	
	$condition = '';
	$current_date = date('Y-m-d');
	
	if (get_query_var('event_related_id')) {
		
		if (get_query_var('geodir_event_type') == 'feature')
			$condition = " event_date >= '".$current_date."' ";
		
		if (get_query_var('geodir_event_type') == 'past')
			$condition = " event_date <= '".$current_date."' ";
			
		if (get_query_var('geodir_event_type') == 'upcoming')
			$condition = " event_date >= '".$current_date."' ";
		
		$where .= " AND ".$table.".geodir_link_business = ".get_query_var('event_related_id');
	}
	
	if (get_query_var('geodir_event_date_calendar')) {
		
		$current_year = date('Y', get_query_var('geodir_event_date_calendar'));
		$current_month = date('m', get_query_var('geodir_event_date_calendar'));
		
		$condition = " YEAR(event_date) = ".$current_year." AND MONTH(event_date) = ".$current_month." ";
	}
	
	
	if ($condition) {
			
		$find_postids = $wpdb->get_var("SELECT GROUP_CONCAT( DISTINCT event_id SEPARATOR \"','\") as event_ids FROM ".EVENT_SCHEDULE." WHERE ".$condition);
		
		$event_ids = "'".$find_postids."'";
		
		if (get_query_var('geodir_event_date_calendar')) {
			$where .= " AND $wpdb->posts.ID IN ($event_ids)";
		} else {
			$where .= " AND $wpdb->posts.ID IN ($event_ids)";
		}
	}
	// for dashboard listing
	$is_geodir_dashbord = isset($_REQUEST['geodir_dashbord']) && $_REQUEST['geodir_dashbord'] ? true : false;
	if ( ( is_main_query() && $geodir_post_type == 'gd_event' && (geodir_is_page('listing') || is_search() || $is_geodir_dashbord) ) || get_query_var('geodir_event_listing_filter')) {
		
		$filter = isset($_REQUEST['etype']) ? $_REQUEST['etype'] : '';
		
		if($filter == '')
			$filter = get_option('geodir_event_defalt_filter');
		
		if(get_query_var('geodir_event_listing_filter'))
			$filter = get_query_var('geodir_event_listing_filter');
		
		if($filter == 'today')
			$where .= " AND ".EVENT_SCHEDULE.".event_date = '".$current_date."' ";
			
		if($filter == 'upcoming')
			$where .= " AND ".EVENT_SCHEDULE.".event_date >= '".$current_date."' ";
		
		if($filter == 'past')
			$where .= " AND ".EVENT_SCHEDULE.".event_date < '".$current_date."' ";
		
		if(isset($_REQUEST['event_start']) && !empty($_REQUEST['event_start']) && !isset($_REQUEST['event_end'])){
		
			$where .= " AND ".EVENT_SCHEDULE.".event_date = '".date('Y-m-d',strtotime($_REQUEST['event_start']))."' ";	
		
		}else{
		
			if(isset($_REQUEST['event_start']) && !empty($_REQUEST['event_start']))
				$where .= " AND ".EVENT_SCHEDULE.".event_date >= '".date('Y-m-d',strtotime($_REQUEST['event_start']))."' ";
				
			if(isset($_REQUEST['event_end']) && !empty($_REQUEST['event_end']))
				$where .= " AND ".EVENT_SCHEDULE.".event_date <= '".date('Y-m-d',strtotime($_REQUEST['event_end']))."' ";
		}
		
		
		
		if(isset($_SESSION['all_near_me'])){
			global $plugin_prefix,$wp_query;
			//print_r($wp_query);
			
			if(!$geodir_post_type){$geodir_post_type = $wp_query->query_vars['post_type'];}
			$table = $plugin_prefix . $geodir_post_type . '_detail';
			$DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
			$mylat = $_SESSION['user_lat'];
			$mylon = $_SESSION['user_lon'];
			
			if(isset($_SESSION['near_me_range']) && is_numeric($_SESSION['near_me_range'])){$dist =$_SESSION['near_me_range']; }
			elseif(get_option('geodir_near_me_dist')!=''){$dist = get_option('geodir_near_me_dist');}
			else{ $dist = '200';  }
			
			$lon1 = $mylon-$dist/abs(cos(deg2rad($mylat))*69); 
			$lon2 = $mylon+$dist/abs(cos(deg2rad($mylat))*69);
			$lat1 = $mylat-($dist/69);
			$lat2 = $mylat+($dist/69);
			
			$rlon1 = is_numeric(min($lon1,$lon2)) ? min($lon1,$lon2) : '';
			$rlon2 = is_numeric(max($lon1,$lon2)) ? max($lon1,$lon2) : '';
			$rlat1 = is_numeric(min($lat1,$lat2)) ? min($lat1,$lat2) : '';
			$rlat2 = is_numeric(max($lat1,$lat2)) ? max($lat1,$lat2) : '';
			
			$where .= " AND ( ".$table.".post_latitude between $rlat1 and $rlat2 ) 
						AND ( ".$table.".post_longitude between $rlon1 and $rlon2 ) ";
		}
		
		
	}
	
	if( is_search() && isset($_REQUEST['geodir_search']) && isset($_REQUEST['event_calendar'])  && is_main_query()){
		
		$condition_date = substr($_REQUEST['event_calendar'],0,4).'-'.substr($_REQUEST['event_calendar'],4,2).'-'.substr($_REQUEST['event_calendar'],6,2);
		
		$condition = " event_date = '".date('Y-m-d',strtotime($condition_date))."' ";
		
		if($condition){
				$where .= " And $condition " ;
		}
		
	}
	
	
	
	
	
	
	
	return $where;
}

function geodir_event_date_calendar_fields( $fields ) {
	global $query, $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $fields;
	}
	
	$schedule_table = EVENT_SCHEDULE;
		
	if ( get_query_var( 'geodir_event_date_calendar' ) ) {
		$current_year = date( 'Y', get_query_var( 'geodir_event_date_calendar' ) );
		$current_month = date( 'm', get_query_var( 'geodir_event_date_calendar' ) );
		
		$condition = " YEAR(event_date) = ".$current_year." AND MONTH(event_date) = ".$current_month." ";
		$fields = " (SELECT GROUP_CONCAT( DISTINCT ".$schedule_table.".event_date) FROM ".$schedule_table." WHERE ".$condition.") AS event_dates";
	} else {
		if ( ( is_main_query() && ( geodir_is_page( 'listing' ) || ( is_search() && isset($_REQUEST['geodir_search'])) || isset($_REQUEST['geodir_dashbord'] ) ) ) || get_query_var( 'geodir_event_listing_filter' ) ) {
			$fields .= ", ".$table.".*".", ".EVENT_SCHEDULE.".* ";
		}
	}
	
	
	if(isset($_SESSION['all_near_me'])){
			$DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
			$mylat = $_SESSION['user_lat'];
			$mylon = $_SESSION['user_lon'];
			
			
			
			$fields .= " , (".$DistanceRadius." * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(".$table.".post_latitude)) * pi()/180 / 2), 2) +COS(ABS($mylat) * pi()/180) * COS( ABS(".$table.".post_latitude) * pi()/180) *POWER(SIN(($mylon - ".$table.".post_longitude) * pi()/180 / 2), 2) )))as distance ";
		}
	
	return $fields;
}


function geodir_event_date_calendar_join($join)
{
	global $wpdb,$query,$geodir_post_type,$table,$table_prefix,$plugin_prefix, $gdevents_widget;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $join;
	}
	
	$schedule_table = EVENT_SCHEDULE;
	if(((is_main_query() && (geodir_is_page('listing') || ( is_search() && isset($_REQUEST['geodir_search'])))) || get_query_var('geodir_event_date_calendar') || isset($_REQUEST['geodir_dashbord'])) || get_query_var('geodir_event_listing_filter')){
		if ( !geodir_is_geodir_page() && $gdevents_widget ) {
			$geodir_post_type = 'gd_event';
			$table = $plugin_prefix . $geodir_post_type . '_detail';
			$join .= " INNER JOIN ".$table." ON (".$table.".post_id = $wpdb->posts.ID)  " ;
		}
		$join .= " INNER JOIN ".$schedule_table." ON (".$schedule_table.".event_id = $wpdb->posts.ID)  " ;
	}

	return $join;
}


function geodir_event_posts_order_by_sort($orderby, $sort_by, $table){
	global $query, $geodir_post_type,$wpdb;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $orderby;
	}
	
	if(((is_main_query() && (geodir_is_page('listing') || ( is_search() && isset($_REQUEST['geodir_search']))) || get_query_var('geodir_event_date_calendar') || isset($_REQUEST['geodir_dashbord']))) || get_query_var('geodir_event_listing_filter')){
	
		$orderby .= " ".$wpdb->prefix."geodir_event_schedule.event_date asc,  ".$wpdb->prefix."geodir_event_schedule.event_starttime asc , ";
		
	}
	
	return $orderby;
	
}

function geodir_event_posts_order_by_sort_distance($orderby){
	global $query, $geodir_post_type,$wpdb;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $orderby;
	}
	
	if(((is_main_query() && (geodir_is_page('listing') || ( is_search() && isset($_REQUEST['geodir_search']))) || get_query_var('geodir_event_date_calendar') || isset($_REQUEST['geodir_dashbord']))) || get_query_var('geodir_event_listing_filter')){
	
		if(isset($_SESSION['all_near_me'])){
		$orderby =	" distance, ".$orderby;
		}
		
	}
	
	return $orderby;
	
}


function geodir_event_loop_filter_groupby( $groupby )
{
  global $wp_query,$query,$wpdb,$geodir_post_type,$table,$condition_date;
	
	if($geodir_post_type == 'gd_event' && is_main_query() && geodir_is_page('listing')){
		$groupby = " $wpdb->posts.ID," . EVENT_SCHEDULE . ".event_date";
	}elseif(get_query_var('geodir_event_date_calendar')){
		 $groupby = ' event_id';
	}
	
  return $groupby;
}

function geodir_event_loop_filter($query){
	
	global $wp_query,$geodir_post_type;
	
	if ( isset($query->query_vars['is_geodir_loop']) && $query->query_vars['is_geodir_loop'] && ($geodir_post_type=='gd_event' || get_query_var('geodir_event_date_calendar') || get_query_var('geodir_event_listing_filter'))) {
		
			add_filter('posts_fields', 'geodir_event_date_calendar_fields' ,1 );
			add_filter('posts_join', 'geodir_event_date_calendar_join',1);
			add_filter('geodir_posts_order_by_sort', 'geodir_event_posts_order_by_sort', 2, 3);
			add_filter('posts_where', 'geodir_event_loop_filter_where', 2);
			add_filter('posts_groupby', 'geodir_event_loop_filter_groupby' );
			//add_filter('posts_orderby', 'geodir_event_posts_order_by_sort_distance' ,1 );
	}
	
	return $query;
}


function geodir_event_cat_post_count_join($join,$post_type){
	global $plugin_prefix;
	if($post_type == 'gd_event')
	{
		$join .= ", ".$plugin_prefix."event_schedule sch ";
	}
	
	return $join;
}

add_filter('geodir_cat_post_count_join', 'geodir_event_cat_post_count_join', 1, 2);

function geodir_event_cat_post_count_where($where, $post_type){
	global $plugin_prefix;
	$current_date = date('Y-m-d');
	
	if($post_type == 'gd_event')
	{
		$table_name = $plugin_prefix.$post_type.'_detail';
		
		$where .= " AND ".$table_name.".post_id=sch.event_id AND sch.event_date >= '".$current_date."' ";
	}
	
	
	return $where;
}

add_filter('geodir_cat_post_count_where', 'geodir_event_cat_post_count_where',1 ,2);

function geodir_event_fill_listings( $term ) {
	//$listings = geodir_event_get_my_listings( 'gd_place', $term );
	$listings = geodir_event_get_my_listings( 'all', $term );
	$options = '<option value="">' . __( 'No Business', GEODIREVENTS_TEXTDOMAIN ) . '</option>';
	if( !empty( $listings ) ) {
		foreach( $listings as $listing ) {
			$options .= '<option value="' . $listing->ID . '">' . $listing->post_title . '</option>';
		}
	}
	return $options;
}

function geodir_event_manager_ajax(){

	$task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : '';
	switch( $task ) {
		case 'geodir_fill_listings' :
			$term = isset( $_REQUEST['term'] ) ? $_REQUEST['term'] : '';
			echo geodir_event_fill_listings( $term );
			exit;
		break;
	}
	
	if(isset($_REQUEST['event_type']) && $_REQUEST['event_type'] == 'calendar'){
		geodir_event_display_calendar(); exit;
	}

	if(isset($_REQUEST['gd_event_general_settings'])){
		geodir_update_options( geodir_event_general_setting_options() );
		
		$msg = 'Your settings have been saved.';
		
		$msg = urlencode($msg);
		
			$location = admin_url()."admin.php?page=geodirectory&tab=gd_event_fields_settings&subtab=gd_event_general_options&event_success=".$msg;
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['auto_fill']) && $_REQUEST['auto_fill'] == 'geodir_business_autofill'){
		
		if(isset($_REQUEST['place_id']) && $_REQUEST['place_id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_link_business_autofill_nonce' ) )
						exit;
			
			geodir_business_auto_fill($_REQUEST);
			exit;
			
		}else{
		
			wp_redirect(home_url().'/?geodir_signup=true');
			exit();
		}
		
	}
	
}

function geodir_business_auto_fill($request){

	if(!empty($request)){
		
		$place_id = $request['place_id'];
		$post_type = get_post_type( $place_id );
		$package_id = geodir_get_post_meta($place_id,'package_id',true);
		$custom_fields = geodir_post_custom_fields($package_id,'all',$post_type); 
		
		$json_array = array();
		
		$content_post = get_post($place_id);
		$content = $content_post->post_content;
		
		$json_array['post_title'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_title',true));
		
		$json_array['post_desc'] = array(	'key' => 'textarea', 
																			'value' => $content);
		
		
		foreach($custom_fields as $key=>$val){
			
			$type = $val['type'];
			
			switch($type){
			
				case 'phone':
				case 'email':
				case 'text':
				case 'url':
					
					$value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
					$json_array[$val['htmlvar_name']] = array('key' => 'text', 'value' => $value);
					
				break;
				
				case 'textarea':
					
					$value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
					$json_array[$val['htmlvar_name']] = array('key' => 'textarea', 'value' => $value);
					
				break;
				
				case 'address':
					
					$json_array['post_address'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_address',true));
					$json_array['post_zip'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_zip',true));
					$json_array['post_latitude'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_latitude',true));
					$json_array['post_longitude'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_longitude',true));
					
					
					$extra_fields = unserialize($val['extra_fields']);
					
					$show_city = isset($extra_fields['show_city']) ? $extra_fields['show_city'] : '';
					
					if($show_city){

						$json_array['post_country'] = array('key' => 'text',
																				'value' => geodir_get_post_meta($place_id,'post_country',true));
						$json_array['post_region'] = array('key' => 'text',
																				'value' => geodir_get_post_meta($place_id,'post_region',true));
						$json_array['post_city'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_city',true));
						
					}
					
					
				break;
				
			}
			
		}

	}
	
	if(!empty($json_array))
		echo json_encode($json_array);

	
}


function geodir_wp_default_date_time_format()
{
	return get_option('date_format'). ' ' .	get_option('time_format');
}

function geodir_get_cal_trans_array()
{

	$cal_trans = array('month_long_1' => __( 'January',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_2' => __( 'February',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_3' => __( 'March',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_4' => __( 'April',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_5' => __( 'May',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_6' => __( 'June',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_7' => __( 'July',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_8' => __( 'August',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_9' => __( 'September',GEODIREVENTS_TEXTDOMAIN ), 
'month_long_10' => __( 'October' ,GEODIREVENTS_TEXTDOMAIN), 
'month_long_11' => __( 'November' ,GEODIREVENTS_TEXTDOMAIN), 
'month_long_12' => __( 'December' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_1' => __( 'Jan',GEODIREVENTS_TEXTDOMAIN ), 
'month_s_2' => __( 'Feb',GEODIREVENTS_TEXTDOMAIN ), 
'month_s_3' => __( 'Mar',GEODIREVENTS_TEXTDOMAIN ), 
'month_s_4' => __( 'Apr' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_5' => __( 'May' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_6' => __( 'Jun' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_7' => __( 'Jul',GEODIREVENTS_TEXTDOMAIN ), 
'month_s_8' => __( 'Aug' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_9' => __( 'Sep' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_10' => __( 'Oct' ,GEODIREVENTS_TEXTDOMAIN), 
'month_s_11' => __( 'Nov',GEODIREVENTS_TEXTDOMAIN ), 
'month_s_12' => __( 'Dec',GEODIREVENTS_TEXTDOMAIN ), 
'day_s1_1' => __( 'S' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s1_2' => __( 'M' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s1_3' => __( 'T' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s1_4' => __( 'W' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s1_5' => __( 'T' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s1_6' => __( 'F' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s1_7' => __( 'S' ,GEODIREVENTS_TEXTDOMAIN),
'day_s2_1' => __( 'Su' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s2_2' => __( 'Mo' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s2_3' => __( 'Tu',GEODIREVENTS_TEXTDOMAIN ), 
'day_s2_4' => __( 'We',GEODIREVENTS_TEXTDOMAIN ), 
'day_s2_5' => __( 'Th',GEODIREVENTS_TEXTDOMAIN ), 
'day_s2_6' => __( 'Fr' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s2_7' => __( 'Sa' ,GEODIREVENTS_TEXTDOMAIN),
'day_s3_1' => __( 'Sun',GEODIREVENTS_TEXTDOMAIN ), 
'day_s3_2' => __( 'Mon' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s3_3' => __( 'Tue' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s3_4' => __( 'Wed',GEODIREVENTS_TEXTDOMAIN ), 
'day_s3_5' => __( 'Thu' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s3_6' => __( 'Fri' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s3_7' => __( 'Sat' ,GEODIREVENTS_TEXTDOMAIN),
'day_s5_1' => __( 'Sunday',GEODIREVENTS_TEXTDOMAIN ), 
'day_s5_2' => __( 'Monday' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s5_3' => __( 'Tuesday',GEODIREVENTS_TEXTDOMAIN ), 
'day_s5_4' => __( 'Wednesday',GEODIREVENTS_TEXTDOMAIN ), 
'day_s5_5' => __( 'Thursday',GEODIREVENTS_TEXTDOMAIN ), 
'day_s5_6' => __( 'Friday' ,GEODIREVENTS_TEXTDOMAIN), 
'day_s5_7' => __( 'Saturday' ,GEODIREVENTS_TEXTDOMAIN),

's_previousMonth' => __( 'Previous Month' ),
's_nextMonth' => __( 'Next Month' ),
's_close' => __( 'Close' ));

return $cal_trans;
}

function geodir_event_link_businesses( $post_id, $post_type, $arr = false ) {
	global $wpdb, $plugin_prefix;
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$sql = $wpdb->prepare(
		"SELECT post_id FROM " . $table . " WHERE post_status=%s AND geodir_link_business=%d", array( 'publish', $post_id )
	);
	
	$rows = $wpdb->get_results($sql);
	
	$result = array();
	if ( !empty( $rows ) ) {
		foreach ($rows as $row) {
			$result[] = $row->post_id;
		}
	}
		
	return $result;
}

function geodir_event_link_businesses_data( $post_ids ) {
	global $wpdb, $plugin_prefix;
	
	$table = $plugin_prefix . 'gd_event_detail';
	if ( $post_ids == '' || ( is_array( $post_ids ) && empty( $post_ids ) ) ) {
		return NULL;
	}
	$post_ids = is_array( $post_ids ) ? implode( ",", $post_ids ) : $posts;
	
	$limit = get_option('geodir_related_post_count');
	$list_sort = get_option('geodir_related_post_sortby');
	$character_count = (int)get_option('geodir_related_post_excerpt');
	
	$current_date = date('Y-m-d');
	$limit = $limit < 1 || $limit > 20 ? 5 : $limit;
		
	$sql =  $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS p.*, gde.*, gdes.*
		FROM " . $wpdb->posts . " AS p
		INNER JOIN " . $table ." AS gde ON (gde.post_id = p.ID)
		INNER JOIN " . EVENT_SCHEDULE . " AS gdes ON (gdes.event_id = p.ID)
		WHERE p.ID IN (%s)
			AND p.post_type = 'gd_event'
			AND p.post_status = 'publish'
			AND gdes.event_date >= %s
		ORDER BY gdes.event_date ASC, gdes.event_starttime ASC , gde.is_featured ASC, p.post_date DESC, p.post_title ASC
		LIMIT %d", array( $post_ids, $current_date, $limit) );
	
	$rows = $wpdb->get_results($sql);
	
	return $rows;
}

function geodir_event_display_link_business() {
	global $post;
	$post_type = geodir_get_current_posttype();
	$all_postypes = geodir_get_posttypes();
		
	if ( !empty( $post ) && $post_type == 'gd_event' && geodir_is_page( 'detail' ) && isset( $post->geodir_link_business ) && !empty( $post->geodir_link_business ) ) {
		$linked_post_id = $post->geodir_link_business;
		$linked_post_info = get_post($linked_post_id);
		if( !empty( $linked_post_info ) ) {
			$linked_post_type_info = in_array( $linked_post_info->post_type, $all_postypes ) ? geodir_get_posttype_info( $linked_post_info->post_type )  : array();
			if( !empty( $linked_post_type_info ) ) {
				$linked_post_type_name = isset( $linked_post_type_info['labels']['singular_name'] ) ? trim( $linked_post_type_info['labels']['singular_name'] ) : '';
				$linked_post_type_name = $linked_post_type_name != '' ? $linked_post_type_name : 'Place';
			
				$linked_post_url = get_permalink($linked_post_id);
				
				$html_link_business = '<div class="geodir_more_info geodir_more_info_even geodir_link_business"><span class="geodir-i-website"><i class="fa fa-link"></i> <a title="' . stripslashes_deep( $linked_post_info->post_title ) . '" href="'.$linked_post_url.'">' . __( 'Go to', GEODIREVENTS_TEXTDOMAIN ) . ' ' . __( $linked_post_type_name, GEODIRECTORY_TEXTDOMAIN ) . '</a></span></div>';
				
				echo apply_filters( 'geodir_more_info_link_business', $html_link_business, $linked_post_id, $linked_post_url );
			}
		}
	}
}

function geodir_event_get_my_listings( $post_type = 'all', $search = '', $limit = 5 ) {
	global $wpdb, $current_user;
	
	if( empty( $current_user->ID ) ) {
		return NULL;
	} 
	$geodir_postypes = geodir_get_posttypes();

	$search = trim( $search );
	$post_type = $post_type != '' ? $post_type : 'all';
	
	if( $post_type == 'all' ) {
		$geodir_postypes = implode( ",", $geodir_postypes );
		$condition = $wpdb->prepare( " AND FIND_IN_SET( post_type, %s )" , array( $geodir_postypes ) );
	} else {
		$post_type = in_array( $post_type, $geodir_postypes ) ? $post_type : 'gd_place';
		$condition = $wpdb->prepare( " AND post_type = %s" , array( $post_type ) );
	}
	$condition .= !current_user_can( 'manage_options' ) ? $wpdb->prepare( "AND post_author=%d" , array( (int)$current_user->ID ) ) : '';
	$condition .= $search != '' ? $wpdb->prepare( " AND post_title LIKE %s", array( $search . '%%' ) ) : "";
	
	$orderby = " ORDER BY post_title ASC";
	$limit = " LIMIT " . (int)$limit;
	
	$sql = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = %s AND post_type != 'gd_event' " . $condition . $orderby . $limit, array( 'publish' ) );
	$rows = $wpdb->get_results($sql);
	
	return $rows;
}

add_filter('geodir_filter_widget_listings_fields','geodir_filter_event_widget_listings_fields',10,3);
function geodir_filter_event_widget_listings_fields($fields,$table,$post_type){
	global $plugin_prefix;
	if($post_type=='gd_event'){
	$fields .= ", ".EVENT_SCHEDULE.".* ";	
	}
	return $fields;
}

add_filter('geodir_filter_widget_listings_join','geodir_filter_event_widget_listings_join',10,2);
function geodir_filter_event_widget_listings_join($join,$post_type){
	global $plugin_prefix,$wpdb;
	if($post_type=='gd_event'){
	$join .= " INNER JOIN ".EVENT_SCHEDULE." ON (".EVENT_SCHEDULE.".event_id = $wpdb->posts.ID) ";	
	}
	return $join;
}

add_filter('geodir_filter_widget_listings_where','geodir_filter_event_widget_listings_where',10,2);
function geodir_filter_event_widget_listings_where($where,$post_type){
	global $plugin_prefix,$wpdb;
	if($post_type=='gd_event'){
	$where .= " AND ".EVENT_SCHEDULE.".event_date >= '".date('Y-m-d')."' ";	
	}
	return $where;
}

add_filter('geodir_filter_widget_listings_orderby','geodir_filter_event_widget_listings_orderby',10,3);
function geodir_filter_event_widget_listings_orderby($orderby,$table,$post_type){
	global $plugin_prefix,$wpdb;
	if($post_type=='gd_event'){
	$orderby = " ".EVENT_SCHEDULE.".event_date asc,".EVENT_SCHEDULE.".event_starttime asc , ".EVENT_DETAIL_TABLE.".is_featured asc, $wpdb->posts.post_date desc, ";	
	}
	return $orderby;
}


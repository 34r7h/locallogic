<?php
/**
 * GeoDirectory Events Template Functions
 *
 * Functions used in the template files to output content - in most cases hooked in via the template actions.
 *
 * @package		GeoDirectory Events
 * @category	Core
 * @author		Vikas Sharma
 */


/* --------- event date format change  -------- */

function geodir_event_display_filter_options(){
	
	global $wp_query, $geodir_post_type, $paged;
	
	$filter_by = '';
	$filter_field_options = '';
	
	if(isset($_REQUEST['etype'])) $filter_by = $_REQUEST['etype'];
	
	$event_filters_opt = apply_filters( 'geodir_event_filter_options', array(
		'all' => __('All Events', GEODIREVENTS_TEXTDOMAIN),
		'today' => __('Today', GEODIREVENTS_TEXTDOMAIN),
		'upcoming' => __('Upcoming', GEODIREVENTS_TEXTDOMAIN),
		'past' => __('Past', GEODIREVENTS_TEXTDOMAIN),
	) );
	
	if($filter_by == '')
		$filter_by = get_option('geodir_event_defalt_filter');
	
	$current_link = esc_url(get_pagenum_link());
	$current_link = str_replace('#038;', '&',add_query_arg(array('etype'=>'all'), $current_link ));
	
	
	if(!empty($event_filters_opt)){
	
		foreach($event_filters_opt as $key => $opts){
			
			($filter_by == $key) ? $selected = 'selected="selected"' :  $selected = '';	
			
			$filter_field_options .= '<option '.$selected.' value="'.add_query_arg( array('etype'=>$key),$current_link ).'">'.$event_filters_opt[$key].'</option>';
			
		}
	
	}
	
	if($filter_field_options != ''){ ?>
		
		<div class="geodir-event-filter">
		
			<select name="etype" id="etype" onchange="javascript:window.location=this.value;">
				<?php echo $filter_field_options;?>
			</select>
		
		</div>
		<div style="clear:both"></div> <?php
	
	}
	
}


function time_select_options($select = '')
{
	$event_times = geodir_event_get_times();
	
	$all_times = '';
	foreach($event_times as $key => $times){
		$selected = ''; 
		if($select ==  $key || $select == $times || '0'.$select == $times)
			 $selected = 'selected="selected"';
		$all_times.= '<option '.$selected.' value="'.$key.'">'.$times.'</option>'; 
	}
	return $all_times;
}

function geodir_event_get_times() {

	$time_increment = apply_filters('geodir_event_time_increment' , 15) ;
	$event_time_array =array();
	for($i=0;$i<24 ; $i++ )
	{
		 for($j=0 ; $j < 60 ; $j+= $time_increment )
		 {
		 	$time_hr_abs = $i ;
			
		 	$time_am_pm = ' AM' ;
			
			if($i >=12)
			{
				$time_am_pm = ' PM' ;
				
			}
			
			if($i > 12)
				$time_hr_abs = $i - 12 ;	
			
					
		 	if($time_hr_abs<10)
				$time_hr = '0' . $time_hr_abs ;
			else
				$time_hr =  $time_hr_abs ;
				
			
			if($j<10)
				$time_min = '0' . $j ;
			else
				$time_min =  $j ;
					
			if($i<10)
				$time_hr_index = '0' . $i ;
			else
				$time_hr_index = $i ;
			
		 	$event_time_array[ $time_hr_index  . ":" . $time_min ] = $time_hr . ":" . $time_min . $time_am_pm  ;
			
		 }
	}
	
	return apply_filters( 'geodir_event_schedule_times' , $event_time_array);
	
}


function geodir_event_show_event_fields_html($recuring_data = array()){
	
	global $post;
	
	$starttime = '';
	$endtime = '';
	
	if(!empty($recuring_data)){
		$recuring = $recuring_data['Recurring'];
		$event_day = isset($recuring_data['event_day']) ? $recuring_data['event_day'] : '';
		$event_week = isset($recuring_data['event_week']) ? $recuring_data['event_week'] : '';
		$event_month = isset($recuring_data['event_month']) ? $recuring_data['event_month'] : '';
		$event_year = isset($recuring_data['event_year']) ? $recuring_data['event_year'] : '';
		
		
		$event_recurring_dates = isset($recuring_data['event_recurring_dates']) ? $recuring_data['event_recurring_dates'] : '';
		$different_times = isset($recuring_data['different_times']) ? $recuring_data['different_times'] : '';
    $starttime = isset($recuring_data['starttime']) ? $recuring_data['starttime'] : '';
    $endtime = isset($recuring_data['endtime']) ? $recuring_data['endtime'] : '';
		
	}
	
	$differnttimes_selected = '';
	$selected = '';
	if(isset($recuring_data['Recurring']) && ($recuring_data['Recurring'] == '0' || $recuring_data['Recurring'] == '')){
		$save_dates = array();
			$hide_dates = array();
			$differnttimes_selected = '';
			if(isset($event_recurring_dates) && $event_recurring_dates != '')
			{
				$event_recurring_dates1 = explode(',', $event_recurring_dates);
				foreach($event_recurring_dates1 as $key => $times){
				$save_dates[] = date('m/d/Y',strtotime($event_recurring_dates1[$key]));
				$hide_dates[] = $key;
				
				if($recuring_data['different_times']=='1'){
					$exttimes = $recuring_data['starttimes'];
					$extendtimes = $recuring_data['endtimes'];
					
					$exttimes_display = isset($exttimes[$key]) ? $exttimes[$key] : '';
					$extendtimes_display = isset($extendtimes[$key]) ? $extendtimes[$key] : '';
					
					$differnttimes_selected.='<div class="event-multiple-times clearfix"><label class="event-multiple-dateto">'.$event_recurring_dates1[$key].'</label><label class="event-multiple-start">'.__('Start', GEODIREVENTS_TEXTDOMAIN).'</label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="starttimes[]" class="chosen_select">'.time_select_options($exttimes_display).'</select></div><label class="event-multiple-end">'.__('End', GEODIREVENTS_TEXTDOMAIN).'</label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="endtimes[]" class="chosen_select" style="width:90px;">'.time_select_options($extendtimes_display).'</select></div></div>';
				}
			}
			}
			
			if(!empty($hide_dates))
				$imphide_dates = implode(',',$hide_dates);
			
			if(!empty($save_dates)){
				$impdata = implode(',',$save_dates);
				$selected = ", selected:'".$impdata."'";
			}
	}
	
	if(!is_admin())
		echo '<h5>'.__('Event Schedule', GEODIREVENTS_TEXTDOMAIN).'</h5>';
		
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	// i have set this value to 0 to remove recurring event option.
	$package_info->recurring_pkg = 0 ;
	
		if(isset($package_info->recurring_pkg) && $package_info->recurring_pkg == '1'){?>
    
    <div class="geodir_form_row clearfix">
        <label><?php _e('Recurring Event?', GEODIREVENTS_TEXTDOMAIN);?> <span>*</span> </label>
        <div class="geodir-category_label">
            <div class="geodir-form_cat">
						 <style>
							.hide{ z-index:-10; position:absolute; }
						 </style>
			   
              <input type="radio" class="geodir-checkbox" name="Recurring" id="Recurring2" <?php if(!isset($recuring) ||$recuring=='0' || $recuring == '' ){echo 'checked="checked"';}?> value="0"  /> <?php _e('No', GEODIREVENTS_TEXTDOMAIN);?>
               <input type="radio" class="geodir-checkbox" name="Recurring" id="Recurring1" <?php if(isset($recuring) && $recuring=='1' ){echo 'checked="checked"';}?>  value="1" /> <?php _e('Yes', GEODIREVENTS_TEXTDOMAIN);?>
            </div>
            <span class="geodir_message_error" id="Recurring_span"></span>
        </div>
    </div><?php }else{?>
		
			<input type="hidden" class="geodir-checkbox" name="Recurring" id="Recurring2" checked="checked" value="0"  /><?php
			
		}	?>
		
        
		<script type="text/javascript">
					var error_message = '';
					jQuery(document).ready(function(){
					
						error_message = jQuery("#NotRecurring_sh").find('span.geodir_message_error').html();
					
						jQuery('input[name=Recurring]').click(function(){
							
							jQuery('input[name=Recurring]').each(function(){
							
								if(jQuery(this).is(':checked') == true){
									if(jQuery(this).val() == '1'){
										
										jQuery('#Recurring_sh').show();
										jQuery('#NotRecurring_sh').hide();
										jQuery("select.chosen_select").chosen().trigger("chosen:updated");
									}else{
										jQuery('#Recurring_sh').hide();
										jQuery('#NotRecurring_sh').show();
									}
								}
								
							});
						
						});
						
						jQuery('input[name=Recurring]').each(function(){
							if(jQuery(this).is(':checked') == true)
								jQuery(this).click();
						});
						
						
						jQuery('.different_times').change(function(){
							
							if(jQuery(this).is(':checked') == false){
								jQuery('.show_times_div').hide();
								
							}else{
								jQuery('.show_times_div').show();
							}
							
							
							var event_dates = jQuery('#dates').val();
							
							if(event_dates!='')
							{
								var spdates = event_dates.split(",");
								var total_dates = spdates.length;
								var total_dates_selected='';
								
								for(i=0;i<total_dates;i++)
								{
									
									total_dates_selected+='<div class="event-multiple-times clearfix"><label class="event-multiple-dateto">'+spdates[i]+'</label><label class="event-multiple-start"><?php echo __('Start', GEODIREVENTS_TEXTDOMAIN);?></label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="starttimes[]" class="chosen_select"><?php echo time_select_options(); ?></select></div><label class="event-multiple-end"><?php echo __('End', GEODIREVENTS_TEXTDOMAIN);?></label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="endtimes[]" class="chosen_select" style="width:90px;"><?php echo time_select_options(); ?></select></div></div>';
								
								
								}
								jQuery('.show_different_times_div').html('');
								jQuery('.show_different_times_div').append(total_dates_selected);
								jQuery(".show_different_times_div .chosen_select").chosen();
							}else{
							
								jQuery('.show_different_times_div').html('');
							}
					});
					
				});
        		cal = '';
				function gd_event_date_format(date){
				var format = '<?php echo apply_filters('geodir_add_event_calendar_date_format','Y-m-d');?>';
				
				var formatted_date = format;
				formatted_date = formatted_date.replace("Y", date.getFullYear());
				formatted_date = formatted_date.replace("m", (date.getMonth()+1));
				formatted_date = formatted_date.replace("d", date.getDate());

				return formatted_date;	
					
				}
				
				
				multicalInit1 = function() {
					cal = new YAHOO.widget.CalendarGroup("multiCal1","multiCalContainer1",{pages:2, MULTI_SELECT: true <?php echo $selected; ?>,start_weekday:<?php echo apply_filters('geodir_calendar_start_weekday','0');?>}); 
					cal.render();
				
					var updated = function(){
						var selectedDates = cal.getSelectedDates();
						var txt = "";
						var val = "";
						for(var ii=0; ii<selectedDates.length; ii++){
							var date = selectedDates[ii];
							var current = date.getFullYear()+ "-" + (date.getMonth()+1) + "-" + date.getDate();
							var current_display = gd_event_date_format(date);
							if(ii>0){
								val += ",";
							}
							val += current;
							txt += "<span>"+current_display + "</span>";
						}
						
						if(txt == ''){
							jQuery('#help1').hide();
							<?php if(!is_admin()){?>
							
							jQuery('.event_recurring_dates').closest('.required_field').find('.geodir_message_error').show();
							jQuery('.event_recurring_dates').closest('.required_field').find('.geodir_message_error').html(error_message);
							<?php }?>
						}else{
							jQuery('#help1').show();
							jQuery('.event_recurring_dates').closest('.required_field').find('.geodir_message_error').hide();
						}
						
						document.getElementById('multiDisplay1').innerHTML = txt;
						document.getElementById('dates').value = val;
						
						if(jQuery('.different_times').is(':checked') == true){
							jQuery('.different_times').change();
						}
						calChanged(cal);
						
						
						
					}
					cal.selectEvent.subscribe(updated, cal, true);
					cal.deselectEvent.subscribe(updated, cal, true);
				}
				YAHOO.util.Event.onDOMReady(multicalInit1);
        
        </script>
				
    <div id="Recurring_sh" class="Recurring_sh" style=" <?php  if(!isset($recuring) || $recuring != '1' || (!isset($package_info->recurring_pkg) || $package_info->recurring_pkg != '1') ) echo 'display:none;';?> " >
        <div class="geodir_form_row clearfix">
            <label><?php _e('Date', GEODIREVENTS_TEXTDOMAIN);?></label>
            
            <select option-ajaxChosen="false" class="chosen_select" name="event_day[]" id="event_day" multiple="multiple" style="width:395px;">
                <option value="*" <?php if(!empty($event_day)){if(in_array('*',$event_day )){ echo 'selected'; } } ?> >
                    <?php _e('Every Day', GEODIREVENTS_TEXTDOMAIN);?>
                </option>
                <?php
								$selected = '';
                for($i=1;$i<=31;$i++){
                    if(!empty($event_day))
											$selected = (in_array($i,$event_day )) ? 'selected' : ''; 	
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
                ?>
            </select>
           <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
        </div>
        
        
        <div class="geodir_form_row clearfix">
            <label><?php _e('Day of Month', GEODIREVENTS_TEXTDOMAIN);?></label>
            <div class="day_of_month">
            <?php
         $event_week_day = array();
					
				$gd_weeks[] = 'Every'; 
				$gd_weeks = array_merge($gd_weeks,geodir_get_weeks());
				
				foreach($gd_weeks as $week){
					$event_week_day = isset($event_week[strtolower($week)]) ? $event_week[strtolower($week)] : ''; 
					if(!empty($event_week_day))
						$checked = (in_array(strtolower($week),$event_week_day )) ? 'checked' : ''; 
					echo '<div style="margin:0 0 10px 0;">';	
					echo '<label style="width:15%; display:inline-block;float:none; margin:5px 5px;">'.__($week, GEODIREVENTS_TEXTDOMAIN).'</label>';
					
				?>
                   <select option-ajaxChosen="false" class="chosen_select" name="event_week[<?php echo strtolower($week);?>][]" id="event_week<?php echo strtolower($week);?>" multiple="multiple" style="width:75%;" >
                        <?php 
                        $gd_week_day = geodir_get_weekday();
                        foreach($gd_week_day as $day ){ 
                             if(!empty( $event_week_day ))
								 $selected = (in_array(strtolower($day),$event_week_day )) ? 'selected' : ''; 
                            echo '<option '.$selected.' value="'.strtolower($day).'" >'.__($day, GEODIREVENTS_TEXTDOMAIN).'</option>';
                        } ?>
                    </select>
                    </div>
                <?php } ?>
           </div>     	    
           <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
        </div>           
        
        <div class="geodir_form_row clearfix">
            <label><?php _e('Month', GEODIREVENTS_TEXTDOMAIN);?></label>
                
            <select option-ajaxChosen="false" class="chosen_select" name="event_month[]" id="event_month" multiple="multiple" style="width:395px;">
             <?php
				
				if(!empty($event_month))
					$selected = (in_array('*',$event_month)) ? 'selected' : ''; 
				
				echo '<option '.$selected.' value="*">'.__('Every Month', GEODIREVENTS_TEXTDOMAIN).'</option>'; 
				$month = strtotime(date('Y').'-'.date(1).'-'.date(1));
				$end = strtotime(date('Y').'-'.date(1).'-'.date(1).' + 12 months');
				while($month < $end){
						if(!empty($event_month))
							$selected = (in_array(date('m', $month),$event_month)) ? 'selected' : ''; 
						echo '<option '.$selected.' value="'.date('m', $month).'">'.date('F',$month).'</option>';
						$month = strtotime("+1 month", $month);
				}
               ?>  		
            </select>
           <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
        </div>
        <div class="geodir_form_row clearfix">
            <label><?php _e('Year', GEODIREVENTS_TEXTDOMAIN);?></label>
            <select option-ajaxChosen="false" class="chosen_select" name="event_year[]" id="event_year" multiple="multiple" style="width:395px;">
                <?php
                
				 $selected = '';
               
                if(!empty($event_year))    
	                $selected = (in_array('*',$event_year)) ? 'selected' : ''; 
    
	            echo '<option '.$selected.' value="*">'.__('Every Year', GEODIREVENTS_TEXTDOMAIN).'</option>'; 
                
                $year = strtotime(date('Y').'-'.date('F').'-'.date(1));
                $end = strtotime('2037-12-31');
                
                while( $year < $end && date('Y', $year) != '1970' ){
	                if(!empty($event_year))   
				    	$selected = (in_array(date('Y', $year),$event_year)) ? 'selected' : ''; 
                    echo '<option '.$selected.' value="'.date('Y', $year).'">'.date('Y', $year).'</option>';
                    $year = strtotime("+1 year", $year);
                }
                ?>  	  		
            </select>
           <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
        </div>
				
				
				 <div class="geodir_form_row clearfix">
        <div class="required_field geodir_form_row">
            <label><?php _e('Event Date', GEODIREVENTS_TEXTDOMAIN);?><span>*</span> </label>
            <script language="javascript">
                 jQuery(function($) {
                    $( "#event_start" ).datepicker({
                        dateFormat:'yy-mm-dd',
                        minDate: 0,
                        onClose: function( selectedDate ) {
                                 $( "#event_end" ).datepicker( "option", "minDate", selectedDate );}
                    });
                    $( "#event_end" ).datepicker({dateFormat:'yy-mm-dd'});
                });
            </script>
            <div style="float:left;">
            <input type="text" field_type="text" name="event_start" id="event_start" class="geodir-datefield" value="<?php if(isset($recuring_data['event_start'])){ echo esc_attr(stripslashes($recuring_data['event_start']));} ?>"  />
            </div>
            <label class="eventdateto"><?php _e('To', GEODIREVENTS_TEXTDOMAIN);?></label>
            <div style="float:left;">
            <input type="text" field_type="text" name="event_end" id="event_end" class="geodir-datefield" value="<?php if(isset($recuring_data['event_end'])){ echo esc_attr(stripslashes($recuring_data['event_end']));} ?>"  />
            </div>
            <br/>
           <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
        </div>
    </div>
                        
					<div class="geodir_form_row clearfix" >
							<div class="required_field geodir_form_row">
									<label><?php _e('Event Time', GEODIREVENTS_TEXTDOMAIN);?><span>*</span> </label>
									<script language="javascript">
											jQuery(document).ready(function(){
											
													jQuery('#event_start_time').timepicker({timeFormat: 'hh:mm tt',});
													
													jQuery('#event_end_time').timepicker({timeFormat: 'hh:mm tt',});
											});
													
									</script>
									<div style="float:left;">
									<input type="text" field_type="text" name="event_start_time" id="event_start_time" class="geodir-datefield" value="<?php if(isset($recuring_data['event_start_time'])){ echo esc_attr(stripslashes($recuring_data['event_start_time']));} ?>"  />
									<input type="hidden" id="event_start_pick" name="event_start_pick" />
									</div>
									<label class="eventdateto"><?php _e('To', GEODIREVENTS_TEXTDOMAIN);?></label>
									<div style="float:left;">
									<input type="text" field_type="text" name="event_end_time" id="event_end_time" class="geodir-datefield" value="<?php if(isset($recuring_data['event_end_time'])){ echo esc_attr(stripslashes($recuring_data['event_end_time']));} ?>"  />
									<input type="hidden" id="event_end_pick" name="event_end_pick" />
									</div>
									<br/>
								 <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
							</div>
					</div>
		
    </div>
                            
    <div id="NotRecurring_sh" class="NotRecurring_sh" style=" <?php if(!isset($recuring) || $recuring != '1' ) echo 'display:block;';?> " >
    <div class="required_field  geodir_form_row clearfix">
    <label><?php _e('Event Date(s)', GEODIREVENTS_TEXTDOMAIN);?></label>
    	
			<div id="doc2" class="yui-skin-sam yui-t2">
				
    		<div class="fullitem"> 
        	
				<div class="yui-calcontainer multi" id="multiCalContainer1">
					
					<div class="yui-calcontainer groupcal first-of-type" id="multiCalContainer1_0"></div>
					
					<div class="yui-calcontainer groupcal last-of-type" id="multiCalContainer1_1"></div>
					
				</div>
			
				<div style="z-index: 2; visibility: visible; left: 1139px; top: 347px;" id="help1_c" class="yui-panel-container shadow">
				
					<div id="help1" class="popup yui-module yui-overlay yui-panel" style="display:<?php if(isset($event_recurring_dates) && $event_recurring_dates != ''){echo 'block';}else{ echo 'none';} ?>;">
						<div id="help1_h" style="cursor: move;" class="hd"><?php _e('Selected Dates', GEODIREVENTS_TEXTDOMAIN); ?> <!--<a href="#" class="container-close">Close</a>--></div>
						<div class="bd"> 
						<div id="multiDisplay1">
						<?php 
							if(isset($event_recurring_dates) && $event_recurring_dates != ''){
								
								$selected_dates_arr = explode(',', $event_recurring_dates);
								
								foreach($selected_dates_arr as $dates){
									$format = apply_filters('geodir_add_event_calendar_date_format','Y-m-d');
									$date = date_create($dates);
									$new_date = date_format($date,$format);
									echo '<span>'.$new_date.'</span>';
								}
								
							}
							
						?>
						
						</div>
						</div>
						<div class="ft"></div>
					</div>
					
					<div class="underlay"></div>
				
				</div> 
			<input name="event_recurring_dates" class="event_recurring_dates" id="dates" value="<?php if(isset($event_recurring_dates)){ echo $event_recurring_dates;} ?>" type="hidden"> 
         </div>
        </div> 
				
				<span class="geodir_message_note"><?php _e('Click on each day your event will be held. You may choose more than one day. Selected dates appear in blue and can be unselected by clicking on them.', GEODIREVENTS_TEXTDOMAIN);?></span>
				
				<span class="geodir_message_error" style="display:none;"><?php _e('Please select at least one event date.', GEODIREVENTS_TEXTDOMAIN);?></span>
				
    </div>
		

			<div class="required_field geodir_form_row  clearfix">
				<label><?php _e('Event Time', GEODIREVENTS_TEXTDOMAIN);?></label>
				
				<label class="eventdateto"><?php _e('Start', GEODIREVENTS_TEXTDOMAIN); ?></label>
				<div class="event-single-dateto-inner">
				<select option-ajaxChosen="false" id="starttime" name="starttime" class="chosen_select">
					<?php echo time_select_options($starttime); ?>
				</select>
				</div>
				
				<label class="eventdateto"><?php _e('End', GEODIREVENTS_TEXTDOMAIN); ?></label>
				<div class="event-single-dateto-inner">
				<select option-ajaxChosen="false" id="endtime" name="endtime" class="chosen_select" style="width:90px;">
					<?php echo time_select_options($endtime); ?>
				</select>
				</div>
			</div>
			
			
			<div class="geodir_form_row clearfix" >
					<label><?php _e('Different Event Times', GEODIREVENTS_TEXTDOMAIN);?></label>
					 <input <?php if(isset($different_times) && $different_times == '1'){ echo 'checked="checked"'; }  ?> name="different_times" class="different_times" value="1" type="checkbox"  />
					 <span class="geodir_message_note"><?php _e('Checked to different dates have different start and end times', GEODIREVENTS_TEXTDOMAIN)?></span>
			</div>
			
			
			<div class="geodir_form_row show_times_div clearfix" style=" <?php if(isset($different_times) && $different_times == '1'){ echo 'display:block;'; }else{ echo 'display:block;'; } ?> ">
					<label></label>
				
						<!--<input type="button" class="show_different_times" value="Click to set times" />-->
							<div class="show_different_times_div"> 
							
							<?php echo $differnttimes_selected; ?>
								
							</div>
				
			</div>
			
    </div>
	
	<?php   
}


function geodir_event_show_shedule_date(){
	
	global $post;
	
	if(geodir_is_page('preview'))
		$recuring_data = (array)$post; 
	else	
		$recuring_data = unserialize($post->recurring_dates); 
	
	if(!empty($recuring_data) && (isset($recuring_data['event_recurring_dates']) && $recuring_data['event_recurring_dates'] != '') || (isset($recuring_data['Recurring']) && $recuring_data['Recurring'] == '1')){
	
	ob_start();
	
	$geodir_num_dates = 0;
		
		if(isset($recuring_data['Recurring']) && $recuring_data['Recurring'] == '1'){?>
			
							
			<?php 
					$output = '<p><span class="geodir-i-date">'.__('Open For', GEODIREVENTS_TEXTDOMAIN).' : </span>';
					if(!empty($recuring_data['event_day']))
					{
					
						$output .=  '<p>'.__('Dates', GEODIREVENTS_TEXTDOMAIN).' : ';
						
						if(in_array('*', $recuring_data['event_day']))
						{
							$output .=  __('All Dates', GEODIREVENTS_TEXTDOMAIN);
						}
						else
						{
							$output .=  implode(', ',$recuring_data['event_day']);
						}
						
						$output .=  '</p>';
					}
					
					
					if(empty($recuring_data['event_day']) && empty($recuring_data['event_week']))
					{
						$output .=  '<p>'.__('Dates', GEODIREVENTS_TEXTDOMAIN).' : ';
							$output .=  __('Every Day', GEODIREVENTS_TEXTDOMAIN);
						$output .=  '</p>';
					}
				
				
					if(!empty($recuring_data['event_week']))
					{
						
						$event_day = $recuring_data['event_week'];
						
						$output .=  '<div class="geodir-event-day">'.__('Days', GEODIREVENTS_TEXTDOMAIN).' : <br />';
						
						foreach($event_day as $key => $value)
						{
							
							$output .=  '<div class="geodirevent_days clearfix">';
							$output .=  '<span class="heading">'.ucfirst($key).': </span>';
								
							if(is_array($value))
								$output .=  '<span class="value">'.ucfirst(implode(', ', $value)).' </span>';
							
							$output .=  '</div>';
						}
						
						$output .=  '</div>';
						
					}
					
					
					if(!empty($recuring_data['event_day']) || !empty($recuring_data['event_week']) || !empty($recuring_data['event_month']))
					{
						
							$output .=  '<p class="geodir-eventmonth">'.__('Months', GEODIREVENTS_TEXTDOMAIN).' : ';
							
							if(!empty($recuring_data['event_month']))
							{
							
								if(!in_array('*', $recuring_data['event_month']))
								{
									$add_month = '';
									foreach($recuring_data['event_month'] as $month)
									{
										$output .=  $add_month.date("F", mktime(0, 0, 0, $month, 10));
																$add_month = ', ';
									}
								}
								else
								{
									$output .=  __('All Months', GEODIREVENTS_TEXTDOMAIN);
								}
							}
							else
							{
									$output .=  __('All Months', GEODIREVENTS_TEXTDOMAIN);
							}
						$output .=  '</p>';
					
					}
				
				$output .=  '<p>'.__('Years', GEODIREVENTS_TEXTDOMAIN).' : ';
					
					if(!empty($recuring_data['event_year']))
					{
						if(!in_array('*', $recuring_data['event_year']))
						{
							$output .=  implode(', ', $recuring_data['event_year']);
						}
						
					}
					else
					{
						
						$output .=  $recuring_data['event_start'].' To '.$recuring_data['event_end'];
					
					}
				$output .=  '</p>';
				
			
			?>
		</p>
	<?php }else{ 
					$output = '';
					
					
					$output .= '<div class="geodir_event_schedule">';	
					
					$event_recurring_dates = explode(',', $recuring_data['event_recurring_dates']);
					
					$starttimes = isset($recuring_data['starttime']) ? $recuring_data['starttime'] : '';
					$endtimes = isset($recuring_data['endtime']) ? $recuring_data['endtime'] : '';
							
					foreach($event_recurring_dates as $key => $date){
						
						//if(strtotime($date) < strtotime(date("Y-m-d"))){continue;} // if the event is old don't show it on the map
						
						$output .=  '<p>';
						$geodir_num_dates++;
						if(isset($recuring_data['different_times']) && $recuring_data['different_times'] == '1'){
							$starttimes = isset($recuring_data['starttimes'][$key]) ? $recuring_data['starttimes'][$key] : '';
							$endtimes = isset($recuring_data['endtimes'][$key]) ? $recuring_data['endtimes'][$key] : '';
						}	
						
						$sdate = strtotime($date.' '.$starttimes);
						$edate = strtotime($date.' '.$endtimes);
						
						if($starttimes > $endtimes){
							$edate = strtotime($date.' '.$endtimes . " +1 day");
						}
						
						

						global $geodir_date_time_format; 	
						
						$output .=  '<i class="fa fa-caret-right"></i>'.date_i18n($geodir_date_time_format, $sdate);
							//$output .=  __(' To', GEODIREVENTS_TEXTDOMAIN).' ';
							$output .= '<br />';
						$output .=  '<i class="fa fa-caret-left"></i>'.date_i18n($geodir_date_time_format, $edate);//.'<br />';
						$output .=  '</p>';	
					}
					
					$output .= '</div>';	
						
						
						
				?>
			
	<?php } 
		
		$geodir_event_dates_display = '';
		if($geodir_num_dates > 5)
			$geodir_event_dates_display = 'geodir_event_dates_display';
		
		$geodir_date_count = __('Dates',GEODIREVENTS_TEXTDOMAIN);	
		
		if($geodir_num_dates == 1)	
			$geodir_date_count = __('Date',GEODIREVENTS_TEXTDOMAIN);
		
			
	echo '<div class="geodir-company_info '.$geodir_event_dates_display.'">';
	
	if($geodir_num_dates == 1){
		echo  '<span class="geodir-event-dates"><i class="fa fa-calendar"></i>'.$geodir_date_count.' : ';
		
	}
	else{
		echo  '<span class="geodir-event-dates"><i class="fa fa-calendar"></i>'.$geodir_date_count.' : ';
	}
	 
	echo $output;
	echo $datehtml = ob_get_clean();
		
		echo '</span></div>' ;
	}
	
	
}


function geodir_event_show_business_fields_html() {

	global $post,$wpdb,$current_user,$post_info;
	
	$package_info = array();
	$package_info = geodir_post_package_info( $package_info , $post );
	
	if( !isset( $package_info->post_type ) || $package_info->post_type != 'gd_event' ) {
		return false;
	}
		
	$geodir_link_business = '';
	
	if( isset( $_REQUEST['backandedit'] ) ) {
		$post = (object)unserialize( $_SESSION['listing'] );
		$geodir_link_business = $post->geodir_link_business;	
	} else if( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
		$geodir_link_business = geodir_get_post_meta( $_REQUEST['pid'], 'geodir_link_business' );
	} else if( isset( $post->geodir_link_business ) ) {
		$geodir_link_business = $post->geodir_link_business;
	} else if( isset( $post_info->geodir_link_business ) ) {
		$geodir_link_business = $post_info->geodir_link_business;
	}
	
	if( $geodir_link_business == '' && isset( $post->ID ) ) {
		$geodir_link_business = geodir_get_post_meta( $post->ID, 'geodir_link_business' );
	}
	
	$listings = geodir_event_get_my_listings( 'gd_place' );
		
	if( isset( $package_info->link_business_pkg ) && $package_info->link_business_pkg  == '1' ) {
		
		if(!is_admin())
			echo '<h5>'.__('Businesses', GEODIREVENTS_TEXTDOMAIN).'</h5>';?>
		<div id="geodir_link_business_row" class="geodir_form_row clearfix">
		  <label>
		  <?php _e( 'Link Business', GEODIREVENTS_TEXTDOMAIN );?>
		  </label>
		  <div class="geodir_link_business_chosen_div" style="width:50%;float:left;margin-bottom:7px">
			<input type="hidden" name="geodir_link_business_val" value="<?php echo $geodir_link_business;?>" />
			<select id="geodir_link_business" name="geodir_link_business" class="geodir_link_business_chosen" data-location_type="link_business"data-placeholder="<?php _e( 'Please wait..&hellip;', GEODIRLOCATION_TEXTDOMAIN );?>" data-ajaxchosen="1"  data-addsearchtermonnorecord="0" data-autoredirect="0">
			  <?php			
				$options = '';
				$found = false;
				if( !empty( $listings ) ) {
					foreach( $listings as $listing ) {
						$selected = ( $listing->ID == $geodir_link_business ) ? 'selected="selected"' : '';
						$options .= '<option ' . $selected . ' value="' . $listing->ID . '">' . $listing->post_title . '</option>';
						if( $listing->ID == $geodir_link_business ) {
							$found = true;
						}
					}
				}
				$selected = !$found && $geodir_link_business == '' ? 'selected="selected"' : '';
				$options = '<option ' . $selected . ' value="">' . __( 'No Business', GEODIREVENTS_TEXTDOMAIN ) . '</option>' . $options;
				if( !$found && $geodir_link_business > 0 ) {
					$listing_info = get_post( $geodir_link_business );
					if( !empty( $listing_info ) ) {
						$options .= '<option selected="selected" value="' . $geodir_link_business . '">' . $listing_info->post_title . '</option>';
					}
				}
				echo $options;
			?>
			</select>
			<?php $geodir_link_business = wp_create_nonce( 'geodir_link_business_autofill_nonce' );?>
			<input type="hidden" name="geodir_link_business_nonce" value="<?php echo $geodir_link_business;?>">
		  </div>
		  <input type="button" id="geodir_link_business_autofill" class="geodir_button button-primary" value="<?php echo EVENT_FILL_IN_BUSINESS_DETAILS; ?>" style="float:none;margin-left:30%;" />
		</div>
		<?php
	}
}


function geodir_add_search_fields($fields,$stype){

echo geodir_get_current_posttype();
	  if($stype == 'gd_event' )
	   $fields[]= array('field_type'=>'text','site_title'=>'Search By Date ','htmlvar_name'=>'event','data_type'=>'DATE');
	 return $fields;
}


function geodir_event_add_event_features(){
	
	global $post;
	
	$event_reg_desc = '';
	$event_reg_fees = '';
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if(!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
	
	if(isset($_REQUEST['backandedit']) &&  $_REQUEST['backandedit'] && isset($_SESSION['listing']) ){ 
	
		$post = unserialize($_SESSION['listing']);
		$event_reg_desc = isset($post['event_reg_desc']) ? $post['event_reg_desc'] : '';
		$event_reg_fees = isset($post['event_reg_fees']) ? $post['event_reg_fees'] : '';
		
	}elseif( isset($_REQUEST['pid']) && $_REQUEST['pid']!='' && $post_info = geodir_get_post_info($_REQUEST['pid']) ){ 
		
		$event_reg_desc = isset($post_info->event_reg_desc) ? $post_info->event_reg_desc : '';
		$event_reg_fees = isset($post_info->event_reg_fees) ? $post_info->event_reg_fees : '';
		
	}
	 
	if($event_reg_desc == '' && isset($post->ID))
		$event_reg_desc = geodir_get_post_meta( $post->ID, 'event_reg_desc');
		
	if($event_reg_fees == '' && isset($post->ID))
		$event_reg_fees = geodir_get_post_meta($post->ID, 'event_reg_fees');
			
	if(isset($package_info->reg_desc_pkg) && $package_info->reg_desc_pkg  == '1'){?>
	
		<div id="geodir_event_reg_desc_row" class="geodir_form_row clearfix">
			<label><?php _e('How to Register', GEODIREVENTS_TEXTDOMAIN);?></label><?php
			
			$show_editor = get_option('geodir_tiny_editor_event_reg_on_add_listing');
			
			if(!empty($show_editor) && $show_editor=='yes'){
													
				$editor_settings = array('media_buttons'=>false, 'textarea_rows'=>10);?>
				
				<div class="editor" field_id="event_reg_desc" field_type="editor">
				<?php wp_editor( stripslashes($event_reg_desc), "event_reg_desc", $editor_settings ); ?>
				</div><?php
				
			}else{
				
				?><textarea field_type="textarea" name="event_reg_desc" id="event_reg_desc" class="geodir_textarea" ><?php echo esc_attr(stripslashes($event_reg_desc)); ?></textarea><?php
			
			}?>
			
			<span class="geodir_message_note"><?php _e('Basic HTML tags are allowed', GEODIREVENTS_TEXTDOMAIN);?></span>
			<span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
		</div><?php
		
	}
	
	if(isset($package_info->reg_fees_pkg) && $package_info->reg_fees_pkg  == '1'){ ?>
		
		<div id="geodir_event_reg_fees_row" class="geodir_form_row clearfix">
				<label><?php _e('Registration Fees', GEODIREVENTS_TEXTDOMAIN);?></label>
				<input type="text" field_type="text" name="event_reg_fees" id="event_reg_fees" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($event_reg_fees)); ?>"  />
				<span class="geodir_message_note"><?php 
				
				$currency  = (get_option('geodir_currency')) ? get_option('geodir_currency') : 'USD';
				$sym  = (get_option('geodir_currencysym')) ? get_option('geodir_currencysym') : '$';
				
				printf(__('Enter Registration Fees, in %s eg. : %s50', GEODIREVENTS_TEXTDOMAIN), $currency,$sym); 
				
				?></span>
			 <span class="geodir_message_error"><?php if(isset($required_msg)){ echo $required_msg;}?></span>
		</div><?php
		
	}
	
}


function geodir_event_before_description(){ 
	
	global $post;
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if(!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
		
	$event_reg_desc = ''; 
	$event_reg_fees = '';
	
	if(isset($package_info->reg_desc_pkg) && $package_info->reg_desc_pkg  == '1'){
		$event_reg_desc = isset($post->event_reg_desc) ? $post->event_reg_desc : '';
	 
		if($event_reg_desc == '' && isset($post->ID))
			$event_reg_desc = geodir_get_post_meta( $post->ID, 'event_reg_desc');
	}
	
	if(isset($package_info->reg_fees_pkg) && $package_info->reg_fees_pkg  == '1'){
		$event_reg_fees = isset($post->event_reg_fees) ? $post->event_reg_fees : '';
	 
		if($event_reg_desc == '' && isset($post->ID))
			$event_reg_fees = geodir_get_post_meta( $post->ID, 'event_reg_fees');

	}
	
	if($event_reg_desc != '' || $event_reg_fees != ''){
		
		echo '<div class="geodir-company_info field-group">';
		
		if($event_reg_desc != ''){
			echo '<h3>'.__('How to Register', GEODIREVENTS_TEXTDOMAIN).'</h3>';
			echo wpautop(stripslashes($event_reg_desc));
		}
		
		if($event_reg_fees != ''){
			echo '<p class="" style="clear:both;"><span class="geodir-i-text" style="">Fees: </span>'.$event_reg_fees.'</p>';
		}
		
		echo '</div>';
	
	}
	
}



function geodir_get_post_feature_events($query_args = array(), $layout='gridview_onehalf'){
	
	global $gridview_columns;
	
	$character_count = (isset($query_args['character_count']) && !empty($query_args['character_count'])) ? $query_args['character_count'] : '';
	
	$geodir_event_type = $query_args['geodir_event_type'];
	
	if($geodir_event_type == 'all' || empty($geodir_event_type) || (is_array($geodir_event_type) && (in_array('all',$geodir_event_type) || in_array('feature',$geodir_event_type)))){
		
		$query_args['geodir_event_type'] = 'feature';
		
	}else{
		return false;
	}
	

	$all_events = query_posts( $query_args );
	
	
	$all_events = query_posts( $query_args );
	
	if(!empty($all_events)){
	
		if(strstr($layout,'gridview')){
			
			$listing_view_exp = explode('_',$layout);
			
			$gridview_columns = $layout;
			
			$layout = $listing_view_exp[0];
			
		}
		
		
			
			$template = apply_filters( "geodir_template_part-feature-event-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
		
		
		
		
			?>
			<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo apply_filters('geodir_widget_feature_event_title', __('Events', GEODIREVENTS_TEXTDOMAIN));?></h3> 
			</div><?php
			include( $template );
			?> </div> <?php
	
	}
	
	wp_reset_query();
			

}
			
function geodir_get_post_past_events($query_args = array(), $layout='gridview_onehalf'){
	
	global $gridview_columns;
	
	$character_count = (isset($query_args['character_count']) && !empty($query_args['character_count'])) ? $query_args['character_count'] : '';

	$geodir_event_type = $query_args['geodir_event_type'];
	
	if($geodir_event_type == 'all' || empty($geodir_event_type) || (is_array($geodir_event_type) && (in_array('all',$geodir_event_type) || in_array('past',$geodir_event_type)))){
		
		$query_args['geodir_event_type'] = 'past';
		
	}else{
		return false;
	}
	
	$all_events = query_posts( $query_args );
	
	if(!empty($all_events)){
	
		if(strstr($layout,'gridview')){
			
			$listing_view_exp = explode('_',$layout);
			
			$gridview_columns = $layout;
			
			$layout = $listing_view_exp[0];
			
		}
		
	
			
			$template = apply_filters( "geodir_template_part-past-event-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
		
		
		
			?>
			<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo apply_filters('geodir_widget_past_event_title', __('Past Events', GEODIREVENTS_TEXTDOMAIN));?></h3> 
			</div> <?php
			
			include( $template );
			?> </div> <?php
		
	}
	 
	wp_reset_query();

}


function geodir_get_post_widget_events_old($query_args = array(), $layout='gridview_onehalf'){
	
	global $gridview_columns;
	
	$character_count = (isset($query_args['character_count']) && !empty($query_args['character_count'])) ? $query_args['character_count'] : '';

	$geodir_event_type = $query_args['geodir_event_type'];
	
/*	if($geodir_event_type == 'all' || empty($geodir_event_type) || (is_array($geodir_event_type) && (in_array('all',$geodir_event_type) || in_array('past',$geodir_event_type)))){
		
		$query_args['geodir_event_type'] = 'past';
		
	}else{
		return false;
	}*/
	//print_r($query_args);
	$all_events = query_posts( $query_args );
	//print_r($all_events);
	if(!empty($all_events)){
	
		if(strstr($layout,'gridview')){
			
			$listing_view_exp = explode('_',$layout);
			
			$gridview_columns = $layout;
			
			$layout = $listing_view_exp[0];
			
		}
		
	
			
			//$template = apply_filters( "geodir_template_part-past-event-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
		$template = apply_filters( "geodir_template_part-link-business-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
		
		
			?>
			<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo apply_filters('geodir_widget_past_event_title', __('Past Events', GEODIREVENTS_TEXTDOMAIN));?></h3> 
			</div> <?php
			
			include( $template );
			?> </div> <?php
		
	}
	 
	wp_reset_query();

}

function geodir_get_post_widget_events( $query_args = array(), $layout = 'gridview_onehalf' ) {
	global $gridview_columns, $geodir_event_widget_listview, $character_count;
	
	$character_count = ( isset( $query_args['character_count'] ) && $query_args['character_count'] != '' ) ? $query_args['character_count'] : 20;
	$geodir_event_type = $query_args['geodir_event_type'];
	
	$geodir_widget_title = __( 'Related Events', GEODIREVENTS_TEXTDOMAIN );
	switch ( $geodir_event_type ) {
		case 'feature' :
			$geodir_widget_title = __( 'Feature Events', GEODIREVENTS_TEXTDOMAIN );
		break;
		case 'past' :
			$geodir_widget_title = __( 'Past Events', GEODIREVENTS_TEXTDOMAIN );
		break;
		case 'upcoming' :
			$geodir_widget_title = __( 'Upcoming Events', GEODIREVENTS_TEXTDOMAIN );
		break;
	}
	$geodir_widget_title = apply_filters( 'geodir_widget_past_event_title', $geodir_widget_title );
	
	$widget_events = geodir_event_get_widget_events( $query_args );
	//echo '<pre>'; print_r($widget_events); echo '</pre>';
	
	if( !empty( $widget_events ) ) {
		if( strstr( $layout, 'gridview' ) ) {
			$listing_view_exp = explode( '_', $layout );
			$gridview_columns = $layout;
			$layout = $listing_view_exp[0];
		}
		
		$template = apply_filters( "geodir_event_template_widget_listview", WP_PLUGIN_DIR . '/geodir_event_manager/gdevents_widget_listview.php' );
				
		global $post;
		$current_post = $post;
		$geodir_event_widget_listview = true;
		ob_start();
		?>
		<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo $geodir_widget_title;?></h3> 
			</div>
			<?php include( $template ); ?>
		</div>
		<?php
		$GLOBALS['post'] = $current_post;
		setup_postdata( $current_post );
		$geodir_event_widget_listview = false;	
		
		$content = ob_get_clean();
		return $content;	
	}
	return NULL;
}

function geodir_event_get_widget_events( $query_args ) {
	global $wpdb, $plugin_prefix;
	$GLOBALS['gd_query_args'] = $query_args;
	$gd_query_args = $query_args;
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$fields = $wpdb->posts . ".*, " . $table . ".*, " . EVENT_SCHEDULE . ".*";
	$fields = apply_filters( 'geodir_event_filter_widget_events_fields', $fields );
	
	$join = "INNER JOIN " . $table ." ON (" . $table .".post_id = " . $wpdb->posts . ".ID)";
	$join .= " INNER JOIN " . EVENT_SCHEDULE ." ON (" . EVENT_SCHEDULE .".event_id = " . $wpdb->posts . ".ID)";
	$join = apply_filters( 'geodir_event_filter_widget_events_join', $join );
	
	$where = " AND " . $wpdb->posts . ".post_status = 'publish' AND " . $wpdb->posts . ".post_type = 'gd_event'";
	$where = apply_filters( 'geodir_event_filter_widget_events_where', $where );
	$where = $where != '' ? " WHERE 1=1 " . $where : '';
	
	$groupby = "";
	$groupby = apply_filters( 'geodir_event_filter_widget_events_groupby', $groupby );
	
	$orderby = geodir_event_widget_events_get_order( $query_args );
	$orderby = apply_filters( 'geodir_event_filter_widget_events_orderby', $orderby );
	$orderby .= $wpdb->posts . ".post_title ASC";
	$orderby = $orderby != '' ? " ORDER BY " . $orderby : '';
	
	$limit = !empty( $query_args['posts_per_page'] ) ? $query_args['posts_per_page'] : 5;
	$limit = apply_filters( 'geodir_event_filter_widget_events_limit', $limit );
	
	$limit = $limit>0 ? " LIMIT " . (int)$limit : "";
	
	$sql =  "SELECT SQL_CALC_FOUND_ROWS " . $fields . " FROM " . $wpdb->posts . "
		" . $join . "
		" . $where . "
		" . $orderby . "
		" . $groupby . "
		" . $limit;
	//echo '<pre>sql : '; print_r($sql); echo '</pre>';
	
	$rows = $wpdb->get_results($sql);
	
	return $rows;
}
function geodir_event_widget_events_get_order( $query_args ) {
	global $wpdb, $plugin_prefix, $gd_query_args, $table;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $wpdb->posts . ".post_date DESC, ";
	}
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$sort_by = !empty( $query_args['order_by'] ) ? $query_args['order_by'] : '';
	
	switch ( $sort_by ) {
		case 'latest':
		case 'newest':
			$orderby = $wpdb->posts . ".post_date DESC, ";
		break;
		case 'featured':
			$orderby = $table . ".is_featured ASC, ";
		break;
		case 'high_review':
			$orderby = $wpdb->posts . ".comment_count DESC, ";
		break;
		case 'high_rating':
			$orderby = $table . ".overall_rating DESC, ";
		break;
		case 'random':
			$orderby = "RAND(), ";
		break;
		default:
			$orderby = $wpdb->posts . ".post_title ASC, ";
		break;
	}
	
	return $orderby;
}

add_filter( 'geodir_event_filter_widget_events_orderby', 'geodir_event_function_widget_events_orderby' );
function geodir_event_function_widget_events_orderby( $orderby ) {
	global $wpdb, $plugin_prefix, $gd_query_args, $table;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $orderby;
	}
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$orderby .= EVENT_SCHEDULE . ".event_date ASC, " . EVENT_SCHEDULE . ".event_starttime ASC , " . $table . ".is_featured ASC, ";
	
	return $orderby;
}

add_filter( 'geodir_event_filter_widget_events_where', 'geodir_event_function_widget_events_where' );
function geodir_event_function_widget_events_where( $where ) {
	global $wpdb, $plugin_prefix, $gd_query_args;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $where;
	}
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$date_now = date( 'Y-m-d' );
	
	if ( !empty( $gd_query_args ) && !empty( $gd_query_args['event_related_id'] ) ) {
		$where .= " AND " . $table . ".geodir_link_business = " . (int)$gd_query_args['event_related_id'];
	}
	
	if ( !empty( $gd_query_args ) && isset( $gd_query_args['geodir_event_type'] ) ) {
		if ( $gd_query_args['geodir_event_type'] == 'feature' ) {
			$where .= " AND " . EVENT_SCHEDULE . ".event_date >= '" . $date_now . "' ";
		}
		
		if ( $gd_query_args['geodir_event_type'] == 'past' ) {
			$where .= " AND " . EVENT_SCHEDULE . ".event_date <= '" . $date_now . "' ";
		}
		
		if ( $gd_query_args['geodir_event_type'] == 'upcoming' ) {
			$where .= " AND " . EVENT_SCHEDULE . ".event_date >= '" . $date_now . "' ";
		}
	}
	
	if ( !empty( $gd_query_args ) && !empty( $gd_query_args['gd_location'] ) && function_exists( 'geodir_default_location_where' ) ) {
		$where = geodir_default_location_where( $where,$table );
	}
	
	return $where;
}

add_filter( 'geodir_event_filter_widget_events_limit', 'geodir_event_function_widget_events_limit' );
function geodir_event_function_widget_events_limit( $limit ) {
	global $wpdb, $plugin_prefix, $gd_query_args;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $limit;
	}
	
	if ( !empty( $gd_query_args ) && !empty( $gd_query_args['posts_per_page'] ) ) {
		$limit = (int)$gd_query_args['posts_per_page'];
	}
	
	return $limit;
}

function geodir_event_display_calendar(){

	
	$day = $_REQUEST["sday"];
	$monthNames = Array(__("January", GEODIREVENTS_TEXTDOMAIN), __("February", GEODIREVENTS_TEXTDOMAIN), __("March", GEODIREVENTS_TEXTDOMAIN), __("April", GEODIREVENTS_TEXTDOMAIN), __("May", GEODIREVENTS_TEXTDOMAIN), __("June", GEODIREVENTS_TEXTDOMAIN), __("July", GEODIREVENTS_TEXTDOMAIN), __("August", GEODIREVENTS_TEXTDOMAIN), __("September", GEODIREVENTS_TEXTDOMAIN), __("October", GEODIREVENTS_TEXTDOMAIN), __("November", GEODIREVENTS_TEXTDOMAIN), __("December", GEODIREVENTS_TEXTDOMAIN));
	
	if (!isset($_REQUEST["mnth"])) $_REQUEST["mnth"] = date("n");
	if (!isset($_REQUEST["yr"])) $_REQUEST["yr"] = date("Y");
	
	$cMonth = $_REQUEST["mnth"];
	$cYear = $_REQUEST["yr"];
	
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	$mainlink = $_SERVER['REQUEST_URI'];
	if(strstr($_SERVER['REQUEST_URI'],'?mnth') && strstr($_SERVER['REQUEST_URI'],'&yr'))
	{
		$replacestr = "?mnth=".$_REQUEST['mnth'].'&yr='.$_REQUEST['yr'];
		$mainlink = str_replace($replacestr,'',$mainlink);
	}elseif(strstr($_SERVER['REQUEST_URI'],'&mnth') && strstr($_SERVER['REQUEST_URI'],'&yr'))
	{
		$replacestr = "&mnth=".$_REQUEST['mnth'].'&yr='.$_REQUEST['yr'];
		$mainlink = str_replace($replacestr,'',$mainlink);
	}
	if(strstr($_SERVER['REQUEST_URI'],'?') && !strstr($_SERVER['REQUEST_URI'],'?mnth'))
	{
		$pre_link = $mainlink."&mnth=". $prev_month . "&yr=" . $prev_year;
		$next_link = $mainlink."&mnth=". $next_month . "&yr=" . $next_year;
	}else
	{
		$pre_link = $mainlink."?mnth=". $prev_month . "&yr=" . $prev_year;	
		$next_link = $mainlink."?mnth=". $next_month . "&yr=" . $next_year;
	}
	
	
	$add_location_filter = '';
		$query_args = array(
										'geodir_event_date_calendar' => strtotime($cYear.'-'.$cMonth),
										'is_geodir_loop' => true,
										'gd_location' 	 => ($add_location_filter) ? true : false,
										'post_type' => 'gd_event',
									);
									
		
		$all_events = query_posts( $query_args );
		
		$all_event_dates = array();
		
		if(!empty($all_events)){
			foreach($all_events as $event){
				if($event->event_dates!= '')
					$all_event_dates = explode(',',$event->event_dates);
			}
		}
		
		
		wp_reset_query();
		
		
		
		
		
	?> 
    
     <span id="cal_title" style="margin-top:-28px; display:block; text-align:center; padding-bottom:11px;"><strong><?php echo $monthNames[$cMonth-1].' '.$cYear; ?></strong></span>
 
	</td>
	</tr>
	<tr>
	<td align="center">
	<table width="100%" border="0" cellpadding="2" cellspacing="2"  class="calendar_widget" style="background:#e6e6e6; border:1px #bbb solid;">
	
	<tr>
	<?php if($day!='1'){ ?><td align="center" class="days" style="padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid; border-right:1px #bbb solid;" ><strong><?php echo apply_filters('geodir_event_cal_single_day_sunday',__('S', GEODIREVENTS_TEXTDOMAIN));?></strong></td><?php } ?>
	<td class="days" style="padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid; border-right:1px #bbb solid;"  ><strong><?php echo apply_filters('geodir_event_cal_single_day_monday',__('M', GEODIREVENTS_TEXTDOMAIN));?></strong></td>
	<td class="days" style=" padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid;border-right:1px #bbb solid;"  ><strong><?php echo apply_filters('geodir_event_cal_single_day_tuesday',__('T', GEODIREVENTS_TEXTDOMAIN));?></strong></td>
	<td class="days" style=" padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid;border-right:1px #bbb solid;"  ><strong><?php echo apply_filters('geodir_event_cal_single_day_wednesday',__('W', GEODIREVENTS_TEXTDOMAIN));?></strong></td>
	<td class="days" style=" padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid;border-right:1px #bbb solid;"  ><strong><?php echo apply_filters('geodir_event_cal_single_day_thursday',__('T', GEODIREVENTS_TEXTDOMAIN));?></strong></td>
	<td class="days" style=" padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid;border-right:1px #bbb solid;"  ><strong><?php echo apply_filters('geodir_event_cal_single_day_friday',__('F', GEODIREVENTS_TEXTDOMAIN));?></strong></td>
	<td class="days" style=" padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid;border-right:1px #bbb solid;"  ><strong><?php echo apply_filters('geodir_event_cal_single_day_saturday',__('S', GEODIREVENTS_TEXTDOMAIN));?></strong></td>
    <?php if($day=='1'){ ?><td class="days" style=" padding:10px; text-align:center; background:#ccc; border-top:1px #fff solid; border-right:1px #bbb solid;" ><strong><?php echo apply_filters('geodir_event_cal_single_day_sunday',__('S', GEODIREVENTS_TEXTDOMAIN));?></strong></td><?php } ?>
	</tr> 
	<?php
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	$maxday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	$startday = $thismonth['wday'];
	if($day=='1'){if($startday==0){$startday = $startday+6;}else{$startday = $startday-1;}}
	
	if(isset($_GET['m']))
	{
		$m = $_GET['m'];	
		$py=substr($m,0,4);
		$pm=substr($m,4,2);
		$pd=substr($m,6,2);
		$monthstdate = "$cYear-$cMonth-01";
		$monthenddate = "$cYear-$cMonth-$maxday";
	}
	global $wpdb;
		if(isset($_SESSION['multi_city']))
		$city_id =  $_SESSION['multi_city'];
		
		
	for ($i=0; $i<($maxday+$startday); $i++) {
		if(($i % 7) == 0 ) echo "<tr style=' border:1px #bbb solid;'>\n";
		if($i < $startday){
			echo "<td style='padding:10px; text-align:center; border-right:1px #bbb solid;'>&nbsp;</td>";
		}
		else 
		{
			$cal_date = $i - $startday + 1;
			$calday = $cal_date;
			if(strlen($cal_date)==1)
			{
				$calday="0".$cal_date;
			}
			$cMonth1 = $cMonth;
			if(strlen($cMonth)==1)
			{
				$cMonth1="0".$cMonth;
			}
			$urlddate = "$cYear$cMonth1$calday";
			
			$thelink = home_url()."/?geodir_search=1&stype=gd_event&s=+&near=&event_calendar=$urlddate";
			
			
			$the_cal_date = $cal_date;
			if(strlen($the_cal_date)==1){$the_cal_date = '0'.$the_cal_date;}
			$todaydate = "$cYear-$cMonth1-$the_cal_date";
			
			$condition_date = $cYear.'-'.$cMonth1.'-'.$calday;
			
			$event_date = date('Y-m-d',strtotime($condition_date)).' 00:00:00';
			
			echo "<td valign='middle' style='text-align:center; border-right:1px #bbb solid;'>";
			if(in_array($event_date, $all_event_dates)){
					
				echo "<a class=\"event_highlight\" href=\"$thelink\" title=\"".__('Click to view events on this date', GEODIREVENTS_TEXTDOMAIN)."\" style=' color:#000; padding:10px; background:#cfcfcf; display:block'>". ($cal_date) . "</a>";
			
			}else{
				echo "<span class=\"no_event\" style='padding:10px; display:block' >". ($cal_date) . "</span>";
			}
			echo "</td>\n";
			
		
		}
		if(($i % 7) == 6 ) echo "</tr>\n";
	}
	?>
	</table>
	</td>
	</tr>
	</table>
	<?php
}


function geodir_calender_event_details_after_post_title() {
	global $post, $wpdb, $condition_date, $geodir_post_type, $geodir_is_link_business, $geodir_event_widget_listview;
	
	$is_post_type_event = !empty( $post ) && isset( $post->post_type ) && $post->post_type == 'gd_event' && isset( $post->event_date ) && $post->event_date ? true : false;
	
	if ( ( ( is_main_query() && $geodir_post_type == 'gd_event' ) || get_query_var( 'geodir_event_listing_filter' ) || ( isset( $geodir_is_link_business ) && $geodir_is_link_business ) || ( isset( $geodir_event_widget_listview ) && $geodir_event_widget_listview ) ) && $is_post_type_event ) {
		global $geodir_date_time_format, $geodir_date_format, $geodir_time_format;
	?>
	<p style="clear:both;" class="geodir-event-meta">
		<span class="geodir-i-datepicker"><i class="fa fa-calendar-o"></i> <?php _e( 'Date:', GEODIREVENTS_TEXTDOMAIN ); ?></span>
		<?php echo date_i18n( $geodir_date_format, strtotime( $post->event_date ) ); ?>
	</p>
	<?php if ( ( isset( $post->event_starttime ) && !empty( $post->event_starttime ) ) || ( isset( $post->event_endtime ) && !empty( $post->event_endtime ) ) ) { ?>
	<p style="clear:both;" class="geodir-event-meta">
		<span class="geodir-i-time"><i class="fa fa-clock-o"></i> <?php _e('Time:', GEODIREVENTS_TEXTDOMAIN); ?> </span>
		<?php 
		if ( isset( $post->event_starttime ) && !empty( $post->event_starttime ) ) {
			echo date_i18n( $geodir_time_format, strtotime( $post->event_starttime ) ). ' ' ;
		}	
		if ( isset( $post->event_endtime ) && !empty( $post->event_endtime ) ) {
			echo __( ' to', GEODIREVENTS_TEXTDOMAIN ).' '. date( $geodir_time_format, strtotime( $post->event_endtime ) );	
		}											
		?>
	</p>
		<?php
		}
	}			
}

function geodir_event_get_link_business( $listings = array() ) {
	global $gridview_columns, $geodir_is_link_business;
	
	if( empty ( $listings ) ) {
		return NULL;
	}
	
	$template = apply_filters( "geodir_template_part-link-business-listview", WP_PLUGIN_DIR . '/geodir_event_manager/link-business-listview.php', 'gd_event' );		
	ob_start();
	?>
	<style>#link_businessTab .geodir_category_list_view li{margin-left:0;margin-right:0;}#link_businessTab .geodir-entry-header{border-bottom:none;}#link_businessTab .geodir_category_list_view li .geodir-content p.geodir-event-meta{margin-bottom:0}#link_businessTab .geodir_category_list_view header.geodir-entry-header{padding-bottom:.5em}</style>
	<div class="geodir_locations geodir_location_listing">
	<?php
	$linked_listings = $listings;
	$related_posts = true;
	if ( !isset( $character_count ) ) {
		$character_count = (int)get_option('geodir_related_post_excerpt');
		$character_count = empty( $character_count ) ? 20 : apply_filters( 'widget_list_sort', $character_count );
	}
	global $post;
	$current_post = $post;
	$geodir_is_link_business = true;
	include( $template );
	$GLOBALS['post'] = $current_post;
	setup_postdata( $current_post );
	$geodir_is_link_business = false;
	?>
	</div>
	<?php
	wp_reset_query();
	
	$content = ob_get_clean();
	
	return $content;
}

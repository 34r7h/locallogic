<?php
/**
* GeoDirectory Events related posts widget *
**/

function geodir_event_register_widgets() {
	class geodir_event_related_listing_postview extends WP_Widget {
	
		function geodir_event_related_listing_postview() {
			//Constructor
			$widget_ops = array( 'classname' => 'geodir_event_related_listing_post_view', 'description' => __( 'GD > Related Events Listing',GEODIREVENTS_TEXTDOMAIN ) );
			$this->WP_Widget( 'event_related_listing', __( 'GD > Related Events Listing', GEODIREVENTS_TEXTDOMAIN ), $widget_ops );
		}
		
		function widget( $args, $instance ) {
			// prints the widget
			extract( $args, EXTR_SKIP );
			
			$post_number = empty( $instance['post_number'] ) ? '5' : apply_filters( 'widget_post_number', $instance['post_number'] );
			$layout = empty( $instance['layout'] ) ? 'gridview_onehalf' : apply_filters( 'widget_layout', $instance['layout'] );
			$event_type = empty( $instance['event_type'] ) ? 'all' : apply_filters( 'widget_event_type', $instance['event_type'] );
			$add_location_filter = empty( $instance['add_location_filter'] ) ? '0' : apply_filters( 'widget_layout', $instance['add_location_filter'] );
			$listing_width = empty( $instance['listing_width'] ) ? '' : apply_filters( 'widget_layout', $instance['listing_width'] );
			$list_sort = empty( $instance['list_sort'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['list_sort'] );
			$character_count = isset( $instance['character_count'] ) && $instance['character_count']=='' ? 20 : apply_filters( 'widget_character_count', $instance['character_count'] );
			
			global $post;
			
			$post_id = '';
			$post_type = '';
			
			if ( isset($_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
				$post = geodir_get_post_info( $_REQUEST['pid'] );
				$post_type = $post->post_type;
				$post_id = $_REQUEST['pid'];
			} else if ( isset( $post->post_type ) && $post->post_type != '' ) {
				$post_type = $post->post_type;
				$post_id = $post->ID;
			}
			
			$all_postypes = geodir_get_posttypes();
			
			if ( !in_array( $post_type, $all_postypes ) ) {
				return false;
			}			
			if ( $post_type == 'gd_place' && $post_id != '' ) {
				$query_args = array(
									'geodir_event_type' => $event_type,
									'event_related_id' => $post_id,
									'posts_per_page' => $post_number,
									'is_geodir_loop' => true,
									'gd_location' 	 => $add_location_filter ? true : false,
									'post_type' => 'gd_event',
									'order_by' => $list_sort,
									'excerpt_length' => $character_count,
									'character_count' => $character_count
								);
					
				echo $before_widget;
				echo geodir_get_post_widget_events( $query_args, $layout );
				echo $after_widget;			
			}			
		}
		
		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			
			$instance['post_number'] = strip_tags($new_instance['post_number']);
			$instance['layout'] = strip_tags($new_instance['layout']);
			$instance['listing_width'] = strip_tags($new_instance['listing_width']);
			$instance['list_sort'] = strip_tags($new_instance['list_sort']);
			$instance['event_type'] = isset($new_instance['event_type']) ?  $new_instance['event_type'] : '';
			$instance['character_count'] = $new_instance['character_count'];
			if(isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
			$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
			else
			$instance['add_location_filter'] = '0';
			
			
			return $instance;
		}
		
		function form($instance) 
		{
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, 
										array('list_sort'=>'', 
												'list_order'=>'',
												'event_type'=>'',
												'post_number' => '5',
												'layout'=> 'gridview_onehalf',
												'listing_width' => '',
												'add_location_filter'=>'1',
												'character_count'=>'20') 
									 );
			
			$list_sort = strip_tags($instance['list_sort']);
			
			$list_order = strip_tags($instance['list_order']);
			
			$event_type = $instance['event_type'];
			
			$post_number = strip_tags($instance['post_number']);
			
			$layout = strip_tags($instance['layout']);
			
			$listing_width = strip_tags($instance['listing_width']);
			
			$add_location_filter = strip_tags($instance['add_location_filter']);
			
			$character_count = $instance['character_count'];
			
			?>
				
					<p>
						<label for="<?php echo $this->get_field_id('event_type'); ?>"><?php _e('Display Events:',GEODIREVENTS_TEXTDOMAIN);?>
							
						 <select  class="widefat" id="<?php echo $this->get_field_id('event_type'); ?>" name="<?php echo $this->get_field_name('event_type'); ?>">
															 	
								<option <?php if(isset($event_type) &&  $event_type=='feature'){ echo 'selected="selected"'; } ?> value="feature"><?php _e('Feature Events',GEODIREVENTS_TEXTDOMAIN); ?></option>
								
								<option <?php if(isset($event_type) && $event_type=='past'){ echo 'selected="selected"'; } ?> value="past"><?php _e('Past Events',GEODIREVENTS_TEXTDOMAIN); ?></option>
                                
								<option <?php if(isset($event_type) && $event_type=='upcoming' ){ echo 'selected="selected"'; } ?> value="upcoming"><?php _e('Upcoming Events',GEODIREVENTS_TEXTDOMAIN); ?></option>
							
							</select>
							</label>
					</p>
				 
					<p>
								<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:',GEODIREVENTS_TEXTDOMAIN);?>
									
								 <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
										
											<option <?php if($list_sort == 'latest'){ echo 'selected="selected"'; } ?> value="latest"><?php _e('Latest',GEODIREVENTS_TEXTDOMAIN); ?></option>
										 
											 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured',GEODIREVENTS_TEXTDOMAIN); ?></option>
											
											<option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review',GEODIREVENTS_TEXTDOMAIN); ?></option>
											
											<option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating',GEODIREVENTS_TEXTDOMAIN); ?></option>
											
											<option <?php if($list_sort == 'random'){ echo 'selected="selected"'; } ?> value="random"><?php _e('Random',GEODIREVENTS_TEXTDOMAIN); ?></option>
											
									</select>
									</label>
							</p>
					
					<p>
					
							<label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:',GEODIREVENTS_TEXTDOMAIN);?>
							
							<input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
							</label>
					</p>
				
					<p>
						<label for="<?php echo $this->get_field_id('layout'); ?>">
				<?php _e('Layout:',GEODIREVENTS_TEXTDOMAIN);?>
							<select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
								<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
								<option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
								<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
								<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
								<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view',GEODIREVENTS_TEXTDOMAIN); ?></option>
									
							</select>    
							</label>
					</p>
					
					<p>
							<label for="<?php echo $this->get_field_id('listing_width'); ?>"><?php _e('Listing width:',GEODIREVENTS_TEXTDOMAIN);?>
							
								<input class="widefat" id="<?php echo $this->get_field_id('listing_width'); ?>" name="<?php echo $this->get_field_name('listing_width'); ?>" type="text" value="<?php echo esc_attr($listing_width); ?>" />
							</label>
					</p>
					
					<p>
							<label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :',GEODIREVENTS_TEXTDOMAIN);?> 
							<input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
							</label>
					</p>
					
					 <p>
						<label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
				<?php _e('Enable Location Filter:',GEODIREVENTS_TEXTDOMAIN);?>
							<input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if($add_location_filter) echo 'checked="checked"';?>  value="1"  />
							</label>
					</p>
				
		<?php  
		} 
	}
	register_widget('geodir_event_related_listing_postview');	
	
	
	/* --- Geodir Event calender widget --- */
	
	class geodir_event_calendar_widget extends WP_Widget {
	
		function geodir_event_calendar_widget() {
		
			$widget_ops = array('classname' => 'geodir_event_listing_calendar', 'description' =>  __('GD > Event Listing Calendar',GEODIREVENTS_TEXTDOMAIN) );		
			$this->WP_Widget('geodir_event_listing_calendar', __('GD > Event Listing Calendar', GEODIREVENTS_TEXTDOMAIN ), $widget_ops);
			
		}
		
		function widget($args, $instance) {
	
			global $post;
			extract($args, EXTR_SKIP);
			
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$day = empty($instance['day']) ? '' : apply_filters('widget_day', $instance['day']);
			
			add_action( 'wp_enqueue_scripts', 'geodir_event_calenders_script' );
			
			echo $before_widget;
			
			?>
			
			<div class="geodir_locations geodir_location_listing">
				
				<?php if(trim($title) != ''){ ?>
					<div class="locatin_list_heading clearfix">
					<h3><?php echo __($title, GEODIREVENTS_TEXTDOMAIN);?></h3> 
					</div> 
				<?php } ?>
				
				<table width="100%" style="background:#D0D0D0; border:1px #bbb solid;">
					<tr align="center" class="title">
						<td width="10%" class="title" style="padding:10px;"><img id="geodir_cal_prev" style="cursor: pointer; cursor:hand" src="<?php echo plugins_url('gdevents-assets/images/previous2.png', __FILE__); ?>" alt=""  /></td>
						
						<td   class="title" style="padding:10px;"><center><img id="geodir_event_loading" style="margin-top:-10px" src="<?php echo plugins_url('gdevents-assets/images/ajax-loader.gif', __FILE__); ?>" /></strong></center></td>
						
						<td width="10%" class="title" style="padding:10px;"><img  id="geodir_cal_next" style="cursor: pointer; cursor: hand" src="<?php echo plugins_url('gdevents-assets/images/next2.png', __FILE__) ; ?>" alt=""  /></td>
					</tr>
				</table>
				
				<div id="geodir_event_calendar"></div>
				
			</div> 
			
			<script type="text/javascript">
				function geodir_event_call_calendar(){
				
				var sday = '<?php echo $day; ?>';
				
				var myurl = "<?php echo geodir_event_manager_ajaxurl().'&event_type=calendar'; ?>"+"&sday="+sday;
				
				jQuery.ajax({
					type: "GET",
					url: myurl,
					success: function(msg){		 
						document.getElementById('geodir_event_loading').style.display="none";
						jQuery("#geodir_event_calendar").html(msg);
					}
				});
				
				var mnth = <?php echo date("n"); ?>;
				var year = <?php echo date("Y"); ?>;
				
				jQuery("#geodir_cal_next").click(function(){
					
					jQuery('#geodir_event_loading').show();
					mnth++;
					if(mnth > 12){year++; mnth=1;}	
					
					var nexturl = "<?php echo geodir_event_manager_ajaxurl().'&event_type=calendar'; ?>&mnth="+mnth+"&yr="+year+"&sday="+sday;
					jQuery.ajax({
						type: "GET",
						url: nexturl,
						success: function(next){
							jQuery('#geodir_event_loading').hide();
							jQuery("#geodir_event_calendar").html(next);
						}
					});
				
				});
				
				jQuery("#geodir_cal_prev").click(function(){
				
					jQuery('#geodir_event_loading').show();
					mnth--;
					
					if(mnth < 1){year--; mnth=12;}	
					var prevurl = "<?php echo geodir_event_manager_ajaxurl().'&event_type=calendar'; ?>&mnth="+mnth+"&yr="+year+"&sday="+sday;
					jQuery.ajax({
						type: "GET",
						url: prevurl,
						success: function(prev){
							jQuery('#geodir_event_loading').hide();
							jQuery("#geodir_event_calendar").html(prev);
						}
					});
				});
				
			};
			
			
			jQuery(document).ready(function(){
				if(typeof geodir_event_call_calendar == 'function') { 
				geodir_event_call_calendar();
				}
			});
			
			</script>
				
			<?php
			
			echo $after_widget;
			
		}
		
		function update($new_instance, $old_instance) {
		
			$instance = $old_instance;		
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['day'] = strip_tags($new_instance['day']);
			return $instance;
			
		}
		
		function form($instance) {
			
			$instance =	wp_parse_args( (array) $instance, 
										array( 'title' => '',
										'day' => '')
									);
					
			$title = strip_tags($instance['title']);
			$day = strip_tags($instance['day']);
			
			?>
			
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', GEODIREVENTS_TEXTDOMAIN)?>:
				
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
					
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('day'); ?>"><?php _e('Start day?', GEODIREVENTS_TEXTDOMAIN);?>
					
					<select class="widefat" id="<?php echo $this->get_field_id('day'); ?>" name="<?php echo $this->get_field_name('day'); ?>">
					<option value="1" <?php if(esc_attr($day)=='1'){ echo 'selected="selected"';} ?>><?php _e('Monday', GEODIREVENTS_TEXTDOMAIN);?></option>
					<option value="0" <?php if(esc_attr($day)=='0'){ echo 'selected="selected"';} ?>><?php _e('Sunday', GEODIREVENTS_TEXTDOMAIN);?></option>
					</select>
					
				</label>
			</p> 
			<?php
		}
		
	}
	
	register_widget('geodir_event_calendar_widget');  
	
	
	/* --- Geodir Event popular posts widget --- */
	
	
class geodir_event_postview extends WP_Widget {

	function geodir_event_postview()
	{
		//Constructor
		$widget_ops = array('classname' => 'geodir_event_listing', 'description' => __('GD > Event Listing',GEODIREVENTS_TEXTDOMAIN) );
		$this->WP_Widget('event_post_listing', __('GD > Event Listing',GEODIREVENTS_TEXTDOMAIN), $widget_ops);
	}
	
	
	function widget($args, $instance) 
	{
		
		// prints the widget
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		global $gdevents_widget;
		$gdevents_widget = true;
		
		$title = empty($instance['title']) ? ucwords($instance['category_title']) : apply_filters('widget_title', __($instance['title'],GEODIREVENTS_TEXTDOMAIN));
		
		$post_type = 'gd_event';
		
		$category = empty($instance['category']) ? '0' : apply_filters('widget_category', $instance['category']);
		
		$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
		
		$layout = empty($instance['layout']) ? 'gridview_onehalf' : apply_filters('widget_layout', $instance['layout']);
		
		$add_location_filter = empty($instance['add_location_filter']) ? '0' : apply_filters('widget_layout', $instance['add_location_filter']);
		
		$listing_width = empty($instance['listing_width']) ? '' : apply_filters('widget_layout', $instance['listing_width']);
		
		$list_sort = empty($instance['list_sort']) ? 'latest' : apply_filters('widget_list_sort', $instance['list_sort']);
		
		$list_filter = empty($instance['list_filter']) ? 'all' : apply_filters('widget_list_filter', $instance['list_filter']);
		
		if(isset($instance['character_count'])){$character_count = apply_filters('widget_list_character_count', $instance['character_count']);}
		else{$character_count ='';}
		
		if(empty($title) || $title == 'All' ){
			$title .= ' '.get_post_type_plural_label($post_type);
		}
		
		$location_url = '';
		
		$location_url = array();
		$city = get_query_var('gd_city');
		if( !empty($city) ){
			
			if(get_option('geodir_show_location_url') == 'all'){
				$country = get_query_var('gd_country');
				$region = get_query_var('gd_region');
				if(!empty($country))
					$location_url[] = $country;
				
				if(!empty($region))
					$location_url[] = $region;
			}		
			$location_url[] = $city;		
		}
			
			
		$location_url = implode("/",$location_url);			
			
		
		
		if ( get_option('permalink_structure') )
			$viewall_url = get_post_type_archive_link($post_type);
		else
			$viewall_url = get_post_type_archive_link($post_type);
		
		
		if(!empty($category) && $category[0] != '0'){
			global $geodir_add_location_url;
			$geodir_add_location_url = '0';
			if($add_location_filter != '0'){
				$geodir_add_location_url = '1'; 
			}	
			$viewall_url = get_term_link( (int)$category[0], $post_type.'category');
			$geodir_add_location_url = NULL; 
		}
		
		?>
			<div class="geodir_locations geodir_location_listing">
            <?php do_action('geodir_before_view_all_link_in_widget') ; ?>
							<div class="geodir_list_heading clearfix">
								<?php echo $before_title.$title.$after_title;?>
								 <a href="<?php echo $viewall_url;?>" class="geodir-viewall">
									<?php _e('View all',GEODIREVENTS_TEXTDOMAIN);?>
								 </a>
							</div>
							<?php do_action('geodir_after_view_all_link_in_widget') ; ?>	
							<?php 
								$query_args = array( 
									'posts_per_page' => $post_number,
									'is_geodir_loop' => true,
									'gd_location' 	 => ($add_location_filter) ? true : false,
									'post_type' => $post_type,
									'geodir_event_listing_filter' => $list_filter,
									'order_by' =>$list_sort,
									'excerpt_length' => $character_count,
									);
								
								if(!empty($category) && $category[0] != '0'){
									
									$category_taxonomy = geodir_get_taxonomies($post_type); 
									
									######### WPML #########
									if(function_exists('icl_object_id')) {
									$category = gd_lang_object_ids($category, $category_taxonomy[0]);
									}
									######### WPML #########
									
									$tax_query = array( 'taxonomy' => $category_taxonomy[0],
												 		'field' => 'id',
														'terms' => $category);
									
									$query_args['tax_query'] = array( $tax_query );					
								}
								
								global $gridview_columns;
								query_posts( $query_args );

								if(strstr($layout,'gridview')){
									
									$listing_view_exp = explode('_',$layout);
									
									$gridview_columns = $layout;
									
									$layout = $listing_view_exp[0];
									
								}
								
								$template = apply_filters( "geodir_template_part-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );

								include( $template );
							    
								wp_reset_query(); 
							 ?>				
						   
						</div>
		
		
		<?php	
		$gdevents_widget = NULL;
		unset( $gdevents_widget );
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		
		if($new_instance['title'] == '')
		{
			$title = ucwords(strip_tags($new_instance['category_title']));
			//$instance['title'] = $title;
		}
		$instance['title'] = strip_tags($new_instance['title']);	
		
		//$instance['category'] = strip_tags($new_instance['category']);
		$instance['category'] = isset($new_instance['category']) ?  $new_instance['category'] : '';
		$instance['category_title'] = strip_tags($new_instance['category_title']);
		$instance['post_number'] = strip_tags($new_instance['post_number']);
		$instance['layout'] = strip_tags($new_instance['layout']);
		$instance['listing_width'] = strip_tags($new_instance['listing_width']);
		$instance['list_sort'] = strip_tags($new_instance['list_sort']);
		$instance['list_filter'] = strip_tags($new_instance['list_filter']);
		$instance['character_count'] = $new_instance['character_count'];
		if(isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
		$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
		else
		$instance['add_location_filter'] = '0';
		
		
		return $instance;
	}
	
	function form($instance) 
	{
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, 
									array( 	'title' => '', 
											'category'=>array(),
											'category_title'=>'',
											'list_sort'=>'', 
											'list_filter'=>'', 
											'list_order'=>'',
											'post_number' => '5',
											'layout'=> 'gridview_onehalf',
											'listing_width' => '',
											'add_location_filter'=>'1',
											'character_count'=>'20') 
								 );
		
		$title = strip_tags($instance['title']);
		
		$category = $instance['category'];
		
		$category_title = strip_tags($instance['category_title']);
		
		$list_sort = strip_tags($instance['list_sort']);
		
		$list_filter = strip_tags($instance['list_filter']);
		
		$list_order = strip_tags($instance['list_order']);
		
		$post_number = strip_tags($instance['post_number']);
		
		$layout = strip_tags($instance['layout']);
		
		$listing_width = strip_tags($instance['listing_width']);
		
		$add_location_filter = strip_tags($instance['add_location_filter']);
		
		$character_count = $instance['character_count'];
		
		?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',GEODIREVENTS_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
	
        
        <p id="post_type_cats">
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Post Category:',GEODIREVENTS_TEXTDOMAIN);?>

         <?php 
				 
				$category_taxonomy = geodir_get_taxonomies('gd_event'); 
				$categories = get_terms( $category_taxonomy, array( 'orderby' => 'count','order' => 'DESC') );
				
			?>
					
            <select multiple="multiple" class="widefat" name="<?php echo $this->get_field_name('category'); ?>[]" onchange="geodir_event_widget_cat_title(this)" >
            	
                <option <?php if(is_array($category)  && in_array( '0', $category)){ echo 'selected="selected"'; } ?> value="0"><?php _e('All',GEODIREVENTS_TEXTDOMAIN); ?></option>
				<?php foreach($categories as $category_obj){ 
					$selected = '';
					 if(is_array($category)  && in_array( $category_obj->term_id, $category))
					 	echo $selected = 'selected="selected"';
					 
					?>
            		
                    <option <?php echo $selected; ?> value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>
                
				<?php } ?>
                
            </select>
									
           <input type="hidden" name="<?php echo $this->get_field_name('category_title'); ?>" id="<?php echo $this->get_field_id('category_title'); ?>" value="<?php if($category_title != '') echo $category_title; else echo __('All',GEODIREVENTS_TEXTDOMAIN);?>" />
					 
            </label>
        </p>
        
				<p>
							<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:',GEODIREVENTS_TEXTDOMAIN);?>
								
							 <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
									
										<option <?php if($list_sort == 'latest'){ echo 'selected="selected"'; } ?> value="latest"><?php _e('Latest',GEODIREVENTS_TEXTDOMAIN); ?></option>
									 
										 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
										<option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
										<option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
										<option <?php if($list_sort == 'random'){ echo 'selected="selected"'; } ?> value="random"><?php _e('Random',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
								</select>
								</label>
						</p>
				
				<p>
							<label for="<?php echo $this->get_field_id('list_filter'); ?>"><?php _e('Filter by:',GEODIREVENTS_TEXTDOMAIN);?>
								
							 <select class="widefat" id="<?php echo $this->get_field_id('list_filter'); ?>" name="<?php echo $this->get_field_name('list_filter'); ?>">
									
										<option <?php if($list_filter == 'all'){ echo 'selected="selected"'; } ?> value="all"><?php _e('All Events',GEODIREVENTS_TEXTDOMAIN); ?></option>
									 
										 <option <?php if($list_filter == 'today'){ echo 'selected="selected"'; } ?> value="today"><?php _e('Today',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
										<option <?php if($list_filter == 'upcoming'){ echo 'selected="selected"'; } ?> value="upcoming"><?php _e('Upcoming',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
										<option <?php if($list_filter == 'past'){ echo 'selected="selected"'; } ?> value="past"><?php _e('Past',GEODIREVENTS_TEXTDOMAIN); ?></option>
										
								</select>
								</label>
						</p>
        
        <p>
        
            <label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:',GEODIREVENTS_TEXTDOMAIN);?>
            
            <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
            </label>
        </p>
       
        <p>
        	<label for="<?php echo $this->get_field_id('layout'); ?>">
			<?php _e('Layout:',GEODIREVENTS_TEXTDOMAIN);?>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
            	<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
              <option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)',GEODIREVENTS_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view',GEODIREVENTS_TEXTDOMAIN); ?></option>
								
            </select>    
            </label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('listing_width'); ?>"><?php _e('Listing width:',GEODIREVENTS_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('listing_width'); ?>" name="<?php echo $this->get_field_name('listing_width'); ?>" type="text" value="<?php echo esc_attr($listing_width); ?>" />
            </label>
        </p>
				
				<p>
            <label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :',GEODIREVENTS_TEXTDOMAIN);?> 
            <input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
            </label>
        </p>
        
         <p>
        	<label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
			<?php _e('Enable Location Filter:',GEODIREVENTS_TEXTDOMAIN);?>
           	<input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if($add_location_filter) echo 'checked="checked"';?>  value="1"  />
            </label>
        </p>
				
        
        <script type="text/javascript">
				
				function geodir_event_widget_cat_title(val){
				
					jQuery(val).find("option:selected").each(function(i){
						if(i == 0)
							jQuery(val).closest('form').find('#post_type_cats input').val(jQuery(this).html());
						
					});
					
				}
			
		</script>

        
	<?php  
	} 
}
register_widget('geodir_event_postview');	

}

<?php 
//paste this function in location_functions.php
function geodir_get_country_region_location($country = '', $gd_region='')
{
	global $wpdb;

	$location = $wpdb->get_results(
		$wpdb->prepare(
			"select * from ".POST_LOCATION_TABLE." where country_slug = %s AND region_slug = %s ",
			array($country,$gd_region)
		)
	);	
	if(!empty($location))
		return $location;
	else
		return false;	

}


if(!function_exists('register_geodir_location_widgets')){
function register_geodir_location_widgets(){	
	/**
	* Geodirectory popular locations widget *
	**/
	class geodir_popular_location extends WP_Widget {
	
		function geodir_popular_location()
		{
			
			$widget_ops = array('classname' => 'geodir_popular_location', 'description' => __('GD > Popular Locations', GEODIRLOCATION_TEXTDOMAIN) );
			$this->WP_Widget('popular_location', __('GD > Popular Location', GEODIRLOCATION_TEXTDOMAIN), $widget_ops);
		}
		
		
		function widget($args, $instance) 
		{
			global $wp ;
			extract($args, EXTR_SKIP);
			
		
			
			$title = empty($instance['title']) ? __('Popular Locations',GEODIRLOCATION_TEXTDOMAIN) : apply_filters('geodir_popular_location_widget_title', __($instance['title'],GEODIRLOCATION_TEXTDOMAIN));
			
			echo $before_widget;
			?>
			<div class="geodir-category-list-in clearfix">
				<div class="geodir-cat-list clearfix">
				  <?php echo $before_title.__($title).$after_title;?>
			
			<?php
            $location_terms= geodir_get_current_location_terms() ; //locations in sessions
			
			// get all the cities in current region
			$args = array(
						'what' => 'city' ,
						'city_val' => '', 
						'region_val' => '',
						'country_val' =>'',
						'country_non_restricted' =>'' ,
						'region_non_restricted' =>'' ,
						'city_non_restricted' =>'' ,
						'filter_by_non_restricted' => true, 
						'compare_operator' =>'like' ,
						'country_column_name' => 'country_slug' ,
						'region_column_name' => 'region_slug' ,
						'city_column_name' => 'city_slug' ,
						'location_link_part' => true ,
						'order_by' => ' asc ',
						'no_of_records' => '',
						'format' => array( 'type' => 'list',
												'container_wrapper' => 'ul' ,
												'container_wrapper_attr' => '' ,
												'item_wrapper' => 'li' ,
												'item_wrapper_attr' => ''
												)
					);
			if(!empty($location_terms))
			{
				
				if(isset($location_terms['gd_region']) && $location_terms['gd_region'] != '')
				{
					$args['region_val']= $location_terms['gd_region'] ;
					$args['country_val']= $location_terms['gd_country'] ;
				}
				else if(isset($location_terms['gd_country']) && $location_terms['gd_country'] != '')
					$args['country_val']= $location_terms['gd_country'] ;
			}
			echo $geodir_cities_list = geodir_get_location_array($args, false);
			?>
            	</div>
            </div>
            <?php
			echo $after_widget;
		}
		
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}
		
		function form($instance) 
		{
			
			$instance = wp_parse_args( (array) $instance, array('title' => ''));
			
			$title = strip_tags($instance['title']);
			
			?>
			
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', GEODIRLOCATION_TEXTDOMAIN);?>
				
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p>
			
			<?php  
		} 
	}
	register_widget('geodir_popular_location'); 

}
}

/* LOCATION NEIGHBOURHOOD WIDGET */

 
if(!function_exists('register_geodir_neighbourhood_widgets')){
	
	function register_geodir_neighbourhood_widgets(){	
		/**
		* Geodirectory popular locations widget *
		**/
		class geodir_location_neighbourhood extends WP_Widget {
		
			function geodir_location_neighbourhood()
			{
				
				$widget_ops = array('classname' => 'geodir_location_neighbourhood', 'description' => __('GD > Location Neighbourhood', GEODIRLOCATION_TEXTDOMAIN) );
				$this->WP_Widget('location_neighbourhood', __('GD > Location Neighbourhood', GEODIRLOCATION_TEXTDOMAIN), $widget_ops);
			}
			
			
			function widget($args, $instance) 
			{
				
				extract($args, EXTR_SKIP);
				
				echo $before_widget;
				
				$title = empty($instance['title']) ? __('Location Neighbourhood',GEODIRLOCATION_TEXTDOMAIN) : apply_filters('geodir_location_neighbourhood_widget_title', __($instance['title'],GEODIRLOCATION_TEXTDOMAIN));
					
				global $wpdb;
				
				$location_id = '';
				if($gd_city = get_query_var('gd_city')){
					$location_id = $wpdb->get_var($wpdb->prepare("SELECT location_id from ".POST_LOCATION_TABLE." WHERE city_slug=%s",array($gd_city)));
				}
				
				$gd_neighbourhoods = geodir_get_neighbourhoods($location_id);
				
				$location_request = array();
				$location_request[] = get_query_var('gd_country');
				$location_request[] = get_query_var('gd_region');
				$location_request[] = get_query_var('gd_city');
				
				if( $gd_neighbourhoods){ ?>
				
				<div id="geodir-category-list">              
					<div class="geodir-category-list-in clearfix">
						<div class="geodir-cat-list clearfix">
						  <?php echo $before_title.__($title).$after_title;?>
							
							<?php 
							$hood_count = 0;
							echo '<ul>';     
							foreach($gd_neighbourhoods as $gd_neighbourhood){ 	
								
								if($hood_count%15 == 0 )
									echo '</ul><ul>';
									
									//$neigtbourhood_action = add_query_arg( array('neighbourhood'=>$gd_neighbourhood->hood_slug), geodir_get_location_link($location_request) );
									
									$neigtbourhood_action = add_query_arg( array('gd_neighbourhood'=>$gd_neighbourhood->hood_slug), rtrim( geodir_get_location_link('current'), '/' ).'/' );
									
								echo '<li><a href="'.$neigtbourhood_action.'">'.ucwords($gd_neighbourhood->hood_name).'</a></li>';
									$hood_count++;
							 } 
							 echo '</ul>';     
							?>
						  
						</div>  
					</div>
				</div>
				
				<?php } 
				
				echo $after_widget;
			}
			
			function update($new_instance, $old_instance) {
				
				$instance = $old_instance;
				$instance['title'] = strip_tags($new_instance['title']);
				return $instance;
			}
			
			function form($instance) 
			{
				
				$instance = wp_parse_args( (array) $instance, array('title' => ''));
				
				$title = strip_tags($instance['title']);
				
				?>
				
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', GEODIRLOCATION_TEXTDOMAIN);?>
					
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
					</label>
				</p>
				
				<?php  
			} 
		}
		register_widget('geodir_location_neighbourhood'); 
	
	}
}

/* LOCATION NEIGHBOURHOOD WIDGET */

function register_geodir_neighbourhood_posts_widgets(){	
	
	class geodir_neighbourhood_posts extends WP_Widget {
	
		function geodir_neighbourhood_posts()
		{
			
			$widget_ops = array('classname' => 'geodir_neighbourhood_posts', 'description' => __('GD > Popular Neighbourhood Post', GEODIRLOCATION_TEXTDOMAIN) );
			$this->WP_Widget('neighbourhood_posts', __('GD > Popular Neighbourhood Post', GEODIRLOCATION_TEXTDOMAIN), $widget_ops);
		}
		
		
		function widget($args, $instance) 
		{
			
			extract($args, EXTR_SKIP);
			
			echo $before_widget;
			
			$title = empty($instance['title']) ? $instance['category_title'] : apply_filters('widget_title', $instance['title']);
			$hood_post_type = empty($instance['hood_post_type']) ? 'gd_place' : apply_filters('widget_hood_post_type', $instance['hood_post_type']);
			
			$hood_category = empty($instance['hood_category']) ? '0' : apply_filters('widget_hood_category', $instance['hood_category']);
			
			$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
			
			$layout = empty($instance['layout']) ? 'gridview_onehalf' : apply_filters('widget_layout', $instance['layout']);
			
			$add_location_filter = empty($instance['add_location_filter']) ? '0' : apply_filters('widget_layout', $instance['add_location_filter']);
			
			$list_sort = empty($instance['list_sort']) ? 'latest' : apply_filters('widget_list_sort', $instance['list_sort']);
			
			$character_count = empty($instance['character_count']) ? 20 : apply_filters('widget_list_sort', $instance['character_count']);
			
			
			if(empty($title) || $title == 'All' ){
				$title .= ' Neighbourhood '.get_post_type_plural_label($hood_post_type);
			}
			
			
			global $wpdb,$post,$geodir_post_type;
	
			if($geodir_post_type == '')
				$geodir_post_type = 'gd_place';
			
			$all_postypes = geodir_get_posttypes();
			
			$location_id = '';
			
			$not_in_array = array();
			
			if(geodir_is_page('detail') || geodir_is_page('preview') || geodir_is_page('add-listing')){
			
				if(isset($post->post_type) && $post->post_type == $hood_post_type && isset($post->post_location_id)){
					
					$not_in_array[] = $post->ID;
					
					$location_id = $post->post_location_id;
					
				}
				
			}elseif(in_array($geodir_post_type, $all_postypes) && $geodir_post_type == $hood_post_type){
				
				if(isset($_SESSION['gd_city']) && $_SESSION['gd_city'] != ''){
					
					$location_id = $wpdb->get_var(
						$wpdb->prepare(
						"SELECT location_id FROM ".POST_LOCATION_TABLE." WHERE city_slug = %s",
						array($_SESSION['gd_city'])
						)
					);
					
				}else{
					
					$default_location = geodir_get_default_location();
					$location_id = $default_location->location_id;
					
				}	
				
			}
			
			$gd_neighbourhoods = geodir_get_neighbourhoods($location_id);
			
			if( $gd_neighbourhoods){ ?>
			
			<div class="geodir_locations geodir_location_listing">
						<div class="locatin_list_heading clearfix">
							<h3><?php _e(ucfirst($title), GEODIRLOCATION_TEXTDOMAIN);?></h3>
						</div>
						
						<?php 
								$hood_slug_arr = array();
								if(!empty($gd_neighbourhoods)){
									foreach($gd_neighbourhoods as $hoodslug){
										$hood_slug_arr[] = $hoodslug->hood_slug;
									}
								}
								
								$query_args = array( 
								'posts_per_page' => $post_number,
								'is_geodir_loop' => true,
								'post__not_in' => $not_in_array,
								'gd_neighbourhood' => $hood_slug_arr,
								'gd_location' 	 => ($add_location_filter) ? true : false,
								'post_type' => $hood_post_type,
								'order_by' =>$list_sort,
								'excerpt_length' => $character_count);
								
								if( $hood_category != 0 || $hood_category != ''){
									
									$category_taxonomy = geodir_get_taxonomies($hood_post_type); 
									
									$tax_query = array( 'taxonomy' => $category_taxonomy[0],
														'field' => 'id',
														'terms' => $hood_category);
									
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
			
			<?php } 
			
			echo $after_widget;
		}
		
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);	
			$instance['hood_post_type'] = strip_tags($new_instance['hood_post_type']);
			$instance['hood_category'] = strip_tags($new_instance['hood_category']);
			$instance['category_title'] = strip_tags($new_instance['category_title']);
			$instance['post_number'] = strip_tags($new_instance['post_number']);
			$instance['layout'] = strip_tags($new_instance['layout']);
			$instance['list_sort'] = strip_tags($new_instance['list_sort']);
			$instance['character_count'] = $new_instance['character_count'];
			if(isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
			$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
			else
			$instance['add_location_filter'] = '0';
			
			$character_count = $instance['character_count'];
			
			return $instance;
		}
		
		function form($instance) 
		{
			
			$instance = wp_parse_args( 
										(array) $instance, 
										array(	'title' => '', 
														'hood_post_type' => '',
														'hood_category'=>'',
														'category_title'=>'',
														'list_sort'=>'', 
														'list_order'=>'',
														'post_number' => '5',
														'layout'=> 'gridview_onehalf',
														'add_location_filter'=>'1',
														'character_count'=>'20'
													) 
									 );
			
			$title = strip_tags($instance['title']);
			
			$hood_post_type = strip_tags($instance['hood_post_type']);
			
			$hood_category = strip_tags($instance['hood_category']);
			
			$category_title = strip_tags($instance['category_title']);
			
			$list_sort = strip_tags($instance['list_sort']);
			
			$list_order = strip_tags($instance['list_order']);
			
			$post_number = strip_tags($instance['post_number']);
			
			$layout = strip_tags($instance['layout']);
			
			$add_location_filter = strip_tags($instance['add_location_filter']);
			
			$character_count = $instance['character_count'];
			
			?>
			
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',GEODIRLOCATION_TEXTDOMAIN);?>
				
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p>
			
			<p>
					<label for="<?php echo $this->get_field_id('hood_post_type'); ?>"><?php _e('Post Type:',GEODIRLOCATION_TEXTDOMAIN);?>
			
					<?php $postypes = geodir_get_posttypes(); ?>
			
					<select class="widefat" id="<?php echo $this->get_field_id('hood_post_type'); ?>" name="<?php echo $this->get_field_name('hood_post_type'); ?>" onchange="geodir_change_hood_category_list(this)">
						
			<?php foreach($postypes as $postypes_obj){ ?>
							
									<option <?php if($hood_post_type == $postypes_obj){ echo 'selected="selected"'; } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_',$postypes_obj); echo ucfirst($extvalue[1]); ?></option>
							
			<?php } ?>
							
					</select>
					</label>
			</p>
			
			<p id="hood_post_categories">
				<label for="<?php echo $this->get_field_id('hood_category'); ?>"><?php _e('Post Category:',GEODIRLOCATION_TEXTDOMAIN);?>
			
			<?php 
			
			$hood_post_type = ($hood_post_type!= '') ? $hood_post_type : 'gd_place';
			
			$category_taxonomy = geodir_get_taxonomies($hood_post_type); 
			$categories = get_terms( $category_taxonomy, array( 'orderby' => 'count','order' => 'DESC') );
			?>
			
				<select class="widefat" id="<?php echo $this->get_field_id('hood_category'); ?>" name="<?php echo $this->get_field_name('hood_category'); ?>" onchange="jQuery('#<?php echo $this->get_field_id('category_title'); ?>').val(jQuery('#<?php echo $this->get_field_id('hood_category'); ?> option:selected').text());" >
					
					<option <?php if($hood_category == '0'){ echo 'selected="selected"'; } ?> value="0"><?php _e('All',GEODIRLOCATION_TEXTDOMAIN); ?></option>
					
					<?php foreach($categories as $category_obj){ ?>
					
					<option <?php if($hood_category == $category_obj->term_id){ echo 'selected="selected"'; } ?> value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>
						
					<?php } ?>
						
				</select>
			 <input type="hidden" name="<?php echo $this->get_field_name('category_title'); ?>" id="<?php echo $this->get_field_id('category_title'); ?>" value="<?php if($category_title != '') echo $category_title; else echo __('All',GEODIRLOCATION_TEXTDOMAIN);?>" />
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:',GEODIRLOCATION_TEXTDOMAIN);?>
					
				 <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
							<option <?php if($list_sort == 'latest'){ echo 'selected="selected"'; } ?> value="latest"><?php _e('Latest',GEODIRLOCATION_TEXTDOMAIN); ?></option>
							 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured',GEODIRLOCATION_TEXTDOMAIN); ?></option>
							<option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review',GEODIRLOCATION_TEXTDOMAIN); ?></option>
							<option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating',GEODIRLOCATION_TEXTDOMAIN); ?></option>	
					</select>
					</label>
			</p>
							
			<p>
			
				<label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:',GEODIRLOCATION_TEXTDOMAIN);?>
				
				<input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
				</label>
			</p>
							
			<p>
				<label for="<?php echo $this->get_field_id('layout'); ?>">
			<?php _e('Layout:',GEODIRLOCATION_TEXTDOMAIN);?>
					<select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
						<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)',GEODIRLOCATION_TEXTDOMAIN); ?></option>
              <option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)',GEODIRLOCATION_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)',GEODIRLOCATION_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)',GEODIRLOCATION_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view',GEODIRLOCATION_TEXTDOMAIN); ?></option>
					</select>    
					</label>
			</p>
			
			<p>
            <label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :',GEODIRLOCATION_TEXTDOMAIN);?> 
            <input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
            </label>
        </p>
							
			<p style="display:none;">
					<label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
			<?php _e('Enable Location Filter:',GEODIRLOCATION_TEXTDOMAIN);?>
						<input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if($add_location_filter) echo 'checked="checked"';?>  value="1"  />
						</label>
				</p>
			
			<script type="text/javascript">
				function geodir_change_hood_category_list(obj,selected){
				
					var ajax_url = '<?php echo geodir_get_ajax_url(); ?>'
					
					var hood_post_type = obj.value;
					
					var myurl = ajax_url+"&geodir_ajax=admin_ajax&ajax_action=get_cat_dl&post_type="+hood_post_type+"&selected="+selected;
					
					jQuery.ajax({
						type: "GET",
						url: myurl,
						success: function(data){
							
							jQuery(obj).closest('form').find('#hood_post_categories select').html(data);
							
						}
					});
					
					}
					
					<?php if(is_active_widget( false, false, $this->id_base, true )){ ?>
					var hood_post_type = jQuery('#<?php echo $this->get_field_id('hood_post_type'); ?>').val();
					
					<?php } ?>
			
			</script>
	
			<?php   
		} 
	}
	
	register_widget('geodir_neighbourhood_posts'); 

}

/* LOCATION DESCRIPTION WIDGET */
if(!function_exists('register_geodir_location_description_widgets')){
	
	function register_geodir_location_description_widgets(){	
		/**
		* Geodirectory location description widget *
		**/
		class geodir_location_description extends WP_Widget {
		
			function geodir_location_description()
			{
				$widget_ops = array('classname' => 'geodir_location_description', 'description' => __('GD > Location Description', GEODIRLOCATION_TEXTDOMAIN) );
				$this->WP_Widget('location_description', __('GD > Location Description', GEODIRLOCATION_TEXTDOMAIN), $widget_ops);
			}
			
			function widget($args, $instance) 
			{
				global $wpdb, $wp;
				
				extract($args, EXTR_SKIP);
				
				$gd_country = isset($wp->query_vars['gd_country']) ? $wp->query_vars['gd_country'] : '';
				$gd_region = isset($wp->query_vars['gd_region']) ? $wp->query_vars['gd_region'] : '';
				$gd_city = isset($wp->query_vars['gd_city']) ? $wp->query_vars['gd_city'] : '';
				
				$location_title = '';
				//$seo_title = '';
				$seo_desc = '';
				if ($gd_city) {
					$info = geodir_city_info_by_slug($gd_city, $gd_country, $gd_region);
					if (!empty($info)) {
						$location_title =  $info->city;
						$seo_title = $info->city_meta;
						$seo_desc = $info->city_desc;
					}
				} else if (!$gd_city && $gd_region) {
					$info = geodir_location_seo_by_slug($gd_region, 'region', $gd_country);
					if (!empty($info)) {
						//$seo_title = $info->seo_title;
						$seo_desc = $info->seo_desc;
						$location_title = $wpdb->get_var( $wpdb->prepare( "SELECT region FROM ".POST_LOCATION_TABLE." WHERE region_slug!='' AND region_slug=%s ORDER BY location_id ASC", array($gd_region) ) );
					}
				} else if (!$gd_city && !$gd_region && $gd_country) {
					$info = geodir_location_seo_by_slug($gd_country, 'country');
					if (!empty($info)) {
						//$seo_title = $info->seo_title;
						$seo_desc = $info->seo_desc;
						$location_title = $wpdb->get_var( $wpdb->prepare( "SELECT country FROM ".POST_LOCATION_TABLE." WHERE country_slug!='' AND country_slug=%s ORDER BY location_id ASC", array($gd_country) ) );
					}
				}
				$location_desc = $seo_desc;
				if ($location_desc=='') {
					return NULL;
				}
				
				$title = empty($instance['title']) ? __('Location Description',GEODIRECTORY_TEXTDOMAIN) : apply_filters('geodir_location_description_widget_title', __($instance['title'],GEODIRECTORY_TEXTDOMAIN));
				$title = str_replace('%s', $location_title, $title);
				$title = str_replace('%location%', $location_title, $title);
				
				echo $before_widget;
				if ( ! empty( $title ) ) {
					echo $before_title . __($title) . $after_title;
				}
				$location_desc = stripslashes_deep($location_desc);
				echo '<div class="geodir-category-list-in clearfix geodir-location-desc">'.$location_desc.'</div>';
				echo $after_widget;
			}
			
			function update($new_instance, $old_instance) {
				
				$instance = $old_instance;
				$instance['title'] = strip_tags($new_instance['title']);
				return $instance;
			}
			
			function form($instance) 
			{	
				$instance = wp_parse_args( (array) $instance, array('title' => ''));
				
				$title = strip_tags($instance['title']);
	
				?>
				<p>
				  <label for="<?php echo $this->get_field_id('title'); ?>">
				  <?php _e('Title:', GEODIRLOCATION_TEXTDOMAIN);?>
				  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				  </label>
				</p>
				<?php  
			} 
		}
		register_widget('geodir_location_description');
	}
}

// =============================== Near Me Widget ======================================
class geodir_near_me_button_widget extends WP_Widget {
	function geodir_near_me_button_widget() {
	//Constructor
		$widget_ops = array('classname' => 'geodir_near_me_button', 'description' =>__('GD > Near Me Button',GEODIRECTORY_TEXTDOMAIN) );		
		$this->WP_Widget('geodir_near_me_button',  __('GD > Near Me Button',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		// prints the widget
		extract($args, EXTR_SKIP );
		
		$title = empty( $instance['title'] ) ? __( 'Near Me', GEODIRECTORY_TEXTDOMAIN ) : apply_filters( 'widget_title', __( $instance['title'], GEODIRECTORY_TEXTDOMAIN ) );
		//$count = empty( $instance['count'] ) ? '5' : apply_filters( 'widget_count', $instance['count'] );
		
		//$comments_li = geodir_get_recent_reviews( 30, $count, 100, false );?>
		<script>
		
		function gdGetLocationNearMe(){
	
	//jQuery('.snear').removeClass("near-country near-region near-city");// remove any location classes
	
	//if(box && box.prop('checked') != true){gdClearUserLoc();return;}
	
	// Try HTML5 geolocation
  if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
		lat = position.coords.latitude;									  
		lon = position.coords.longitude;
		my_location=1;
		if(typeof gdSetupUserLoc === 'function') { gdSetupUserLoc();}
	
		
		 jQuery.ajax({
       // url: url,
        url: "<?php echo admin_url('admin-ajax.php'); ?>",
        type: 'POST',
        dataType: 'html',
		data: {action: 'gd_location_manager_set_user_location',lat:lat,lon:lon,myloc:1},
        beforeSend: function () {
        },
        success: function (data, textStatus, xhr) {
			window.location.href="<?php echo geodir_get_location_link('base').'me/';?>";
		},
        error: function (xhr, textStatus, errorThrown) {
			alert(textStatus);
        }
    });
		
		
		
		

  },gdLocationError,gdLocationOptions); }else {
    // Browser doesn't support Geolocation
    alert('error');
  }
	
}

</script>
		
			<?php
			echo $before_widget;
			?>
			<button type="button" onclick=" gdGetLocationNearMe();"><?php echo $title;?></button>
			<?php 
			echo $after_widget;
		
	}
	
	function update($new_instance, $old_instance) {
	//save the widget
		$instance = $old_instance;		
		$instance['title'] = strip_tags($new_instance['title']);
 		return $instance;
	}
	function form($instance) {
	//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );		
		$title = strip_tags($instance['title']);
 ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
        
<?php
	}}
	

add_action('widgets_init', 'register_geodir_near_me_button_widget');
function register_geodir_near_me_button_widget() {
   register_widget('geodir_near_me_button_widget');
}


<?php

/* Plugin Activation Function */
function geodir_custom_post_type_activation(){

	if (get_option('geodir_installed')) {
		 
		 add_option('geodir_custom_post_type_activation_redirect', 1);
		 
	}
		
}

function geodir_custom_post_type_uninstall(){
	if ( ! isset($_REQUEST['verify-delete-adon']) ) 
	{
		$plugins = isset( $_REQUEST['checked'] ) ? (array) $_REQUEST['checked'] : array();
			//$_POST = from the plugin form; $_GET = from the FTP details screen.
			
			wp_enqueue_script('jquery');
					require_once(ABSPATH . 'wp-admin/admin-header.php');
					printf( '<h2>%s</h2>' ,__( 'Warning!!' , GEODIR_CP_TEXTDOMAIN) );
					printf( '%s<br/><strong>%s</strong><br /><br />%s <a href="http://wpgeodirectory.com">%s</a>.' , __('You are about to delete a Geodirectory Ad-on which has important option and custom data associated to it.' ,GEODIR_CP_TEXTDOMAIN) ,__('Deleting this and activating another version, will be treated as a new installation of plugin, so all the data will be lost.', GEODIR_CP_TEXTDOMAIN), __('If you have any problem in upgrading the plugin please contact Geodirectroy', GEODIR_CP_TEXTDOMAIN) , __('support' ,GEODIR_CP_TEXTDOMAIN) ) ;
					
	?><br /><br />
		<form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display:inline;">
						<input type="hidden" name="verify-delete" value="1" />
						<input type="hidden" name="action" value="delete-selected" />
						<input type="hidden" name="verify-delete-adon" value="1" />
						<?php
							foreach ( (array) $plugins as $plugin )
								echo '<input type="hidden" name="checked[]" value="' . esc_attr($plugin) . '" />';
						?>
						<?php wp_nonce_field('bulk-plugins') ?>
						<?php submit_button(  __( 'Delete plugin files only' , GEODIR_CP_TEXTDOMAIN ), 'button', 'submit', false ); ?>
					</form>
					<form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display:inline;">
						<input type="hidden" name="verify-delete" value="1" />
						<input type="hidden" name="action" value="delete-selected" />
                        <input type="hidden" name="verify-delete-adon" value="1" />
						<input type="hidden" name="verify-delete-adon-data" value="1" />
						<?php
							foreach ( (array) $plugins as $plugin )
								echo '<input type="hidden" name="checked[]" value="' . esc_attr($plugin) . '" />';
						?>
						<?php wp_nonce_field('bulk-plugins') ?>
						<?php submit_button(  __( 'Delete both plugin files and data' , GEODIR_CP_TEXTDOMAIN) , 'button', 'submit', false ); ?>
					</form>
					
	<?php
		require_once(ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}
	
	
	if ( isset($_REQUEST['verify-delete-adon-data']) ) 
	{	
		$geodir_custom_post_types = get_option('geodir_custom_post_types');
		$geodir_post_types = get_option( 'geodir_post_types' );
		$geodir_taxonomies = get_option('geodir_taxonomies');
		
		if(!empty($geodir_custom_post_types))
		{
			foreach($geodir_custom_post_types as $key)
			{
				if (array_key_exists($key.'category', $geodir_taxonomies))
				{
					unset($geodir_taxonomies[$key.'category']);
					update_option( 'geodir_taxonomies', $geodir_taxonomies );
				}
				
				if (array_key_exists($key.'_tags', $geodir_taxonomies))
				{
					unset($geodir_taxonomies[$key.'_tags']);
					update_option( 'geodir_taxonomies', $geodir_taxonomies );
				}
				
				if (array_key_exists($key, $geodir_post_types))
				{
					unset($geodir_post_types[$key]);
					update_option( 'geodir_post_types', $geodir_post_types );
					geodir_custom_post_type_ajax($key); /* delete all releated data */
				}
				
				if (array_key_exists($key, $geodir_custom_post_types))
				{
					unset($geodir_custom_post_types[$key]);
					update_option( 'geodir_custom_post_types', $geodir_custom_post_types );
				}
					
			}
		}
	}
}

function geodir_cp_activation_redirect(){
    if (get_option('geodir_custom_post_type_activation_redirect', false))
	{
        delete_option('geodir_custom_post_type_activation_redirect');
        wp_redirect(admin_url('admin.php?page=geodirectory&tab=geodir_manage_custom_posts')); 
    }
}


function geodir_cp_from_submit_handler(){

	global $plugin_prefix, $wpdb;
	if(isset($_REQUEST['geodir_save_post_type']))
	{
	
			$custom_post_type	= (trim($_REQUEST['geodir_custom_post_type']));
			$listing_slug 		= (trim($_REQUEST['geodir_listing_slug']));
			$listing_order 		= trim($_REQUEST['geodir_listing_order']);
			$categories 			= $_REQUEST['geodir_categories'];
			$tags 						= isset($_REQUEST['geodir_tags']) ? $_REQUEST['geodir_tags'] : '';
			$name 						= ($_REQUEST['geodir_name']);//htmlentities(trim($_REQUEST['geodir_name']));
			$singular_name 		= (trim($_REQUEST['geodir_singular_name']));
			$add_new 					= (trim($_REQUEST['geodir_add_new']));
			$add_new_item 		= (trim($_REQUEST['geodir_add_new_item']));
			$edit_item 				= (trim($_REQUEST['geodir_edit_item']));
			$new_item 				= (trim($_REQUEST['geodir_new_item']));
			$view_item 				= (trim($_REQUEST['geodir_view_item']));
			$search_item 			= (trim($_REQUEST['geodir_search_item']));
			$not_found 				= (trim($_REQUEST['geodir_not_found']));
			$not_found_trash 	= (trim($_REQUEST['geodir_not_found_trash']));
			$support 					= $_REQUEST['geodir_support'];
			$description 			= (trim($_REQUEST['geodir_description']));
			$menu_icon 				= (trim($_REQUEST['geodir_menu_icon']));
			$can_export 			= $_REQUEST['geodir_can_export'];
			$geodir_cp_meta_keyword = $_REQUEST['geodir_cp_meta_keyword'];
			$geodir_cp_meta_description = $_REQUEST['geodir_cp_meta_description'];
			$label_post_profile 	= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_profile']));
			$label_post_info 		= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_info']));
			$label_post_images 		= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_images']));
			$label_post_map 		= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_map']));
			$label_reviews 			= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_reviews']));
			$label_related_listing 	= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_related_listing']));
			
			if($can_export == 'true')
			{
				$can_export = true;
			}
			else
			{
				$can_export = false;
			}
			
			$custom_post_type	= geodir_clean( $custom_post_type ); // erase special characters from string
			$listing_slug 		= geodir_clean( $listing_slug ); // erase special characters from string
			
			
			if(isset($_REQUEST['posttype']) && $_REQUEST['posttype'] != '')
			{
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				$post_type_array = $geodir_post_types[$_REQUEST['posttype']];
			}
			
			
			if($custom_post_type != '' && $listing_slug != '')
			{
						
				if(empty($post_type_array))
				{
						$is_custom = 1; //check post type create by custom or any other add-once
						
						$posttypes_array = get_option( 'geodir_post_types' );
						
						$post_type = $custom_post_type;
						$custom_post_type = 'gd_'.$custom_post_type;
						
						if (array_key_exists($custom_post_type, $posttypes_array))
						{
							$error[] = __( 'Post Type already exists.', GEODIR_CP_TEXTDOMAIN );
						}
						
						foreach($posttypes_array as $key=>$value)
						{
							if($value['has_archive'] == $listing_slug)
							{
								$error[] = __( 'Listing Slug already exists.', GEODIR_CP_TEXTDOMAIN );
								break;
							}
						}
						
				}
				else
				{
						
						$post_type = preg_replace('/gd_/', '', $_REQUEST['posttype'], 1);	
						$custom_post_type = $_REQUEST['posttype'];
						
						$is_custom = isset($post_type_array['is_custom']) ? $post_type_array['is_custom'] : ''; /*check post type create by custom or any other add-once */
						
						//Edit case check duplicate listing slug
						if($post_type_array['has_archive'] != $listing_slug)
						{
							$posttypes_array = get_option( 'geodir_post_types' );
						
							foreach($posttypes_array as $key=>$value)
							{
								if($value['has_archive'] == $listing_slug)
								{
									$error[] = __( 'Listing Slug already exists.', GEODIR_CP_TEXTDOMAIN );
									break;
								}
							}
						}
				
				}
				
				
				if(empty($error))
				{		
						
						if(!empty($post_type_array))
						{
						
							if(!$categories)
							{
								
								$geodir_taxonomies = get_option('geodir_taxonomies');
								
								if(array_key_exists($custom_post_type.'category', $geodir_taxonomies))
								{
									unset($geodir_taxonomies[$custom_post_type.'category']);
									
									update_option( 'geodir_taxonomies', $geodir_taxonomies );
											
								}
							
							}

							if(!$tags)
							{
								
								$geodir_taxonomies = get_option('geodir_taxonomies');
								
								if (array_key_exists($custom_post_type.'_tags', $geodir_taxonomies))
								{
									unset($geodir_taxonomies[$custom_post_type.'_tags']);
									
									update_option( 'geodir_taxonomies', $geodir_taxonomies );
											
								}
								
							}
						
						}
						
						
						$taxonomies = array();
						if ( $categories ) {
							$taxonomies[] = $custom_post_type.'category';
							$categories =  $custom_post_type.'category';
						}
						
						if ( $tags ) {
							$taxonomies[] = $custom_post_type.'_tags';
							$tags =  $custom_post_type.'_tags';
						}
						
						if ( $categories ) {
							$gd_placecategory = array();
							$gd_placecategory['object_type']= $custom_post_type;
							$gd_placecategory['listing_slug']= $listing_slug;
							
							$gd_placecategory['args'] = array (
								'public' 		=> true,
								'hierarchical'  => true,
								'rewrite' 		=> array ( 'slug' => $listing_slug, 'with_front' => false, 'hierarchical' => true ),
								'query_var'		=> true,
								'labels' 		=> array (
									'name'          => __( ucfirst($post_type).' Categories', GEODIR_CP_TEXTDOMAIN ),
									'singular_name' => __( ucfirst($post_type).' Category', GEODIR_CP_TEXTDOMAIN ),
									'search_items'  => __( 'Search '.ucfirst($post_type).' Categories', GEODIR_CP_TEXTDOMAIN ),
									'popular_items' => __( 'Popular '.ucfirst($post_type).' Categories', GEODIR_CP_TEXTDOMAIN ),
									'all_items'     => __( 'All '.ucfirst($post_type).' Categories', GEODIR_CP_TEXTDOMAIN ),
									'edit_item'     => __( 'Edit '.ucfirst($post_type).' Category', GEODIR_CP_TEXTDOMAIN ),
									'update_item'   => __( 'Update '.ucfirst($post_type).' Category', GEODIR_CP_TEXTDOMAIN ),
									'add_new_item'  => __( 'Add New '.ucfirst($post_type).' Category', GEODIR_CP_TEXTDOMAIN ),
									'new_item_name' => __( 'New '.ucfirst($post_type).' Category', GEODIR_CP_TEXTDOMAIN ),
									'add_or_remove_items' => __( 'Add or remove '.ucfirst($post_type).' categories', GEODIR_CP_TEXTDOMAIN ),
								),
								'show_in_nav_menus' => ( !empty( $_REQUEST['geodir_disable_nav_menus']['cats'] ) ? 0 : 1 ),
							);
							$geodir_taxonomies = get_option( 'geodir_taxonomies' );
							$geodir_taxonomies[$categories] = $gd_placecategory;
							update_option( 'geodir_taxonomies', $geodir_taxonomies );
						}
						
						if ( $tags ) {
							$gd_placetags = array();
							$gd_placetags['object_type']	= $custom_post_type;
							$gd_placetags['listing_slug']	= $listing_slug.'/tags';
							
							$gd_placetags['args'] = array (
								'public' 			=> true,
								'hierarchical' 		=> false,
								'rewrite' 			=> array ( 
															'slug' => $listing_slug.'/tags', 
															'with_front' => false, 'hierarchical' => false 
														),
								'query_var' 		=> true,
								'labels' 			=> array (
									'name'          => __( ucfirst($post_type).' Tags', GEODIR_CP_TEXTDOMAIN ),
									'singular_name' => __( ucfirst($post_type).' Tag', GEODIR_CP_TEXTDOMAIN ),
									'search_items'  => __( 'Search '.ucfirst($post_type).' Tags', GEODIR_CP_TEXTDOMAIN ),
									'popular_items' => __( 'Popular '.ucfirst($post_type).' Tags', GEODIR_CP_TEXTDOMAIN ),
									'all_items'     => __( 'All '.ucfirst($post_type).' Tags', GEODIR_CP_TEXTDOMAIN ),
									'edit_item'     => __( 'Edit '.ucfirst($post_type).' Tag', GEODIR_CP_TEXTDOMAIN ),
									'update_item'   => __( 'Update '.ucfirst($post_type).' Tag', GEODIR_CP_TEXTDOMAIN ),
									'add_new_item'  => __( 'Add New '.ucfirst($post_type).' Tag', GEODIR_CP_TEXTDOMAIN ),
									'new_item_name' => __( 'New '.ucfirst($post_type).' Tag Name', GEODIR_CP_TEXTDOMAIN ),
									'add_or_remove_items' => __( 'Add or remove '.ucfirst($post_type).' tags', GEODIR_CP_TEXTDOMAIN ),
									'choose_from_most_used' => __( 'Choose from the most used '.ucfirst($post_type).' tags', GEODIR_CP_TEXTDOMAIN ),
									'separate_items_with_commas' => __( 'Separate '.ucfirst($post_type).' tags with commas', GEODIR_CP_TEXTDOMAIN ),
								),
								'show_in_nav_menus' => ( !empty( $_REQUEST['geodir_disable_nav_menus']['tags'] ) ? 0 : 1 ),
							);
							
							$geodir_taxonomies = get_option( 'geodir_taxonomies' );
							$geodir_taxonomies[$tags] = $gd_placetags;
							update_option( 'geodir_taxonomies', $geodir_taxonomies );
						}
						
						
						if(empty($name)) $name = __( ucfirst($post_type), GEODIR_CP_TEXTDOMAIN );
						if(empty($singular_name)) $singular_name = __( ucfirst($post_type), GEODIR_CP_TEXTDOMAIN );
						if(empty($add_new)) $add_new = __( 'Add New '.ucfirst($post_type), GEODIR_CP_TEXTDOMAIN );
						if(empty($add_new_item)) $add_new_item = __( 'Add New Item', GEODIR_CP_TEXTDOMAIN );
						if(empty($edit_item)) $edit_item = __( 'Edit Item', GEODIR_CP_TEXTDOMAIN );
						if(empty($new_item)) $new_item = __( 'New Item', GEODIR_CP_TEXTDOMAIN );
						if(empty($view_item)) $view_item = __( 'View Item', GEODIR_CP_TEXTDOMAIN );
						if(empty($search_item)) $search_item = __( 'Search Items', GEODIR_CP_TEXTDOMAIN );
						if(empty($not_found)) $not_found = __( 'Not Found', GEODIR_CP_TEXTDOMAIN );
						if(empty($not_found_trash)) $not_found_trash = __( 'Not Found In Trash', GEODIR_CP_TEXTDOMAIN );
						
						$labels = array (
							'name'         		=> 	ucfirst($name),
							'singular_name' 	=> 	ucfirst($singular_name),
							'add_new'       	=>	ucfirst($add_new),
							'add_new_item'  	=> 	ucfirst($add_new_item),
							'edit_item'     	=> 	ucfirst($edit_item),
							'new_item'      	=> 	ucfirst($new_item),
							'view_item'     	=> 	ucfirst($view_item),
							'search_items'  	=> 	ucfirst($search_item),
							'not_found'     	=> 	ucfirst($not_found),
							'not_found_in_trash' => ucfirst($not_found_trash),
							'label_post_profile' 	=> $label_post_profile,
							'label_post_info' 		=> $label_post_info,
							'label_post_images' 	=> $label_post_images,
							'label_post_map' 		=> $label_post_map,
							'label_reviews'			=> $label_reviews,
							'label_related_listing' => $label_related_listing
						);

						$place_default = array (
											'labels' 			=> $labels,
											'can_export' 		=> $can_export,
											'capability_type'	=> 'post',
											'description'		=> $description,
											'has_archive' 		=> $listing_slug,
											'hierarchical' 		=> false,
											'map_meta_cap' 		=> true,
											'menu_icon' 		=> apply_filters('geodir_custom_post_type_default_menu_icon', $menu_icon),
											'public'			=> true,
											'query_var' 		=> true,
											'rewrite' 			=> array (
																		'slug' => $listing_slug.'/%gd_taxonomy%', 
																		'with_front' => false, 
																		'hierarchical' => true
																	),
											'supports' 			=> $support,
											'taxonomies' 		=> $taxonomies,
											'is_custom' 		=> $is_custom,
											'listing_order'     => $listing_order,
											'seo'         		=> array (
																		'meta_keyword'=> $geodir_cp_meta_keyword,
																		'meta_description'=> $geodir_cp_meta_description
																	),
											'show_in_nav_menus' => ( !empty( $_REQUEST['geodir_disable_nav_menus']['posts'] ) ? 0 : 1 ),
										);
						
						update_option( 'temp_post_type' , $place_default ) ;
						$geodir_post_types = get_option( 'geodir_post_types' );
						$geodir_post_types[$custom_post_type] = $place_default;
						update_option( 'geodir_post_types', $geodir_post_types );
						
						//ADD NEW CUSTOM POST TYPE IN SHOW POST TYPE NAVIGATIONS 
						
						if(!isset($_REQUEST['posttype'])){
					
							$get_posttype_settings_options = array('geodir_add_posttype_in_listing_nav','geodir_allow_posttype_frontend','geodir_add_listing_link_add_listing_nav','geodir_add_listing_link_user_dashboard','geodir_listing_link_user_dashboard','geodir_favorite_link_user_dashboard');
							
							foreach($get_posttype_settings_options as $get_posttype_settings_options_obj){
								$geodir_post_types_listing = get_option( $get_posttype_settings_options_obj);
								
								if(empty($geodir_post_types_listing) || (is_array($geodir_post_types_listing) && !in_array($custom_post_type, $geodir_post_types_listing))){
								
								$geodir_post_types_listing[] = $custom_post_type;
								update_option( $get_posttype_settings_options_obj, $geodir_post_types_listing );
								
								}
							}
					}
						
						
						// Save post types in default table
						if(empty($post_type_array))
						{
							
							$geodir_custom_post_types = get_option('geodir_custom_post_types');
							
							if(!$geodir_custom_post_types)
								$geodir_custom_post_types = array();
							
							if (!array_key_exists($custom_post_type, $geodir_custom_post_types))
							{
								$geodir_custom_post_types[$custom_post_type] = $custom_post_type;
								
								update_option( 'geodir_custom_post_types', $geodir_custom_post_types );
							}
								
						}
						
						// Table for storing custom post type attribute - these are user defined
						
						$collate = '';
						if($wpdb->has_cap( 'collation' )) {
							if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
							if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
						}
						
						$newtable_name = $plugin_prefix.$custom_post_type.'_detail';
						
						$newposttype_detail = "CREATE TABLE IF NOT EXISTS ".$newtable_name." (
										`post_id` int(11) NOT NULL,
										`post_title` text NULL DEFAULT NULL,
										`post_status` varchar(20) NULL DEFAULT NULL,
										`default_category` INT NULL DEFAULT NULL,
										`post_tags` varchar(254) NULL DEFAULT NULL,
										`post_location_id` int(11) NOT NULL,
										`marker_json` text NULL DEFAULT NULL,
										`claimed` ENUM( '1', '0' ) NULL DEFAULT '0',
										`businesses` ENUM( '1', '0' ) NULL DEFAULT '0',
										`is_featured` ENUM( '1', '0' ) NULL DEFAULT '0',
										`featured_image` VARCHAR( 254 ) NULL DEFAULT NULL,
										`paid_amount` DOUBLE NOT NULL DEFAULT '0', 
										`package_id` INT(11) NOT NULL DEFAULT '0',
										`alive_days` INT(11) NOT NULL DEFAULT '0',
										`paymentmethod` varchar(30) NULL DEFAULT NULL,
										`expire_date` VARCHAR( 100 ) NULL DEFAULT NULL,
										`submit_time` varchar(25) NULL DEFAULT NULL,
										`submit_ip` varchar(15) NULL DEFAULT NULL,
										`overall_rating` float(11) DEFAULT NULL, 
										`rating_count` INT(11) DEFAULT '0',
										`post_locations` VARCHAR( 254 ) NULL DEFAULT NULL,
										PRIMARY KEY (`post_id`)) $collate ";
						
						$wpdb->query($newposttype_detail);
						
						do_action('geodir_after_custom_detail_table_create', $custom_post_type, $newtable_name);
						
						$package_info = array() ;
						/*$package_info = apply_filters('geodir_post_package_info' , $package_info , '', $custom_post_type);
						$package_id = $package_info->pid;*/
						
						$package_info = geodir_post_package_info($package_info , '', $custom_post_type);
						$package_id = $package_info->pid;
						
						if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages)",array($package_id))))
						{
							
							$table = $plugin_prefix.$custom_post_type.'_detail';
							
							$wpdb->query($wpdb->prepare("UPDATE ".$table." SET package_id=%d",array($package_id)));
							
							$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages=%s WHERE post_type=%s",array($package_id,$custom_post_type)));
							
						}
						
						
						geodir_cp_create_default_fields($custom_post_type, $package_id);
						
						$msg = 	__( 'Post type created successfully.', GEODIR_CP_TEXTDOMAIN );
						
						if(isset($_REQUEST['posttype']) && $_REQUEST['posttype'] != ''){
							$msg = 	__( 'Post type updated successfully.', GEODIR_CP_TEXTDOMAIN );
						}
						
						/// call the geodirectory core function to register all posttypes again.
						geodir_register_post_types();
						// call the geodirectory core function to register all taxonomies again.
						geodir_register_taxonomies();
						
						
						geodir_flush_rewrite_rules();
						
						geodir_set_user_defined_order() ;
						
						$msg = urlencode($msg);
						
						$redirect_to = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&cp_success='.$msg;
						
						wp_redirect( $redirect_to );
						
						exit;
						
				}
				else
				{
					
					global $cp_error;
					foreach($error as $err)
					{
						$cp_error .= '<div id="message" style="color:#FF0000;" class="updated fade"><p><strong>' . $err . '</strong></p></div>';
					}
					
				}
			
		}
	
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'cp_delete')
	{
		if($_REQUEST['posttype'])
		{
				
				$geodir_taxonomies = get_option('geodir_taxonomies');
				
				if (array_key_exists($_REQUEST['posttype'].'category', $geodir_taxonomies))
				{
					unset($geodir_taxonomies[$_REQUEST['posttype'].'category']);
					update_option( 'geodir_taxonomies', $geodir_taxonomies );
				}
				
				
				if (array_key_exists($_REQUEST['posttype'].'_tags', $geodir_taxonomies))
				{
					unset($geodir_taxonomies[$_REQUEST['posttype'].'_tags']);
					update_option( 'geodir_taxonomies', $geodir_taxonomies );
				}
				
				
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				if (array_key_exists($_REQUEST['posttype'], $geodir_post_types))
				{
					unset($geodir_post_types[$_REQUEST['posttype']]);
					update_option( 'geodir_post_types', $geodir_post_types );
				}
				
				//UPDATE SHOW POST TYPES NAVIGATION OPTIONS 
					
				$get_posttype_settings_options = array('geodir_add_posttype_in_listing_nav','geodir_allow_posttype_frontend','geodir_add_listing_link_add_listing_nav','geodir_add_listing_link_user_dashboard','geodir_listing_link_user_dashboard','geodir_favorite_link_user_dashboard');
									
				foreach($get_posttype_settings_options as $get_posttype_settings_options_obj)
				{
					$geodir_post_types_listing = get_option( $get_posttype_settings_options_obj);
					
					if (in_array($_REQUEST['posttype'], $geodir_post_types_listing))
					{
						$geodir_update_post_type_nav = array_diff($geodir_post_types_listing, array($_REQUEST['posttype']));
						update_option( $get_posttype_settings_options_obj, $geodir_update_post_type_nav );	
					}
				}
				
				//END CODE OPTIONS				
				
				geodir_flush_rewrite_rules() ;
				
				$msg = 	__( 'Post type deleted successfully.', GEODIR_CP_TEXTDOMAIN );
		
				$msg = urlencode($msg);
				
				$redirect_to = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&confirm=true&geodir_customposttype='.$_REQUEST['posttype'].'&cp_success='.$msg;
				
				wp_redirect( $redirect_to );
				
				exit;
					
		}
	}
	
}

function geodir_set_user_defined_order()
{
	$geodir_post_types = get_option( 'geodir_post_types' );
	$geodir_post_types_in_new_order = array() ;
	$geodir_temp_post_types = array() ;
	$geodir_temp_post_type_keys = array() ;
	foreach($geodir_post_types as $key =>$value)
	{
		if(!empty($geodir_temp_post_types ) )
		{
			if(!isset($value['listing_order']) || $value['listing_order']==0 || array_key_exists($value['listing_order'], $geodir_temp_post_types ))
				$value['listing_order'] = max(array_keys($geodir_temp_post_types))+1 ;
		}
		else
		{
			if(!isset($value['listing_order']) || $value['listing_order']==0 )
				$value['listing_order'] =1 ;
		}
		$geodir_temp_post_types[$value['listing_order']] = $value;
		$geodir_temp_post_type_keys[$value['listing_order']] = $key ;
	}
	
	ksort($geodir_temp_post_types) ;
	foreach($geodir_temp_post_types as $key => $value)
	{
		$geodir_post_types_in_new_order[$geodir_temp_post_type_keys[$key]] =  $value ;
	}
	
	update_option( 'geodir_post_types', $geodir_post_types_in_new_order );
	
}

function geodir_cp_create_default_fields($custom_post_type, $package_id='')
{
	
	$fields = array();
	
	$fields[]	= array(	'listing_type' 	=> $custom_post_type, 
						'data_type' 	=> 'VARCHAR', 
						'field_type' 	=> 'taxonomy', 
						'admin_title' 	=> __( 'Category', GEODIR_CP_TEXTDOMAIN ), 
						'admin_desc' 	=> __( 'Select listing category from here. Select at least one category', GEODIR_CP_TEXTDOMAIN ), 
						'site_title' 	=> __('Category', GEODIR_CP_TEXTDOMAIN ), 
						'htmlvar_name' 	=> $custom_post_type.'category', 
						'default_value'	=> '', 
						'is_default'  	=> '1',
						'is_admin'			=> '1',
						'is_required'	=> '1', 
						'show_on_pkg' => array($package_id),
						'clabels'		=> __('Category', GEODIR_CP_TEXTDOMAIN ));
	
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'address', 
							'admin_title' 	=> __('Address', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> ADDRESS_MSG, 
							'site_title' 	=> __('Address', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'post', 
							'default_value'	=> '', 
							'option_values' => '', 
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'is_required'	=> '1',
							'required_msg'	=> __('Address fields are required', GEODIR_CP_TEXTDOMAIN ),
							'clabels'		=> 'Address',
							'extra'	=> array(	'show_city'=> 1 , 'city_lable' => __('City', GEODIR_CP_TEXTDOMAIN ),
												'show_region' => 1, 'region_lable' => __('Region', GEODIR_CP_TEXTDOMAIN ),
												'show_country' => 1, 'country_lable' => __('Country', GEODIR_CP_TEXTDOMAIN ),
												'show_zip' => 1, 'zip_lable' => __('Zip/Post Code', GEODIR_CP_TEXTDOMAIN ),
												'show_map' => 1, 'map_lable' => __('Set Address On Map', GEODIR_CP_TEXTDOMAIN ),
												'show_mapview' => 1, 'mapview_lable' => __('Select Map View', GEODIR_CP_TEXTDOMAIN ),
												'show_mapzoom' => 1, 'mapzoom_lable' => 'hidden',
												'show_latlng' => 1));
							
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'text', 
							'admin_title' 	=> __( 'Time', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __('Enter Business or Listing Timing Information.<br/>eg. : 10.00 am to 6 pm every day', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __( 'Time', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'timing', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1', 
							'show_on_pkg' => array($package_id),
							'clabels'		=> __( 'Time', GEODIR_CP_TEXTDOMAIN ));
	
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'phone', 
							'admin_title' 	=> __( 'Phone', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __( 'You can enter phone number,cell phone number etc.', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __( 'Phone', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'contact', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1', 
							'show_on_pkg' => array($package_id),
							'clabels'		=> __( 'Phone', GEODIR_CP_TEXTDOMAIN ));
	
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'email', 
							'admin_title' 	=> __( 'Email', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __( 'You can enter your business or listing email.', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __( 'Email', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'email', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id), 
							'clabels'		=> __( 'Email', GEODIR_CP_TEXTDOMAIN ));												
							
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'url', 
							'admin_title' 	=> __( 'Website', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __( 'You can enter your business or listing website.', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __('Website', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'website', 
							'default_value'	=> '', 
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Website', GEODIR_CP_TEXTDOMAIN ));
	
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'url', 
							'admin_title' 	=> __('Twitter', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __('You can enter your business or listing twitter url.', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __('Twitter', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'twitter', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Twitter', GEODIR_CP_TEXTDOMAIN ));
	
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'VARCHAR', 
							'field_type' 	=> 'url', 
							'admin_title' 	=> __('Facebook', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __('You can enter your business or listing facebook url.', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __('Facebook', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'facebook', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '1',
							'is_admin'			=> '1', 
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Facebook', GEODIR_CP_TEXTDOMAIN ));
							
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'TEXT', 
							'field_type' 	=> 'textarea', 
							'admin_title' 	=> __('Video', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __('Add video code here, YouTube etc.', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __('Video', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'video', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '0',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Video', GEODIR_CP_TEXTDOMAIN ));
	
	$fields[]	= array(	'listing_type'	=> $custom_post_type, 
							'data_type' 	=> 'TEXT', 
							'field_type' 	=> 'textarea', 
							'admin_title' 	=> __('Special Offers', GEODIR_CP_TEXTDOMAIN ), 
							'admin_desc' 	=> __('Note: List out any special offers (optional)', GEODIR_CP_TEXTDOMAIN ), 
							'site_title' 	=> __('Special Offers', GEODIR_CP_TEXTDOMAIN ), 
							'htmlvar_name' 	=> 'special_offers', 
							'default_value'	=> '', 
							'option_values' => '',
							'is_default'  	=> '0',
							'is_admin'			=> '1',
							'show_on_pkg' => array($package_id),
							'clabels'		=> __('Special Offers', GEODIR_CP_TEXTDOMAIN ));																								
	
	$fields = apply_filters('geodir_add_custom_field',$fields,$custom_post_type,$package_id);
	
	foreach($fields as $field_index => $field )
	{ 
		geodir_custom_field_save( $field ); 
	}
}


function geodir_cp_ajax_url(){
	return admin_url('admin-ajax.php?action=geodir_cp_ajax_action');
}

function geodir_custom_post_type_ajax($post_type = ''){
	
	global $wpdb, $plugin_prefix;
	
	if($post_type == '')
		$post_type = $_REQUEST['geodir_deleteposttype'];
	
	$args = array( 'post_type' => $post_type, 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
	
	
	/* ------- START DELETE ALL TERMS ------- */
	
	$terms = $wpdb->get_results("SELECT term_id, taxonomy FROM ".$wpdb->prefix."term_taxonomy WHERE taxonomy IN ('".$post_type."category', '".$post_type."_tags')");
	
	if(!empty($terms)){
		foreach( $terms as $term ){
			wp_delete_term($term->term_id,$term->taxonomy);
		}
	}
	
	$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name LIKE '%tax_meta_".$post_type."_%'");
	
	
	/* ------- END DELETE ALL TERMS ------- */
	
	$geodir_all_posts = get_posts( $args );
	
	if(!empty($geodir_all_posts)){
	
		foreach($geodir_all_posts as $posts)
		{
			wp_delete_post($posts->ID);
		}
	}
	
	do_action('geodir_after_post_type_deleted'  , $post_type);

	$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type=%s",array($post_type)));
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." WHERE post_type=%s",array($post_type)));
	
	$detail_table =  $plugin_prefix . $post_type . '_detail';
	
	$wpdb->query("DROP TABLE IF EXISTS ".$detail_table);
	
	$msg = 	__( 'Post type related data deleted successfully.', GEODIR_CP_TEXTDOMAIN );
	
	$msg = urlencode($msg);
	
	if(isset($_REQUEST['geodir_deleteposttype']) && $_REQUEST['geodir_deleteposttype']){
	
		$redirect_to = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&cp_success='.$msg;
		wp_redirect( $redirect_to );
	
		exit;
	}
	
}


function geodir_payment_remove_unnecessary_fields(){
	global $wpdb, $plugin_prefix;
	
	if(!get_option('geodir_payment_remove_unnecessary_fields')){
		
		$all_postypes = geodir_get_posttypes();
		
		foreach($all_postypes as $post_type){
			
			$table_name = $plugin_prefix.$post_type.'_detail';
			
			if($wpdb->get_var("SHOW COLUMNS FROM ".$table_name." WHERE field = 'categories'"))
				$wpdb->query("ALTER TABLE `".$table_name."` DROP `categories`");
			
		}
		
		update_option('geodir_payment_remove_unnecessary_fields', '1');
		
	}
}


function geodir_display_cp_messages(){
	
	if(isset($_REQUEST['cp_success']) && $_REQUEST['cp_success'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' . $_REQUEST['cp_success'] . '</strong></p></div>';			
				
	}
	
}
?>

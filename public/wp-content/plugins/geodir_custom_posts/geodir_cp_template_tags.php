<?php
function geodir_custom_post_type_script() {
	wp_enqueue_script( 'custom_post_type_js', plugins_url( 'js/script.js' , __FILE__ ));
}

function geodir_cp_listing()
{
?>



<?php if(isset($_REQUEST['confirm']) && $_REQUEST['confirm'] == 'true' && isset($_REQUEST['geodir_customposttype']) && $_REQUEST['geodir_customposttype'] != ''){
	?>
	<div id="message" class="updated fade"><p><strong>Click to <a href="<?php echo geodir_cp_ajax_url();?>&geodir_deleteposttype=<?php echo $_REQUEST['geodir_customposttype']; ?>"><?php _e('Delete',GEODIR_CP_TEXTDOMAIN); ?></a></strong> all posts of (<?php echo $_REQUEST['geodir_customposttype']; ?>) post type. If you don't delete posts of this post type now and create a same name post types in future then all the posts will be assigned to (<?php echo $_REQUEST['geodir_customposttype']; ?>) post type again.</strong></p></div>

<?php }?>

<div class="inner_content_tab_main">
	<div class="gd-content-heading active">
<h3><?php _e('Manage Custom Post Types',GEODIR_CP_TEXTDOMAIN); ?></h3>
<p style="padding-left:15px;"><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_addedit'?>"><strong><?php _e('Add Post Type',GEODIR_CP_TEXTDOMAIN); ?></strong></a> </p>
		
		<table cellpadding="5" class="widefat post fixed" >
		
		<thead>
				<tr>
						<th width="152" align="left"><strong><?php _e('Post Type',GEODIR_CP_TEXTDOMAIN); ?></strong></th>
					 
						<th width="152" align="left"><strong><?php _e('Listing Slug',GEODIR_CP_TEXTDOMAIN); ?></strong></th>
						
						<th width="220" align="left"><strong><?php _e('Taxonomies',GEODIR_CP_TEXTDOMAIN); ?></strong></th>
						
						<th width="100" align="left"><strong><?php _e('Can Export',GEODIR_CP_TEXTDOMAIN); ?> </strong></th>
						
						<th width="70" align="left"><strong><?php _e('Edit',GEODIR_CP_TEXTDOMAIN); ?></strong></th>
						
						<th width="70" align="left"><strong><?php _e('Delete',GEODIR_CP_TEXTDOMAIN); ?></strong></th>
					
				</tr>
		<?php


$geodir_post_types = get_option( 'geodir_post_types' );
$total_zero_count = 0;

$geodir_post_types_in_new_order = array() ;
$geodir_temp_post_types = array() ;
$geodir_temp_post_type_keys = array() ;
foreach($geodir_post_types as $key =>$value)
{
	if(!empty($geodir_temp_post_types ) )
	{
		if(!isset($value['listing_order']) || $value['listing_order']==0)	
		{
			$total_zero_count++;
		}
		
		if(!isset($value['listing_order']) || $value['listing_order']==0|| array_key_exists($value['listing_order'], $geodir_temp_post_types ))
			$value['listing_order'] = max(array_keys($geodir_temp_post_types))+1 ;
	
	}
	else
	{
		if(!isset($value['listing_order']) || $value['listing_order']==0 )
		{
				$value['listing_order'] =1 ;
				$total_zero_count++;
		}
		
	}
	$geodir_temp_post_types[$value['listing_order']] = $value;
	$geodir_temp_post_type_keys[$value['listing_order']] = $key ;
}

ksort($geodir_temp_post_types) ;
foreach($geodir_temp_post_types as $key => $value)
{
	$geodir_post_types_in_new_order[$geodir_temp_post_type_keys[$key]] =  $value ;
}

if($total_zero_count==count($geodir_post_types_in_new_order))	
{
	update_option( 'geodir_post_types', $geodir_post_types_in_new_order );
}


foreach($geodir_post_types as $key => $value)
{ 
?>
						<tr>
								<td><?php echo $key;?></td>
								
								<td><?php echo $value['has_archive'];?></td>
								
								<td><?php 
								
								if(!empty($value['taxonomies']))
								{
									echo implode(', ', $value['taxonomies']);
								}
								
								
								?></td>
								
								<td><?php if($value['can_export'])
													{
														_e('Yes',GEODIR_CP_TEXTDOMAIN);
													}
													else
													{
														_e('No',GEODIR_CP_TEXTDOMAIN);
													}
								
								?></td>
								
								
								<td><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_addedit&posttype='.$key; ?>"><?php _e('Edit',GEODIR_CP_TEXTDOMAIN); ?></a> </td>
								<td>
								<?php if(isset($value['is_custom']) && $value['is_custom'] != ''){?>
								<a class="delete_posttype" package_id="<?php echo $key;?>" href="javascript:void(0);"><?php _e('Delete',GEODIR_CP_TEXTDOMAIN); ?></a>
								<?php
								}else{echo "&nbsp;";}
								?>
								
								</td>
								
						</tr>
<?php
}
?>
		</thead>
		</table>
                                
</div>                               
</div>                    
<script type="text/javascript" language="javascript">
	
	jQuery(".delete_posttype").click(function(){
		
		var posttype = jQuery(this).attr('package_id');
		
		var confirm_post = confirm('<?php _e('Are you wish to delete this Post Type?',GEODIR_CP_TEXTDOMAIN); ?>');
		
		if(confirm_post)
		{
			window.location.href = "<?php echo admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_delete&posttype='; ?>"+posttype;
		}
		
	});
	
</script> 
<?php 
}


function geodir_cp_add_edit_form() {
	global $cp_error;
	
	if ( isset( $_REQUEST['posttype'] ) && $_REQUEST['posttype'] != '' ) {
		$geodir_post_types = get_option( 'geodir_post_types' );
		
		$post_type_array = $geodir_post_types[$_REQUEST['posttype']];
		
		$nav_menus_posts = $nav_menus_cats = $nav_menus_tags = 0;
		
		if ( !empty( $post_type_array ) ) {
			$hide_fields 		= 'readonly="readonly"';
			$custom_post_type	= $_REQUEST['posttype'];
			$listing_slug 		= $post_type_array['has_archive'];
			$listing_order 		= $post_type_array['listing_order'];
			
			if ( !empty( $post_type_array['taxonomies'] ) ) {
				if ( in_array( $_REQUEST['posttype'] . 'category', $post_type_array['taxonomies'] ) ) {
					$categories = 'categories';
				}
				
				if ( in_array( $_REQUEST['posttype'] . '_tags', $post_type_array['taxonomies'] ) ) {
					$tags = 'tags';
				}
			}
			
			$name 					= stripslashes($post_type_array['labels']['name']);
			$singular_name 			= stripslashes($post_type_array['labels']['singular_name']);
			$add_new 				= stripslashes($post_type_array['labels']['add_new']);
			$add_new_item 			= stripslashes($post_type_array['labels']['add_new_item']);
			$edit_item 				= stripslashes($post_type_array['labels']['edit_item']);
			$new_item 				= stripslashes($post_type_array['labels']['new_item']);
			$view_item 				= stripslashes($post_type_array['labels']['view_item']);
			$search_item 			= stripslashes($post_type_array['labels']['search_items']);
			$not_found 				= stripslashes($post_type_array['labels']['not_found']);
			$not_found_trash 		= stripslashes($post_type_array['labels']['not_found_in_trash']);
			$support 				= $post_type_array['supports'];
			$description 			= stripslashes_deep($post_type_array['description']);
			$menu_icon 				= $post_type_array['menu_icon'];
			$can_export 			= $post_type_array['can_export'];
			$geodir_cp_meta_keyword = stripslashes($post_type_array['seo']['meta_keyword']);
			$geodir_cp_meta_description = stripslashes($post_type_array['seo']['meta_description']);
			
			$taxonomies = get_option('geodir_taxonomies');
			
			$nav_menus_posts 	= isset( $post_type_array['show_in_nav_menus'] ) && $post_type_array['show_in_nav_menus'] != 1 ? 1 : 0;
			$nav_menus_cats 	= !empty( $taxonomies ) && isset( $taxonomies[$custom_post_type . 'category']['args']['show_in_nav_menus'] ) && $taxonomies[$custom_post_type . 'category']['args']['show_in_nav_menus'] != 1 ? 1 : 0;
			$nav_menus_tags 	= !empty( $taxonomies ) && isset( $taxonomies[$custom_post_type . '_tags']['args']['show_in_nav_menus'] ) && $taxonomies[$custom_post_type . '_tags']['args']['show_in_nav_menus'] != 1 ? 1 : 0;
		}
	}
	
	$label_post_profile = !empty($post_type_array) && isset($post_type_array['labels']['label_post_profile']) ? stripslashes_deep($post_type_array['labels']['label_post_profile']) : '';
	$label_post_info = !empty($post_type_array) && isset($post_type_array['labels']['label_post_info']) ? stripslashes_deep($post_type_array['labels']['label_post_info']) : '';
	$label_post_images = !empty($post_type_array) && isset($post_type_array['labels']['label_post_images']) ? stripslashes_deep($post_type_array['labels']['label_post_images']) : '';
	$label_post_map = !empty($post_type_array) && isset($post_type_array['labels']['label_post_map']) ? stripslashes_deep($post_type_array['labels']['label_post_map']) : '';
	$label_reviews = !empty($post_type_array) && isset($post_type_array['labels']['label_reviews']) ? stripslashes_deep($post_type_array['labels']['label_reviews']) : '';
	$label_related_listing = !empty($post_type_array) && isset($post_type_array['labels']['label_related_listing']) ? stripslashes_deep($post_type_array['labels']['label_related_listing']) : '';
		
	if ( isset ( $_REQUEST['geodir_save_post_type'] ) ) {
		$custom_post_type		= stripslashes($_REQUEST['geodir_custom_post_type']);
		$listing_slug 			= stripslashes($_REQUEST['geodir_listing_slug']);
		$listing_order      	= $_REQUEST['geodir_listing_order'];
		$categories 			= $_REQUEST['geodir_categories'];
		$tags 					= isset($_REQUEST['geodir_tags']) ? $_REQUEST['geodir_tags'] : '';
		$name 					= stripslashes($_REQUEST['geodir_name']);
		$singular_name 			= stripslashes($_REQUEST['geodir_singular_name']);
		$add_new 				= stripslashes($_REQUEST['geodir_add_new']);
		$add_new_item 			= stripslashes($_REQUEST['geodir_add_new_item']);
		$edit_item 				= stripslashes($_REQUEST['geodir_edit_item']);
		$new_item 				= stripslashes($_REQUEST['geodir_new_item']);
		$view_item 				= stripslashes($_REQUEST['geodir_view_item']);
		$search_item 			= stripslashes($_REQUEST['geodir_search_item']);
		$not_found 				= stripslashes($_REQUEST['geodir_not_found']);
		$not_found_trash 		= stripslashes($_REQUEST['geodir_not_found_trash']);
		$support 				= $_REQUEST['geodir_support'];
		$description 			= stripslashes($_REQUEST['geodir_description']);
		$menu_icon 				= stripslashes($_REQUEST['geodir_menu_icon']);
		$can_export 			= $_REQUEST['geodir_can_export'];
		$geodir_cp_meta_keyword = stripslashes($_REQUEST['geodir_cp_meta_keyword']);
		$geodir_cp_meta_description = stripslashes($_REQUEST['geodir_cp_meta_description']);
		$label_post_profile 	= stripslashes_deep($_REQUEST['geodir_label_post_profile']);
		$label_post_info 		= stripslashes_deep($_REQUEST['geodir_label_post_info']);
		$label_post_images 		= stripslashes_deep($_REQUEST['geodir_label_post_images']);
		$label_post_map 		= stripslashes_deep($_REQUEST['geodir_label_post_map']);
		$label_reviews 			= stripslashes_deep($_REQUEST['geodir_label_reviews']);
		$label_related_listing 	= stripslashes_deep($_REQUEST['geodir_label_related_listing']);
		
		$nav_menus_posts 	= isset( $_REQUEST['geodir_disable_nav_menus']['posts'] ) && (int)$_REQUEST['geodir_disable_nav_menus']['posts'] == 1 ? 1 : 0;
		$nav_menus_cats 	= isset( $_REQUEST['geodir_disable_nav_menus']['cats'] ) && (int)$_REQUEST['geodir_disable_nav_menus']['cats'] == 1 ? 1 : 0;
		$nav_menus_tags 	= isset( $_REQUEST['geodir_disable_nav_menus']['tags'] ) && (int)$_REQUEST['geodir_disable_nav_menus']['tags'] == 1 ? 1 : 0;
	}

	if(isset($cp_error) && $cp_error != ''){
		
		echo $cp_error;
		
	}

?>
<div class="inner_content_tab_main">

	<div class="gd-content-heading active">
<h3><?php _e('Post Type',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Post type',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input <?php if(isset($hide_fields)){ echo $hide_fields;}?> maxlength="17" class="require" type="text"  size="80" style="width:440px" id="geodir_custom_post_type" name="geodir_custom_post_type" value="<?php if(isset($custom_post_type)){ echo $custom_post_type = preg_replace('/gd_/', '',$custom_post_type, 1); }  ?>" /><span class="description"><?php _e('The new post type system name ( max. 17 characters ). Lower-case characters and underscores only. Min 2 letters. Once added the post type system name cannot be changed.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2"> <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Listing slug', GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input maxlength="20" class="require" type="text"  size="80" style="width:440px" id="geodir_listing_slug" name="geodir_listing_slug" value="<?php if(isset($listing_slug)){ echo $listing_slug;} ?>" /><span class="description"><?php _e("The listing slug name ( max. 20 characters ). Alphanumeric lower-case characters and underscores  and hyphen(-) only. Min 2 letters. Once added the post type 'Listing Slug' name cannot be changed.",GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2"> <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
            <tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Order in post type list', GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input maxlength="20" class="require" type="text"  size="80" style="width:440px" id="geodir_listing_order" name="geodir_listing_order" value="<?php if(isset($listing_order)){ echo $listing_order;}else echo '0'; ?>" /><span class="description"><?php _e("Position at which this post type will appear in post type list everywhere on the website.",GEODIR_CP_TEXTDOMAIN);?></span>
                        
                         <span class="description"><b><?php _e("Note: If the entered value is already an order of other post type then this will not make any effect.",GEODIR_CP_TEXTDOMAIN);?></b></span>
						 <div class="gd-location_message_error2"> <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
	</tbody>
</table>

<!--<h3><?php //_e('Support Regular Taxonomies',GEODIR_CP_TEXTDOMAIN);?></h3>-->
<table class="form-table" style="display:none;">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Categories',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" checked="checked"  type="checkbox" value="geodir_categories" name="geodir_categories">&nbsp;
						 <div class="gd-location_message_error2"> <?php if(isset($required_msg)){echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Tags',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" checked="checked" type="checkbox" value="geodir_tags" name="geodir_tags">&nbsp;
						 <div class="gd-location_message_error2"> <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
	</tbody>
</table>	

<h3><?php _e('Labels',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Name',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_name" name="geodir_name" value="<?php if(isset($name)){echo $name;} ?>" /><span class="description"><?php _e('General name for the post type, usually plural.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Singular name',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_singular_name" name="geodir_singular_name" value="<?php if(isset($singular_name)){ echo $singular_name;} ?>" /><span class="description"><?php _e('Name for one object of this post type. Defaults to value of name.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Add new',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_add_new" name="geodir_add_new" value="<?php if(isset($add_new)){ echo $add_new;} ?>" /><span class="description"><?php _e('The add new text. The default is Add New for both hierarchical and non-hierarchical types.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Add new item',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_add_new_item" name="geodir_add_new_item" value="<?php if(isset($add_new_item)){ echo $add_new_item;} ?>" /><span class="description"><?php _e('The add new item text. Default is Add New Post/Add New Page.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Edit item',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_edit_item" name="geodir_edit_item" value="<?php if(isset($edit_item)){echo $edit_item;} ?>" /><span class="description"><?php _e('The edit item text. Default is Edit Post/Edit Page.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('New item',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_new_item" name="geodir_new_item" value="<?php if(isset($new_item)){echo $new_item;} ?>" /><span class="description"><?php _e('The new item text. Default is New Post/New Page.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('View item',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_view_item" name="geodir_view_item" value="<?php if(isset($view_item)){echo $view_item;} ?>" /><span class="description"><?php _e('The view item text. Default is View Post/View Page.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Search items',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_search_item" name="geodir_search_item" value="<?php if(isset($search_item)){echo $search_item;} ?>" /><span class="description"><?php _e('The search items text. Default is Search Posts/Search Pages.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Not found',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_not_found" name="geodir_not_found" value="<?php if(isset($not_found)){echo $not_found;} ?>" /><span class="description"><?php _e('The not found text. Default is No posts found/No pages found.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Not found in trash',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_not_found_trash" name="geodir_not_found_trash" value="<?php if(isset($not_found_trash)){echo $not_found_trash;} ?>" /><span class="description"><?php _e('The not found in trash text. Default is No posts found in Trash/No pages found in Trash.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('Profile tab label', GEODIR_CP_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
					<input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_profile" name="geodir_label_post_profile" value="<?php echo $label_post_profile;?>" />
					<span class="description"><?php _e('Text label for "Profile" tab on post detail page.(optional)', GEODIR_CP_TEXTDOMAIN);?></span>
				</div>
			</td>
		</tr>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('More Info tab label', GEODIR_CP_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
					<input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_info" name="geodir_label_post_info" value="<?php echo $label_post_info;?>" />
					<span class="description"><?php _e('Text label for "More Info" tab on post detail page.(optional)', GEODIR_CP_TEXTDOMAIN);?></span>
				</div>
			</td>
		</tr>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('Photo tab label', GEODIR_CP_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
					<input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_images" name="geodir_label_post_images" value="<?php echo $label_post_images;?>" />
					<span class="description"><?php _e('Text label for Photo" tab on post detail page.(optional)', GEODIR_CP_TEXTDOMAIN);?></span>
				</div>
			</td>
		</tr>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('Map tab label', GEODIR_CP_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
					<input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_map" name="geodir_label_post_map" value="<?php echo $label_post_map;?>" />
					<span class="description"><?php _e('Text label for "Map" tab on post detail page.(optional)', GEODIR_CP_TEXTDOMAIN);?></span>
				</div>
			</td>
		</tr>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('Reviews tab label', GEODIR_CP_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
					<input class="require" type="text"  size="80" style="width:440px" id="geodir_label_reviews" name="geodir_label_reviews" value="<?php echo $label_reviews;?>" />
					<span class="description"><?php _e('Text label for "Reviews" tab on post detail page.(optional)', GEODIR_CP_TEXTDOMAIN);?></span>
				</div>
			</td>
		</tr>
		<tr valign="top" class="single_select_page">
			<th class="titledesc" scope="row"><?php _e('Related Listing tab label', GEODIR_CP_TEXTDOMAIN);?></th>
			<td class="forminp">
				<div class="gtd-formfeild">
					<input class="require" type="text"  size="80" style="width:440px" id="geodir_label_related_listing" name="geodir_label_related_listing" value="<?php echo $label_related_listing;?>" />
					<span class="description"><?php _e('Text label for "Related Listing" tab on post detail page.(optional)', GEODIR_CP_TEXTDOMAIN);?></span>
				</div>
			</td>
		</tr>
	</tbody>
</table>	
<h3><?php _e( 'Nav Menus', GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
  <tbody>
    <tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Posts', GEODIR_CP_TEXTDOMAIN);?></th>
      <td class="forminp">
	  	<div class="gtd-formfeild">
          <input id="geodir_navmenu_disable_posts" type="checkbox" <?php echo ( $nav_menus_posts == 1 ? 'checked="checked"' : '' );?> value="1" name="geodir_disable_nav_menus[posts]">&nbsp;<?php _e('Disable posts in nav menus.', GEODIR_CP_TEXTDOMAIN);?>
        </div>
        <span class="description"></span></td>
    </tr>
	<tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Categories', GEODIR_CP_TEXTDOMAIN);?></th>
      <td class="forminp">
	  	<div class="gtd-formfeild">
          <input id="geodir_navmenu_disable_cats" type="checkbox" <?php echo ( $nav_menus_cats == 1 ? 'checked="checked"' : '' );?> value="1" name="geodir_disable_nav_menus[cats]">&nbsp;<?php _e('Disable post categories in nav menus.', GEODIR_CP_TEXTDOMAIN);?>
        </div>
        <span class="description"></span></td>
    </tr>
	<tr valign="top" class="single_select_page">
      <th class="titledesc" scope="row"><?php _e('Tags', GEODIR_CP_TEXTDOMAIN);?></th>
      <td class="forminp">
	  	<div class="gtd-formfeild">
          <input id="geodir_navmenu_disable_tags" type="checkbox" <?php echo ( $nav_menus_tags == 1 ? 'checked="checked"' : '' );?> value="1" name="geodir_disable_nav_menus[tags]">&nbsp;<?php _e('Disable posts tags in nav menus.', GEODIR_CP_TEXTDOMAIN);?>
        </div>
        <span class="description"></span></td>
    </tr>
  </tbody>
</table>
<h3><?php _e('Supports',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
	
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Supports',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
					 	<?php _e('Register support of certain features for a post type.',GEODIR_CP_TEXTDOMAIN);?>	
						 
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page" style="display:none;">
					<th class="titledesc" scope="row"><?php _e('Title',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" type="checkbox" checked="checked" value="title" name="geodir_support[]">&nbsp;<?php _e('Title',GEODIR_CP_TEXTDOMAIN);?>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page" style="display:none;">
					<th class="titledesc" scope="row"><?php _e('Editor',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" type="checkbox" checked="checked" value="editor" name="geodir_support[]">&nbsp;<?php _e('Editor - Content',GEODIR_CP_TEXTDOMAIN);?> 
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Author',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
					 
						 <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('author', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="author" name="geodir_support[]">&nbsp;<?php _e('Author', GEODIR_CP_TEXTDOMAIN);?>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Thumbnail',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('thumbnail', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="thumbnail" name="geodir_support[]">&nbsp;<?php _e('Thumbnail - featured image - current theme must also support post-thumbnails.',GEODIR_CP_TEXTDOMAIN);?> 
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Excerpt',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('excerpt', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="excerpt" name="geodir_support[]">&nbsp;<?php _e('Excerpt', GEODIR_CP_TEXTDOMAIN);?>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Custom fields',GEODIR_CP_TEXTDOMAIN);?> </th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('custom-fields', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="custom-fields" name="geodir_support[]">&nbsp;<?php _e('Custom fields',GEODIR_CP_TEXTDOMAIN);?> 
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Comments',GEODIR_CP_TEXTDOMAIN);?>  </th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('comments', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="comments" name="geodir_support[]">&nbsp;<?php _e('Comments - also will see comment count balloon on edit screen.',GEODIR_CP_TEXTDOMAIN);?>   
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Post formats',GEODIR_CP_TEXTDOMAIN);?> </th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('post-formats', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="post-formats" name="geodir_support[]">&nbsp;<?php _e('Post formats - add post formats.',GEODIR_CP_TEXTDOMAIN);?> 
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
	</tbody>
</table>	

<h3><?php _e('Description',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Description',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 
						 <textarea name="geodir_description" class="require" style="width:440px"><?php if(isset($description)){echo $description;} ?></textarea><span class="description"><?php _e('A short descriptive summary of what the post type is.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
	</tbody>
</table>


<h3><?php _e('Menu Icon',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Menu Icon',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 
						 <input class="require" type="text"  size="80" style="width:440px" id="geodir_menu_icon" name="geodir_menu_icon" value="<?php if(isset($menu_icon)){echo $menu_icon;} ?>" /><span class="description"><?php _e('The url to the icon to be used for this menu.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
	</tbody>
</table>	


<h3><?php _e('Can Export',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Can Export',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 
						 <input id="geodir_tiny_editor1" <?php if(isset($can_export) && ($can_export == true || $can_export == 1)){echo 'checked="checked"';}?> type="radio" value="true" name="geodir_can_export">&nbsp;<?php _e('True',GEODIR_CP_TEXTDOMAIN);?>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row">&nbsp;</th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 
						 <input id="geodir_tiny_editor1" <?php if(!isset($can_export) || $can_export == false || $can_export == 0){echo 'checked="checked"';}?> type="radio" value="false" name="geodir_can_export">&nbsp;<?php _e('False',GEODIR_CP_TEXTDOMAIN);?>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
	</tbody>
</table>	
<h3><?php _e('SEO',GEODIR_CP_TEXTDOMAIN);?></h3>
<table class="form-table">
	<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Meta Keywords',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 
						 <textarea name="geodir_cp_meta_keyword" class="require" style="width:440px"><?php if(isset($geodir_cp_meta_keyword)){echo $geodir_cp_meta_keyword;} ?></textarea><span class="description"><?php _e('Meta keywords will appear in head tag of this post type listing page.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php _e('Meta Description',GEODIR_CP_TEXTDOMAIN);?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 
						 <textarea name="geodir_cp_meta_description" class="require" style="width:440px"><?php if(isset($geodir_cp_meta_description)){echo $geodir_cp_meta_description;} ?></textarea><span class="description"><?php _e('Meta description will appear in head tag of this post type listing page.',GEODIR_CP_TEXTDOMAIN);?></span>
						 <div class="gd-location_message_error2">  <?php if(isset($required_msg)){ echo $required_msg;}?></div>
					</div>
					<span class="description"></span>
					</td>
			</tr>
			
	</tbody>
</table>

<p class="submit" style="margin-top:10px;">
<input name="geodir_save_post_type" class="button-primary" type="submit" value="<?php _e( 'Save changes',GEODIR_CP_TEXTDOMAIN ); ?>" />
<input type="hidden" name="subtab" id="last_tab" />
</p>

</div>

</div>
<?php
}
?>

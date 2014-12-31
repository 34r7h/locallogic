<?php

add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_review_manager', 10,1); 
function geodir_diagnose_multisite_conversion_review_manager($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_rating_style'] = __('Rating style',GEODIRREVIEWRATING_VERSION);
	$table_arr['geodir_rating_category'] = __('Rating category',GEODIRREVIEWRATING_VERSION);
	$table_arr['geodir_unassign_comment_images'] = __('Comment image',GEODIRREVIEWRATING_VERSION);
	$table_arr['geodir_comments_reviews'] = __('Comment reviews',GEODIRREVIEWRATING_VERSION);
	return $table_arr;
}

/* Plugin Activation Function */
function geodir_reviewrating_activation(){
 
	if (get_option('geodir_installed')) {
		geodir_reviewrating_db_install();
		update_option( "geodir_reviewrating_db_version", GEODIRREVIEWRATING_VERSION );
		geodir_update_options(geodir_reviewrating_default_options(), true);
		add_option('geodir_reviewrating_activation_redirect_opt', 1);
	}
	
}
/* Plugin Database Instalation Function */
function geodir_reviewrating_db_install(){
	
	global $wpdb,$plugin_prefix;

	$wpdb->hide_errors();

	if($wpdb->query("SHOW TABLES LIKE 'geodir_rating_style'")>0){$wpdb->query("RENAME TABLE geodir_rating_style TO ".$wpdb->prefix."geodir_rating_style");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_rating_category'")>0){$wpdb->query("RENAME TABLE geodir_rating_category TO ".$wpdb->prefix."geodir_rating_category");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_post_review'")>0){$wpdb->query("RENAME TABLE geodir_post_review TO ".$wpdb->prefix."geodir_post_review");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_unassign_comment_images'")>0){$wpdb->query("RENAME TABLE geodir_unassign_comment_images TO ".$wpdb->prefix."geodir_unassign_comment_images");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_comments_reviews'")>0){$wpdb->query("RENAME TABLE geodir_comments_reviews TO ".$wpdb->prefix."geodir_comments_reviews");}


	$collate = '';
	if($wpdb->has_cap( 'collation' )) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_REVIEWRATING_STYLE_TABLE."'") != GEODIR_REVIEWRATING_STYLE_TABLE){
		$default_img = GEODIR_REVIEWRATING_PLUGINDIR_URL."/icons/stars.png";
		$wpdb->query("CREATE TABLE IF NOT EXISTS ".GEODIR_REVIEWRATING_STYLE_TABLE." (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(200) NOT NULL,
			`s_img_off` text NOT NULL,
			`s_img_width` text NOT NULL,
			`s_img_height` text NOT NULL,
			`star_color` text NOT NULL,
			`star_lables` text NOT NULL,
			`star_number` varchar(200) NOT NULL,
			`is_default` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)) $collate  ");
		
		$default_star_lables = array();
		$default_star_lables[] = 'Terrible';
		$default_star_lables[] = 'Poor';
		$default_star_lables[] = 'Average';
		$default_star_lables[] = 'Very Good';
		$default_star_lables[] = 'Excellent';
		$default_star_lables = maybe_serialize( $default_star_lables );
		
		$wpdb->query("INSERT INTO  ".GEODIR_REVIEWRATING_STYLE_TABLE." (name,s_img_off,s_img_width,s_img_height,star_color,star_lables,star_number,is_default) VALUES ('overall','$default_img','23','20','#ff9900','$default_star_lables','5','1') ");
		
		update_option( 'geodir_reviewrating_change_star_lables_field', '1' );	
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_REVIEWRATING_CATEGORY_TABLE."'") != GEODIR_REVIEWRATING_CATEGORY_TABLE){
	
		$wpdb->query("CREATE TABLE IF NOT EXISTS ".GEODIR_REVIEWRATING_CATEGORY_TABLE."(
		`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
		`title` varchar( 500 ) NOT NULL ,
		`post_type` varchar( 500 ) NOT NULL ,
		`category` varchar( 500 ) NOT NULL ,
		`category_id` varchar( 200 ) NOT NULL ,
		`check_text_rating_cond` ENUM( '0', '1' ) NOT NULL DEFAULT '1',
		PRIMARY KEY ( `id` )) $collate ");
		
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_UNASSIGN_COMMENT_IMG_TABLE."'") != GEODIR_UNASSIGN_COMMENT_IMG_TABLE){
	
		$wpdb->query("CREATE TABLE IF NOT EXISTS ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`directory` varchar(240) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY (`id`)
		) $collate ");
					
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_COMMENTS_REVIEWS_TABLE."'") != GEODIR_COMMENTS_REVIEWS_TABLE){
	
		$wpdb->query("CREATE TABLE IF NOT EXISTS `".GEODIR_COMMENTS_REVIEWS_TABLE."` (
		`like_id` int(11) NOT NULL AUTO_INCREMENT,
		`comment_id` int(11) NOT NULL,
		`ip` varchar(100) NOT NULL,
		`like_unlike` int(11) NOT NULL,
		PRIMARY KEY (`like_id`)) $collate");
		
	}
	
	if(!$wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE field = 'read_unread'"))
	{
		$wpdb->query("ALTER TABLE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." ADD `read_unread` VARCHAR( 50 )  NOT NULL");
	}

	if(!$wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE field = 'total_images'"))
	{
		$wpdb->query("ALTER TABLE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." ADD `total_images` int( 11 )  NOT NULL");
	}
	
	$post_types = geodir_get_posttypes();
	foreach($post_types as $post_type){
		
		global $plugin_prefix;
		
		$detail_table = $plugin_prefix . $post_type . '_detail';	
		geodir_add_column_if_not_exist($detail_table, 'ratings',  'TEXT NULL DEFAULT NULL');
		
	}
	
	$link_image = GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/up-img.png';
	$unlink_image = GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/down.png';
	$default_img = GEODIR_REVIEWRATING_PLUGINDIR_URL."/icons/stars.png";
	
	if(!get_option('geodir_reviewrating_review_like_img'))
		update_option('geodir_reviewrating_review_like_img',$link_image);
	
	if(!get_option('geodir_reviewrating_review_unlike_img'))
		update_option('geodir_reviewrating_review_unlike_img',$unlink_image);
		
	if(!get_option('geodir_reviewrating_overall_off_img'))
		update_option('geodir_reviewrating_overall_off_img',$default_img);
		
	if(!get_option('geodir_reviewrating_overall_color'))
		update_option('geodir_reviewrating_overall_color','#ff9900');
	
	if(!get_option('geodir_reviewrating_overall_off_img_width'))
		update_option('geodir_reviewrating_overall_off_img_width','23');
	
	if(!get_option('geodir_reviewrating_overall_off_img_height'))
		update_option('geodir_reviewrating_overall_off_img_height','20');
		
	if(!get_option('geodir_reviewrating_overall_rating_texts'))
		update_option('geodir_reviewrating_overall_rating_texts',array(0 => 'Terrible',1 => 'Poor',2 => 'Average',3 => 'Very Good',4 => 'Excellent'));
		
	if(!get_option('geodir_reviewrating_overall_count'))
		update_option('geodir_reviewrating_overall_count','5');
}

function geodir_reviewrating_uninstall(){
if ( ! isset($_REQUEST['verify-delete-adon']) ) 
	{
		$plugins = isset( $_REQUEST['checked'] ) ? (array) $_REQUEST['checked'] : array();
			//$_POST = from the plugin form; $_GET = from the FTP details screen.
			
			wp_enqueue_script('jquery');
					require_once(ABSPATH . 'wp-admin/admin-header.php');
					printf( '<h2>%s</h2>' ,__( 'Warning!!' , GEODIRREVIEWRATING_TEXTDOMAIN) );
					printf( '%s<br/><strong>%s</strong><br /><br />%s <a href="http://wpgeodirectory.com">%s</a>.' , __('You are about to delete a Geodirectory Adon which has important option and custom data associated to it.' ,GEODIRREVIEWRATING_TEXTDOMAIN) ,__('Deleting this and activating another version, will be treated as a new installation of plugin, so all the data will be lost.', GEODIRREVIEWRATING_TEXTDOMAIN), __('If you have any problem in upgrading the plugin please contact Geodirectroy', GEODIRREVIEWRATING_TEXTDOMAIN) , __('support' ,GEODIRREVIEWRATING_TEXTDOMAIN) ) ;
					
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
						<?php submit_button(  __( 'Delete plugin files only' , GEODIRREVIEWRATING_TEXTDOMAIN ), 'button', 'submit', false ); ?>
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
						<?php submit_button(  __( 'Delete both plugin files and data' , GEODIRREVIEWRATING_TEXTDOMAIN) , 'button', 'submit', false ); ?>
					</form>
					
	<?php
		require_once(ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}
	
	
	if ( isset($_REQUEST['verify-delete-adon-data']) ) 
	{	

		global $wpdb,$plugin_prefix;
		
		$default_options = geodir_reviewrating_default_options();
		
		if(!empty($default_options)){
			foreach($default_options as $value){
				if(isset($value['id']) && $value['id'] != '')
					delete_option($value['id'], '');
			}
		}
		
		$post_types = geodir_get_posttypes();
		foreach($post_types as $post_type){
			
			global $plugin_prefix;
			
			$detail_table = $plugin_prefix . $post_type . '_detail';	
			
			if($wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." WHERE field = 'ratings'"))
					$wpdb->query("ALTER TABLE ".$detail_table." DROP ratings");
		}
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE field = 'read_unread'"))
					$wpdb->query("ALTER TABLE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." DROP read_unread");
		
		$wpdb->query("DROP TABLE ".GEODIR_REVIEWRATING_STYLE_TABLE);
		$wpdb->query("DROP TABLE ".GEODIR_REVIEWRATING_CATEGORY_TABLE);
		$wpdb->query("DROP TABLE ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE);
		$wpdb->query("DROP TABLE ".GEODIR_COMMENTS_REVIEWS_TABLE);
	}
	
}


function geodir_reviewrating_delete_post_type($post_type = ''){

	global $wpdb, $plugin_prefix;
	
	if($post_type != ''){
		
		$all_postypes = geodir_get_posttypes();
		
		$rating_data = $wpdb->get_results($wpdb->prepare("SELECT id, post_type FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE FIND_IN_SET(%s, post_type)", array($post_type)));
		
		if(!empty($rating_data)){
			
			foreach($rating_data as $key => $rating){
			
				$ratings = explode(",",$rating->post_type);
				
				if(($del_key = array_search($post_type, $ratings)) !== false)
					unset($ratings[$del_key]);
				
				if(!empty($ratings)){
					
					$ratings = implode(',',$ratings);
					
					$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_REVIEWRATING_CATEGORY_TABLE." SET post_type=%s WHERE id=%d",array($ratings,$rating->id)));
					
				}else{
					
					$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE id=%d", array($rating->id)));
					
				}
					
			}
			
		}
	
	}
	
}


function geodir_reviewrating_after_custom_detail_table_create($post_type, $detail_table){
	
	$post_types = geodir_get_posttypes();
	
	if(in_array($post_type, $post_types)){
		geodir_add_column_if_not_exist($detail_table, 'ratings',  'TEXT NULL DEFAULT NULL');
		geodir_add_column_if_not_exist($detail_table, 'overall_rating',  'float(11) DEFAULT NULL');
	}
	
}


function geodir_reviewrating_activation_redirect() {
	if (get_option('geodir_reviewrating_activation_redirect_opt', false)) {
			delete_option('geodir_reviewrating_activation_redirect_opt');
			wp_redirect(admin_url('admin.php?page=geodirectory&tab=multirating_fields&subtab=geodir_multirating_options')); 
	}
}


function geodir_reviewrating_ajax_url(){
		return admin_url('admin-ajax.php?action=geodir_reviewrating_ajax');
}


function geodir_reviewrating_option_forms($tab){
	
	if($tab == 'multirating_fields'){
		
		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_multirating_options')
			add_action('geodir_admin_option_form', 'geodir_reviewrating_general_options_form');

		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_rating_settings')
			geodir_reviewrating_overall_settings_form();
		
		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_rating_style')
			geodir_reviewrating_manage_rating_style_form();
		
		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_create_rating')
			geodir_reviewrating_create_rating_form();
		
		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_manage_review')
			geodir_reviewrating_manage_review_form();
	
	}elseif($tab == 'reviews_fields'){
	
		geodir_reviewrating_manage_comments();
		
	}
}


function geodir_reviewrating_comment_rating_fields(){
		
		global $geodir_post_type;
		
		$all_postypes = geodir_get_posttypes();

		if(!in_array($geodir_post_type, $all_postypes))
			return false;
		
		if(get_option('geodir_reviewrating_enable_rating')):
			geodir_reviewrating_rating_frm_html();
		endif;
		
		if(get_option('geodir_reviewrating_enable_images')):
			geodir_reviewrating_rating_img_html();
		endif;
}

function geodir_reviewrating_general_options_form($current_tab){
	
	$current_tab = $_REQUEST['subtab'];
	geodir_review_rating_general_options($current_tab);
	
}



function geodir_reviewrating_advance_stars_on_infowindow($rating_star, $avg_rating, $post_id)
{
	
	$rating_star  = geodir_reviewrating_draw_overall_rating($avg_rating);


	return $rating_star;
}


function geodir_before_reviewrating_advance_stars_on_detail($avg_rating, $post_id)
{
	ob_start();
}

function geodir_after_reviewrating_advance_stars_on_detail($avg_rating, $post_id)
{
	$star_html = ob_get_clean();
	$star_html = '';
		$star_html  .= geodir_reviewrating_draw_overall_rating($avg_rating);
		
	if ( $star_html != '' ) {
		$star_html .= geodir_reviewrating_detail_page_rating_summary( $avg_rating, $post_id );
	}
	
	echo $star_html;
}

function geodir_before_reviewrating_advance_stars_on_gridview($avg_rating, $post_id)
{
	ob_start();
}

function geodir_after_reviewrating_advance_stars_on_gridview($avg_rating, $post_id)
{
	$star_html = ob_get_clean();
	$star_html = '';
		$star_html  .= geodir_reviewrating_draw_overall_rating($avg_rating);

	echo $star_html;
}


function geodir_before_reviewrating_advance_stars_on_listview($avg_rating, $post_id)
{
	ob_start();
}

function geodir_after_reviewrating_advance_stars_on_listview($avg_rating, $post_id)
{
	$star_html = ob_get_clean();
		$star_html = '';
		$star_html  .= geodir_reviewrating_draw_overall_rating($avg_rating);

	echo $star_html;
}


function geodir_reviewrating_default_options($arr=array()){

	$arr[] = array( 'name' => __( 'Options', GEODIRREVIEWRATING_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'review_rating' );
	
	$arr[] = array( 'name' => __( 'General Settings', GEODIRREVIEWRATING_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'review_rating_default_options');
	
	$arr[] = array(  
		'name'  => __( 'Enable multirating:', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'desc' 	=> __('Enable multirating for comment on post.', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'id' 	=> 'geodir_reviewrating_enable_rating',
		'type' 	=> 'checkbox',
		'std' 	=> '1' // Default value to show home top section
	);
	
	$arr[] = array(  
		'name'  => __( 'Enable comment images upload:', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'desc' 	=> __('Enable upload images in comments for a post.', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'id' 	=> 'geodir_reviewrating_enable_images',
		'type' 	=> 'checkbox',
		'std' 	=> '0' 
	);
	
	$arr[] = array(  
		'name'  => __( 'Enable review on comments:', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'desc' 	=> __('Let\'s users rate comments useful or not.', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'id' 	=> 'geodir_reviewrating_enable_review',
		'type' 	=> 'checkbox',
		'std' 	=> '0' 
	);
	
	$arr[] = array(  
		'name'  => __( 'Enable comment list sorting:', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'desc' 	=> __('Enable comment list sorting.', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'id' 	=> 'geodir_reviewrating_enable_sorting',
		'type' 	=> 'checkbox',
		'std' 	=> '0' 
	);
	
	$arr[] = array(  
		'name'  => __( 'Hide rating stars summary on detail page:', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'desc' 	=> __( 'Hide rating stars summary on the detail page sidebar.', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'id' 	=> 'geodir_reviewrating_hide_rating_summary',
		'type' 	=> 'checkbox',
		'std' 	=> '0' 
	);
	
	/*$arr[] = array(  
		'name'  => __( 'Enable comment social sharing:', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'desc' 	=> __('Enable share a comment on social wall.', GEODIRREVIEWRATING_TEXTDOMAIN ),
		'id' 	=> 'geodir_reviewrating_enable_sharing',
		'type' 	=> 'checkbox',
		'std' 	=> '0' 
	);*/
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'review_rating_default_options');
	
	$arr = apply_filters('geodir_reviewrating_default_options' ,$arr );
	
	return $arr;
}


function geodir_reviewrating_rating_categories($cat_id = ''){	
	global $wpdb;
	$where = '';
	if($cat_id != '')
		$where = $wpdb->prepare(" AND rt.id = %d ", array($cat_id));
									
	$results = $wpdb->get_results("SELECT rt.id as id,
									rt.title as title,
									rt.post_type as post_type,
									rt.category as category,
									rt.category_id as category_id,
									rt.check_text_rating_cond as check_text_rating_cond,	 
									rs.s_img_off  as s_img_off,
									rs.s_img_width as s_img_width,
									rs.s_img_height as s_img_height,
									rs.star_color as star_color,
									rs.star_lables as star_lables,
									rs.star_number as star_number
									FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." rt,".GEODIR_REVIEWRATING_STYLE_TABLE." rs
									WHERE rt.category_id = rs.id $where order by rt.id");								
		
	if(!empty($results) && $cat_id != '')
		return $results[0];
	elseif(!empty($results))
		return $results;
	else
		return false;	
}


function geodir_reviewrating_delete_comments( $comment_id ){ 
	
	global $wpdb;
	
	geodir_reviewrating_delete_comment_images($comment_id);
	
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);
	
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM ".GEODIR_COMMENTS_REVIEWS_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);
}


function geodir_reviewrating_set_comment_status($comment_id,$status){
	global $wpdb, $plugin_prefix, $user_ID;
	
	$comment_info = get_comment($comment_id);
	
	$post_id = isset($comment_info->comment_post_ID) ? $comment_info->comment_post_ID : '';
	
	if(!empty($comment_info))
		$status = $comment_info->comment_approved;
	
	if($status=='approve' || $status==1){$status=1;}else{$status=0;}
	
	$comment_info_ID = isset($comment_info->comment_ID) ? $comment_info->comment_ID : '';
	$old_rating = geodir_get_commentoverall($comment_info_ID);
	
	$post_type = get_post_type($post_id);
	
	$detail_table =  $plugin_prefix . $post_type . '_detail';
	
	if($comment_id){
		
		$overall_rating = $old_rating;
		
		if(isset($old_rating)){
		
			$sqlqry = $wpdb->prepare("UPDATE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." SET
						overall_rating = %f,
						status		= %s 
						WHERE comment_id = %d",
						array($overall_rating,$status,$comment_id)
						);
			
			$wpdb->query($sqlqry);
			
			$post_newrating = geodir_get_review_total($post_id);
			
			if ($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table){
				
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE ".$detail_table." SET overall_rating = %f where post_id =%d",
						array($post_newrating,$post_id)
					)
				);				
								
			}else{
				update_post_meta( $post_id, 'overall_rating', $post_newrating );
			} 
			
		}
		
	
		$post_ratings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ratings FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE post_id = %d AND status=1 AND ratings != ''",
				array($post_id)
			)
		);
		
		$post_comments_rating = array();
		
		if(!empty($post_ratings)){
		$r_count=count($post_ratings);
			foreach($post_ratings as $rating){
				
				$ratings = unserialize($rating->ratings);
				
				foreach($ratings as $rating_id=>$rating_value){
				
					if( !empty($post_comments_rating) && array_key_exists($rating_id,$post_comments_rating) ){
				
						$new_rating_value = (float)$post_comments_rating[$rating_id]['r']  + (float)$rating_value;
						$post_comments_rating[$rating_id]['c'] = $r_count;
						$post_comments_rating[$rating_id]['r'] = $new_rating_value;
						
					}else{
						$post_comments_rating[$rating_id]['c'] = (float)$r_count;
						$post_comments_rating[$rating_id]['r'] = (float)$rating_value;
					}
					
				}
				
			}
		
		}	
		
		if ($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table){
			
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE ".$detail_table." SET ratings  = %s where post_id =%d",
					//array(@serialize($post_comments_rating),$post_id)
					array(@serialize($post_comments_rating),$post_id)
				)
			);
			
		}else{
			update_post_meta( $post_id, 'ratings ', @serialize($post_comments_rating) );
		}	
		
	
	}
	
}


function geodir_reviewrating_filter_comments($comments) {
	
	global $post, $wpdb;
	
	if (empty($post) || (!is_single() && !is_page()) || (isset($post->comment_count) && $post->comment_count <= 0) ) { return $comments; }
	
	if(isset($_REQUEST['comment_sorting']))
	{
		add_filter('comments_clauses', 'geodir_reviewrating_comments_shorting');
		$comments = get_comments( array('post_id' => $post->ID, 'status' => 'approve') );
	}else
		remove_filter('comments_clauses', 'geodir_reviewrating_comments_shorting' );		
	
	return $comments;
}

/* ----------- GET UNACTION CLAIMED --------- */
function geodir_reviewrating_unread_reviews(){
	
	global $wpdb, $plugin_prefix;
	
	$geodir_review_count = $wpdb->get_var(
		"SELECT COUNT(wpc.comment_ID) FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id AND	read_unread='' ");
	
	return $geodir_review_count;
}


function geodir_reviewrating_update_overall_settings(){

	if(current_user_can( 'manage_options' ) && isset($_REQUEST['geodir_overall_rating_nonce']) && wp_verify_nonce( $_REQUEST['geodir_overall_rating_nonce'], 'geodir_overall_rating' )){
	
		global $vailed_file_type, $wpdb;
		
		if(isset($_REQUEST['overall_rating_text']) && $_REQUEST['overall_rating_text']!='')
			update_option('geodir_reviewrating_overall_rating_texts',$_REQUEST['overall_rating_text']);
			//$star_rating_text_value = implode(',',$_REQUEST['overall_rating_text']);
			
		$overall_rating_text = ( isset( $_REQUEST['overall_rating_text'] ) && !empty( $_REQUEST['overall_rating_text'] ) ) ? $_REQUEST['overall_rating_text'] : '';
		$star_rating_text_value = geodir_reviewrating_serialize_star_lables( $overall_rating_text );
		
		if(isset($_REQUEST['overall_count']) &&  $_REQUEST['overall_count']!='')
			update_option('geodir_reviewrating_overall_count',$_REQUEST['overall_count']);
			$style_count = $_REQUEST['overall_count'];
			
		if(isset($_REQUEST['overall_color']) &&  $_REQUEST['overall_color']!='')
			update_option('geodir_reviewrating_overall_color',$_REQUEST['overall_color']);
			$style_color = $_REQUEST['overall_color'];
		
		if(isset($_REQUEST['geodir_reviewrating_show_rating_cond']) && $_REQUEST['geodir_reviewrating_show_rating_cond']!='')
			update_option('geodir_reviewrating_show_rating_cond',$_REQUEST['geodir_reviewrating_show_rating_cond']);
			
		$set_query = 	$wpdb->prepare("SET name = 'overall',
							star_lables = %s,
							star_number = %s,
							star_color = %s,
							is_default = '1' ",
							array($star_rating_text_value,$style_count,$style_color)
							);
		
		if(isset($_FILES['file_off']) && in_array($_FILES['file_off']['type'],$vailed_file_type))
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile = $_FILES['file_off'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile ) {
					@list($width, $height) = getimagesize($movefile['file']);
					if($width){update_option('geodir_reviewrating_overall_off_img_width',$width);$width='';}
					if($height){update_option('geodir_reviewrating_overall_off_img_height',$height);$height='';}
					update_option('geodir_reviewrating_overall_off_img',$movefile['url']);
				} 
		}
		
		
		
		if(get_option('geodir_reviewrating_overall_off_img')){
			$s_file_off_path = get_option('geodir_reviewrating_overall_off_img');
			$set_query .= $wpdb->prepare(", s_img_off = %s ",array(addslashes($s_file_off_path)));
		}else{$default_img = GEODIR_REVIEWRATING_PLUGINDIR_URL."/icons/stars.png";
		update_option('geodir_reviewrating_overall_off_img',$default_img);
		$set_query .= $wpdb->prepare(", s_img_off = %s ",array(addslashes($default_img)));
		}
		
		if(get_option('geodir_reviewrating_overall_on_img_width')):
			$s_file_on_path = get_option('geodir_reviewrating_overall_on_img_width');
			$set_query .= $wpdb->prepare(", s_img_width = %s ", array(addslashes($s_file_on_path)));
		endif;
		
		if(get_option('geodir_reviewrating_overall_half_img_height')):
			$s_file_half_path = get_option('geodir_reviewrating_overall_half_img_height');
			$set_query .= $wpdb->prepare(", s_img_height = %s ",array(addslashes($s_file_half_path)));
		endif;
		
		geodir_add_column_if_not_exist(GEODIR_REVIEWRATING_STYLE_TABLE, 'is_default',  "ENUM( '0', '1' ) NOT NULL DEFAULT '0'");
		
		if(!$wpdb->get_var("SELECT id FROM ".GEODIR_REVIEWRATING_STYLE_TABLE." WHERE is_default='1'"))
		{
			$wpdb->query("INSERT INTO  ".GEODIR_REVIEWRATING_STYLE_TABLE." {$set_query} ");
		}	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
	
}
	

function geodir_reviewrating_update_rating_styles(){
	
	if(current_user_can( 'manage_options' )){
	
		global $vailed_file_type,$wpdb,$multi_rating_path;
	
		if( isset($_REQUEST['ajax_action'])  && $_REQUEST['ajax_action']=='update_styles' && isset($_REQUEST['geodir_update_rating_styles_nonce'])){
			
			if( !wp_verify_nonce( $_REQUEST['geodir_update_rating_styles_nonce'], 'geodir_update_rating_styles' )){
				wp_redirect(home_url());exit();}
			
			$plugin_dir_path = GEODIR_REVIEWRATING_PLUGINDIR_PATH;
			
			$multi_rating_category = $_REQUEST['multi_rating_category'];
			$style_count = $_REQUEST['style_count'];
			$style_color = $_REQUEST['style_color'];
			
			$star_rating_text = $_REQUEST['star_rating_text'];
			
			if ( count( $star_rating_text ) > 0 ) {
				//$star_rating_text_value = implode( ',', $star_rating_text );
				$star_rating_text_value = geodir_reviewrating_serialize_star_lables( $star_rating_text );
			}
			
			$set_query = 	$wpdb->prepare("SET name = %s,
											star_lables = %s,
											star_number = %s , 
											star_color = %s ", 
											array($multi_rating_category,$star_rating_text_value,$style_count,$style_color)
										);
			
			if(isset($_FILES['s_file_off']) && in_array($_FILES['s_file_off']['type'],$vailed_file_type)){
		
				$move_off_file = $plugin_dir_path.'/images/'.$_FILES['s_file_off']['name'];
				
				if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile = $_FILES['s_file_off'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				$s_file_off_path = $movefile['url'];
				if ( $movefile ) {
					@list($width, $height) = getimagesize($movefile['file']);
					if($width){$set_query .= $wpdb->prepare(", s_img_width = %s ", array(addslashes($width)));$width='';}
					if($height){$set_query .= $wpdb->prepare(", s_img_height = %s ", array(addslashes($height)));$height='';}
					$set_query .= $wpdb->prepare(", s_img_off = %s ", array(addslashes($s_file_off_path)));
					
				} 	
			}
			
			
			
			if(isset($_REQUEST['update_category']) && $_REQUEST['update_category'] !='' ){		
		
				$wpdb->query($wpdb->prepare("UPDATE  ".GEODIR_REVIEWRATING_STYLE_TABLE." {$set_query} WHERE id = %d ", array($_REQUEST['update_category'])) );
			
			}else{	
				
				$wpdb->query("INSERT INTO  ".GEODIR_REVIEWRATING_STYLE_TABLE." {$set_query} ");	
			}
		}
		
		if( isset($_REQUEST['ajax_action'])  && $_REQUEST['ajax_action']=='delete_style' && isset($_REQUEST['_wpnonce'])){
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_delete_rating_styles_'.$_REQUEST['cat_id'] ) ){
				wp_redirect(home_url());exit();}
				
			$wpdb->query($wpdb->prepare("DELETE FROM  ".GEODIR_REVIEWRATING_STYLE_TABLE." WHERE id = %d",	array($_REQUEST['cat_id'])));
			
		}
		
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
}


function geodir_reviewrating_delete_comment_images_by_url(){
	
	if(current_user_can( 'manage_options' )){
	
		if( isset($_REQUEST['ajax_action'])  && $_REQUEST['ajax_action']=='remove_images_by_url' && isset($_REQUEST['_wpnonce'])) {
				
				if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'del_img_'.$_REQUEST['remove_image_id'] ) )
					return false;
				
				global $wpdb;
				
				$remove_image_id = $_REQUEST['remove_image_id'];
				
				$img_url = $_REQUEST['img_url'];
				
				$comment_imges = $wpdb->get_var($wpdb->prepare("SELECT comment_images FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",array($remove_image_id)));
				
				$del_images = explode(',',$comment_imges);
				$total_images = count($del_images);
				
				if(($key = array_search($img_url, $del_images)) !== false) {
					unset($del_images[$key]);
					$total_images = $total_images-1;
					
					$wp_upload_dir = wp_upload_dir();
				
					$comment_img_path = $wp_upload_dir['basedir'].'/comment_images/';
					
					$file_name = basename($img_url);
					
					$new_file_name =  $comment_img_path . $file_name;
					
					if(file_exists($new_file_name))
					{
						unlink($new_file_name);
					}
					
				}
				
				$del_images = implode(',', $del_images);
				
				$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE ."
							SET comment_images = %s,
							total_images = %d
							WHERE comment_id = %d",
							array($del_images, $total_images, $remove_image_id)
						 ));
				
				echo $total_images;exit;
			}
		
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}

}


function geodir_reviewrating_add_update_ratings(){
	
	if(current_user_can( 'manage_options' )){
	
		global $wpdb;
		if(isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'update_rating_category' && isset($_REQUEST['geodir_create_rating_nonce_field'])){
		
			if( !wp_verify_nonce( $_REQUEST['geodir_create_rating_nonce_field'], 'geodir_create_rating_nonce' )){
				wp_redirect(home_url());exit();}
			
			$title = $_REQUEST['rating_title'];
		
			if(isset($_REQUEST['number_of_post']) && $_REQUEST['number_of_post']!='')
			{	
				for($j=1;$j<=$_REQUEST['number_of_post'];$j++){
				
					if(isset($_REQUEST['post_type'.$j]) && $_REQUEST['post_type'.$j]!=''){
					
						$post_type[] = $_REQUEST['post_type'.$j];
						
						if(isset($_REQUEST['categories_type_'.$j]) && $_REQUEST['categories_type_'.$j]!=''){
						
							foreach($_REQUEST['categories_type_'.$j] as $value){
								$cat_arr[] = $value;
							}
						}
					}
				}
			}
		
			if(count($cat_arr)>0)
				$categories = implode(',',$cat_arr);
			
			if(count($post_type)>0)
				$post_type = implode(',',$post_type);
		
			$geodir_rating_style_dl = $_REQUEST['geodir_rating_style_dl'];
			$show_text_star_count = $_REQUEST['show_star'];
		
			if(isset($_REQUEST['rating_cat_id']) && $_REQUEST['rating_cat_id'] != '')
			{	
				$category_insert_id = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '';
				
				$sqlqry = $wpdb->prepare(
								"UPDATE ".GEODIR_REVIEWRATING_CATEGORY_TABLE." SET 
								title		= %s,
								post_type 	= %s,
								category	= %s,
								category_id = %s,
								check_text_rating_cond = %s
								WHERE id = %d",
								array($title,$post_type,$categories,$geodir_rating_style_dl,$show_text_star_count,$_REQUEST['rating_cat_id'])
							);
				
				$wpdb->query($sqlqry);	
			
			}else if($title !='' && $post_type !='' && $categories !=''){
			
				$geodir_rating_style_dl = $_REQUEST['geodir_rating_style_dl'];
				
				$sqlqry = $wpdb->prepare(
								"INSERT INTO ".GEODIR_REVIEWRATING_CATEGORY_TABLE." SET
								title		= %s,
								post_type 	= %s,
								category	= %s,
								category_id = %s,
								check_text_rating_cond = %s",
								array($title,$post_type,$categories,$geodir_rating_style_dl,$show_text_star_count)
							);
							
				$wpdb->query($sqlqry);
			}
		}
		
		if(isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'delete_rating_category' && isset($_REQUEST['_wpnonce'])){	
		
		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_delete_rating_'.$_REQUEST['rating_cat_id'] ) ){
				wp_redirect(home_url());exit();}
				
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE id = %d", array($_REQUEST['rating_cat_id'])));
		
	}
	
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
}


function geodir_reviewrating_update_review_setting(){
	
	global $vailed_file_type;
	
	if(current_user_can( 'manage_options' ) && isset($_REQUEST['geodir_update_review_nonce_field'])){
	
		if( !wp_verify_nonce( $_REQUEST['geodir_update_review_nonce_field'], 'geodir_update_review_nonce' )){
				wp_redirect(home_url());exit();}
	
		if(isset($_FILES['file_like']) && in_array($_FILES['file_like']['type'],$vailed_file_type))
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile = $_FILES['file_like'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile ) {
					update_option('geodir_reviewrating_review_like_img',$movefile['url']);
				} 
		}
		
		if(isset($_FILES['file_unlike']) && in_array($_FILES['file_unlike']['type'],$vailed_file_type))
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile = $_FILES['file_unlike'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile ) {
					update_option('geodir_reviewrating_review_unlike_img',$movefile['url']);
				}
		}
		
	}else{
		
		wp_redirect(home_url().'/?geodir_signup=true');
		exit();
	
	}
}


/* Review Rating ajax submit function */
function geodir_reviewrating_ajax_actions(){
	
	global $wpdb;
	$url = admin_url( 'admin.php');
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_multirating_options'){
		
		geodir_update_options(geodir_reviewrating_default_options());
		
		$msg = __('Your settings have been saved.', GEODIRREVIEWRATING_TEXTDOMAIN);
		
		$msg = urlencode($msg);
		
		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_multirating_options&gdrr_success='.$msg), $url );
		wp_redirect( $url );exit;
	}
	
	if($_REQUEST['ajax_action'] == 'update_overall_setting'){
	
		geodir_reviewrating_update_overall_settings();
		
		$msg = __('Your settings have been saved.', GEODIRREVIEWRATING_TEXTDOMAIN);
		
		$msg = urlencode($msg);
		
		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_rating_settings&gdrr_success='.$msg), $url );
		wp_redirect( $url );exit;
	}
	
	if($_REQUEST['ajax_action'] == 'update_review_setting'){
		
		geodir_reviewrating_update_review_setting();
		
		$msg = __('Your settings have been saved.', GEODIRREVIEWRATING_TEXTDOMAIN);
		
		$msg = urlencode($msg);
		
		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_manage_review&gdrr_success='.$msg), $url );
		wp_redirect( $url );exit;
	}
	
	if($_REQUEST['ajax_action'] == 'update_styles' || $_REQUEST['ajax_action'] == 'delete_style' ){
		
		geodir_reviewrating_update_rating_styles();
		
		$msg = __('Your settings have been saved.', GEODIRREVIEWRATING_TEXTDOMAIN);
		
		if($_REQUEST['ajax_action'] == 'delete_style')
			$msg = __('Rating Style Delete successfully.', GEODIRREVIEWRATING_TEXTDOMAIN);
		
		$msg = urlencode($msg);
		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_rating_style&gdrr_success='.$msg), $url );
		
		wp_redirect( $url );exit;
	}
	
	if($_REQUEST['ajax_action'] == 'update_rating_category' || $_REQUEST['ajax_action'] == 'delete_rating_category' ){
		
		geodir_reviewrating_add_update_ratings();
		
		$msg = __('Your settings have been saved.', GEODIRREVIEWRATING_TEXTDOMAIN);
		if($_REQUEST['ajax_action'] == 'delete_rating_category')
			$msg = __('Rating Delete successfully.', GEODIRREVIEWRATING_TEXTDOMAIN);
		
		$msg = urlencode($msg);
		
		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_create_rating&gdrr_success='.$msg), $url );
		
		wp_redirect( $url );exit;
	}
	
	if($_REQUEST['ajax_action'] == 'ajax_tax_cat'){
		
		if(isset($_REQUEST['post_type'])){
			global $cat_display;	
			$cat_display = 'select';
			echo geodir_custom_taxonomy_walker($_REQUEST['post_type'].'category');
		}
		exit;
	}
	
	if($_REQUEST['ajax_action'] == 'review_update_frontend'){
	
		geodir_reviewrating_save_like_unlike($_REQUEST['ajaxcommentid']);
		
	}
	
	if($_REQUEST['ajax_action'] == 'comment_actions' || $_REQUEST['ajax_action'] == 'show_tab_head'){
	
		geodir_reviewrating_comment_action($_REQUEST);
	
	}
	
	if(isset( $_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'remove_images_by_url'){
		
		geodir_reviewrating_delete_comment_images_by_url();
		
	}
}

/* Review Rating module related Post Metabox function */
function geodir_reviewrating_comment_metabox(){

	add_meta_box('geodir_comment_xtra_box', __('Extra Arguments', GEODIRREVIEWRATING_TEXTDOMAIN), 'geodir_reviewrating_comment_rating_box', 'comment', 'normal','high');
}


/* new comments change unread to read */
function geodir_reviewrating_reviews_change_unread_to_read(){
	
	global $wpdb, $plugin_prefix;
	
	if(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'reviews_fields'):
		
		$wpdb->query("update ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." set read_unread='1' where read_unread = ''");
		
	endif;
}

/* Review Rating module delete images function */
function geodir_reviewrating_delete_comment_images($comment_id){

	global $wpdb;
	
	$del_comment_imges = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT comment_images FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);

	if($del_comment_imges != '')
	{
		$del_images = explode(',', $del_comment_imges);
		
		$wp_upload_dir = wp_upload_dir();
		
		$comment_img_path = $wp_upload_dir['basedir'].'/comment_images/';
		
		foreach($del_images as $img)
		{
			$file_name = basename($img);
			
			$new_file_name =  $comment_img_path . $file_name;
			
			if(file_exists($new_file_name))
			{
				unlink($new_file_name);
			}
		}	
	}
}

/* Review Rating module plupload_action */

if (!function_exists('geodir_reviewrating_plupload_action')) {
	
	function geodir_reviewrating_upload_dir($upload) {
		global $wpdb, $current_user;
		
		$temp_folder_name = 'temp_'.$current_user->data->ID;
	
		if($current_user->data->ID == '')
		{
			$temp_folder_name = 'temp_'.session_id();
			
			$geodir_unassing_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT count(id) FROM ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." WHERE directory=%s",
					array($temp_folder_name)
				)
			);
			
			
			
			
			if($geodir_unassing_count == 0 )
			{
				
				
				$date = date("Y-m-d H:i:s");
				
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." (directory, date) VALUES (%s, %s)",
						array($temp_folder_name,$date)
					)
				);
				
			}	
			
		}
		
		$upload['subdir']	= $upload['subdir'].'/'.$temp_folder_name;
		$upload['path']		= $upload['basedir'] . $upload['subdir'];
		$upload['url']		= $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}
	
	function geodir_reviewrating_plupload_action() {
	 
		// check ajax noonce
		$imgid = $_POST["imgid"];
		
		check_ajax_referer($imgid . 'pluploadan');
	 	
		// handle custom file uploaddir
		add_filter('upload_dir', 'geodir_reviewrating_upload_dir');
				
		// handle file upload
		$status = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'geodir_reviewrating_plupload'));
	 	// remove handle custom file uploaddir
	 	remove_filter('upload_dir', 'geodir_reviewrating_upload_dir');
		// send the uploaded file url in response
		if ( isset( $status['url'] ) && $status['url'] != '' ) {
			echo $status['url'];
		} else {
			echo '';
		}
		exit;
	}
}

/* Review Rating replay link */
function geodir_reviewrating_comment_replylink($link){
	
	$link = '<div id="gd_comment_replaylink">'.$link.'</div>';
	
	return $link;
}

/* Review Rating cancel reply link */
function geodir_reviewrating_cancle_replylink($link){
	
	$link = '<div id="gd_cancle_replaylink">'.$link.'</div>';
	
	return $link;
}

/* Review Rating update Comment Rating */
function geodir_reviewrating_update_comments($comment_content){
	global $wpdb, $post, $user_ID;
	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'editedcomment'):
	
	$comment_ID = $_REQUEST['comment_ID'];
	$comment_post_ID = $_REQUEST['comment_post_ID'];
	
	if(isset($_REQUEST['geodir_rating']) && is_array($_REQUEST['geodir_rating'])){
		
		$rating = array();
		
		foreach($_REQUEST['geodir_rating'] as $key => $value ){
			
			if($key != 'overall'){
				$rating[$key] = $value;
			}else{
				$overall_rating = $value;
			}	
		}
		
		if(!empty($rating))
			$ratings = serialize($rating);
	}
	//print_r($_REQUEST);
	//print_r($rating);echo'###';
	$comment_images = '';	
	if(isset($_POST['comment_images']) && $file_info = $_POST['comment_images']){
	
		if($file_info != '')
		{
			$newArr = explode(',', $file_info);
			$comment_images = geodir_reviewrating_add_remove_images($comment,$newArr);
			
			if(!empty($comment_images))
			{
					$comment_images = implode(',', $comment_images);
			}
		}
	}	
	
	$status = isset($_POST['comment_status']) ? $_POST['comment_status'] : '';
	if(!empty($rating) || $overall_rating || $comment_images != ''){
		$rating_ip = getenv("REMOTE_ADDR");	
							
		$sqlqry = $wpdb->prepare(
								"UPDATE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." SET
								ratings		= %s,
								overall_rating = %f,
								comment_images = %s,
								status		= %s 
								WHERE comment_id = %d",
								array($ratings,$overall_rating,$comment_images,$status,$comment_ID)
							);					
			
		$wpdb->query($sqlqry);
		if(!empty($rating) || $overall_rating)	
			geodir_reviewrating_update_postrating($comment_post_ID,$rating,$overall_rating);
	}
	
	endif;
	
	return $comment_content;
}



/* Review Rating insert Comment Rating */
function geodir_reviewrating_save_rating($comment = 0){
	global $wpdb, $post, $user_ID;
	
	$post_id = isset($post->ID) ? $post->ID : '';
	
	$post_type = get_post_type( $post_id );
	
	$all_postypes = geodir_get_posttypes();

	if(!in_array($post_type, $all_postypes))
		return false;
	
	$comment_info = get_comment($comment);
	$status = $comment_info->comment_approved;
	$status = $comment_info->comment_approved;
	
	if(isset($_REQUEST['geodir_rating']) && is_array($_REQUEST['geodir_rating']) && get_option('geodir_reviewrating_enable_rating')){
		
		$rating = array();
		
		foreach($_REQUEST['geodir_rating'] as $key => $value ){
			
			if($key != 'overall'){
				$rating[$key] = $value;
			}else{
				$overall_rating = $value;
			}	
		}
		
		if(!empty($rating))
			$ratings = serialize($rating);
			
	}else{
		
		$overall_rating = $_REQUEST['geodir_overallrating'];
		
	}
	if ( isset( $comment_info->comment_parent ) && (int)$comment_info->comment_parent == 0 ) {
		$overall_rating = $overall_rating > 0 ? $overall_rating : '0';
	} else {
		$overall_rating = '';
	}
	
	$comment_images = '';
	
	$total_images = '';
	
	if(isset($_POST['comment_images']) && $_POST['comment_images'] != ''){
	
		$file_info = $_POST['comment_images'];
	
		if($file_info != '')
		{
			$newArr = explode(',', $file_info);
			
			$total_images = count($newArr);
			
			$comment_images = geodir_reviewrating_add_remove_images($comment,$newArr);
			
			if(!empty($comment_images))
			{
					$comment_images = implode(',', $comment_images);
					
					
			}

		}
	}	
			
	if(!empty($rating) || $overall_rating || $comment_images != ''){
		
		$ratings = isset($ratings) ? $ratings : '';
		
		$rating_ip = getenv("REMOTE_ADDR");	
		global $plugin_prefix;
		$post_details = $wpdb->get_row("SELECT * FROM ".$plugin_prefix.$post->post_type."_detail WHERE post_id =".$post->ID);
		if($post->post_status=='publish'){$post_status='1';}else{$post_status='0';}
		$sqlqry = $wpdb->prepare(
								"INSERT INTO ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." SET
								post_id		= %d,
								post_title	= %s,
								post_type	= %s,
								user_id		= %d,
								comment_id	= %d,
					   			total_images = %d,
								rating_ip	= %s,
								ratings		= %s,
								overall_rating = %f,
								comment_images = %s,
								status		= %s,
								post_status		= %s,
								post_date		= %s, 
								post_city		= %s, 
								post_region		= %s, 
								post_country	= %s
								",
								array($post->ID,$post->post_title,$post->post_type,$user_ID,$comment,$total_images,$rating_ip,$ratings,$overall_rating,$comment_images,$status,$post_status,date("Y-m-d H:i:s"),$post_details->post_city,$post_details->post_region,$post_details->post_country)
							);
			
		$wpdb->query($sqlqry);
		
		if(!empty($rating) || $overall_rating)	
			geodir_reviewrating_update_postrating($post->ID,$rating,$overall_rating);
	}
}

/* Review Rating remove unncesssary images */
function geodir_reviewrating_remove_unncesssary_directories()
{
	global $wpdb;
	
	$date = date('Y-m-d H:i:s', strtotime("-1 day"));
	
	$unassing_imges = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, directory FROM ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." WHERE date<=%s",
			array($date)
		)
	);
		
	if(!empty($unassing_imges))
	{
		foreach($unassing_imges as $unimg)
		{
			
			$wp_upload_dir = wp_upload_dir();
			$temp_folder = $wp_upload_dir['path'] . '/'.$unimg->directory;
			
			geodir_delete_directory($temp_folder);
			/*if(is_dir($temp_folder))
			{
				$dirPath = $temp_folder;
				if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
					$dirPath .= '/';
				}
				$files = glob($dirPath . '*', GLOB_MARK);
				foreach ($files as $file) {
					if (is_dir($file)) {
						self::deleteDir($file);
					} else {
						unlink($file);
					}
				}
				rmdir($dirPath);
			}*/
			
			
			$wpdb->get_results(
				$wpdb->prepare(
					"DELETE FROM ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." WHERE id=%d",
					array($unimg->id)
				)
			);
			
		}	
	}	
}
	

/* Review Rating comments shorting */
function geodir_reviewrating_comments_shorting($clauses){
		
		global $post, $wpdb, $comments_shorting;
		
		switch($_REQUEST['comment_sorting'])
		{
			case 'low_rating':
					$sorting_orderby = 'geodir_post_review.overall_rating';
					$sorting_order = 'ASC';
				break;
				
			case 'high_rating':
					$sorting_orderby = 'geodir_post_review.overall_rating';
					$sorting_order = 'DESC';
				break;
				
			case 'low_review':
					$sorting_orderby = 'wasthis_review';
					$sorting_order = 'ASC';
				break;
			
			case 'high_review':
					$sorting_orderby = 'wasthis_review';
					$sorting_order = 'DESC';
				break;
					
			case 'oldest':
					$sorting_orderby = 'comment_date_gmt';
					$sorting_order = 'ASC';
				break;
				
			case 'least_images':
					$sorting_orderby = 'total_images';
					$sorting_order = 'ASC';
				break;
				
		    case 'highest_images':
					$sorting_orderby = 'total_images';
					$sorting_order = 'DESC';
				break;			
				
			default:
					$sorting_orderby = 'comment_date_gmt';
					$sorting_order = 'DESC';
			
		}
		
	$clauses['fields']	=	'*';
	$clauses['orderby']	=	$sorting_orderby;
    $clauses['order'] 	= $sorting_order;
	$clauses['join'] 	.= ", geodir_post_review";
    $clauses['where'] 	.= " AND geodir_post_review.comment_id = $wpdb->comments.comment_ID  ";
	$clauses['groupby'] .= " $wpdb->comments.comment_ID  ";
		
		return $clauses;
		
}


/* Review Rating comment ajax actions */
function geodir_reviewrating_comment_action($request){
		global $wpdb;
		
		$comment_ids = array();
		if(isset($request['comment_ids']) && $request['comment_ids'] != '')
		$comment_ids = explode(',', $request['comment_ids']);
		
		if(!empty($comment_ids) && $request['comment_ids'] != ''){
		
			if( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_review_action_nonce' ))
				return false;
			
			foreach($comment_ids as $comment_id){
				
				if($comment_id != ''){
				
					switch ( $request['comment_action'] ){
						case 'deletecomment' :
							wp_delete_comment( $comment_id );
							break;
						case 'trashcomment' :
							wp_trash_comment($comment_id);
							break;
						case 'untrashcomment' :
							wp_untrash_comment($comment_id);
							break;
						case 'spamcomment' :
							wp_spam_comment($comment_id);
							break;
						case 'unspamcomment' :
							wp_unspam_comment($comment_id);
							break;
						case 'approvecomment' :
							wp_set_comment_status( $comment_id, 'approve' );
							break;
						case 'unapprovecomment' :
							wp_set_comment_status( $comment_id, 'hold' );
							break;
					}
					
				}
			
			}
			
				if(isset($request['geodir_comment_search']))
					$geodir_commentsearch = $request['geodir_comment_search'];
				
				if(isset($request['geodir_comment_posttype']))
					$post_type = $request['geodir_comment_posttype'];
				
				$status = $request['subtab'];
				
				$orderby = 'comment_date_gmt';
					$order = 'DESC';
					if(isset($request['geodir_comment_sort']) )
					{	
						if($request['geodir_comment_sort'] == 'oldest'){
							$orderby = 'comment_date_gmt';
							$order = 'ASC';
					}
				}
				
				if(isset($request['paged']) && $request['paged'] != '')
				{
					$paged = $request['paged'];
				}
				else
				{
					$paged = 1;
				}
				
				$show_post = $request['show_post'];
				
				$defaults = array(
				'paged' => $paged,
				'show_post' => $show_post,
				'orderby' => $orderby,
				'order' => $order,
				'post_type' => $post_type,
				'comment_approved' => $status,
				'user_id' => '',
				'search' => $geodir_commentsearch,
				);
				
				$comments = geodir_reviewrating_get_comments($defaults);  
				
				geodir_reviewrating_show_comments($comments['comments']);
		
		}
		
		if(isset($request['gd_tab_head'])){
			
			geodir_reviewrating_show_tab_head($request['gd_tab_head']);
			
		}
		
		exit;		
}


function geodir_reviewrating_get_style_by_id($style_id = '')
{
	global $wpdb;
	
	$select_style = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM ".GEODIR_REVIEWRATING_STYLE_TABLE." WHERE id = %d",
			array($style_id)
		)
	);
 
	if(!empty($select_style))
		return $select_style;
	else
		return false;	
}


function geodir_reviewrating_get_styles(){
	
	global $wpdb;
	$styles = array();
	
	$styles = $wpdb->get_results("SELECT * FROM ".GEODIR_REVIEWRATING_STYLE_TABLE);
	
	if(!empty($styles))
		return $styles; 
	else
		return false; 
}	


function geodir_reviewrating_get_comment_rating_by_id($comment_id = 0){
	
	global $wpdb;
	$reatings = array();
	
	$reatings = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT ratings,overall_rating,status FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);
	
	if(!empty($reatings))
		return $reatings; 
	else
		return false; 
}


function geodir_reviewrating_update_postrating_all($post_id = 0){
	
	
	global $wpdb,$plugin_prefix;
	
	$post = get_post( $post_id );
	
	$post_ratings = array();

#########################################################
$post_ratings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ratings FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE post_id = %d AND status=1 AND ratings != ''",
				array($post_id)
			)
		);
		
		$post_comments_rating = array();
		$new_ratings = array();
		
		if(!empty($post_ratings)){
		$r_count=count($post_ratings);
			foreach($post_ratings as $rating){
				
				$ratings = unserialize($rating->ratings);
				
				foreach($ratings as $rating_id=>$rating_value){
				
					if( !empty($post_comments_rating) && array_key_exists($rating_id,$post_comments_rating) ){
				
						$new_rating_value = (float)$post_comments_rating[$rating_id]['r'] + (float)$rating_value;
						$post_comments_rating[$rating_id]['c'] = $r_count;
						$post_comments_rating[$rating_id]['r'] = $new_rating_value;
						
					}else{
						$post_comments_rating[$rating_id]['c'] = (float)$r_count;
						$post_comments_rating[$rating_id]['r'] = (float)$rating_value;
					}
					
				}
				
			}
		
		}
		if($post_comments_rating){$new_ratings = $post_comments_rating;}
##########################################################


	$overall_rating = '';
		
	
	$post_newrating_count = geodir_get_review_count_total($post_id);
	$overall_rating = geodir_get_review_total($post_id);
	
	$detail_table = $plugin_prefix . $post->post_type . '_detail';
	if($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table)
	{
		$post_rating = serialize($new_ratings);
		 	
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `".$detail_table."` SET ratings = %s, overall_rating = %f, rating_count=%f where post_id =%d",
					array($post_rating,$overall_rating,$post_newrating_count,$post_id)
				)
			);
		
	}else{
		update_post_meta( $post_id, 'ratings', $new_ratings );
		update_post_meta( $post_id, 'overall_rating', $overall_rating );
	} 
	

	
}
function geodir_reviewrating_update_postrating($post_id = 0, $ratings, $overall){
	geodir_reviewrating_update_postrating_all($post_id); return; // DISABLED FOR NOW, WE WILL JUST CALL AN OVERAL UPDATE FUNCTION ON COMMENT SAVE. geodir_reviewrating_update_postrating_all
	
	global $wpdb,$plugin_prefix;
	
	$post = get_post( $post_id );
	
	$post_ratings = array();
	$post_ratings = geodir_reviewrating_get_post_rating($post_id);
	//print_r($post_ratings);exit;
	$old_ratings = $post_ratings;
	$new_ratings = array();
	//print_r($ratings);exit;
	if(!empty($ratings)){
		$r_count = count($ratings);
		foreach($ratings as $rating_id=>$rating_value){
			
			$rating_info = geodir_reviewrating_rating_categories($rating_id);
			
			if( !empty($post_ratings) && array_key_exists($rating_id,$old_ratings) ){
				
				$new_rating_value = (float)$old_ratings[$rating_id]['r'] + (float)$rating_value;
				$new_ratings[$rating_id]['c'] = $new_rating_value;
				$new_ratings[$rating_id]['r'] = (float)$old_ratings[$rating_id]['c']+1;
				
			}/*elseif($post->comment_count > 1){
				$new_ratings[$rating_id] = ($post->comment_count * $rating_info->star_number) + (float)$rating_value;
			}*/else{
				$new_ratings[$rating_id]['c'] = (float)$r_count;
				$new_ratings[$rating_id]['r'] = (float)$rating_value;
			}
		}
	}	
	$overall_rating = '';
	if($overall){	
		if(isset($post_ratings['overall']) && !empty($post_ratings['overall']))
			$overall_rating =  (float)$post_ratings['overall'] + (float)$overall;
		else
			$overall_rating =  (float)$overall;	
	}	
	
	$post_newrating_count = geodir_get_review_count_total($post_id);
	
	$detail_table = $plugin_prefix . $post->post_type . '_detail';
	if($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table)
	{
		$post_rating = serialize($new_ratings);
		 	
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `".$detail_table."` SET ratings = %s, overall_rating = %f, rating_count=%f where post_id =%d",
					array($post_rating,$overall_rating,$post_newrating_count,$post_id)
				)
			);
		
	}else{
		update_post_meta( $post_id, 'ratings', $new_ratings );
		update_post_meta( $post_id, 'overall_rating', $overall_rating );
	} 
	
}


function geodir_reviewrating_get_post_rating($post_id) {
	global $wpdb, $plugin_prefix;
	
	$post_type = get_post_type( $post_id );
	$detail_table = $plugin_prefix . $post_type . '_detail';
	
	$ratings = array();
	
	if ($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table) {
		$sql = $wpdb->prepare(
				"SELECT ratings, rating_count, overall_rating FROM ".$detail_table." WHERE post_id = %d",
				array($post_id)
			);
		$post_ratings = $wpdb->get_row($sql);
		
		if (!empty($post_ratings) && $post_ratings->rating_count>0 ) {
			$old_rating = @unserialize($post_ratings->ratings);
			
			if (function_exists('geodir_get_commentoverall_number')) {
				$overall = 	geodir_get_commentoverall_number($post_id);
			} else {
				$overall = 	$post_ratings->overall_rating/$post_ratings->rating_count;
			}
		}
		
	} else {
		$old_rating_val = get_post_meta( $post_id, 'ratings');
		if(isset($post_id) && is_array($old_rating_val)){$old_rating = end($old_rating_val);}else{$old_rating =array();}
		$overall_val = get_post_meta( $post_id, 'overall_rating');
		if(isset($post_id) && is_array($overall_val)){$overall= end($overall_val);}else{$overall =array();}
	} 
	
	if (!empty($old_rating)) {
		foreach ($old_rating as $key=>$value) {
			$ratings[$key] = $value;
		}
	}		
	
	if (isset($overall) && $overall != '') {
		$ratings['overall'] = $overall;
	}
	
	if(!empty($ratings))
		return $ratings;
	else
		return false;	
}


function geodir_reviewrating_get_comments_count($status = ''){
	
	global $wpdb;
	
	switch($status):
	
		case 'approved':
			$status = " AND wpc.comment_approved = '1' ";
		break;
		
		case 'pending':
			$status = " AND wpc.comment_approved = '0' ";
		break;
		
		case 'trash':
		case 'spam':
			$status = $wpdb->prepare(" AND wpc.comment_approved = %s ", array($status));
		break;
		
		default:
			$status = " AND wpc.comment_approved != 'spam' AND wpc.comment_approved != 'trash' ";
			
	endswitch;
	
	$geodir_review_count = $wpdb->get_var("SELECT COUNT(wpc.comment_ID) 
						FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc
						WHERE wpc.comment_ID = gdc.comment_id ".$status);
	
	return $geodir_review_count;
	exit;
}


function geodir_reviewrating_get_comments($default=''){
	global $wpdb,$paged, $show_post;
	
	$condition = '';
	$orderby = '';
	$value_order = '';
	$value_field = '';
	$limit = '';
	
	$search_array = array();
	foreach($default as $key=>$value)
	{	
		if($key == 'comment_approved')
		{
			switch($value):
				case 'approved':
					$condition .= " AND wpc.comment_approved = '1' ";
				break;
				case 'pending':
					$condition .= " AND wpc.comment_approved = '0' ";
				break;
				case 'trash':
				case 'spam':
					$condition .= " AND wpc.comment_approved = '{$value}' ";
				break;
				default:
					$condition .= " AND wpc.comment_approved != 'spam' AND wpc.comment_approved != 'trash' ";
			endswitch;
		}
		elseif($key == 'orderby' || $key == 'order' )
		{
			if($key == 'orderby')
			{
				$value_field = $value;
			}
			else
			{
				$value_order = $value;
			}
			
			$orderby = " ORDER BY wpc.{$value_field} {$value_order} ";
			
			if($value_field == 'overall_rating')
			{
				$orderby = " ORDER BY gdc.{$value_field} {$value_order} ";
			}
			
		}
		elseif($value != '' && $key == 'post_type')
		{
			$condition .= " AND gdc.{$key} = '{$value}' ";
		}
		elseif($value != '' && $key == 'search' )
		{
			$condition .= " AND (gdc.post_title LIKE %s || wpc.comment_author LIKE %s || wpc.comment_content LIKE %s ) ";
			
			$search_array = array('%'.$value.'%','%'.$value.'%','%'.$value.'%');
			
		}
		elseif($value != '' && $key == 'paged')
		{
			$paged = $value;
			$start = $value;
		}
		elseif($value != '' && $key == 'show_post')
		{
			$show_post = $value;
		}	
	}
	
	if($condition == '')
	{
		$condition .= " AND wpc.comment_approved != 'spam' AND wpc.comment_approved != 'trash' ";
	}
	
	if($start != '' && $show_post != '')
	{
		if($start > 0)
		{
			
			$start = ($start-1)*$show_post;
			
			$limit = "LIMIT $start, $show_post";
		}
		else
		{
			$limit = "LIMIT $start, $show_post";
		}
	}
	
	if(!empty($search_array)) { 
		$array['comment_count'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(wpc.comment_ID) FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby, $search_array));
		
		$array['comments'] = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby.$limit, $search_array));
	}else{
	
		$array['comment_count'] = $wpdb->get_var("SELECT COUNT(wpc.comment_ID) FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby);
		
		$array['comments'] = $wpdb->get_results("SELECT * FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby.$limit);
	
	}
	
	return $array;
	
}


function geodir_reviewrating_save_like_unlike($ajaxcommentid){
		
		global $wpdb;
		
		if($ajaxcommentid != '')
		{
			$ids = explode('-', $ajaxcommentid);
			
			$comment_id = $ids[1];
			
			$like_unlike = $ids[0];
			
			$ip_address = $_SERVER["REMOTE_ADDR"];
			
			$has_liked = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT count(like_id) FROM ".GEODIR_COMMENTS_REVIEWS_TABLE." WHERE comment_id=%d AND ip=%s",
					array($comment_id,$ip_address)
				)
			);
		
			if($like_unlike == 'like')
			{
				if($has_liked == 0)
				{
					
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE geodir_post_review SET wasthis_review=wasthis_review+1 WHERE comment_id=%d",
							array($comment_id)
						)
					);
					
					$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO ".GEODIR_COMMENTS_REVIEWS_TABLE." (comment_id, ip, like_unlike) VALUES (%d, %s, '1')",
							array($comment_id,$ip_address)
						)
					);
				
				}
			}
			else
			{
			
				if($has_liked > 0)
				{
				
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE geodir_post_review SET wasthis_review=wasthis_review-1 WHERE comment_id=%d",
							array($comment_id)
						)
					);
					
					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM ".GEODIR_COMMENTS_REVIEWS_TABLE." WHERE comment_id=%d AND ip=%s",
							array($comment_id,$ip_address)
						)
					);
					
				}
			}
			
		}
		
		geodir_reviewrating_comments_like_unlike($comment_id);
		exit;
		
}


function geodir_reviewrating_add_remove_images($comment,$newArr){
	
	global $wpdb, $current_user;
	
	$temp_folder_name = 'temp_'.$current_user->data->ID;
	
	if($current_user->data->ID == '')
		$temp_folder_name = 'temp_'.session_id();
	
	$wp_upload_dir = wp_upload_dir();
	$temp_folder = $wp_upload_dir['path'] . '/'.$temp_folder_name;
	$comment_img_path = $wp_upload_dir['basedir'].'/comment_images';
	
	if(!is_dir($comment_img_path)):
		
		$target = 'wp-content/uploads/comment_images';
		wp_mkdir_p( $target );
		
	endif;
	
	
	$comment_images = array();
	foreach($newArr as $img)
	{
		
		$file_ext = pathinfo($img, PATHINFO_EXTENSION);
		$file_name = basename($img, ".".$file_ext);
		$filename =  $temp_folder.'/'. basename($img);
		$new_file_name =  $comment_img_path . '/'. $file_name.'_'.time().'.'.$file_ext;
		copy($filename, $new_file_name);
		$comment_images[] = $wp_upload_dir['baseurl'] . '/comment_images/'. $file_name.'_'.time().'.'.$file_ext;
		
	}
	
	geodir_delete_directory($temp_folder);
	
	/*if(is_dir($temp_folder))
	{
		$dirPath = $temp_folder;
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				self::deleteDir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}*/
	
	return $comment_images;
	
}


function geodir_reviewrating_create_new_post_type($post_type = ''){
	
	global $wpdb, $plugin_prefix;
	
	if($post_type != ''){
	
		$all_postypes = geodir_get_posttypes();
		
		if(!in_array($post_type, $all_postypes))
			return false;
		
		$detail_table = $plugin_prefix . $post_type . '_detail';	
		geodir_add_column_if_not_exist($detail_table, 'ratings',  'TEXT NULL DEFAULT NULL');
		
	}
}


/* function for display geodirectory review rating error and success messages */
function geodir_reviewrating_display_messages(){

	if(isset($_REQUEST['gdrr_success']) && $_REQUEST['gdrr_success'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' . $_REQUEST['gdrr_success'] . '</strong></p></div>';			
				
	}
	
	if(isset($_REQUEST['gdrr_error']) && $_REQUEST['gdrr_error'] != '')
	{
			echo '<div id="payment_message_error" class="updated fade"><p><strong>' . $_REQUEST['gdrr_error'] . '</strong></p></div>';			
	
	}
}


function geodir_review_rating_is_reviews_show($is_display, $pageview){
	
	if(get_option('geodir_reviewrating_enable_rating')){
		$is_display = true;
	}else{
		$is_display = false;
	}
		
	return $is_display;
}


function geodir_reviewrating_comment_meta_row_action( $a ) {
	global $comment;
	
	$rating = geodir_get_commentoverall($comment->comment_ID);
	if($rating != 0){
		
		$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
		echo geodir_reviewrating_draw_overall_rating($comment_ratings->overall_rating);
		
	}
	return $a;
}

function geodir_reviewrating_serialize_star_lables( $star_lables ) {
	if ( empty( $star_lables ) ) {
		return $star_lables;
	}
	
	$star_lables = maybe_serialize( $star_lables );
	
	return $star_lables;
}

function geodir_reviewrating_star_lables_to_arr( $star_lables ) {
	if ( $star_lables == '' ) {
		return array();
	}
	
	if ( is_serialized( $star_lables ) ) {
		$star_lables = maybe_unserialize( $star_lables );
	} else {
		$star_lables = explode( ',', $star_lables );
	}
	
	return $star_lables;
}

function geodir_reviewrating_star_lables_to_str( $star_lables, $separator = ',' ) {
	if ( empty( $star_lables ) ) {
		return NULL;
	}
	
	if ( is_serialized( $star_lables ) ) {
		$star_lables = maybe_unserialize( $star_lables );
	}
	
	if ( is_array( $star_lables ) ) {
		$star_lables = implode( $separator, $star_lables );
	}
	$star_lables = $star_lables != '' ? stripslashes_deep( $star_lables ) : $star_lables;
	
	return $star_lables;
}

function geodir_reviewrating_action_on_init() {
	global $wpdb;
	
	/* change field type varchar to text*/
	if ( !get_option( 'geodir_reviewrating_change_star_lables_field' ) ) {
		$wpdb->query( "ALTER TABLE `" . GEODIR_REVIEWRATING_STYLE_TABLE . "` CHANGE `star_lables` `star_lables` TEXT NOT NULL" );
		
		update_option( 'geodir_reviewrating_change_star_lables_field', '1' );
	}
}

/* display rating summary on detail page sidebar*/
function geodir_reviewrating_detail_page_rating_summary( $avg_rating, $post_id ) {
	$html = '';
	if ( get_option( 'geodir_reviewrating_hide_rating_summary' ) ) {
		return $html;
	}
	
	if ( !empty( $post_id ) && $avg_rating != '' ) {
		$geodir_post_info = geodir_get_post_info( $post_id );
		
		if ( !empty( $geodir_post_info ) ) {
			$post_title = $geodir_post_info->post_title;
			$post_thumbnail = '';
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );
			
			$overall_max_rating = (int)get_option( 'geodir_reviewrating_overall_count' );
			$total_reviews = geodir_get_review_count_total( $post_id );
			
			if ( $total_reviews > 0 ) {
				$html .= '<div class="average-review">';
				
				$avg_ratings = ( is_float( $avg_rating ) || ( strpos( $avg_rating, ".", 1 ) == 1 && strlen( $avg_rating ) > 3) ) ? number_format( $avg_rating, 1, '.', '') : $avg_rating;
								
				$html .= '<span>';
				$html .= '<span class="rating">' . $avg_ratings . '</span>&nbsp;/&nbsp;<span>' . $overall_max_rating . '</span> '.__( 'based on', GEODIRREVIEWRATING_TEXTDOMAIN ) . '&nbsp;<span class="count">' . $total_reviews . '</span>&nbsp;';
				$html .= $total_reviews > 1 ? __( 'reviews', GEODIRREVIEWRATING_TEXTDOMAIN ) : __( 'review', GEODIRREVIEWRATING_TEXTDOMAIN );
				$html .= '</span><br />';
				$html .= '<span class="item">';
				$html .= '<span class="fn">' . $post_title . '</span>';
				
				if ( $post_thumbnail_id > 0 ) {
					$attachment_image = wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );
					$post_thumbnail = !empty( $attachment_image ) && isset( $attachment_image[0] ) && $attachment_image[0] != '' ? $attachment_image[0] : '';
					if ( $post_thumbnail != '' ) {
						$html .= '<br /><img src="' . $post_thumbnail . '" class="photo hreview-img"  alt="' . esc_attr( $post_title ) . '" />';
					}
				}
				
				$html .= '</span>';
				$html .= '</div>';
			}
		}
	}
	
	$html = apply_filters( 'geodir_reviewrating_filter_detail_page_rating_summary', $html, $avg_rating, $post_id ) ;
	
	return $html;
}

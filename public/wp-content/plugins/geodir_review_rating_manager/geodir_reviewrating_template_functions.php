<?php
function geodir_review_rating_general_options($tab_name){
	switch ($tab_name)
	{
		case 'geodir_multirating_options' :
			
			geodir_admin_fields( geodir_reviewrating_default_options() );?>
				<p class="submit">
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRREVIEWRATING_TEXTDOMAIN ); ?>" />
				<input type="hidden" name="subtab" value="geodir_multirating_options" id="last_tab" />
				</p>
			</div>
			<?php
			
		break;	
	}// end of switch
}


function geodir_reviewrating_wrap_comment_text($content,$comment=''){
	global $geodir_post_type;
	$all_postypes = geodir_get_posttypes();

	if(!in_array($geodir_post_type, $all_postypes))
		return $content;
		
	$like_unlike = '';
	
	if(!empty($comment) && !is_admin() && !$comment->comment_parent){
		if(get_option('geodir_reviewrating_enable_rating')):
			$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
			$comment_rating_overall = isset($comment_ratings->overall_rating) ? $comment_ratings->overall_rating : '';
			$overall_html = geodir_reviewrating_draw_overall_rating($comment_rating_overall);
			$ratings = @unserialize($comment_ratings->ratings);
		endif;
		
		if(!is_admin()){
			$ratings_html = geodir_reviewrating_draw_ratings($ratings);
			$comment_images = geodir_reviewrating_get_comment_images($comment->comment_ID);
		}	
		
		$images_show_hide = '';
		$comment_images_display = '';
		
		if(get_option('geodir_reviewrating_enable_images')):
					
			$total_images = 0;
			if(isset($comment_images->images) && $comment_images->images != ''){
				$total_images = explode(',',$comment_images->images);
			}
			// open lightbox on click
			$div_click = (int)get_option( 'geodir_disable_gb_modal' ) != 1 ? 'div.place-gallery' : 'div.overall-more-rating';
			$onclick = !empty($comment_images) && count($total_images)>0 ? 'onclick="javascript:jQuery(this).closest(\'.gdreview_section\').find(\''.$div_click.' a:first\').trigger(\'click\');"' : '';
			
			$images_show_hide = '<span class="showcommentimages" comment_id="'.$comment->comment_ID.'" '.$onclick.' ><i class="fa fa-camera"></i> <a href="javascript:void(0);">';
			
			if(empty($comment_images) || count($total_images) == 0)
				$images_show_hide .= 'No Photo';
			elseif(count($total_images) == 1)
				$images_show_hide .= '1 Photo';
			else
				$images_show_hide .= count($total_images).' Photos';
						
			$images_show_hide .= '</a></span>';
			
			$comment_images_display = $images_show_hide;
			
		endif;
		
		if(get_option('geodir_reviewrating_enable_rating')):
			$overallrating_html = '<div class="comment_overall"><span>'.$overall_html.'</span></div>';
			$rating_html = $ratings_html;
		endif;
		
		if(get_option('geodir_reviewrating_enable_review') && !is_admin()):
			$like_unlike = geodir_reviewrating_comments_like_unlike($comment->comment_ID, false);
		endif;
		
		ob_start(); ?>
			<div class="gdreview_section">
                <div class="clearfix">
                    <?php echo $overallrating_html; ?>
                    <div  style="float:left;"><?php echo $comment_images_display; ?></div>
                    <?php echo $like_unlike; ?>
                    <div class="overall-more-rating"><a href="javascript:void(0)"><?php _e('more', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></div>
                </div>
                <div class="comment_more_ratings clearfix">
					<?php echo 	$rating_html; ?>
                    <?php if(isset($comment_images->html)){ echo $comment_images->html;}?>
                </div>
          	</div>
            <div class="commenttext"><?php echo $content;?></div>
         
		<?php $content = ob_get_clean();
		
		return $content;
	}else
		return 	$content;
	
}

function geodir_reviewrating_show_post_ratings(){
	global $post,$geodir_post_type;
	
	$all_postypes = geodir_get_posttypes();

	if(!in_array($geodir_post_type, $all_postypes))
		return false;
	
	if(isset($_REQUEST['comment_sorting'])){?>
	
			<script type="text/javascript">
				
				jQuery(document).ready(function(){
					
					jQuery('#gd-tabs dl dd').removeClass('geodir-tab-active');
					
					jQuery('#gd-tabs dl dd').find('a').each(function(){
					
						if(jQuery(this).attr('data-tab') == '#reviews')
							jQuery(this).closest('dd').addClass('geodir-tab-active');
						
					});
					
				});
				
			</script> <?php		
	}
	global $post;
	if(!isset($post->id)){return;}
	$post_link = get_permalink($post->id);
	
	
	$comment_shorting_form_field_val = array(  'latest' => 'Latest',
											   'oldest' => 'Oldest', 
											   'low_rating' => 'Lowest Rating',
											   'high_rating' => 'Highest Rating'
											 );
	
	$comment_shorting_form_field_val = apply_filters( 'geodir_reviews_rating_comment_shorting', $comment_shorting_form_field_val );
	
   ?>
   <form name="comment_shorting_form" id="comment_sorting_form" method="get" action="<?php echo $post_link; ?>">
   <?php
	
	 	$query_variables = $_GET;
		
		$hidden_vars = '';
		if(!empty($query_variables)){
			
			foreach($query_variables as $key => $val){
				
				if( $key != 'comment_sorting')
					$hidden_vars .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />';
			}
		}
		
		echo $hidden_vars;
	?>
	
	<select name="comment_sorting" class="comment_sorting" onchange="jQuery(this).closest('#comment_sorting_form').submit()">
    
    <?php
	if(isset($comment_shorting_form_field_val) && !empty($comment_shorting_form_field_val))
			foreach($comment_shorting_form_field_val as $key => $value) {
	?>
	<option <?php if(isset($_REQUEST['comment_sorting']) && $_REQUEST['comment_sorting'] ==  $key){echo 'selected="selected"';} ?> value="<?php echo $key; ?>"><?php _e($value, GEODIRREVIEWRATING_TEXTDOMAIN);?></option>
            <?php
	       }
	?>
	</select>
  </form>
    <?php
	$ratings = array();
	$ratings = geodir_reviewrating_get_post_rating($post->ID);
	if (!$ratings['overall']) {
		$ratings = '';
	}
	if (!empty($ratings)) {
		$overall_html = geodir_reviewrating_draw_overall_rating($ratings['overall']); 
		$ratings_html = geodir_reviewrating_draw_ratings($ratings); 
		echo '<span>'.__('Overall Rating:', GEODIRREVIEWRATING_TEXTDOMAIN).$overall_html.'</span>'.$ratings_html;
	}
}


function geodir_reviewrating_style_dl($style_id = ''){

	global $wpdb;

	$select_styles = $wpdb->get_results("SELECT * FROM ".GEODIR_REVIEWRATING_STYLE_TABLE);
	
	if(empty($style_id)){
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_REVIEWRATING_STYLE_TABLE." WHERE field = 'is_default'"))
		{
			$style_id = $wpdb->get_var("SELECT id FROM ".GEODIR_REVIEWRATING_STYLE_TABLE." WHERE is_default='1'");
		}
		
	}
	
	 ?>
    <select id="geodir_rating_style_dl" name="geodir_rating_style_dl" style="width:298px;">
    	<option value="0"><?php _e('--Select multirating style--', GEODIRREVIEWRATING_TEXTDOMAIN);?></option>
		<?php
        foreach($select_styles as $select_style)
        {
						$checked = '';
            if($style_id == $select_style->id)
                $checked = 'selected="selected"';
				
            ?><option  <?php echo $checked ?>  value="<?php echo $select_style->id; ?>"><?php _e($select_style->name, GEODIRREVIEWRATING_TEXTDOMAIN); ?></option>
    	<?php } ?>
    </select>
<?php
}


// over all rating 
function geodir_reviewrating_draw_overall_rating($rating) {
	$overall_star = get_option('geodir_reviewrating_overall_count');
	$overall_star_lable = get_option('geodir_reviewrating_overall_rating_texts');
	$overall_star_onimg = get_option('geodir_reviewrating_overall_on_img');
	$overall_star_halfimg = get_option('geodir_reviewrating_overall_half_img');
	$star_offimg = get_option('geodir_reviewrating_overall_off_img');
	$star_color = get_option('geodir_reviewrating_overall_color');
	$overall_star_offimg_size = get_option('geodir_reviewrating_overall_off_img_width');
	$star_height = get_option('geodir_reviewrating_overall_off_img_height');
	$star_top = 0;
	$rtn_str = '';
	
	$floor_rating = floor($rating);
	$ceil_rating = 	ceil($rating);
	$overall_rating_star = '';
	$label = '';
	
	if ($overall_star > 0) {
		$rating_percent = ($rating / $overall_star) * 100;
	} else {
		$rating_percent = 0;
	}
	if ($overall_star_offimg_size) {
	} else {
		$overall_star_offimg_size = 23;
	}
	$star_width = $overall_star_offimg_size * $overall_star;
	
	$x = 1; 
	$rating_img = '<img src="'.$star_offimg.'" />';
	$rating_imgs = '';
	
	while ($x<=$overall_star) {
		$rating_imgs .= $rating_img;
		$x++;
	}
	
	/* fix rating star for safari */
	global $is_safari, $is_iphone, $ios, $is_chrome;
	$attach_style = ( $is_safari || $is_iphone || $ios || $is_chrome ) && $star_width > 0 ? 'width:' . $star_width . 'px;max-width:none' : '';	
	
	$overall_rating ='<div class="geodir-rating" style="' . $attach_style . '"><div class="gd_rating_show" data-average="'.$rating.'" ><div class="geodir_RatingAverage" style="width: '.$rating_percent.'%;background-color:'.$star_color.'"></div><div class="geodir_Star">'.$rating_imgs.'</div></div></div>';
		
	return $overall_rating;
}


function geodir_reviewrating_draw_ratings($ratings = ''){
	
	global $wpdb,$post;
	
	$post_id = isset($post->ID) ? $post->ID : '';
	$rating_ids = array(0);
	$format = '%d';
	if(!empty($ratings)){
		
		if(array_key_exists('overall',$ratings))
			unset($ratings['overall']);
		
		if(!empty($ratings))
			$rating_ids = array_keys($ratings);
		
		$rating_ids_length = count($rating_ids);
		if($rating_ids_length > 0){
			$rating_ids_format = array_fill(0, $rating_ids_length, '%d');
			$format = implode(',', $rating_ids_format);
		}
		
		$styles = $wpdb->get_results($wpdb->prepare("SELECT rt.id as id,
									rt.title as title,
									rt.post_type as post_type,
									rt.category as category,
									rt.check_text_rating_cond as check_text_rating_cond,	 
									rs.s_img_off  as s_img_off,
									rs.s_img_width as s_img_width,
									rs.s_img_height as s_img_height,
									rs.star_color as star_color,
									rs.star_lables as star_lables,
									rs.star_number as star_number	 
									FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." rt,".GEODIR_REVIEWRATING_STYLE_TABLE." rs
									WHERE rt.category_id= rs.id and rt.id IN($format) order by rt.id", $rating_ids));
		
		$rating_style = array();
									
		foreach($styles as $style){
			$rating_style[$style->id] = $style;
		}							
	}	
	$label = '';
	$rtn_str = '';
	$rating_html = '';
	$star_width_overall = get_option('geodir_reviewrating_overall_off_img_width');
	$star_width_overall = $star_width_overall > 0 ? $star_width_overall : 23;
	if(!empty($ratings)):
		$rating_html .= '<div class="gd_ratings_module_box">
			<h4>'.__('Individually rated for:', GEODIRREVIEWRATING_TEXTDOMAIN).'</h4>
			<div class="gd-rating-box-in clearfix">
				<div class="gd-rating-box-in-right">
					<div class="gd-rate-category clearfix">';
						
							foreach ( $ratings as $id => $rating ):
								if ( isset( $rating_style[$id] ) ) {
									$rating_style_category = isset( $rating_style[$id]->category ) ? $rating_style[$id]->category : '';
									$rating_cat = explode( ",", trim( ",", $rating_style_category ) );
									
									$post_cat = array();
									$post_categories = isset( $post->categories ) ? $post->categories : '';
									$post_cat  = explode( ",", trim( ",", $post_categories ) );
									$showing_cat = array_intersect( $rating_cat, $post_cat );
									
									if ( !empty( $showing_cat ) ) {
										$title = isset($rating_style[$id]->title) ? $rating_style[$id]->title : '';
										$max_star = isset($rating_style[$id]->star_number) ? $rating_style[$id]->star_number : '';
										$rating_style_star_lables = isset($rating_style[$id]->star_lables) ? $rating_style[$id]->star_lables : '';
										//$star_lable = explode(",",$rating_style_star_lables);
										$star_lable = geodir_reviewrating_star_lables_to_arr( $rating_style_star_lables );
										$star_width = isset($rating_style[$id]->s_img_width) ? $rating_style[$id]->s_img_width : '';
										$star_height = isset($rating_style[$id]->s_img_height) ? $rating_style[$id]->s_img_height : '';
										$star_color = isset($rating_style[$id]->star_color) ? $rating_style[$id]->star_color : '#ff9900';
										$star_offimg = isset($rating_style[$id]->s_img_off) ? $rating_style[$id]->s_img_off : '';
										
										$star_width = $star_width > 0 ? $star_width : $star_width_overall;
										$star_width_single = $star_width;
										
										if(is_array($rating)){
											$rating = $rating['r']/$rating['c'];
										}
																		
										//$rating_percent = ($rating/$max_star)*100;
										($max_star > 0) ? $rating_percent = ($rating/$max_star)*100 : $rating_percent = 0;
										$star_width = $star_width*$max_star;
										$star_top = 2*$star_height;
										
										$floor_rating = floor($rating); 
										$ceil_rating = ceil($rating);
										
										$rating_html .=	'<div class="clearfix gd-rate-cat-in"><span class="lable">'.__($title, GEODIRREVIEWRATING_TEXTDOMAIN).'</span>';
										
										$x = 1; 
										$rating_img = '<img src="'.$star_offimg.'" style="width:' . $star_width_single . 'px" />';
										$rating_imgs = '';
										
										while( $x <= $max_star ) {
											$rating_imgs .= $rating_img;
											$x++;
										}
										
										$rating_star_html ='<div class="geodir-rating" style="max-width:' . ( $star_width_single * $x ) . 'px"><div class="gd_rating_show" data-average="'.$rating.'" data-id="'.$post_id.'"><div class="geodir_RatingAverage" style="width: '.$rating_percent.'%;background-color:'.$star_color.'"></div><div class="geodir_Star">'.$rating_imgs.'</div></div></div>';
										$rating_html .= '<ul title="'. __($label, GEODIRREVIEWRATING_TEXTDOMAIN).'" class="rate-area-list">'.$rating_star_html.'</ul>';
										$rating_html .= '</div>';
									}//end if	
								}
							endforeach; 
						
					$rating_html .= '</div> 
				</div>
			</div>
		</div>';
		
	endif;
        
	return $rating_html;

}


function geodir_reviewrating_show_comments($geodir_reviews=array()){	
	
	echo '<ul>';
	
	if(!empty($geodir_reviews)){
		
		foreach($geodir_reviews as $comment){ 
					
						?>
            <li id="comment-<?php echo $comment->comment_ID; ?>"  >
                <div class="clearfix">
                    <div class="comment-info">
                        <div class="clearfix">
                            <form>
                           		<input name="chk-action[]" type="checkbox" value="<?php echo $comment->comment_ID; ?>" />
                            </form>
                            <div class="post-info">
                            	<h2 class="comment-post-title"><?php echo $comment->post_title; ?></h2>
															
															<?php
																$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
																$overall_html = geodir_reviewrating_draw_overall_rating($comment_ratings->overall_rating);
																echo 	'<span>'.$overall_html.'</span>';
															?>
															
                            	<p><?php echo wpautop(stripslashes($comment->comment_content)); ?></p>
                                
                                
                               
															 <div class="post-action clearfix">
																
															<?php
																if($comment->comment_approved == '0')
																{
																	?>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="approvecomment"><a href="javascript:void(0);"><?php _e('Approve', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<?php
																}elseif($comment->comment_approved == '1')
																{
																	?>
																	
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="unapprovecomment"><a href="javascript:void(0);"><?php _e('Unapprove', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<?php
																}elseif($comment->comment_approved == 'spam')
																{
																	?>
																	
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="unspamcomment"><a href="javascript:void(0);"><?php _e('Not Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<?php
																}elseif($comment->comment_approved == 'trash')
																{
																	?>
																	
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="untrashcomment"><a href="javascript:void(0);"><?php _e('Restore', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<span comment_id="<?php echo $comment->comment_ID; ?>" action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																	<?php
																}?>
                
<?php 
		
	$multirating_over_all = unserialize (($comment->ratings));
	
	   //if($multirating_over_all[1] != 0) { 
		 if(is_array($multirating_over_all) && array_filter($multirating_over_all)) { /* if all values not empty */  ?>
                                <span comment_id="<?php echo $comment->comment_ID; ?>" action="ratingshowhide" ><a href="javascript:void(0);"><?php _e('Show Multi Ratings', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																
	<?php }	?>
                                                                
                                                                					<?php 
																					
																					
																					
																					
																if($comment->comment_images != '')
																{
																?>
																<span comment_id="<?php echo $comment->comment_ID; ?>" action="commentimages" ><a href="javascript:void(0);"><?php _e('Show Images', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></span>
																<?php
																}
																?>
																
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="author-info">                                 
                        <div class="clearfix"> 
                            <div class="avtar-img">
                            	<?php echo get_avatar( $comment->user_id, 60); ?> 
                            </div>
                            <div class="author-name">                                      
                            	<?php echo $comment->comment_author; ?>
                            	<span><?php echo $comment->comment_author_email; ?></span>
                            	<span><?php if(isset($comment->omment_author_IP)){echo $comment->omment_author_IP;} ?></span>
                            </div>
                        </div>                                 
                        <span class="time">Submitted on:
                        <?php if(!function_exists('how_long_ago')){echo get_comment_date('M d, Y',$comment->comment_ID); } else { echo get_comment_time('M d, Y'); } ?>
                        </span>
                    </div>
									
                </div>
								<?php
									
									$ratings = @unserialize($comment_ratings->ratings);
									
									$ratings_html = geodir_reviewrating_draw_ratings($ratings); 
									
									echo 	'<div class="edit-form-comment-rating" style="display:none;">'.$ratings_html.'</div>';?>
									
									<div class="edit-form-comment-images" style="display:none;">
									
									<?php 
									if($comment->comment_images != '')
									{
										$comment_data = geodir_reviewrating_get_comment_images($comment->comment_id);
										echo $comment_data->html;
										
									}
								?>	
									</div>
            </li>
	<?php }
		
	}
	echo '</ul>';
}


function geodir_reviewrating_pagination($comments=array()){
	
	global $show_post, $paged;
	
	if($paged == 0)
	{
		$paged = 1;
	}
	
	if($show_post > 0 && $show_post < $comments && $paged > 0)
	{
		
		$total_pages_exp = explode('.', $comments/$show_post);
		
		$total_pages = $total_pages_exp[0];
		
		if(isset($total_pages_exp[1]) && $total_pages_exp[1] > 0)
			$total_pages = $total_pages_exp[0]+1;
		
		$previous_link = 1;
		if($paged > 1)
			$previous_link = $paged-1;
		
		
		$next_link = $paged+1;
		
		if($next_link > $total_pages)
			$next_link = $paged;
		?>
		
		<div id="gd_pagging">
		
        
			<spam><?php echo $comments;?> <?php _e('Items', GEODIRREVIEWRATING_TEXTDOMAIN);?></spam>
			
			<spam>
			<a class="<?php if($paged == 1){echo "disabled";}?>" title="<?php _e('Go to the first page', GEODIRREVIEWRATING_TEXTDOMAIN);?>" style="text-decoration:none;" href="<?php echo remove_query_arg( 'paged', get_permalink() );?>">&laquo;</a>
			</spam>
			
			<spam>
			<a class="<?php if($paged == 1){echo "disabled";}?>" title="<?php _e('Go to the previous page', GEODIRREVIEWRATING_TEXTDOMAIN);?>" style="text-decoration:none;" href="<?php echo add_query_arg( 'paged', $previous_link, get_permalink() );?>"> &lt;</a>
			</spam>
			
			<spam>
			<input type="text" value="<?php echo $paged; ?>" style="width:30px; text-align:center;" /> <?php _e('of', GEODIRREVIEWRATING_TEXTDOMAIN);?> <?php echo $total_pages;?>
			</spam>
			
			<spam>
			<a class="<?php if($paged == $total_pages){echo "disabled";}?>" title="<?php _e('Go to the next page', GEODIRREVIEWRATING_TEXTDOMAIN);?>" style="text-decoration:none;" href="<?php echo add_query_arg( 'paged', $next_link, get_permalink() );?>">&gt;</a>
			</spam>
			
			<spam>
			<a class="<?php if($paged == $total_pages){echo "disabled";}?>" title="<?php _e('Go to the last page', GEODIRREVIEWRATING_TEXTDOMAIN);?>" style="text-decoration:none;" href="<?php echo add_query_arg( 'paged', $total_pages, get_permalink() );?>">&raquo;</a>
			</spam>
            
            
			
		</div><?php
		
	}
	
}


function geodir_reviewrating_show_tab_head($seleted){

		?>
		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == ''){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=all');?>"><?php _e('All', GEODIRREVIEWRATING_TEXTDOMAIN);?><?php echo ' ('.geodir_reviewrating_get_comments_count(),')';?></a>
		</dd>
		
		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'pending'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=pending');?>">
		<?php _e('Pending', GEODIRREVIEWRATING_TEXTDOMAIN);?><?php echo ' ('.geodir_reviewrating_get_comments_count('pending'),')';?>
		</a>
		</dd>
		
		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'approved'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=approved');?>">
		<?php _e('Approve', GEODIRREVIEWRATING_TEXTDOMAIN);?><?php echo ' ('.geodir_reviewrating_get_comments_count('approved'),')';?>
		</a>
		</dd>
		
		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'spam'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=spam');?>">
		<?php _e('Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?><?php echo ' ('.geodir_reviewrating_get_comments_count('spam'),')';?>
		</a>
		</dd>
		
		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'trash'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=trash');?>">
		<?php _e('Trash', GEODIRREVIEWRATING_TEXTDOMAIN);?><?php echo ' ('.geodir_reviewrating_get_comments_count('trash'),')';?>
		</a>
		</dd>
	<?php    
}


/* CSS AND JAVASCRIPT FOR COMMENTS LIKE-UNLIKE */
function geodir_reviewrating_comments_like_unlike($comment_id, $echo = true){	
	ob_start();
	
	global $wpdb;
	$current_user = wp_get_current_user();
	$ip_address = $_SERVER["REMOTE_ADDR"];

	$has_liked = $wpdb->get_var($wpdb->prepare("SELECT count(like_id) FROM ".GEODIR_COMMENTS_REVIEWS_TABLE." WHERE comment_id=%d AND ip=%s",array($comment_id,$ip_address)));
	
	$get_total_likes = $wpdb->get_var($wpdb->prepare("SELECT wasthis_review FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id=%d",array($comment_id)));
	
	?>
	<div class="comments_review_likeunlike"><?php
			if($has_liked == 0){?>
			<span class="comments_likeunlike" id="like-<?php echo $comment_id;?>"><img src="<?php echo get_option('geodir_reviewrating_review_like_img'); ?>" /></span><?php }else {?><span class="comments_likeunlike" id="unlike-<?php echo $comment_id;?>"><img src="<?php echo get_option('geodir_reviewrating_review_unlike_img'); ?>" /></span><?php }   ?><span class="like_count"><?php echo number_format($get_total_likes);?> <?php _e('people like this.', GEODIRREVIEWRATING_TEXTDOMAIN);?></span>
	</div><?php
	
	$html = ob_get_clean();
	if($echo)
		echo $html; 
	else	
		return $html; 
}

function geodir_reviewrating_get_comment_images( $comment_id ) {
	global $wpdb;
	
	$comment_imges = $wpdb->get_var( $wpdb->prepare( "SELECT comment_images  FROM " . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . " WHERE comment_id = %d", array( $comment_id ) ) );

	if ( !empty( $comment_imges ) ) {
		$all_comment_images = array();
		
		$row_images = explode( ',', $comment_imges );
		if ( !empty( $row_images ) ) {
			foreach( $row_images as $row_image ) {
				$row_image_filetype = $row_image != '' ? wp_check_filetype($row_image) : NULL;
			
				if ( ( !empty( $row_image_filetype ) && isset( $row_image_filetype['ext'] ) && !empty( $row_image_filetype['ext'] ) ) ) {
					$all_comment_images[] = $row_image;
				}
			}
		}
		$comment_imges = !empty( $all_comment_images ) ? implode( ',', $all_comment_images ) : '';
		
		ob_start();
		?>
		<?php if( (int)get_option( 'geodir_disable_gb_modal' ) != 1 ) { /* disable gd modal */ ?>
		<script type="text/javascript">
		 jQuery(function() {
			jQuery('#place-gallery-<?php echo $comment_id; ?> a').lightBox({
				overlayOpacity : 0.5,
				imageLoading : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-ico-loading.gif';?>',
				imageBtnNext : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-btn-next.gif';?>',
				imageBtnPrev : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-btn-prev.gif';?>',
				imageBtnClose : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-btn-close.gif';?>',
				imageBlank : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-blank.gif';?>'
			});
		});
		</script>
		<?php } ?>			
		<div id="place-gallery-<?php echo $comment_id; ?>" class="place-gallery">
		<?php
		if ( is_admin() && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'reviews_fields' ) {
		?>
			<div class="clearfix reviews_rating_images_all_images"> 
				<ul class="reviews_rating_images_wrap_in_ul">
		<?php
		}
		
		foreach ( $all_comment_images as $comm_img ) {
			$comm_img_filetype = $comm_img != '' ? wp_check_filetype($comm_img) : NULL;
			
			if ( !( !empty( $comm_img_filetype ) && isset( $comm_img_filetype['ext'] ) && !empty( $comm_img_filetype['ext'] ) ) ) {
				continue;
			}
			
			$comm_img_title = geodir_reviewrating_get_image_name( $comm_img );
			
			if ( is_admin() && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'reviews_fields' ) {
			?>
				<li>
					<div class="reviews_rating_images_backend reviews_rating_images_frontend">
					
					<?php $delimgwpnonce = wp_create_nonce( 'del_img_'.$comment_id );?>
					<div class="thumb">
						<span class="review_rating_thumb_remove review_rating_thumb_remove_link_frontend review_rating_thumb_remove_link_backend">&nbsp;&nbsp;<input type="hidden" name="comment_id" value="<?php  echo $comment_id; ?>" /><input type="hidden" name="delimgwpnonce" value="<?php  echo $delimgwpnonce ; ?>" /></span>
					</div>
					<div class="review_rating_images"><a href="<?php echo $comm_img;?>"><img width="125" height="115" title="<?php echo $comm_img_title;?>" alt="<?php echo $comm_img_title;?>" src="<?php echo $comm_img;?>"></a></div>
					</div>
				</li>
			<?php } else { ?><a href="<?php echo $comm_img;?>"><img width="125" height="115" title="<?php echo $comm_img_title;?>" alt="<?php echo $comm_img_title;?>" src="<?php echo $comm_img;?>"></a><?php
			}
		}
		
		if ( is_admin() && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'reviews_fields' ) {
			echo '</ul></div>';
		}
		?>
		</div>
		<?php
		$comment_img_html =	ob_get_clean();
		
		return (object)array( 'images' => $comment_imges, 'html' => $comment_img_html );
	}						
}
function geodir_reviewrating_overall_settings_form(){
	$nonce = wp_create_nonce( 'geodir_overall_rating' );
?>	
<div class="gd-content-heading active">
	<h3><?php _e('Overall Rating Settings', GEODIRREVIEWRATING_TEXTDOMAIN); ?></h3>

	<input type="hidden" name="geodir_overall_rating_nonce" value="<?php echo $nonce; ?>" />
	<div id="form_div">
		<table class="form-table" id='tblSample1'> 
				<tr>
					<th><?php _e('Overall rating image.', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
					<td>
					<?php if(get_option('geodir_reviewrating_overall_off_img')){?>
					<img style="width:32px;"src="<?php echo get_option('geodir_reviewrating_overall_off_img'); ?>"  />
					<?php }?>
					</td>
					<td><input type="file" name="file_off" value="" /> </td>
				</tr>
		
        		<tr>
					<th scope="row"><?php _e('Overall rating color code', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
					<td>&nbsp;</td>
					<td>
							<input class="" type="color" name="overall_color" id="overall_color" value="<?php if(get_option('geodir_reviewrating_overall_color')!=''){echo get_option('geodir_reviewrating_overall_color'); }else{echo '#ff9900';}?>"  autocomplete="off" />
						</td>
				</tr>
                
				<tr>
					<th scope="row"><?php _e('Overall rating setting score', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
					<td>&nbsp;</td>
					<td>
							<input class="" type="text" name="overall_count" id="overall_count" value="<?php if(get_option('geodir_reviewrating_overall_count')!=''){echo get_option('geodir_reviewrating_overall_count'); }else{echo '5';}?>" onBlur="overall_the_text_box()" autocomplete="off" />
						</td>
				</tr>
		
				<tr>
					<th scope="row"><?php _e('Overall rating score text.', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
				<td><?php
								if(get_option('geodir_reviewrating_overall_rating_texts'))
										$getoratting = get_option('geodir_reviewrating_overall_rating_texts');
								
								if(isset($getoratting) && count($getoratting)>0)
								{
									//$impl = implode(',',$getoratting);
									$impl = geodir_reviewrating_serialize_star_lables( $getoratting );
								}
								
								?>
								<input type="hidden" id="hidden-text" value="<?php echo ( isset( $impl ) ? esc_attr( $impl ) : '' ); ?>" />
								<input type="hidden" id="hidden-serialized" value="<?php echo ( isset($impl) && is_serialized( $impl ) ? 1 : 0 ); ?>" />
								</td>
						<td id="overall_texts">
								<?php   
								$i=1;
								
								if(isset($getoratting) && count($getoratting)>0){
										foreach($getoratting as $value){
											$value = esc_attr( $value );
											$value = stripslashes_deep( $value );
											echo $i.' '.__('Score text', GEODIRREVIEWRATING_TEXTDOMAIN).' &nbsp;&nbsp;<input class="overall_rating_text" type="text" name="overall_rating_text[]" value="'.$value.'" style="width:247px;"/><br>';
											$i++;
										}
								}else{
										for($k=1;$k<=5;$k++){
											echo $k.' '.__('Score text', GEODIRREVIEWRATING_TEXTDOMAIN).'&nbsp;&nbsp;<input class="overall_rating_text" type="text" name="overall_rating_text[]" value="" style="width:247px;"><br>';
										}
								}?>
						</td>
				</tr>
		
		</table>
		<p class="submit" style="padding-left:10px; margin-bottom:10px;">
			<input id="geodir_reviewrating_overall_settings" type="button" class="button-primary" value="<?php _e('Update Settings', GEODIRREVIEWRATING_TEXTDOMAIN);?>"  />
			</p>
	</div>

</div>
<?php

}


function geodir_reviewrating_manage_rating_style_form(){
	global $wpdb;	
	$ajax_url = geodir_reviewrating_ajax_url();
	$category_select_info = array();
	
	if(isset($_REQUEST['cat_id'])){
		$category_select_info = geodir_reviewrating_get_style_by_id($_REQUEST['cat_id']); 
	}
		$nonce = wp_create_nonce( 'geodir_update_rating_styles' );	?>
	
	<div class="gd-content-heading active">
	
	<div id="li_tab2_div" class="multi_rating_div">
	
	<h3><?php _e('Manage Rating Styles', GEODIRREVIEWRATING_TEXTDOMAIN); ?></h3>
	<div id="form_div">
		<input type="hidden" name="geodir_update_rating_styles_nonce" value="<?php echo $nonce; ?>" />
		<input type="hidden" name="update_category" value="<?php if(isset($_REQUEST['cat_id'])){ echo $_REQUEST['cat_id'];}?>" />
		<input type="hidden" name="cat_id_save" value="save" />
		<table class="widefat">
				<tr id="multi_rating_category_tr">
					<th scope="row"><?php _e('Title', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
					<td><input class="regular-text" type="text" name="multi_rating_category" id="multi_rating_category" value="<?php if(isset($category_select_info->name)) echo $category_select_info->name; ?>" /></td>
				</tr>
		
				<tr>
						<th scope="row"><?php _e('Rating score (default 5)', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
						<td>
						<?php 
							$star_lables = isset( $category_select_info->star_lables ) ? $category_select_info->star_lables : '';
							$star_lables = esc_attr( $star_lables );
						?>
						<input type="hidden" id="hidden-stles-text" value="<?php if ( $star_lables != '' ) { echo $star_lables; } ?>" />
						<input type="hidden" id="hidden-stles-serialized" value="<?php echo ( isset($category_select_info->star_lables) && is_serialized( $category_select_info->star_lables ) ? 1 : 0 ); ?>" />
						<input class="regular-text" type="text" name="style_count" id="style_count" value="<?php if(isset($category_select_info->star_number) && $category_select_info->star_number!=""){echo $category_select_info->star_number; }else { echo '5';}; ?>" onBlur="style_the_text_box()" autocomplete="off" />
						</td>
				</tr>
	 
				<tr>
						<th scope="row"><?php _e('Rating text', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
						<td id="style_texts">
								<input type="hidden" name="update_category" value="<?php if(isset($_REQUEST['cat_id'])){echo $_REQUEST['cat_id'];} ?>" />
								<?php
								
								$values = isset($category_select_info->star_lables) ? $category_select_info->star_lables : '';
								$arr = array();
								if ( $values != '' ) {
									//$arr = explode(',',$values);
									$arr = geodir_reviewrating_star_lables_to_arr( $values );
								}

								if(count($arr)>0)
								{
										$i=1;
										foreach($arr as $value)
										{
											$value = esc_attr( $value );
											$value = stripslashes_deep( $value );
											echo $i.' '.__('Star Text', GEODIRREVIEWRATING_TEXTDOMAIN).' &nbsp;&nbsp;<input class="star_rating_text" type="text" name="star_rating_text[]" value="'.$value.'" style="width:247px;"/><br>';
											$i++;
										}
								}else{
								
										for($k=1;$k<=5;$k++)
										{
											echo $k.' '.__('Star Text', GEODIRREVIEWRATING_TEXTDOMAIN).' &nbsp;&nbsp;<input class="star_rating_text" type="text" name="star_rating_text[]" value="" style="width:247px;"/><br>';
										}
								
								}?>
						</td>
				</tr>
				<tr>
						<th><?php _e('Rating off image.&nbsp;', GEODIRREVIEWRATING_TEXTDOMAIN);?>
								
								<?php if(isset($category_select_info->s_img_off)){?>
								<img style="width:32px;" src="<?php  echo $category_select_info->s_img_off; ?>"  />
								<?php }?>
						</th>
						<td><input type="file" name="s_file_off" value="" /></td>
				</tr>
                
                <tr>
						<th scope="row"><?php _e('Rating color', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
						<td>
						<input class="regular-text" type="color" name="style_color" id="style_color" value="<?php if(isset($category_select_info->star_color) && $category_select_info->star_color!=""){echo $category_select_info->star_color; }else { echo '#ff9900';}; ?>"  autocomplete="off" />
						</td>
				</tr>
				
		</table>
		<p class="submit" style="padding-left:10px;">
			<input type="button" class="button-primary" value="<?php _e('Save Style', GEODIRREVIEWRATING_TEXTDOMAIN) ?>" name="submit_Categorie" id="manage_rating_submit" />
		</p>
	</div>     
			 
			<table class="widefat" >
					<thead>
							<tr>
									<th width="50"><strong><?php _e('S No.', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
									<th><strong><?php _e('Style Title', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
									<th><strong><?php _e('Rating Text', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
									<th><strong><?php _e('Image', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
									<th><strong><?php _e('Max Rating', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
									<th><strong><?php _e('Action', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
									<th>&nbsp;</th>
							</tr>
					</thead>
					
					<tbody>
			<?php 
		 
			$geodir_rating_styles = geodir_reviewrating_get_styles();
			
		if($geodir_rating_styles){
			$counter = 1;
			
			foreach($geodir_rating_styles as $geodir_category_reviews )
			{
				$nonce = wp_create_nonce( 'geodir_delete_rating_styles_'.$geodir_category_reviews->id );
				
				if($counter%2==0){$bgcolor='#FFF';}else{$bgcolor='#FCFCFC';}
				
				echo '<tr style="background-color:'.$bgcolor.';height:40px;">';
				echo '<td>'.$counter.'</td>';
				echo '<td>'.$geodir_category_reviews->name.'</td>';
				echo '<td>' . geodir_reviewrating_star_lables_to_str( $geodir_category_reviews->star_lables ) . '</td>';
				echo '<td><img style="background-color:'.$geodir_category_reviews->star_color.'" src="'.$geodir_category_reviews->s_img_off.'"/>
				</td>';
				echo '<td>'.$geodir_category_reviews->star_number.'</td>';
				
				$url = admin_url( 'admin.php' );
				$edit_action = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_rating_style','cat_id'=>$geodir_category_reviews->id), $url );
				$delete_action = add_query_arg( array('ajax_action'=>'delete_style','cat_id'=>$geodir_category_reviews->id, '_wpnonce'=>$nonce), $ajax_url );
				
				echo '<td><a href="'.$edit_action.'"> <img src="'.GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/edit.png" alt="'.__('Edit Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'" title="'.__('Edit Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'"/> </a>&nbsp;&nbsp;<a href="'.$delete_action.'" onclick="return delete_rating();"> <img src="'.GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/delete.png" alt="'.__('Delete Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'" title="'.__('Delete Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'"/> </a></td>';
				
				echo '<td>&nbsp;</td>';
				echo '</tr>';
				
				$counter++;			
			}
			
			}else{
			echo '<tr><td colspan="7" align="center">'.__('No Record Found.', GEODIRREVIEWRATING_TEXTDOMAIN).'</td></tr>';
		}
			?>
				</tbody>
			</table>
	</div>
	
	</div>
	<?php
}


function geodir_reviewrating_create_rating_form(){
	
	global $cat_display,$post_cat,$table_prefix, $wpdb;
	
	$ajax_url = geodir_reviewrating_ajax_url();
	
	if(isset($_REQUEST['rating_cat_id']) && $_REQUEST['rating_cat_id']!=''){
	
		$sqlquery = $wpdb->prepare("select * from ".GEODIR_REVIEWRATING_CATEGORY_TABLE." where id = %d",array($_REQUEST['rating_cat_id']));
		
		$qry_result = $wpdb->get_row($sqlquery);	
	}
	$nonce = wp_create_nonce( 'geodir_create_rating_nonce' );?>

	<div class="gd-content-heading active">
	
	<h3><?php _e('Create Ratings', GEODIRREVIEWRATING_TEXTDOMAIN);?></h3>
	<div id="form_div">
	
		<?php if(isset($_REQUEST['rating_cat_id']) && $_REQUEST['rating_cat_id']!=''){ ?>
			<input type="hidden" value="<?php echo $_REQUEST['rating_cat_id'];?>" name="rating_cat_id"  />
		<?php } ?>
		
		<input type="hidden" name="geodir_create_rating_nonce_field" value="<?php echo $nonce; ?>" />
			
		<table class="form-table" id='tblSample'>
			<tr>
				<th scope="row"><?php _e('Select multirating style', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
				<td><?php 
						$qry_result_category_id = isset($qry_result->category_id) ? $qry_result->category_id : '';
						echo geodir_reviewrating_style_dl($qry_result_category_id); 
						?>
					</td>
			</tr>
			
			<tr>
				<th scope="row"><?php _e('Rating title', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
				<td><input class="regular-text" type="text" name="rating_title" id="rating_title" value="<?php if(isset($qry_result->title)){ echo $qry_result->title;}?>" /></td>
			</tr>
			
			<tr>
				<th scope="row"><?php _e('Showing method', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
				<td>
					<?php
					$checked = '';
					$checked_false = '';
					if(isset($qry_result) && $qry_result->check_text_rating_cond=='0'):
						$checked_false = "checked";
					else:
						$checked = "checked";
					endif; 
					?>
					<input type="radio" name="show_star" id="select_star" value="1" <?php echo $checked; ?> />
					<?php _e('Show star', GEODIRREVIEWRATING_TEXTDOMAIN);?> 
					<input type="radio" name="show_star" id="select_text" value="0" <?php echo $checked_false;?> />
					<?php _e('Show dropdown', GEODIRREVIEWRATING_TEXTDOMAIN);?>
			 </td>
			</tr>
			
			<tr>
				<th scope="row"><?php _e('Select post type', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
				<td>
					<?php 
					$post_arr = array();
					if(isset($qry_result->post_type) && $qry_result->post_type!='')
						$post_arr = explode(',',$qry_result->post_type);
					
					$geodir_post_types = get_option( 'geodir_post_types' );
					$geodir_posttypes = geodir_get_posttypes();
					
					$i=1;
					foreach($geodir_posttypes as $p_type){
					
						$geodir_posttype_info = $geodir_post_types[$p_type];
						$listing_slug = $geodir_posttype_info['labels']['singular_name'];
						
						?>
						<div style="float:left; border:1px solid #CCCCCC; margin:0 5px;" >
							<input type="checkbox" name="post_type<?php echo $i; ?>" id="_<?php echo $i; ?>" value="<?php echo $p_type;?>" class="rating_checkboxs" <?php if(count($post_arr)>0){	if (in_array($p_type,$post_arr)) echo 'checked="checked"';}?> /><b>&nbsp;<?php echo ucwords($listing_slug);?>&nbsp;</b>
							
							<?php 
							$cat_display = 'select';
							$post_cat = isset($qry_result->category) ? $qry_result->category : '';
							?><br/>
							<select id="categories_type_<?php echo $i; ?>" name="categories_type_<?php echo $i; ?>[]"  multiple="multiple" style="display:<?php if(!in_array($p_type,$post_arr)) {echo 'none';} ?>;">
							<?php echo geodir_custom_taxonomy_walker($p_type.'category');?>
							</select>
							
						</div>
						<?php
						
					$i++;
					}		 
					?>
						<input type="hidden" value="<?php echo $i-=1; ?>" name="number_of_post" />     
				</td>
			</tr>
		</table>
		
		<p class="submit"><input type="button" id="create_rating_submit" class="button-primary" value="<?php _e('Save Changes', GEODIRREVIEWRATING_TEXTDOMAIN) ?>"  /></p>
	</div>
													
	<table class="widefat" style="width:100%;">
			<thead>
					<tr>
							<th width="50"><strong><?php _e('S No.', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							<th width="100"><strong><?php _e('Rating Style', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							<th width="100"><strong><?php _e('Rating Title', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							<th width="100"><strong><?php _e('Rating Image', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							<th width="125"><strong><?php _e('Post Types', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							<th width="125"><strong><?php _e('Categories', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							<th width="50"><strong><?php _e('Action', GEODIRREVIEWRATING_TEXTDOMAIN);?></strong></th>
							
					</tr>
			</thead>
		<tbody>
		<?php 
			$geodir_reviews = geodir_reviewrating_rating_categories();
			$check_multi_category_name = '';
			
		if(!empty($geodir_reviews)){
		
			$counter = 1;
			foreach($geodir_reviews as $wnw_review ){
			
				$nonce = wp_create_nonce( 'geodir_delete_rating_'.$wnw_review->id );
					
				if($counter%2==0){$bgcolor='#FFF';}else{$bgcolor='#FCFCFC';}
				$rating_style = geodir_reviewrating_get_style_by_id($wnw_review->category_id);
				echo '<tr style="background-color:'.$bgcolor.';height:40px;">';
					echo '<td>'.$counter.'</td>';
					echo '<td>'.$rating_style->name.'</td>';
					echo '<td>'.$wnw_review->title.'</td>';
					echo '<td><img style="width:16px;" src="'.$rating_style->s_img_off.'"/>';
						/*<img style="width:16px;" src="'.$rating_style->s_img_on.'"/>
						<img style="width:16px;" src="'.$rating_style->s_img_half.'"/>*/
					echo '</td>';
					echo '<td>';
							if($wnw_review->post_type != ''){
								
								$post_types = explode(',', $wnw_review->post_type);	
								
								if(!empty($post_types)){
								
									$j = 0;
									$comma = '';
									$get_post_types = '';
									foreach($post_types as $ptype){
										
										$post_typeinfo = get_post_type_object( $ptype );
										
										if($j != 0)
											$comma = ', ';
											
										$get_post_types .= $comma.ucwords($post_typeinfo->labels->singular_name) ;
										
									$j++;
									}
								}
								echo $get_post_types ;
							}
							
					echo '</td>';
					echo '<td>';
					
					if(isset($wnw_review->multi_category_name))
						$check_multi_category_name .= ','.$wnw_review->multi_category_name.',';
						
					$category = trim($wnw_review->category,",");
					
					$terms = explode(",",$category);
					$rating_term = '';
					
					foreach($terms as $termid)
						$rating_term .= $wpdb->get_var($wpdb->prepare("select name from ".$table_prefix."terms where term_id = %d",array($termid))).',';
					
					echo trim($rating_term,',');	
					
					echo '</td>';
				
					$url = admin_url( 'admin.php');
					$edit_action = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_create_rating','rating_cat_id'=>$wnw_review->id), $url );
					$delete_action = add_query_arg( array('ajax_action'=>'delete_rating_category','rating_cat_id'=>$wnw_review->id, '_wpnonce'=>$nonce), $ajax_url );
				
					echo '<td><a href="'.$edit_action.'">
					
					<img src="'.GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/edit.png" alt="'.__('Edit Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'" title="'.__('Edit Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'"/></a>&nbsp;&nbsp;<a href="'.$delete_action.'" onclick="return delete_rating();">
					
					<img src="'.GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/delete.png" alt="'.__('Delete Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'" title="'.__('Delete Rating', GEODIRREVIEWRATING_TEXTDOMAIN).'"/></a></td>';
					
				echo '</tr>';
				$counter++;			
			}
		}else{
			echo '<tr><td colspan="7" align="center">'.__('No Record Found.', GEODIRREVIEWRATING_TEXTDOMAIN).'</td></tr>';
		}
	?>
		</tbody>
	</table>		
	</div><?php
}

function geodir_reviewrating_manage_review_form(){
	
	$nonce = wp_create_nonce( 'geodir_update_review_nonce' );
	?>
	<div class="gd-content-heading active">
	<h3><?php _e('Manage Like / Unlike Icons', GEODIRREVIEWRATING_TEXTDOMAIN);?></h3>
		<div id="form_div">
			<input type="hidden" name="geodir_update_review_nonce_field" value="<?php echo $nonce; ?>" />
				<table class="form-table" id='tblSample1'> 
					
						<tr>
							<th><?php _e('Review like image', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
							<td><img style="width:32px;"src="<?php echo get_option('geodir_reviewrating_review_like_img'); ?>"  /></td>
							<td><input type="file" name="file_like" value="" /> </td>
						</tr>
						<tr>
							<th><?php _e('Review unlike image', GEODIRREVIEWRATING_TEXTDOMAIN);?></th>
							<td><img style="width:32px;" src="<?php echo get_option('geodir_reviewrating_review_unlike_img'); ?>"  /></td>
							<td><input type="file" name="file_unlike" value="" /> </td>
						</tr>
				 
				</table>
			<p class="submit" style="padding-left:10px;">
				<input id="geodir_review_settings" type="button" class="button-primary" value="<?php _e('Update Settings', GEODIRREVIEWRATING_TEXTDOMAIN);?>"  />
				</p>
		</div>
	
	</div>
	<?php
}

function geodir_reviewrating_rating_img_html(){
	
	$id = "comment_images";
	$svalue = isset($curImages) ? $curImages : '';
	$multiple = true; 
	$width = geodir_media_image_large_width(); 
	$height = geodir_media_image_large_height();?>
	 
	<div class="gd-form_row clearfix" id="<?php echo $id; ?>dropbox" align="center" style="border:1px solid #ccc; min-height:100px; height:auto; padding:10px;">
		<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>" />
			<div class="gd-plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>gd-plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $id; ?>plupload-upload-ui">
				<h4><?php _e('Drop files to upload', GEODIRREVIEWRATING_TEXTDOMAIN);?></h4><br/>
				<input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php _e('Select Files', GEODIRREVIEWRATING_TEXTDOMAIN); ?>" class="button" />
					<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id.'pluploadan'); ?>"></span>
					<?php if ($width && $height): ?>
							<span class="plupload-resize"></span>
							<span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
							<span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
					<?php endif; ?>
					<div class="filelist"></div>
		</div>
	
			<div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?> clearfix" id="<?php echo $id; ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
			</div>
			<span id="upload-msg" ><?php _e('Please drag &amp; drop the images to rearrange the order', GEODIRREVIEWRATING_TEXTDOMAIN);?></span>
			<span id="upload-error" style="display:none"></span>
	</div><?php
}

function geodir_reviewrating_rating_frm_html(){
	
	if($overall_star = get_option('geodir_reviewrating_overall_count')){
		$overall_star_lable = get_option('geodir_reviewrating_overall_rating_texts');
		$star_color = get_option('geodir_reviewrating_overall_color');
		$overall_star_offimg = get_option('geodir_reviewrating_overall_off_img');
		$star_width = get_option('geodir_reviewrating_overall_off_img_width');
		global $post;?>
		
		<div id="gd_ratings_module">
				<div id="rating_frm" style="margin-top:15px;">
					<div class="gd-rating-box-in clearfix">
						 <div class="gd-rating-box-in-left">
								 <div class="gd-rate-area clearfix">
										<span class="gd-ratehead"><?php printf(__('Rate this %s (overall):', GEODIRREVIEWRATING_TEXTDOMAIN),get_post_type_singular_label($post->post_type));?></span>
										<ul class="rate-area-list">
											 <?php for($star=1; $star <= $overall_star; $star++){ 
											 	$overall_star_text = isset( $overall_star_lable[$star-1] ) ? esc_attr( $overall_star_lable[$star-1] ) : '';
												$overall_star_text = stripslashes_deep( $overall_star_text );
												?>
												 <li star_rating="<?php echo $star;?>" star_lable="<?php echo $overall_star_text;?>" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;"  /></a></li>
											 <?php } ?>
										</ul>
                                        <style>ul.geodir-tabs-content li ul.rate-area-list li.active, ul.rate-area-list li.active{background-color:<?php echo $star_color;?>}</style>
																				
										<span class="gd-rank">&nbsp;</span>
										<input type="hidden" name="geodir_rating[overall]" value="0"  />
								</div> 
					<?php 
					$post_arr = (array)$post ;
					if(isset($post_arr[$post->post_type.'category']))
						$post_categories = explode(",",$post_arr[$post->post_type.'category']);
					else
						$post_categories = wp_get_post_categories( $post->ID, array( 'fields' => 'ids') );	
					
					$ratings = geodir_reviewrating_rating_categories();
											
								if($ratings){
								
									$rating_style_html = '';
									foreach($ratings as $rating){
										
										if (!in_array($post->post_type, explode(",", $rating->post_type))) {continue;}// if not for this post type then skip.
																				
										//$star_lable = explode(",",$rating->star_lables);
										$star_lable = geodir_reviewrating_star_lables_to_arr( $rating->star_lables );
																		
																		$rating_cat = explode(",",$rating->category);
																	
										$showing_cat = array_intersect($rating_cat,$post_categories);
																		
																		
											if(!empty($showing_cat)){
																
													if($rating->check_text_rating_cond){
														
														$rating_style_html .= '<div class="clearfix gd-rate-cat-in">';
														$rating_style_html .= '<span class="lable">'. __($rating->title, GEODIRREVIEWRATING_TEXTDOMAIN).'</span>';
														$rating_style_html .= '<ul class="rate-area-list rating-'.$rating->id.'">';
															for($star=1; $star <= $rating->star_number; $star++){ 
																$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
																$star_lable_text = stripslashes_deep( $star_lable_text );
																$rating_style_html .= '<li star_rating="'.$star.'" star_lable="'. $star_lable_text.'"  class="gd-multirating-star"><a><img src="'.$rating->s_img_off.'" style="width:'.$rating->s_img_width.'px;height:auto" /></a></li>';
															 } 
															$rating_style_html .= '</ul>';
															$rating_style_html .= ' <style>ul.geodir-tabs-content li ul.rating-'.$rating->id.' li.active, body ul.rating-'.$rating->id.' li.active{background-color:'.$rating->star_color.'}</style>';

															$rating_style_html .= '<span class="gd-rank">&nbsp;</span>';
															$rating_style_html .= '<input type="hidden" name="geodir_rating['.$rating->id.']" value="0"  />';
														$rating_style_html .= '</div>';
														
													}else{
														
														$rating_style_html .= '<div class="clearfix gd-rate-cat-in">';
														$rating_style_html .= '<span class="lable">'.__($rating->title).'</span>';
														$rating_style_html .= '<select name="geodir_rating['.$rating->id.']" > ';
														for($star=1; $star <= $rating->star_number; $star++){
															$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
															$star_lable_text = stripslashes_deep( $star_lable_text );
															$rating_style_html .= '<option value="'.$star.'">';
															$rating_style_html .= $star_lable_text;
															$rating_style_html .= '</option>	';
														} 
															$rating_style_html .= '</select>';
														$rating_style_html .= '</div>';
														
													}
												
												}
												
											}
											
											if($rating_style_html != ''){?>
												
												<div class="gd-rate-category clearfix">
														<span class="gd-ratehead"><?php printf(__('Rate this %s individually for:', GEODIRREVIEWRATING_TEXTDOMAIN),get_post_type_singular_label($post->post_type));?></span>
														<div>
														<?php echo $rating_style_html; ?>
														</div>
												 </div><?php
												
											}
									 } ?>    
								 
						 </div>
						 
				 </div>
				</div>
		</div><?php 
		
	} 
}


function geodir_reviewrating_comment_rating_box($comment) {
	
	if ($overall_star = get_option('geodir_reviewrating_overall_count')) {
		$post_type = get_post_type( $comment->comment_post_ID );
						
		$all_postypes = geodir_get_posttypes();

		if (!in_array($post_type, $all_postypes)) {
			return false;
		}
			
		$overall_star_lable = get_option('geodir_reviewrating_overall_rating_texts');
		$star_color = get_option('geodir_reviewrating_overall_color');
		$overall_star_offimg = get_option('geodir_reviewrating_overall_off_img');
		$star_width = get_option('geodir_reviewrating_overall_off_img_width');
		
		global $comment,$wpdb,$is_geodir_loop;
		
		$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
		if (!empty($comment_ratings)) {
		?>
		<div id="gd_ratings_module">
				<div id="rating_frm" style="margin-top:15px;">
					<lable for="rating"><?php _e('You can rate the area', GEODIRREVIEWRATING_TEXTDOMAIN);?></lable>
					<div class="gd-rating-box-in clearfix">
						 <div class="gd-rating-box-in-left">
								 <div class="gd-rate-area clearfix">
										<span class="gd-ratehead"><?php _e('Rate this area:', GEODIRREVIEWRATING_TEXTDOMAIN);?></span>
											<ul class="rate-area-list">
											<?php 
											for($star=1; $star <= $overall_star; $star++){ 
											
												if($comment_ratings->overall_rating && $star <= (int)$comment_ratings->overall_rating )
													$active = 'active';
												else
													$active = '';
												
												$overall_star_text = isset( $overall_star_lable[$star-1] ) ? esc_attr( $overall_star_lable[$star-1] ) : '';
												$overall_star_text = stripslashes_deep( $overall_star_text );
												?>
													<li star_rating="<?php echo $star;?>" star_lable="<?php echo $overall_star_text;?>"  class="gd-multirating-star <?php echo $active;?>"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px"  /></a></li>
											<?php } ?>
											</ul>
                                          <?php
										  $overall_star_text = isset( $overall_star_lable[$comment_ratings->overall_rating - 1] ) ? esc_attr( $overall_star_lable[$comment_ratings->overall_rating - 1] ) : '';
										  $overall_star_text = stripslashes_deep( $overall_star_text );
										  ?> 
                                        <style>#rating_frm ul.rate-area-list li.active{background-color:<?php echo $star_color;?>}</style>
											<span class="gd-rank"><?php echo ($comment_ratings->overall_rating) ? $overall_star_text : '&nbsp;';?></span>
											<input type="hidden" name="geodir_rating[overall]" value="<?php echo ($comment_ratings->overall_rating) ? $comment_ratings->overall_rating : '0'; ?>"  />
								</div> 
					<?php 
					
						$post_type = get_post_type( $comment->comment_post_ID );
						
						$post_categories = wp_get_post_terms( $comment->comment_post_ID, $post_type.'category', array( 'fields' => 'ids') );
					
					$ratings = geodir_reviewrating_rating_categories();	
					$old_ratings = @unserialize($comment_ratings->ratings);
					
					if($ratings):?>
					<div class="gd-rate-category clearfix">
									<span class="gd-ratehead"><?php _e('Rate this area:', GEODIRREVIEWRATING_TEXTDOMAIN);?></span>
									<div><?php
											
													foreach($ratings as $rating):
													
													//$star_lable = explode(",",$rating->star_lables);
													$star_lable = geodir_reviewrating_star_lables_to_arr( $rating->star_lables );
													
													$rating_cat = explode(",",$rating->category);
													
													$showing_cat = array_intersect($rating_cat,$post_categories);
													
													if(!empty($showing_cat)){
																	
														if($rating->check_text_rating_cond):
														?>
															<div class="clearfix gd-rate-cat-in">
																	<span class="lable"><?php _e($rating->title);?></span>
																	<ul class="rate-area-list rating-<?php echo $rating->id;?>">
																	<?php 
																	for($star=1; $star <= $rating->star_number; $star++){ 
																		
																		if($old_ratings[$rating->id] && $star <= (int)$old_ratings[$rating->id] )
																			$active = 'active';
																		else
																			$active = '';
																		
																		$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
																		$star_lable_text = stripslashes_deep( $star_lable_text );
																		?>
																		<li star_rating="<?php echo $star;?>" star_lable="<?php echo $star_lable_text;?>"  class="gd-multirating-star <?php echo $active;?>"><a><img src="<?php echo $rating->s_img_off;?>" style="width:<?php echo $rating->s_img_width;?>px" /></a></li>
																	<?php } ?>
																	</ul>
																	<?php
																	$star_lable_text = isset( $star_lable[$old_ratings[$rating->id] - 1] ) ? esc_attr( $star_lable[$old_ratings[$rating->id] - 1] ) : '';
																	$star_lable_text = stripslashes_deep( $star_lable_text );
																	?>
                                          <style>#rating_frm ul.rating-<?php echo $rating->id;?> li.active{background-color:<?php echo $rating->star_color;?>}</style>

																	<span class="gd-rank"><?php echo ($old_ratings[$rating->id]) ? $star_lable_text : '&nbsp;';?></span>
																	<input type="hidden" name="geodir_rating[<?php echo $rating->id;?>]" value="<?php echo ($old_ratings[$rating->id]) ? $old_ratings[$rating->id] : '0'; ?>"  />
															</div>
						<?php 
						else:
						?>
							<div class="clearfix gd-rate-cat-in">
									<span class="lable"><?php _e($rating->title, GEODIRREVIEWRATING_TEXTDOMAIN);?></span>
									<select name="geodir_rating[<?php echo $rating->id;?>]" > 
										<?php for($star=1; $star <= $rating->star_number; $star++){ 
											$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
											$star_lable_text = stripslashes_deep( $star_lable_text );
										?>
										<option value="<?php echo $star;?>" <?php if($old_ratings[$rating->id]) echo 'selected="selected"'; ?>  ><?php echo $star_lable_text;?></option>	
									<?php } ?>
									</select>
							</div>
						<?php 	
						endif;
						
					}// endif 
					endforeach;?>		 
					
									</div>
							 </div> 
								<?php endif;?>     
						 </div>	 
				 </div>
				</div>
		</div>
		<?php
		}
	} 
}


function geodir_reviewrating_manage_comments(){ 
	
	if(isset($_REQUEST['geodir_comment_search']))
		$geodir_commentsearch = $_REQUEST['geodir_comment_search'];
	
	if(isset($_REQUEST['geodir_comment_posttype']))
		$post_type = $_REQUEST['geodir_comment_posttype'];
	
	$status = $_REQUEST['subtab'];
	
	$orderby = 'comment_date_gmt';
	$order = 'DESC';
	if(isset($_REQUEST['geodir_comment_sort']) )
	{	
		if($_REQUEST['geodir_comment_sort'] == 'oldest')
		{
			$orderby = 'comment_date_gmt';
			$order = 'ASC';
		}
		elseif($_REQUEST['geodir_comment_sort'] == 'lowest_rating')
		{
			$orderby = 'overall_rating';
			$order = 'ASC';
		}
		elseif($_REQUEST['geodir_comment_sort'] == 'highest_rating')
		{
			$orderby = 'overall_rating';
			$order = 'DESC';
		}
		
	}
	
	
	if(isset($_REQUEST['paged']) && $_REQUEST['paged'] != '')
	{
		$paged = $_REQUEST['paged'];
	}
	else
	{
		$paged = 1;
	}
	
	if(get_option('comments_per_page'))
		$_REQUEST['show_post'] = get_option('comments_per_page');
	else
		$_REQUEST['show_post'] = '20';	
	
	$show_post = isset($_REQUEST['show_post']) ? $_REQUEST['show_post'] : '';
	
	$defaults = array(
		'paged' => $paged,
		'show_post' => $show_post,
		'orderby' => $orderby,
		'order' => $order,
		'post_type' => isset($post_type) ? $post_type : '',
		'comment_approved' => $status,
		'user_id' => '',
		'search' => isset($geodir_commentsearch) ? $geodir_commentsearch : '',
	);
	
	$comments = geodir_reviewrating_get_comments($defaults); 

?>
		
		<div style="float:right; margin-top:0px;">
			<?php echo $comment_pagings = geodir_reviewrating_pagination($comments['comment_count']);?>
		</div>
		<div style="clear:both;"></div>
		
	 <div class="gd-content-heading" style="display:block">
		 <h3>
				<div class="clearfix">				
					 <input name="checkedall" type="checkbox" value="" style="float:left; margin-top:8px;" />
		         	<div class="three-tab">
			 <ul class="clearfix">
					<?php
					if($_REQUEST['subtab'] == 'pending')
					{
						?>
						<li action="approvecomment"><a href="javascript:void(0);"><?php _e('Approve', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<?php
					}elseif($_REQUEST['subtab'] == 'approved')
					{
						?>
						<li action="unapprovecomment"><a href="javascript:void(0);"><?php _e('Unapprove', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<?php
					}elseif($_REQUEST['subtab'] == 'spam')
					{
						?>
						<li action="unspamcomment"><a href="javascript:void(0);"><?php _e('Not Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<?php
					}elseif($_REQUEST['subtab'] == 'trash')
					{
						?>
						<li action="untrashcomment"><a href="javascript:void(0);"><?php _e('Restore', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<?php
					}else
					{
						?>
						<li action="approvecomment"><a href="javascript:void(0);"><?php _e('Approve', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="unapprovecomment"><a href="javascript:void(0);"><?php _e('Unapprove', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<li action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', GEODIRREVIEWRATING_TEXTDOMAIN);?></a></li>
						<?php
					}
					
						?>
			 </ul>
		</div>
		<?php $nonce = wp_create_nonce( 'geodir_review_action_nonce' );?>
				
		<form>
			<input type="hidden" name="geodir_review_action_nonce_field" value="<?php echo $nonce; ?>" />
			 <input type="hidden" name="review_url" value="<?php echo admin_url( 'admin.php?page=geodirectory' );?>" />
				<input type="hidden" name="geodir_review_paged" value="<?php echo $paged;?>" />
				<input type="hidden" name="geodir_review_show_post" value="<?php echo $show_post;?>" />
				<input type="hidden" name="tab" value="reviews_fields" />
				<input type="hidden" name="subtab" value="<?php if(isset($_REQUEST['subtab'])){ echo $_REQUEST['subtab'];}?>" />
				<div class="gd-search">
				<input name="geodir_comment_search" value="<?php if(isset($_REQUEST['geodir_comment_search'])){ echo $_REQUEST['geodir_comment_search'];}?>" type="text" />
				</div>
				
				<div class="gd-search">
				
				<?php
				
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				$geodir_posttypes = geodir_get_posttypes();
				
				$selected = isset($_REQUEST['geodir_comment_posttype']) ? $_REQUEST['geodir_comment_posttype'] : '';
				?>
				<select name="geodir_comment_posttype" id="commentposttype">
				<option value = "" >Show all post types</option>
				<?php
				if(!empty($geodir_posttypes)):
				
					foreach( $geodir_posttypes as $p_type ):
					
						$geodir_posttype_info = $geodir_post_types[$p_type];
						
						$listing_slug = $geodir_posttype_info['labels']['singular_name'];
						
						echo '<option value="', $p_type, '"', $selected == $p_type ? ' selected="selected"' : '', '>', $listing_slug, '</option>';
						
					endforeach;
					
				endif;
				?>
				</select>
				</div>
				
				<div class="gd-sort">
				<label>Sort :</label>
				<select name="geodir_comment_sort">
					<option <?php if(isset($_REQUEST['geodir_comment_sort']) && $_REQUEST['geodir_comment_sort'] == 'newest'){echo 'selected="selected"';} ?> value="newest"><?php _e('Newest', GEODIRREVIEWRATING_TEXTDOMAIN);?></option>
					<option <?php if(isset($_REQUEST['geodir_comment_sort']) && $_REQUEST['geodir_comment_sort'] == 'oldest'){echo 'selected="selected"';} ?> value="oldest"><?php _e('Oldest', GEODIRREVIEWRATING_TEXTDOMAIN);?></option>
					<option <?php if(isset($_REQUEST['geodir_comment_sort']) && $_REQUEST['geodir_comment_sort'] == 'lowest_rating'){echo 'selected="selected"';} ?> value="lowest_rating"><?php _e('Lowest rating', GEODIRREVIEWRATING_TEXTDOMAIN);?></option>
					<option <?php if(isset($_REQUEST['geodir_comment_sort']) && $_REQUEST['geodir_comment_sort'] == 'highest_rating'){echo 'selected="selected"';} ?> value="highest_rating"><?php _e('Highest rating', GEODIRREVIEWRATING_TEXTDOMAIN);?></option>
				</select>
				</div>
				<div class="gd-search" style="padding-top:2px;">
				<input id="gdcomment-filter_button" class="button-primary" type="button" name="searchfilter" value="<?php _e('Filter', GEODIRREVIEWRATING_TEXTDOMAIN);?>" />	
				</div>
		
		</form>
		</div>
		</h3>
				 
		<div class="comment-listing">			 
		<?php 
		geodir_reviewrating_show_comments($comments['comments']);
		?>
		
		</div>
					 
	</div><?php 
}

function geodir_review_rating_reviews_rich_snippets() {
	if ( geodir_is_geodir_page() && geodir_is_page( 'detail' ) ) {
		$post_id = get_the_ID();
		
		$geodir_post_info = geodir_get_post_info( $post_id );
				
		if ( !empty( $geodir_post_info ) ) {
			$post_title = $geodir_post_info->post_title;
			$post_thumbnail = '';
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );
			
			$max_rating = get_option('geodir_reviewrating_overall_count');
			$average_rating = geodir_get_commentoverall_number( $post_id );
			$total_reviews = geodir_get_review_count_total( $post_id );
			
			if ( $total_reviews > 0 ) {
				if ( $post_thumbnail_id > 0 ) {
					$attachment_image = wp_get_attachment_image_src( $post_thumbnail_id, 'post-thumbnail' );
					$post_thumbnail = !empty( $attachment_image ) && isset( $attachment_image[0] ) && $attachment_image[0] != '' ? $attachment_image[0] : '';
				}
				
				$content = '';
				$content .= '<div style="height:0;width:0;margin:0;padding:0" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">';
				$content .= '<meta itemprop="itemreviewed" content="' . esc_attr( $post_title ) . '" />';
				if ( $post_thumbnail != '' ) {
					$content .= '<meta itemprop="photo" content="' . $post_thumbnail . '" />';
				}
				$content .= '<div itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
				$content .= '<meta itemprop="average" content="' . $average_rating . '" />';
				$content .= '<meta itemprop="best" content="' . $max_rating . '" />';
				$content .= '</div>';
				$content .= '<meta itemprop="count" content="' . $total_reviews . '" />';
				$content .= '</div>';
				
				echo $content;
			}
		}
	}
}

function geodir_reviewrating_get_image_name( $img_src ) {
	$comm_img_title = '';
	if ( $img_src != '' ) {
		$comm_img_str = basename( $img_src );
		if ( $comm_img_str != '' ) {
			if ( strpos( $comm_img_str, '.' ) !== false ) {
				$comm_img_arr = explode( '.', $comm_img_str );
				if ( !empty( $comm_img_arr ) ) {
					unset( $comm_img_arr[( count( $comm_img_arr ) - 1 )] );
					if ( !empty( $comm_img_arr ) ) {
						$comm_img_str = implode( ".", $comm_img_arr );
					}
				}
			}

			if ( strpos( $comm_img_str, '_' ) !== false ) {
				$comm_img_arr = explode( '_', $comm_img_str );
				if ( !empty( $comm_img_arr ) ) {
					unset( $comm_img_arr[( count( $comm_img_arr ) - 1 )] );
					if ( !empty( $comm_img_arr ) ) {
						$comm_img_str = implode( "_", $comm_img_arr );
					}
				}
			}
			
			$comm_img_title = preg_replace( '/[_-]/', ' ', $comm_img_str );
		}
	}
	return $comm_img_title;
}
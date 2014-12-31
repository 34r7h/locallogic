<?php
do_action( 'geodir_before_listing_listview' );
global $gridview_columns;
$grid_view_class = apply_filters( 'geodir_grid_view_widget_columns', $gridview_columns );
if ( isset( $_SESSION['gd_listing_view']) && $_SESSION['gd_listing_view'] != '' && !isset( $before_widget ) ){
	if($_SESSION['gd_listing_view']=='1'){$grid_view_class = '';}
	if($_SESSION['gd_listing_view']=='2'){$grid_view_class = 'gridview_onehalf';}
	if($_SESSION['gd_listing_view']=='3'){$grid_view_class = 'gridview_onethird ';}
	if($_SESSION['gd_listing_view']=='4'){$grid_view_class = 'gridview_onefourth';}
	if($_SESSION['gd_listing_view']=='5'){$grid_view_class = 'gridview_onefifth';}
}

global $listing_width;
?>
<ul class="geodir_category_list_view clearfix">
  <?php 
if ( !empty( $widget_events ) ) {
	do_action('geodir_before_listing_post_listview');
	foreach ( $widget_events as $widget_event ) {
		$post = $widget_event;
		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		
		$post_view_class = apply_filters( 'geodir_post_view_extra_class', '' );
		$post_view_article_class = apply_filters( 'geodir_post_view_article_extra_class', '' );
	?>
  <li id="post-<?php echo $post->ID;?>" class="clearfix <?php if($grid_view_class){ echo 'geodir-gridview '.$grid_view_class;}?> <?php if($post_view_class){echo $post_view_class;}?>" <?php if($listing_width) echo "style='width:{$listing_width}%;'"; // Width for widget listing ?>>
    <article class="geodir-category-listing <?php if($post_view_article_class){echo $post_view_article_class;}?>">
      <div class="geodir-post-img">
        <?php if($fimage = geodir_show_featured_image($post->ID, 'list-thumb', true, false, $post->featured_image)){ ?>
        <a  href="<?php the_permalink(); ?>">
        <?php  echo $fimage;?>
        </a>
        <?php do_action('geodir_before_badge_on_image', $post) ;
				if( $post->is_featured ) {
					echo geodir_show_badges_on_image( 'featured', $post, get_permalink() );
				}
				
				$geodir_days_new = (int)get_option('geodir_listing_new_days');
				
				if(round(abs(strtotime($post->post_date)-strtotime(date('Y-m-d')))/86400)<$geodir_days_new){
					echo geodir_show_badges_on_image('new' , $post,get_permalink());
				}
				
				do_action('geodir_after_badge_on_image', $post);
			} ?>
      </div>
      <div class="geodir-content">
        <?php do_action('geodir_before_listing_post_title', 'listview', $post ); ?>
        <header class="geodir-entry-header">
          <h3 class="geodir-entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
            <?php the_title(); ?>
            </a></h3>
        </header>
        <!-- .entry-header -->
        <?php do_action('geodir_after_listing_post_title', 'listview', $post );?>
        <?php /// Print Distance
			if ( isset( $_REQUEST['sgeo_lat'] ) && $_REQUEST['sgeo_lat'] != '' ) {
				$startPoint = array( 'latitude'	=> $_REQUEST['sgeo_lat'], 'longitude' => $_REQUEST['sgeo_lon']);	
				$endLat = $post->post_latitude; 
				$endLon = $post->post_longitude;
				$endPoint = array( 'latitude'	=> $endLat, 'longitude'	=> $endLon);
				$uom = get_option( 'geodir_search_dist_1' );
				$distance = geodir_calculateDistanceFromLatLong( $startPoint, $endPoint, $uom );
				?>
        <h3>
          <?php
				if (round((int)$distance,2) == 0){
					$uom = get_option('geodir_search_dist_2');
					$distance = geodir_calculateDistanceFromLatLong ($startPoint,$endPoint,$uom);
					echo round($distance).' '.__( $uom, GEODIRECTORY_TEXTDOMAIN ).'<br />';
				} else {
					echo round($distance,2).' '.__( $uom, GEODIRECTORY_TEXTDOMAIN ).'<br />';
				}
			?>
        </h3>
        <?php } ?>
        <?php do_action('geodir_before_listing_post_excerpt', $post); ?>
        <?php echo geodir_show_listing_info( 'listing' );?>
        <?php if(isset( $character_count ) && $character_count == '0' ) { } else { ?>
			<div class="geodir-entry-content">
			  <p>
				<?php
				if(isset( $character_count ) && $character_count != '' ) {
					echo geodir_max_excerpt( $character_count ); 
				} else { 
					the_excerpt(); 
				}
				?>
			  </p>
			</div>
			<?php } ?>
        <?php do_action('geodir_after_listing_post_excerpt', $post ); ?>
      </div>
      <!-- gd-content ends here-->
      <footer class="geodir-entry-meta">
        <div class="geodir-addinfo clearfix">
          <?php 
				$review_show = geodir_is_reviews_show('listview');
				if ($review_show) {
					
					global $preview;
					if (!$preview) {
						$post_avgratings = geodir_get_commentoverall_number($post->ID);
						
						do_action('geodir_before_review_rating_stars_on_listview' , $post_avgratings , $post->ID) ;
						echo geodir_get_rating_stars($post_avgratings,$post->ID);
						do_action('geodir_after_review_rating_stars_on_listview' , $post_avgratings , $post->ID);
					}
					?>
          <a href="<?php comments_link(); ?>" class="geodir-pcomments"><i class="fa fa-comments"></i>
          <?php geodir_comments_number( $post->rating_count ); ?>
          </a>
          <?php 
				}
				geodir_favourite_html($post->post_author,$post->ID);
				?>
        </div>
        <!-- geodir-addinfo ends here-->
      </footer>
      <!-- .entry-meta -->
    </article>
  </li>
  <?php }
		do_action('geodir_after_listing_post_listview');
		
	} else {
		echo '<li class="no-listing">'.__('No listings found which match your selection.',GEODIRECTORY_TEXTDOMAIN).'</li>'; 
	}
	?>
</ul>
<!-- geodir_category_list_view ends here-->
<div class="clear"></div>
<?php do_action('geodir_after_listing_listview'); ?>
<?php
class XooBbPress
{

	
	
	
	function __construct() 
	{
		
				
			
		
		
	}
	
	function count_user_posts_published( $userid, $post_type) 
	{
		global $wpdb;

		//$where = get_posts_by_author_sql( $post_type, true, $userid );
		
		$where = " WHERE post_author = '$userid'  AND post_type = '$post_type' and post_status= 'publish' ";	
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );	
		return apply_filters( 'get_usernumposts', $count, $userid );
	}
	
	function get_total_replies( $post_parent_id, $post_type) 
	{
		global $wpdb;

	
		$where = " WHERE post_parent = '$post_parent_id'  AND post_type = '$post_type' and post_status= 'publish' ";	
		$count = $wpdb->get_var( "SELECT COUNT(*) as total FROM $wpdb->posts $where" );	
		return apply_filters( 'get_usernumposts', $count, $userid );
	}
	
	/**
	 * My Topics in Profile
	 */
	function show_my_topics_in_profile($user_id, $type, $from=null, $howmany=null)
	{
		global $wpdb, $current_user, $xoouserultra; 
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH. 'wp-admin/includes/user.php' );
		require_once(ABSPATH.  'wp-includes/query.php' );		
		
		$args = array(
         'author' => $user_id,
         'post_type' => 'topic',  
		  'post_status' => 'publish',                  
        
        );
 
		
        $loop = new WP_Query( $args );	
		
		//echo $sql;
		//print_r($loop);
		
		$html="";
		
		while ( $loop->have_posts() ) : $loop->the_post();
		
		
	
			$permalink = get_permalink( $post->ID ); 			
			$comment_count = wp_count_comments($post->ID);
			
			$desc = $this->get_excerpt_by_id($loop->post->post_content, 20);
			
			$total_replies  = $this->get_total_replies( $post->ID, 'reply' );
			
			$html .='<li>';
			
			$html .= '<h1 class="uultra-topic-title"><a href="'.$permalink.'">'. $loop->post->post_title.'</a></h1>';
			
			$html .='<div class="uultra-my-topic-desc">';
			$html .='<p>'.$desc.'</p>';
			$html .='</div>';
			
			$html .='<div class="uultra-my-post-info-bar">';
			$html .='<p><span class="uultra-post-date"> <i class="fa fa-calendar "></i>'.date("m/d/Y",strtotime($loop->post->post_date)).'</span>  <span class="uultra-post-comments"><i class="fa fa-comment-o "></i>'.$comment_count->approved .'</span> <span class="uultra-post-see"> <i class="fa fa-eye "> <a href="'.$permalink.'">read more </a></i></span></p>';
			$html .='</div>';
			
			//info
			$html .='<div class="uultra-my-topic-info">';
			$html .='<ul class="topicstats">';
			$html .='<li class="stattopic"><i class="fa fa-eye "> Views</i><p>'.$total_replies.'</p></li>';
			$html .='<li  class="stattopic"><i class="fa fa-eye "> Voices</i><p>'.$total_replies.'</p></li>';
			$html .='<li  class="stattopic"><i class="fa  fa-comment-o "> Replies</i><p>'.$total_replies.'</p></li>';
			$html .='</ul>';
			$html .='</div>';
			
			$html .='</li>';
		
		endwhile;
		
		return $html;
	
	}
	
	function get_excerpt_by_id($the_excerpt,$excerpt_length)
	{
		$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
		$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	
		if(count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words, '… ');
			$the_excerpt = implode(' ', $words);
		endif;
	
		$the_excerpt = '<p>' . $the_excerpt . '</p>';
	
		return $the_excerpt;
	}
	
	
	
	

}
$key = "bbpress";
$this->{$key} = new XooBbPress();
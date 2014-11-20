<?php
class XooUserWall {

	public $allowed_extensions;

	function __construct() 
	{
				
		$this->ini_wall();
		
		add_action( 'wp_ajax_wall_post_message',  array( &$this, 'wall_post_message' ));
		add_action( 'wp_ajax_wall_post_reply',  array( &$this, 'wall_post_reply' ));
		add_action( 'wp_ajax_wall_reload_whole_messages',  array( &$this, 'uultra_get_latest_conversations' ));
		add_action( 'wp_ajax_nopriv_wall_reload_whole_messages',  array( &$this, 'uultra_get_latest_conversations' ));
		
		add_action( 'wp_ajax_reload_whole_replies',  array( &$this, 'reload_whole_replies' ));
		add_action( 'wp_ajax_nopriv_reload_whole_replies',  array( &$this, 'reload_whole_replies' ));
		
		add_action( 'wp_ajax_wall_delete_reply',  array( &$this, 'wall_delete_reply' ));
		
		//add_action( 'wp_ajax_reply_private_message',  array( $this, 'reply_private_message' ));
		//add_action( 'wp_ajax_message_change_status',  array( $this, 'message_change_status' ));
		//add_action( 'wp_ajax_message_delete',  array( $this, 'message_delete' ));
		
		

	}
	
	public function ini_wall()
	{
		global $wpdb;

			// Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_wall (
				`comment_id` bigint(20) NOT NULL auto_increment,				
				`comment_wall_user_id` bigint(20) NOT NULL ,
				`comment_posted_by_id` bigint(20) NOT NULL ,				
				`comment_message` text NOT NULL,				
				`comment_date` datetime NOT NULL,				
				PRIMARY KEY (`comment_id`)
			) COLLATE utf8_general_ci;';

		   $wpdb->query( $query );
		   
		   // Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_wall_replies (
				`reply_id` bigint(20) NOT NULL auto_increment,
				`reply_comment_id` bigint(20) NOT NULL ,
				`reply_commented_by_id` bigint(20) NOT NULL ,				
				`reply_message` text NOT NULL,				
				`reply_date` datetime NOT NULL,				
				PRIMARY KEY (`reply_id`)
			) COLLATE utf8_general_ci;';

		   $wpdb->query( $query );
		
		   $this->update_table();
		
	}
	
	function update_table()
	{
		global $wpdb;
		
			
		
	}
	
	
	public function wall_post_message()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$receiver_id =  sanitize_text_field($_POST["wall_profile_id"]);
		$uu_message =   sanitize_text_field($_POST["wall_message"]);
		
		//get receiver		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		
		//store in the db		
		if($receiver->ID >0)
		{
			
			$new_message = array(
						'comment_id'        => NULL,
						'comment_wall_user_id' => $receiver_id,
						'comment_posted_by_id'   => $logged_user_id,						
						'comment_message'   => $uu_message,					
						'comment_date'=> date('Y-m-d H:i:s')
						
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'usersultra_wall', $new_message, array( '%d', '%s', '%s', '%s',  '%s' ));
					
			
			//$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		die();
		
		
		
	}
	
	public function wall_post_reply()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$wall_comment_id =  sanitize_text_field($_POST["wall_comment_id"]);
		$wall_reply_message =   sanitize_text_field($_POST["wall_reply_message"]);
		
		//get receiver		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		//print_r($receiver );
		
		//store in the db		
		if($wall_comment_id >0)
		{
			
			$new_message = array(
						'reply_id'        => NULL,
						'reply_comment_id' => $wall_comment_id,
						'reply_commented_by_id'   => $logged_user_id,						
						'reply_message'   => $wall_reply_message,					
						'reply_date'=> date('Y-m-d H:i:s')
						
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'usersultra_wall_replies', $new_message, array( '%d', '%s', '%s', '%s',  '%s' ));
					
			
			//$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		die();
		
		
		
	}
	
	function message_authorization($wall_reply_id, $wall_comment_id)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		
	
	}
	
	//get one message posted on my wall	
	public function get_one_message($message_id) 
	{
		global $wpdb, $xoouserultra;
		
		$logged_user_id = get_current_user_id();
		

		$messages = $wpdb->get_results( 'SELECT *  FROM ' . $wpdb->prefix . 'usersultra_wall WHERE `comment_id` = ' . $message_id . ' AND  `comment_wall_user_id` = ' . $logged_user_id . ' ' );
		

		foreach ( $messages as $message )
		{
			return $message;
							
		}
		
	
	}
	
	public function wall_delete_reply()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_reply_id =   sanitize_text_field($_POST["wall_reply_id"]);
		$wall_comment_id =   sanitize_text_field($_POST["wall_comment_id"]);
		
		
		//store in the db		
		if($wall_reply_id >0 && $logged_user_id >0)
		{			
			
			$query = "DELETE FROM " . $wpdb->prefix ."usersultra_wall_replies WHERE reply_id = '$wall_reply_id'  ";						
		    $wpdb->query( $query );			
		
		}
		
		die();
		
		
		
	}
	
	
	
	public function reply_private_message()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
				
		
		$message_id =  sanitize_text_field($_POST["message_id"]);				
		$uu_message =   sanitize_text_field($_POST["uu_message"]);
		
		$message = $this->get_one($message_id, $logged_user_id);
		
		$uu_subject =   __("Reply: ", 'xoousers')." ".$message->subject;
		
		//check if reply equal to sender
		$receiver_id = $message->sender;
		
		if($receiver_id==$logged_user_id)
		{
			
			$receiver_id = $message->recipient;
		
		
		}
		
		//get receiver
		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		//store in the db
		
		if($receiver->ID >0)
		{
			
			$new_message = array(
						'id'        => NULL,
						'subject'   => $uu_subject,						
						'content'   => $uu_message,
						'sender'   => $logged_user_id,
						'recipient'   => $receiver_id,	
						'parent'   => $message->id,						
						'date'=> date('Y-m-d H:i:s'),
						'readed'   => 0,
						'deleted'   => 0
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'users_ultra_pm', $new_message, array( '%d', '%s', '%s', '%s',  '%s', '%s', '%s', '%s' , '%s' ));
					
			
			$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		echo "<div class='uupublic-ultra-success'>".__(" Reply sent ", 'xoousers')."</div>";
		die();
		
		
		
	}
	
	
	//this is called when leaving a reply
	public function reload_whole_replies()
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";	
		
		$comment_id = $_POST['comment_id'];
		
		$html = "";
		
		//get replies
		$drReplies = $this->get_convers_replies($comment_id );
		
		if ( !empty( $drReplies ) )
		{
		
			$html .='<ul>';
			
			foreach ( $drReplies as $reply )
			{
  
				  //replieds
				  $html .='<li class="uultra-commentHolder">';
				  
				  $when = $this->nicetime($reply->reply_date);
				  
				   //check if i can delete.. only if it's my own profile or if it's my own comment
				  $can_delete = $this->can_delete_reply($reply->reply_commented_by_id);	
				  
				  
				   if( $can_delete)
					{
						// i can delete this reply.		
						//$html .= ' <span class="uultraprofile-wall-edit">
						//<a href="#" id="uultra-wall-edit-message"> <i class="fa fa-edit fa-2"></i> </a> </span>';													  
						$html .= ' <span class="uultraprofile-wall-delete">
						<a href="#" class="uultra-wall-delete-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$comment_id.'"> <i class="fa fa-times fa-2"></i> </a> </span>';								 
					   
					}
							  
							  
				  
				 
				  //avatar =
				  $html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $reply->reply_commented_by_id, 30, 'avatar', $pic_boder_type, 'fixed').'</span>';
				 
								
				  $html .='<p><a>'. $xoouserultra->userpanel->get_display_name($reply->reply_commented_by_id).'</a>: <span></a>: <span>'.$reply->reply_message.'</span></p>
								
								<div class="uultra-commentFooter"> <span class="timeago" >'.$when.'</span>&nbsp;</div>
						   </li>';
		   
			 } //end for each
			
			
			   $html .='   </ul>';
	
			
		
		} // end if
		echo $html;
	   die();
	}
	
	//get conversation
	public function uultra_get_latest_conversations($user_id=null, $howmany=null)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";	
		
		$user_id = $_POST['user_id'];
		$howmany = $_POST['howmany'];
		
		$html ='';
		
		//write a message box		
		$html .= '<div class="uultra-publishContainer" style="clear:both">
    <textarea class="uultra-msgTextArea" id="uultra-txtMessage"   style="height: 49px; overflow: hidden; word-wrap: break-word; resize: none;" placeholder="'.__("What's new?","xoousers").'"></textarea>
    <input value="'.__("Share","xoousers").'" class="xoouserultra-button-wall" id="uultra-wall-post-commment"  data-id="'.$user_id.'" type="button">
</div>
';
		
		$html .= '<ul id="msgHolder">';
		
		//loop through messages		
		$drConversations = $this->get_conversation($user_id, $howmany);
		
		if ( !empty( $drConversations ) )
		{
			
			foreach ( $drConversations as $conversa )
			{
				
				$reply_msg_date = date("F j, Y, g:i a", strtotime($conversa->comment_date));				
				$user_id = $conversa->comment_posted_by_id;				
				$when_c = $this->nicetime($conversa->comment_date);	
		
				//main message
				$html .= '<li class="uultra-postHolder">';
				
				//avatar =
				$html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $user_id, 50, 'avatar', $pic_boder_type, 'fixed').'</span>';
								
				$html .='<p><a >'. $xoouserultra->userpanel->get_display_name($user_id).'</a>: <span>'.stripslashes($conversa->comment_message).'</span></p>
				
				<div class="uultra-postFooter">
					<span class="timeago">'.$when_c.'</span>&nbsp;<a class="linkComment" href="#">Comment</a>
					<div class="commentSection" id="uultra-replies-list-'.$conversa->comment_id.'">';

					//get replies
					$drReplies = $this->get_convers_replies($conversa->comment_id);
					
					if ( !empty( $drReplies ) )
					{
					
						$html .='<ul>';
						
						foreach ( $drReplies as $reply )
						{
							 $when = $this->nicetime($reply->reply_date);
							 							 
							 //check if i can delete.. only if it's my own profile or if it's my own comment
							 $can_delete = $this->can_delete_reply($reply->reply_commented_by_id);					 
			  
							  //replieds
							  $html .='<li class="uultra-commentHolder">';
							  
							  if( $can_delete)
							  {
								  // i can delete this reply.		
								 // $html .= ' <span class="uultraprofile-wall-edit">
								 // <a href="#" id="uultra-wall-edit-message"> <i class="fa fa-edit fa-2"></i> </a> </span>';													  
								  $html .= ' <span class="uultraprofile-wall-delete">
								  <a href="#" class="uultra-wall-delete-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-times fa-2"></i> </a> </span>';								 
								 
							  }
							  
							 
							  //avatar =
							  $html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $reply->reply_commented_by_id, 30, 'avatar', $pic_boder_type, 'fixed').'</span>';
							 
											
							  $html .='<p><a>'. $xoouserultra->userpanel->get_display_name($reply->reply_commented_by_id).'</a>: <span></a>: <span>'.stripslashes($reply->reply_message).'</span></p>
											
								<div class="uultra-commentFooter"> <span class="timeago" >'.$when.'</span>&nbsp;</div>
							 </li>';
					   
			             } //end for each
						
						
				           $html .='   </ul>';
				
						
					
					} // end if
			
				
           $html .= ' </div>';
		   $html .='   <div style="display: block" class="uultra-publishComment">
                    <textarea style="height: 19px; overflow: hidden; word-wrap: break-word; resize: none;" class="uultra-commentTextArea" placeholder="'.__("write a comment ...","xoousers").'" id="uultra-reply-to_comment-'.$conversa->comment_id.'"></textarea>
                    <input value="'.__("Comment","xoousers").'" id="uultra-wall-post-reply" class="xoouserultra-button-wall" type="button" data-comment-id="'.$conversa->comment_id.'">
                </div>
				
           
        </div>
    </li>';
	
			} //for each
	
	
			} //end if
	
	
	
	
		
		//end message holder
		$html .= '</ul>';
	   
	   echo $html;
	   die();
	}
	
	function can_delete_reply($reply_user_id)
	{
		$return = false;
		
		$user_id = get_current_user_id();
		
		if($user_id == $reply_user_id) //this is one of my own reply, then i can delete it.
		{
			$return = true;
		
		}
		
		if($user_id == "") //user not logged in
		{
			$return = false;
		
		}
		
		return $return;
	
	}
	
	
	function nicetime($date)
	{
		if(empty($date)) {
			return "No date provided";
				}
	   
		$periods         = array(__("second", 'xoousers'), __("minute", 'xoousers'), __("hour", 'xoousers'), __("day", 'xoousers'), __("week", 'xoousers'), __("month", 'xoousers'), __("year", 'xoousers'), __("decade", 'xoousers'));
		$lengths         = array("60","60","24","7","4.35","12","10");
	   
		$now             = time();
		$unix_date         = strtotime($date);
	   
		   // check validity of date
		if(empty($unix_date)) {   
			return "Bad date";
		}
	
		// is it future date or past date
		if($now > $unix_date) {   
			$difference     = $now - $unix_date;
			$tense         = "ago";
		   
		} else {
			$difference     = $unix_date - $now;
			$tense         = "from now";
		}
	   
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
	   
		$difference = round($difference);
	   
		if($difference != 1) {
			$periods[$j].= "s";
		}
	   
		return "$difference $periods[$j] {$tense}";
	}
	function get_conversation($user_id, $howmany)	
	{
		global $wpdb, $current_user, $xoouserultra;		
	
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_wall   ' ;		
	    $sql .= " WHERE comment_wall_user_id = '".$user_id."'  ORDER BY comment_id DESC LIMIT  ".$howmany." ";			
		$rows = $wpdb->get_results($sql);
		return  $rows;			
	
	}
	
	function get_convers_replies($conversation_id)	
	{
		global $wpdb, $current_user, $xoouserultra;		
	
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_wall_replies   ' ;		
	    $sql .= " WHERE reply_comment_id = '".$conversation_id."'  ORDER BY reply_id  ";	
		//echo $sql;		
		$rows = $wpdb->get_results($sql);
		return  $rows;			
	
	}
	
	
	
	
	
	
	
	
	public function message_delete()
	{
		
		global $wpdb,  $xoouserultra;
		
		$message_id = $_POST["message_id"];
		$logged_user_id = get_current_user_id();
		
			
		$sql = "UPDATE " . $wpdb->prefix . "users_ultra_pm SET `deleted` = '2' WHERE `id` = '$message_id' AND  `recipient` = '".$logged_user_id."' ";
		
		$wpdb->query($sql);
		
		echo "<div class='uupublic-ultra-success'>".__(" The message has been deleted. Please refresh your screen.", 'xoousers')."</div>";
		die();
	
	}
	
		
	
	
	

}
$key = "wall";
$this->{$key} = new XooUserWall();
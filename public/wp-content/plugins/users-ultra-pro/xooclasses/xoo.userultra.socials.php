<?php
class XooSocial
{
	
	
	var $mDateToday ;
	
	
	function __construct() 
	{
		
		
		$this->ini_module();
		$this->mDateToday =  date("Y-m-d"); 		
		$this->set_ajax();
		
		
	}
	
	public function set_ajax()
	{
		require_once( ABSPATH . "wp-includes/pluggable.php" );
		
		add_action( 'wp_ajax_send_friend_request',  array( $this, 'send_friend_request' ));
					
		add_action( 'wp_ajax_nopriv_send_friend_request',  array( $this, 'send_friend_request' ));			
		add_action( 'wp_ajax_like_item',  array( $this, 'like_item' ));					
			
		add_action( 'wp_ajax_nopriv_like_item',  array( $this, 'like_item' ));									
		add_action( 'wp_ajax_get_item_likes_amount_only',  array( $this, 'get_item_likes_amount_only' ));
		add_action( 'wp_ajax_friend_request_action',  array( $this, 'friend_request_action' ));
		add_action( 'wp_ajax_show_all_my_friends',  array( $this, 'show_all_my_friends' ));
		add_action( 'wp_ajax_show_friend_request',  array( $this, 'show_friend_request' ));
		add_action( 'wp_ajax_send_follow_request',  array( $this, 'send_follow_request' ));
		add_action( 'wp_ajax_nopriv_send_follow_request',  array( $this, 'send_follow_request' ));
		add_action( 'wp_ajax_send_unfollow_request',  array( $this, 'send_unfollow_request' ));
		
		
		
		
	}
	
	public function ini_module()
	{
		global $wpdb;
	
    	$query = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'usersultra_friends` (
		  `friend_id` int(11) NOT NULL AUTO_INCREMENT,
		  `friend_receiver_id` int(11) NOT NULL ,
		  `friend_sender_user_id` int(11) NOT NULL,
		  `friend_status` int(1) NOT NULL,		 		 
		  `friend_date` datetime NOT NULL,
		  PRIMARY KEY (`friend_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		
		$wpdb->query( $query );
		
		//likes
		$query = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'usersultra_likes` (
		  `like_id` int(11) NOT NULL AUTO_INCREMENT,
		  `like_liked_id` int(11) NOT NULL ,
		  `like_liker_user_id` int(11) NOT NULL,
		  `like_module` varchar(50) NOT NULL,	
		  `like_vote` int(2) NOT NULL,	 		 
		  `like_date` datetime NOT NULL,
		  PRIMARY KEY (`like_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		
		$wpdb->query( $query );
		
		//followers
		$query = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'usersultra_followers` (
		  `follower_id` int(11) NOT NULL AUTO_INCREMENT,
		  `follower_follow_user_id` int(11) NOT NULL ,
		  `follower_following_user_id` int(11) NOT NULL,
		  `follower_date` datetime NOT NULL,
		  PRIMARY KEY (`follower_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		
		$wpdb->query( $query );
		
	
	
		
	}
	
	public function send_unfollow_request()
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$receiver_id = $_POST["user_id"];
		
		if(isset($logged_user_id) && $receiver_id >0 && $logged_user_id >0 )
		{
			$sql = "DELETE FROM " . $wpdb->prefix . "usersultra_followers  WHERE follower_follow_user_id  = '$receiver_id' AND  follower_following_user_id = '$logged_user_id'	 ";
			
			$res = $wpdb->query( $sql );				
		
			echo __("You're not following this user anymore ", 'xoousers');
					
		}else{
					
			echo __("You have to be logged in to unfollow this user ", 'xoousers');
					
				
		}
		
		
		die();
		
	
	}
	
	//send follow request	
	public function send_follow_request()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$receiver_id = $_POST["user_id"];		
		
		if($logged_user_id!=$receiver_id)
		{
			//store in the db		
			if($this->check_if_following($receiver_id)) {
			
				if(isset($logged_user_id) && $receiver_id >0 && $logged_user_id >0 )
				{
					
					$data = array(
								'follower_id'        => NULL,
								'follower_follow_user_id'   =>   $receiver_id,		// this is the followed users				
								'follower_following_user_id'   => $logged_user_id, //this is who follow users								
								'follower_date'=> date('Y-m-d H:i:s')
								
								
							);
							
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'usersultra_followers', $data, array( '%d', '%s', '%s', '%s' ));
							
									
					echo __(" Following ", 'xoousers');
					
				}else{
					
					echo __("You have to be logged in to follow this user ", 'xoousers');
					
				
				}
			
			}else{
				
				echo __("Already Following", 'xoousers');
				
			
			}
		
		}else{
			
			echo __("You cannot follow yourself!", 'xoousers');
			
		}
			
		
		die();	
		
		
	}
	
	public function check_if_following($following_id) 
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$sql = "SELECT follower_id FROM " . $wpdb->prefix . "usersultra_followers  WHERE follower_follow_user_id  = '$following_id' AND  follower_following_user_id = '$logged_user_id'	 ";	 
		 
		 $res = $wpdb->get_results( $sql );
		 
		 if(empty($res))
		 {
			 return true; 
			
		 }else{
			 return false;
		
		}
		
		
	
	}
	
	public function send_friend_request()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$receiver_id = $_POST["user_id"];		
		$sender = get_user_by('id',$logged_user_id);
		
		$sender_id = $sender->ID;		
		$receiver = get_user_by('id',$receiver_id);		
		
		//store in the db
		
		if($this->check_if_sent($receiver_id )) {
		
			if(isset($logged_user_id) && $logged_user_id >0 && $logged_user_id >0 )
			{
				
				$data = array(
							'friend_id'        => NULL,
							'friend_receiver_id'   => $receiver_id,						
							'friend_sender_user_id'   => $sender_id,
							'friend_status'   => '0',
							'friend_date'=> date('Y-m-d H:i:s')
							
							
						);
						
				// insert into database
				$wpdb->insert( $wpdb->prefix . 'usersultra_friends', $data, array( '%d', '%s', '%s', '%s',  '%s' ));
						
				$xoouserultra->messaging->send_friend_request($receiver ,$sender);
				
				
				echo __(" Friend Request Sent ", 'xoousers');
				
			}else{
				
				echo __("You have to be logged in to send a friend request ", 'xoousers');
				
			
			}
		
		}else{
			
			echo __("Request Already Sent", 'xoousers');
			
		
		}
		
		
		die();	
		
		
	}
	
	public function check_if_sent($friend_id) 
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$sql = "SELECT friend_id FROM " . $wpdb->prefix . "usersultra_friends  WHERE friend_receiver_id  = '$friend_id' AND  friend_sender_user_id = '$logged_user_id'	 ";	 
		 
		 $res = $wpdb->get_results( $sql );
		 
		 if(empty($res))
		 {
			 return true; //first time
			
		 }else{
			 return false;
		
		}
		
		
	
	}
	
	public function like_item()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$item_id = $_POST["item_id"];		
		$module = $_POST["module"]; 
		$vote = $_POST["vote"];
		
		$already_voted = $this->check_already_voted($item_id, $module, $vote);
		
		if($already_voted==0)
		{
		
			//store in the db		
			if(isset($logged_user_id) && $logged_user_id >0)
			{
				//check if already liked it				
				$data = array(
							'like_id'        => NULL,
							'like_liked_id'   => $item_id,						
							'like_liker_user_id'   => $logged_user_id,
							'like_module'   => $module,
							'like_vote'   => $vote,
							'like_date'=> date('Y-m-d H:i:s')
							
							
						);
						
						// insert into database
				$wpdb->insert( $wpdb->prefix . 'usersultra_likes', $data, array( '%d', '%s', '%s', '%s', '%s',  '%s' ));
						
						
				
				echo __("Thanks ", 'xoousers');
				
			}else{
				
				echo __("Please login to rate ", 'xoousers');
				
			
			}
		}else{
			
			echo __("You've already liked it ", 'xoousers');
		
		
		}
		
		die();
		
		
		
	}
	
	/**
	 * My followers
	 */
	function show_my_followers($user_id, $from=null, $howmany=null)
	{
		global $wpdb, $current_user, $xoouserultra;
		
	
	     $sql = ' SELECT follower.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_followers follower  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->users ." u ON ( u.ID = follower.follower_following_user_id)";
		
		$sql .= " WHERE u.ID = follower.follower_following_user_id  AND  follower.follower_follow_user_id = '".$user_id."'  ORDER BY follower.follower_id DESC  ";	
		
		
		$res = $wpdb->get_results($sql );
		
		//echo $sql;
		
		
		$html="";
		
		foreach($res as $follower)
		{
			//get user thumb
			
			$thumb = $xoouserultra->userpanel->get_profile_bg_url($follower->ID);
			
			$permalink = $xoouserultra->userpanel->get_user_profile_permalink($follower->ID);
			
			$html .='<li>';
			
    		//check card background			
			$style_bg_thumb ="";
			$style_custom_color ="background-color:#24afb2;";
			
			if($thumb!="")
			{				
				$style_bg_thumb =  'background-image: url('.$thumb.');';			
			}
			
			
			
			$html .='<a style="'.$style_bg_thumb.$style_custom_color.'" tabindex="-1" href="'.$permalink.'" class="uultra-profile-card-bg-profile">  </a>';
			
			$html .= ' <div class="uultra-my-thumb">
               
                   '.$xoouserultra->userpanel->get_user_pic( $follower->ID, 70, 'avatar', $pic_boder_type, 'fixed').'             
               
               </div> ';
			
			$html .=' <p class="uultra-my-follow-name"><a href="'.$permalink.'">'. $xoouserultra->userpanel->get_display_name($follower->ID).'</a></p> ';
			
			$html .='<div class="uultra-desc-info">';
			$html .='<p>'.$this->get_follow_widget($follower->ID).'</p>';
			$html .='</div>';
			
					
			$html .='</li>';
		
		}
		
		return $html;
	
	}
	
	/**
	 * People i follow
	 */
	function show_my_following($user_id, $from=null, $howmany=null)
	{
		global $wpdb, $current_user, $xoouserultra;
		
	
	     $sql = ' SELECT follower.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_followers follower  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->users ." u ON ( u.ID = follower.follower_follow_user_id)";
		
		$sql .= " WHERE u.ID = follower.follower_follow_user_id  AND  follower.follower_following_user_id = '".$user_id."'  ORDER BY follower.follower_id DESC  ";	
		
		
		$res = $wpdb->get_results($sql );
		
		$html="";
		
		foreach($res as $follower)
		{
			//get user thumb
			
			$thumb = $xoouserultra->userpanel->get_profile_bg_url($follower->ID);
			
			$permalink = $xoouserultra->userpanel->get_user_profile_permalink($follower->ID);
			
			$html .='<li>';
			
    		//check card background
			
			//check card background			
			$style_bg_thumb ="";
			$style_custom_color ="background-color:#24afb2;";
			
			if($thumb!="")
			{				
				$style_bg_thumb =  'background-image: url('.$thumb.');';			
			}
			
			
			
			$html .='<a style="'.$style_bg_thumb.'" tabindex="-1" href="'.$permalink.'" class="uultra-profile-card-bg-profile">  </a>';
			
			$html .= ' <div class="uultra-my-thumb">
               
                   '.$xoouserultra->userpanel->get_user_pic( $follower->ID, 70, 'avatar', $pic_boder_type, 'fixed').'             
               
               </div> ';
			
			$html .=' <p class="uultra-my-follow-name"><a href="'.$permalink.'">'. $xoouserultra->userpanel->get_display_name($follower->ID).'</a></p> ';
			
			$html .='<div class="uultra-desc-info">';
			$html .='<p>'.$this->get_follow_widget($follower->ID).'</p>';
			$html .='</div>';
			
					
			$html .='</li>';
		
		}
		
		return $html;
	
	}
	
	/**
	 * People i follow
	 */
	function show_my_friends($user_id, $from=null, $howmany=null)
	{
		global $wpdb, $current_user, $xoouserultra;
		
	
	    $sql = ' SELECT friend.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_friends friend  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->prefix ."users u ON ( u.ID = friend.friend_sender_user_id)";
		
		$sql .= " WHERE u.ID = friend.friend_sender_user_id  AND  friend.friend_status = 1 AND friend.friend_receiver_id = '".$user_id."'  ORDER BY friend.friend_id  ";	
		
		
		$res = $wpdb->get_results($sql );
		
		$html="";
		
		foreach($res as $follower)
		{
			//get user thumb
			
			$thumb = $xoouserultra->userpanel->get_profile_bg_url($follower->ID);
			
			$permalink = $xoouserultra->userpanel->get_user_profile_permalink($follower->ID);
			
			$html .='<li>';
			
		
			//check card background			
			$style_bg_thumb ="";
			$style_custom_color ="background-color:#24afb2;";
			
			if($thumb!="")
			{				
				$style_bg_thumb =  'background-image: url('.$thumb.');';			
			}
			
			
			
			$html .='<a style="'.$style_bg_thumb.'" tabindex="-1" href="'.$permalink.'" class="uultra-profile-card-bg-profile">  </a>';
			
			$html .= ' <div class="uultra-my-thumb">
               
                   '.$xoouserultra->userpanel->get_user_pic( $follower->ID, 70, 'avatar', $pic_boder_type, 'fixed').'             
               
               </div> ';
			
			$html .=' <p class="uultra-my-follow-name"><a href="'.$permalink.'">'. $xoouserultra->userpanel->get_display_name($follower->ID).'</a></p> ';
			
			$html .='<div class="uultra-desc-info">';
			$html .='<p>'.$this->get_follow_widget($follower->ID).'</p>';
			$html .='</div>';
			
					
			$html .='</li>';
		
		}
		
		return $html;
	
	}
	
	function get_followers_total($user_id)	
	{
		global $wpdb, $current_user, $xoouserultra;
		
		$sql = ' SELECT count(*) as total
		  
		  FROM ' . $wpdb->prefix . 'usersultra_followers ' ;						
		$sql .= " WHERE follower_follow_user_id = '".$user_id."'  ";	
		
		//echo $sql;
			
		$res = $wpdb->get_results($sql);
		
		 if(!empty($res))
		 {
			  foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }
		  

		return  $total;
			
	
	}
	
	function get_following_total($user_id)	
	{
		global $wpdb, $current_user, $xoouserultra;
		
		$sql = ' SELECT count(*) as total
		  
		  FROM ' . $wpdb->prefix . 'usersultra_followers ' ;						
		$sql .= " WHERE 	follower_following_user_id = '".$user_id."'  ";	
		
		//echo $sql;
			
		$res = $wpdb->get_results($sql);
		
		 if(!empty($res))
		 {
			  foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }
		  

		return  $total;
			
	
	}
	
	function get_friends_total($user_id)	
	{
		global $wpdb, $current_user, $xoouserultra;
		
		$sql = ' SELECT count(*) as total
		  
		  FROM ' . $wpdb->prefix . 'usersultra_friends ' ;						
		$sql .= " WHERE 	friend_receiver_id = '".$user_id."' AND friend_status = '1' ";	
		
		//echo $sql;
			
		$res = $wpdb->get_results($sql);
		
		 if(!empty($res))
		 {
			  foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }
		  

		return  $total;
			
	
	}
	
	
	function get_followers_widget($user_id, $howmany)	
	{
		global $wpdb, $current_user, $xoouserultra;
		
		
	
		$sql = ' SELECT follower.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_followers follower  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->users ." u ON ( u.ID = follower.follower_following_user_id)";
		
		$sql .= " WHERE u.ID = follower.follower_following_user_id  AND  follower.follower_follow_user_id = '".$user_id."'  ORDER BY follower.follower_id DESC LIMIT  ".$howmany." ";	
		
		//echo $sql;
			
		$rows = $wpdb->get_results($sql);

		return  $rows;
			
	
	}
	
	function get_friends_widget($user_id, $howmany)	
	{
		global $wpdb, $current_user, $xoouserultra;
		
		
	
		$sql = ' SELECT friend.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_friends friend  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->prefix ."users u ON ( u.ID = friend.friend_receiver_id)";
		
		$sql .= " WHERE u.ID = friend.friend_receiver_id  AND  friend.friend_status = 1 AND friend.friend_receiver_id = '".$user_id."'  ORDER BY friend.friend_id DESC LIMIT  ".$howmany." ";	
		
		//echo $sql;
			
		$rows = $wpdb->get_results($sql);

				
		return  $rows;
			
			
			
	
	}
	
	public function get_friends($user_id)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$total = 0;		
		
		 $sql = "SELECT count(*) as total FROM " . $wpdb->prefix . "usersultra_friends  WHERE friend_receiver_id  = '$user_id' AND 	friend_status = 1 ";	 
		 
		 $res = $wpdb->get_results( $sql );
		 
		 if(!empty($res))
		 {
			  foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }
		  
		 if($total=="")$total = 0;	
		
		$html = "";
		
		$html.= "<div class='uultra-friend-request-box'>";
			
		$html .= '<a class="uultra-btn-friend-directory" id="uu-send-friend-request" href="#" user-id="'.$user_id.'" title="'.__('Send Friend Request','xoousers').'"><span><i class="fa fa fa-users fa-lg"></i> '.__('Be a Friend', 'xoousers').'</span> </a>';
		
		$html.= "</div>";
		
		return $html;
	
	
	}
	
	/*This get the follow or unfollow button*/
	
	public function get_follow_button($user_id)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$html = "";		
		
		//this is the current logged in user		
		$logged_user_id = get_current_user_id();	
		
		
		$html.= "<div class='uultra-follow-request-box'>";
		
		if($this->check_if_following($user_id)) 
		{
			$html .= '<a class="uultra-btn-follow-request"  href="#" user-id="'.$user_id.'" title="'.__('Follow User','xoousers').'"><span><i class="fa fa fa-plus fa-lg"></i> <i class="fa fa fa-user fa-lg"></i>  '.__('Follow', 'xoousers').'</span> </a>';
		
		}else{
			
			$html .= '<a class="uultra-btn-unfollow-request"  href="#" user-id="'.$user_id.'" title="'.__('Unfollow User','xoousers').'"><span><i class="fa fa fa-plus fa-lg"></i> <i class="fa fa fa-user fa-lg"></i>  '.__('Unfollow', 'xoousers').'</span> </a>';
		
		
		
		}
		
			
		
		
		$html.= "</div>";
		
		return $html;
	
	
	}
	
		
	function get_user_follower_sql($user_id)	
	{
		global $wpdb, $current_user, $xoouserultra;
		
		
	
		$sql = ' SELECT follower.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_followers follower  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->users ." u ON ( u.ID = follower.follower_following_user_id)";
		
		$sql .= " WHERE u.ID = follower.follower_following_user_id  AND  follower.follower_follow_user_id = '".$user_id."'  ORDER BY follower.follower_id DESC  ";	
		
		//echo $sql;
			
		$rows = $wpdb->get_results($sql);

		return  $rows;
			
	
	}
	
	public function get_follow_widget($user_id)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
				
		$html = "";
		
		$html.= "<div class='uultra-follow-request-box'>";
			
		$html .= '<a class="uultra-btn-follow" id="uu-follow-request" href="#" user-id="'.$user_id.'" title="'.__('Send Friend Request','xoousers').'"><span><i class="fa fa fa-eye fa-lg"></i>'.__('Follow', 'xoousers').'</span> </a>';
		
		$html.= "</div>";
		
		return $html;
	
	
	}
	
	public function friend_request_action()
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		if(isset($_POST["item_id"]))
		{
			$item_id = $_POST["item_id"];
		}
		
		if(isset($_POST["item_action"]))
		{
			$item_action = $_POST["item_action"];
		}
		
		$logged_user_id = get_current_user_id();
		
		if($item_action=='approve')
		{
			$sql = "UPDATE " . $wpdb->prefix . "usersultra_friends SET friend_status = 1  WHERE friend_id  = '$item_id' AND friend_receiver_id = '$logged_user_id'";
			
			//auto friend			
			
			$this->auto_friend($item_id);	
			
			
			$message = __('Request approved','xoousers'); 	
		}
		
		if($item_action=='block')
		{
			$sql = "UPDATE " . $wpdb->prefix . "usersultra_friends SET friend_status = 2  WHERE friend_id  = '$item_id' AND friend_receiver_id = '$logged_user_id'";
			
			$message = __('Request approved','xoousers'); 	
		}
		
		if($item_action=='deny')
		{
			$sql = "DELETE FROM " . $wpdb->prefix . "usersultra_friends  WHERE friend_id  = '$item_id' AND friend_receiver_id = '$logged_user_id'";
			
			$message = __('Request rejected','xoousers'); 	
		}	
		
		echo $message;	 
		 
		$res = $wpdb->query( $sql );
		
		
		
		die();
		
		
		
		
	
	}
	
	public function auto_friend($item_id)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		//get friend request		
		 $sql = "SELECT * FROM " . $wpdb->prefix . "usersultra_friends  WHERE friend_id  = '$item_id' ";		 
		 $res = $wpdb->get_results( $sql );
		 
		 if(!empty($res))
		 {
			 foreach ( $res as $friend )
			 {
				$friend_receiver_id =	$friend->friend_receiver_id;
				$friend_sender_user_id =	$friend->friend_sender_user_id;					
					
			 } 
		
		  }	 
		
		$data = array(
		
				'friend_id'        => NULL,
				'friend_receiver_id'   => $friend_sender_user_id,						
				'friend_sender_user_id'   => $friend_receiver_id,
				'friend_status'   => '1',
				'friend_date'=> date('Y-m-d H:i:s')							
							
	 	);
						
		// insert into database
		$wpdb->insert( $wpdb->prefix . 'usersultra_friends', $data, array( '%d', '%s', '%s', '%s',  '%s' ));
			
	
	
	}
	
	public function get_item_likes($item_id, $module)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$total = 0;
		
		
		 $sql = "SELECT SUM(like_vote) as total FROM " . $wpdb->prefix . "usersultra_likes  WHERE like_liked_id  = '$item_id' AND like_module = '$module'";	 
		 
		 $res = $wpdb->get_results( $sql );
		 
		 if(!empty($res))
		 {
			 foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }
		  
		 if($total=="")$total = 0;	
		
		$html = "";
		
		$html.= "<div class='likebox'>";
		$html.= "<p class='total_likes' id='uu-like-sore-id-".$item_id."'>". $total." ".__('Likes','xoousers')." </p>";	
		$html .= '<a class="uultra-btn-like" id="uu-like-item" href="#" item-id="'.$item_id.'" data-module="'.$module.'" data-vote="1" title="'.__('Like','xoousers').'"><span><i class="fa fa fa-thumbs-o-up fa-lg"></i></span> </a> <a class="uultra-btn-like" id="uu-like-item" href="" title="'.__('Dislike','xoousers').'" item-id="'.$item_id.'" data-module="'.$module.'" data-vote="-1"><span><i class="fa fa fa-thumbs-o-down fa-lg"></i></span> </a>';
		
		$html.= "</div>";
		
		return $html;
	
	
	}
	
	public function get_item_likes_amount_only()
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$item_id ="";
		$module = "";
		
		if(isset($_POST["item_id"]))
		{
			$item_id = $_POST["item_id"];
		}
		
		if(isset($_POST["module"]))
		{
			$module = $_POST["module"];
		}
		
		
		 $sql = "SELECT SUM(like_vote) as total FROM " . $wpdb->prefix . "usersultra_likes  WHERE like_liked_id  = '$item_id' AND like_module = '$module'";	 
		 
 
		 $res = $wpdb->get_results( $sql );
		 
		 if(!empty($res))
		 {
			  foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }		
		
				
		echo $total." ".__('Likes','xoousers');
		die();
	
	
	}
	
	public function check_already_voted($item_id, $module, $vote)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		 $sql = "SELECT count(*) as total FROM " . $wpdb->prefix . "usersultra_likes  WHERE like_liked_id  = '$item_id' AND like_module = '$module' AND like_liker_user_id = '$logged_user_id' AND like_vote = '$vote' ";	 
		 
 
		 $res = $wpdb->get_results( $sql );
		 
		 if(!empty($res))
		 {
			  foreach ( $res as $like )
			 {
				$total = $like->total;				
			 }
			 
		
		  }else{
			  
			  $total = 0;  
		  
		  }		
		
				
		return $total;	
	
	
	}
	
	function show_friend_request()
		
	{
		global $wpdb, $current_user, $xoouserultra;
		
		$user_id = get_current_user_id();		
	
		$sql = ' SELECT friend.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_friends friend  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->prefix ."users u ON ( u.ID = friend.friend_receiver_id)";
		
		$sql .= " WHERE u.ID = friend.friend_receiver_id  AND  friend.friend_status = 0 AND friend.friend_receiver_id = '".$user_id."'  ORDER BY friend.friend_id DESC ";	
		
		//echo $sql;
			
		$rows = $wpdb->get_results($sql);
		
		$html = " ";
		$html .='<div class="tablenav">	';	
		
		$html .= "<h3>".__('Latest Friend Requests','xoousers')."</h3> ";
	                        
                  
                    
		$html.='		</div>
	
				<table class="widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>						
                        <th class="manage-column" >'.__( 'Pic', 'xoousers' ).'</th>
						<th class="manage-column">'. __( 'Sender', 'xoousers' ).'</th>						
						<th class="manage-column" >'. __( 'Date', 'xoousers' ).'</th>
						<th class="manage-column" >'. __( 'Action', 'xoousers' ).'</th>
					</tr>
					</thead>
					<tbody>';
					
					
						
					foreach ( $rows as $msg )
					{
						$friend_sender_user_id = $msg->friend_sender_user_id;
						$request_id = $msg->friend_id;	
												
						$msg->sender = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$msg->friend_sender_user_id'" );
							
							
					$html .= '<tr >
												
                             <td >'. $xoouserultra->userpanel->get_user_pic( $friend_sender_user_id, 50, 'avatar', 'rounded', 'dynamic' ).'</td>
                             
							<td>'.$msg->sender.'</td>
							
							<td>'.$msg->friend_date.'</td>
							
							<td><a class="uultra-btn-denyapprove" id="uu-approvedeny-friend" href="#" item-id="'.$request_id.'" action-id="approve" title="'.__('Approve','xoousers').'"><span><i class="fa fa fa-thumbs-o-up fa-lg"></i></span> '.__('Accept', 'xoousers').' </a> <a class="uultra-btn-denyred" id="uu-approvedeny-friend" href="" title="'.__('Deny','xoousers').'" item-id="'.$request_id.'" action-id="deny"><span><i class="fa fa fa-thumbs-o-down fa-lg"></i></span> Deny </a></td>
						</tr>';
							
	
						}
						
						
						
					$html .='</tbody>
					
				</table>';
				
				echo $html;
				die();
			
	
	}
	
	function show_all_my_friends()		
	{
		global $wpdb, $current_user, $xoouserultra;
		
		$user_id = get_current_user_id();		
	
		$sql = ' SELECT friend.*, u.ID
		  
		  FROM ' . $wpdb->prefix . 'usersultra_friends friend  ' ;		
		$sql .= " RIGHT JOIN ".$wpdb->prefix ."users u ON ( u.ID = friend.friend_receiver_id)";
		
		$sql .= " WHERE u.ID = friend.friend_receiver_id  AND  friend.friend_status = 1 AND friend.friend_receiver_id = '".$user_id."'  ORDER BY friend.friend_id DESC ";	
		
		//echo $sql;
			
		$rows = $wpdb->get_results($sql);
		
		$html = " ";
		$html .='<div class="tablenav">	';	
		
		$html .= "<h3>".__('All My Friends','xoousers')."</h3> ";
	                        
                  
                    
		$html.='		</div>
	
				<table class="widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>						
                        <th class="manage-column" >'.__( 'Pic', 'xoousers' ).'</th>
						<th class="manage-column">'. __( 'Sender', 'xoousers' ).'</th>						
						<th class="manage-column" >'. __( 'Date', 'xoousers' ).'</th>
						<th class="manage-column" >'. __( 'Action', 'xoousers' ).'</th>
					</tr>
					</thead>
					<tbody>';
					
					
						
					foreach ( $rows as $msg )
					{
						$friend_sender_user_id = $msg->friend_sender_user_id;
						$request_id = $msg->friend_id;	
												
						$msg->sender = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$msg->friend_sender_user_id'" );
							
							
					$html .= '<tr>
							
                             <td>'. $xoouserultra->userpanel->get_user_pic( $friend_sender_user_id, 50, 'avatar', 'rounded', 'dynamic' ).'</td>
                             
							<td>'.$msg->sender.'</td>
							
							<td>'.$msg->friend_date.'</td>
							
							<td><a class="uultra-btn-denyred" id="uu-approvedeny-friend" href="" title="'.__('Block','xoousers').'" item-id="'.$request_id.'" action-id="block"><span><i class="fa fa fa-thumbs-o-down fa-lg"></i></span> '.__('Block', 'xoousers').' </a></td>
						</tr>';
							
	
						}
						
						
						
					$html .='</tbody>
					
				</table>';
				
				echo  $html;
				
				die();
			
	
	}


}
$key = "social";
$this->{$key} = new XooSocial();
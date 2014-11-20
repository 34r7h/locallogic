<?php
class XooBadge
{
	var $mBadgesArray =  array();
	
	
	
	
	
	function __construct() 
	{
		
		
			
		
		
	}

	
	
	/* Get if user is verified */
	function uultra_is_verified($user_id){
		$test = get_user_meta($user_id, 'uultra_verified', true);
		if ($test == 1) {
			return true;
		} else {
			$user = get_userdata($user_id);
			if ( $user->user_level >= 10 ) {
				return true;
			}
		}
		return false;
	}
	
	/* display badges beside name */
	function uultra_show_badges( $user_id, $inline=false, $disallowed=array() )
	{
		global $current_user, $wp, $xoouserultra;
		get_currentuserinfo();
		$output = null;
		
			
		/* the badges wrap */
		if ($inline == true){
		$output .= '<div class="uultra-badges inline">';
		} else {
		$output .= '<div class="uultra-badges">';
		}
		
		
				
		/* Custom defined badges */
		$badges = apply_filters('uultra_show_badges', $user_id);
		if ($badges != $user_id){
			$output .= $badges;
		}
		
		/* Add custom badges */
		if ( !in_array( 'custom', $disallowed ) ){
			$after_badges = apply_filters('uultra_after_all_badges', $user_id);
			if ( !is_numeric($after_badges)) {
				$output .= $after_badges;
			}
		}
		
		/* Online/offline status */
		if ($xoouserultra->get_option('modstate_online')) {
			
			if ($xoouserultra->userpanel->is_user_online($user_id)) 
			{
				$output .= $this->uultra_get_badge('online');
			} else {
				
				if ($xoouserultra->get_option('modstate_showoffline'))
				{
					$output .= $this->uultra_get_badge('offline');
				}
			}
		}
		
		$output .= '</div>';
		return $output;
	}
	
	/* show badge */
	function uultra_get_badge($badge,$user_id=null, $tooltip=null) 
	{
		global $xoouserultra;
		switch($badge){
		
				
			case 'online':
				return '<img class="uultra-profile-badge uultra-profile-badge-'.$badge.' uultra-hide-from-list" src="'.$xoouserultra->badges_url.'online.png" alt="" title="'.__('User is online :)','xoousers').'" />';
				break;
			
			case 'offline':
				return '<img class="uultra-profile-badge uultra-profile-badge-'.$badge.' uultra-hide-from-list" src="'.$xoouserultra->badges_url.'offline.png" alt="" title="'.__('User is offline :(','xoousers').'" />';
				break;
			
				
		}
	}

	
	

}
$key = "badge";
$this->{$key} = new XooBadge();
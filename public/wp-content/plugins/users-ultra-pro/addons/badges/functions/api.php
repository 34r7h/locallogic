<?php
class uultra_dg_api {

	function __construct() {

	}
	
	/* Loop medallions from badges folder */
	function loop_badges(){
		$res = '';
		$active = null;
		foreach (glob(uultra_dg_path . 'badges/*') as $filename) {
			if (uultra_badges_admin_edit()){
				$info = uultra_badges_admin_edit_info();
				if ( $info['badge_url'] == uultra_dg_url . 'badges/'. basename($filename) ){
					$active = 'active';
				} else {
					$active = null;
				}
			}
			$res .= '<span class="uultra-admin-badge '.$active.'"><img src="'.uultra_dg_url . 'badges/'. basename($filename) .'" alt="" /></span>';
		}
		return $res;
	}
	
	/* Remove fulfillment medallion */
	function remove_achievement_badge($btype, $bid){
		$badges = get_option('_uultra_badges');
		if (isset( $badges[$btype][$bid] ) ) {
			unset($badges[$btype][$bid]);
			update_option('_uultra_badges',$badges);
		}
	}
	
	/* Remove member medallion */
	function remove_user_badge($user_id, $badge_url) {
		$badges = get_user_meta($user_id, '_uultra_badges', true);
		if (is_array($badges)){
			foreach($badges as $k => $badge){
				if ( $badge['badge_url'] == $badge_url ) {
					unset($badges[$k]);
				}
			}
		}
		update_user_meta($user_id, '_uultra_badges', $badges);
	}
	
	/* Find manual medallions */
	function find_badges( $form ) {
		$result = null;
		unset($form['find-user-badges']);
		extract($form);
		if ($badge_user == '') {
			$result['error'] = __('You did not choose a member.','xoousers');
		}
		return $result;
	}
	
	/* Add new medallion */
	function new_badge( $form ) {
		$result = null;
		unset($form['insert-badge']);
		extract($form);
		
		// Manual medallion setting
		if ($badge_method == 'manual') {
			if (!$badge_url) {
				$result['error'] = __('You must choose a medallion first.','xoousers');
			} else if (!$badge_title) {
				$result['error'] = __('You must enter a title for the medallion.','xoousers');
			} else {
			
				if (isset($badge_to_users) && is_array($badge_to_users)){
					$this->give_badge_to_users( $form );
					echo '<div class="updated"><p><strong>'.__('Medallions have been assigned.','xoousers').'</strong></p></div>';
				}
			
			}
		}
		
		// Fulfillment
		if ($badge_method == 'achievement') {
			if (!$badge_url) {
				$result['error'] = __('You must choose a medallion first.','xoousers');
			} else if (!$badge_title) {
				$result['error'] = __('You must enter a title for the medallion.','xoousers');
			} else if (!$badge_achieved_num) {
				$result['error'] = __('You did not select any number for this fulfillment.','xoousers');
			} else {
			
				$this->achievement_badge( $form );
				echo '<div class="updated"><p><strong>'.__('Medallions have been assigned.','xoousers').'</strong></p></div>';
			
			}
		}
		
		// Points
		if ($badge_method == 'points') {
			if (!$badge_url) {
				$result['error'] = __('You must choose a medallion first.','xoousers');
			} else if (!$badge_title) {
				$result['error'] = __('You must enter a title for the medallion.','xoousers');
			} else if (!$badge_points_req) {
				$result['error'] = __('You must enter a required number of points for this medallion.','xoousers');
			} else {
			
			}
		}
		
		return $result;
	}
	
	/* Fulfillment medallion */
	function achievement_badge($form){
		extract($form);
		$achievements = get_option('_uultra_badges');
		if (uultra_badges_admin_edit() && $badge_achieved_num != $_GET['bid'] ) {
			unset( $achievements[$badge_achieved_type][$_GET['bid']] );
		}
		$achievements[$badge_achieved_type][$badge_achieved_num] = array(
			'badge_url' => $badge_url,
			'badge_title' => $badge_title
		);
		update_option('_uultra_badges', $achievements);
	}
	
	/* Give medallions to members */
	function give_badge_to_users($form) {
		extract($form);
		foreach($badge_to_users as $user_id) {
			$badges = get_user_meta($user_id, '_uultra_badges', true);
			
			// find if that medallion exists
			if (is_array($badges)){
				foreach($badges as $k => $badge){
					if ( $badge['badge_url'] == $badge_url ) {
						unset($badges[$k]);
					}
					if ( $badge['badge_title'] == $badge_title ) {
						unset($badges[$k]);
					}
				}
				update_user_meta($user_id, '_uultra_badges', true);
			}
			
			// add new medallion to member
			$badges[] = array(
				'badge_url' => $badge_url,
				'badge_title' => $badge_title
			);
			update_user_meta($user_id, '_uultra_badges', $badges);
		}
	}
	
	/* Get medallions of member */
	function get_badges($user_id){
		
		// member medallions
		$badges = (array)get_user_meta($user_id, '_uultra_badges', true);
		
		// fulfillments
		$badges_o = get_option('_uultra_badges');		
		if (isset($badges_o) && !empty($badges_o)) {
			foreach($badges_o as $t => $n) {
				foreach($n as $k=>$arr){
					if ($t == 'comments') {
						if ($this->count_user_comments($user_id) >= $k ) {
						
							if (!isset($badges_acquired[$t])) {
								$highest_num = $k;
								$badges_acquired[$t] = $arr;
							} else {
								if ($k > $highest_num) {
									$badges_acquired[$t] = $arr;
									$highest_num = $k;
								}
							}
							
						}
					} else if ( $this->count_user_posts($user_id,$t) >= $k ) {
					
							if (!isset($badges_acquired[$t])) {
								$highest_num = $k;
								$badges_acquired[$t] = $arr;
							} else {
								if ($k > $highest_num) {
									$badges_acquired[$t] = $arr;
									$highest_num = $k;
								}
							}
							
					}
				}
			}
		}
		
		// merge and display
		if (isset($badges_acquired)){
			$badges = array_merge( $badges, $badges_acquired );
		}
		
		// show them
		if (isset($badges) && is_array($badges)){
			return $badges;
		} else {
			return '';
		}
		
	}
	
	/* count member posts */
	function count_user_posts($user_id,$type ) {
		$args['author'] = $user_id;
		$args['post_type'] = $type;
		$args['posts_per_page'] = -1;
		$user_posts = new WP_Query($args);
		if (isset($user_posts->posts)){
		return count($user_posts->posts);
		} else {
		return 0;
		}
	}

	/* comment count */
	function count_user_comments($user_id) {
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		$count = $wpdb->get_var('
				 SELECT COUNT(comment_ID) 
				 FROM ' . $wpdb->comments. ' 
				 WHERE user_id = "' . $user_id . '"');
		return (int)$count;
	}
	
}

$uultra_badges = new uultra_dg_api();
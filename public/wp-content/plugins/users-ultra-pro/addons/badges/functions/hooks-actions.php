<?php

	/* add custom badges */
	add_filter('uultra_show_badges', 'uultra_badges_show');
	function uultra_badges_show($user_id){
		global $uultra_badges;
		$output = null;
		
		/* Find member badges (get_user_meta - _uultra_badges) */
		$get_badges = $uultra_badges->get_badges($user_id);
		if (is_array($get_badges)){
			foreach($get_badges as $key => $badge) {
				if (isset($badge['badge_url'])) {
					$sanitized = preg_replace('/\s*/', '', $badge['badge_title'] );
					$sanitized = strtolower($sanitized);
					$output .= '<img class="uultra-profile-badge uultra-profile-badge-'.$sanitized.'" src="'.$badge['badge_url'].'" alt="" title="'.$badge['badge_title'].'" />';
				}
			}
		}
		
		return $output;
	
	}
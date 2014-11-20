<?php

	/* get a global option */
	function uultra_dg_get_option( $option ) {
		$uultra_default_options = uultra_dg_default_options();
		$settings = get_option('uultra_dg');
		switch($option){
		
			default:
				if (isset($settings[$option])){
					return $settings[$option];
				} else {
					return $uultra_default_options[$option];
				}
				break;
	
		}
	}

	/* set a global option */
	function uultra_dg_set_option($option, $newvalue){
		$settings = get_option('uultra_dg');
		$settings[$option] = $newvalue;
		update_option('uultra_dg', $settings);
	}
	
	/* default options */
	function uultra_dg_default_options(){
		$array = array();
		return apply_filters('uultra_dg_default_options_array', $array);
	}
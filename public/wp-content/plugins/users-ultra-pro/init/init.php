<?php
define('uultraxoousers_pro_url','http://usersultra.com/');


function xoousers_load_textdomain() 
{     	   
	   $locale = apply_filters( 'plugin_locale', get_locale(), 'users-ultra-pro' );	   
       $mofile = xoousers_path . "languages/xoousers-$locale.mo";
			
		// Global + Frontend Locale
		load_textdomain( 'xoousers', $mofile );
		load_plugin_textdomain( 'xoousers', false, plugin_basename( dirname( __FILE__ ) ) . "/languages"  );
}

/* Load plugin text domain (localization) */
add_action('init', 'xoousers_load_textdomain');	
		
add_action('init', 'xoousers_output_buffer');
function xoousers_output_buffer() {
		ob_start();
}
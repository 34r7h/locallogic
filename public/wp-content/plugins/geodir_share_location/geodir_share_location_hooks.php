<?php

add_action('activated_plugin','geodir_share_location_plugin_activated') ;
add_action('wp_footer','geodir_localize_all_share_location_js_msg');
add_action( 'wp_enqueue_scripts', 'geodir_share_location_js_scripts');
add_action('wp_ajax_geodir_share_location', "geodir_share_location");
add_action( 'wp_ajax_nopriv_geodir_share_location', 'geodir_share_location' ); // call for not logged in ajax


//include_once('geodir_search_location.php');  

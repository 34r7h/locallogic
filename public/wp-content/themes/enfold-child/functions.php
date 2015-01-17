<?php

// Add Shortcode
function current_userFn() {

	// Code
    $current_user = wp_get_current_user();
    /**
     * @example Safe usage: $current_user = wp_get_current_user();
     * if ( !($current_user instanceof WP_User) )
     *     return;
     */
    echo 'Username: ' . $current_user->user_login . '<br />';
    echo 'User email: ' . $current_user->user_email . '<br />';
    // echo 'User first name: ' . $current_user->user_firstname . '<br />';
    // echo 'User last name: ' . $current_user->user_lastname . '<br />';
    // echo 'User display name: ' . $current_user->display_name . '<br />';
    // echo 'User ID: ' . $current_user->ID . '<br />';
}
add_shortcode( 'current_user', 'current_userFn' );
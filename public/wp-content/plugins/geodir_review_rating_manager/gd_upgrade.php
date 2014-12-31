<?php 
global $wpdb;

if(get_option(GEODIRREVIEWRATING_TEXTDOMAIN.'_db_version') != GEODIRREVIEWRATING_VERSION){	
	add_action( 'plugins_loaded', 'geodir_reviewratings_upgrade_all' );
	update_option( GEODIRREVIEWRATING_TEXTDOMAIN.'_db_version',  GEODIRREVIEWRATING_VERSION );
}

function geodir_reviewratings_upgrade_all(){
	geodir_reviewrating_db_install();
	geodir_reviewratings_upgrade_1_0_7();
}

function geodir_reviewratings_upgrade_1_0_7(){
	global $wpdb,$plugin_prefix;
	
}



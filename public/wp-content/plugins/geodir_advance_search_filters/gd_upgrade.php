<?php 
global $wpdb;

if(get_option(GEODIRADVANCESEARCH_TEXTDOMAIN.'_db_version') != GEODIRADVANCESEARCH_VERSION){
	//ini_set("display_errors", "1");error_reporting(E_ALL); // for error checking
	
	add_action( 'plugins_loaded', 'geodiradvancesearch_upgrade_all' );
	update_option( GEODIRADVANCESEARCH_TEXTDOMAIN.'_db_version',  GEODIRADVANCESEARCH_VERSION );
}

function geodiradvancesearch_upgrade_all(){
	geodir_advance_search_field();
	geodiradvancesearch_upgrade_1_0_7();
}

function geodiradvancesearch_upgrade_1_0_7(){
	global $wpdb,$plugin_prefix;
	
}



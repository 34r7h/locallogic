<?php 
global $wpdb;

if(get_option(GEODIREVENTS_TEXTDOMAIN.'_db_version') != GDEVENTS_VERSION){
	//ini_set("display_errors", "1");error_reporting(E_ALL); // for error checking
	//echo GDEVENTS_VERSION; exit;
	add_action( 'plugins_loaded', 'geodirevents_upgrade_all' );
	update_option( GEODIREVENTS_TEXTDOMAIN.'_db_version',  GDEVENTS_VERSION );
}

function geodirevents_upgrade_all(){
	geodir_event_tables_install();
	geodirevents_upgrade_1_1_0();
}

function geodirevents_upgrade_1_1_0(){
	global $wpdb,$plugin_prefix;
	
$wpdb->query("ALTER TABLE ".$wpdb->prefix."geodir_gd_event_detail MODIFY `post_title` text NULL");	
	
}



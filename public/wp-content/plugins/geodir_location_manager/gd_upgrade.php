<?php 
global $wpdb;

if(get_option(GEODIRLOCATION_TEXTDOMAIN.'_db_version') != GEODIRLOCATION_VERSION){
	//ini_set("display_errors", "1");error_reporting(E_ALL); // for error checking
	
	add_action( 'plugins_loaded', 'geolocation_upgrade_all' );
	update_option( GEODIRLOCATION_TEXTDOMAIN.'_db_version',  GEODIRLOCATION_VERSION );
}

function geolocation_upgrade_all(){
	geodir_location_activation_script();
	geolocation_upgrade_1_1_4();
}

function geolocation_upgrade_1_1_4(){
	
}



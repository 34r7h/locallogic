<?php 
global $wpdb;

if(get_option(GEODIRLOCATION_TEXTDOMAIN.'_db_version') != GEODIRLOCATION_VERSION){
	//ini_set("display_errors", "1");error_reporting(E_ALL); // for error checking
	
	add_action( 'plugins_loaded', 'geolocation_upgrade_all' );
	update_option( GEODIRLOCATION_TEXTDOMAIN.'_db_version',  GEODIRLOCATION_VERSION );
}

function geolocation_upgrade_all(){
	//geolocation_upgrade_1_0_9(); // removed as not needed any more
	geodir_location_activation_script();
	geolocation_upgrade_1_1_0();
}
function geolocation_upgrade_1_0_9(){
	global $wpdb;
	geodir_add_column_if_not_exist( POST_LOCATION_TABLE, "country_ISO2", "varchar(254) NOT NULL" );
	$wpdb->query("UPDATE ".POST_LOCATION_TABLE." pl JOIN ".GEODIR_COUNTRIES_TABLE." c ON pl.country=c.Country SET pl.country_ISO2 = c.ISO2 WHERE pl.country=c.Country AND c.ISO2!=''");
}

function geolocation_upgrade_1_1_0(){
	global $wpdb;
}


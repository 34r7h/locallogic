<?php 
global $wpdb;

if(get_option(GEODIRPAYMENT_TEXTDOMAIN.'_db_version') != GEODIRPAYMENT_VERSION){	
	add_action( 'plugins_loaded', 'geodir_payments_upgrade_all' );
	update_option( GEODIRPAYMENT_TEXTDOMAIN.'_db_version',  GEODIRPAYMENT_VERSION );
}

function geodir_payments_upgrade_all(){
	geodir_payment_activation_script();
	geodir_payments_upgrade_1_0_9();
}

function geodir_payments_upgrade_1_0_9(){
	global $wpdb,$plugin_prefix;
	
}



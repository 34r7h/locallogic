<?php
class XooCredit
{
	var $mBadgesArray =  array();
	
	
	
	
	
	function __construct() 
	{		
		
		$this->ini_module();
		
		
		
	}
	
		public function ini_module()
	{
		global $wpdb;

			// Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_credits (
				`credit_id` bigint(20) NOT NULL auto_increment,
				`credit_user_id` bigint(20) NOT NULL,
				`credit_date` datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
				`credit_amount` decimal(11,2) NOT NULL,				
				`credit_transaction_type` varchar(100)  NULL,
								
				PRIMARY KEY (`credit_id`)
			) COLLATE utf8_general_ci;';
	
	
		$wpdb->query( $query );
		
		//$this->update_package_table();
		
		
	}

	
	
	

}
$key = "credit";
$this->{$key} = new XooCredit();
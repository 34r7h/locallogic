<?php
class XooGroup
{

	
	
	
	function __construct() 
	{
		
		$this->ini_module();
		
		
	}
	
	public function ini_module()
	{
		global $wpdb;

		// Create table
		$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_groups (
				`group_id` bigint(20) NOT NULL auto_increment,
				`group_creator_id` int(11) NOT NULL ,
				`group_admin_id` int(11) NOT NULL ,
				`group_name` varchar(60) NOT NULL,					
				`group_desc` text NOT NULL,			
				PRIMARY KEY (`group_id`)
			) COLLATE utf8_general_ci;';
	
		$wpdb->query( $query );

	}

	
	

}
$key = "group";
$this->{$key} = new XooGroup();
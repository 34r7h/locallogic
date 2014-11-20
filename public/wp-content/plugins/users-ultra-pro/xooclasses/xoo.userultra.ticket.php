<?php
class XooUserTicket {

	public $ticket_status;

	function __construct() 
	{
				
		$this->ini_modules();
		$this->set_ticket_status();	

	}
	
	public function ini_modules()
	{
		global $wpdb;

			// Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_tickets (
				`ticket_id` bigint(20) NOT NULL auto_increment,				
				`ticket_created_by_user_id` bigint(20) NOT NULL ,
				`ticket_assigned_to_user_id` bigint(20) NOT NULL ,
				`ticket_replied_by_id` bigint(20) NOT NULL ,
				`ticket_category_id` bigint(20) NOT NULL ,	
				`ticket_department_id` bigint(20) NOT NULL ,
				`ticket_status` int(10) NOT NULL ,	
				`ticket_subject` varchar(100) NOT NULL,			
				`ticket_message` text NOT NULL,				
				`ticket_date` datetime NOT NULL,				
				PRIMARY KEY (`ticket_id`)
			) COLLATE utf8_general_ci;';

		   $wpdb->query( $query );
		   
		   // Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_ticket_replies (
				`reply_id` bigint(20) NOT NULL auto_increment,
				`reply_ticket_id` bigint(20) NOT NULL ,
				`reply_replied_by_id` bigint(20) NOT NULL ,				
				`reply_message` text NOT NULL,				
				`reply_date` datetime NOT NULL,				
				PRIMARY KEY (`reply_id`)
			) COLLATE utf8_general_ci;';
		   $wpdb->query( $query );
		   
		    
		   $query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_ticket_departments (
				`department_id` bigint(20) NOT NULL auto_increment,
				`department_name` varchar(100) NOT NULL	,				
				PRIMARY KEY (`department_id`)
			) COLLATE utf8_general_ci;';
			
		   $wpdb->query( $query );
		   
		   $query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_ticket_categories (
				`category_id` bigint(20) NOT NULL auto_increment,
				`category_name` varchar(100) NOT NULL,					
				PRIMARY KEY (`category_id`)
			) COLLATE utf8_general_ci;';
			
		   $wpdb->query( $query );
		
		   $this->update_table();
		
	}
	
	function update_table()
	{
		global $wpdb;
		
			
		
	}
	
	function set_ticket_status()
	{
				
			
		
	}
	
	public function get_categories () 
	{
		global $wpdb, $xoouserultra;
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_ticket_categories ORDER BY category_name ASC  ' ;
		$res = $wpdb->get_results($sql);
		return $res ;	
	
	}
	
	public function get_departments () 
	{
		global $wpdb, $xoouserultra;
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_ticket_departments ORDER BY department_name ASC  ' ;
		$res = $wpdb->get_results($sql);
		return $res ;	
	
	}
	
	function get_categories_box($selected=null)
	{
		global $wpdb;
		
		$rows = $this->get_categories();
		$html = ' <select name="uultra-ticket-cat" id="ultra-ticket-cat" class="uultra-user-drop-ticketings">';
       
     
		$html .= '  <option value="">'.__('--- Select Category','xoousers').'</option>';
		foreach ( $rows as $item )
		{
			$html .= '  <option value="'.$item->category_id.'">'.$item->category_name.'</option>';		
		
		}
		
		$html .= ' </select>';		
		return $html ;
	}
	
	function get_departments_box($selected=null)
	{
		global $wpdb;
		
		$rows = $this->get_departments();
		$html = ' <select name="ultra-ticket-depto" id="ultra-ticket-depto" class="uultra-user-drop-ticketings">';
       
     
		$html .= '  <option value="">'.__('--- Select Department','xoousers').'</option>';
		foreach ( $rows as $item )
		{
			$html .= '  <option value="'.$item->department_id.'">'.$item->department_name.'</option>';		
		
		}
		
		$html .= ' </select>';		
		return $html ;
	}
	
	
	
	
		
	
	

}
$key = "ticket";
$this->{$key} = new XooUserTicket();
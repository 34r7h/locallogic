<?php
class XooDefender 
{
	
	//-------- modules: (user,photo,gallery)
	var $mDateToday ;


	function __construct() 
	{
		$this->ini_module();
		$this->mDateToday =  date("Y-m-d"); 
		
		
	}
	
	public function ini_module()
	{
		global $wpdb;
	
    	  $query = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'usersultra_ip_defender` (
		  `ip_id` int(11) NOT NULL AUTO_INCREMENT,
		  `ip_number` varchar(100) NOT NULL,		  
		  PRIMARY KEY (`ip_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		
		$wpdb->query( $query );
		
		
		
	}
	
	
	
	
	 public function check_ip($ip)
	 {
		 global $wpdb;
		 
         $sql = "SELECT  count(*) as total FROM " . $wpdb->prefix . "usersultra_ip_defender  WHERE ip_number  = '$ip'  ";	 
		 
		 
		 $rows = $wpdb->get_results( $sql );
		 
		 if ( !empty( $rows ) )
		 {
			foreach ( $rows as $item )
			{
				$total = $item->total;			
			
			}
		
		}else{
			
			$total = 0;	
			
	    }
		
		return $total;
		
     }
	
	

}
$key = "defender";
$this->{$key} = new XooDefender();
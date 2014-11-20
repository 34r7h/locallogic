<?php
class XooChangelog {
	
	

	function __construct() 
	{
				

	}
	
	public function show_change_log($atts)
	{
		global $wpdb,  $xoouserultra;
		
		//require_once(ABSPATH . 'wp-includes/user.php');
		
		extract( shortcode_atts( array(
		
			'template' => 'profile', //this is the template file's name	
			
			
		), $atts ) );
		
		$html='<div class="uultra-changelogmodule">';
		
		
		$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'usersultra_changelog WHERE changelog_status = 1  ORDER BY changelog_id DESC ' );
		
		if ( empty( $rows ) )
		{
		
		
		}else{
			
			
			foreach ( $rows as $changelog )
			{
				$html.='<ul>';
				$html.='<span class="versionchangelog"> '.__('Version: ','xoousers').''.$changelog->changelog_version.'</span> - <span class="datechangelog">'.date("m/d/Y", strtotime($changelog->changelog_date)).'</span>';
				
					//loop through items
					$rowsItems = $this->get_changelog_items($changelog->changelog_id);
					
					if ( !empty( $rowsItems ) )
					{
					
						foreach ( $rowsItems as $rowT )
						{
							$html.='<li>'.$rowT->type_name.' - '.$rowT->item_title.'</li>';				
						
						}
					
					}
					
				
				
				$html .='</ul>';			
					
			
			}
			
		}
		
		
		
		$html .='</div>';
		
		
		return $html;
	
	
	}
	
	function get_changelog_items($item)
	{
		global $wpdb,  $xoouserultra;
		
		$sql = 'SELECT  item.*, type.*
		               
				FROM ' . $wpdb->prefix . 'usersultra_changelog_items item
				RIGHT JOIN ' . $wpdb->prefix . 'usersultra_changelog_types type ON (item.item_type_id = type.type_id )
						
				 WHERE item.item_changelog_id = ' . $item . ' AND item.item_type_id = type.type_id  ORDER BY  item.item_type_id  ';
				 
				 //echo $sql;
					   
		$rows = $wpdb->get_results($sql);
		
		return $rows;
	
	
	}
	
	
	
	
	

}
$key = "changelog";
$this->{$key} = new XooChangelog();
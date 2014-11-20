<?php
class XooUserApi
{
	var $current_user = array();

	function __construct() 
	{			

	}
	
	/*Loads User Information*/
	function load_user($value, $method) 
	{
		global $xoouserultra;		
		$user = $this->get_user($value, $method);		
		
	}
	
	/******************************************
	Get usermeta
	******************************************/
	function get($field, $user_id)
	{
		return get_user_meta($user_id, $field, true);
	}
	
	/******************************************
	Get user url
	******************************************/
	function get_user_url($user_id)
	{
		global $xoouserultra;		
		$url = $xoouserultra->userpanel->get_user_profile_permalink($user_id);		
		return $url;
		
		
	}
	
	/******************************************
	Get user meta
	******************************************/
	function get_user_field($user_id, $field)
	{		
		return $this->get($field, $user_id);			
	}
	
	
	/******************************************
	Get user avatar
	******************************************/
	function get_user_avatar($user_id, $args)
	{
		global $xoouserultra;
		
		extract($args);
		
		$avatar = $xoouserultra->userpanel->get_user_pic($user_id, $size, $pic_type, $pic_boder_type, $size_type);		
		return $avatar;		
		
	}
	
	/******************************************
	Get user badges
	******************************************/
	function get_user_badges($user_id)
	{
		global $xoouserultra;		
	
		$badges = $xoouserultra->badge->uultra_show_badges($user_id);		
		return $badges;		
		
	}
	
	/******************************************
	Get user
	******************************************/
	function get_user($value, $method)
	{
		if($method=='id')		
		{
			$user = get_user_by('ID',$value);
			
		}elseif($method=='login'){
			
			$user = get_user_by('login',$value);
		
		}elseif($method=='email'){
			
			$user = get_user_by('email',$value);		
		
		}
		
		return $user;
		
	}
	

}
$key = "api";
$this->{$key} = new XooUserApi();
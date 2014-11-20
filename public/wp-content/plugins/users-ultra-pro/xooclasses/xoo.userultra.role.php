<?php
class XooUserRole {
	
	 var $user_roles;

	

	function __construct() 
	{
				

	}
	
	
	 /* Returns the available user roles for the application. */
    public function uultra_get_available_user_roles($mode = '') {
        global $wp_roles;

        $roles = $wp_roles->get_names();
        
		// EDit mode automaticaaly contains admin role. So it's removed for selections
        if($mode == 'edit')
		{
            unset($roles['administrator']);
        }

        return $roles;
    }
	
	/* Setting for available user roles at registration */
    public function uultra_available_user_roles_registration(){
        global $wp_roles;
        $user_roles = array();

        if ( ! isset( $wp_roles ) ) 
            $wp_roles = new WP_Roles(); 

        $skipped_roles = array('administrator');

        foreach( $wp_roles->role_names as $role => $name ) {
            if(!in_array($role, $skipped_roles)){
                $user_roles[$role] = $name;
            }
        }

        return $user_roles;
    }
	
	public function get_public_roles_registration() 
	{
		global $wpdb, $xoouserultra;
		
		$display = "";
		
		//text to display
		$label_for_role = $xoouserultra->get_option('label_for_registration_user_role');
			
		if($label_for_role =="")
		{
			$label_for_role = __('Select your Role','xoousers');
			
		}
			
		$allowed_user_roles = $this->uultra_allowed_user_roles_registration();
		
		 $meta= 'uultra_custom_user_role';
		
	    $display .= '<select class="validate[required]  xoouserultra-input" name="' . $meta . '" id="reg_' . $meta . '" value="' . $xoouserultra->get_post_value($meta) . '" title="' . $name . '" >';
         
		 $display .= '<option value="">' .$label_for_role . '</option>';
		 
           foreach ($allowed_user_roles as $key => $val)
		   {
			   $display .= '<option value="' . $key . '">' . $val . '</option>';
           }
          
		  $display .= '</select>';
									
			return  $display;
	
	
	}
	
	public function get_package_roles($selected_package = null) 
	{
		global $wpdb, $xoouserultra;
		
		$display = "";
		
		
		$label_for_role = __('Select Role','xoousers');
			
		
		$allowed_user_roles = $this->uultra_available_user_roles_registration();
		
		$meta= 'p_role';
		
	    $display .= '<select class="xoouserultra-input" name="' . $meta . '" id="' . $meta . '" >';
         
		if(!$selected_package) 
		{
		 $display .= '<option value="">' .$label_for_role . '</option>';
		 
		}
           foreach ($allowed_user_roles as $key => $val)
		   {
			   $sel ="";
			   if($selected_package==$key) 
			   {
				   $sel = 'selected="selected"';
				  
			   }
			   $display .= '<option value="' . $key . '" '.$sel.' >' . $val . '</option>';
           }
          
		  $display .= '</select>';
									
			return  $display;
	
	
	}
	
	public function get_package_roles_custom_id($selected_package = null, $custom_id=null) 
	{
		global $wpdb, $xoouserultra;
		
		$display = "";
		
		
		$label_for_role = __('Select Role','xoousers');
			
		
		$allowed_user_roles = $this->uultra_available_user_roles_registration();
		
		$meta= 'p_role_'.$custom_id;
		
	    $display .= '<select class="xoouserultra-input" name="' . $meta . '" id="' . $meta . '" >';
         
		if(!$selected_package) 
		{
		 $display .= '<option value="">' .$label_for_role . '</option>';
		 
		}
           foreach ($allowed_user_roles as $key => $val)
		   {
			   $sel ="";
			   if($selected_package==$key) 
			   {
				   $sel = 'selected="selected"';
				  
			   }
			   $display .= '<option value="' . $key . '" '.$sel.' >' . $val . '</option>';
           }
          
		  $display .= '</select>';
									
			return  $display;
	
	
	}
	
	public function get_package_roles_edit($package_id, $selected_role) 
	{
		global $wpdb, $xoouserultra;
		
		$display = "";
		
		
		$label_for_role = __('Select Role','xoousers');
			
		
		$allowed_user_roles = $this->uultra_available_user_roles_registration();
		
		 $meta= 'p_role_'.$package_id;
		
	    $display .= '<select class="xoouserultra-input" name="' . $meta . '" id="' . $meta . '" >';
         
		
           foreach ($allowed_user_roles as $key => $val)
		   {
			   $sel ="";
			   if($selected_role==$key) 
			   {
				   $sel = 'selected="selected"';
				  
			   }
			   $display .= '<option value="' . $key . '" '.$sel.' >' . $val . '</option>';
           }
          
		  $display .= '</select>';
									
			return  $display;
	
	
	}
	
	
	   /* Setting for alowed user roles from available user roles */
    public function uultra_allowed_user_roles_registration()
	{
        global $wp_roles;

        $user_roles = array();

        if ( ! isset( $wp_roles ) ) 
            $wp_roles = new WP_Roles(); 

        $current_option = get_option('userultra_options');
        $user_roles_registration = $current_option['choose_roles_for_registration'];

        
        $allowed_user_roles = is_array($user_roles_registration) ? $user_roles_registration : array($user_roles_registration);

        $default_role = get_option("default_role");
        if(!in_array($default_role, $allowed_user_roles)){
            array_push($allowed_user_roles, $default_role);
        }

        if('' == $current_option['choose_roles_for_registration']){
            $user_roles[$default_role] = $wp_roles->role_names[$default_role];
            return $user_roles;
        }

        foreach ($allowed_user_roles as $usr_role) {
            $user_roles[$usr_role] = $wp_roles->role_names[$usr_role];
        }

        
        return $user_roles;
    }
	
	/* This will give us the roles of the given user */
    public function uultra_get_user_roles_by_id($user_id) 
	{
        $user = new WP_User($user_id);
		
        if (!empty($user->roles) && is_array($user->roles)) 
		{
            $this->user_roles = $user->roles;
            return $user->roles;
			
        } else {
            $this->user_roles = array();
            return array();
        }
    }
	
	/* Check the permission to show/edit given field by user Id */
    public function uultra_fields_by_user_role($user_role, $user_role_list) {

        $show_status = FALSE;
        if ('0' == $user_role) 
		{
            $show_status = TRUE;
			
        } else {

           if('' !=  $user_role_list)
		   {
                $user_role_list = explode(',', $user_role_list);
                
				foreach ($this->user_roles as $role)
				{
                    if (in_array($role, $user_role_list)) {
                        $show_status = TRUE;
                    }
                }
            }
            
        }
        return $show_status;
    }

	
	
	
	

}
$key = "role";
$this->{$key} = new XooUserRole();
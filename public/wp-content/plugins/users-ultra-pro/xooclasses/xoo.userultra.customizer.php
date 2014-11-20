<?php
class XooCustomizer
{
	
	var $mWidgetsCol;	
	var $mDefaultLayout;
	var $mTemplatesList;
	var $mDisallowedModules ;
		
	function __construct() 
	{
		
		$this->uultra_plugin_updater();
		//$this->set_default_user_panel();
		
		add_action('admin_init', array(&$this, 'set_default_user_panel'));
		add_action('init', array(&$this, 'set_default_user_panel'));
		add_action('admin_init', array(&$this, 'set_template_list'));
		//$this->set_template_list();		
		//$this->set_default_widgets_layout();
		
		add_action('init', array(&$this, 'set_default_uultra_features'));
		add_action('admin_init', array(&$this, 'set_default_uultra_features'));	
		
			
		$this->get_disallowed_modules();
		$this->uultra_user_profile_bg_image();
		
		add_action('wp_ajax_arrange_widgets',  array($this, 'save_pro_widgets' ));
		add_action('wp_ajax_customizer_set_default_template',  array($this, 'customizer_set_default_template' ));		
		add_action('wp_ajax_ajax_upload_profile_bg',  array($this, 'ajax_upload_profile_bg' ));
		add_action('wp_ajax_get_profile_bg_refresh',  array($this, 'get_profile_bg_refresh' ));
		add_action('wp_ajax_uultra_delete_user_profile_bg',  array($this, 'uultra_delete_user_profile_bg' ));
		add_action('wp_ajax_customizer_widget_appearance',  array($this, 'customizer_widget_appearance' ));
		add_action('wp_ajax_customizer_profile_appearance',  array($this, 'customizer_profile_appearance' ));
		add_action('wp_ajax_uultra_reload_user_modules',  array($this, 'uultra_reload_user_modules' ));
		add_action('wp_ajax_uultra_modules_deactivate_activate',  array($this, 'uultra_modules_deactivate_activate' ));
		add_action('wp_ajax_uultra_reload_user_menu_customizer',  array($this, 'uultra_reload_user_menu_customizer' ));
		add_action('wp_ajax_uulra_reload_all_widgets_admin',  array($this, 'uulra_reload_all_widgets_admin' ));
		add_action('wp_ajax_uultra_sort_user_menu_ajax',  array($this, 'uultra_sort_user_menu_ajax' ));
		add_action('wp_ajax_uultra_update_unused_wigets',  array($this, 'uultra_update_unused_wigets' ));
		add_action('wp_ajax_uultra_load_wdiget_addition_form',  array($this, 'uultra_load_wdiget_addition_form' ));
		add_action('wp_ajax_uultra_widget_addition_form_confirm',  array($this, 'widget_addition_form_confirm' ));
		add_action('wp_ajax_uultra_edit_widget_content',  array($this, 'uultra_edit_widget_content' ));
		add_action('wp_ajax_uultra_edit_widget_content_confirm',  array($this, 'uultra_edit_widget_content_confirm' ));
		
		add_action('wp_ajax_uultra_load_links_addition_form',  array($this, 'uultra_load_links_addition_form' ));
		add_action('wp_ajax_uultra_internal_user_menu_add_new_link_confirm',  array($this, 'uultra_internal_user_menu_add_new_link_confirm' ));
		
		add_action('wp_ajax_uultra_load_custom_link_options',  array($this, 'uultra_load_custom_link_options' ));
		add_action('wp_ajax_uultra_update_user_custom_link_admin',  array($this, 'uultra_update_user_custom_link_admin' ));
		add_action('wp_ajax_uultra_delete_custom_link',  array($this, 'uultra_delete_custom_link' ));
		add_action('wp_ajax_uultra_get_custom_link_content',  array($this, 'uultra_get_custom_link_content' ));
		add_action('wp_ajax_uultra_update_user_custom_link_content',  array($this, 'uultra_update_user_custom_link_content' ));		
		add_action('wp_ajax_uultra_rebuild_user_link',  array($this, 'uultra_rebuild_user_link' ));
		add_action('wp_ajax_uultra_delete_default_bg_image',  array($this, 'uultra_delete_default_bg_image' ));
		
		
	}
	
	
	
	public function uultra_user_profile_bg_image()
	{
		
		if (isset($_POST['uultra-form-custom-profile-bg'])) 
		{
			/* Let's Update the Profile */
			$this->process_user_bg_uploader($_FILES);
				
		}
	
	}
	
	/*This returns the default image for the users profile*/
	function get_custom_bg_for_user_profile() 
	{
		global $xoouserultra;
		
		$site_url = site_url()."/";
		
		$html = '';
		$img = get_option('uultra_default_profile_bg');
		
		$path_f = $xoouserultra->get_option('media_uploading_folder');						
		$target_path = $site_url.$path_f.'/custom_profile_bg/'.$img;
				
		if ($img!="") 
		{			
			
			$html .= $target_path;			
			
		}
		
		return $html;
	
	}
	
	/*This returns the default image for the users profile*/
	function get_custom_bg_for_user_profile_admin() 
	{
		global $xoouserultra;
		
		$site_url = site_url()."/";
		
		$html = '';
		$img = get_option('uultra_default_profile_bg');
		
		$path_f = $xoouserultra->get_option('media_uploading_folder');						
		$target_path = $site_url.$path_f.'/custom_profile_bg/'.$img;
				
		if ($img!="") 
		{			
			
			$html .= '<img src="'.$target_path.'" style="max-width:98% !important;">';	
			$html .= '<input type="button" name="submit"  class="button button-secondary " id="uultradmin-remove-custom-user-bg-image"   value="'.__('Remove','xoousers').'"  />';		
			
		}
		
		return $html;
	
	}
	
	/*Process uploads*/
	function process_user_bg_uploader($array) 
	{
		global $wpdb,  $xoouserultra;
		
		/* File upload conditions */
		$this->allowed_extensions = array("jpg", "jpeg", "png");		
							
		if (isset($_FILES))
		{
			
			
			foreach ($_FILES as $key => $array) {				
							
				extract($array);
				
				$file = $_FILES[$key];				
				$info = pathinfo($file['name']);
				$real_name = $file['name'];
				$ext = $info['extension'];
				$ext=strtolower($ext);
		
				
				if ($name) {					
						
				    
					
					if ( !in_array($ext, $this->allowed_extensions) )
					{
						$this->messages_process .= __('The file format is not allowed!','xoousers');	
						
													
					
					} else {
					
						/*Upload file*/									
						$path_f = ABSPATH.$xoouserultra->get_option('media_uploading_folder');
						
						$target_path = $path_f.'/custom_profile_bg/';
						// Checking for upload directory, if not exists then new created. 
						if(!is_dir($target_path))
						    mkdir($target_path, 0755);
						
						$target_path = $target_path."uultra_default_profile_bg_." .$ext;						
						move_uploaded_file( $tmp_name, $target_path);							
										
						//update						
						update_option('uultra_default_profile_bg', "uultra_default_profile_bg_." .$ext );						
						
						$this->messages_process .= __('--- Finished ---  ', 'xoousers');
						
										
												
					}
				}
			}
		}
		
	}
	
	public function uultra_plugin_updater()
	{
		
		if (!get_option('userultra_upgrade_113')) 
		{			
			update_option('userultra_upgrade_113', 1 );
			
			//reset fields
			$this->uultra_rebuild_user_link();
		}
	
	
	}
	
	public function set_template_list()
	{
		
		$tabs = array();
		
		$tabs[1] =array(
			  'template_id' => 1,
			  
			  'snapshot' => '880x660-userspro.png',			
			  'title' => __('Fancy Template 3 Columns', 'xoousers'),
			  'description' => __('This is the default template of Users Ultra PRO. This is a widgetized template that can be easily customized by the users.', 'xoousers'),												
			  'visible' => 1
		);
		
		
		$tabs[3] =array(
			  'template_id' => 3,			  
			  'snapshot' => 'twocolumn-userultrapro.png',			
			  'title' => __('Fancy Template 2 Columns', 'xoousers'),
			  'description' => __('This template will display all the widgets in two columns.', 'xoousers'),												
			  'visible' => 1
	    
		);	
		
		$tabs[4] =array(
			  'template_id' => 4,			  
			  'snapshot' => 'onecolumn-userultrapro.png',			
			  'title' => __('Fancy Template 1 Column', 'xoousers'),
			  'description' => __('This template will display all the widgets in one column', 'xoousers'),												
			  'visible' => 1
	    
		);			
		
		$tabs[2] =array(
			  'template_id' => 2,			  
			  'snapshot' => '880x660-usersfree.png',			
			  'title' => __('Default Template', 'xoousers'),
			  'description' => __('This template is the same than Lite version. Widgets are not available on this template. Some features may not be available.', 'xoousers'),												
			  'visible' => 1
	    
		);			
			
		//ksort($tabs);				
		update_option('userultra_default_templates', $tabs );	
		$this->mTemplatesList =$tabs;
		
		// we set the default template for the user's profile if not selected
		if (!get_option('userultra_default_system_template')) 
		{			
			update_option('userultra_default_system_template', '1' );
		}
		
	
	}
	
	public function get_disallowed_modules()
	{
		$modules=get_option('uultra_excluded_user_modules');
		$this->mDisallowedModules =unserialize($modules);
			
	}
	
	public function customizer_set_default_template()
	{
		$template=$_POST["template_id"];
		update_option('userultra_default_system_template',$template );
			
	}
	
	public function get_default_profile_template()
	{
		$template = get_option('userultra_default_system_template');
		return $template;
	
	}
	
	//this feature sets the defaul widgets in the customizer	
	public function set_default_user_panel()
	{
		
		$tabs = array();		
		$tabs[1] =array(
			  'position' => 1,
			  'icon' => 'fa-credit-card',			
			  'title' => __('Basic Information', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0, //the user can edit this widget
			  'native' =>1,		
			  'content' =>NULL,										
			  'visible' => 1
		);
		
		$tabs[2] =array(
			  'position' => 2,
			  'icon' => 'fa-users',			
			  'title' => __('My Friends', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,					
			  'native' =>1,
			  'content' =>NULL,					
			  'visible' => 1
		);
		
		$tabs[3] =array(
			  'position' => 3,
			  'icon' => 'fa-camera',			
			  'title' => __('My Photos', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,	
			  'native' =>1,
			  'content' =>NULL,									
			  'visible' => 1
		);
		
		$tabs[4] =array(
			  'position' => 4,
			  'icon' => 0,			
			  'title' => __('My Galleries', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,	
			  'native' =>1,
			  'content' =>NULL,									
			  'visible' => 1
		);
		
		$tabs[5] =array(
			  'position' => 5,
			  'icon' => 'fa-pencil-square-o',			
			  'title' => __('My Latest Posts', 'xoousers'),	
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,	
			  'native' =>1,
			  'content' =>NULL,									
			  'visible' => 1
		);
		
		$tabs[6] =array(
			  'position' =>6,
			  'icon' => 'fa-eye',			
			  'title' => __('Followers', 'xoousers'),	
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,
			  'native' =>1,
			  'content' =>NULL,										
			  'visible' => 1
		);
		
		/*$tabs[7] =array(
			  'position' =>7,
			  'icon' => 'fa-user',			
			  'title' => __('My Card', 'xoousers'),											
			  'visible' => 1
		);*/
		
		$tabs[8] =array(
			  'position' =>8,
			  'icon' => 'fa-film',			
			  'title' => __('My Latest Videos', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,
			  'native' =>1,
			  'content' =>NULL,											
			  'visible' => 1
		);
		
		$tabs[9] =array(
			  'position' =>9,
			  'icon' => 'fa-film',			
			  'title' => __('My Wall', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode
			  'editable' =>0,	
			  'native' =>1,	
			  'content' =>NULL,									
			  'visible' => 1
		);
		
		$tabs[10] =array(
			  'position' =>10,
			  'icon' => 'fa-file-text',			
			  'title' => __('About / Bio', 'xoousers'),
			  'type' =>1, // 1-text, 2-shortcode	
			  'editable' =>0,
			  'native' =>1,
			  'content' =>NULL,										
			  'visible' => 1
		);
			
		ksort($tabs);
		
		//custom tabs
		$custom_widgets = get_option('userultra_custom_user_widgets');
		
		if(!is_array($custom_widgets))
		{			
			$custom_widgets = array();		
		}
		
		$tabs =  $tabs + $custom_widgets;
		
		update_option('userultra_default_user_tabs', $tabs );		
		$this->mWidgetsCol =$tabs;	
	
	}
	
	//update unused widgets 
	public function uultra_update_unused_wigets()
	{
		$module_list = array();
		$modules = $_POST["order"]; 
		$module_list = explode(",", $modules);		
		$modules_disalowed = serialize($module_list);		
				
		update_option('uultra_unused_user_widgets',$modules_disalowed);
		die();
	
	}
	
	public function uultra_check_if_unused_widget($widget_id, $unused=false)
	{
		$widgets = array();		
			
		$widgets = get_option('uultra_unused_user_widgets');	
		$widgets = unserialize($widgets);
		
		if (!is_array($widgets))
		{
			$widgets = array();
		}
		
		if($unused) //loads only unused widgets
		{
		
			if(in_array($widget_id,$widgets))	
			{
				return false;
				
			}else{
				
				return true;
			
			}
			
		}else{
			
			//loads only used widgets list
			
			if(in_array($widget_id,$widgets))	
			{
				return true;
				
			}else{
				
				return false;
			
			}
			
		}
				
		
	}
	
	//this adds the new widget	
	public function widget_addition_form_confirm()
	{
		
		$default_widgets = get_option('userultra_default_user_tabs');
		$custom_widgets = get_option('userultra_custom_user_widgets');
		
		if(!is_array($custom_widgets))
		{
			
			$custom_widgets = array();
		
		}
		
		$uu_title = $_POST["uu_title"];
		$uu_type = $_POST["uu_type"];
		$uu_editable = $_POST["uu_editable"];
		$uu_content = $_POST["uu_content"];
		
		$new_widget_key = count($default_widgets) +10;
		
		echo "new widget: ".$new_widget_key;
		
		if($uu_title!="")
		{
		
			$default_widgets[$new_widget_key] =array(
				  'position' =>$new_widget_key,
				  'icon' => 'fa-file-text',			
				  'title' => $uu_title,
				  'type' =>$uu_type, // 1-text, 2-shortcode	
				  'editable' =>$uu_editable ,	
				  'native' =>0,
				  'content' =>$uu_content,									
				  'visible' => 1
			);
			
			$custom_widgets[$new_widget_key] =array(
				  'position' =>$new_widget_key,
				  'icon' => 'fa-file-text',			
				  'title' => $uu_title,
				  'type' =>$uu_type, // 1-text, 2-shortcode	
				  'editable' =>$uu_editable ,	
				  'native' =>0,		
				  'content' =>$uu_content,									
				  'visible' => 1
			);
		
		}
		
		update_option('userultra_custom_user_widgets', $custom_widgets );
		
		print_r($custom_widgets);		
		die();		
		
		
		
	}
	
	//this returns the form to add a new widget	
	public function uultra_load_links_addition_form()
	{
		$html = '';
		$html .= '<div class="uultra-adm-links-cont-add-new" >';
  
		$html .= '<table width="100%" border="0" cellspacing="2" cellpadding="3">
			<tr>
				<td width="50%"> '.__("Name: ",'xoousers').'</td>
				<td width="50%"><input name="uultra_add_mod_link_title" type="text" id="uultra_add_mod_link_title" style="width:120px"  /> 
		 </td>
		 </tr>
		  <tr>
				<td width="50%"> '.__('Type:','xoousers').'</td>
				<td width="50%">
				<select name="uultra_add_mod_link_type"  id="uultra_add_mod_link_type" size="1">
				  <option value="" selected="selected">'.__("Select Type: ",'xoousers').'</option>
				  <option value="1">'.__("Text: ",'xoousers').'</option>
				  <option value="2">Shortocode</option>
				</select>

		 </td>
			  </tr>
					  
			  <tr>
				<td>'.__('Content:','xoousers').'</td>
				<td>&nbsp;</textarea> 
			   </td>
			  </tr>
			  
			  <tr>
				
				<td colspan="2"><textarea name="uultra_add_mod_links_content" id="uultra_add_mod_links_content" style="width:98%;" rows="5"></textarea> 
			   </td>
			  </tr> 
			  
			 
			</table>     ';       			
			          
			$html .= ' <p class="submit">
					<input type="button" name="submit"  class="button  uultra-links-add-new-close"   value="'.__('Close','xoousers').'"  />  <input type="button" name="submit"  class="button button-primary uultra-links-add-new-confirm"   value="'.__('Submit','xoousers').'"  /> <span id="uultra-add-new-links-m-w" ></span>
				</p> ';
				
			$html .= '</div>';
				
			echo $html;
			die();
	
	}
	
	//this returns the form to add a new widget	
	public function uultra_load_wdiget_addition_form()
	{
		$html = '';
		$html .= '<div class="uultra-adm-widget-cont-add-new" >';
  
		$html .= '<table width="100%" border="0" cellspacing="2" cellpadding="3">
			<tr>
				<td width="50%"> '.__("Name: ",'xoousers').'</td>
				<td width="50%"><input name="uultra_add_mod_widget_title" type="text" id="uultra_add_mod_widget_title" style="width:120px"  /> 
		 </td>
		 </tr>
		  <tr>
				<td width="50%"> '.__('Type:','xoousers').'</td>
				<td width="50%">
				<select name="uultra_add_mod_widget_type"  id="uultra_add_mod_widget_type" size="1">
				  <option value="" selected="selected">'.__("Select Type: ",'xoousers').'</option>
				  <option value="1">'.__("Text: ",'xoousers').'</option>
				  <option value="2">Shortocode</option>
				</select>

		 </td>
			  </tr>
		<tr>
				<td width="50%"> '.__('Editable by user:','xoousers').'</td>
				<td width="50%">
				<select name="uultra_add_mod_widget_editable"  id="uultra_add_mod_widget_editable" size="1">
				 
				  <option value="0" selected="selected">'.__("NO ",'xoousers').'</option>
				  <option value="1">'.__("YES",'xoousers').'</option>
				</select>

		 </td>
			  </tr>
			  
			  <tr>
				<td>'.__('Content:','xoousers').'</td>
				<td>&nbsp;</textarea> 
			   </td>
			  </tr>
			  
			  <tr>
				
				<td colspan="2"><textarea name="uultra_add_mod_widget_content" id="uultra_add_mod_widget_content" style="width:98%;" rows="5"></textarea> 
			   </td>
			  </tr> 
			  
			 
			</table>     ';       			
			          
			$html .= ' <p class="submit">
					<input type="button" name="submit"  class="button  uultra-widgets-add-new-close"   value="'.__('Close','xoousers').'"  />  <input type="button" name="submit"  class="button button-primary uultra-widgets-add-new-confirm"   value="'.__('Submit','xoousers').'"  /> <span id="uultra-add-new-widget-m-w" ></span>
				</p> ';
				
			$html .= '</div>';
				
			echo $html;
			die();
	
	}
	
	//this set all the modules and user menu options.	
	public function uulra_reload_all_widgets_admin()
	{
		$html = '';		
		$widgets_col = $this->mWidgetsCol;
		
		$unused = false;
		
		if(isset($_POST["unused"]))
		{
			$unused = true;		
		}
		
		
		foreach($widgets_col as $key => $widget)
		{
			$widget_title = $widget["title"];
			
			if(!$this->uultra_check_if_unused_widget($key, $unused))
			{
			
				$html .= '<li class="left_widget_customizer_li" id="'.$key.'">
				
						<div class="uultra-expandable-panel-heading-widgets "  widget-id="'.$key.'">
						<h2 id="uultra-widget-title-id-'.$key.'">'.$widget_title.'</h2>  
						<span class="uultra-widgets-icon-close-open" id="uultra-widgets-icon-close-open-id-'.$key.'" style="background-position: 0px 0px;" widget-id="'.$key.'"></span>
						</div>'; 
	
				$widget_customization = $this->get_widget_appearance($key);
				
				$html .= '<div class="uultra-adm-widget-cont" id="uultra-widget-adm-cont-id-'.$key.'" style=" display:none">';
	  
				$html .= '<table width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr>
					<td width="50%"> '.__("Widget's Title ",'xoousers').'</td>
					<td width="50%"><input name="widget_title_'.$key.'" type="text" id="widget_title_'.$key.'" value="'.$widget_customization['widget_title'].'" style="width:120px"  /> 
			 </td>
			 </tr>
			  <tr>
					<td width="50%"> '.__('Header Background Color','xoousers').'</td>
					<td width="50%"><input name="widget_header_bg_color_'.$key.'" type="text" id="widget_header_bg_color_'.$key.'" value="'.$widget_customization['widget_header_bg_color'].'" class="color-picker" data-default-color=""/> 
			 </td>
				  </tr>
				  <tr>
					<td>'.__('Background Color','xoousers').'</td>
					<td><input name="widget_bg_color_'.$key.'" type="text" id="widget_bg_color_'.$key.'" value="'.$widget_customization['widget_bg_color'].'" class="color-picker"  data-default-color="" /> 
				   </td>
				  </tr>
				  
				   <tr>
					<td>'.__('Header Text Color','xoousers').'</td>				
					<td><input name="widget_header_text_color_'.$key.'" type="text" id="widget_header_text_color_'.$key.'" value="'.$widget_customization['widget_header_text_color'].'" class="color-picker"  data-default-color="" /> 
				   </td>
				  </tr>
				  
				  <tr>
					<td>'.__('Widget Text Color','xoousers').'</td>
					<td><input name="widget_text_color_'.$key.'" type="text" id="widget_text_color_'.$key.'" value="'.$widget_customization['widget_text_color'].'" class="color-picker"  data-default-color="" /> 
				   </td>
				  </tr>
				 
				</table>     ';       
				
				//check if this is a custom widget				
				if( isset($widget["native"]) && $widget["native"] ==0)
				{
					$html .= $this->get_widget_custom_settings($key, $widget);
				
				}
				
				
				//check if we need to add extra options for this
				$html .= $this->get_widget_extra_settings($key);            
				$html .= ' <p class="submit">
						<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="'.$key.'" value="'.__('Save Changes','xoousers').'"  /> <span id="uultra-widget-update-message-'.$key.'"></span>
					</p> ';
					
				$html .= '</div>';
				$html .= ' </li>  ';
			}			
		
		}
		
		echo $html;		
		die();
		
	}
	
	//this option are displayed only if it's a custom widget	
	public function get_widget_custom_settings($key, $widget)
	{
		global  $xoouserultra;
		
		//$custom_widgets = get_option('userultra_custom_user_widgets');
		
	/*	 'title' => $uu_title,
				  'type' =>$uu_type, // 1-text, 2-shortcode	
				  'editable' =>$uu_editable ,	
				  'native' =>0,		
				  'content' =>$uu_content,*/
				  
		$editable_0 = "";
		$editable_1 = "";
		if($widget['editable']==0) {$editable_0 = 'selected="selected"';}
		if($widget['editable']==1) {$editable_1 = 'selected="selected"';}
		
		$type_1 = "";
		$type_2 = "";
		if($widget['type']==1) {$type_1 = 'selected="selected"';}
		if($widget['type']==2) {$type_2 = 'selected="selected"';}
		
		$html = '';
		$html .= '<div class="uultra-adm-widget-cont-add-new" style="width:98%" >';
  
		$html .= '<table width="100%" border="0" cellspacing="2" cellpadding="3">
			<tr>
				<td width="50%"> '.__("Name: ",'xoousers').'</td>
				<td width="50%"><input name="uultra_add_mod_widget_title_'.$key.'" type="text" id="uultra_add_mod_widget_title_'.$key.'" style="width:120px" value="'.$widget['title'].'"  /> 
		 </td>
		 </tr>
		  <tr>
				<td width="50%"> '.__('Type:','xoousers').'</td>
				<td width="50%">
				<select name="uultra_add_mod_widget_type_'.$key.'"  id="uultra_add_mod_widget_type_'.$key.'" size="1">
				 
				  <option value="1" '.$type_1.'>'.__("Text: ",'xoousers').'</option>
				  <option value="2" '.$type_2.'>Shortocode</option>
				</select>

		 </td>
			  </tr>
		<tr>
				<td width="50%"> '.__('Editable by user:','xoousers').'</td>
				<td width="50%">
				<select name="uultra_add_mod_widget_editable_'.$key.'"  id="uultra_add_mod_widget_editable_'.$key.'" size="1">
				 
				  <option value="0" '.$editable_0.'>NO</option>
				  <option value="1" '.$editable_1.'>YES</option>
				</select>

		 </td>
			  </tr>
			  
			  <tr>
				<td>'.__('Content:','xoousers').'</td>
				<td>&nbsp;</textarea> 
			   </td>
			  </tr>
			  
			  <tr>
				
				<td colspan="2">
				
				<textarea name="uultra_add_mod_widget_content_'.$key.'" id="uultra_add_mod_widget_content_'.$key.'" style="width:98%;" rows="5">'.stripslashes($widget['content']).'</textarea>
				
				
				
				 
			   </td>
			  </tr> 
			  
			 
			</table>     ';       			
			          
			$html .= ' <p class="submit"></p> ';
				
			$html .= '</div>';
		
			
		
		return $html;
	
	}
	
	public function get_widget_extra_settings($widget)
	{
		global  $xoouserultra;
		$html = '';
		
		if($widget==5)
		{
			
			$html .= ' <h4>'.__('Post Types','xoousers').'</h4>            
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
                <td width="50%">'.__('Display Post Types','xoousers').'</td>
                <td width="50%">'.$xoouserultra->publisher->get_all_post_types().'
				 </td>
				 </tr>  
				 
			</table>   ';
		
		}
		
		
		return $html;
	
	}
	
	
	
	public function customizer_profile_appearance()
	{
		
		$array_customizer = array();		
		
		$array_customizer = array('uultra_profile_bg_color' =>$_POST['uultra_profile_bg_color'], 'uultra_profile_bg_color_transparent' =>$_POST['uultra_profile_bg_color_transparent'] ,'uultra_profile_inferior_bg_color' =>$_POST['uultra_profile_inferior_bg_color'] ,'uultra_profile_inferior_bg_color_transparent' =>$_POST['uultra_profile_inferior_bg_color_transparent'] ,'uultra_profile_image_bg_color' =>$_POST['uultra_profile_image_bg_color']);
		
		$widget_customization = serialize($array_customizer);		
		$widget = 'uultra_profile_customizing';				
		update_option($widget,$widget_customization);
		
		echo __("Settings has been udpated! ", "xoousers");
		die();
	
	}
	
	
	//This function saves the widget customization
	public function customizer_widget_appearance()
	{
		
		$array_customizer = array();		
		$widget_id = $_POST['p_id'];		
				
		$widget_post_types = "";
		if($widget_id==5) //my posts widgets
		{
			$str_types = "";
			$widget_post_types = $_POST['widget_post_type_list'];
			$ii = 0;
			
			foreach($widget_post_types as $type)
			{
				//echo $type["value"];
				$str_types .= $type["value"];
				
				if(count($widget_post_types)-1>$ii)
				{
					$str_types .= ",";
				
				}
				
				$ii++;				
			}
			
		}
		
		$array_customizer = array('widget_header_bg_color' =>$_POST['widget_header_bg_color'], 'widget_bg_color' =>$_POST['widget_bg_color'] ,'widget_header_text_color' =>$_POST['widget_header_text_color'] ,'widget_text_color' =>$_POST['widget_text_color'] ,'widget_transparent' =>$_POST['widget_transparent'] ,'widget_title' =>$_POST['widget_title'],'widget_post_types' =>$str_types);
		
		$widget_customization = serialize($array_customizer);		
		$widget = 'uultra_widget_customing_'.$widget_id;			
		update_option($widget,$widget_customization);
		
		//update widget properties only if it's a custom widget
		
		if($this->uultra_check_if_custom_widget($widget_id) && isset($_POST['uultra_add_mod_widget_title'])  && $_POST['uultra_add_mod_widget_title'] !="" )	{
			
			
			//update widget array	
			
			$custom_widgets = get_option('userultra_custom_user_widgets');
			
			$custom_widgets[$widget_id]['title'] = $_POST['uultra_add_mod_widget_title'];
			$custom_widgets[$widget_id]['type'] = $_POST['uultra_add_mod_widget_type'];
			$custom_widgets[$widget_id]['editable'] = $_POST['uultra_add_mod_widget_editable'];
			$custom_widgets[$widget_id]['content'] = $_POST['uultra_add_mod_widget_content'];				
							
			update_option('userultra_custom_user_widgets', $custom_widgets);
			
		
		}
		
		
		
		echo __("Settings has been udpated! ", "xoousers");
		die();
	
	}
	
	/*This function lets us know if it's a custom widget*/	
	public function uultra_check_if_custom_widget($widget_id)
	{
		$custom_widgets = get_option('userultra_custom_user_widgets');
		
		if(isset($custom_widgets[$widget_id]))
		{
			//it's a custom widget			
			return true;
		
		}else{
			
			return false;
			
		}
		
	
	}
	
	
	function check_if_disallowed_array($key_to_check)
	{
		
		$disallowed = $this->mDisallowedModules;
		
		if (!is_array($disallowed))
		{
			$disallowed = array();
		}		
		
		foreach($disallowed as $item)
		{			
			if($item == $key_to_check && $item!="")
			{
				
				return true;			
			}		
		}
		
		return false;
	}
	
	/*This feature displays all the modules in the appearance module*/	
	public function uultra_reload_user_modules()
	{
		$modules = get_option('userultra_default_user_features');		
		
		
		$html = '';
		foreach($modules as $key => $module)
		{
			$checked = '';			
			
			if ($this->check_if_disallowed_array($key))
			{
				$checked = 'checked="checked"';
			}	
			
			
			$html .= '<li class="uultra-profile-widget rounded" id="'.$key.'"><input name="uultra-modules[]" type="checkbox" value="'.$key.'" class="uultra-my-modules-checked"  '.$checked.'/><label for="checkbox1"><span></span>'.$module["title"].'</label></li>';
			
			
		
		
		}
		
		
		echo $html;
		die();
		
	}
	
	/*Add new link to the user's menu through admin*/	
	public function uultra_internal_user_menu_add_new_link_confirm()
	{
		global $xoouserultra;
		
		$html = '';
		$error_message = false;
		
		$current_menu = get_option('userultra_default_user_features_custom');
		$new_count = count($current_menu) +10;	
		
		//custom menu options added by the admin
		$custom_menues_added  = array();		
		$custom_menues_added = get_option('userultra_default_user_features_added_admin');
				
		
		$uu_title = $_POST["uu_title"];
		$uu_slug = strtolower($_POST["uu_slug"]);
		$uu_type = $_POST["uu_type"];
		$uu_content = $_POST["uu_content"];	
		
		if($this->check_if_slug_exists($uu_slug))
		{
			$error_message = true;
			$html .= __('ERROR: Slug already exists!', 'xoousers');
		
		}
		
		if($uu_slug ==""  && !$error_message)
		{
			$error_message = true;
			$html .= __('ERROR: Please input the slug!', 'xoousers');		
		}
		
		
		
		if (!preg_match('/^[\w-]*$/', $uu_slug)) 
		{
			$error_message = true;
			$html .= __('Please input only letters, numbers and - _ !', 'xoousers');		
		}
			
		
		if(!$error_message)
		{
			$current_menu[$new_count] =array(
					  'position' => $new_count,
					  'slug' => $uu_slug,
					  'slug_public' => 'fa-video-camera',
					  'link_type' => 'custom',
					  'content' => $uu_content,
					  'content_type' => $uu_type,
					  'icon' => 'fa-chain',			
					  'title' => $uu_title,											
					  'visible' => 1
				);
				
			$custom_menues_added[$new_count] =array(
					  'position' => $new_count,
					  'slug' => $uu_slug,
					  'slug_public' => 'fa-video-camera',
					  'link_type' => 'custom',
					  'content' => $uu_content,
					  'content_type' => $uu_type,
					  'icon' => 'fa-chain',			
					  'title' => $uu_title,											
					  'visible' => 1
				);
				
				
				
				$html .= __('Done!', 'xoousers');		
		
		}				
		
	
		//update links added by admin array		
		update_option('userultra_default_user_features_added_admin',$custom_menues_added);	
		
		//upate master array	
		update_option('userultra_default_user_features_custom',$current_menu);
		
		
		echo $html ;		
		die();
		
	}
	
	//this deletes a custom links	
	function uultra_delete_custom_link()
	{		
		$modules = get_option('userultra_default_user_features_custom');
		$modules_custom = get_option('userultra_default_user_features_added_admin');
		
		$id =  $_POST["link_id"];		
		
		foreach($modules as $key => $module)
		{
			if($id==$key)
			{
				unset($modules[$key]);
			
			}
		}
		
		foreach($modules_custom as $key => $module)
		{
			if($id==$key)
			{
				unset($modules_custom[$key]);			
			
			}
		}	
		
		update_option('userultra_default_user_features_custom',$modules);
		update_option('userultra_default_user_features_added_admin',$modules_custom);
		
		print_r($modules_custom);
		die();
	
	}
	
	//this updates some info of the user links	
	function uultra_get_custom_link_content()
	{		
		$modules = get_option('userultra_default_user_features_custom');
				
		$id =  $_POST["link_id"];
		$html = '';
		
		if(isset($modules[$id]) )
		{
			$link =$modules[$id] ;			
			$html .= stripslashes($link['content']);
		
		}else{
			
			$html .= __('Content not found', 'xoousers');
		
		
		}
		
		echo $html;
		die();		
	
	}
	
	//this updates some info of the user links	
	function uultra_update_user_custom_link_admin()
	{		
		$modules = get_option('userultra_default_user_features_custom');
		$modules_custom = get_option('userultra_default_user_features_added_admin');
		
		$id =  $_POST["link_id"];				
		$uu_title = $_POST["uu_title"];
		$uu_slug = strtolower($_POST["uu_slug"]);
		$uu_type = $_POST["uu_type"];
		
		$html = '';
		
		
		if(isset($modules[$id]) && $uu_title!="")
		{
			$link =$modules[$id] ;		
			
			
			if($link['link_type']=='custom')
			{
				
				$modules[$id]['title'] =$uu_title;					
				
				//custom array
				$modules_custom[$id]['title'] =$uu_title;	
						
				$html = __('Done!', 'xoousers');
				
				//check for slug
							
			}else{
				
				//this is a native link so we just update the title					
				$modules[$id]['title'] =$uu_title;				
				$html = __('Done!', 'xoousers');
			
			}		
			
			
			
			
		}
		
		echo $html;
		update_option('userultra_default_user_features_custom',$modules);
		update_option('userultra_default_user_features_added_admin',$modules_custom);
		die();
	
	}
	
	//this updates some info of the user links	
	function uultra_update_user_custom_link_content()
	{		
		$modules = get_option('userultra_default_user_features_custom');
		$modules_custom = get_option('userultra_default_user_features_added_admin');
		
		$id =  $_POST["link_id"];				
		$uu_content = $_POST["widget_text"];
		
		$html = '';
		
		
		if(isset($modules[$id]) )
		{
			$link =$modules[$id] ;
			$modules[$id]['content'] =$uu_content;					
				
			//custom array
			$modules_custom[$id]['content'] =$uu_content;	
						
			$html = __('Done!', 'xoousers');
				
		}
		
		echo $html;
		update_option('userultra_default_user_features_custom',$modules);
		update_option('userultra_default_user_features_added_admin',$modules_custom);
		die();
	
	}
	
	function check_if_slug_exists($slug_to_check)
	{
		
		$modules = get_option('userultra_default_user_features_custom');
		
		$check =  false;
		
		foreach($modules as $key => $module)
		{
			$slug = $module['slug'];
			
			if($slug_to_check==$slug)
			{
				return true;
			
			}			
				
		}
		
		return $check;
	
	}
	
	function get_custom_module_link_by_slug($slug_to_check)
	{
		
		$modules = get_option('userultra_default_user_features_custom');
		
		$check =  false;
		
		foreach($modules as $key => $module)
		{
			$slug = $module['slug'];
			
			if($slug_to_check==$slug)
			{
				return $module;
			
			}			
				
		}
		
		return $check;
	
	}
	
	//This Displays the custom content of the link in the user dashboard
	function get_custom_link_content_by_slug($slug)
	{		
		
		$html = '';			
		$module = $this->get_custom_module_link_by_slug($slug);
		
				
		
		if(isset($module) && $module != false)
		{
			$content = stripslashes($module['content']);
			
			$html .= '<div class="commons-panel xoousersultra-shadow-borers" >
                                
                      <div class="commons-panel-heading">
                              <h2>'.$module['title'].' </h2>
                       </div>';
			
			$html .= ' <div class="commons-panel-content" > ';
			
			if($module['content_type']=='2') //shortcode
			{
				$html .= do_shortcode($content);		
			
			}else{
				
				$html .= $content;			
			
			}
			
			$html .= '</div>';
			$html .= '</div>';
		}
		
		return $html;
	
	}
	
	/*This feature displays the navigation menu in the users dashboard*/	
	public function uultra_internal_user_menu_options()
	{
		global $xoouserultra;
		$modules = get_option('userultra_default_user_features_custom');
		
		$html = ' <ul class="main_menu">';		
		
			foreach($modules as $key => $module)
			{
				$slug = $module['slug'];					
				
				if($module['visible']==1)
				{										
					$html .= '<li>'.$xoouserultra->userpanel->get_user_backend_menu_new($module).'</li>';
				
				}
			}
		
		$html .= ' </ul>';
		
		return $html;
				
	}
	
	/*This feature displays all the nav option in the menu within the customizer, it can be used to sort the items*/	
	public function uultra_reload_user_menu_customizer()
	{
		$modules = get_option('userultra_default_user_features_custom');
						
		$html = '';
		
		foreach($modules as $key => $module)
		{
			$checked = '';							
			$html .= '<li class="uultra-profile-widget rounded" id="'.$key.'"> ';	
			
			$html .= '<div class="uultra-custom-link-header" id="uultra-link-item-header'.$key.'">';			
			$html .= '<a class="uultra-btn-widget-elements uultra-links-close-open-link" href="#" link-id="'.$key.'">';			
			
			$html .= ''.$module["title"].'
			
			<span class="uultra-links-icon-close-open" id="uultra-links-icon-close-open-id-'.$key.'" style="background-position: 0px 0px;" link-id="'.$key.'"> </span> ';
			
			$html .= '</a>';			
			$html .= '</div>';
			
			$html .= '<div id="uultra-link-adm-cont-id-'.$key.'" style="display:none">';		
				
			$html .= '</div>';			
			$html .= '</li>';
				
		}
		
		echo $html;
		die();
		
	}
	
	//This function lets us know if the module can be used by the user	
	public function uultra_load_custom_link_options()
	{					
		global $xoouserultra;
		
		$key = $_POST["link_id"];
		
		$html = '';		
		$modules = get_option('userultra_default_user_features_custom');		
		$module = $modules[$key];
		
		if($module["link_type"]!='custom')
		{		 
				$disable = 'readonly="readonly"';
			
		}
		
		$html .= '<p><strong>'.__('Title', 'xoousers').'</strong></p>';
		$html .= '<p><input type="text" name="uultra-link-name-'.$key.'" id="uultra-link-name-'.$key.'" value="'.$module['title'].'"></p>';
		
		$html .= '<p><strong>'.__('Slug', 'xoousers').'</strong></p>';
		$html .= '<p><input type="text" name="uultra-link-slug-'.$key.'" id="uultra-slug-name-'.$key.'" value="'.$module['slug'].'" '.$disable.'></p>';					
			
			$html .= ' <p class="submit">
					<input type="button" name="submit"  class="button button-primary uultra-links-edit-content-btn"   value="'.__('Submit','xoousers'). '" uultra-linkid = '.$key.'  /> <span id="uultra-add-new-links-m-w-d-'.$key.'" ></span>
				</p> ';	
				
		 //
			 
		if($module["link_type"] =='custom')
		{		 
				 $html .= '<p><strong>'.__('Edit Content:', 'xoousers').'</strong></p>';
				 $html .= '<p>'.__('You can edit what the user will see by clicking on the following button:', 'xoousers').'</p>';		
				 
				 $html .= ' <p class="submit">
					<input type="button" name="submit"  class="button button-primary uultra-links-edit-text-btn"   value="'.__('Click to edit content','xoousers'). '" uultra-linkid = '.$key.'  /> <span id="uultra-add-new-links-m-w-text-'.$key.'" ></span>
				</p> ';	
						 
				 $html .= '<p style="text-align:right;"><a href="#" class="uultra-delete-custom-link" uultra-linkid="'.$key.'">delete</a></p>';
			
		}
		
		echo $html;
		die();
	
	}
	
	//This function lets us know if the module can be used by the user	by SLUG
	public function user_module_menu_allowed($user_id = null, $module_key)
	{					
		global $xoouserultra;
		
		$modules = get_option('userultra_default_user_features_custom');
		
		foreach($modules as $key => $module)
		{
			if($module['slug']==$module_key)
		
			{
				return true;
			
			}
		}	
		
		
	}
	
	//This function lets us know if the module can be used by the user by KEY	
	public function user_front_nav_menu_allowed($user_id = null, $module_key)
	{					
		global $xoouserultra;
		
		$modules = get_option('userultra_default_user_features_custom');
		
		foreach($modules as $key => $module)
		{
			if($key==$module_key)
		
			{
				return true;
			
			}
		}	
		
		
	}
		
	//This  check if the module has been disable
	public function user_front_widgets_disabled_my_module($user_id = null, $module_key)
	{					
		global $xoouserultra;
		
		if($module_key==2) //my friends
		{
			$key =  6;
		
		}elseif($module_key==3){ //my photos
		
			$key =  3;
		
		}elseif($module_key==4){ //my galleries
		
			$key =  4;
		
		}elseif($module_key==5){ //my posts
		
			$key =  5;
		
		}elseif($module_key==6){ //followers
		
			$key =  1;
		
		}elseif($module_key==8){ //videos
		
			$key =  4;
		
		}else{ //videos
			
			return true;
		
		}
		
		return $this->user_front_nav_menu_allowed($user_id, $key);
		
		
	}
	
	function check_if_in_deactivate_array($modules, $key_to_check)
	{
		foreach($modules as $item)
		{			
			if($item == $key_to_check && $item!="")
			{
				
				return true;			
			}		
		}
		
		return false;
	}
	
	//update deactivated modules
	public function uultra_modules_deactivate_activate()
	{
		$module_list = array();
		$modules = $_POST["modules_list"]; 
		
		
		if($modules!="")
		{
			$module_list = explode("|", $modules);
			
			//$module_list =  $this->cleanArray($module_list);
			$modules_disalowed = serialize($module_list);
		
		}
		
		print_r($module_list);
				
		//update custom		
		$defaultmodules = get_option('userultra_default_user_features');
		
		//user menu modules
		$user_menu_modules = get_option('userultra_default_user_features_custom');
		
		//user menu modules added by admin
		$user_menu_modules_by_admin = get_option('userultra_default_user_features_added_admin');
		
		$html = '';
		
		$modules_array = array();
		$i = 1;
		foreach($defaultmodules as $key => $module)
		{
			if(!$this->check_if_in_deactivate_array($module_list, $key))
			{
				//if (strpos($modules,$key) !== true) {
					
				//set position and custom settings							
				if(isset($user_menu_modules[$key]))
				{
					$module["position"] = $i;
				
				}else{
					
					$module["position"] = $i;
				
				}		
				
				$modules_array[$key] = $module;							
				$i++;
				
			}else{
				
				echo "disallowed: " . $key;
				
			}	
		
		}
		
		if(is_array($user_menu_modules_by_admin) && $user_menu_modules_by_admin!="")
		{
			
			$modules_array = $modules_array +  $user_menu_modules_by_admin;		
		}
		
		update_option('userultra_default_user_features_custom',$modules_array);					
		update_option('uultra_excluded_user_modules',$modules_disalowed);
		die();
	
	}
	
	
	//this set all the modules and user menu options.	
	public function set_default_uultra_features()
	{
		
		$tabs = array();
		
		$tabs[0] =array(
			  'position' => 0,
			  'slug' => 'dashboard',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-tachometer',			 			
			  'title' => __('Dashboard', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[1] =array(
			  'position' => 1,
			  'slug' => 'followers',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'icon' => 'fa-credit-card',			 			
			  'title' => __('Followers', 'xoousers'),											
			  'visible' => 0 //not visible in user's dashboard
		);
		
		$tabs[2] =array(
			  'position' => 2,
			  'slug' => 'following',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'icon' => 'fa-users',			
			  'title' => __('Following', 'xoousers'),											
			  'visible' => 0 //not visible in user's dashboard
		);
		
		$tabs[3] =array(
			  'position' => 3,
			  'slug' => 'photos',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-camera',			
			  'title' => __('My Photos', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[4] =array(
			  'position' => 4,
			   'slug' => 'videos',
			  'slug_public' => 'fa-video-camera',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-video-camera',			
			  'title' => __('My Videos', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[5] =array(
			  'position' => 5,
			  'slug' => 'posts',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-edit',			
			  'title' => __('My Posts', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[6] =array(
			  'position' =>6,
			  'slug' => 'friends',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-users',			
			  'title' => __('My Friends', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[7] =array(
			  'position' =>7,
			  'slug' => 'topics',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-user',			
			  'title' => __('My Topics', 'xoousers'),											
			  'visible' => 0 //not visible in user's dashboard
		);
		
		$tabs[8] =array(
			  'position' =>8,
			  'slug' => 'messages',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-envelope-o',			
			  'title' => __('My Messages', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[9] =array(
			  'position' =>9,
			  'slug' => 'profile-customizer',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  
			  'icon' => 'fa-puzzle-piece',			
			  'title' => __('Profile Customizer', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[10] =array(
			  'position' =>10,
			  'slug' => 'wootracker',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-truck',			
			  'title' => __('My Purchases', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[11] =array(
			  'position' =>11,
			  'slug' => 'myorders',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-list',			
			  'title' => __('My Orders', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[12] =array(
			  'position' =>12,
			  'slug' => 'profile',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-user',			
			  'title' => __('My Profile', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[13] =array(
			  'position' =>13,
			  'slug' => 'account',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-wrench',			
			  'title' => __('Account', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[14] =array(
			  'position' =>14,
			  'slug' => 'settings',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-gear',			
			  'title' => __('Settings', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs[15] =array(
			  'position' =>15,
			  'slug' => 'logout',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-arrow-circle-right',			
			  'title' => __('Logout', 'xoousers'),											
			  'visible' => 1
		);
			
		ksort($tabs);
		
		
		/*User Profile Front Navigator*/				
		$tabs_front_nav = array();		
		$tabs_front_nav[1] =array(
			  'position' => 1,
			  'slug' => 'my_followers',			  
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-credit-card',			
			  'title' => __('FOLLOWERS', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs_front_nav[2] =array(
			  'position' => 2,
			  'slug' => 'my_following',			 
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-users',			
			  'title' => __('FOLLOWING', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs_front_nav[3] =array(
			  'position' => 3,
			  'slug' => 'my_galleries',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-camera',			
			  'title' => __('PHOTOS', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs_front_nav[4] =array(
			  'position' => 4,
			  'slug' => 'my_videos',
			  'slug_public' => 'fa-credit-card',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 0,			
			  'title' => __('VIDEOS', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs_front_nav[5] =array(
			  'position' => 5,
			  'slug' => 'my_posts',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-pencil-square-o',			
			  'title' => __('POSTS', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs_front_nav[6] =array(
			  'position' =>6,
			  'slug' => 'my_friends',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-eye',			
			  'title' => __('FRIENDS', 'xoousers'),											
			  'visible' => 1
		);
		
		$tabs_front_nav[7] =array(
			  'position' =>7,
			  'slug' => 'my_topics',
			  'link_type' => 'default',
			  'content' => '',
			  'content_type' => '',
			  'icon' => 'fa-user',			
			  'title' => __('TOPICS', 'xoousers'),											
			  'visible' => 1
		);
			
		ksort($tabs_front_nav);	
					
		//these are the default modules of UU Pro
		update_option('userultra_default_user_features', $tabs );	
			
		$this->mDefaultUserFeatures =$tabs;
		$this->mDefaultFrontNavigatorLinks =$tabs_front_nav;
		
		//USED TO BUILD MENUES ONLY - set the cutomized options available as menu options in the user's backend only if already not set
		if (!get_option('userultra_default_user_features_custom')) 
		{
			$modules_custom = get_option('userultra_default_user_features_added_admin');
			if(is_array($modules_custom))
			{
				
				 $tabs =  $tabs +$modules_custom;
			
			
			}			
			update_option('userultra_default_user_features_custom', $tabs );
		}
		
		//USED TO BUILD FRONT PROFILE NAVIGATOR ONLY 		
		if (!get_option('userultra_default_user_profile_front_navigator')) 
		{			
			update_option('userultra_default_user_profile_front_navigator', $tabs_front_nav );
		}
		
	}
	
	function uultra_rebuild_user_link()
	{
		delete_option( 'userultra_default_user_features_custom' );
		delete_option( 'userultra_default_user_profile_front_navigator' );		
	}
	
	
	function uultra_delete_default_bg_image()
	{
		delete_option( 'uultra_default_profile_bg' );
		
	}
	
	function sksort(&$array, $subkey="id", $sort_ascending=false) 
	{

		if (count($array))
        $temp_array[key($array)] = array_shift($array);

		foreach($array as $key => $val){
			$offset = 0;
			$found = false;
			foreach($temp_array as $tmp_key => $tmp_val)
			{
				if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
				{
					$temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
					$found = true;
				}
				$offset++;
			}
			if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
		}

		if ($sort_ascending) $array = array_reverse($temp_array);

		else $array = $temp_array;
	}
	
	/*This Function Change the order of the items in the user dashboard */	
	public function uultra_sort_user_menu_ajax() 
	{
		global $wpdb;
	
		$order = explode(',', $_POST['order']);
		$counter = 0;
		$new_pos = 10;
		
		$modules = get_option('userultra_default_user_features_custom');
		
		$new_fields = array();
		
		$fields_temp = $modules;
		//ksort($fields);
		
		foreach($modules as $key => $module)
		{			
			$fields_temp[$order[$counter]]["position"] = $new_pos;						
			$counter++;
			$new_pos=$new_pos+10;
		}
		
		$this->sksort($fields_temp,'position', true);	
		//print_r($fields_temp);			
		update_option('userultra_default_user_features_custom', $fields_temp);		
		die();
		
    }
	
	
	
		
	public function get_widget_appearance($widget_id)
	{
		
		$widget = 'uultra_widget_customing_'.$widget_id;		
		$customization = get_option($widget);		
		return unserialize($customization);
	}
	
	public function get_profile_customizing()
	{				
		$widget = 'uultra_profile_customizing';		
		$customization = get_option($widget);		
		return unserialize($customization);			
	}
	
	//set the default widget values &&
	public function set_default_widgets_layout($custom_user_id=null)
	{
		
		$user_id = get_current_user_id();
		
		if($user_id=="")
		{
			$user_id = $custom_user_id;				
		}
				
		$widgets_setup = get_user_meta($user_id,'uultra_profile_widget_setup', true);			
			
		if($widgets_setup=="" && $user_id!="")
		{
			
			update_user_meta ($user_id, 'uultra_profile_widget_col_1', array(1,3,8));
			update_user_meta ($user_id, 'uultra_profile_widget_col_2',  array(9,6));
			update_user_meta ($user_id, 'uultra_profile_widget_col_3',array(2,5));			
			update_user_meta ($user_id, 'uultra_profile_widget_setup', 'ok');
			
		}		
		
	
	}
	
		
	
	
	public function get_profile_widgets()
	{
		$widgets = get_option('userultra_default_user_tabs');
		
		$html = '';
		foreach($widgets as $key => $widget)
		{
			//check if is in column			
			if($this->check_if_active($key))
			{
				if(!$this->uultra_check_if_unused_widget($key))
				{
			
					$html .= '<li class="uultra-profile-widget ui-state-default rounded" id="'.$key.'"><a class="uultra-btn-widget-elements" href="#" widget-id="'.$key.'"><span><i class="fa '.$widget["icon"].' fa-2x"></i></span>'.$widget["title"].' <span class="uultra-widgets-icon-close-open" id="uultra-widgets-icon-close-open-id-'.$key.'" style="background-position: 0px 0px;" widget-id="'.$key.'"></span></a>';
					
					
					$html .= '<div id="uultra-widget-adm-cont-id-'.$key.'" class="uultra-widget-user-options-cont"  style="display:none">';
					//get widget options					
					$html .= $this->get_widget_customization_options($key);
					$html .= '</div>';
					
					$html .= '</li>';
					
				}
			
			}
		
		
		}
		
		
		
		return $html;
		
	}
	
	public function check_if_active($widget_id)
	{
		
		$widgets_col_1 = array();
		$widgets_col_2 = array();
		$widgets_col_3 = array();
		
		$user_id = get_current_user_id();
			
		$resp = false;
		$widgets_col_1_d = get_user_meta($user_id,'uultra_profile_widget_col_1', true);
		$widgets_col_2_d = get_user_meta($user_id,'uultra_profile_widget_col_2', true);
		$widgets_col_3_d = get_user_meta($user_id,'uultra_profile_widget_col_3', true);
		
		if(is_array($widgets_col_1_d)) $widgets_col_1 = $widgets_col_1_d;		
		if(is_array($widgets_col_2_d)) $widgets_col_2 = $widgets_col_2_d;		
		if(is_array($widgets_col_3_d)) $widgets_col_3 = $widgets_col_3_d;		
		
		
		//check in col 1
		
		if(!in_array($widget_id,$widgets_col_1) && !in_array($widget_id,$widgets_col_2) && !in_array($widget_id,$widgets_col_3))
		{
			$resp = true;
						
		}
				
		return $resp;
		
	}
	
	//get widgets by column private	
	public function get_profile_column_widgets($col)
	{
		
		$user_id = get_current_user_id();
				
		$col_widgets = 'uultra_profile_widget_col_'.$col;				
		$widgets = get_user_meta($user_id, $col_widgets, true);			
		
		//if display only 1 column we have to add the other's colum widgets		
		$cols = $this->get_amount_of_cols_by_template();
		
		if($col == 1 && $cols==1) // this is a one column template
		{
			$all_widgets = array();
			
			$widgets_col_2 = array();
			$widgets_col_3 = array();
			// we have to get the widgets in column 2 and 3			
			$col_to_get_2  ="uultra_profile_widget_col_2";
			$widgets_col_2 = $this->get_user_meta($col_to_get_2, $user_id );
			
			$col_to_get_3  ="uultra_profile_widget_col_3";
			$widgets_col_3= $this->get_user_meta($col_to_get_3, $user_id );
			
			
			if(is_array($widgets_col_2) && is_array($widgets_col_3))
			{
			
				$all_widgets = array_merge($widgets_col_2, $widgets_col_3);		
			}
			
			if(is_array($all_widgets) )
			{
					
				$widgets = array_merge($widgets, $all_widgets);		
		
			}
		}
		
		if($col == 2 && $cols==2) // this is a two column template
		{
			$widgets_col_3 = array();
						
			$col_to_get_3  ="uultra_profile_widget_col_3";
			$widgets_col_3= $this->get_user_meta($col_to_get_3, $user_id );	
			
			if(is_array($widgets_col_3) && is_array($widgets))
			{
				$widgets = array_merge($widgets, $widgets_col_3);		
			
			}
						
				
		}
		
		
			
		
		$html = '';
	
		if(count($widgets)>0 && is_array($widgets))
		{
			
			foreach($widgets as $key )
			{
				if(!$this->uultra_check_if_unused_widget($key))
				{
					$widget = $this->mWidgetsCol[$key];		
					
					
					$html .= '<li class="uultra-profile-widget ui-state-default rounded" id="'.$key.'">
					<a class="uultra-btn-widget-elements" href="#" widget-id="'.$key.'"><span><i class="fa '.$widget["icon"].' fa-2x"></i></span>'.$widget["title"].' <span class="uultra-widgets-icon-close-open" id="uultra-widgets-icon-close-open-id-'.$key.'" style="background-position: 0px 0px;" widget-id="'.$key.'"></span></a>';
					$html .= '<div id="uultra-widget-adm-cont-id-'.$key.'" class="uultra-widget-user-options-cont" style="display:none">';
					
					//get widget options					
					$html .= $this->get_widget_customization_options($key);
					
					$html .= '</div>';
					
					$html .= '</li>';
					
					
				
				}	
						
		
			}
		
		}	
		return $html;
		
	}
	
	//this gives us the widget options available for the users	
	function uultra_update_user_widget_customization()
	{
		global $xoouserultra;		
		
		$html ="";
		$user_id = get_current_user_id();
		
		$widget_id = $_POST["widget_id"];
		$widget_custom_text = $_POST["widget_custom_text"];
		$custom_user_optons = 'uultra_custom_user_widget_id_' .$widget_id;
		
	}
	
	//this gives us the widget options available for the users	
	function get_widget_customization_options($widget_id)
	{
		global $xoouserultra;		
		
		$html ="";
		$user_id = get_current_user_id();
		
		$custom_widgets = get_option('userultra_default_user_tabs');
		$widget = $custom_widgets[$widget_id];
		
		$widget_text = $widget["title"];
		$uu_type = $widget["type"];
		$uu_editable = $widget["editable"];
		$uu_content = $widget["content"];
		
		if($widget["native"]==0) //this is a custom widget
		{
			
			if($uu_editable ==1 && $uu_type == 1) //this is a text widget that can be edited by the user
			{
				$meta = 'uultra_user_widget_cont_edition_'.$widget_id;
				
				$html .='<div class="uultra-widget-int-edit-content">';
				$html .='<input type="submit" name="uultra-update-widget-custom-user-information" id="uultra-update-widget-custom-text" class="xoouserultra-button uultra-edit-widget-content-html-editor" value="Edit Content" widget-id="'.$widget_id.'">';
				
				
				//$html .='<input type="submit" name="uultra-update-widget-custom-user-information" id="uultra-update-widget-custom-text" class="xoouserultra-button uultra-update-widget-custom-user-information" value="Update" widget-id="'.$widget_id.'">';	
				
				$html .='</div>';
			
			}elseif($uu_editable ==0 && $uu_type == 1){ //this is a text widget that cannot be edited
			
				$html .='<div class="uultra-widget-int-edit-content">';
				$html .=''.$uu_content.'';
				$html .='</div>';
			
			}
			
		}else{ //this is a native widget
		
				
		}
		
		return $html;
		
	
	}
	
	function uultra_link_content_editor_html()
	{
		
		$meta = 'uultra_link_html_editor_content_';
		$html = '<div id="uultra-links-content-editor-box" class="uultra-link-content-edition-box " title="Edit Link Settings">';
		
		$html .= '<input name="uultra-current-selected-link-to-edit"  id="uultra-current-selected-link-to-edit" type="hidden" />';
		
		$html .= '<div class="uultra-field-msbox-div-history" id="uultra-msg-history-list">';
		
		$html .= $this->uultra_get_addlink_html_form($meta, $content);
		
		$html .= '</div>';			
		$html .= '</div>';		
			
		return $html;
	}
	
	
	function uultra_plugin_editor_form()
	{
		
		$meta = 'uultra_widget_html_editor_content_';
		$html = '<div id="uultra-plugin-settings-editor" class="uultra-widget-content-edition-box " title="Edit Widget Settings">';
		
		$html .= '<input name="uultra-current-selected-widget-to-edit"  id="uultra-current-selected-widget-to-edit" type="hidden" />';
		
		$html .= '<div class="uultra-field-msbox-div-history" id="uultra-msg-history-list">';
		
		$html .= $this->uultra_get_me_wphtml_editor($meta, $content);
		
		$html .= '</div>';			
		$html .= '</div>';		
			
		return $html;
	}
	
	//this si the form to edit user links
	function uultra_new_links_add_form()
	{
		
		$meta = 'uultra_link_content';
		$html = '<div id="#uultra-add-links-box" class="uultra-links-content-edition-box " title="Add New Link">';
		
		$html .= '<input name="uultra-current-selected-widget-to-edit"  id="uultra-current-selected-widget-to-edit" type="hidden" />';
		
		
		$html .= '<table width="100%" border="0" cellspacing="2" cellpadding="3">
			<tr>
				<td width="50%"> '.__("Name: ",'xoousers').'</td>
				<td width="50%"><input name="uultra_link_title" type="text" id="uultra_link_title"   /> 
		 </td>
		 </tr>
		 <tr>
				<td width="50%"> '.__("Slug: ",'xoousers').'</td>
				<td width="50%"><input name="uultra_link_slug" type="text" id="uultra_link_slug"   /> 
		 </td>
		 </tr>
		  <tr>
				<td width="50%"> '.__('Type:','xoousers').'</td>
				<td width="50%">
				<select name="uultra_link_type"  id="uultra_link_type" size="1">
				  <option value="" selected="selected">'.__("Select Type: ",'xoousers').'</option>
				  <option value="1">'.__("Text: ",'xoousers').'</option>
				  <option value="2">Shortocode</option>
				</select>

		 </td>
			  </tr>
					  
			  <tr>
				<td>'.__('Content:','xoousers').'</td>
				<td>&nbsp;</textarea> 
			   </td>
			  </tr>
			
			 
			</table>     ';       			
		
		
		
		
		$html .= '<div class="uultra-field-msbox-div-history" id="uultra-msg-history-list">';
		
		$html .= $this->uultra_get_addlink_html_form($meta, $content);
		
		$html .= '</div>';	
		
		
			          
		$html .= ' <p class="submit">
					<input type="button" name="submit"  class="button  uultra-links-add-new-close"   value="'.__('Close','xoousers').'"  />  <input type="button" name="submit"  class="button button-primary uultra-links-add-new-confirm"   value="'.__('Submit','xoousers').'"  /> <span id="uultra-add-new-links-m-w" ></span>
				</p> ';		
		$html .= '</div>';		
			
		return $html;
	}
	
	
	
	//this saves the custom content for this user in the widget	
	function uultra_edit_widget_content_confirm()
	{
		global $xoouserultra;		
		
		$html =__('Success!','xoousers');
		$user_id = get_current_user_id();
		
		$widget_id = $_POST["widget_id"];
		$widget_custom_text = $_POST["widget_data"];
		$custom_user_options = 'uultra_custom_user_widget_content_id_' .$widget_id;
		
		update_user_meta($user_id, $custom_user_options, $widget_custom_text);
		
		echo $html ;		
		
		die();
			
	}
	
	//this retreives the widget content
	function uultra_edit_widget_content()
	{
		$html = '';
		
		$user_id = get_current_user_id();		
		$widget_id = $_POST["widget_id"];
		
		$custom_widgets = get_option('userultra_default_user_tabs');
		$widget = $custom_widgets[$widget_id];
		
		$widget_text = $widget["title"];
		$uu_type = $widget["type"];
		$uu_editable = $widget["editable"];
		$uu_content = $widget["content"];
		
		//check if custom text has been set for this widget		
		if($uu_editable ==1 && $uu_type == 1) //this is a text widget that can be edited by the user
		{
			$custom_user_options = 'uultra_custom_user_widget_content_id_' .$widget_id;			
			//check if uses has set a custom text
			$custom_text = get_user_meta($user_id, $custom_user_options, true);
			
			if($custom_text!="")
			{
				$uu_content = $custom_text;
			
			}
		
		}
		
		
		
		$html .= $uu_content;
		echo $html;
		die();
			
	}
	
	function uultra_get_user_links_html_editor($meta, $content)
	{
		// Turn on the output buffer
		ob_start();
		
		$editor_id = $meta;				
		$editor_settings = array('media_buttons' => false , 'textarea_rows' => 40 , 'teeny' =>true,   'tinymce' => array( 'height' => 300 , 'quicktags' => false)); 
							
					
		wp_editor( $content, $editor_id , $editor_settings);
		
		// Store the contents of the buffer in a variable
		$editor_contents = ob_get_clean();
		
		// Return the content you want to the calling function
		return $editor_contents;
			
	}
	
	function uultra_get_addlink_html_form($meta, $content)
	{
		// Turn on the output buffer
		ob_start();
		
		$editor_id = $meta;				
		$editor_settings = array('media_buttons' => false , 'textarea_rows' => 40 , 'teeny' =>true,   'tinymce' => array( 'height' => 300 ), 'quicktags' => false); 
							
					
		wp_editor( $content, $editor_id , $editor_settings);
		
		// Store the contents of the buffer in a variable
		$editor_contents = ob_get_clean();
		
		// Return the content you want to the calling function
		return $editor_contents;
			
	}
	
	function uultra_get_me_wphtml_editor($meta, $content)
	{
		// Turn on the output buffer
		ob_start();
		
		$editor_id = $meta;				
		$editor_settings = array('media_buttons' => false , 'textarea_rows' => 40 , 'teeny' =>true,   'tinymce' => array( 'height' => 300 )); 
							
					
		wp_editor( $content, $editor_id , $editor_settings);
		
		// Store the contents of the buffer in a variable
		$editor_contents = ob_get_clean();
		
		// Return the content you want to the calling function
		return $editor_contents;
			
	}
	
	
	
	function save_pro_widgets()
	{
		global $wpdb;
		$user_id = get_current_user_id();	
		
		$order_col_1 = "";
		$order_col_2 = "";
		$order_col_3 = "";	
		
		$order_col_1 = $_POST["order_col_1"];		
		if($order_col_1!="" && $order_col_1 !="[object Object]")
		{
			$order_col_1 = explode(',',$order_col_1);
		
	    }
		
		$order_col_2 = $_POST["order_col_2"];		
		if($order_col_2!="" && $order_col_2 !="[object Object]")
		{
			$order_col_2 = explode(',',$order_col_2);
		}	
		
		
		$order_col_3 = $_POST["order_col_3"];
		if($order_col_3!="" && $order_col_3 !="[object Object]")
		{
			$order_col_3 = explode(',',$order_col_3);
								
		}else{
			$order_col_3 = "";
		
		
		}		
		
		update_user_meta ($user_id, 'uultra_profile_widget_col_1', $order_col_1);
		update_user_meta ($user_id, 'uultra_profile_widget_col_2', $order_col_2);
		update_user_meta ($user_id, 'uultra_profile_widget_col_3', $order_col_3);
		
		die(1);
		
	}
	
	//this gives us the amount of cols for each template	
	function get_amount_of_cols_by_template()
	{
		global $xoouserultra;
		
		$current_template = $this->get_default_profile_template();
		
		if($current_template==1 || $current_template=="") // 3 columns
		{
			$cols = 3;		
		}
		
		if($current_template==3) //two cols
		{
			$cols = 2;
		}
		
		if($current_template==4) //one column
		{
			$cols = 1;
		}
		
		return $cols;	
	}
	
	//this function displays the 3 columns in the user's dahsboard
	function  get_customizer_columns_user ()	
	{
		
		global $xoouserultra;
		
		$cols = $this->get_amount_of_cols_by_template();
		
		$html = '';
		
		
		 $dimension_style = $xoouserultra->userpanel->get_width_of_column($cols);
				
		if($cols==1 || $cols==2 || $cols==3)
		{
		
			$html .= ' <div class="col1" '. $dimension_style.'>    
						<h3 class="colname_widget">'.__('Column 1','xoousers').'</h3>                           
						 <ul class="droptrue" id="uultra-prof-customizar-1">
									 
						   '.$this->get_profile_column_widgets(1).'
									
							</ul>                               
					  </div>';
		}
				  
		if($cols==2 || $cols==3)
		{
		
			$html .=' <div class="col2" '. $dimension_style.'>  
						<h3 class="colname_widget">'. __('Column 2','xoousers').'</h3>                             
						  <ul class="droptrue" id="uultra-prof-customizar-2">
									   
							  '.$this->get_profile_column_widgets(2).'
						   </ul>
								   
						</div>';
		}			
					
		if($cols==3)
		{
		
			$html .='<div class="col3" '. $dimension_style.'> 
								   
					   <h3 class="colname_widget">'.__('Column 3','xoousers').'</h3>                        
						   <ul class="droptrue" id="uultra-prof-customizar-3">
								  '.$this->get_profile_column_widgets(3).'
							 </ul>
								   
					 </div>';
		}
					
					
		$html .= $this->uultra_plugin_editor_form();		   
		
		return $html;
	
	
	}
	
	public function  get_profile_col_widgets ($user_id, $col, $cols, $atts)	
	{
		
		require_once(ABSPATH . 'wp-includes/user.php');
		
		extract( shortcode_atts( array(		
			
			'pic_type' => 'avatar', // display either avatar or main picture of the user
			'pic_boder_type' => 'none', // rounded
			'pic_size_type' => 'fixed', // dynamic or fixed	
			'pic_size' => 120, // size in pixels of the user's picture	
			
		), $atts ) );
		
		$col_to_get  ="uultra_profile_widget_col_". $col;
		
		$html = "";
		
		$widgets = $this->get_user_meta($col_to_get, $user_id );		
		
		if($col == 1 && count($cols)==1) // this is a one column template
		{
			$all_widgets = array();
			// we have to get the widgets in column 2 and 3			
			$col_to_get_2  ="uultra_profile_widget_col_2";
			$widgets_col_2 = $this->get_user_meta($col_to_get_2, $user_id );
			
			$col_to_get_3  ="uultra_profile_widget_col_3";
			$widgets_col_3= $this->get_user_meta($col_to_get_3, $user_id );
			
			if(is_array($widgets_col_2) && is_array($widgets_col_3))
			{
			
				$all_widgets = array_merge($widgets_col_2, $widgets_col_3);	
			}
			
			if(is_array($all_widgets) )
			{
				$widgets = array_merge($widgets, $all_widgets);		
			}
		
		}
		
		if($col == 2 && count($cols)==2) // this is a two column template
		{
			$all_widgets = array();
						
			$col_to_get_3  ="uultra_profile_widget_col_3";
			$widgets_col_3= $this->get_user_meta($col_to_get_3, $user_id );	
			
			if(is_array($widgets_col_3) )
			{		
			
				$widgets = array_merge($widgets, $widgets_col_3);	
			
			
			}		
		}			
					
		
		//print_r($widgets);
		
		if(count($widgets)>0 && is_array($widgets))
		{
			
			foreach ($widgets as $id)
			{
				//check if active module
				if($this->user_front_widgets_disabled_my_module($user_id, $id))
				{
					if(!$this->uultra_check_if_unused_widget($id))
					{						
						
						if($id==1) //basic information
						{
							
							$html .= $this->get_profile_info($user_id);
							
						}elseif($id==2){ // My Friends
							
							$html .= $this->uultra_get_my_friends($user_id, 10);						
						
						}elseif($id==3){ // My Photos
							
							$html .= $this->uultra_get_latest_photos($user_id, 8);
						
						}elseif($id==4){ // My Galleries
							
							$html .= $this->uultra_get_latest_gallery_widget($user_id, 8);	
						
						}elseif($id==5){ // My latest posts
							
							$html .= $this->uultra_get_my_posts($user_id, 10);	
							
						
						}elseif($id==6){ // My followers
						
							$html .= $this->uultra_get_my_followers($user_id, 10);
							
								
						}elseif($id==7){ // My Card
							
							//$html .= $this->uultra_get_card($user_id, $pic_size, $pic_type, $pic_boder_type,  $pic_size_type);
							
						
						}elseif($id==8){ // My Videos
							
							$html .= $this->uultra_get_latest_videos($user_id, 4);
						
						}elseif($id==9){ // My Wall
							
							$html .= $this->uultra_get_wall($user_id, 5);
						
						}elseif($id==10){ // My Bio
							
							$html .= $this->uultra_get_my_bio($user_id);
							
						}else{ // My Custom Widgets
						
							
							//in this section we have to apply special rules for custom widgets						
							$html .= $this->uultra_custom_widgets_actions($user_id, $id);
						
									
						
						
						}
					
					
					} //endif if deactivated by admin
				
				} //endif if deactivated by module
					
				
			
			} //end for each
		} //end if
		return $html;
	
	}
	
	//this will give us teh custom widget with the custom information	
	public function uultra_custom_widgets_actions ($user_id, $widget_id)	
	{
		//		
		$custom_widgets = get_option('userultra_custom_user_widgets');
		$widget = $custom_widgets[$widget_id];
		
		$widget_text = $widget["title"];
		$uu_type = $widget["type"];
		$uu_editable = $widget["editable"];
		$uu_content = $widget["content"];
		
		$header_css = $this->get_widget_header_inline_css($widget_id);
		$widget_bg_css = $this->get_widget_bg_inline_css($widget_id);
		$widget_font_color_css = $this->get_widget_box_color_css($widget_id);				
		$widget_text = $this->get_widget_header_text($widget_id);	
		
		if($widget_text=="")
		{
			$widget_text = $widget["title"];		
		}
		
		$html = '';
		
		$html .=' <li><!--- custom widget--->                 
                  <div class="widget-ultra"   >
                    <h3 class="uultra-basic" '.$header_css .' >'.$widget_text.'</h3>
                     <section class="default-bg uultra-profile-widget-arrow-'.$widget_id.' " ></section>
                     <div class="uultra-table-widget-custom" '.$widget_bg_css.'>';
					 
							 
				if($uu_type==1) //text
				{
					
					//check if custom text has been set for this widget		
					if($uu_editable ==1 ) //this is a text widget that can be edited by the user
					{						
						$custom_user_options = 'uultra_custom_user_widget_content_id_' .$widget_id;			
						//check if uses has set a custom text
						$custom_text = get_user_meta($user_id, $custom_user_options, true);
						
						if($custom_text!="")
						{
							$html .= $custom_text;
						
						}else{
							
							$html .= $uu_content;						
						
						}
					
					}else{						
						
						$html .= $uu_content;					
					}
					
					
					
				}elseif($uu_type==2){ //shortcode
				
					$html .= do_shortcode($uu_content);
				
				
				}
		$html .= '</div>                               
                   </div>              
                  </li><!--- End widget 1 --->';
		return $html;
	
	}
	
	public function get_widget_header_inline_css ($id)	
	{
		$widget = array();
		$widget = $this->get_widget_appearance($id);
		
		$css = '';		
		$css = ' style=" ';
		
				
		if($widget['widget_header_bg_color']!="")
		{
			$css .= 'background-color:'.$widget['widget_header_bg_color'].' !important; ';		
		}
		
		if($widget['widget_header_text_color']!="")
		{
			$css .= 'color:'.$widget['widget_header_text_color'].' !important; ';		
		}
		
		$css .= ' " ';
		
		return $css;
	
	}
	
	public function  get_widget_bg_inline_css ($id)	
	{
		$widget = array();
		$widget = $this->get_widget_appearance($id);		
				
		$css = ' style=" ';				
		if($widget['widget_bg_color']!="")
		{
			$css .= 'background-color:'.$widget['widget_bg_color'].' !important; ';		
		}		
				
		$css .= ' " ';		
		return $css;
	
	}
	
	public function  get_widget_bg_inline_arrows_css ($id, $type='default')	
	{
		$widget = array();
		$widget = $this->get_widget_appearance($id);		
				
		$css = '';
		
		if($type=='default')
		{	
					
			if($widget['widget_header_bg_color']!="")
			{
				$css .= ' .uultra-profile-widget-arrow-'.$id.':before {background-color:'.$widget['widget_header_bg_color'].' !important} ';		
			}
		
		}elseif($type=='custom'){
			
			
			if($widget['widget_header_bg_color']!="")
			{
				$css .= '.uultra-profile-widget-arrow-'.$id.':after {	top: -50px;z-index: 10;	background: inherit;}';
				$css .= '.uultra-profile-widget-arrow-'.$id.':before {left: 50%;	width: 50px;	height: 50px;	transform: translateX(-50%) rotate(45deg) !important;	-webkit-transform: translateX(-50%) rotate(45deg);	top: -50px;	background: none repeat scroll 0% 0% #24afb2;}';
				
				$css .= ' .uultra-profile-widget-arrow-'.$id.':before {background-color:'.$widget['widget_header_bg_color'].' !important} ';		
			}
			
		}
				
		return $css;
	
	}
	
	
	public function  get_widget_box_color_css ($id)	
	{
		$widget = array();
		$widget = $this->get_widget_appearance($id);		
		
		$css = ' style=" ';				
				
		if($widget['widget_text_color']!="")
		{
			$css .= 'color:'.$widget['widget_text_color'].' !important; ';		
		}
		
		$css .= ' " ';
		
		return $css;
	
	}
	
	public function  get_widget_header_text ($id)	
	{
		$widget = array();
		$widget = $this->get_widget_appearance($id);
		
		$title =$widget['widget_title'];	
		
				
		if($id==1) //basic information
		{
			if($title=="")
			{
				$title = __("Basic Information","xoousers");			
			}
			
		}elseif($id==2){ // My Friends
			
			if($title=="")
			{
				$title = __( "My Friends","xoousers");			
			}
		
		}elseif($id==3){ // My Photos
			
			if($title=="")
			{
				$title = __( "My Photos","xoousers");			
			}
		
		}elseif($id==4){ // My Galleries
			
			if($title=="")
			{
				$title = __( "My Galleries","xoousers");			
			}	
		
		}elseif($id==5){ // My latest posts
			
			if($title=="")
			{
				$title = __( "My Latest Posts","xoousers");			
			}			
		
		}elseif($id==6){ // My followers
		
			if($title=="")
			{
				$title = __( "My Followers","xoousers");			
			}
			
				
		}elseif($id==7){ // My Card
			
			//$html .= $this->uultra_get_card($user_id, $pic_size, $pic_type, $pic_boder_type,  $pic_size_type);
			
		
		}elseif($id==8){ // My Videos
			
			if($title=="")
			{
				$title = __( "My Videos","xoousers");			
			}
		
		}elseif($id==9){ // My Wall
			
			if($title=="")
			{
				$title = __( "My Wall","xoousers");			
			}
		
		}elseif($id==10){ // My Bio
			
			if($title=="")
			{
				$title = __( "About / Bio","xoousers");			
			}	
		
		
		}
		
			
	
		return $title;
	
	}
	
	public function  get_profile_info ($user_id)	
	{
		global $xoouserultra ;	
		
		//get user form		
		$custom_form = $this->get_user_meta( 'uultra_custom_registration_form', $user_id); 
		
		if($custom_form!="")
		{
			
			$custom_form = 'usersultra_profile_fields_'.$custom_form;		
			$array = get_option($custom_form);
		
		}else{			
			
			$array = get_option('usersultra_profile_fields');			
		
		}
		
		
		
		//$array = get_option('usersultra_profile_fields');

		foreach($array as $key=>$field) 
		{
		    // Optimized condition and added strict conditions 
		    $exclude_array = array('user_pass', 'user_pass_confirm', 'user_email');
		    if(isset($field['meta']) && in_array($field['meta'], $exclude_array))
		    {
		        unset($array[$key]);
		    }
		}
		
		$i_array_end = end($array);
		
		if(isset($i_array_end['position']))
		{
		    $array_end = $i_array_end['position'];
		    if ($array[$array_end]['type'] == 'separator') {
		        unset($array[$array_end]);
		    }
		}
		
		$header_css = $this->get_widget_header_inline_css(1);
		$widget_bg_css = $this->get_widget_bg_inline_css(1);
		$widget_font_color_css = $this->get_widget_box_color_css(1);		
		$widget_text = $this->get_widget_header_text(1);	
		
				
		
		$html .= ' <li><!--- widget 1 column 1--->                 
                  <div class="widget-ultra"   >
                    <h3 class="uultra-basic" '.$header_css .' >'.$widget_text.'</h3>
                     <section class="default-bg uultra-profile-widget-arrow-1 " ></section>
                     <div class="uultra-table" '.$widget_bg_css.'>';
		
	
		foreach($array as $key => $field) 
		{
			$show_to_user_role = 0;	
			$show_to_user_role_list = '';

			extract($field);
			
			
			if(!isset($private))
			    $private = 0;
			
			if(!isset($show_in_widget))
			    $show_in_widget = 1;
				
			
			
			/* Fieldset separator */
			if ( $type == 'separator' && $deleted == 0 && $private == 0  && $show_in_widget == 1) 
			{
				if(!isset($show_to_user_role) || $show_to_user_role =="")
				{
					$show_to_user_role = 0;			
				}
				
				if(!isset($show_to_user_role_list) || $show_to_user_role_list =="")
				{
					$show_to_user_role_list = '';	
					
				}	 
			 
				 $xoouserultra->role->uultra_get_user_roles_by_id($user_id);
				 $show_field_status =  $xoouserultra->role->uultra_fields_by_user_role($show_to_user_role, $show_to_user_role_list);
				 
				 if($show_field_status)
				 {
					 $html .= '<div class="uultra-profile-seperator" '.$widget_font_color_css.'>'.$name.'</div>';
					
				 }
				 
				
				
			}
			
			if ( $type == 'usermeta' && $deleted == 0 && $private == 0 && $show_in_widget == 1)			
			{
				
				if(!isset($show_to_user_role) || $show_to_user_role =="")
				{
					$show_to_user_role = 0;			
				}
				
				if(!isset($show_to_user_role_list) || $show_to_user_role_list =="")
				{
					$show_to_user_role_list = '';	
					
				}	 
			 
				 $xoouserultra->role->uultra_get_user_roles_by_id($user_id);
				 $show_field_status =  $xoouserultra->role->uultra_fields_by_user_role($show_to_user_role, $show_to_user_role_list);
				
				
				if($show_field_status)
				{
										
					/* Show the label */
					if (isset($array[$key]['name']) && $name)
					{
						$html .= ' <span class="data-a" '.$widget_font_color_css.'>'.$name.':</span><span class="data-b" '.$widget_font_color_css.'>'.$this->get_user_meta( $meta, $user_id).'</span> ';
					}
				
				}
			
			}
				 	
				
			
		}
		
		$html .= '</div>                               
                   </div>              
                  </li><!--- End widget 1 --->';
		return $html;
		
	}
	
	
	
	public function get_user_meta ($meta, $user_id)
	{
		return get_user_meta( $user_id, $meta, true);
		
	}
	
	
	//this is the bio widget	
	public function  uultra_get_my_bio ($user_id)	
	{
		
		
		$array = get_option('usersultra_profile_fields');

		
		$header_css = $this->get_widget_header_inline_css(10);
		$widget_bg_css = $this->get_widget_bg_inline_css(10);
		$widget_font_color_css = $this->get_widget_box_color_css(10);		
		$widget_text = $this->get_widget_header_text(10);	
		
		$meta = 'description';
				
		
		$html .= ' <li>               
                  <div class="widget-ultra"   >
                    <h3 class="uultra-basic" '.$header_css .' >'.$widget_text.'</h3>
                     <section class="default-bg uultra-profile-widget-arrow-10 " ></section>
                     <div class="uultra-table" '.$widget_bg_css.'>';
		
	
		$html .= ' <p class="uultra-commmon-text"  '.$widget_font_color_css.'>'.nl2br($this->get_user_meta( $meta, $user_id)).'</p> ';
				
				
			
		
		$html .= '</div>                               
                   </div>              
                  </li><!--- End widget 1 --->';
		return $html;
		
	}
	
		
	
	                 
	
	//get my latest posts
	public function uultra_get_my_posts($user_id, $howmany)
	{
		global $wpdb, $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		require_once(ABSPATH . 'wp-includes/post-thumbnail-template.php');
		require_once(ABSPATH . 'wp-includes/comment.php');
		
		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
			
		
		$dr = $xoouserultra->publisher->show_my_posts_widget($user_id, $howmany);
		
		
		$header_css = $this->get_widget_header_inline_css(5);
		$widget_bg_css = $this->get_widget_bg_inline_css(5);
		$widget_font_color_css = $this->get_widget_box_color_css(5);
		$widget_text = $this->get_widget_header_text(5);
		
		$html .= ' <li><!--- widget 2 column 3--->                  
                   <div class="widget-ultra">
                    <h3 '.$header_css.'>'.$widget_text.'</h3>
                    <section class="default-bg uultra-profile-widget-arrow-5"></section>  
                     <div class="uultra-latest-posts" '.$widget_bg_css.'>
                       <ul>';
					   
		
		foreach ( $dr as $row )
		{
			
			$permalink = get_permalink( $row->ID ); 
			
			$thumb = get_the_post_thumbnail($row->ID, 'thumbnail');
			$comment_count = wp_count_comments($row->ID);			
			$desc = $this->get_excerpt_by_id($row->post_content, 15);
					
											   
					   
         $html .= '      <li>
                           <span><a href="'.$permalink.'">'.$thumb  .'</a></span>
                           <div class="uultra-latest-descrip">
                            <p class="uultra-tit"><a href="'.$permalink.'" '.$widget_font_color_css.'>'.$row->post_title.'</a></p>
                            <p class="uultra-date">'.date("m/d/Y",strtotime($row->post_date)).'</p>
                            <p class="uultra-text" '.$widget_font_color_css.'>'.$desc.'</p>
                           </div> 
                            <div class="uultra-comment-icons">
                             <div class="uultra-more-icons"><a href="'.$permalink.'#comments"><i class="fa fa-lg fa-comments-o  uultra-small-icon"></i>'.$comment_count->approved .'</a></div>
                             <div class="uultra-more-icons"><i class="fa fa-lg  fa-star  uultra-small-icon"></i></div>                             
                            </div>
                         </li>';
         } 
		 
		                  
                          
        $html .='               </ul>
                     </div>         
                  </div>
                 </li>  ';
		
		
					 
		
		
				 
	return  $html;			 
		
	}
	
	function get_excerpt_by_id($the_excerpt,$excerpt_length)
	{
		$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
		$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	
		if(count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words, '… ');
			$the_excerpt = implode(' ', $words);
		endif;
	
		$the_excerpt = '' . $the_excerpt . '';
	
		return $the_excerpt;
	}
	
	//get my friends
	public function uultra_get_my_friends($user_id, $howmany)
	{
		global $wpdb, $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 	
		$dr = $xoouserultra->social->get_friends_widget($user_id, $howmany);
		
		$header_css = $this->get_widget_header_inline_css(2);
		$widget_bg_css = $this->get_widget_bg_inline_css(2);
		$widget_font_color_css = $this->get_widget_box_color_css(2);
		$widget_text = $this->get_widget_header_text(2);
		
		
		$html .= '
		                 <li><!--- widget 1 column 3--->                  
                   <div class="widget-ultra">
                    <h3 class="uultra-gnral" '.$header_css .'>'.$widget_text.'</h3>
                    <section class="default-bg uultra-profile-widget-arrow-2"> </section> 
                     <div class="uultra-friends" '.$widget_bg_css.'>
                      <ul>';
					foreach ( $dr as $row )
		            {
						
						$friend_sender_user_id = $row->friend_sender_user_id;

						$request_id = $row->friend_id;	
						
						$u_meta_country = get_user_meta($friend_sender_user_id, 'country', true);					
						
					  
                    $html .='    <li><span>'. $xoouserultra->userpanel->get_user_pic( $friend_sender_user_id, 50, 'avatar', 'rounded', 'dynamic' ).'</span>
                          <div class="uultra-info">
                           <p class="uultra-name" '.$widget_font_color_css.'>'.$xoouserultra->userpanel->get_display_name($friend_sender_user_id).'</p>
                           <p class="uultra-profession" '.$widget_font_color_css.'>'.$u_meta_country.'</p>
                          </div>
                        </li>';
                        }  
                         
               $html .= '       </ul>
                     </div>                
                   </div> 
                  </li>';
		
					 
		
		
				 
	return  $html;			 
		
	}
	
		//get my friends
	public function uultra_get_my_followers($user_id, $howmany)
	{
		global $wpdb, $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
			
		
		$dr = $xoouserultra->social->get_followers_widget($user_id, $howmany);
		
		$header_css = $this->get_widget_header_inline_css(6);
		$widget_bg_css = $this->get_widget_bg_inline_css(6);
		$widget_font_color_css = $this->get_widget_box_color_css(6);
		$widget_text = $this->get_widget_header_text(6);
		
		
		$html .= '
		                 <li><!--- widget 1 column 3--->                  
                   <div class="widget-ultra">
                    <h3 class="uultra-gnral" '.$header_css.'>'.$widget_text.'</h3>
                    <section class="default-bg uultra-profile-widget-arrow-6"> </section> 
                      <div class="uultra-followers" '.$widget_bg_css.'>
                      <ul>';
					foreach ( $dr as $row )
		            {
						
						$uid = $row->follower_following_user_id;
						$request_id = $row->friend_id;	
						
						$u_meta_country = get_user_meta($uid, 'country', true);
						
						
					  
                    $html .='    <li><span>'. $xoouserultra->userpanel->get_user_pic( $uid, 50, 'avatar', 'rounded', 'fixed' ).'</span>
                          <div class="uultra-info-follow">
                           <p class="uultra-name-follow" '.$widget_font_color_css.'>'.$xoouserultra->userpanel->get_display_name($uid).'</p>                <p class="uultra-count-follow" '.$widget_font_color_css.'>'.$u_meta_country.'</p>
                          </div>
                        </li>';
                        }  
                         
               $html .= '       </ul>
                     </div>                
                   </div> 
                  </li>';
		
					 
		
		
				 
	return  $html;			 
		
	}
	
	//get latest videos widgets	
	public function uultra_get_latest_videos($user_id, $howmany)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
			
		
		$dr = $xoouserultra->photogallery->get_videos_public_widget($user_id, $howmany);
		
		$header_css = $this->get_widget_header_inline_css(8);
		$widget_bg_css = $this->get_widget_bg_inline_css(8);
		$widget_font_color_css = $this->get_widget_box_color_css(8);
		$widget_text = $this->get_widget_header_text(8);
		
		$html = "";
		$html .= '<li><!--- widget 3 column 1 ---> 
                   <div class="widget-ultra">
                    <h3 '.$header_css.'>'.$widget_text.'</h3>
                    <section class="default-bg uultra-profile-widget-arrow-8"></section>';
					
		foreach ( $dr as $video )
		{
				   
			$html .='	    <div class="uultra-video" '.$widget_bg_css .'> ';
			
			
                        
						
				switch($video->video_type): case "youtube":
                        
						$html .= '<iframe width="100%" src="http://www.youtube.com/embed/'.$video->video_unique_vid.'?autohide=1&modestbranding=1&showinfo=0" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen '.$widget_bg_css .'></iframe>';
						
						
							
					 break; 
					 
					 case "vimeo": 
					 
							$html .= '<iframe src="http://player.vimeo.com/video/'.$video->video_unique_vid.'?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff" width="100%"  frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen '.$widget_bg_css .'></iframe>';
					
					
					 case "embed": 
					 
							$html .= stripslashes($video->video_unique_vid);
							
							
							
							
					 endswitch;		
						
			$html .=' </div>';
						
			
			
			   
		   }
                                       
		
		
		 $html .= '				             
                    </div>              
                 </li><!--- End widget 3 ---> ';				 
		
		
				 
	return  $html;			 
		
	}
	
	//get latest videos widgets	
	public function uultra_get_wall($user_id, $howmany)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');		
		$site_url = site_url()."/";
		
		$header_css = $this->get_widget_header_inline_css(9);
		$widget_bg_css = $this->get_widget_bg_inline_css(9);
		$widget_font_color_css = $this->get_widget_box_color_css(9);
		$widget_text = $this->get_widget_header_text(9);
				

		$html = "";
		
		$html .= ' <li><!--- widget 1 column 1--->                 
                  <div class="widget-ultra">
                    <h3 class="uultra-basic" '.$header_css .'>'.$widget_text.'</h3>
                     <section class="default-bg uultra-profile-widget-arrow-9"></section>
                     <div class="uultra-table" id="uultra-wall-messages" '.$widget_bg_css.'>';
			 
		
	   $html .= '</div>                               
                   </div>              
                  </li><!--- End widget 1 --->';
				 
	return  $html;			 
		
	}
	
	//get latest galleries widgets	
	public function uultra_get_latest_gallery_widget($user_id, $howmany)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
			
		
		$dr = $xoouserultra->photogallery->get_latest_gallery_public($user_id, $howmany);
		
		
		$header_css = $this->get_widget_header_inline_css(4);
		$widget_bg_css = $this->get_widget_bg_inline_css(4);
		$widget_font_color_css = $this->get_widget_box_color_css(4);
		$widget_text = $this->get_widget_header_text(4);
		
		$html = "";
		
		 $html .= '<li><!--- widget 2 column 1--->                
                   <div class="widget-ultra">
                    <h3 class="uultra-gnral" '.$header_css.'>'.$widget_text.'</h3>
                    <section class="default-bg uultra-profile-widget-arrow-4"> 
					
                      <div class="uultra-latest-gall-widgets" '.$widget_bg_css.'>					  
                       <ul>';
					   
		       //get my latest photos
	           foreach ( $dr as $gall )
			   {
				   
				   //get main picture
					$thumb = $xoouserultra->photogallery->get_main_picture_public($gall->gallery_id);
					
					//get amount of pictures
					$amount_pictures = $xoouserultra->photogallery->get_total_pictures_of_gal($gall->gallery_id);
				   
				  $html.= "<li>";		
				  $html.= " <a href='".$xoouserultra->userpanel->public_profile_get_album_link($gall->gallery_id, $user_id)."' class=''  ><img src='".$thumb."' class='rounded' /> </a> ";
				  
				  $html.= "<p class='usersultra-amount_pictures'>".$amount_pictures." ".__( 'Picture(s)', 'xoousers' )."</p>				
					<p class='galdesc'>".$gall->gallery_desc."</p>";
				  
				  $html.= "</li>";
			   
			   }
           
		     $html .= '            </ul>
                      </div>              
                    </section>
                  </div> 
                 </li><!--- End widget 2 --->';
				 
				 
	return  $html;			 
		
	}
	
	
	
	
	//get latest photos widgets	
	public function uultra_get_latest_photos($user_id, $howmany)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
			
		
		$dr = $xoouserultra->photogallery->get_latest_photos_public($user_id, $howmany);
		
		
		$header_css = $this->get_widget_header_inline_css(3);
		$widget_bg_css = $this->get_widget_bg_inline_css(3);
		$widget_font_color_css = $this->get_widget_box_color_css(3);
		$widget_text = $this->get_widget_header_text(3);
		
		$html = "";
		
		 $html .= '<li><!--- widget 2 column 1--->                
                   <div class="widget-ultra">
                    <h3 class="uultra-gnral" '.$header_css.'>'.$widget_text.'</h3>
                    <section class="default-bg uultra-profile-widget-arrow-3"> 
                      <div class="uultra-latest-photo" '.$widget_bg_css.'>					  
                       <ul>';
					   
		       //get my latest photos
	           foreach ( $dr as $photo )
			   {
				   
				   $file=$photo->photo_thumb;		
						
				   $thumb = $site_url.$upload_folder."/".$user_id."/".$file;
				
				   $html .= '<li><a href="'.$xoouserultra->userpanel->public_profile_get_photo_link($photo->photo_id, $user_id).'"><img src="'.$thumb.'" class="rounded"/> </a></li>'  ;  
			   
			   }
           
		     $html .= '            </ul>
                      </div>              
                    </section>
                  </div> 
                 </li><!--- End widget 2 --->';
				 
				 
	return  $html;			 
		
	}
	
	public function get_profile_bg($user_id)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";
		
		$html = "";
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder');		
		$user_pic = get_user_meta($user_id, 'user_profile_bg', true);
		
		
		if($user_pic!="")
		{
			$src = $site_url.$upload_folder.'/'.$user_id.'/'.$user_pic;			
			$html .= '<img src="'.$src.'" />';
			$html .= '<div><a href="#" id="uultra-remove-profile-bg" ><img src="'.xoousers_url.'templates/'.xoousers_template.'/img/remove_p.png" class="uultra-remove-bg-ico" title="'.__('Remove image','xoousers').'" alt="'.__('Remove image','xoousers').'" /></a></div>';		
			
		} 
		
		
		return $html;
	
	
	}
	
	public function get_profile_bg_refresh()
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$user_id = get_current_user_id();		
		$site_url = site_url()."/";		
		$html = "";
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder');		
		$user_pic = get_user_meta($user_id, 'user_profile_bg', true);
		
		
		if($user_pic!="")
		{
			$src = $site_url.$upload_folder.'/'.$user_id.'/'.$user_pic;			
			$html .= '<img src="'.$src.'" />';
			$html .= '<div><a href="#" id="uultra-remove-profile-bg" ><img src="'.xoousers_url.'templates/'.xoousers_template.'/img/remove_p.png" class="uultra-remove-bg-ico" title="'.__('Remove image','xoousers').'" alt="'.__('Remove image','xoousers').'" /></a></div>';
		
			
		} 		
		
		echo $html;
		die(1);
	
	
	}
	
	public function uultra_delete_user_profile_bg()
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$user_id = get_current_user_id();		
		$site_url = site_url()."/";						
		update_user_meta($user_id, 'user_profile_bg', '');
		
		
		die();
	
	
	}
	
	
	//get latest photos widgets	
	public function uultra_get_card($user_id, $pic_size, $pic_type, $pic_boder_type,  $pic_size_type)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";		
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
			
		
		$dr = $xoouserultra->photogallery->get_latest_photos_public($user_id, $howmany);
		
		//get description
		$brief = $this->get_user_meta( 'brief_description', $user_id);
		
		//get profile bg		
		$bg_src = $this->get_profile_bg($user_id);
		
		//stats
		$total = $this->get_author_posts_total_number_of_comments($user_id);
		
		
		$html = '<input type="hidden" value="'.$user_id.'" id="receiver_id">';
		
		$html .= ' <li><!--- widget 1 column 2--->                     
                    <div class="widget-ultra uultra-card">
                      <div class="uultra-card-bg">'.$bg_src.'</div>      
                       <div class="uultra-avatar">
                        '.$xoouserultra->userpanel->get_user_pic( $user_id, $pic_size, $pic_type, $pic_boder_type,  $pic_size_type).'
                       </div>                    
                        <h1 class="uultra-name">'.$xoouserultra->userpanel->get_display_name($user_id).'</h1>
                                                
                          '.$xoouserultra->userpanel->get_user_social_icons_widget($user_id).'
                        
						  <div id="uultra-friend-follow-div-box">
                         '.$xoouserultra->social->get_friends($user_id).'
						 '.$xoouserultra->social->get_follow_widget($user_id).'
						 
						 </div>
						 
                         <p class="uultra-descrip">'.$brief.'</p>
                         <div class="uultra-social-activities">
                          <ul>
                            <li><i class="fa fa-2x fa-comments-o uultra-icons" title="Comments"></i>'.$total.'</li> 
                            <li><i class="fa fa-2x fa-eye uultra-icons"></i>40</li> 
                            <li><i class="fa fa-2x fa-heart-o uultra-icons"></i>180</li>
							 <li><a href id="uultra-send-private-message-box"><i class="fa fa-2x fa-heart-o uultra-icons"></i></a>180</li>
                          </ul>
                         </div>
						 
						 <div class="uultra-social-activities">
                          <ul>
						    <li>Followers
                             <p class="num">180</p>
                            </li>
							
							<li>Following
                             <p class="num">180</p>
                            </li>
							
                            <li>Photos</i>
                             <p class="num">23</p>
                            </li> 
                            <li>Friends
                             <p class="num">400</p>
                            </li> 
                            <li>Favorites
                             <p class="num">180</p>
                            </li>
                            <li>Posts
                             <p class="num">180</p>
                            </li>                            
							 <li>Comments
                             <p class="num">'.$total.'</p>
                            </li>
							
							
                          </ul>
                         </div>
                     </div>
                 </li><!--- End widget 1 ---> ';
		
		
				$html .= $this->contact_me_public_form(); 
				 
	return  $html;			 
		
	}
	
	function get_author_posts_total_number_of_comments($user_id)
	{
		global $wpdb;	
		
		
		$sql = 'SELECT SUM(comment_count) AS total FROM ' . $wpdb->prefix . 'posts WHERE post_author=%d'; 
		return $wpdb->get_var($wpdb->prepare($sql,$user_id));		
		
	}
	
	public function get_bg_uploader() 
	{
		
	   // Uploading functionality trigger:
	  // (Most of the code comes from media.php and handlers.js)
	      $template_dir = get_template_directory_uri();
		  
		  $user_id = get_current_user_id();
?>
		
		<div id="uploadContainer" style="margin-top: 10px;">
			
			
			<!-- Uploader section -->
			<div id="uploaderSection" style="position: relative;">
				<div id="plupload-upload-ui-profilebg" class="hide-if-no-js">
                
					<div id="drag-drop-area-profilebg">
						<div class="drag-drop-inside">
                        
                        <div  class="uultra-bg-image-box" id="uultra-bg-img-bg-id"><?php echo $this->get_profile_bg($user_id )?></div>
							<p class="drag-drop-info"><?php	_e('Drop Profile Background Image Here', 'xoousers') ; ?></p>
                            <div style="display:">
							<p><?php _ex('or', 'Uploader: Drop files here - or - Select Files'); ?></p>
							<p class="drag-drop-buttons"><input id="plupload-browse-button-profilebg" type="button" value="<?php esc_attr_e('Select File'); ?>" class="button" /></p>
								</div>						
						</div>
                        
                        <div id="progressbar-profilebg"></div>                 
                         <div id="symposium_filelist_profilebg" class="cb"></div>
					</div>
				</div>
                
                 
			
			</div>
            
           
		</div>

		<?php
			
			$plupload_init = array(
				'runtimes'            => 'html5,silverlight,flash,html4',
				'browse_button'       => 'plupload-browse-button-profilebg',
				'container'           => 'plupload-upload-ui-profilebg',
				'drop_element'        => 'drag-drop-area-profilebg',
				'file_data_name'      => 'async-upload',
				'multiple_queues'     => true,
				'multi_selection'	  => false,
				'max_file_size'       => wp_max_upload_size().'b',
				//'max_file_size'       => get_option('drag-drop-filesize').'b',
				'url'                 => admin_url('admin-ajax.php'),
				'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
				//'filters'             => array(array('title' => __('Allowed Files', $this->text_domain), 'extensions' => "jpg,png,gif,bmp,mp4,avi")),
				'filters'             => array(array('title' => __('Allowed Files', "xoousers"), 'extensions' => "jpg,png,gif,jpeg")),
				'multipart'           => true,
				'urlstream_upload'    => true,

				// Additional parameters:
				'multipart_params'    => array(
					'_ajax_nonce' => wp_create_nonce('photo-upload'),
					'action'      => 'ajax_upload_profile_bg' // The AJAX action name
					
				),
			);
			
			//print_r($plupload_init);

			// Apply filters to initiate plupload:
			$plupload_init = apply_filters('plupload_init', $plupload_init); ?>

			<script type="text/javascript">
			
				jQuery(document).ready(function($){
					
					// Create uploader and pass configuration:
					var uploader_profilebg = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

					// Check for drag'n'drop functionality:
					uploader_profilebg.bind('Init', function(up){
						
						var uploaddiv_profilebg = $('#plupload-upload-ui-profilebg');
						
						// Add classes and bind actions:
						if(up.features.dragdrop){
							uploaddiv_profilebg.addClass('drag-drop');
							
							$('#drag-drop-area-profilebg')
								.bind('dragover.wp-uploader', function(){ uploaddiv_profilebg.addClass('drag-over'); })
								.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv_profilebg.removeClass('drag-over'); });

						} else{
							uploaddiv_profilebg.removeClass('drag-drop');
							$('#drag-drop-area').unbind('.wp-uploader');
						}

					});

					
					// Init ////////////////////////////////////////////////////
					uploader_profilebg.init(); 
					
					// Selected Files //////////////////////////////////////////
					uploader_profilebg.bind('FilesAdded', function(up, files) {
						
						
						var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
						
						// Limit to one limit:
						if (files.length > 1){
							alert("<?php _e('You may only upload one image at a time!', 'xoousers'); ?>");
							return false;
						}
						
						// Remove extra files:
						if (up.files.length > 1){
							up.removeFile(uploader_profilebg.files[0]);
							up.refresh();
						}
						
						// Loop through files:
						plupload.each(files, function(file){
							
							// Handle maximum size limit:
							if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
								alert("<?php _e('The file you selected exceeds the maximum filesize limit.', 'xoousers'); ?>");
								return false;
							}
						
						});
						
						jQuery.each(files, function(i, file) {
							jQuery('#symposium_filelist_profilebg').append('<div class="addedFile" id="' + file.id + '">' + file.name + '</div>');
						});
						
						up.refresh(); 
						uploader_profilebg.start();
						
					});
					
					// A new file was uploaded:
					uploader_profilebg.bind('FileUploaded', function(up, file, response){
						
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {"action": "get_profile_bg_refresh"},
							
							success: function(data){
								
								$("#uultra-bg-img-bg-id").html(data);
								//jQuery("#uu-message-noti-id").slideDown();
								//setTimeout("hidde_noti('uu-message-noti-id')", 3000)	;
								
								
								}
						});
						
						
					
					});
					
					// Error Alert /////////////////////////////////////////////
					uploader_profilebg.bind('Error', function(up, err) {
						alert("Error: " + err.code + ", Message: " + err.message + (err.file ? ", File: " + err.file.name : "") + "");
						up.refresh(); 
					});
					
					// Progress bar ////////////////////////////////////////////
					uploader_profilebg.bind('UploadProgress', function(up, file) {
						
						var progressBarValue = up.total.percent;
						
						jQuery('#progressbar-profilebg').fadeIn().progressbar({
							value: progressBarValue
						});
						
						jQuery('#progressbar-profilebg').html('<span class="progressTooltip">' + up.total.percent + '%</span>');
					});
					
					// Close window after upload ///////////////////////////////
					uploader_profilebg.bind('UploadComplete', function() {
						
						//jQuery('.uploader').fadeOut('slow');						
						jQuery('#progressbar-profilebg').fadeIn().progressbar({
							value: 0
						});
						
						
					});
					
					
					
				});
				
					
			</script>
			
		<?php
	
	
	}
	
	
	// File upload handler:
	function ajax_upload_profile_bg()
	{
		global $xoouserultra;
		global $wpdb;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		$site_url = site_url()."/";
		
		// Check referer, die if no ajax:
		check_ajax_referer('photo-upload');
		
		/// Upload file using Wordpress functions:
		$file = $_FILES['async-upload'];		
		
		$original_max_width = 1800; 
        $original_max_height =700; 
		
		
			
		$o_id = get_current_user_id();
		
				
		$info = pathinfo($file['name']);
		$real_name = $file['name'];
        $ext = $info['extension'];
		$ext=strtolower($ext);
		
		$rand = $this->genRandomString();
		
		$rand_name = "profile_bg_".$rand."_".session_id()."_".time(); 
		
		$path_pics = ABSPATH.$xoouserultra->get_option('media_uploading_folder');
			
			
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif') 
		{
			if($o_id != '')
			{
				
				   if(!is_dir($path_pics."/".$o_id."")) {
						$this->CreateDir($path_pics."/".$o_id);								   
					}					
										
					$pathBig = $path_pics."/".$o_id."/".$rand_name.".".$ext;						
					
					
					if (copy($file['tmp_name'], $pathBig)) 
					{
						$upload_folder = $xoouserultra->get_option('media_uploading_folder');				
						$path = $site_url.$upload_folder."/".$o_id."/";
						
						//check max width
												
						list( $source_width, $source_height, $source_type ) = getimagesize($pathBig);
						
						if($source_width > $original_max_width) 
						{
							//resize
							if ($this->createthumb($pathBig, $pathBig, $original_max_width, $original_max_height,$ext)) 
							{
								$old = umask(0);
								chmod($pathBig, 0777);
								umask($old);
														
							}
						
						
						}
						
						
						
						$new_avatar = $rand_name.".".$ext;
						
						$new_avatar_url = $path.$rand_name.".".$ext;
						
						
						//check if there is another avatar						
						$user_pic = get_user_meta($o_id, 'user_profile_bg', true);						
						
						if ( $user_pic!="" )
			            {
							//there is a pending avatar - delete avatar																					
							$o_id = get_current_user_id();		
							//$path_pics = $site_url.$xoouserultra->get_option('media_uploading_folder');
							
							$path_avatar = $path_pics."/".$o_id."/".$user_pic;					
														
							//delete								
							if(file_exists($path_avatar))
							{
								unlink($path_avatar);
							}
							
							//update meta
							update_user_meta($o_id, 'user_profile_bg', $new_avatar);

							
							
							
						}else{
							
							//update meta
							update_user_meta($o_id, 'user_profile_bg', $new_avatar);
												
						
						}
						
						//update user meta
						
						
						
					}
									
					
			     }  		
			
			  
			
        } // image type
		
		// Create response array:
		$uploadResponse = array('image' => $new_avatar_url);
		
		// Return response and exit:
		echo json_encode($uploadResponse);
		
		//echo $new_avatar_url;
		die();
		
	}
	
	public function CreateDir($root){

               if (is_dir($root))        {

                        $retorno = "0";
                }else{

                        $oldumask = umask(0);
                        $valrRet = mkdir($root,0777);
                        umask($oldumask);


                        $retorno = "1";
                }

    }
	
	
    public function createthumb($imagen,$newImage,$toWidth, $toHeight,$extorig)
	{             				
				
                 $ext=strtolower($extorig);
                 switch($ext)
                  {
                   case 'png' : $img = imagecreatefrompng($imagen);
                   break;
                   case 'jpg' : $img = imagecreatefromjpeg($imagen);
                   break;
                   case 'jpeg' : $img = imagecreatefromjpeg($imagen);
                   break;
                   case 'gif' : $img = imagecreatefromgif($imagen);
                   break;
                  }

               
                $width = imagesx($img);
                $height = imagesy($img);  
				

				
				$xscale=$width/$toWidth;
				$yscale=$height/$toHeight;
				
				// Recalculate new size with default ratio
				if ($yscale>$xscale){
					$new_w = round($width * (1/$yscale));
					$new_h = round($height * (1/$yscale));
				}
				else {
					$new_w = round($width * (1/$xscale));
					$new_h = round($height * (1/$xscale));
				}
				
				
				
				if($width < $toWidth)  {
					
					$new_w = $width;	
				
				//}else {					
					//$new_w = $current_w;			
				
				}
				
				if($height < $toHeight)  {
					
					$new_h = $height;	
				
				//}else {					
					//$new_h = $current_h;			
				
				}
			
				
				
				
                $dst_img = imagecreatetruecolor($new_w,$new_h);
				
				/* fix PNG transparency issues */                       
				imagefill($dst_img, 0, 0, IMG_COLOR_TRANSPARENT);         
				imagesavealpha($dst_img, true);      
				imagealphablending($dst_img, true); 				
                imagecopyresampled($dst_img,$img,0,0,0,0,$new_w,$new_h,imagesx($img),imagesy($img));
               
                
				
				 switch($ext)
                  {
                   case 'png' : $img = imagepng($dst_img,"$newImage",9);
                   break;
                   case 'jpg' : $img = imagejpeg($dst_img,"$newImage",100);
                   break;
                   case 'jpeg' : $img = imagejpeg($dst_img,"$newImage",100);
                   break;
                   case 'gif' : $img = imagegif($dst_img,"$newImage");
                   break;
                  }
				  
				   imagedestroy($dst_img);	
				
				
				
                return true;

    }
	
	
	function contact_me_public_form()
	{
		$html = '<div id="dialog-form" class="uultra-send-pm-box" title=" '.__("Send Private Message ",'xoousers').'">
			<p class="validateTips"> '.__("All form fields are required. ",'xoousers').'</p>
			<form>
			<fieldset>
			<label for="name">'.__("Subject: ",'xoousers').'</label>
			<input type="text" name="uu_subject" id="uu_subject" class="text ">
			<label for="email">'.__("Message ",'xoousers').':</label>
			
			<textarea  name="uu_message" id="uu_message" cols="" rows=""></textarea>
			
			</fieldset>
			</form>
			</div>';
	
	return $html;
	}
	
	public function genRandomString() 
	{
		$length = 5;
		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWZYZ";
		
		$real_string_legnth = strlen($characters) ;
		//$real_string_legnth = $real_string_legnth– 1;
		$string="ID";
		
		for ($p = 0; $p < $length; $p++)
		{
			$string .= $characters[mt_rand(0, $real_string_legnth-1)];
		}
		
		return strtolower($string);
	}
	
	//clean all empty values from array
	function cleanArray($array)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $sub_array)
			{
				$result = $this->cleanArray($sub_array);
				if ($result === false)
				{
					unset($array[$key]);
				}
				else
				{
					$array[$key] = $result;
				}
			}
		}
	
		if (empty($array))
		{
			//return false;
		}
	
		return $array;
	}
	

	
	
	
	

}
$key = "customizer";
$this->{$key} = new XooCustomizer();
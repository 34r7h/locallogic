var $ = jQuery;

jQuery(document).ready(function($) {

	jQuery("#uultra-add-new-custom-field-frm").slideUp();
	

	/* open package form */
	jQuery('.uultra-add-new-package').live('click',function(e){
		e.preventDefault();
		
		jQuery("#uultra-add-package").slideDown();				
		return false;
	});
	
	
	/* open new menu link form */
	jQuery('.uultra-links-add-new').live('click',function(e){
		e.preventDefault();		
		
		$("#uultra-add-newlink-message").html(msg_loading_widgets);
		
		jQuery("#uultra-add-links-cont").slideDown();
		 $("#uultra-add-newlink-message").html("");
				
		return false;
	});
	
	
	/* confirm widget addition*/
	jQuery('.uultra-links-add-new-confirm').live('click',function(e){
		e.preventDefault();		
		
		$("#uultra-add-new-links-m-w").html(msg_adding_widget);
		
		var uu_title = $("#uultra_link_title").val();
		var uu_type = $("#uultra_link_type").val();
		var uu_slug = $("#uultra_link_slug").val();
		
		//var uu_content = $("#uultra_link_content").val();
		
		
		 if (typeof(tinymce.get('uultra_link_content'))=="undefined")
		 {
				var uu_content = $("#uultra_link_content").val();
		} else {
				var uu_content =tinymce.get('uultra_link_content').getContent(); 
		}
			
			
			
		
		
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_internal_user_menu_add_new_link_confirm", "uu_title": uu_title, "uu_type": uu_type , "uu_content": uu_content , "uu_slug": uu_slug
						},
						
						success: function(data){
							
							$("#uultra-add-new-links-m-w").html(data);	
							
							//clean form
							
							$("#uultra_link_title").val('');
							$("#uultra_link_type").val('');
							$("#uultra_link_slug").val('');
							tinyMCE.get('uultra_link_content').setContent('');
							
							 uultra_reload_user_menu_customizer();
							
							}
					});
				
		return false;
	});
	
		
	
	/* close add new link form */
	jQuery('.uultra-links-add-new-close').live('click',function(e){
		e.preventDefault();		
		
		 jQuery("#uultra-add-links-cont").slideUp();
		  jQuery("#ultra-add-links-cont").html("");			
			
		return false;
	});
	
	/* open new widget form */
	jQuery('.uultra-widgets-add-new').live('click',function(e){
		e.preventDefault();		
		
		$("#uultra-add-w-message").html(msg_loading_widgets);
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_load_wdiget_addition_form"
						},
						
						success: function(data){
							
							 $("#uultra-add-widget-cont").html(data);							
							 jQuery("#uultra-add-widget-cont").slideDown();
							 $("#uultra-add-w-message").html("");
							
							
							}
					});
				
		return false;
	});
	
	/* close add new widget form */
	jQuery('.uultra-widgets-add-new-close').live('click',function(e){
		e.preventDefault();		
		
		 jQuery("#uultra-add-widget-cont").slideUp();
		  jQuery("#ultra-add-widget-cont").html("");			
			
		return false;
	});
	
	/* confirm widget addition*/
	jQuery('.uultra-widgets-add-new-confirm').live('click',function(e){
		e.preventDefault();		
		
		$("#uultra-add-new-widget-m-w").html(msg_adding_widget);
		
		var uu_title = $("#uultra_add_mod_widget_title").val();
		var uu_type = $("#uultra_add_mod_widget_type").val();
		var uu_editable = $("#uultra_add_mod_widget_editable").val();
		var uu_content = $("#uultra_add_mod_widget_content").val();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_widget_addition_form_confirm", "uu_title": uu_title, "uu_type": uu_type , "uu_editable": uu_editable , "uu_content": uu_content
						},
						
						success: function(data){
							
							$("#uultra-add-new-widget-m-w").html(msg_adding_widget_done);	
							 uultra_reload_all_active_widgets();
							
							}
					});
				
		return false;
	});
	
	/* close package form */
	jQuery('.uultra-close-new-package').live('click',function(e){
		e.preventDefault();
		
		jQuery("#uultra-add-package").slideUp();
				
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER -  Edit Field Form */
	jQuery('.uultra-btn-edit-field').live('click',function(e)
	{		
		e.preventDefault();
		var block_id =  jQuery(this).attr("data-edition");			
		
		var uultra_custom_form =  $('#uultra__custom_registration_form').val();
		
		$("#uultra-spinner").show();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_reload_field_to_edit", 
						"pos": block_id, "uultra_custom_form": uultra_custom_form},
						
						success: function(data){
							
							 $("#uu-edit-fields-bock-"+block_id).html(data);							
							jQuery("#uu-edit-fields-bock-"+block_id).slideDown();
							
							$("#uultra-spinner").hide();								
							
							
							}
					});
		
					
		return false;
	});
	
	
	/* 	FIELDS CUSTOMIZER -  restore default */
	jQuery('#uultra-restore-fields-btn').live('click',function(e)
	{
		
		e.preventDefault();
		
		doIt=confirm(custom_fields_reset_confirmation);
		  
		  if(doIt)
		  {
			// jQuery("#uultra-sucess-delete-fields").slideDown();	
			
			var uultra_custom_form =  $('#uultra__custom_registration_form').val();
			  
				jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "custom_fields_reset", 
						"p_confirm": "yes"  , 		"uultra_custom_form": uultra_custom_form },
						
						success: function(data){
							
							jQuery("#fields-mg-reset-conf").slideDown();			
						
							 window.location.reload();						
							
							
							}
					});
			
		  }
			
					
		return false;
	});
	
	
	/* 	LINKS CUSTOMIZER - restore custom fields */	
	jQuery('#uultradmin-reset-modules-setting').live('click',function(e)
	{		
		e.preventDefault();
		
		var doIt = false;
		
		doIt=confirm(msg_link_rebuild);
		  
		if(doIt)
		{
			jQuery('#loading-animation-users-module').show();
		
			jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {"action": "uultra_rebuild_user_link"},
							
							success: function(data){							
								
								uultra_reload_user_menu_customizer();	
								uultra_reload_user_menu_customizer();
								jQuery('#loading-animation-users-module').hide();							
															
								
								
								}
						});
					
		}
		
					
		return false;
	});
	
	/* 	 CUSTOMIZER - delete profile bg image */	
	jQuery('#uultradmin-remove-custom-user-bg-image').live('click',function(e)
	{		
		e.preventDefault();
		
		var doIt = false;
		
		doIt=confirm(msg_link_deletion);
		  
		if(doIt)
		{
			//jQuery('#loading-animation-users-module').show();
		
			jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {"action": "uultra_delete_default_bg_image"},
							
							success: function(data){	
							
							jQuery("#uultra-u-p-bgimage").slideUp();							
							jQuery('#uultra-u-p-bgimage').html('');						
								
													
															
								
								
								}
						});
					
		}
		
					
		return false;
	});
	
	/* 	LINKS CUSTOMIZER - Delete Link */	
	jQuery('.uultra-delete-custom-link').live('click',function(e)
	{		
		e.preventDefault();
		var link_id =  jQuery(this).attr("uultra-linkid");	
		
	
		var doIt = false;
		
		doIt=confirm(msg_link_deletion);
		  
		if(doIt)
		{
		
			jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {"action": "uultra_delete_custom_link", 
							"link_id": link_id},
							
							success: function(data){							
								
								uultra_reload_user_menu_customizer();								
															
								
								
								}
						});
					
		}
		
					
		return false;
	});
	
	//save user settings			
		$(document).on("click", ".uultra-links-edit-text-btn", function(e) {
			
			e.preventDefault();
			
			var link_id =  jQuery(this).attr("uultra-linkid");
				
    		jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {"action": "uultra_get_custom_link_content",  "link_id": link_id },
					
					success: function(data){					
						
						$("#uultra-links-content-editor-box" ).dialog( "open" );	
						//tinyMCE.activeEditor.setContent('');	
						tinyMCE.get('uultra_link_html_editor_content_').setContent('');
						//tinyMCE.get('uultra_link_html_editor_content_').setContent(data);
				
						tinyMCE.get('uultra_link_html_editor_content_').execCommand( 'mceInsertContent', false, data );						
						$("#uultra-current-selected-link-to-edit" ).val(link_id );
						
						}
				});
			
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 
				
        });
	
	
	jQuery( "#uultra-links-content-editor-box" ).dialog({
			autoOpen: false,
			height: 560,																				
			width: 650,
			modal: true,
			buttons: {
			"Update": function() {
				
								
				var link_id = jQuery("#uultra-current-selected-link-to-edit" ).val();
				
				 if (typeof(tinymce.get('uultra_link_html_editor_content_'))=="undefined")
				 {
						var widget_text = $("#uultra_link_html_editor_content_").val();
				} else {
						var widget_text =tinymce.get('uultra_link_html_editor_content_').getContent(); 
				}
		
				
				uultra_save_user_link_data(link_id, widget_text);
				$( this ).dialog( "close" );
		
			//custom function here
			},
			Cancel: function() {
			$( this ).dialog( "close" );
			}
			},
			close: function() {
			
			
			}
			});
			
			
		function uultra_save_user_link_data(link_id, widget_text) 
		{
			
			jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_update_user_custom_link_content", 
						"link_id": link_id, "widget_text": widget_text },
						
						success: function(data){							
							
							//jQuery('#uultra-add-new-links-m-w-d-'+link_id).html(data);								
														
							
							
							}
					});
		
		
		}
	
	/* 	LINKS CUSTOMIZER - Confirm Links Change */	
	jQuery('.uultra-links-edit-content-btn').live('click',function(e)
	{		
		e.preventDefault();
		var link_id =  jQuery(this).attr("uultra-linkid");	
		
		var uu_title =  $('#uultra-link-name-'+link_id).val();	
		var uu_slug =  $('#uultra-slug-name-'+link_id).val();
			
		
		jQuery('#uultra-add-new-links-m-w-d-'+link_id).html(msg_updating_message);
		
		
		//$("#uultra-spinner").show();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_update_user_custom_link_admin", 
						"link_id": link_id, "uu_title": uu_title , "uu_slug": uu_slug},
						
						success: function(data){							
							
							jQuery('#uultra-add-new-links-m-w-d-'+link_id).html(data);								
														
							
							
							}
					});
		
					
		return false;
	});
	
	
	/* 	LINKS CUSTOMIZER -  Close Open Widget */
	jQuery('.uultra-links-icon-close-open, .uultra-links-close-open-link').live('click',function(e)
	{
		
		e.preventDefault();
		var link_id =  jQuery(this).attr("link-id");		
		var iconheight = 20;
		
		//alert(widget_id);
		if(jQuery("#uultra-link-adm-cont-id-"+link_id).is(":visible")) 
	  	{			
			jQuery("#uultra-links-icon-close-open-id-"+link_id).css('background-position', '0px 0px');
			jQuery("#uultra-link-adm-cont-id-"+link_id).slideToggle();	
			
			jQuery("#uultra-link-adm-cont-id-"+link_id).html("");	
			
			
		}else{
			
			jQuery("#uultra-link-adm-cont-id-"+link_id).slideToggle();							
			jQuery("#uultra-link-adm-cont-id-"+link_id).html(msg_loading_widgets);
			
			
			jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_load_custom_link_options", 
						"link_id": link_id },
						
						success: function(data){								
						
						jQuery("#uultra-link-adm-cont-id-"+link_id).html(data);						
						jQuery("#uultra-links-icon-close-open-id-"+link_id).css('background-position', '0px -'+iconheight+'px');
						
							
								
						
							
							
							}
					});
					
			
						
	 	 }
		
		
		
					
		return false;
	});
	
	/* 	WIDGETS CUSTOMIZER -  Close Open Widget */
	jQuery('.uultra-widgets-icon-close-open, .uultra-expandable-panel-heading-widgets').live('click',function(e)
	{
		
		e.preventDefault();
		var widget_id =  jQuery(this).attr("widget-id");		
		var iconheight = 20;
		
		//alert(widget_id);
		if(jQuery("#uultra-widget-adm-cont-id-"+widget_id).is(":visible")) 
	  	{
			
			jQuery("#uultra-widgets-icon-close-open-id-"+widget_id).css('background-position', '0px 0px');
			
			
			
		}else{
			
			jQuery("#uultra-widgets-icon-close-open-id-"+widget_id).css('background-position', '0px -'+iconheight+'px');			
	 	 }
		
		
		jQuery("#uultra-widget-adm-cont-id-"+widget_id).slideToggle();	
					
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER -  ClosedEdit Field Form */
	jQuery('.uultra-btn-close-edition-field').live('click',function(e)
	{
		
		e.preventDefault();
		var block_id =  jQuery(this).attr("data-edition");		
		jQuery("#uu-edit-fields-bock-"+block_id).slideUp();				
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER -  Add New Field Form */
	jQuery('#uultra-add-field-btn').live('click',function(e)
	{
		
		e.preventDefault();
			
		jQuery("#uultra-add-new-custom-field-frm").slideDown();				
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER -  Add New Field Form */
	jQuery('#uultra-close-add-field-btn').live('click',function(e){
		
		e.preventDefault();
			
		jQuery("#uultra-add-new-custom-field-frm").slideUp();				
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER - Delete Field */
	jQuery('.uultra-delete-profile-field-btn').live('click',function(e)
	{
		e.preventDefault();
		
		var doIt = false;
		
		doIt=confirm(custom_fields_del_confirmation);
		  
		  if(doIt)
		  {
			  
			  var p_id =  jQuery(this).attr("data-field");	
			  var uultra_custom_form =  $('#uultra__custom_registration_form').val();
		
				jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "delete_profile_field", 
						"_item": p_id , "uultra_custom_form": uultra_custom_form },
						
						success: function(data){					
						
							jQuery("#uultra-sucess-delete-fields-"+p_id).slideDown();
						    setTimeout("hidde_noti('uultra-sucess-delete-fields-" + p_id +"')", 1000);
							jQuery( "#"+p_id ).addClass( "uultra-deleted" );
							setTimeout("hidde_noti('" + p_id +"')", 1000);
							
							//reload fields list added 08-08-2014						
							uultra_reload_custom_fields_set();		
							
							
							}
					});
			
		  }
		  else{
			
		  }		
		
				
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER - Update Field Data */
	jQuery('.uultra-btn-submit-field').live('click',function(e){
		e.preventDefault();
		
		var key_id =  jQuery(this).attr("data-edition");	
		
		$('#p_name').val()		  
		
		var _position = $("#uultra_" + key_id + "_position").val();		
		var _type =  $("#uultra_" + key_id + "_type").val();
		var _field = $("#uultra_" + key_id + "_field").val();		
		var _meta =  $("#uultra_" + key_id + "_meta").val();
		var _meta_custom = $("#uultra_" + key_id + "_meta_custom").val();		
		var _name = $("#uultra_" + key_id + "_name").val();
		var _tooltip =  $("#uultra_" + key_id + "_tooltip").val();		
		var _social =  $("#uultra_" + key_id + "_social").val();
		
		var _can_edit =  $("#uultra_" + key_id + "_can_edit").val();		
		var _allow_html =  $("#uultra_" + key_id + "_allow_html").val();
		var _can_hide =  $("#uultra_" + key_id + "_can_hide").val();		
		var _private = $("#uultra_" + key_id + "_private").val();
		var _required =  $("#uultra_" + key_id + "_required").val();		
		var _show_in_register = $("#uultra_" + key_id + "_show_in_register").val();
		var _show_in_widget = $("#uultra_" + key_id + "_show_in_widget").val();
		
		
		var _choices =  $("#uultra_" + key_id + "_choices").val();	
		var _predefined_options =  $("#uultra_" + key_id + "_predefined_options").val();		
		var _icon =  $('input:radio[name=uultra_' + key_id +'_icon]:checked').val();
		
		var uultra_custom_form =  $('#uultra__custom_registration_form').val();
		
		//special for roles		
		var _show_to_user_role =  $("#uultra_" + key_id + "_show_to_user_role").val();	
		var _edit_by_user_role =  $("#uultra_" + key_id + "_edit_by_user_role").val();	
		
		//list of role to show	 -- added on 11-02-2014	
		var _show_to_user_role_list = $('.uultra_' + key_id +'_show_roles_ids:checked').map(function() { 
			return this.value; }).get().join(',');
		
		var _edit_by_user_role_list = $('.uultra_' + key_id +'_edit_roles_ids:checked').map(function() { 
			return this.value;	}).get().join(',');	
					

		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "save_fields_settings", 
						"_position": _position , "_type": _type ,
						"_field": _field ,
						"_meta": _meta ,"_meta_custom": _meta_custom  
						,"_name": _name  ,"_tooltip": _tooltip ,
						"_tooltip": _tooltip ,
						"_social": _social ,
						"_icon": _icon ,
						"_can_edit": _can_edit ,"_allow_html": _allow_html  ,
						"_can_hide": _can_hide  ,"_private": _private, 
						"_required": _required  ,"_show_in_register": _show_in_register ,
						"_show_in_widget": _show_in_widget ,
						"_choices": _choices, 
						"_predefined_options": _predefined_options,
						"pos": key_id  , 
						"uultra_custom_form": uultra_custom_form ,
						
						"_show_to_user_role": _show_to_user_role,
						"_edit_by_user_role": _edit_by_user_role,
						"_show_to_user_role_list": _show_to_user_role_list,
						"_edit_by_user_role_list": _edit_by_user_role_list
											
						},
						
						success: function(data){		
						
												
						jQuery("#uultra-sucess-fields-"+key_id).slideDown();
						setTimeout("hidde_noti('uultra-sucess-fields-" + key_id +"')", 1000)
						
						//reload fields list added 08-08-2014						
						uultra_reload_custom_fields_set();		
						
							
							}
					});
			
		 
		
				
		return false;
	});
	
	
	
	 /* Enable Roles only when adding a new field */
    $('#uultra_show_to_user_role').change(function()
	{
        if ($(this).val() == '1') 
		{
            $('#uultra_show_to_user_role_div').show();
			
        } else {
			
            $('#uultra_show_to_user_role_div').hide();
        }
    });

    $('#uultra_edit_by_user_role').change(function()
	{
        if ($(this).val() == '1') 
		{
            $('#uultra_edit_by_user_role_id').show();
			
        } else {
			
            $('#uultra_edit_by_user_role_id').hide();
        }
    });
	
	
	//Display/hide when editing the field
	jQuery('.uultra_show_to_user_role_edit').live('change',function(e){
		e.preventDefault();
        
        var elementId = $(this).attr("id");
		
        if ($(this).val() == '1') 
		{
            $('#'+elementId+'_list_div').show();
			
        } else {
            
            var elementClass = $('#'+elementId+'_list').attr("class");

            $('.'+elementClass).each(
                function() {
              
                    $(this).attr('checked', false);
                }
                );
            $('#'+elementId+'_list_div').hide();
        }
    });
	
	jQuery('.uultra_edit_by_user_role_edit').live('change',function(e){
		e.preventDefault();
        
        var elementId = $(this).attr("id");
		
        if ($(this).val() == '1') 
		{
            $('#'+elementId+'_list_div').show();
			
        } else {
            
            var elementClass = $('#'+elementId+'_list').attr("class");

            $('.'+elementClass).each(
                function() {
              
                    $(this).attr('checked', false);
                }
                );
            $('#'+elementId+'_list_div').hide();
        }
    });
	
	
	/* 	FIELDS CUSTOMIZER - Add New Field Data */
	jQuery('#uultra-btn-add-field-submit').live('click',function(e){
		e.preventDefault();
		
		
		var _position = $("#uultra_position").val();		
		var _type =  $("#uultra_type").val();
		var _field = $("#uultra_field").val();		
		var _meta =  $("#uultra_meta").val();
		var _meta_custom = $("#uultra_meta_custom").val();		
		var _name = $("#uultra_name").val();
		var _tooltip =  $("#uultra_tooltip").val();		
		var _social =  $("#uultra_social").val();
		var _can_edit =  $("#uultra_can_edit").val();		
		var _allow_html =  $("#uultra_allow_html").val();
		var _can_hide =  $("#uultra_can_hide").val();		
		var _private = $("#uultra_private").val();
		var _required =  $("#uultra_required").val();		
		var _show_in_register = $("#uultra_show_in_register").val();
		var _show_in_widget = $("#uultra_show_in_widget").val();
		var _choices =  $("#uultra_choices").val();	
		var _predefined_options =  $("#uultra_predefined_options").val();		
		
		var uultra_custom_form =  $('#uultra__custom_registration_form').val();
		
		//special for roles		
		var _show_to_user_role =  $("#uultra_show_to_user_role").val();	
		var _edit_by_user_role =  $("#uultra_edit_by_user_role").val();	
		
		//list of role to show		
	
		var _show_to_user_role_list = $('.uultra_show_roles_ids:checked').map(function() { 
    return this.value; 
}).get().join(',');

		var _edit_by_user_role_list = $('.uultra_edit_roles_ids:checked').map(function() { 
    return this.value; 
}).get().join(',');	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "add_new_custom_profile_field", 
						"_position": _position , "_type": _type ,"_field": _field 
						,"_meta": _meta ,"_meta_custom": _meta_custom  
						,"_name": _name  ,"_tooltip": _tooltip ,
						"_tooltip": _tooltip ,"_social": _social ,
						"_can_edit": _can_edit ,"_allow_html": _allow_html  ,
						"_can_hide": _can_hide  ,"_private": _private, 
						"_required": _required  ,
						"_show_in_register": _show_in_register ,
						"_show_in_widget": _show_in_widget ,
						"_choices": _choices,  
						"_predefined_options": _predefined_options , 
						"uultra_custom_form": uultra_custom_form,
						"_show_to_user_role": _show_to_user_role,
						"_edit_by_user_role": _edit_by_user_role,
						"_show_to_user_role_list": _show_to_user_role_list,
						"_edit_by_user_role_list": _edit_by_user_role_list },
						
						success: function(data){		
						
												
						jQuery("#uultra-sucess-add-field").slideDown();
						setTimeout("hidde_noti('uultra-sucess-add-field')", 3000)		
						//alert("done");
						window.location.reload();
							 							
							
							
							}
					});
			
		 
		
				
		return false;
	});
	
	function uultra_get_checked_roles(selected_list)	
	{
		
		var checkbox_value = "";
		var total_items=  jQuery("." + selected_list).length;
		//alert(total_items);
		var i = 1;
		jQuery("." + selected_list).each(function () {
			
			var ischecked = $(this).is(":checked");
		   
			if (ischecked) 
			{
				checkbox_value += $(this).val() ;
				
				if (i<=total_items-1) 
				{
					checkbox_value +=  ",";
				}	
			
			}
			
				
			i++;
			
			
		});
		
		return checkbox_value;		
	}
	
	
	/* 	FIELDS CUSTOMIZER -  Add New Field Form */
	jQuery('#uultra-btn-sync-btn').live('click',function(e){
		
		e.preventDefault();
		
		$("#uultra-sync-results").html(message_sync_users);		
		jQuery("#uultra-sync-results").slideDown();
		
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "sync_users"},
						
						success: function(data){					
						
							 $("#uultra-sync-results").html(data);
							 jQuery("#uultra-sync-results").slideDown();
							 setTimeout("hidde_noti('uultra-sync-results')", 5000)
													
							
							
							}
					});
			
				
		return false;
	});
	
	
	
	/* 	FIELDS CUSTOMIZER -  Edit Field Form */
	jQuery('#uultra__custom_registration_form').live('change',function(e)
	{		
		e.preventDefault();
		uultra_reload_custom_fields_set();
					
		return false;
	});
	
	
	
	/* 	MEDIA UPGRADING */
	jQuery('#uultra-btn-upgrade-features-btn').live('click',function(e)
	{
		e.preventDefault();
		
		var doIt = false;
		
		doIt=confirm(message_upgrade_media);
		  
		  if(doIt)
		  {
			  
			 	jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_upgrade_to_media_confirm"},
						
						success: function(data){					
						
							jQuery("#uultra-sync-new-media-feature").html(data);
						    								
							
							
							}
					});
			
		  }
		  else{
			
		  }		
		
				
		return false;
	});
	
	
	


    /* close package form */
	jQuery('.uultra-package-edit').live('click',function(e){
		e.preventDefault();
		
		 
		 var p_id =  jQuery(this).attr("data-package");	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "package_edit_form", 
						"p_id": p_id },
						
						success: function(data){					
						
							 $("#uu-edit-package-box-"+p_id).html(data);
							 $('.color-picker').wpColorPicker();
							 jQuery("#uu-edit-package-box-"+p_id).slideDown();							
							
							
							}
					});
			
		 	
		
				
		return false;
	});
	
	/* close package form */
	jQuery('.uultra-package-deactivate').live('click',function(e){
		e.preventDefault();
		
		doIt=confirm(package_confirmation);
		  
		  if(doIt)
		  {
			  
			  var p_id =  jQuery(this).attr("data-package");	
		
				jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "package_delete", 
						"p_id": p_id },
						
						success: function(data){					
						
							 reload_packages();								
							
							
							}
					});
			
		  }
		  else{
			
		  }		
		
				
		return false;
	});
	
	/* confirm package addition */
	jQuery('.uultra-add-new-package-confirm').live('click',function(e){
		e.preventDefault();
		
		//validate
		
		var p_name = $('#p_name').val();
		var p_desc = $('#p_desc').val();			
		var p_price = $('#p_price').val();
		
		var p_period = $('#p_period').val();
		var p_every = $('#p_every').val();
		var p_type = $('#p_type').val();
		
		
		var p_moderation = $('#p_moderation').val();
		var p_max_photos = $('#p_max_photos').val();
		var p_max_gallery = $('#p_max_gallery').val();	
		
		
		var p_max_posts = $('#p_max_posts').val();
		var p_max_posts_read = $('#p_max_posts_read').val();
		var p_credits = $('#p_credits').val();
		
		//customization
		
		var p_price_color = $('#p_price_color').val();
		var p_price_bg_color = $('#p_price_bg_color').val();
		
		var p_signup_color = $('#p_signup_color').val();
		var p_signup_bg_color = $('#p_signup_bg_color').val();
		var p_role = $('#p_role').val();
		
		var p_custom_registration_form = $('#p_custom_registration_form').val();
		
		
				
		
		if(p_name==""){alert(package_error_message_name);return}
		if(p_desc==""){alert(package_error_message_desc);return}
		if(p_price==""){alert(package_error_message_price);return}
		
		
		
		jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "package_add_new", "p_name": p_name , "p_desc": p_desc , 				"p_price": p_price, "p_period": p_period  , "p_every": p_every ,  "p_type": p_type ,  "p_moderation": p_moderation ,  "p_max_photos": p_max_photos ,  "p_max_gallery": p_max_gallery  ,  "p_max_posts": p_max_posts ,  "p_max_posts_read": p_max_posts_read ,  "p_credits": p_credits ,  "p_price_color": p_price_color ,  "p_price_bg_color": p_price_bg_color ,  "p_signup_color": p_signup_color ,  "p_signup_bg_color": p_signup_bg_color ,  "p_role": p_role ,  "p_custom_registration_form": p_custom_registration_form},
				
				success: function(data){	
				
				
				     reload_packages();
								
									
					
					
					}
			});
			
		
		
		
		jQuery("#uultra-add-package").slideDown();
				
		return false;
	});
	
	//create upload folder
	jQuery('#uultradmin-create-upload-folder').live('click',function(e){
		e.preventDefault();
		
		 
	
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "create_uploader_folder" 
						 },
						
						success: function(data){
							
							window.location.reload();
								
							
							}
					});
			
		 	
		
				
		return false;
	});
	
	/* confirm package addition */
	jQuery('.uultra-edit-package-confirm').live('click',function(e){
		e.preventDefault();
		
		 var p_id =  jQuery(this).attr("data-package");
		
		//validate
		var p_name = $('#p_name_'+p_id).val();
		var p_desc = $('#p_desc_'+p_id).val();			
		var p_price = $('#p_price_'+p_id).val();
		
		var p_period = $('#p_period_'+p_id).val();
		var p_every = $('#p_every_'+p_id).val();
		var p_type = $('#p_type_'+p_id).val();
		
		var p_moderation = $('#p_moderation_'+p_id).val();
		var p_max_photos = $('#p_max_photos_'+p_id).val();
		var p_max_gallery = $('#p_max_gallery_'+p_id).val();
		
		var p_max_posts = $('#p_max_posts_'+p_id).val();
		var p_max_posts_read = $('#p_max_posts_read_'+p_id).val();
		var p_credits = $('#p_credits_'+p_id).val();
		
		var p_price_color = $('#p_price_color_'+p_id).val();
		var p_price_bg_color = $('#p_price_bg_color_'+p_id).val();
		
		var p_signup_color = $('#p_signup_color_'+p_id).val();
		var p_signup_bg_color = $('#p_signup_bg_color_'+p_id).val();
		var p_role = $('#p_role_'+p_id).val();
		var p_custom_registration_form = $('#p_custom_registration_form_'+p_id).val();
		
		if(p_name==""){alert(package_error_message_name);return}
		if(p_desc==""){alert(package_error_message_desc);return}
		if(p_price==""){alert(package_error_message_price);return}
		
		
		jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "package_edit_confirm", "p_id":p_id,  "p_name": p_name , "p_desc": p_desc , 				"p_price": p_price, "p_period": p_period  , "p_every": p_every ,  "p_type": p_type ,  "p_moderation": p_moderation ,  "p_max_photos": p_max_photos ,  "p_max_gallery": p_max_gallery ,  "p_max_posts": p_max_posts ,  "p_max_posts_read": p_max_posts_read ,  "p_credits": p_credits ,  "p_price_color": p_price_color ,  "p_price_bg_color": p_price_bg_color ,  "p_signup_color": p_signup_color ,  "p_signup_bg_color": p_signup_bg_color ,  "p_role": p_role ,  "p_custom_registration_form": p_custom_registration_form},
				
				success: function(data){
					
					//display message							
					$("#package-edit-noti-mess-"+p_id).html(data);
					jQuery("#package-edit-noti-mess-"+p_id).slideDown();
					setTimeout("reload_packages()", 2000);		
				
					
					
					}
			});
			
		
		e.preventDefault();
				
		return false;
	});
	
	/* customirzer - save main styles */
	jQuery('.uultra-profile-customizer-save-style').live('click',function(e){
		e.preventDefault();
		
		 var p_id =  jQuery(this).attr("data-widget");
		
		//validate2
		var uultra_profile_bg_color = $('#uultra_profile_bg_color').val();
		var uultra_profile_bg_color_transparent = $('#uultra_profile_bg_color_transparent').val();			
		var uultra_profile_inferior_bg_color = $('#uultra_profile_inferior_bg_color').val();		
		var uultra_profile_inferior_bg_color_transparent = $('#uultra_profile_inferior_bg_color_transparent').val();
		
		var uultra_profile_image_bg_color = $('#uultra_profile_image_bg_color').val();
		
		jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "customizer_profile_appearance",   "uultra_profile_bg_color": uultra_profile_bg_color , "uultra_profile_bg_color_transparent": uultra_profile_bg_color_transparent , "uultra_profile_inferior_bg_color": uultra_profile_inferior_bg_color, "uultra_profile_inferior_bg_color_transparent": uultra_profile_inferior_bg_color_transparent ,"uultra_profile_image_bg_color": uultra_profile_image_bg_color },
				
				success: function(data){
					
					//display message					
					$("#uultra-prof-custom-basic-message").html(data);
					
					}
			});
			
		e.preventDefault();
		return false;
	});
	
	
	/* confirm package addition */
	jQuery('.uultra-widget-customizer-save').live('click',function(e){
		e.preventDefault();
		
		 var p_id =  jQuery(this).attr("data-widget");
		
		//validate
		var widget_header_bg_color = $('#widget_header_bg_color_'+p_id).val();
		var widget_bg_color = $('#widget_bg_color_'+p_id).val();			
		var widget_header_text_color = $('#widget_header_text_color_'+p_id).val();		
		var widget_text_color = $('#widget_text_color_'+p_id).val();
		var widget_transparent = $('#widget_transparent_'+p_id).val();
		var widget_title = $('#widget_title_'+p_id).val();
		
		//custom widgets		
		var uultra_add_mod_widget_title = $('#uultra_add_mod_widget_title_'+p_id).val();
		var uultra_add_mod_widget_type = $('#uultra_add_mod_widget_type_'+p_id).val();			
		var uultra_add_mod_widget_editable = $('#uultra_add_mod_widget_editable_'+p_id).val();		
		var uultra_add_mod_widget_content = $('#uultra_add_mod_widget_content_'+p_id).val();
		
		$("#uultra-widget-update-message-"+p_id).html("");
		
		//
		var widget_post_type_list=  $('input[name*="uultra_all_post_types"]:checked').serializeArray();
		
		jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "customizer_widget_appearance", "p_id":p_id,  "widget_header_bg_color": widget_header_bg_color , "widget_bg_color": widget_bg_color , "widget_header_text_color": widget_header_text_color, "widget_text_color": widget_text_color  , "widget_transparent": widget_transparent , "widget_title": widget_title, "widget_post_type_list": widget_post_type_list , "uultra_add_mod_widget_title": uultra_add_mod_widget_title , "uultra_add_mod_widget_type": uultra_add_mod_widget_type , "uultra_add_mod_widget_editable": uultra_add_mod_widget_editable , "uultra_add_mod_widget_content": uultra_add_mod_widget_content},
				
				success: function(data){
					
					//display message					
					$("#uultra-widget-update-message-"+p_id).html(data);
					$("#uultra-widget-title-id-"+p_id).html(uultra_add_mod_widget_title);	
					
					}
			});
			
		e.preventDefault();
				
		return false;
	});
	
	/* Verify user */	
	jQuery('.uultradmin-user-approve').live('click',function(e){
		e.preventDefault();
		
		 
		 var user_id =  jQuery(this).attr("user-id");	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_approve_pending_account", 
						"user_id": user_id },
						
						success: function(data){					
						
								$("#uultra-user-acti-noti").html(data);
								//jQuery("#package-edit-noti-mess-"+p_id).slideDown();
								setTimeout("reload_pending_moderation()",2000);	
								setTimeout("reload_pending_payment()", 2000);					
							
							
							}
					});
			
		 	
		return false;
	});
	
	/* Verify user */
	
	jQuery('.uultradmin-user-approve-2').live('click',function(e){
		e.preventDefault();
		 
		 var user_id =  jQuery(this).attr("user-id");	
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_approve_pending_account", 
						"user_id": user_id },
						
						success: function(data){					
						
								$("#uultra-user-acti-noti").html(data);
								setTimeout("reload_pending_activation()", 2000);	
														
							
							}
					});
		
				
		return false;
	});
	
	
	/* Unverify user */	
	jQuery('.uultradmin-user-deny').live('click',function(e){
		e.preventDefault();
		
		 
		 var user_id =  jQuery(this).attr("user-id");	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_delete_account", 
						"user_id": user_id },
						
						success: function(data){					
						
								$("#uultra-user-acti-noti").html(data);
								//jQuery("#package-edit-noti-mess-"+p_id).slideDown();
								setTimeout("reload_pending_moderation()", 2000);
								setTimeout("reload_pending_payment()", 2000);						
							
							
							}
					});
			
		return false;
	});
	
	
	jQuery('#uultradmin-confirm-rearrange').live('click',function(e){
		e.preventDefault();
		
		
		 var itemList = jQuery('#uu-fields-sortable');		 
		 var uultra_custom_form =  $('#uultra__custom_registration_form').val();
		
		$('#loading-animation').show(); // Show the animate loading gif while waiting
		
		opts = {
                url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
                type: 'POST',
                async: true,
                cache: false,
                dataType: 'json',
                data:{
                    action: 'sort_fileds_list', // Tell WordPress how to handle this ajax request
                    order: itemList.sortable('toArray').toString() // Passes ID's of list items in  1,3,2 format
                },
                success: function(response) {
                    $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                },
                error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
                    alert(e);
                    // alert('There was an error saving the updates');
                  //  $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                }
            };
            jQuery.ajax(opts);
		
		 
		
			 	
		
				
		return false;
	});
	
	
	//edit user package, this displays a form to edit package inline
	jQuery(document).on("click", ".uultra-user-edit-package", function(e) {
		
		e.preventDefault();
		 
		 var user_id =  jQuery(this).attr("user-id");	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_package_edit_form", 
						"user_id": user_id },
						
						success: function(data){			
									
						
							jQuery("#uu-edit-user-box-"+user_id).html(data);
							jQuery("#uu-edit-user-box-"+user_id).slideDown();							
							
							
							}
					});
			
		return false;
	});
	
	//see submited deailes
	jQuery(document).on("click", ".uultra-user-see-details", function(e) {
		
		e.preventDefault();
		 
		 var user_id =  jQuery(this).attr("user-id");	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_see_details_backend", 
						"user_id": user_id },
						
						success: function(data){			
									
						
							jQuery("#uu-edit-user-box-"+user_id).html(data);
							jQuery("#uu-edit-user-box-"+user_id).slideDown();							
							
							
							}
					});
			
		return false;
	});
	
	//delete user
	jQuery(document).on("click", ".uultra-private-user-deletion", function(e) {
		
		e.preventDefault();
		
		doIt=confirm(message_users_module_delete);
		  
	    if(doIt)
		{		 
		    var user_id =  jQuery(this).attr("user-id");	
			
			jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {"action": "uultra_user_private_user_deletion", 
							"user_id": user_id },
							
							success: function(data){										
							
								jQuery("#uu-edit-user-box-"+user_id).html(data);
								jQuery("#uu-edit-user-box-"+user_id).slideDown();
								
								window.location.reload();							
								
								
								}
						});
		
		}
			
		return false;
	});
	
	
	//close user package confirm
	jQuery(document).on("click", ".uultra-user-edit-package-close", function(e) {
		
		e.preventDefault();
		 
		var user_id =  jQuery(this).attr("data-user");		 
		jQuery("#uu-edit-user-box-"+user_id).slideUp();
	
		
					
		return false;
	});
	
	
	//edit user package confirm
	jQuery(document).on("click", ".uultra-user-edit-package-confirm", function(e) {
		
		e.preventDefault();
		 
		var user_id =  jQuery(this).attr("data-user");		 
		var myRadio = $('input[name=usersultra_package_id_'+user_id+']');
		var package_id =myRadio.filter(':checked').val();	
		
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_package_edit_form_confirm", 
						"user_id": user_id, "package_id": package_id },
						
						success: function(data){			
									
						
							jQuery("#uultra-u-package-ed-"+user_id).html(data);
							jQuery("#uultra-u-package-ed-"+user_id).slideDown();							
							
							
							}
					});
			
		return false;
	});
	
	/* open new widget form */
	jQuery('#uultra-delete-csv-export-file').live('click',function(e){
		e.preventDefault();		
		
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_delete_exported_csv_file"
						},
						
						success: function(data){
							
													
							 jQuery("#uultra-csv-download-box").slideUp();
							
							
							
							}
					});
				
		return false;
	});
	
	//switch template
	jQuery(document).on("click", ".uultra-template-user-activate", function(e) {
		
		e.preventDefault();		 
		var template_id =  jQuery(this).attr("data-rel");		
				
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "customizer_set_default_template", 
						"template_id": template_id },
						
						success: function(data){
							
							window.location.reload();						
									
							}
					});
			
		return false;
	});
	
	
	
	
	jQuery('#uultradmin-btn-validate-copy').live('click',function(e){
		e.preventDefault();
		
		 
		 var p_ded =  $('#p_serial').val();	
		 
		 
		 
		 jQuery("#loading-animation").slideDown();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_vv_c_de_a", 
						"p_s_le": p_ded },
						
						success: function(data){
							
							jQuery("#loading-animation").slideUp();							
						
								jQuery("#uultra-validation-results").html(data);
								jQuery("#uultra-validation-results").slideDown();
								
								setTimeout("hidde_noti('uultra-validation-results')", 5000)
								
								window.location.reload();
							
							}
					});
			
		 	
		
				
		return false;
	});
	
	jQuery('#uultradmin-create-basic-fields').live('click',function(e){
		e.preventDefault();
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "create_default_pages_auto" 
						 },
						
						success: function(data){
							
							window.location.reload();
								
							
							}
					});
			
		return false;
	});
	
	//user ultra re-send activation link
	jQuery(document).on("click", ".uultradmin-user-resend-link", function(e) {
		
		e.preventDefault();	
		 
		 var user_id =  jQuery(this).attr("user-id");
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "user_resend_activation_link", 
						"user_id": user_id },
						
						success: function(data){			
									
						
							
							}
					});
			
		return false;
	});
	
	//module activation - save	
	jQuery(document).on("click", "#uultradmin-save-modules-setting", function(e) {
		
		e.preventDefault();			 
		var modules_list =  get_disabled_modules_list();	
		 
		jQuery('#loading-animation-users-module').show();	 
		 
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "uultra_modules_deactivate_activate", 
						"modules_list": modules_list },
						
						success: function(data){
							
							jQuery('#loading-animation-users-module').hide();
							uultra_reload_user_menu_customizer();	
							
							}
					});
			
		return false;
	});
	
		

	
});


function get_disabled_modules_list ()	
{
	
	var checkbox_value = "";
    jQuery(".uultra-my-modules-checked").each(function () {
		
        var ischecked = $(this).is(":checked");
       
	    if (ischecked) 
		{
            checkbox_value += $(this).val() + "|";
        }
		
		
    });
	
	return checkbox_value;		
}

function reload_packages ()	
	{
		  jQuery.post(ajaxurl, {
							action: 'get_packages_ajax'
									
							}, function (response){									
																
							jQuery("#usersultra-data_list").html(response);
							jQuery('.color-picker').wpColorPicker();
							jQuery(".user-ultra-success").slideDown();
							setTimeout("hidde_package_noti('user-ultra-success')", 3000);
									
														
		 });
		
	}

function reload_pending_moderation ()	
	{
		  jQuery.post(ajaxurl, {
							action: 'get_pending_moderation_list'
									
							}, function (response){									
																
							jQuery("#uultra-pending-moderation-list").html(response);
																
														
		 });
		
}

function reload_pending_payment ()	
	{
		  jQuery.post(ajaxurl, {
							action: 'get_pending_payment_list'
									
							}, function (response){									
																
							jQuery("#uultra-pending-payment-list").html(response);
																
														
		 });
		
}


function reload_pending_activation ()	
	{
		  jQuery.post(ajaxurl, {
							action: 'get_pending_activation_list'
									
							}, function (response){									
																
							jQuery("#uultra-pending-activation-list").html(response);
																
														
		 });
		
}

function uultra_reload_user_modules ()	
{
		  jQuery.post(ajaxurl, {
							action: 'uultra_reload_user_modules'
									
							}, function (response){									
																
							jQuery("#uultra-user-mod-list").html(response);
							
							
																
														
		 });
		
}

function uultra_reload_user_menu_customizer ()	
{
		
		jQuery.post(ajaxurl, {
							action: 'uultra_reload_user_menu_customizer'
									
							}, function (response){									
																
							jQuery("#uultra-user-menu-option-list").html(response);							
							sortable_user_menu();
							
																
														
		 });
		
}

function sortable_user_widgets()
{
	 var itemList = jQuery('#uultra-profile-widgets-available, #uultra-profile-widgets-unavailable ');
	 var itemListWidgetUnused = jQuery('#uultra-profile-widgets-unavailable ');
	 
	 itemList.sortable({
		 
		 connectWith: ".uultra-connectedSortable",
		  cursor: 'move',
          update: function(event, ui) {
           // $('#loading-animation').show(); // Show the animate loading gif while waiting

            opts = {
                url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
                type: 'POST',
                async: true,
                cache: false,
                dataType: 'json',
                data:{
                    action: 'uultra_update_unused_wigets', // Tell WordPress how to handle this ajax request
                    order: itemListWidgetUnused.sortable('toArray').toString() // Passes ID's of list items in  1,3,2 format
                },
                success: function(response) {
                   // $('#loading-animation').hide(); // Hide the loading animation
				  // uultra_reload_user_menu_customizer();
				  				   
                    return; 
                },
                error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
                    alert(e);
                    // alert('There was an error saving the updates');
                  //  $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                }
            };
            jQuery.ajax(opts);
        }
    }); 
	
}

function sortable_user_menu()
{
	 var itemList = jQuery('#uultra-user-menu-option-list');
	 
	 itemList.sortable({
		  cursor: 'move',
          update: function(event, ui) {
           // $('#loading-animation').show(); // Show the animate loading gif while waiting

            opts = {
                url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
                type: 'POST',
                async: true,
                cache: false,
                dataType: 'json',
                data:{
                    action: 'uultra_sort_user_menu_ajax', // Tell WordPress how to handle this ajax request
                    order: itemList.sortable('toArray').toString() // Passes ID's of list items in  1,3,2 format
                },
                success: function(response) {
                   // $('#loading-animation').hide(); // Hide the loading animation
				   uultra_reload_user_menu_customizer();
				  				   
                    return; 
                },
                error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
                    alert(e);
                    // alert('There was an error saving the updates');
                  //  $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                }
            };
            jQuery.ajax(opts);
        }
    }); 
	
}




function hidde_package_noti (div_d)
{
		jQuery("."+div_d).slideUp();		
		
}

function hidde_noti (div_d)
{
		jQuery("#"+div_d).slideUp();		
		
}

function uultra_reload_custom_fields_set ()	
{
	
	jQuery("#uultra-spinner").show();
	
	 var uultra_custom_form =  $('#uultra__custom_registration_form').val();
		
		jQuery.post(ajaxurl, {
							action: 'uultra_reload_custom_fields_set', 'uultra_custom_form': uultra_custom_form
									
							}, function (response){									
																
							jQuery("#uu-fields-sortable").html(response);							
							sortable_fields_list();
							
							jQuery("#uultra-spinner").hide();
							
																
														
		 });
		
}

function uultra_reload_all_active_widgets ()	
{
	
	jQuery("#uultra-profile-widgets-available").html(msg_loading_widgets);
	jQuery("#uultra-profile-widgets-unavailable").html(msg_loading_widgets);
			
	jQuery.post(ajaxurl, {
							action: 'uulra_reload_all_widgets_admin'
									
							}, function (response){									
																
							jQuery("#uultra-profile-widgets-available").html(response);	
							 jQuery('.color-picker').wpColorPicker();															
							jQuery("#uultra-spinner").hide();
							uultra_reload_all_unused_widgets();
							
																
														
		 });
		
}

function uultra_reload_all_unused_widgets ()	
{
	
	jQuery.post(ajaxurl, {
							action: 'uulra_reload_all_widgets_admin', unused: true
									
							}, function (response){									
																
							jQuery("#uultra-profile-widgets-unavailable").html(response);	
							 jQuery('.color-picker').wpColorPicker();															
							jQuery("#uultra-spinner").hide();
							sortable_user_widgets();
							
																
														
		 });
		
}

function sortable_fields_list ()
{
	var itemList = jQuery('#uu-fields-sortable');	 
	var uultra_custom_form =  $('#uultra__custom_registration_form').val();
   
    itemList.sortable({
		cursor: 'move',
        update: function(event, ui) {
        jQuery("#uultra-spinner").show(); // Show the animate loading gif while waiting

            opts = {
                url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
                type: 'POST',
                async: true,
                cache: false,
                dataType: 'json',
                data:{
                    action: 'sort_fileds_list', // Tell WordPress how to handle this ajax request
					uultra_custom_form: uultra_custom_form, // Tell WordPress how to handle this ajax request
                    order: itemList.sortable('toArray').toString() // Passes ID's of list items in  1,3,2 format
                },
                success: function(response) {
                   // $('#loading-animation').hide(); // Hide the loading animation
				   uultra_reload_custom_fields_set();
                    return; 
                },
                error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
                    alert(e);
                    // alert('There was an error saving the updates');
                  //  $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                }
            };
            jQuery.ajax(opts);
        }
    }); 
	
	
}

jQuery(document).ready(function($) 
{ 
   //sortable_fields_list();
   
});
jQuery(document).ready(function($) {
	
	jQuery("body").on("click", ".uultra-group-edit", function(e) {
		e.preventDefault();		
		 
		 var cate_id =  jQuery(this).attr("data-gal");	
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "edit_photo_cate", 
						"cate_id": cate_id },
						
						success: function(data){					
						
							 $("#uu-edit-cate-box-"+cate_id).html(data);
							 jQuery("#uu-edit-cate-box-"+cate_id).slideDown();							
							
							
							}
					});
		return false;
	});
	
	jQuery("body").on("click", ".uultra-group-modify", function(e) {
		e.preventDefault();		
		 
		 var cate_id=  jQuery(this).attr("data-id");		
		 var cate_name = $('#uultra_group_name_edit_'+cate_id).val();	
		 
				
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "edit_group_conf", "cate_id": cate_id, 
						"cate_name": cate_name },
						
						success: function(data){									
							//refresh
							//window.location.reload();	
							
							$("#uu-edit-group-row-name-"+cate_id).html(data);
							 jQuery("#uu-edit-group-box-"+cate_id).slideUp();								
													
							
							
							}
					});
		return false;
	});
	
	jQuery("body").on("click", ".uultra-group-del", function(e) {
		e.preventDefault();		
		 
		 var cate_id=  jQuery(this).attr("data-gal");		
			
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "edit_photo_cate_del", "cate_id": cate_id 
						 },
						
						success: function(data){
							
							//refresh
							//window.location.reload();	
							
							jQuery("#uu-edit-cate-row-"+cate_id).slideUp();						
						
													
							
							
							}
					});
		return false;
	});
	
	jQuery("body").on("click", ".uultra-group-close", function(e) {
		e.preventDefault();		
		 
		 var cate_id =  jQuery(this).attr("data-id");			
		jQuery("#uu-edit-group-box-"+cate_id).slideUp();
		return false;
	});
	
});
jQuery(document).ready(function($) {
	
	
	
	jQuery("body").on("click", ".uultra-ipnumber-del", function(e) {
		e.preventDefault();		
		 
		 var ip_id=  jQuery(this).attr("data-gal");		
			
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "ip_number_del", "ip_id": ip_id 
						 },
						
						success: function(data){
							
	
							jQuery("#uu-edit-ipnumber-row-"+ip_id).slideUp();						
							
							
							}
					});
		return false;
	});
	
	
});
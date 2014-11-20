jQuery(document).ready(function($) {
	
	
	
	jQuery("body").on("click", ".uultra-do-integrity-checks", function(e) {
		
		
		e.preventDefault();		
		
		doIt=confirm(mant_confirmation);
		  
		if(doIt)
		 {
		 
			
					jQuery.ajax({
									type: 'POST',
									url: ajaxurl,
									data: {"action": "uulra_do_integrity_checks"},
									
									success: function(data){							
																
				
										jQuery("#uultra-integritycheck-results").html(data);						
										
										
										}
								});
								
								
		}
					return false;
				});
	
	
	
	
	
});
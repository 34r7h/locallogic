
jQuery(document).ready(function(){
	var keyup_timer
	
	jQuery('input[name="loc_pick_country_filter"]').keyup(function(){
																   
		var $obj =jQuery(this);
		jQuery(this).parent().find('ul').html(geodir_location_all_js_msg.LOCATION_PLEASE_WAIT);
		clearInterval(keyup_timer) ;
		keyup_timer = setTimeout(
			function(){
				
							var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 												                            jQuery.post(ajax_url,
							{	action: 'geodir_location_ajax', 
								gd_loc_ajax_action:'get_location',
								gd_formated_for : 'location_switcher',
								gd_which_location:'country',
								gd_country_val : $obj.val(),
							},
							function(data){
							
								$obj.parent().find('ul').html(data) ;
							});
					   }, 500) ; 
		
		
	});
	
	jQuery('input[name="loc_pick_region_filter"]').keyup(function(){
		var country_val = jQuery(this).parent().parent().find('input[name="loc_pick_country_filter"]').val();
		
		jQuery(this).parent().find('ul').html(geodir_location_all_js_msg.LOCATION_PLEASE_WAIT);
		var $obj =jQuery(this);
		clearInterval(keyup_timer) ;
		keyup_timer = setTimeout(
			function(){
				
							var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 												                            jQuery.post(ajax_url,
							{	action: 'geodir_location_ajax', 
								gd_loc_ajax_action:'get_location',
								gd_formated_for : 'location_switcher',
								gd_which_location:'region',
								gd_country_val : country_val ,
								gd_region_val : $obj.val(),
							},
							function(data){
							
								$obj.parent().find('ul').html(data) ;
							});
					   }, 500) ; 
		
	});
	
	jQuery('input[name="loc_pick_city_filter"]').keyup(function(){
															
		jQuery(this).parent().find('ul').html(geodir_location_all_js_msg.LOCATION_PLEASE_WAIT);														
		var country_val = jQuery(this).parent().parent().find('input[name="loc_pick_country_filter"]').val();
		var region_val = jQuery(this).parent().parent().find('input[name="loc_pick_region_filter"]').val();
		var $obj =jQuery(this);
		clearInterval(keyup_timer) ;
		keyup_timer = setTimeout(
			function(){
				
							var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 												                            jQuery.post(ajax_url,
							{	action: 'geodir_location_ajax', 
								gd_loc_ajax_action:'get_location',
								gd_formated_for : 'location_switcher',
								gd_which_location:'city',
								gd_country_val : country_val ,
								gd_region_val : region_val ,
								gd_city_val : $obj.val(),
							},
							function(data){
							
								$obj.parent().find('ul').html(data) ;
							});
					   }, 500) ; 
		
	});
	
	
	jQuery('.geodir-locListing_column ul').on('click',' .geodir_loc_arrow a' ,function(){
		
		var which_location = '' ,country_val = '' ,region_val = '', city_val='',ul_index_to_fill =0;
		jQuery(this).parents('ul').find('li').removeClass('geodir_active') ;
		jQuery(this).parents('li').addClass('geodir_active') ;
		jQuery(this).parents('.geodir-locListing_column').find('input').val(jQuery(this).parents('li').find('a').html());
		if(jQuery(this).parents('ul').attr('class') == 'geodir_country_column')
		{
			which_location = 'region' ; 
			
			country_val =jQuery(this).parents('li').find('a').html();
			jQuery(this).parents('.geodir_locListing_main').find('input').eq(1).val('');
			ul_index_to_fill = 1; 
		}
		else // region arrow is clicked
		{
			which_location = 'city' ; 
			
			region_val =jQuery(this).parents('li').find('a').html();
			jQuery(this).parents('.geodir_locListing_main').find('input').eq(2).val('');
			ul_index_to_fill =2
		}
		
		var ul_item = jQuery(this).parents('.geodir_locListing_main').find('.geodir-locListing_column').eq(ul_index_to_fill).find('ul').html(geodir_location_all_js_msg.LOCATION_PLEASE_WAIT) ;		
		var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 												                            jQuery.post(ajax_url,
							{	action: 'geodir_location_ajax', 
								gd_loc_ajax_action:'get_location',
								gd_formated_for : 'location_switcher',
								gd_which_location:which_location,
								gd_country_val : country_val ,
								gd_region_val : region_val ,
								gd_city_val : city_val,
							},
							function(data){
								ul_item.html(data) ;
							});
		
		
	})
	
	
	jQuery('.geodir_location_tab_container .geodir_location_tabs').bind('click',function(){
				var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 
				var tab = this;
				var tab_id = jQuery(this).data('location');
				var autoredirect = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('autoredirect');
				var show_every_where = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('showeverywhere');
				jQuery.post(ajax_url +'?action=geodir_location_ajax&gd_loc_ajax_action=fill_location&autoredirect='+autoredirect+'&gd_which_location='+tab_id+"&show_every_where=" + show_every_where,
				function(data){
					jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").html(data).chosen().trigger("chosen:updated");
					geodir_enable_click_on_chosen_list_item();
				});
				
				jQuery(tab).parents('.geodir_location_tab_container').find('.geodir_location_tabs').removeClass('gd-tab-active');
				jQuery(tab).addClass('gd-tab-active');
			
				geodir_location_switcher_chosen_ajax();
			});
			
			
		// Chosen selects
		if(jQuery("select.geodir_location_switcher_chosen").length > 0)
		{
			jQuery("select.geodir_location_switcher_chosen").chosen({no_results_text: geodir_location_all_js_msg.LOCATION_CHOSEN_NO_RESULT_TEXT});
			
		}
		
		if(jQuery("select.geodir_location_add_listing_chosen").length > 0)
		{
			jQuery("select.geodir_location_add_listing_chosen").chosen({no_results_text: geodir_location_all_js_msg.LOCATION_CHOSEN_NO_RESULT_TEXT});
			
		}
		
		// now add an ajax function when value is entered in chose select text field
		
		geodir_location_switcher_chosen_ajax();
		geodir_enable_click_on_chosen_list_item();
	
}); // end of document.ready jquery
	
	function geodir_location_switcher_chosen_ajax()
	{
		jQuery("select.geodir_location_switcher_chosen").each(function(){
			var curr_chosen = jQuery(this);
			var autoredirect = curr_chosen.data('autoredirect');
			var countrysearch= curr_chosen.data('countrysearch');
			var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 
			
			if(curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined)
			{
				var listfor = curr_chosen.parents('.geodir_location_tab_container').find('.gd-tab-active').data('location')
				
				
				var show_every_where = curr_chosen.data('showeverywhere');
				
				curr_chosen.ajaxChosen({
					keepTypingMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_KEEP_TYPE_TEXT,
					lookingForMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_LOOKING_FOR_TEXT,
					type: 'GET',
					url: ajax_url+'?action=geodir_location_ajax&gd_loc_ajax_action=fill_location&autoredirect='+autoredirect+'&gd_which_location='+listfor+'&show_every_where='+show_every_where ,
					dataType: 'html',
					success: function (data) {
						curr_chosen.html(data).chosen().trigger("chosen:updated");
						geodir_enable_click_on_chosen_list_item();
					}
				}, 
				null,
				{}
				);
			}	
			
		}
		

		);	
	}
	


/* Script for the Add new listing page, country/Region/City chosen */
	
jQuery(document).ready(function(){
	geodir_location_add_listing_chosen();						
	jQuery('select.geodir_location_add_listing_chosen').bind('change',function(){
		var curr_chosen = jQuery(this);
		var location_type = curr_chosen.data('location_type');
		var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 
		var $loader = '<div class="location_dl_loader" align="center" style="width:100%;"><img src="'+geodir_all_js_msg.geodir_plugin_url+'/geodirectory-assets/images/loadingAnimation.gif"  /></div>';
		var geodir_location_add_listing_all_chosen_container = curr_chosen.parents(".geodir_location_add_listing_all_chosen_container");
		
		var country_val='';
		var region_val = '' ;
		var city_val = '';
		
		
		
		if(location_type == 'country')
		{
			// update state/City and neighbour dropdown
			
			if(curr_chosen.attr('name') != 'post_country')
				return false;
			
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_country_chosen_div').find('input[name="geodir_location_add_listing_country_val"]').val(curr_chosen.val()) ;
			country_val =curr_chosen.val();
			
			
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').hide()
		geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').after($loader);
			
			jQuery.post(ajax_url,
				{	action:'geodir_location_ajax',
					gd_loc_ajax_action:'fill_location_on_add_listing',
					gd_which_location:'region',
					country_val: country_val 
					
				},
				 function(data){
				 
					if(data){
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').next('.location_dl_loader').remove();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').show();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').find('select').html(data).chosen().trigger("chosen:updated");
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').find('select').trigger("change");	
						geodir_location_add_listing_chosen();
					}
			});
		}
		
		if(location_type == 'region')
		{
			
			country_val = geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_country_chosen_div').find('input[name="geodir_location_add_listing_country_val"]').val() ;
			// set value of hidden region feld to the selected one.			
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').find('input[name="geodir_location_add_listing_region_val"]').val(curr_chosen.val()) ;
			region_val =curr_chosen.val();
			
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').hide()
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').after($loader);
			
			jQuery.post(ajax_url,
				{	action:'geodir_location_ajax',
					gd_loc_ajax_action:'fill_location_on_add_listing',
					gd_which_location:'city',
					country_val: country_val,
					region_val: region_val 
					
				},
				 function(data){
				 
					if(data){
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').next('.location_dl_loader').remove();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').show();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').find('select').html(data).chosen().trigger("chosen:updated");
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').find('select').trigger("change");	
						geodir_location_add_listing_chosen();
					}
			});
		}
		
		if(location_type =='city')
		{
			country_val = geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_country_chosen_div').find('input[name="geodir_location_add_listing_country_val"]').val() ;
			// set value of hidden region feld to the selected one.			
			region_val = geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').find('input[name="geodir_location_add_listing_region_val"]').val() ;
			
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').find('input[name="geodir_location_add_listing_city_val"]').val(curr_chosen.val()) ;
			
			city_val =curr_chosen.val();
			
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_container').show();
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_div').hide()
			geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_div').after($loader);
			
			
			jQuery.post(ajax_url,
				{	action:'geodir_location_ajax',
					gd_loc_ajax_action:'fill_location_on_add_listing',
					gd_which_location:'neighbourhood',
					country_val: country_val,
					region_val: region_val ,
					city_val: city_val ,
				},
				 function(data){
				 
					if(data){
						
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_container').show();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_div').next('.location_dl_loader').remove();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_div').show();
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_div').find('select').html(data).chosen().trigger("chosen:updated");
					}
					else
					{
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_div').next('.location_dl_loader').remove();	
						geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_neighbourhood_chosen_container').hide();
					}
			});
		}
		
	})	
});

function geodir_location_add_listing_chosen()
{
	jQuery("select.geodir_location_add_listing_chosen").each(function(){
		var curr_chosen = jQuery(this);
		var geodir_location_add_listing_all_chosen_container = curr_chosen.parents(".geodir_location_add_listing_all_chosen_container");
		var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url; 
		var obj_name = curr_chosen.prop('name');
		var obbj_info = obj_name.split('_');
		listfor = obbj_info[1];	
		var country_val='';
		var region_val = '' ;
		var city_val = '';
		country_val = geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_country_chosen_div').find('input[name="geodir_location_add_listing_country_val"]').val() ;
		
		region_val = geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_region_chosen_div').find('input[name="geodir_location_add_listing_region_val"]').val() ;
		
		city_val = geodir_location_add_listing_all_chosen_container.find('.geodir_location_add_listing_city_chosen_div').find('input[name="geodir_location_add_listing_city_val"]').val() ;
		
		if(curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined)
		{
			curr_chosen.ajaxChosen({
				keepTypingMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_KEEP_TYPE_TEXT,
					lookingForMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_LOOKING_FOR_TEXT,
				type: 'GET',
				url: ajax_url+'?action=geodir_location_ajax&gd_loc_ajax_action=fill_location_on_add_listing&gd_which_location='+listfor+'&country_val=' + country_val + '&region_val=' +region_val + '&city_val=' + city_val  ,
				dataType: 'html',
				success: function (data) {
					curr_chosen.html(data).chosen().trigger("chosen:updated");
					geodir_location_add_listing_chosen();
				}
			}, 
			null,
			{}
			);
		}	
		
	}
	

	);	
}

// script to make everywhere link clickable if its already selected or when onchange event is not called
function geodir_enable_click_on_chosen_list_item()
{
	jQuery('.chosen-results').bind('click', function(){
		var first_item_text = jQuery('.chosen-results').find( 'li[data-option-array-index="0"]').html() ;
		var selected_item_text = jQuery(this).parents('.geodir_location_sugestion').find('.chosen-single > span').html();
		if( first_item_text == selected_item_text  )
		{
			jQuery(this).parents('.geodir_location_sugestion').find('select').trigger("change")	;
		}
	})
}

	
function geodir_set_map_default_location(mapid, lat, lng){
	
	if(mapid != '' && lat != '' && lng != '' ){
		
		jQuery("#"+mapid).goMap();
		jQuery.goMap.map.setCenter(new google.maps.LatLng(lat, lng));
		baseMarker.setPosition(new google.maps.LatLng(lat, lng));
		updateMarkerPosition(baseMarker.getPosition());
		geocodePosition(baseMarker.getPosition());
	
	}
	
}
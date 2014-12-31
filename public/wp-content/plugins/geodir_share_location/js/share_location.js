function geodir_do_geolocation_on_load() 
{
	if (navigator.geolocation) 
	{
		navigator.geolocation.getCurrentPosition(geodir_position_success_on_load, geodir_position_error,{timeout: 10000 });
	}
	else
	{
		var error = {code:'-1'};	
		geodir_position_error(error);
	}
}

function geodir_position_error(err) {	
	var ajax_url = geodir_all_share_location_js_msg.geodir_admin_ajax_url;
	var msg;
	switch(err.code) {
	  case err.UNKNOWN_ERROR:
		msg = geodir_all_share_location_js_msg.UNKNOWN_ERROR;
		break;
	  case err.PERMISSION_DENINED:
		msg = geodir_all_share_location_js_msg.PERMISSION_DENINED;
		break;
	  case err.POSITION_UNAVAILABLE:
		msg = geodir_all_share_location_js_msg.POSITION_UNAVAILABLE;
		break;
	  case err.BREAK:
		msg = geodir_all_share_location_js_msg.BREAK;
		break;
	  case 3:
			geodir_position_success_on_load(null);
		break;	
	  default:
		msg = geodir_all_share_location_js_msg.DEFAUTL_ERROR;
		break;
	}
	
	jQuery.post(ajax_url,
	{	action: 'geodir_share_location', 
		geodir_ajax:'share_location',
		error: true,
		
	},
	function(data){
		//window.location = data;
	});
	alert(msg);
}
         

			
function geodir_position_success_on_load(position){
	
	var lat;
	var long;
	if(position != null ){
		var coords = position.coords || position.coordinate || position;
		lat = coords.latitude;
		long = coords.longitude;
	}					
	var ajax_url = geodir_all_share_location_js_msg.geodir_admin_ajax_url; 						 
	var request_param = geodir_all_share_location_js_msg.request_param;
	
	jQuery.post(ajax_url,
	{	action: 'geodir_share_location', 
		geodir_ajax:'share_location',
		lat:lat,
		long:long,
		request_param:request_param
	},
	function(data){
		window.location = data;
	});
}
	
jQuery(document).ready(function(){
								
	if( geodir_all_share_location_js_msg.ask_for_share_location)								
		geodir_do_geolocation_on_load();
})
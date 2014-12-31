// RUN THIS ASAP

(function(){


}());




// RUN THIS ON LOAD
jQuery(document).ready(function() {					
								
		if(geodir_advanced_search_js_msg.geodir_location_manager_active==1 && geodir_advanced_search_js_msg.geodir_enable_autocompleter_near==1){
		
				jQuery( ".snear" ).each(function () {
					jQuery(this).keyup(function() {
						jQuery(this).removeClass("near-country near-region near-city");	
					});
				});		
				
				jQuery("input[name=snear]").autocomplete(
					geodir_advanced_search_js_msg.geodir_admin_ajax_url+"?action=geodir_autocompleter_near_ajax_action",

					{
						delay:500,
						minChars:1,
						matchSubset:1,
						matchContains:1,
						cacheLength:1,
						formatItem:formatItemNear,
						onItemSelect:onSelectItemNear,
						autoFill:false
					}
				);
			
		}
								
	if(geodir_advanced_search_js_msg.geodir_enable_autocompleter==1){
		
					
				
				if(jQuery('.search_by_post').val()){gd_s_post_type =jQuery('.search_by_post').val();}else{gd_s_post_type ="gd_place";}
				jQuery('.search_by_post').change(function() {
						gd_s_post_type =jQuery(this).val();
						gdReplaceASC(gd_s_post_type);
						//alert(gd_s_post_type);
						//console.log(gdsa);
						jQuery.each(gdsa, function(index, item) {
							if(this.autocompleter){
								this.autocompleter.setExtraParams({post_type:gd_s_post_type});
								this.autocompleter.flushCache();
							}
						});
						
				});
											
				var gdsa = jQuery("input[name="+geodir_advanced_search_js_msg.autocomplete_field_name+"]").autocomplete(
					geodir_advanced_search_js_msg.geodir_admin_ajax_url+"?action=geodir_autocompleter_ajax_action",
					{
						delay:500,
						minChars:1,
						matchSubset:1,
						matchContains:1,
						cacheLength:1,
						formatItem:formatItem,
						onItemSelect:onSelectItem,
						autoFill:false,
						extraParams:{post_type:gd_s_post_type}
					}
				);
				
				
			
			
	}
				
				
});


// OTHER 



// STATIC FUNCTIONS

function gdGetLocation(box){
	
	jQuery('.snear').removeClass("near-country near-region near-city");// remove any location classes
	
	if(box && box.prop('checked') != true){gdClearUserLoc();return;}
	
	// Try HTML5 geolocation
  if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
		lat = position.coords.latitude;									  
		lon = position.coords.longitude;
		
		gdSetUserLocation(position.coords.latitude,position.coords.longitude,1);
		
		//alert(position.coords.latitude+','+position.coords.longitude);

  },gdLocationError,gdLocationOptions); }else {
    // Browser doesn't support Geolocation
    alert('error');
  }
	
}

function gdClearUserLoc(){
	lat='';lon='';my_location='';userMarkerActive=true; /* trick script to not add marker */gdSetUserLocation(lat,lon,my_location);
}


function setusermarker(new_lat,new_lon,map_id){
map_id_arr.push(map_id);	
	var image = new google.maps.MarkerImage(
							geodir_advanced_search_js_msg.geodir_advanced_search_plugin_url+'/css/map_me.png',
							null, // size
							null, // origin
							new google.maps.Point( 8, 8 ), // anchor (move to center of marker)
							new google.maps.Size( 17, 17 ) // scaled size (required for Retina display icon)
						);
	jQuery('#'+map_id).goMap();
	if(gdUmarker['visible']){return;}// if marker exists bail
var coord = new google.maps.LatLng(lat,lon);
gdUmarker  = jQuery.goMap.createMarker({
							optimized: false,
							flat: true,
							draggable: true,
							id: 'map_me' ,
							title: 'Set Location' ,
							position: coord,
							visible: true,
							clickable: true,
							icon: image
						});

  jQuery.goMap.createListener({type:'marker', marker:'map_me'}, 'dragend', function() { 
			   latLng = gdUmarker.getPosition();
  				lat = latLng.lat();
				lon = latLng.lng();
				gdSetUserLocation(lat,lon,0)
            });
userMarkerActive=true;
	
}

function moveUserMarker(lat,lon){
			var coord = new google.maps.LatLng(lat,lon);
			 // markerIDArray['map_me'].setPosition(coord );
			  gdUmarker.setPosition(coord );
			  //map.panTo(coord);
	
}

function removeUserMarker(){
			 // gdUmarker.removeMarker();
			 if (typeof goMap != 'undefined'){
			  jQuery.goMap.removeMarker('map_me');
			 }
			  userMarkerActive=false;
}

function gdLocationError(error)
  {
  switch(error.code) 
    {
    case error.PERMISSION_DENIED:
      alert(geodir_advanced_search_js_msg.PERMISSION_DENINED);
      break;
    case error.POSITION_UNAVAILABLE:
      alert(geodir_advanced_search_js_msg.POSITION_UNAVAILABLE);
      break;
    case error.TIMEOUT:
      alert(geodir_advanced_search_js_msg.DEFAUTL_ERROR);
      break;
    case error.UNKNOWN_ERROR:
      alert(geodir_advanced_search_js_msg.UNKNOWN_ERROR);
      break;
    }
}



function gdSetupUserLoc(){	
if(my_location){
jQuery('.geodir-search .fa-compass').css( "color", "#087CC9" );
jQuery('.gt_near_me_s').prop('checked', true);
jQuery('.snear').val(geodir_advanced_search_js_msg.msg_Near+' '+geodir_advanced_search_js_msg.msg_Me);
jQuery('.sgeo_lat').val(lat);
jQuery('.sgeo_lon').val(lon);
}else{

	if(lat && lon){
		jQuery('.geodir-search .fa-compass').css( "color", "#087CC9" );
		jQuery('.gt_near_me_s').prop('checked', true);
		//jQuery('.snear').val('Near: '+lat.substring(0,8)+','+lon.substring(0,8));
		jQuery('.snear').val(geodir_advanced_search_js_msg.msg_Near+' '+geodir_advanced_search_js_msg.msg_User_defined);
		jQuery('.sgeo_lat').val(lat);
		jQuery('.sgeo_lon').val(lon);
	}
	else if(jQuery('.snear').length && jQuery('.snear').val().match("^"+geodir_advanced_search_js_msg.msg_Near)){
		jQuery('.geodir-search .fa-compass').css( "color", "" );	
		jQuery('.gt_near_me_s').prop('checked', false);
		jQuery('.snear').val(''); 
		jQuery('.snear').blur();
		jQuery('.sgeo_lat').val('');
		jQuery('.sgeo_lon').val('');
	}
}
	
}


function gdSetUserLocation(lat,lon,my_loc){
	
if(my_loc){my_location=1;}else{my_location=0;}
gdSetupUserLoc();

	
if(userMarkerActive==false){
setusermarker(lat,lon);//createUserMarker(lat,lon,true);//set marker on map
}
else if(lat && lon){moveUserMarker(lat,lon);}
else{removeUserMarker();}

    jQuery.ajax({
       // url: url,
        url: geodir_advanced_search_js_msg.geodir_admin_ajax_url,
        type: 'POST',
        dataType: 'html',
		data: {action: 'gd_set_user_location',lat:lat,lon:lon,myloc:my_location},
        beforeSend: function () {
        },
        success: function (data, textStatus, xhr) {
			//alert(data);
		},
        error: function (xhr, textStatus, errorThrown) {
			alert(textStatus);
        }
    });
	
}

function gdasShowRange(el){
	jQuery('.gdas-range-value-out').html(jQuery(el).val());
	gdasSetRange(jQuery(el).val());//set the range as a session

}

function gdasSetRange(range){
	var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
	jQuery.post(ajax_url,
	{	action: 'geodir_set_near_me_range', 
		range:range
	},
	function(data){
		//alert(data);
	});

}
// SHARE LOCATION SCRIPT
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
	var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
	var msg;
	switch(err.code) {
	  case err.UNKNOWN_ERROR:
		msg = geodir_advanced_search_js_msg.UNKNOWN_ERROR;
		break;
	  case err.PERMISSION_DENINED:
		msg = geodir_advanced_search_js_msg.PERMISSION_DENINED;
		break;
	  case err.POSITION_UNAVAILABLE:
		msg = geodir_advanced_search_js_msg.POSITION_UNAVAILABLE;
		break;
	  case err.BREAK:
		msg = geodir_advanced_search_js_msg.BREAK;
		break;
	  case 3:
			geodir_position_success_on_load(null);
		break;	
	  default:
		msg = geodir_advanced_search_js_msg.DEFAUTL_ERROR;
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
	var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url; 						 
	var request_param = geodir_advanced_search_js_msg.request_param;
	
	jQuery.post(ajax_url,
	{	action: 'geodir_share_location', 
		geodir_ajax:'share_location',
		lat:lat,
		long:long,
		request_param:request_param
	},
	function(data){
		//alert(data);
		//console.log(data);
		window.location = data;
	});
}
	
jQuery(document).ready(function(){
								
	if( geodir_advanced_search_js_msg.ask_for_share_location && geodir_advanced_search_js_msg.geodir_autolocate_disable!=1){							
		if(geodir_advanced_search_js_msg.geodir_autolocate_ask==1){
			if(confirm(geodir_advanced_search_js_msg.geodir_autolocate_ask_msg)){geodir_do_geolocation_on_load();}else{geodir_position_do_not_share();}
		}else{
			geodir_do_geolocation_on_load();
		}
		
	}
})


function geodir_position_do_not_share(){
	
	var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
	jQuery.post(ajax_url,
	{	action: 'geodir_do_not_share_location', 
		geodir_ajax:'share_location',
		
	},
	function(data){
		//alert(data);
		//console.log(data);
		
	});
}

// AUTOCOMPLETER FUNCTIONS

function formatItemNear(row) {
			console.log(row);
			var attr;
			if(row.length == 3){
				attr = "attr=\"" + row[2] + "\"";
			} else {
				attr = "";
			}
			return row[0] + "<span "+attr+"></span>"
		}
		
function gdReplaceASC(stype){
			
			if(!stype){return;}
			
			jQuery('.geodir_submit_search').css('visibility', 'visible');
			jQuery('.customize_filter').hide('slow');
			
			
			var button = '';
			var html = '';
			 jQuery.ajax({
				url: geodir_advanced_search_js_msg.geodir_admin_ajax_url,
				type: 'POST',
				dataType: 'html',
				data: {action: 'geodir_advance_search_button_ajax',stype:stype},
				beforeSend: function () {
				},
				success: function (data, textStatus, xhr) {
					jQuery('body').removeClass('gd-multi-datepicker');
					if(data){gdGetCustomiseHtml(stype,data);}
					else{gdSetupAjaxAdvancedSearch('','');}	
					geodir_reposition_compass();
					//jQuery('.geodir-filter-container').html(data);
				},
				error: function (xhr, textStatus, errorThrown) {
					alert(textStatus);
				}
			});	
			
			
		}
		
function gdGetCustomiseHtml(stype,button){
			
			 jQuery.ajax({
					url: geodir_advanced_search_js_msg.geodir_admin_ajax_url,
					type: 'POST',
					dataType: 'html',
					data: {action: 'gd_advancedsearch_customise',stype:stype},
					beforeSend: function () {
					},
					success: function (data, textStatus, xhr) {
						gdSetupAjaxAdvancedSearch(button,data);
						geodir_reposition_compass();
						//jQuery('.geodir-filter-container').html(data);
					},
					error: function (xhr, textStatus, errorThrown) {
						alert(textStatus);
					}
				});
			
		}
		
function gdSetupAjaxAdvancedSearch(button,html){
			
			if(button){
				if(jQuery('.showFilters').length){}else{jQuery('.geodir_submit_search').after(button);}
			jQuery('body').addClass('geodir_advance_search');
			jQuery('.geodir-filter-container').html(html);
			}else{
				jQuery('.showFilters').remove();
				jQuery('.geodir-filter-container').html();
				jQuery('body').removeClass('geodir_advance_search');
			}
			geodir_setup_submit_search();
		}
		
function onSelectItem(row,$form){
			if(geodir_advanced_search_js_msg.geodir_autocompleter_autosubmit==1){
			
				if($form.find(' input[name="snear"]').val()=='<?php echo addslashes($default_near_text);?>'){
					jQuery('input[name="snear"]').val('');
				}
				if(typeof(jQuery(row).find('span').attr('attr')) != 'undefined'){
					$form.submit();
				} else {
						$form.submit();
				}
			}
			else{
				jQuery(row).parents("form").find('input[name="'+geodir_advanced_search_js_msg.autocomplete_field_name+'"]').focus();
			}
		}
		
function onSelectItemNear(row,$form){
			
			//gdClearUserLoc(); // we now set this with sessions
			
			
			nClass = "";
			lType = "";
			if(row.extra[2]==1){nClass = "near-country"; lType = "Country";}
			else if(row.extra[2]==2){nClass = "near-region"; lType = "Region";}
			else if(row.extra[2]==3){nClass = "near-city"; lType = "City";}
			
			fillVal = "In: "+row.extra[0]+" ("+lType+")";
			
			
			$form.find(' input[name="snear"]').val(fillVal);
			
			
			
			if($form.find(' input[name="set_location_val"]').length){
			$form.find(' input[name="set_location_val"]').val(row.extra[1]);
			}else{
				$form.find(' input[name="snear"]').after('<input name="set_location_val" type="hidden" value="'+row.extra[1]+'" />');
			}
			
			if($form.find(' input[name="set_location_type"]').length){
			$form.find(' input[name="set_location_type"]').val(row.extra[2]);
			}else{
				$form.find(' input[name="snear"]').after('<input name="set_location_type" type="hidden" value="'+row.extra[2]+'" />');
			}
			
			
			
			$form.find(' input[name="snear"]').removeClass("near-country near-region near-city");
			if(nClass){$form.find(' input[name="snear"]').addClass(nClass);}		
			
			if(geodir_advanced_search_js_msg.geodir_autocompleter_autosubmit_near==1){
				setTimeout(
				  function() 
				  {
					$form.find('.geodir_submit_search').click();
				  }, 100);
				
			}
}
		
function formatItem(row) {
			var attr;
			if(row.length == 3){
				attr = "attr=\"" + row[2] + "\"";
			} else {
				attr = "";
			}
			return row[0] + "<span "+attr+"></span>"
		}
		
		
function geodir_insert_compass(){
								
jQuery('.snear').each(function(){
   var $this = jQuery(this);

   jQuery('<span class="near-compass" data-dropdown=".gd-near-me-dropdown"></span>').css({
       position:'absolute',
       left: $this.position().left + $this.outerWidth()+parseInt($this.css('margin-left'))-($this.innerHeight()),
       top: $this.position().top + parseInt($this.css('margin-top')) + ($this.outerHeight()-$this.innerHeight())/2,
	   fontSize: ($this.innerHeight()+ parseInt($this.css('margin-top')))*0.95,
	   'line-height': '0px'
   }).html('<i class="fa fa-compass"></i>').data('inputEQ', $this.index()).insertAfter($this);
});
	
	
	jQuery(window).resize(function() {
		geodir_reposition_compass();
	});	
	

}

function geodir_reposition_compass(){
	  jQuery('.snear').each(function(){
   var $this = jQuery(this);
    
   jQuery($this ).next('.near-compass').css({
       position:'absolute',
       left: $this.position().left + $this.outerWidth()+parseInt($this.css('margin-left'))-($this.innerHeight()),
       top: $this.position().top + parseInt($this.css('margin-top')) + ($this.outerHeight()-$this.innerHeight())/2,
	   fontSize: ($this.innerHeight()+ parseInt($this.css('margin-top')))*0.95,
	   'line-height': '0px'
   });
});
}
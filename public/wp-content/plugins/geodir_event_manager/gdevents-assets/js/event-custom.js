// JavaScript Document
jQuery(document).ready(function() {
	jQuery('#geodir_link_business_autofill').bind("click", function() {
		var place_id = jQuery('select[name="geodir_link_business"]').val();
		var nonce = jQuery('input[name="geodir_link_business_nonce"]').val();
		if(place_id != '') {
			var ajax_url = geodir_event_alert_js_var.geodir_event_ajax_url;
			jQuery.post(ajax_url, {
				_wpnonce: nonce,
				auto_fill: "geodir_business_autofill",
				place_id: place_id
			}).done(function(data) {
				if(jQuery.trim(data) != '') {
					var address = false;
					var json = jQuery.parseJSON(data);
					jQuery.each(json, function(i, item) {
						if(item.key == 'text') {
							if(item.value == false) item.value = '';
							jQuery('input[name="' + i + '"]').val(item.value);
						}
						if(item.key == 'textarea') {
							if(item.value == false) item.value = '';
							jQuery('#' + i).val(item.value);
							if(typeof tinymce != 'undefined') {
								if(tinyMCE.get('content') && i == 'post_desc') {
									i = 'content';
									jQuery('#title').focus();
								}
								if(tinymce.editors.length > 0 && tinyMCE.get(i)) tinyMCE.get(i).setContent(item.value);
							}
						}
						if(i == 'post_address') address = true;
						if(i == 'post_city' || i == 'post_region' || i == 'post_country') {
							if(jQuery("#" + i + " option:contains('" + item.value + "')").length == 0) {
								jQuery("#" + i).append('<option value="' + item.value + '">' + item.value + '</option>');
							}
							jQuery('#' + i + ' option[value="' + item.value + '"]').attr("selected", true);
							jQuery("#" + i).trigger("chosen:updated");
						}
					});
					if(address) jQuery('#post_set_address_button').click();
				}
			});
		}
	});
	
	// now add an ajax function when value is entered in chose select text field
	geodir_link_business_chosen_ajax();
});

function geodir_link_business_chosen_ajax() {
	jQuery("select#geodir_link_business").each(function() {
		var curr_chosen = jQuery(this);
		var ajax_url = geodir_event_alert_js_var.geodir_event_ajax_url;
		var obj_name = curr_chosen.prop('name');
		var obbj_info = obj_name.split('_');
		listfor = obbj_info[1];
		if(curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined) {
			curr_chosen.ajaxChosen({
				keepTypingMsg: geodir_event_alert_js_var.EVENT_CHOSEN_KEEP_TYPE_TEXT,
				lookingForMsg: geodir_event_alert_js_var.EVENT_CHOSEN_LOOKING_FOR_TEXT,
				type: 'GET',
				url: ajax_url + '&task=geodir_fill_listings',
				dataType: 'html',
				success: function(data) {
					curr_chosen.html(data).chosen().trigger("chosen:updated");
				}
			}, null, {});
		}
	});
}
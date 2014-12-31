jQuery(document).ready(function($){
	
	var submit_button = $('#publish_listing .geodir_publish_button');
	
	//$('#publish_listing input[name="paymentmethod"]').on('change', function(e){ submit_button.click(); });
	submit_button.on('click', function(event){
		
		var that = $(this);
		var ev = event;
		
		
		
		var payment_method 	= $('#publish_listing input[name="paymentmethod"]:checked').val();
		var price_select 	= $('#publish_listing input[name="price_select"]').val();
		var coupon_code 	= $('#publish_listing input[name="coupon_code"]').val();
		var post_type 	= $('#preview_map_canvas_posttype').val();
		var post_title		= $('.entry-title').text();
		
		if( payment_method === 'stripe' ) {
			ev.preventDefault();
			$('.geodir_publish_button').attr('disabled','disabled'); // disable that button
			
			var handler = StripeCheckout.configure({
				key: sData.publishablekey,
				currency: sData.currency,
				token: function( token ) {
					$('<input>',{
						type:	'hidden',
						name:	'stripe_token',
						value:	token.id
					}).appendTo('#publish_listing');
					
					$('<input>',{
						type:	'hidden',
						name:	'stripe_email',
						value:	token.email
					}).appendTo('#publish_listing');
					
					//console.dir( token );
					that.unbind(ev);
					$('#publish_listing').submit();
				}
			});
			
			$.getJSON( sData.ajaxurl, {
				action: 'geodir_stripe_get_payment_info',
				_nonce: sData.nonce,
				price: price_select,
				coupon_code :coupon_code,
				post_type:post_type
			}, function( data ){console.log( data );
				handler.open({
				    name: 'Pay for "' + post_title + '"',
				    description: 'Package chosen: ' + data.title,
			    	amount: ( parseInt( data.amount ) * 100 )
				});
				$('.geodir_publish_button').removeAttr('disabled');// re-enable the pay button incase they close stripe window
			});
		} else { 
		return true;
			//that.unbind(ev);
			//$('.geodir_publish_button').removeAttr('disabled');
		} //re-enable it
	});
});

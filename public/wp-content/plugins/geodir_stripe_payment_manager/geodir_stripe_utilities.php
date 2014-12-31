<?php
	function log_write( $text ) {return;// enable/disable logs
		$path = plugin_dir_path( __FILE__ ) .  'geodir_stripe_log.txt';
		if( !$handle = fopen( $path,'a') )
			file_put_contents( $path, 'vita di merda' . "\n" );
		
		if( fwrite( $handle, date("H:i:s m.d.Y: ") . (string) $text . "\n" ) === false )
			file_put_contents( $path, 'vita ancora piu\' di merda');
		
		fclose( $handle );
	}
	/*function get_id_by_slug($page_slug, $post_type = 'page', $output = OBJECT ) { 
		global $wpdb; 
	  	$page = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'", $page_slug, $post_type ) ); 
	    return $page->ID; 
	}*/
	function geodir_stripe_tryCatch( $action = false ) {
			
		try {
			$result = $action;
		} catch(Stripe_CardError $e) { // Since it's a decline, Stripe_CardError will be caught 
			
			$result = false;
		
			$body = $e->getJsonBody(); 
			$err = $body['error']; 
			log_write('Status is:' . $e->getHttpStatus() . "\n"); 
			log_write('Type is:' . $err['type'] . "\n"); 
			log_write('Code is:' . $err['code'] . "\n"); // param is '' in this case print('Param is:' . $err['param'] . "\n"); 
			log_write('Message is:' . $err['message'] . "\n"); 
			
		} catch (Stripe_InvalidRequestError $e) { // Invalid parameters were supplied to Stripe's API 
				
			$result = false;
		
			$body = $e->getJsonBody(); 
			$err = $body['error']; 
			log_write('Status is:' . $e->getHttpStatus() . "\n"); 
			log_write('Type is:' . $err['type'] . "\n"); 
			log_write('Code is:' . $err['code'] . "\n"); // param is '' in this case print('Param is:' . $err['param'] . "\n"); 
			log_write('Message is:' . $err['message'] . "\n"); 
			
		} catch (Stripe_AuthenticationError $e) { // Authentication with Stripe's API failed // (maybe you changed API keys recently) 
				
			$result = false;
		
			$body = $e->getJsonBody(); 
			$err = $body['error']; 
			log_write('Status is:' . $e->getHttpStatus() . "\n"); 
			log_write('Type is:' . $err['type'] . "\n"); 
			log_write('Code is:' . $err['code'] . "\n"); // param is '' in this case print('Param is:' . $err['param'] . "\n"); 
			log_write('Message is:' . $err['message'] . "\n"); 
			
		} catch (Stripe_ApiConnectionError $e) { // Network communication with Stripe failed 
				
			$result = false;
		
			$body = $e->getJsonBody(); 
			$err = $body['error']; 
			log_write('Status is:' . $e->getHttpStatus() . "\n"); 
			log_write('Type is:' . $err['type'] . "\n"); 
			log_write('Code is:' . $err['code'] . "\n"); // param is '' in this case print('Param is:' . $err['param'] . "\n"); 
			log_write('Message is:' . $err['message'] . "\n"); 
			
		} catch (Stripe_Error $e) { // Display a very generic error to the user, and maybe send // yourself an email 
				
			$result = false;
		
			$body = $e->getJsonBody(); 
			$err = $body['error']; 
			log_write('Status is:' . $e->getHttpStatus() . "\n"); 
			log_write('Type is:' . $err['type'] . "\n"); 
			log_write('Code is:' . $err['code'] . "\n"); // param is '' in this case print('Param is:' . $err['param'] . "\n"); 
			log_write('Message is:' . $err['message'] . "\n"); 
			
		} catch (Exception $e) { // Something else happened, completely unrelated to Stripe 
				
			$result = false;
		
			$body = $e->getJsonBody(); 
			$err = $body['error']; 
			log_write('Status is:' . $e->getHttpStatus() . "\n"); 
			log_write('Type is:' . $err['type'] . "\n"); 
			log_write('Code is:' . $err['code'] . "\n"); // param is '' in this case print('Param is:' . $err['param'] . "\n"); 
			log_write('Message is:' . $err['message'] . "\n"); 
			
		}
		
		return $result;
		
	}
?>
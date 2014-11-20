<h3><?php _e('Payment Gateways Settings','xoousers'); ?></h3>
<form method="post" action="">
<input type="hidden" name="update_settings" />

<div class="user-ultra-sect ">
  <h3><?php _e('PayPal Settings','xoousers'); ?></h3>
  
  <p><?php _e('Here you can configure PayPal if you wish to accept paid registrations','xoousers'); ?></p>
  
  
  <table class="form-table">
<?php 

$this->create_plugin_setting(
                'checkbox',
                'gateway_paypal_active',
                __('Activate PayPal','xoousers'),
                '1',
                __('If checked, PayPal will be activated as payment method','xoousers'),
                __('If checked, PayPal will be activated as payment method','xoousers')
        ); 

$this->create_plugin_setting(
        'input',
        'gateway_paypal_email',
        __('PayPal Email Address','xoousers'),array(),
        __('Enter email address associated to your PayPal account.','xoousers'),
        __('Enter email address associated to your PayPal account.','xoousers')
);

$this->create_plugin_setting(
        'input',
        'gateway_paypal_sandbox_email',
        __('Paypal Sandbox Email Address','xoousers'),array(),
        __('This is not used for production, you can use this email for testing.','xoousers'),
        __('This is not used for production, you can use this email for testing.','xoousers')
);

$this->create_plugin_setting(
        'input',
        'gateway_paypal_currency',
        __('Currency','xoousers'),array(),
        __('Please enter the currency, example USD.','xoousers'),
        __('Please enter the currency, example USD.','xoousers')
);

$this->create_plugin_setting(
	'select',
	'gateway_paypal_mode',
	__('Mode','xoousers'),
	array(
		1 => __('Production Mode','xoousers'), 
		2 => __('Test Mode (Sandbox)','xoousers')
		),
		
	__('.','xoousers'),
  __('.','xoousers')
       );




		
?>
</table>

  
</div>


<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','xoousers'); ?>"  />
	
</p>

</form>
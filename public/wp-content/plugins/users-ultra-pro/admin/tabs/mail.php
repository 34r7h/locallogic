<h3><?php _e('Notifications Settings','xoousers'); ?></h3>
<form method="post" action="">
<input type="hidden" name="update_settings" />

<div class="user-ultra-sect ">
  <h3><?php _e('Advanced Email Options','xoousers'); ?></h3>  
  <p><?php _e('Here you can control how Users Ultra Pro will send the notification to your users.','xoousers'); ?></p>
  
   <table class="form-table">
<?php 
$this->create_plugin_setting(
                'checkbox',
                'uultra_smtp_mailing_html_txt',
                __('Send as HTML','xoousers'),
                'html',
                __('If checked the email format will be HTML. By default text/plain text format is used.','xoousers'),
                __('If checked the email format will be HTML. By default text/plain text format is used.','xoousers')
        ); 

$this->create_plugin_setting(
        'input',
        'messaging_send_from_name',
        __('Send From Name','xoousers'),array(),
        __('Enter the your name or company name here.','xoousers'),
        __('Enter the your name or company name here.','xoousers')
);

$this->create_plugin_setting(
        'input',
        'messaging_send_from_email',
        __('Send From Email','xoousers'),array(),
        __('Enter the email address to be used when sending emails.','xoousers'),
        __('Enter the email address to be used when sending emails.','xoousers')
);

$this->create_plugin_setting(
	'select',
	'uultra_smtp_mailing_mailer',
	__('Mailer:','xoousers'),
	array(
		'mail' => __('Use the PHP mail() function to send emails','xoousers'),
		'smtp' => __('Send all Users Ultra emails via SMTP','xoousers'), 
		'mandrill' => __('Send all Users Ultra emails via Mandrill','xoousers'), 
		
		),
		
	__('Specify which mailer method Users Ultra should use when sending emails.','xoousers'),
  __('Specify which mailer method Users Ultra should use when sending emails.','xoousers')
       );
	   
$this->create_plugin_setting(
                'checkbox',
                'uultra_smtp_mailing_return_path',
                __('Return Path','xoousers'),
                '1',
                __('Set the return-path to match the From Email','xoousers'),
                __('Set the return-path to match the From Email','xoousers')
        ); 
?>
 </table>
 <p> <strong><?php _e('This options should be set only if you have chosen to send email via SMTP','xoousers'); ?></strong></p>
  <table class="form-table">
 <?php
$this->create_plugin_setting(
        'input',
        'uultra_smtp_mailing_host',
        __('SMTP Host:','xoousers'),array(),
        __('Specify host name or ip address.','xoousers'),
        __('Specify host name or ip address.','xoousers')
); 

$this->create_plugin_setting(
        'input',
        'uultra_smtp_mailing_port',
        __('SMTP Port:','xoousers'),array(),
        __('Specify Port.','xoousers'),
        __('Specify Port.','xoousers')
); 


$this->create_plugin_setting(
	'select',
	'uultra_smtp_mailing_encrytion',
	__('Encryption:','xoousers'),
	array(
		'none' => __('No encryption','xoousers'),
		'ssl' => __('Use SSL encryption','xoousers'), 
		'tls' => __('Use TLS encryption','xoousers'), 
		
		),
		
	__('Specify the encryption method.','xoousers'),
  __('Specify the encryption method.','xoousers')
       );
	   
$this->create_plugin_setting(
	'select',
	'uultra_smtp_mailing_authentication',
	__('Authentication:','xoousers'),
	array(
		'false' => __('No. Do not use SMTP authentication','xoousers'),
		'true' => __('Yes. Use SMTP Authentication','xoousers'), 
		
		),
		
	__('Specify the authentication method.','xoousers'),
  __('Specify the authentication method.','xoousers')
       );

$this->create_plugin_setting(
        'input',
        'uultra_smtp_mailing_username',
        __('Username:','xoousers'),array(),
        __('Specify Username.','xoousers'),
        __('Specify Username.','xoousers')
); 

$this->create_plugin_setting(
        'input',
        'uultra_smtp_mailing_password',
        __('Password:','xoousers'),array(),
        __('Input Password.','xoousers'),
        __('Input Password.','xoousers')
); 


 ?>
 
 </table>
 
 
 <p><strong><?php _e('This options should be set only if you have chosen to send email via Mandrill','xoousers'); ?></strong></p>

  <table class="form-table">
 <?php
$this->create_plugin_setting(
        'input',
        'uultra_mandrill_api_key',
        __('Mandrill API Key:','xoousers'),array(),
        __('Specify Mandrill API. Find out more info here: https://mandrillapp.com/api/docs/','xoousers'),
        __('Specify Mandrill API.','xoousers')
); 

?>
 
 </table>
</div>

<div class="user-ultra-sect ">
  <h3><?php _e('Custom Messages','xoousers'); ?></h3>
  
  <p><?php _e('This message will be displayed in the User Panel Controls','xoousers'); ?></p>
  
   <table class="form-table">
<?php 

$this->create_plugin_setting(
        'input',
        'messaging_private_all_users',
        __('Message To Display','xoousers'),array(),
        __('This message will be displayed in the User Panel Controls','xoousers'),
        __('This message will be displayed in the User Panel Controls','xoousers')
);

?>
  
 
 </table>

</div>

<div class="user-ultra-sect ">
  <h3><?php _e('Welcome Email Address','xoousers'); ?></h3>
  
  <p><?php _e('This is the welcome email that is sent to the client when registering a new account','xoousers'); ?></p>
  
  
  <table class="form-table">
<?php 


$this->create_plugin_setting(
        'textarea',
        'messaging_welcome_email_client',
        __('Client Welcome Message','xoousers'),array(),
        __('This message will be sent to the user.','xoousers'),
        __('This message will be sent to the user.','xoousers')
);

$this->create_plugin_setting(
        'textarea',
        'messaging_welcome_email_with_activation_client',
        __('Account Activation Message','xoousers'),array(),
        __('This message will be sent to the user if they need to activate the account by using the activation link.','xoousers'),
        __('This message will be sent to the user if they need to activate the account by using the activation link.','xoousers')
);

//paid
$this->create_plugin_setting(
        'textarea',
        'messaging_welcome_email_client',
        __('Client Account Upgrade Message','xoousers'),array(),
        __('This message will be sent to the user when upgrading.','xoousers'),
        __('This message will be sent to the user when upgrading.','xoousers')
);

$this->create_plugin_setting(
        'textarea',
        'messaging_welcome_email_admin',
        __('Admin Account Upgrade Message','xoousers'),array(),
        __('This message will be sent to the admin when a user upgrades.','xoousers'),
        __('This message will be sent to the admin when a user upgrades.','xoousers')
);
//end paid

$this->create_plugin_setting(
        'textarea',
        'messaging_re_send_activation_link',
        __('Resend Activation Link','xoousers'),array(),
        __('This message will be sent to the user when clicking the re-send activation option.','xoousers'),
        __('This message will be sent to the user when clicking the re-send activation option.','xoousers')
);

$this->create_plugin_setting(
        'textarea',
        'account_verified_sucess_message_body',
        __('Account Verified Message','xoousers'),array(),
        __('This message will be sent to the users when they verify their accounts.','xoousers'),
        __('This message will be sent to the users when they verify their accounts.','xoousers')
);

$this->create_plugin_setting(
        'textarea',
        'password_reset_confirmation',
        __('Password Changed Confirmation','xoousers'),array(),
        __('This message will be sent to the user when the password has been reset.','xoousers'),
        __('This message will be sent to the user when the password has been reset.','xoousers')
);


$this->create_plugin_setting(
        'textarea',
        'messaging_admin_moderation_user',
        __('Client Admin Approval Email','xoousers'),array(),
        __('This message will be sent to the user if the account needs to be approved by the admin.','xoousers'),
        __('This message will be sent to the user if the account needs to be approved by the admin.','xoousers')
);
$this->create_plugin_setting(
        'textarea',
        'messaging_admin_moderation_admin',
        __('Admin New User Approval Email','xoousers'),array(),
        __('This message will be sent to the admin if an account needs to be approved manually.','xoousers'),
        __('This message will be sent to the admin if an account needs to be approved manually.','xoousers')
);


$this->create_plugin_setting(
        'textarea',
        'messaging_welcome_email_client_admin',
        __('Admin New User Message','xoousers'),array(),
        __('This message will be sent to admin.','xoousers'),
        __('This message will be sent to admin.','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'messaging_welcome_email_with_activation_admin',
        __('Admin Pending Activation Message','xoousers'),array(),
        __('This message will be sent to admin if the user needs manual activation.','xoousers'),
        __('This message will be sent to admin if the user needs manual activation.','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'messaging_user_pm',
        __('User Private Message','xoousers'),array(),
        __('This message will be sent to users when other users send a private message.','xoousers'),
        __('This message will be sent to users when other users send a private message.','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'messaging_user_pm_from_admin',
        __('Private Message From Admin','xoousers'),array(),
        __('This message will be sent to users when the admin sends a message.','xoousers'),
        __('This message will be sent to users when the admin sends a message','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'message_friend_request',
        __('Friend Request','xoousers'),array(),
        __('This message is sent to the users when a friend request is sent','xoousers'),
        __('This message is sent to the users when a friend request is sent','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'reset_lik_message_body',
        __('Password Reset','xoousers'),array(),
        __('This message will be sent to users when requesting a new password.','xoousers'),
        __('This message will be sent to users when requesting a new password.','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'admin_account_active_message_body',
        __('Account Activation','xoousers'),array(),
        __('This message is sent when the admin approves the user account.','xoousers'),
        __('This message is sent when the admin approves the user account.','xoousers')
		
);

$this->create_plugin_setting(
        'textarea',
        'admin_account_deny_message_body',
        __('Deny Account Activation','xoousers'),array(),
        __('This message is sent when the admin do not approve the user account.','xoousers'),
        __('This message is sent when the admin do not approve the user account.','xoousers')
		
);




		
?>
</table>

  
</div>


<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','xoousers'); ?>"  />

</p>

</form>
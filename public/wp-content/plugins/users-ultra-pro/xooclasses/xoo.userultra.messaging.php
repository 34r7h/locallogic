<?php
class XooMessaging extends XooUserUltraCommon 
{
	var $mHeader;
	var $mEmailPlainHTML;
	var $mHeaderSentFromName;
	var $mHeaderSentFromEmail;
	var $mCompanyName;
	

	function __construct() 
	{
		$this->setContentType();
		$this->setFromEmails();				
		$this->set_headers();	
		
	}
	
	function setFromEmails() 
	{
		global $xoouserultra;
			
		$from_name =  $this->get_option('messaging_send_from_name'); 
		$from_email = $this->get_option('messaging_send_from_email'); 	
		if ($from_email=="")
		{
			$from_email =get_option('admin_email');
			
		}		
		$this->mHeaderSentFromName=$from_name;
		$this->mHeaderSentFromEmail=$from_email;	
		
    }
	
	function setContentType() 
	{
		global $xoouserultra;			
				
		if( $this->get_option('uultra_smtp_mailing_html_txt')=='html')
		{
			$this->mEmailPlainHTML="text/html";	
				
		}else{
			
			$this->mEmailPlainHTML="text/plain";	
		
		
		}
    }
	
	/* get setting */
	function get_option($option) 
	{
		$settings = get_option('userultra_options');
		if (isset($settings[$option])) 
		{
			return $settings[$option];
			
		}else{
			
		    return '';
		}
		    
	}
	
	public function set_headers() {   			
		//Make Headers aminnistrators
		$header ="MIME-Version: 1.0\n"; 
		$header .= "Content-type: ".$this->mEmailPlainHTML."; charset=UTF-81\n"; 	
		$header .= "From: ".$this->mHeaderSentFromName." <".$this->mHeaderSentFromEmail.">\n";	
		$header .= "Organization: ".$this->mCompanyName." \n";
		$header .=" X-Mailer: PHP/". phpversion()."\n";		
		$this->mHeader = $header;		
    }
	
	
	public function  send ($to, $subject, $message)
	{
		global $xoouserultra , $phpmailer;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		
		
		//$message = nl2br($message);
		//check mailing method	
		$uultra_emailer = $xoouserultra->get_option('uultra_smtp_mailing_mailer');
		
		if($uultra_emailer=='mail' || $uultra_emailer=='' ) //use the defaul email function
		{
			wp_mail( $to , $subject, $message, $this->mHeader);
		
		}elseif($uultra_emailer=='mandrill'){ //send email via Mandrill
		
			$this->send_mandrill( $to , $recipient_name, $subject, $message);
			
		}elseif($uultra_emailer=='smtp'){ //send email via SMTP
		
			// Make sure the PHPMailer class has been instantiated 
			// (copied verbatim from wp-includes/pluggable.php)
			// (Re)create it, if it's gone missing
			if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$phpmailer = new PHPMailer( true );
			}
			
			
			// Empty out the values that may be set
			$phpmailer->ClearAddresses();
			$phpmailer->ClearAllRecipients();
			$phpmailer->ClearAttachments();
			$phpmailer->ClearBCCs();			
			
			// Set the mailer type as per config above, this overrides the already called isMail method
			$phpmailer->Mailer = $uultra_emailer;
						
			$phpmailer->From     = $xoouserultra->get_option('messaging_send_from_email');
			$phpmailer->FromName =  $xoouserultra->get_option('messaging_send_from_name');
			
			//Set the subject line
			$phpmailer->Subject = $subject;			
			$phpmailer->CharSet     = 'UTF-8';
			
			//Set who the message is to be sent from
			//$phpmailer->SetFrom($phpmailer->FromName, $phpmailer->From);
			
			//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
			
			
			// Set the Sender (return-path) if required
			if ($xoouserultra->get_option('uultra_smtp_mailing_return_path')=='1')
				$phpmailer->Sender = $phpmailer->From; 
			
			// Set the SMTPSecure value, if set to none, leave this blank
			$uultra_encryption = $xoouserultra->get_option('uultra_smtp_mailing_encrytion');
			$phpmailer->SMTPSecure = $uultra_encryption == 'none' ? '' : $uultra_encryption;
			
			// If we're sending via SMTP, set the host
			if ($uultra_emailer == "smtp")
			{				
				// Set the SMTPSecure value, if set to none, leave this blank
				$phpmailer->SMTPSecure = $uultra_encryption == 'none' ? '' : $uultra_encryption;
				
				// Set the other options
				$phpmailer->Host = $xoouserultra->get_option('uultra_smtp_mailing_host');
				$phpmailer->Port = $xoouserultra->get_option('uultra_smtp_mailing_port');
				
				// If we're using smtp auth, set the username & password
				if ($xoouserultra->get_option('uultra_smtp_mailing_authentication') == "true") 
				{
					$phpmailer->SMTPAuth = TRUE;
					$phpmailer->Username = $xoouserultra->get_option('uultra_smtp_mailing_username');
					$phpmailer->Password = $xoouserultra->get_option('uultra_smtp_mailing_password');
				}
				
			}
			
			//html plain text			
			if( $xoouserultra->get_option('uultra_smtp_mailing_html_txt')=='html')
			{
				
				$phpmailer->IsHTML(true);	
				$phpmailer->MsgHTML($message);
			
			}else{
				
				$phpmailer->IsHTML(false);
				$phpmailer->Body =$message;
			
			}
				
			
			//Set who the message is to be sent to
			$phpmailer->AddAddress($to);
			
			//Send the message, check for errors
			if(!$phpmailer->Send()) {
			  echo "Mailer Error: " . $phpmailer->ErrorInfo;
			} else {
			  //echo "Message sent!";
			}

		
		}
		
		
		
	}
	
	public function  send_mandrill ($to, $recipient_name, $subject, $message_html)
	{
		global $xoouserultra , $phpmailer;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(xoousers_path."libs/mandrill/Mandrill.php");
		
		$from_email     = $xoouserultra->get_option('messaging_send_from_email');
		$from_name =  $xoouserultra->get_option('messaging_send_from_name');
		$api_key =  $xoouserultra->get_option('uultra_mandrill_api_key');
		
		//html plain text			
		if( $xoouserultra->get_option('uultra_smtp_mailing_html_txt')=='html')
		{
			
			$text_html =  $message_html;
			$text_txt =  "";
		
		}else{
			
			$text_html = "" ;
			$text_txt =  $message_html;
		
		}
			
		
		try {
				$mandrill = new Mandrill($api_key);
				$message = array(
					'html' => $text_html,
					'text' => $text_txt,
					'subject' => $subject,
					'from_email' => $from_email,
					'from_name' => $from_name,
					'to' => array(
						array(
							'email' => $to,
							'name' => $recipient_name,
							'type' => 'to'
						)
					),
					'headers' => array('Reply-To' => $from_email, 'Content-type' => $this->mEmailPlainHTML),
					'important' => false,
					'track_opens' => null,
					'track_clicks' => null,
					'auto_text' => null,
					'auto_html' => null,
					'inline_css' => null,
					'url_strip_qs' => null,
					'preserve_recipients' => null,
					'view_content_link' => null,
					/*'bcc_address' => 'message.bcc_address@example.com',*/
					'tracking_domain' => null,
					'signing_domain' => null,
					'return_path_domain' => null
					/*'merge' => true,
					'global_merge_vars' => array(
						array(
							'name' => 'merge1',
							'content' => 'merge1 content'
						)
					),
					
					
					/*'google_analytics_domains' => array('example.com'),
					'google_analytics_campaign' => 'message.from_email@example.com',
					'metadata' => array('website' => 'www.example.com'),*/
					
				);
				$async = false;
				$ip_pool = 'Main Pool';
				$send_at = date("Y-m-d H:i:s");
				//$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
				$result = $mandrill->messages->send($message, $async);
				//print_r($result);
				
			} catch(Mandrill_Error $e) {
				// Mandrill errors are thrown as exceptions
				echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
				throw $e;
			}
	}
	
	//--- Automatic Activation	
	public function  welcome_email($u_email, $user_login, $user_pass)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		//get welcome email
		$template_client =stripslashes($this->get_option('messaging_welcome_email_client'));
		$template_admim = stripslashes($this->get_option('messaging_welcome_email_client_admin'));
		
		$login_url =site_url("/");
		
		$subject = __('Registration','xoousers');
		$subject_admin = __('New Registration','xoousers');
		
		$template_client = str_replace("{{userl_ultra_login_url}}", $login_url,  $template_client);				
		$template_client = str_replace("{{userultra_user_email}}", $u_email,  $template_client);
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);
		$template_client = str_replace("{{userultra_pass}}", $user_pass,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);
		
		//admin
		$template_admim = str_replace("{{userultra_user_email}}", $u_email,  $template_admim);
		$template_admim = str_replace("{{userultra_user_name}}", $user_login,  $template_admim);
		$template_admim = str_replace("{{userultra_admin_email}}", $admin_email,  $template_admim);		
		
		//send to client
		$this->send($u_email, $subject, $template_client);
		
		//send to admin		
		$this->send($admin_email, $subject_admin, $template_admim);
		
					
		
	}
	
	//--- Link Activation Resend	
	public function  re_send_activation_link($u_email, $user_login, $activation_link)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		$subject = __('Verify Your Account','xoousers');
		
		//get welcome email
		$template_client =stripslashes($this->get_option('messaging_re_send_activation_link'));
		
		$login_url =site_url("/");
		
		$template_client = str_replace("{{user_ultra_activation_url}}", $activation_link,  $template_client);
		$template_client = str_replace("{{userultra_user_email}}", $u_email,  $template_client);
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);
		
		
		$this->send($u_email, $subject, $template_client);
		
	}
	
	//--- Admin Activation	
	public function  welcome_email_with_admin_activation($u_email, $user_login, $user_pass,  $activation_link)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		$subject = __('Account Verification','xoousers');
		$subject_admin = __('New Account To Approve','xoousers');
		
		//get welcome email
		$template_client =stripslashes($this->get_option('messaging_admin_moderation_user'));
		$template_admim = stripslashes($this->get_option('messaging_admin_moderation_admin'));
		
		$login_url =site_url("/");
		
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);
		$template_client = str_replace("{{userultra_pass}}", $user_pass,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);
		
		//admin
		$template_admim = str_replace("{{userultra_user_email}}", $u_email,  $template_admim);
		$template_admim = str_replace("{{userultra_user_name}}", $user_login,  $template_admim);
		$template_admim = str_replace("{{userultra_admin_email}}", $admin_email,  $template_admim);	
				
		
		//send user
		$this->send($u_email, $subject, $template_client);
		
		//send to admin		
		$this->send($admin_email, $subject_admin, $template_admim);
		
	}
	
	//--- Link Activation	
	public function  welcome_email_with_activation($u_email, $user_login, $user_pass,  $activation_link)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		$subject = __('Verify Your Account','xoousers');
		$subject_admin = __('New Account To Verify','xoousers');
		
		//get welcome email
		$template_client =stripslashes($this->get_option('messaging_welcome_email_with_activation_client'));
		$template_admim = stripslashes($this->get_option('messaging_welcome_email_with_activation_admin'));
		
		$login_url =site_url("/");
		
		$template_client = str_replace("{{user_ultra_activation_url}}", $activation_link,  $template_client);
		$template_client = str_replace("{{userultra_user_email}}", $u_email,  $template_client);
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);
		$template_client = str_replace("{{userultra_pass}}", $user_pass,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);
		
		//admin
		$template_admim = str_replace("{{userultra_user_email}}", $u_email,  $template_admim);
		$template_admim = str_replace("{{userultra_user_name}}", $user_login,  $template_admim);
		$template_admim = str_replace("{{userultra_admin_email}}", $admin_email,  $template_admim);	
				
		
		//send user
		$this->send($u_email, $subject, $template_client);
		
		//send to admin		
		$this->send($admin_email, $subject_admin, $template_admim);
		
	}
	
	//---  Activation	
	public function  confirm_activation($u_email, $user_login)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		
		$admin_email =get_option('admin_email'); 
		
		//get welcome email
		$template_client =stripslashes($this->get_option('admin_account_active_message_body'));
		
		$login_url =site_url("/");
		
		$subject = __('Account Activation','xoousers');
		
		
		$template_client = str_replace("{{userl_ultra_login_url}}", $login_url,  $template_client);				
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
		
					
		
	}
	
	//---  Verification Success	
	public function  confirm_verification_sucess($u_email)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		
		$admin_email =get_option('admin_email'); 
		
		//get welcome email
		$template_client =stripslashes($this->get_option('account_verified_sucess_message_body'));
		
		$login_url =site_url("/");
		
		$subject = __('Account Verified','xoousers');
		
		
		$template_client = str_replace("{{userl_ultra_login_url}}", $login_url,  $template_client);				
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
		
					
		
	}
	
	//---  Deny	
	public function  deny_activation($u_email, $user_login)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		
		$admin_email =get_option('admin_email'); 
		
		//get welcome email
		$template_client =stripslashes($this->get_option('admin_account_deny_message_body'));
		
		$login_url =site_url("/");
		
		$subject = __('Account Activation Deny','xoousers');		
					
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);			
		$this->send($u_email, $subject, $template_client);
		
					
		
	}
	
	//--- Paid Upgrade	
	public function  welcome_email_paid_upgrade($u_email, $user_login, $user_pass)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		//get welcome email
		$template_client =stripslashes($this->get_option('account_upgrade_email_client'));
		$template_admim = stripslashes($this->get_option('account_upgrade_email_admin'));
		
		$login_url =site_url("/");
		
		$subject = __('Upgrade Information','xoousers');
		$subject_admin = __('User Account Upgrade','xoousers');
		
					
		$template_admin = str_replace("{{userultra_user_email}}", $u_email,  $template_admim);
		$template_admim = str_replace("{{userultra_user_name}}", $user_login,  $template_admim);		
		
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
		
		//send admin email		
		$this->send($u_email, $subject_admin, $template_admim);
		
					
		
	}
	
	//--- Paid Activation	
	public function  welcome_email_paid($u_email, $user_login, $user_pass)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		//get welcome email
		$template_client =stripslashes($this->get_option('messaging_welcome_email_client'));
		$template_admim = stripslashes($this->get_option('messaging_welcome_email_client_admin'));
		
		$login_url =site_url("/");
		
		$subject = __('Registration Information','xoousers');
		$subject_admin = __('New Paid Subscription','xoousers');
		
		$template_client = str_replace("{{userl_ultra_login_url}}", $login_url,  $template_client);				
		$template_client = str_replace("{{userultra_user_email}}", $u_email,  $template_client);
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);
		$template_client = str_replace("{{userultra_pass}}", $user_pass,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
		
		//send admin email		
		$this->send($u_email, $subject_admin, $template_admim);
		
					
		
	}
	
	//--- Email Activation	
	public function  welcome_email_link_activation($u_email, $user_login, $user_pass)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		//get welcome email
		$template_client =stripslashes($this->get_option('messaging_welcome_email_client'));
		$template_admim = stripslashes($this->get_option('messaging_welcome_email_client_admin'));
		
		$login_url =site_url("/");
		
		$subject = __('Registration ','xoousers');
		
		$template_client = str_replace("{{userl_ultra_login_url}}", $login_url,  $template_client);				
		$template_client = str_replace("{{userultra_user_email}}", $u_email,  $template_client);
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);
		$template_client = str_replace("{{userultra_pass}}", $user_pass,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
					
		
	}
	
	//--- Private Message to User	
	public function  send_private_message_user($receiver, $sender_nick, $uu_subject, $uu_message)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		$u_email = $receiver->user_email;
		
		$template_client =stripslashes($this->get_option('messaging_user_pm'));
		
		$blogname  = $this->get_option('messaging_send_from_name');
		
		$login_url =site_url("/");		
		
		
		$subject = __('New Private Message','xoousers');
		
		$template_client = str_replace("{{userultra_user_name}}", $sender_nick,  $template_client);
		$template_client = str_replace("{{user_ultra_blog_name}}", $blogname,  $template_client);
		$template_client = str_replace("{{userultra_pm_subject}}", $uu_subject,  $template_client);
		$template_client = str_replace("{{userultra_pm_message}}", stripslashes($uu_message),  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
					
		
	}
	
	//--- Private Message to Uses from Admin
	public function  send_private_message_user_from_admin($receiver, $sender_nick, $uu_subject, $uu_message)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		$u_email = $receiver->user_email;
		
		$template_client =stripslashes($this->get_option('messaging_user_pm_from_admin'));
		
		$blogname  = $this->get_option('messaging_send_from_name');
		
		$login_url =site_url("/");		
		
		
		$subject = __('New Private Message','xoousers');
		
		$template_client = str_replace("{{userultra_user_name}}", $sender_nick,  $template_client);
		$template_client = str_replace("{{user_ultra_blog_name}}", $blogname,  $template_client);
		$template_client = str_replace("{{userultra_pm_subject}}", $uu_subject,  $template_client);
		$template_client = str_replace("{{userultra_pm_message}}", stripslashes($uu_message),  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);
					
		
	}
	
	
		//--- Send Friend Request	
	public function  send_friend_request($receiver, $sender)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		$u_email = $receiver->user_email;
		
		$template_client =stripslashes($this->get_option('message_friend_request'));		
		$blogname  = $this->get_option('messaging_send_from_name');
		
		$login_url =site_url("/");				
		
		$subject = __('New Friend Request','xoousers');
		
		$template_client = str_replace("{{user_ultra_blog_name}}", $blogname,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);			
		
		$this->send($u_email, $subject, $template_client);
					
		
	}
		//--- Reset Link	
	public function  send_reset_link($receiver, $link)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		$u_email = $receiver->user_email;
		
		$template_client =stripslashes($this->get_option('reset_lik_message_body'));
		
		$blogname  = $this->get_option('messaging_send_from_name');
		
		
		$subject = __('Reset Your Password','xoousers');
		
		$template_client = str_replace("{{userultra_reset_link}}", $link,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);		
		
		$this->send($u_email, $subject, $template_client);	
				
		
	}
	
	//--- confirm password reset	
	public function  send_new_password_to_user($u_email, $user_login, $user_pass)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		$subject = __('Password Reset Confirmation','xoousers');
		
		//get welcome email
		$template_client =stripslashes($this->get_option('password_reset_confirmation'));
		
		$login_url =site_url("/");
		
		$template_client = str_replace("{{userl_ultra_login_url}}", $login_url,  $template_client);		
		$template_client = str_replace("{{userultra_user_name}}", $user_login,  $template_client);		
		$template_client = str_replace("{{userultra_user_email}}", $u_email,  $template_client);
		$template_client = str_replace("{{userultra_pass}}", $user_pass,  $template_client);
		$template_client = str_replace("{{userultra_admin_email}}", $admin_email,  $template_client);
				
		
		$this->send($u_email, $subject, $template_client);
		
		
	}
	
	
	public function  paypal_ipn_debug( $message)
	{
		global $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$admin_email =get_option('admin_email'); 
		
		
		
		$this->send($admin_email, "IPN notification", $message);
					
		
	}
	
	
	

}
$key = "messaging";
$this->{$key} = new XooMessaging();
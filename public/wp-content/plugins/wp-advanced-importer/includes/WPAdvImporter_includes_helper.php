<?php
/******************************
 * Filename	: includes/WPAdvImporter_includes_helper.php
 * Description	: Helper class for WP Advanced Importer
 * Author 	: Fredrick
 * Owner  	: smackcoders.com
 * Date   	: Mar24,2014
 */
require_once("WXR_importer.php");
class WPAdvImporter_includes_helper {

	public function __construct()
	{
		$this->getKeyVals();
	}

	// @var string CSV upload directory name
	public $uploadDir = 'advanced_importer';

	// @var boolean post title check
	public $titleDupCheck = false;

	// @var boolean content title check
	public $conDupCheck = false;

	// @var boolean for post flag
	public $postFlag = true;

	// @var int duplicate post count
	public $dupPostCount = 0;

	public $dupPageCount = 0;

	public $get_user_id  = 0;

	public $dupCPTCount = 0;

	// @var int inserted post count
	public $insPostCount = 0;

	// @var int inserted post count
	public $insPageCount = 0;

	// @var int inserted post count
	public $insCPTCount = 0;

	// @var int no post author count
	public $noPostAuthCount = 0;

	// @var int updated post count
	public $updatedPostCount=0;

	// @var string delimiter
	public $delim = ",";

	// @var array delilimters supported by CSV importer
	public $delim_avail = array(
			',',
			';'
			);

	// @var array wp field keys
	public $keys = array();

	// @var array for default columns
	// @var array CSV headers
	public $headers = array();

	public $capturedId=0;

	public $defCols = array(
			'post_title' => null,
			'post_content' => null,
			'post_excerpt' => null,
			'post_date' => null,
			'post_name' => null,
			'post_tag' => null,
			'post_id'  => null,
			'post_category' => null,
			'post_author' => null,
			'post_parent' => null,
			'comment_status' => null,
			'status' => null,
			'menu_order' => null,
                        'terms' => null,
                        'post_format' => null,
                        'post_password' => null,
			);

	public $detailedLog = array();

	/* getImportDataConfiguration */
	/**
	 * Get upload directory
	 */
	public function getUploadDirectory($check = 'plugin_uploads')
	{
		$upload_dir = wp_upload_dir();
		if($check == 'plugin_uploads'){
			return $upload_dir ['basedir'] . "/" . $this->uploadDir;
		}else{
			return $upload_dir ['basedir'];
		}
	}

	/**
	 *	generate help tooltip
	 *	@param string $content ** content to show on tooltip **
	 *	@return string $html ** generated HTML **
	 **/
	public function generatehelp($content, $mapping_style = NULL)
	{
		$html = '<div style = "'.$mapping_style.'"> <a href="#" class="tooltip">
			<img src="'.WP_CONST_ADVANCED_XML_IMP_DIR.'images/help.png" />
			<span class="tooltipPostStatus">
			<img class="callout" src="'.WP_CONST_ADVANCED_XML_IMP_DIR.'images/callout.gif" />
			'.$content.'
			<img src="'.WP_CONST_ADVANCED_XML_IMP_DIR.'images/help.png" style="margin-top: 6px;float:right;" />
			</span> </a> </div>';
		return $html;
	}


	public static function output_front_xml_page()
	{
		if(!isset($_REQUEST['__module']))
		{
			wp_redirect( get_admin_url() . 'admin.php?page='.WP_CONST_ADVANCED_XML_IMP_SLUG.'/index.php&__module=importtype&step=uploadfile');
		}
		require_once(WP_CONST_ADVANCED_XML_IMP_DIRECTORY.'config/settings.php');
		require_once(WP_CONST_ADVANCED_XML_IMP_DIRECTORY.'lib/skinnymvc/controller/SkinnyController.php');

		$c = new SkinnyControllerWPAdvXMLFree;
		$c->main();
	}

	public function getSettings(){
		return get_option('wpadvxmlimpfreesettings');
	}

	public function renderMenu()
	{
		include(plugin_dir_path(__FILE__) . '../templates/menu.php');
	}
	/**
	 *To save the user mapping values
	 *@param $_POST values  
	 *@param return user
	 **/
	public function save_user($sel_user) {
		$user_imp_type = '';
		$user_imp_type = $sel_user['user_imp'];
		if($user_imp_type == 'simple') {
			$_SESSION['user_imp_type'] = 'simple';
			$_SESSION['user'] = '';    
			return $_SESSION['user_imp_type'];                
		}
		else if($user_imp_type == 'adv') {
			if($sel_user['user'] == 'xmluser') {
				$_SESSION['user_imp_type'] = 'xmluser';           
				$_SESSION['user'] = $sel_user['xml_author']; 
				return $_SESSION['user']; 
			}
			else if($sel_user['user'] == 'siteuser') {
				$_SESSION['user_imp_type'] = 'siteuser';           
				$_SESSION['user'] = $sel_user['ex_user'];  
				return $_SESSION['user'];   
			}
			else if($sel_user['user'] == 'emailuser') {
				$new_user_name = $sel_user['new_user_name'];
				$email         = $sel_user['new_user'];
				$new_user = $new_user_name.'|'.$email;
				$_SESSION['user_imp_type'] = 'newuser';           
				$_SESSION['user'] = $new_user; 
				return $_SESSION['user']; 
			}
		}
	}
	public function requestedAction($action,$step){
		$actions = array('dashboard','settings','help','users','comments','eshop','wpcommerce','woocommerce','categories','customtaxonomy','export', 'mappingtemplate');
		if(!in_array($action,$actions)){
			include(plugin_dir_path(__FILE__) . '../templates/view.php');
		}else{
			include(plugin_dir_path(__FILE__) . '../modules/'.$action.'/actions/actions.php');
			include(plugin_dir_path(__FILE__) . '../modules/'.$action.'/templates/view.php');
		}
	}

	/**
	 * Move CSV to the upload directory
	 */
	public function move_file()
	{
		if ($_FILES ["csv_import"] ["error"] == 0) {
			$tmp_name = $_FILES ["csv_import"] ["tmp_name"];
			$this->csvFileName = $_FILES ["csv_import"] ["name"];
			move_uploaded_file($tmp_name, $this->getUploadDirectory() . "/$this->csvFileName");
		}
	}

	/**
	 * Check upload dirctory permission
	 */
	function checkUploadDirPermission()
	{
		$this->getUploadDirectory();
		$upload_dir = wp_upload_dir();
		if (!is_dir($upload_dir ['basedir'])) {
			print " <div style='font-size:16px;margin-left:20px;margin-top:25px;'>" . $this->t("UPLOAD_PERMISSION_ERROR") . "
				</div><br/>
				<div style='margin-left:20px;'>
				<form class='add:the-list: validate' method='post' action=''>
				<input type='submit' class='button-primary' name='Import Again' value='" . $this->t("IMPORT_AGAIN") . "'/>
				</form>
				</div>";
			$this->freeze();
		} else {
			if (!is_dir($this->getUploadDirectory())) {
				wp_mkdir_p($this->getUploadDirectory());
			}
		}
	}


	/**
	 * Get field colum keys
	 */
	function getKeyVals()
	{
		/*	$cust_fields='';
			$acf_field=array();
			global $wpdb;
			$active_plugins = get_option('active_plugins');
			$limit = ( int )apply_filters('postmeta_form_limit', 150);
			$this->keys = $wpdb->get_col("SELECT meta_key FROM $wpdb->postmeta
			GROUP BY meta_key
			HAVING meta_key NOT LIKE '\_%' and meta_key NOT LIKE 'field_%'
			ORDER BY meta_key
			LIMIT $limit");

			foreach ($this->keys as $val) {
			$this->defCols ["CF: " . $val] = $val;
			} */



	}

	/**
	 * Function converts XML data to formatted array.
	 *
	 * @param $file XML
	 *            input filename
	 * @param $delim delimiter
	 *            for the XML
	 * @return array formatted XML output as array
	 */
	public function xml_file_data($file,$fileExtension, $module='post') {
		$file = $this->getUploadDirectory() .'/'. $file;
		ini_set("auto_detect_line_endings", true);

		$data_rows = array();
		$commentsArr = array();
		$postmetaArr= array();
		$my_titles = array();
		$my_content = array();
		$terms_val = array();
		$domain_name = array();
		$all_domain=array();
		$my_title=array();
		$l = '';
		$fileexists = file_exists($file);
		if ($fileexists) {
			if($fileExtension=='xml')
			{
				$mycls = new Knol_WXR_Parser();
				$all_arr=$mycls->parse($file); 

				if(is_array($all_arr))
				{
					foreach($all_arr as $key => $value)
					{
						//Under the posts key all Posts are available in all WXR file. 
						if($key=='posts')
						{
							$i=0;
							foreach($value as  $post_value)
							{
								if(array_key_exists('post_type',$post_value) && $post_value['post_type'] == $module){
									$my_post[$i]=$post_value;
									$i++;
								}
							}
						}
					}

					if( (isset($module)) && ( $module != 'post') && ($module != 'page') )  // custompost start here
					{
						foreach($all_arr as $key => $value)
						{
							//Under the posts key all Posts are available in all WXR file. 
							if($key=='posts')
							{
								$i=0;
								foreach($value as  $post_value)
								{
									$eliminate = array('post','page','attachment','revision','nav_menu_item','comments');

									if(array_key_exists('post_type',$post_value) )
									{
										if(isset($post_value['post_type'] ) && $post_value['post_type'] != 'post' && $post_value['post_type'] != 'page' && $post_value['post_type'] != 'attachment'  && $post_value['post_type'] != 'revision'  && $post_value['post_type'] != 'comments'  && $post_value['post_type'] != 'nav_menu_item')
										{ 
											$custom_array= $post_value;

											$my_post[$i]=$custom_array;
											$i++;

										}
									}

								}
							}
						}
					}//custom post end here

					for($j=0;$j<$i;$j++)
					{       
						$n=0;
						// Check here the post_type
						$post_types=get_post_types();

						foreach($my_post[$j] as $my_key => $my_val)
						{

							//Key of my_posts are post headers
							array_push($my_title,$my_key);
							//Value of my_posts are post contents
							if(is_array($my_val)&& $my_key!='postmeta' && $my_key!='comments' && $my_key!= 'category' && $my_key!= 'post_tag' && $my_key!= 'post_category' && $my_key!='terms')
								$my_content[$j][$n]="";
							elseif($my_key!='postmeta' && $my_key!='comments' && $my_key!= 'category' && $my_key!= 'post_tag' && $my_key!= 'post_category' && $my_key!='terms')
								$my_content[$j][$n]=$my_val;
							//In WXR file, post_categories and post_tags will be in terms key
							if($my_key=='terms')
							{ 
								$x=0;$z=0;
								foreach($my_val as $my_category)
								{	$y=0;
									foreach($my_category as $categ_key => $categ_val)
									{      
										//domain refered as post_categories or post_tags
										if($categ_key=='domain')
										{	
											//Some headers are in domain
											if($categ_val){
												if(!in_array($categ_val, $all_domain))
													$all_domain[$z]=$categ_val;
											} else {
											}
											$z++;
										}
										if(isset($categ_key))
											$terms_key[$j][$x][$y]=$categ_key;
										else
											$terms_key[$j][$x][$y]=null;
										if(isset($categ_val))
											$terms_val[$j][$x][$y]=$categ_val;
										else
											$terms_val[$j][$x][$y]=null;

										$y++;
									}
									$x++;
								}

							} 
							if($my_key=='postmeta')
							{
								foreach($my_val as $pmArr){
									$postmetaArr[$j]['postmeta'][$pmArr['key']] = $pmArr['value'];
								} 
							} 
							if($my_key == 'comments')
							{
								$commentsArr[$j]['comments']= $my_val;
							} 
							if($my_key!='postmeta' && $my_key!='comments' && $my_key!= 'category' && $my_key!= 'post_tag' && $my_key!= 'post_category')
								$n++;
						}

						$my_content[$j][$n]="";
					} 
					//Remove duplicates from my_title
					$my_title=array_unique($my_title); 
					//Remove duplicates from all_domain
					$all_domain=array_unique($all_domain);
					//   echo '<pre>'; print_r($all_domain);die('it exist');
					foreach($all_domain as $key => $value)
					{
						array_push($my_title,$value);
					}
					$l=0;
					foreach($my_title as $value)
					{	
						//Make a serial index	
						$my_titles[$l]=$value;$l++;
					}	
					// collect post_categories and post_tags values
					//	$data_rows=$my_content;
					$this->headers=$my_titles;
				}
			}

		} else {


		}		return $my_titles;


	}


	/**
	 * Manage duplicates
	 *
	 * @param string type = (title|content), string content
	 * @return boolean
	 */
	function duplicateChecks($type = 'title', $text, $gettype) {
		global $wpdb;
		//$this->dupPostCount = 0;
		if ($type == 'content') {
			$htmlDecode = html_entity_decode($text);
			$strippedText = strip_tags($htmlDecode);
			$contentLength = strlen($strippedText);
			$allPosts_count = $wpdb->get_results("SELECT COUNT(ID) as count FROM $wpdb->posts WHERE post_type = \"{$gettype}\" and post_status IN('publish','future','draft','pending','private')");
			$allPosts_count = $allPosts_count[0]->count;
			$allPosts = $wpdb->get_results("SELECT ID,post_title,post_date,post_content FROM $wpdb->posts WHERE post_type = \"{$gettype}\" and post_status IN('publish','future','draft','pending','private')");
			foreach ($allPosts as $allPost) {
				$htmlDecodePCont = html_entity_decode($allPost->post_content);
				$strippedTextPCont = strip_tags($htmlDecodePCont);
				similar_text($strippedTextPCont, $strippedText, $p);
				if ($p == 100) {
					if($gettype == 'post'){
						$this->dupPostCount++;
					}
					if($gettype == 'page'){
						$this->dupPageCount++;
					}
					if($gettype != 'post' && $gettype != 'page'){
						$this->dupCPTCount++;
					}
#$this->dupPostCount++;
					return false;
				}
			}
			return true;
		} else if ($type == 'title') {
			$post_exist = $wpdb->get_results("select ID from " . $wpdb->posts . " where post_title = \"{$text}\" and post_type = \"{$gettype}\" and post_status in('publish','future','draft','pending','private')");
			if (count($post_exist) == 0 && ($text != null || $text != ''))
				return true;
		}
		if($gettype == 'post'){
			$this->dupPostCount++;
		}
		if($gettype == 'page'){
			$this->dupPageCount++;
		}
		if($gettype != 'post' && $gettype != 'page'){
			$this->dupCPTCount++;
		}
#$this->dupPostCount++;
		return $post_exist;
	}
	/**
	 *Function to get the selected post type  
	 *@param return post type as array
	 **/
	public function to_import() {
		global $wpdb;
		$ex_arr = array();
		$ex_arr  =  get_option('to_import');        
		return $ex_arr;
	}
	/**
	 *Function for exclude the un choosen post types 
	 *@param post_type , post_array
	 *@param return excluded post_array
	 **/
	public function exclude_list($post_type,$post_arr) {
		global $wpdb;
		$exclusion_list = array();
		$exclusion_list = get_option('exclude_keys');
		if(!empty($exclusion_list)) {
			if(array_key_exists($post_type,$exclusion_list)) {
				foreach($exclusion_list as $post_key) {
					foreach($post_key as $pkey => $pval) {
						unset($post_arr[$pval]);
					}
				}
			}
		}
		return $post_arr;
	}
	/**
	 *Function for upoladed zip media handling 
	 *@param attachment path as url
	 *@param image url
	 **/
	public function get_img_from_zip($attach_url) {
		$zip_location = $file_url = '';
		$get_base_name= @basename($attach_url); 
		$zip_location = $_SESSION['img_path'];
		$url_location = $_SESSION['img_path_url'];
		$files_array  = $this->wp_adv_importer_fetch_all_files($zip_location);              
		foreach($files_array as $singleFile)      {
			$get_file_name = explode('/',$singleFile);
			$c = count($get_file_name);
			$temp_file_name = $get_file_name[$c - 1];
			$temp_file_month = $get_file_name[$c - 2];
			$temp_file_year  = $get_file_name[$c - 3];
			//   $temp_file_uploads = $get_file_name[$c - 4];
			if($temp_file_name == $get_base_name) {
				if( (is_numeric($temp_file_year)) && (is_numeric($temp_file_month)) ) {
					$file_url = $url_location .'/'.$temp_file_year .'/'.$temp_file_month.'/'.$temp_file_name;
					return $file_url;
				}
				else {
					$file_url = $url_location.'/'.$temp_file_name;
					return $file_url;
				}  
			}
		}   

	} 

	/**
	 *Function for recursive scaning the directory
	 *@param upload dir path
	 *@param available file with path
	 **/
	public function wp_adv_importer_fetch_all_files($dir)  { 
		$root = scandir($dir); 
		foreach($root as $value) 
		{ 
			if($value === '.' || $value === '..') 
				continue;

			if(is_file("$dir/$value"))	{
				$files[] = "$dir/$value";continue;
			}
			foreach($this->wp_adv_importer_fetch_all_files("$dir/$value") as $value) 
			{ 
				$files[] = $value; 
			} 
		} 
		return $files; 
	}
	/**
	 *Function for process author 
	 *@param post_array,current limit,auth_type,selected user from user mapping
	 *@param return user_id
	 **/
	public function processAuthor($get_details , $currentLimit ,$type ,$ex_user) {
		$new_user = '';
		if($type == 'siteuser') {
			return $ex_user;
		}
		if($type ==  'newuser') {
			$new_user = explode('|',$ex_user);
			$user_array['user_login'] = $new_user[0];
			$user_array['user_email'] = $new_user[1];
			$user_array['role'] = 'subscriber';
			$user_array['first_name'] = $new_user[0];
			$user_array['user_pass']  = wp_generate_password(12,false);
			$user_exist = $this->user_check($user_array);
			if(!empty($user_exist)) {
				$user_id = $user_exist;
				$this->get_user_id = $user_id;
				$this->detailedLog[$user_id]['verify_here'] = " The user - </b> ". $user_id . " -  </b> is already exists";
				return false;
			}
			else {
				$user_id = wp_insert_user($user_array);
				$this->get_user_id = $user_id;
				$this->detailedLog[$user_id]['verify_here'] = " The user - </b> ". $user_array['user_login'] . " -  </b> has been created";
				return false;  
			}
		}
		foreach($get_details['authors'] as $auth_key => $auth_val) {
			foreach($auth_val as $param_key => $param_val)   {
				if(isset($param_key)) {
					if($param_key == 'author_login'){
						$role = (array) $param_val;   
						foreach($role as $role_val) {   
							$user_array['user_login'] = $role_val;
							$user_array['role'] = $role_val;       
						}
					}
					else if($param_key == 'author_email') {
						$user_array['user_email'] = $param_val;
						$user_array['user_pass'] = wp_generate_password( 12, false );
					}
					else if($param_key == 'author_first_name') {
						$user_array['first_name'] = $param_val;
					} 
					else if($param_key == 'author_last_name')  {
						$user_array['last_name']  = $param_val;
					}
					else if($param_key == 'author_display_name') {
						$user_array['display_name'] = $param_val;

					}   

				}
			}
			$user_exist = $this->user_check($user_array);
			if(!empty($user_exist)) {
				$user_id = $user_exist;
				$this->detailedLog[$user_id]['verify_here'] = " The user - </b> ". $user_array['user_login'] . " -  </b> is already exists";
			}
			else {
				if($type == 'xmluser') { 
					if($role_val == $ex_user) {
						$user_id = wp_insert_user($user_array);
						$this->get_user_id = $user_id;
						$this->detailedLog[$user_id]['verify_here'] = " The user - </b> ". $user_array['user_login'] . " -  </b> is already exists";                           return false;
					}
				}
				else {
					$user_id = wp_insert_user($user_array);

				}
				$current_user = wp_get_current_user();
				$admin_email = $current_user->user_email;
				$headers = "From: Administrator <$admin_email>" . "\r\n";
				$message = "Hi,You've been invited with the role of ".$user_array['role'].". Here, your login details."."\n"."username: ".      $user_array['user_login']."\n"."userpass: ".$user_array['user_pass']."\n"."Please click here to login ".wp_login_url();
				$emailaddress = $user_array['user_email'];
				$subject = 'Login Details';
				if(isset($user_array)){
					$response = wp_mail($emailaddress, $subject, $message, $headers);
				}
				$this->detailedLog[$user_id]['verify_here'] = "<span style ='padding:5px;' > The user - <b> ". $user_array['user_login'] . " </b> - has been created </span>";

			}
			if($type == 'xmluser') {
				if($role_val == $ex_user) {
					return $user_id;
				}
			}

			unset($user_array);


		}


	}
	/**
	 * function to map the xml file and process it
	 *
	 * @return post_id
	 */
	function processDataInWP($get_details,$currentLimit,$user = null,$img,$duptitle,$dupcontent,$authtype) {
		require_once(ABSPATH . "wp-includes/pluggable.php");
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$postmeta_terms =  $to_post = $post_data = $post_arr = $ex_type =  array(); 
		$cur_prev_ids = $tag_name = $img_url =  $local_parent_id = $post_exist = $post_id = $type =  $img_url = $zip_img_url = $guid = '';
		$sample = array('is_normal_post','is_custom_post','is_sticky','terms','postmeta','comments','is_page','post_id','attachment_url','guid','post_parent');
		$un_comments = array('comment_user_id','comment_id','commentmeta');

		if(is_array($get_details['posts'])) { 
			$post_arr = $get_details['posts'];
			$post_data = $post_arr[$currentLimit];
			foreach($post_data as $post_key => $post_val ) {
                                    
                                
				if(isset($post_key) && ($post_key == 'post_author' )) {
					if($authtype != 'simple') {
						if(!empty($user) && (is_numeric($user))) {
							$post_data['post_author'] = $user;
						}
					} 
					else if($authtype == 'simple') {
						$post_data['post_author'] = $this->get_author_id($post_val);
					}
					$postmeta_terms = $post_data;
					$to_post        = $post_data;
				}
                                if(isset($post_key) && ($post_key == 'status')) {
                                             $to_post['post_status'] = $post_val; 
                                             if(empty($post_val) && ($post_val == 'null') && ($post_val == '') ) {
                                                  $to_post['post_status'] = 'publish';
                                                }
                                        }
				if( in_array($post_key,$sample,TRUE) ) {
					unset($to_post['is_normal_post']); unset($to_post['is_custom_post']); unset($to_post['is_sticky']);
					unset($to_post['terms']);unset($to_post['postmeta']); unset($to_post['comments']); unset($to_post['is_page']);
					unset($to_post['post_id']);unset($to_post['attachment_url']); unset($to_post['guid']); unset($to_post['post_parent']);   
				}

				if(isset($post_key) && ($post_key == 'post_type' )) {
					$ex_type =  $this->to_import(); 
					if(!in_array($post_val,$ex_type,TRUE)) {
						return false;
					}
					else {
						$to_post = $this->exclude_list($post_val, $to_post); 
					} 
					if(($duptitle == 'true') || ($dupcontent == 'true') ) {
						$type = 'title';
						$post_exist =  $this->duplicateChecks($type,$post_data['post_title'],$post_data['post_type']);
						if($post_exist != 0 ) {
							$this->detailedLog[$currentLimit]['verify_here'] = "<b>".$post_data['post_title']." </b> is already exist";           
							return false;
						}

					}

					if($post_val == 'attachment') {
						if($img == 'no') {
							$img_url    = $post_data['attachment_url'];
							$zip_img_url =  $this->get_img_from_zip($img_url); 
							$guid        = $this->get_attachment($zip_img_url , $currentLimit);
						} 
						else {
							$guid = $this->get_attachment($post_data['attachment_url'] , $currentLimit); 
						}
						$filename = $guid;
						$parent_post_id = $post_data['post_parent'];
						$filetype = wp_check_filetype( basename( $filename ), null );
						$wp_upload_dir = wp_upload_dir();
						$attachment = array(
								'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
								'post_mime_type' => $filetype['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
								'post_content'   => '',
								'post_status'    => 'inherit'
								);
						$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
						$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
						wp_update_attachment_metadata( $attach_id, $attach_data );
						set_post_thumbnail($parent_post_id, $attach_id);
						$attach_details = $attach_id.'|'.$parent_post_id;
						$_SESSION['attach_id'][$currentLimit] = $attach_details;
						if(!empty($filename)) {                
							$this->detailedLog[$currentLimit]['verify_here'] = " <span style = 'padding:5px;'><b> Image - </b>" . basename($filename)."</span>";
						} else {
							$this->detailedLog[$currentLimit]['verify_here'] = "<span style ='padding:5px;'><b> Image - </b>" . basename($filename)."</span>";
						}
						$to_post['guid'] = $guid;

						return false;
						//     $to_post['attachment_url'] = $guid;               
					}  // attachement end
					// for other post types
					else {

						$local_post_id = $post_data['post_id'];


					}

				} 
			}
			$post_id =  wp_insert_post($to_post);
			if( isset($to_post) && $to_post['post_type'] != 'attachement' ) {
				$cur_prev_ids = $post_id.'|'.$local_post_id;
				$_SESSION['post_id'][$currentLimit] = $cur_prev_ids;
			}  



			if(isset($postmeta_terms['terms'])) {   
				foreach($postmeta_terms['terms'] as $tag_key ) {   
					if(isset($tag_key['domain']) && ($tag_key['domain'] == 'post_tag')) {
						$tag_name = $tag_key['slug'];
						wp_set_post_tags($post_id, $tag_name); 

					}

					else if(isset($tag_key['domain']) && ($tag_key['domain'] == 'category')) {
						$cat[] = $tag_key['slug'];
						wp_set_object_terms($post_id,$cat,'category');

					}

					else if(isset($tag_key['domain']) && ($tag_key['domain'] == 'post_format')) {
						wp_set_object_terms($post_id, $tag_key['slug'], 'post_format'); 
					}


					foreach($tag_key as $term_key => $term_val ) {   } }  
			}
			if($post_val != 'attachment' ) {
				foreach($postmeta_terms['postmeta'] as $pkey => $pval) {  
					update_post_meta($post_id, $pval['key'], $pval['value']);         
				}
			}
			if(isset($postmeta_terms['comments'])) {
				foreach($postmeta_terms['comments'] as $ckey) {
					foreach($ckey as $unkey => $unval) {
						if(in_array($unkey,$un_comments,TRUE))
							unset($ckey[$unkey]); 
					}
					$ckey['comment_post_ID'] = $post_id;
					wp_insert_comment($ckey);
				}
			} 


		}
		$this->detailedLog[$currentLimit]['verify_here'] = "<span style = 'padding:5px;'> The  <b>  ". $to_post['post_title']. " </b> has been created Verify Here - <a href='" . get_permalink( $post_id ) . "' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $to_post['post_title'] ) ) . "' rel='permalink'>" . __( 'Web View' ) . "</a> | <a href='" . get_edit_post_link( $post_id, true ) . "' title='" . esc_attr( __( 'Edit this item' ) ) . "'>" . __( 'Admin View' ) . "</a> </span>"; 
//			$this->detailedLog[$currentLimit]['verify_here'] = $to_post;
		unset($to_post );         
		return $post_id;
	}

	/**
	 * Delete uploaded file after import process
	 */
	public function deletexmlprocesscomplete($uploadDir) {
		//array_map('unlink', glob("$uploadDir/*"));
		$files = array_diff(scandir($uploadDir), array('.','..')); 
		foreach ($files as $file) { 
			(is_dir("$uploadDir/$file")) ? rmdir("$uploadDir/$file") : unlink("$uploadDir/$file"); 
		} 
	}


	/**
	 *Function to get post parent
	 *@param return post_id
	 **/
	public function get_post_parent() {
		global $wpdb;
		//     $lastid = $wpdb->insert_id;
		$post_exist = $wpdb->get_results("select ID from " . $wpdb->posts . " ORDER BY ID DESC LIMIT 1");
		foreach ( $post_exist as $postid ) {
			$post_id = (int)$postid->ID;
		}

		return $post_id;
	}

	/**
	 *Function for covert the file name to hash key 
	 *@param uploaded xml file name
	 *@param return excluded post_array
	 **/
	public function convert_string2hash_key($value) {
		$file_name = hash_hmac('md5', "$value", 'secret');
		return $file_name;
	}
	/**
	 *Function to fetch all get_xml_details 
	 *@param xml file name
	 *@param return excluded post_array
	 **/
	public function get_xml_details($get) {
		$all_arr = array();
		if(isset($get) && $get != '') {
			$filename = $this->convert_string2hash_key($get);
		}
		$file =       $this->getUploadDirectory() .'/'. $filename;
		$fileexists = file_exists($file);
		if(isset($fileexists)){
			$mycls = new Knol_WXR_Parser();
			$all_arr = $mycls->parse($file);
		}
		return  $all_arr;
	}
	/**
	 *Function for fetch the posts 
	 *@param all_array
	 *@param return post_group
	 **/
	public function get_post_details($all_arr) {
		$normal_post_group = ''; 
		foreach($all_arr['posts'] as $key){
			if( isset($key['is_normal_post'])  && ($key['post_type'] == 'post') ){
				$is_noraml_post_title[]=$key['post_title'];
				$is_normal_post_id[]=$key['post_id'];
				$normal_post_group[]=$key;
			}
		}
		return $normal_post_group;
	}
	/**
	 *Function for fetch the pages 
	 *@param all_array
	 *@param return page_group
	 **/
	public function get_page_details($all_arr) {
		$normal_page_group = '';
		foreach($all_arr['posts'] as $key){
			if( isset($key['is_page']) && ($key['post_type'] == 'page' )){
				$is_page_title[]=$key['post_title'];
				$is_page_id[]=$key['post_id'];
				$normal_page_group[]=$key;
			}
		}
		return $normal_page_group;
	}
	/**
	 *Function for fetch the author details 
	 *@param all_array
	 *@param return author_group
	 **/
	public function get_author_details($all_arr) {
		$authors_name = '';
		foreach($all_arr['authors'] as $value){
			$authors_array[]=$value;
			$authors_name[]=$value['author_login'];
		}
		return $authors_name;
	}
	/**
	 *Function for fetch the customposts 
	 *@param all_array
	 *@param return custom_post group
	 **/
	public function get_custom_details($all_arr) {
		$custom_post = '';
		foreach($all_arr['posts'] as $key){
			if( isset($key['is_custom_post']) && ($key['post_type'] != 'post' ) && ($key['post_type'] != 'page')) {
				$custom_post_type[]=$key['post_type'];
				$custom_post[]=$key;
			}
		} 
		return $custom_post;
	}
	/**
	 *Function for get the user list from current site
	 *@param return user name
	 **/
	public function user_list() {
		global $wpdb;
		$user_table = $wpdb->users;
		$getUserName = $wpdb->get_results("select ID from $user_table ");
		return $getUserName;


	}
	/**
	 *Function for check the user existence
	 *@param user_array
	 *@param return user id
	 **/
	public function user_check($user_array) {
		global $wpdb;
		$user_table = $wpdb->users;
		$getUserId = $wpdb->get_results("select ID from $user_table where user_email = '".$user_array["user_email"]."'");
		$get_ID = $getUserId[0]->ID;
		return $get_ID;
	}
	/**
	 *Function for get the user id based on user login name
	 *@param admin name
	 *@param return user id
	 **/
	public function get_author_id($admin_name) {
		global $wpdb;
		$user_table = $wpdb->users;
		$getUserId = $wpdb->get_results("select ID from $user_table where user_login = '".$admin_name."'");
		$get_ID = $getUserId[0]->ID;
		return $get_ID;
	}
	/**
	 *Function to display the user map option
	 *@param return user
	 **/
	public function get_user_map_option() {
                   $user = '';
                   $user .= '<div style="height:auto;">';
                   global $wpdb; 
                   $filename = '';  $arr =  $xml_author = array();
		   $user .= '<label class="textalign" style="margin:10px 0px 10px 15px;">User Mapping :</label>';
                   $user .= '<div id="circlecheck"><input type = "radio" name = "user_imp" id = "simple" class="circlecheckbox" value = "simple"  onclick = "show_user(this.value);" /><label id="optiontext" class="circle-label" for="simple">Simple User Import</label></div>';
                   $user .= '<div id="circlecheck"><input type = "radio" name = "user_imp" id = "adv" class="circlecheckbox" value = "adv" onclick = "show_user(this.value);" ><label id="optiontext" class="circle-label" for="adv" style="margin-top:5px"> Advanced User Import</label></div>';
                   $user .= '<div id = "adv_user" style = "display:none;">';
                   
                   $user .= ' <input type="hidden" id="module" name="module" value="user_mapping" />';
                               if(isset($_SESSION['xml_values']['uploadfilename'])) {
                               $filename = $_SESSION['xml_values']['uploadfilename'];
                               }
                               $arr = $this->get_xml_details($filename);
                               $xml_author = $this->get_author_details($arr);
                               
                    $user .= '<div style = "margin-left:25px;"><span id="circlecheck"><input type = "radio" value = "xmluser" id = "xmluser" name = "user" class="circlecheckbox" onclick = "show_user_form(this.id);"  ><label for="xmluser" class="circle-label" id="optiontext"> Assign Author From Your Xml </label></span>
                      <select name = "xml_author" id ="xml_user"  style="margin-left:12px;margin-bottom:10px;">
                           <option value = "select"> -- select -- </option> ';
                                   if(is_array($xml_author)) {
                                        foreach($xml_author as $auth_key => $auth_val) { 
                                      $user .=  '<option value = "'.$auth_val.'" >'. $auth_val.'  </option>';
                                     } } 
                     $user .= '</select><br/>
                                 <span id="circlecheck"><input type = "radio" value = "" id ="siteuser" name = "user" class="circlecheckbox" onclick = "show_user_form(this.id);"   > <label for="siteuser" class="circle-label" id="optiontext"> Assign Author from Your Site</label></span>
                               <select id = "site_user" name = "ex_user" style="margin-left:12px;margin-bottom:10px;" > 
                               <option value = "select"> -- select -- </option> ';
                      $wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");
                      if(is_array($wp_user_search)) {
                      foreach ($wp_user_search as $userid) {
                      $user_id       = (int) $userid->ID;
                      $user_login    = stripslashes($user_id->user_login);
                      $display_name  = stripslashes($userid->display_name);
                      if(isset($user_id)) { 
                     $user .= '<option value = "'. $user_id .' " > '.$display_name .'  </option>'; 
                       } 
                      } 
                     } 
                     $user .= ' </select>  </label> <br>';
                     $user .= '<span id="circlecheck"> <input type = "radio" value = "emailuser" id = "email_user" name = "user"  class="circlecheckbox" onclick = "show_user_form(this.value);" ><label for="email_user" class="circle-label" id="optiontext"> Create New User </label></span>
                                <div id = "createuser" style = "display:none;"><label id="optiontext" style = "margin-left:10px;">Enter User Login Name <input type = "text" name = "new_user_name" id = "new_user_name" value = ""  placeholder = "Login Name"/>  </label>
                                  <label id="optiontext" style = "margin-left:15px;"> Enter User Email  <input type = "email" name = "new_user" id = "emailuser" value = "" placeholder = "E-mail" onblur = "user_mapping(this.value);"  > </label></div></div>';

                     $user .= '</div>';

                     return $user;

  }
        /**
	 *Function for download the attachement 
	 *@param external url , current limit
	 *@param return guid
	 **/
	public function get_attachment($guid , $currentLimit) {
		require_once(ABSPATH . "wp-includes/pluggable.php");
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$dir = wp_upload_dir();
		$get_media_settings = get_option('uploads_use_yearmonth_folders');
		if($get_media_settings == 1){
			$dirname = date('Y') . '/' . date('m');
			$full_path = $dir ['basedir'] . '/' . $dirname;
			$baseurl = $dir ['baseurl'] . '/' . $dirname;
		}else{
			$full_path = $dir ['basedir'];
			$baseurl = $dir ['baseurl'];
		}

		$f_img = $guid;
		$fimg_path = $full_path;
		$fimg_name = @basename($f_img);
		$fimg_name = preg_replace("/[^a-zA-Z0-9._\s]/", "", $fimg_name);
		$fimg_name = preg_replace('/\s/', '-', $fimg_name);
		$fimg_name = urlencode($fimg_name);

		$parseURL = parse_url($f_img);
		$path_parts = pathinfo($f_img);
		if(!isset($path_parts['extension']))
			$fimg_name = $fimg_name . '.jpg';


		$img_res = $this->get_fimg_from_URL($f_img, $fimg_path, $fimg_name,$currentLimit, $this);
		$filepath = $fimg_path."/" . $fimg_name;

		if(@getimagesize($filepath)){
			$img = wp_get_image_editor($filepath);
			if (!is_wp_error($img)) {
				$sizes_array = array(
						// #1 - resizes to 1024x768 pixel, square-cropped image
						array('width' => 1024, 'height' => 768, 'crop' => true),
						// #2 - resizes to 100px max width/height, non-cropped image
						array('width' => 100, 'height' => 100, 'crop' => false),
						// #3 - resizes to 100 pixel max height, non-cropped image
						array('width' => 300, 'height' => 100, 'crop' => false),
						// #3 - resizes to 624x468 pixel max width, non-cropped image
						array('width' => 624, 'height' => 468, 'crop' => false)
						);
				$resize = $img->multi_resize($sizes_array);
			}
			$file  = $fimg_path."/".$fimg_name;

		}
		else    {
			$file = false;
		}

		return $file;

	}
	/**
	 *Function for get image from url
	 *@param img_name , img_path , current limit
         **/
         public function get_fimg_from_URL($f_img, $fimg_path, $fimg_name, $currentLimit = null, $logObj = ""){
		if($fimg_path!="" && $fimg_path){
			$fimg_path = $fimg_path . "/" .$fimg_name;
		}
		$ch = curl_init ($f_img);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$rawdata = curl_exec($ch);
		if(strpos($rawdata, 'Not Found') != 0) {
			$rawdata = false;
		}
		if ($rawdata == false) {
			if ($logObj == '') {
				$this->detailedLog[$currentLimit]['image'] = "<b>Image -</b> host not resolved";
			} else {
				$logObj->detailedLog[$currentLimit]['image'] = "<b>Image -</b> host not resolved";
			}
		} else {
			if (file_exists($fimg_path)) {
				unlink($fimg_path);
			}
			$fp = fopen($fimg_path, 'x');
			fwrite($fp, $rawdata);
			fclose($fp);
			$logObj->detailedLog[$currentLimit]['image'] = "<b>Image -</b>" . $fimg_name;
		}
		curl_close($ch);
	}

}
class CallWPAdvImporterObj extends WPAdvImporter_includes_helper
{
	private static $_instance = null;
	public static function getInstance()
	{
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
			self::$_instance = new WPAdvImporter_includes_helper();
		return self::$_instance;
	}
}// CallSkinnyObj Class Ends
?>

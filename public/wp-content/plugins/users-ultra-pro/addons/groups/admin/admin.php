<?php
class UsersUltraGroup {

	var $options;

	function __construct() {
	
		/* Plugin slug and version */
		$this->slug = 'userultra';
		$this->subslug = 'uultra-groups';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data( uultra_groups_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];
		
		/* Priority actions */
		add_action('admin_menu', array(&$this, 'add_menu'), 9);
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);
		add_action('admin_head', array(&$this, 'admin_head'), 9 );
		add_action('admin_init', array(&$this, 'admin_init'), 9);
		
		add_action( 'wp_ajax_edit_group', array(&$this, 'edit_group' ));
		add_action( 'wp_ajax_edit_group_conf', array(&$this, 'edit_group_conf' ));
		add_action( 'wp_ajax_edit_group_del', array(&$this, 'edit_group_del' ));
		
		
		
	}
	
	function admin_init() 
	{
	
		$this->tabs = array(
			'manage' => __('Manage Groups','xoousers')
			
		);
		$this->default_tab = 'manage';		
		
	}
	
	
	
	public function edit_group_del ()
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];		
		$query = "DELETE FROM " . $wpdb->prefix ."usersultra_groups  WHERE  `group_id` = '$group_id'  ";			
			
		$wpdb->query( $query );	
		
	}
	public function edit_group_conf ()
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];
		$group_name= $_POST["group_name"];
		if($cate_id !="" &&$cate_name!="" )
		{
			$query = "UPDATE " . $wpdb->prefix ."usersultra_groups SET `group_name` = '$group_name'  WHERE  `group_id` = '$group_id'  ";			
			
			$wpdb->query( $query );
			$html = $cate_name;
			
		}
		
		echo $html;
		die();
		
	}
	
	
	
	public function edit_group ()
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];
		
		if($group_id!="")
		{
		
			$res = $wpdb->get_results( 'SELECT *  FROM ' . $wpdb->prefix . 'usersultra_groups WHERE `group_id` = ' . $group_id . '  ' );
			
			$html="";
			foreach ( $res as $photo )
			{
				
				$html .="<p>".__( 'Name:', 'xoousers' )."</p>";
				
				$html .="<p><input type='text' value='".$photo->group_name."' class='xoouserultra-input' id='uultra_group_name_edit_".$photo->photo_cat_id."'></p>";
				
				
				$html .="<p><input type='button' class='button-primary uultra-group-close' value='".__( 'Close', 'xoousers' )."' data-id= ".$photo->group_id."> <input type='button'  class='button-primary uultra-group-modify' data-id= ".$photo->group_id." value='".__( 'Save', 'xoousers' )."'> </p>";
				
								
			}		
			
					
		}
		
		echo $html;
		die();
		
	}
	
	public function get_all () 
	{
		global $wpdb;
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_groups ORDER BY group_name ASC  ' ;
		$res = $wpdb->get_results($sql);
		return $res ;	
	
	}
	
	function admin_head(){

	}

	function add_styles(){
	
		wp_register_script( 'uultra_group_js', uultra_groups_url . 'admin/scripts/admin.js', array( 
			'jquery'
		) );
		wp_enqueue_script( 'uultra_group_js' );
	
		wp_register_style('uultra_group_css', uultra_groups_url . 'admin/css/admin.css');
		wp_enqueue_style('uultra_group_css');
		
	}
	
	function add_menu()
	{
		add_submenu_page( 'userultra', __('Groups','xoousers'), __('Groups','xoousers'), 'manage_options', 'uultra-groups', array(&$this, 'admin_page') );
		
		do_action('userultra_admin_menu_hook');
		
		
	}

	function admin_tabs( $current = null ) {
			$tabs = $this->tabs;
			$links = array();
			if ( isset ( $_GET['tab'] ) ) {
				$current = $_GET['tab'];
			} else {
				$current = $this->default_tab;
			}
			foreach( $tabs as $tab => $name ) :
				if ( $tab == $current ) :
					$links[] = "<a class='nav-tab nav-tab-active' href='?page=".$this->subslug."&tab=$tab'>$name</a>";
				else :
					$links[] = "<a class='nav-tab' href='?page=".$this->subslug."&tab=$tab'>$name</a>";
				endif;
			endforeach;
			foreach ( $links as $link )
				echo $link;
	}

	function get_tab_content() {
		$screen = get_current_screen();
		if( strstr($screen->id, $this->subslug ) ) {
			if ( isset ( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			} else {
				$tab = $this->default_tab;
			}
			require_once uultra_groups_path.'admin/panels/'.$tab.'.php';
		}
	}
	
	public function save()
	{
		global $wpdb;
		
		if(isset($_POST['group_name']) && $_POST['group_name']!="")
		{
		
			$group_name = $_POST['group_name'];			
			$new_array = array(
							'group_id'     => null,
							'group_name'   => $group_name						
							
							
						);
						
				
			$wpdb->insert( $wpdb->prefix . 'usersultra_groups', $new_array, array( '%d', '%s'));
			echo '<div class="updated"><p><strong>'.__('New group has been created.','xoousers').'</strong></p></div>';
		}else{
			
			echo '<div class="error"><p><strong>'.__('Please input a name.','xoousers').'</strong></p></div>';
			
			
		}
	
	
	}
	
	
	function admin_page() {
	
		
		if (isset($_POST['add-group']) && $_POST['add-group']=='add-group') 
		{
			$this->save();
		}

		
		
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin">
        
           <h2>USERS ULTRA PRO - GROUPS</h2>
           
           <div id="icon-users" class="icon32"></div>
			
						
			<h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?></h2>

			<div class="<?php echo $this->slug; ?>-admin-contain">
				
				<?php $this->get_tab_content(); ?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }

}

$uultra_group = new UsersUltraGroup();
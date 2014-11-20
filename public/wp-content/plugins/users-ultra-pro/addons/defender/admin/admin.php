<?php
class UsersUltraIpDefender {

	var $options;

	function __construct() {
	
		/* Plugin slug and version */
		$this->slug = 'userultra';
		$this->subslug = 'uultra-ipdefender';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data( uultra_ipdefender_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];
		
		/* Priority actions */
		add_action('admin_menu', array(&$this, 'add_menu'), 9);
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);
		add_action('admin_head', array(&$this, 'admin_head'), 9 );
		add_action('admin_init', array(&$this, 'admin_init'), 9);
		
		
		add_action( 'wp_ajax_ip_number_del', array(&$this, 'delete_record' ));
		
		
		
	}
	
	function admin_init() 
	{
	
		$this->tabs = array(
			'manage' => __('I.P. Defender','xoousers')
			
		);
		$this->default_tab = 'manage';		
		
	}
	
	
	
	public function delete_record ()
	{
		global $wpdb;
		
		$ip_id = $_POST["ip_id"];		
		$query = "DELETE FROM " . $wpdb->prefix ."usersultra_ip_defender  WHERE  `ip_id` = '$ip_id'  ";			
			
		$wpdb->query( $query );	
		
	}
		
	
	
	public function get_all () 
	{
		global $wpdb;
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_ip_defender ORDER BY ip_number ASC  ' ;
		$res = $wpdb->get_results($sql);
		return $res ;	
	
	}
	
	function admin_head(){

	}

	function add_styles(){
	
		wp_register_script( 'uultra_ipdefender_js', uultra_ipdefender_url . 'admin/scripts/admin.js', array( 
			'jquery'
		) );
		wp_enqueue_script( 'uultra_ipdefender_js' );
	
		wp_register_style('uultra_ipdefender_css', uultra_ipdefender_url . 'admin/css/admin.css');
		wp_enqueue_style('uultra_ipdefender_css');
		
	}
	
	function add_menu()
	{
		add_submenu_page( 'userultra', __('I.P. Defender','xoousers'), __('I.P. Defender','xoousers'), 'manage_options', 'uultra-ipdefender', array(&$this, 'admin_page') );
		
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
			require_once uultra_ipdefender_path.'admin/panels/'.$tab.'.php';
		}
	}
	
	public function save()
	{
		global $wpdb, $xoouserultra;
		
		if(isset($_POST['ip_number']))
		{
		
			$ip_number = $_POST['ip_number'];			
			$new_array = array(
							'ip_id'     => null,
							'ip_number'   => $ip_number						
							
							
						);
						
			//check if already added			
			$amount = $xoouserultra->defender->check_ip($ip_number);
						
			if($amount==0 && $ip_number!=""){
				
				$wpdb->insert( $wpdb->prefix . 'usersultra_ip_defender', $new_array, array( '%d', '%s'));
				
			}
			
		}
	
	
	}
	
	
	function admin_page() {
	
		
		if (isset($_POST['add-ipnumber']) && $_POST['add-ipnumber']=='add-ipnumber') 
		{
			$this->save();
		}

		
		
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin">
        
           <h2>USERS ULTRA PRO - I.P. DEFENDER</h2>
           
           <div id="icon-users" class="icon32"></div>
			
						
			<h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?></h2>

			<div class="<?php echo $this->slug; ?>-admin-contain">
				
				<?php $this->get_tab_content(); ?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }

}

$uultra_ip_defender = new UsersUltraIpDefender();
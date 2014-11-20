<?php
class uultra_dg_admin {

	var $options;

	function __construct() {
	
		/* Plugin slug and version */
		$this->slug = 'userultra';
		$this->subslug = 'userultra-badges';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data( uultra_dg_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];
		
		/* Priority actions */
		//add_action('uultra_admin_menu_hook', array(&$this, 'add_menu'), 9);
		add_action('admin_menu', array(&$this, 'add_menu'), 9);
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);
		add_action('admin_head', array(&$this, 'admin_head'), 9 );
		add_action('admin_init', array(&$this, 'admin_init'), 9);
		
	}
	
	function admin_init() {
	
		$this->tabs = array(
			'manage' => __('Medallion Fulfillment/Assignment','xoousers'),
			'achievement' => __('View/Edit Fulfillment Medallions','xoousers'),
			'user_badges' => __('Manage Assigned Medallions','xoousers'),
		);
		$this->default_tab = 'manage';
		
		$this->options = get_option('uultra_dg');
		if (!get_option('uultra_dg')) {
			update_option('uultra_dg', uultra_dg_default_options() );
		}
		
	}
	
	function admin_head(){

	}

	function add_styles(){
	
		wp_register_script( 'uultra_badges', uultra_dg_url . 'admin/scripts/admin.js', array( 
			'jquery'
		) );
		wp_enqueue_script( 'uultra_badges' );
	
		wp_register_style('uultra_badges',uultra_dg_url . 'admin/css/admin.css');
		wp_enqueue_style('uultra_badges');
		
	}
	
	function add_menu() {
		add_submenu_page( 'userultra', __('Medallions and Fulfillments','xoousers'), __('Medallions and Fulfillments','xoousers'), 'manage_options', 'userultra-badges', array(&$this, 'admin_page') );
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
			require_once uultra_dg_path.'admin/panels/'.$tab.'.php';
		}
	}
	
	function save() {
	
		$this->options['exclude_post_types'] = '';
		
		/* other post fields */
		foreach($_POST as $key => $value) {
			if ($key != 'submit') {
				if (!is_array($_POST[$key])) {
					$this->options[$key] = esc_attr($_POST[$key]);
				} else {
					$this->options[$key] = $_POST[$key];
				}
			}
		}
		
		update_option('uultra_dg', $this->options);
		echo '<div class="updated"><p><strong>'.__('Settings saved.','xoousers').'</strong></p></div>';
	}

	function reset() {
		update_option('uultra_dg', uultra_dg_default_options() );
		$this->options = array_merge( $this->options, uultra_dg_default_options() );
		echo '<div class="updated"><p><strong>'.__('Settings are reset to default.','xoousers').'</strong></p></div>';
	}
	
	function install_badge(){
		global $uultra_badges;
		$res = $uultra_badges->new_badge( $_POST );
		if (isset($res['error'])){
			echo '<div class="error"><p><strong>'.$res['error'].'</strong></p></div>';
		}
	}
	
	function find_badges(){
		global $uultra_badges;
		$res = $uultra_badges->find_badges( $_POST );
		if (isset($res['error'])){
			echo '<div class="error"><p><strong>'.$res['error'].'</strong></p></div>';
		}
	}

	function admin_page() {
	
		if (isset($_POST['find-user-badges'])){
			$this->find_badges();
		}
	
		if (isset($_POST['insert-badge'])){
			$this->install_badge();
		}

		if (isset($_POST['submit'])) {
			$this->save();
		}

		if (isset($_POST['reset-options'])) {
			$this->reset();
		}
		
		if (isset($_POST['rebuild-pages'])) {
			$this->rebuild_pages();
		}
		
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin">
			
			 <h2>USERS ULTRA PRO - <?php _e('MEDALLIONS AND FULFILLMENTS ','xoousers')?></h2>
			
			<h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?></h2>

			<div class="<?php echo $this->slug; ?>-admin-contain">
				
				<?php $this->get_tab_content(); ?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }

}

$uultra_badges_admin = new uultra_dg_admin();
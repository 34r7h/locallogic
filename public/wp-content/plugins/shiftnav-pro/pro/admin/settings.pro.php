<?php

function shiftnav_settings_pro_links(){
	echo '<a target="_blank" class="button" href="http://sevenspark.com/support"><i class="fa fa-user-md"></i> Support</a>';
}
if( ! SHIFTNAV_EXTENDED ) add_action( 'shiftnav_settings_before_title' , 'shiftnav_settings_pro_links' );

/**
 * CREATE INSTANCE MANAGER
 */

add_action( 'shiftnav_settings_before' , 'shiftnav_instance_manager');

function shiftnav_instance_manager(){
	//update_option( 'shiftnav_menus' , array() );
	//$m = get_option( 'shiftnav_menus' );
	//shiftp( $m );

	?>

	<div class="shiftnav_instance_manager">

		<a class="shiftnav_instance_toggle shiftnav_instance_button">+ Add ShiftNav Instance</a>

		<div class="shiftnav_instance_wrap shiftnav_instance_container_wrap">

			<div class="shiftnav_instance_container">

				<h3>Add ShiftNav Instance</h3>

				<form class="shiftnav_instance_form">
					<input class="shiftnav_instance_input" type="text" name="shiftnav_instance_id" placeholder="menu_id" />
					<?php wp_nonce_field( 'shiftnav-add-instance' ); ?>
					<a class="shiftnav_instance_button shiftnav_instance_create_button">Create Instance</a>
				</form>

				<p class="shiftnav_instance_form_desc">Enter an ID for your new menu.  This ID will be used when printing the menu, 
					and must contain only letters, hyphens, and underscores.  <a class="shiftnav_instance_notice_close" href="#">close</a></p>

				<span class="shiftnav_instance_close">&times;</span>

			</div>

		</div>


		<div class="shiftnav_instance_wrap shiftnav_instance_notice_wrap shiftnav_instance_notice_success">
			<div class="shiftnav_instance_notice">
				New menu created. <a href="<?php echo admin_url('themes.php?page=shiftnav-settings'); ?>" class="shiftnav_instance_button">Refresh Page</a> 
				<p>Note: Any setting changes you've made have not been saved yet.  <a class="shiftnav_instance_notice_close" href="#">close</a></p>
			</div>
		</div>

		<div class="shiftnav_instance_wrap shiftnav_instance_notice_wrap shiftnav_instance_notice_error">
			<div class="shiftnav_instance_notice">
				New menu creation failed.  <span class="shiftnav-error-message">You may have a PHP error on your site which prevents AJAX requests from completing.</span>  <a class="shiftnav_instance_notice_close" href="#">close</a>
			</div>
		</div>

		<div class="shiftnav_instance_wrap shiftnav_instance_notice_wrap shiftnav_instance_delete_notice_success">
			<div class="shiftnav_instance_notice">
				Instance Deleted.  <a class="shiftnav_instance_notice_close" href="#">close</a></p>
			</div>
		</div>

		<div class="shiftnav_instance_wrap shiftnav_instance_notice_wrap shiftnav_instance_delete_notice_error">
			<div class="shiftnav_instance_notice">
				Menu deletion failed.  <span class="shiftnav-delete-error-message">You may have a PHP error on your site which prevents AJAX requests from completing.</span>  <a class="shiftnav_instance_notice_close" href="#">close</a>
			</div>
		</div>

		
	</div>

	<?php
}

function shiftnav_add_instance_callback(){

	check_ajax_referer( 'shiftnav-add-instance' , 'shiftnav_nonce' );

	$response = array();

	$serialized_settings = $_POST['shiftnav_data'];
	$dirty_settings = array();
	parse_str( $serialized_settings, $dirty_settings );
	
	//ONLY ALLOW SETTINGS WE'VE DEFINED	
	$data = wp_parse_args( $dirty_settings, array( 'shiftnav_instance_id' ) );

	$new_id = $data['shiftnav_instance_id'];

	if( $new_id == '' ){
		$response['error'] = 'Please enter an ID. ';
	}
	else{
		$new_id = sanitize_title( $new_id );

		//update 
		$menus = get_option( 'shiftnav_menus' , array() );

		if( in_array( $new_id , $menus ) ){
			$response['error'] = 'That ID is already taken. ';
		}
		else{
			$menus[] = $new_id;
			update_option( 'shiftnav_menus' , $menus );
		}

		$response['id'] = $new_id;
	}

	$response['data'] = $data;

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_shiftnav_add_instance', 'shiftnav_add_instance_callback' );


function shiftnav_delete_instance_callback(){

	check_ajax_referer( 'shiftnav-delete-instance' , 'shiftnav_nonce' );

	$response = array();
//echo json_encode( $_POST['shiftnav_data'] );
//die();
	//$serialized_settings = $_POST['shiftnav_data'];
	//$dirty_settings = array();
	//parse_str( $serialized_settings, $dirty_settings );
	
	$dirty_settings = $_POST['shiftnav_data'];

	//ONLY ALLOW SETTINGS WE'VE DEFINED	
	$data = wp_parse_args( $dirty_settings, array( 'shiftnav_instance_id' ) );

	$id = $data['shiftnav_instance_id'];

	if( $id == '' ){
		$response['error'] = 'Missing ID';
	}
	else{
		
		$menus = get_option( 'shiftnav_menus' , array() );

		if( !in_array( $id , $menus ) ){
			$response['error'] = 'ID not in $menus ['.$id.']';
		}
		else{
			//unset( $menus[$id] );
			$i = array_search( $id , $menus );
			if( $i !== false ) unset( $menus[$i] );

			update_option( 'shiftnav_menus' , $menus );
			$response['menus'] = $menus;
		}

		$response['id'] = $id;
	}

	$response['data'] = $data;

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_shiftnav_delete_instance', 'shiftnav_delete_instance_callback' );


/**
 * CREATE PRO SETTINGS
 */

add_filter( 'shiftnav_settings_panel_sections' , 'shiftnav_settings_panel_sections_pro' );
add_filter( 'shiftnav_settings_panel_fields' , 'shiftnav_settings_panel_fields_pro' );

function shiftnav_settings_panel_sections_pro( $sections = array() ){
	$menus = get_option( 'shiftnav_menus' , array() );

	foreach( $menus as $menu ){
		$sections[] = array(
			'id'	=> SHIFTNAV_PREFIX.$menu,
			'title' => '+'.$menu,
		);
	}

	return $sections;
}

function shiftnav_settings_panel_fields_pro( $fields = array() ){


	/** ADD MAIN NAV PRO OPTIONS **/

	$main = SHIFTNAV_PREFIX.'shiftnav-main';


	$fields[$main][] = array(
		'name'		=> 'disable_menu',
		'label'		=> __( 'Disable Menu' , 'shiftnav' ),
		'desc'		=> __( 'Check this to disable the menu entirely; the panel can be used for custom content' , 'shiftnav' ),
		'type'		=> 'checkbox',
		'default'	=> 'off',
	);

	$fields[$main][] = array(
		'name'	=>	'image',
		'label'	=>	__( 'Top Image' , 'shiftnav' ),
		'desc'	=> __( '' , 'shiftnav' ),
		'type'	=> 'image',
		'default' => ''
	);

	$fields[$main][] = array(
		'name'	=>	'image_padded',
		'label'	=>	__( 'Pad Image' , 'shiftnav' ),
		'desc'	=> __( 'Add padding to align image with menu item text.  Uncheck to expand to the edges of the panel.' , 'shiftnav' ),
		'type'	=> 'checkbox',
		'default' => 'on'
	);

	$fields[$main][] = array(
		'name'	=>	'image_link',
		'label'	=>	__( 'Image Link (URL)' , 'shiftnav' ),
		'desc'	=> __( 'Make the image a link to this URL.' , 'shiftnav' ),
		'type'	=> 'text',
		'default' => ''
	);

	$fields[$main][] = array(
		'name'	=>	'content_before',
		'label'	=>	__( 'Custom Content Before Menu' , 'shiftnav' ),
		'desc'	=> __( '' , 'shiftnav' ),
		'type'	=> 'textarea',
		'default' => '',
		'sanitize_callback' => 'shiftnav_allow_html',
	);

	$fields[$main][] = array(
		'name'	=>	'content_after',
		'label'	=>	__( 'Custom Content After Menu' , 'shiftnav' ),
		'desc'	=> __( '' , 'shiftnav' ),
		'type'	=> 'textarea',
		'default' => '',
		'sanitize_callback' => 'shiftnav_allow_html',
	);




	/** ADD MAIN TOGGLE OPTIONS **/
	$toggle = SHIFTNAV_PREFIX.'togglebar';

	$fields[$toggle][] = array(
		'name'	=> 'toggle_content_left',
		'label'	=> __( 'Toggle Content Left Edge' , 'shiftnav' ),
		'desc'	=> '', //__( '' , 'shiftnav' ),
		'type'	=> 'textarea',
		'default' => '', //get_bloginfo( 'title' )
		'sanitize_callback' => 'shiftnav_allow_html',
	);

	$fields[$toggle][] = array(
		'name'	=> 'toggle_content_right',
		'label'	=> __( 'Toggle Content Right Edge' , 'shiftnav' ),
		'desc'	=> '', //__( '' , 'shiftnav' ),
		'type'	=> 'textarea',
		'default' => '', //get_bloginfo( 'title' )
		'sanitize_callback' => 'shiftnav_allow_html',
	);


	/** ADD INSTANCES **/

	$menus = get_option( 'shiftnav_menus' , array() );

	foreach( $menus as $menu ){

		$integration_code = '
			<div class="shiftnav-desc-row">
				<span class="shiftnav-code-snippet-type">PHP</span> <code class="shiftnav-highlight-code">&lt;?php shiftnav_toggle( \''.$menu.'\' ); ?&gt;</code>
			</div>
			<div class="shiftnav-desc-row">
				<span class="shiftnav-code-snippet-type">Shortcode</span> <code class="shiftnav-highlight-code">[shiftnav_toggle target="'.$menu.'"]</code>'.//Toggle Content[/shiftnav_toggle]</code>
			'</div>
			<div class="shiftnav-desc-row">
				<span class="shiftnav-code-snippet-type">HTML</span> <code class="shiftnav-highlight-code">&lt;a class="shiftnav-toggle" data-shiftnav-target="'.$menu.'"&gt; Toggle Content &lt;/a&gt;</code>
			</div>
			<p class="shiftnav-sub-desc shiftnav-desc-mini" >Click to select, then <strong><em>&#8984;+c</em></strong> or <strong><em>ctrl+c</em></strong> to copy to clipboard</p>
			<p class="shiftnav-sub-desc shiftnav-desc-understated">Pick the appropriate code and add to your template or content where you want the toggle to appear.  The menu panel itself is loaded automatically.  You can add the toggle code as many times as you like.</p>
		';
		
		$fields[SHIFTNAV_PREFIX.$menu] = array(
			array(
				'name'	=> 'php',
				'label'	=> __( 'Integration Code' , 'shiftnav' ),
				'desc'	=> $integration_code,
				'type'	=> 'html',
			),
			array(
				'name'	=> 'instance_name',
				'label'	=> __( 'Instance Name' , 'shiftnav' ),
				'desc'	=> __( '' , 'shiftnav' ),
				'type'	=> 'text',
				'default' => $menu
			),
			array(
				'name'	=> 'menu',
				'label'	=> __( 'Display Menu' , 'shiftnav' ),
				'desc'	=> 'Select the menu to display or <a href="'.admin_url( 'nav-menus.php' ).'">create a new menu</a>.  This setting will override the Theme Location setting.',
				'type'	=> 'select',
				'options' => shiftnav_get_nav_menu_ops(),
				//'options' => get_registered_nav_menus()
			),
			array(
				'name'	=> 'theme_location',
				'label'	=> __( 'Theme Location' , 'shiftnav' ),
				'desc'	=> __( 'Select the Theme Location to display.  The Menu setting will override this setting if a menu is selected.', 'shiftnav' ),
				'type'	=> 'select',
				//'options' => shiftnav_get_nav_menu_ops(),
				'options' => shiftnav_get_theme_location_ops()
			),
			array(
				'name'	=> 'edge',
				'label'	=> __( 'Edge' , 'shiftnav' ),
				'desc'	=> __( 'Select which edge of your site to display the menu on' , 'shiftnav' ),
				'type'	=> 'radio',
				'options' => array(
					'left' => 'Left',
					'right'=> 'Right',
				),
				'default' => 'left'
			),
			array(
				'name'		=> 'disable_menu',
				'label'		=> __( 'Disable Menu' , 'shiftnav' ),
				'desc'		=> __( 'Check this to disable the menu entirely; the panel can be used for custom content' , 'shiftnav' ),
				'type'		=> 'checkbox',
				'default'	=> 'off',
			),

			array(
				'name'	=> 'skin',
				'label'	=> __( 'Skin' , 'shiftnav' ),
				'desc'	=> __( 'Select which skin to use for this instance' , 'shiftnav' ),
				'type'	=> 'select',
				'options' => shiftnav_get_skin_ops(),
				//'options' => get_registered_nav_menus()

			),

			array(
				'name'		=> 'indent_submenus',
				'label'		=> __( 'Indent Always Visible Submenus' , 'shiftnav' ),
				'desc'		=> __( 'Check this to indent submenu items of always-visible submenus' , 'shiftnav' ),
				'type'		=> 'checkbox',
				'default'	=> 'off',
			),

			array(
				'name'	=> 'toggle_content',
				'label'	=> __( 'Toggle Content' , 'shiftnav' ),
				'desc'	=> __( 'Enter the content to be displayed in the toggle, which you will insert into your template with the integration code at the top of this tab.' , 'shiftnav' ),
				'type'	=> 'textarea',
				'default' => 'Toggle', // 'Toggle '.$menu,
				'sanitize_callback' => 'shiftnav_allow_html',
			),


			array(
				'name' => 'display_site_title',
				'label' => __( 'Display Site Title', 'shiftnav' ),
				'desc' => __( 'Display the site title in the menu', 'shiftnav' ),
				'type' => 'checkbox',
				'default' => 'off'
			),

			array(
				'name' => 'display_instance_title',
				'label' => __( 'Display Instance Name', 'shiftnav' ),
				'desc' => __( 'Display the instance name in the menu', 'shiftnav' ),
				'type' => 'checkbox',
				'default' => 'on'
			),

			array(
				'name'	=>	'image',
				'label'	=>	__( 'Top Image' , 'shiftnav' ),
				'desc'	=> __( '' , 'shiftnav' ),
				'type'	=> 'image',
				'default' => ''
			),

			array(
				'name'	=>	'image_padded',
				'label'	=>	__( 'Pad Image' , 'shiftnav' ),
				'desc'	=> __( 'Add padding to align image with menu item text.  Uncheck to expand to the edges of the panel.' , 'shiftnav' ),
				'type'	=> 'checkbox',
				'default' => 'on'
			),

			array(
				'name'	=>	'image_link',
				'label'	=>	__( 'Image Link (URL)' , 'shiftnav' ),
				'desc'	=> __( 'Make the image a link to this URL.' , 'shiftnav' ),
				'type'	=> 'text',
				'default' => ''
			),

			array(
				'name'	=>	'content_before',
				'label'	=>	__( 'Custom Content Before Menu' , 'shiftnav' ),
				'desc'	=> __( '' , 'shiftnav' ),
				'type'	=> 'textarea',
				'default' => '',
				'sanitize_callback' => 'shiftnav_allow_html',
			),

			array(
				'name'	=>	'content_after',
				'label'	=>	__( 'Custom Content After Menu' , 'shiftnav' ),
				'desc'	=> __( '' , 'shiftnav' ),
				'type'	=> 'textarea',
				'default' => '',
				'sanitize_callback' => 'shiftnav_allow_html',
			),

			/*
			array(
				'name' => 'display_condition',
				'label' => __( 'Display on', 'shiftnav' ),
				'desc' => __( '', 'shiftnav' ),
				'type' => 'multicheck',
				'options' => array(
					'all' 	=> 'All',
					'posts' => 'Posts',
					'pages' => 'Pages',
					'home' 	=> 'Home Page',
					'blog'	=> 'Blog Page',
				),
				'default' => array( 'all' => 'all' )
			),
			*/
		
			array(
				'name'	=> 'delete',
				'label'	=> __( 'Delete Instance' , 'shiftnav' ),
				'desc'	=> '<a class="shiftnav_instance_button shiftnav_instance_button_delete" href="#" data-shiftnav-instance-id="'.$menu.'" data-shiftnav-nonce="'.wp_create_nonce( 'shiftnav-delete-instance' ).'">'.__( 'Permanently Delete Instance' , 'shiftnav' ).'</a>',
				'type'	=> 'html',
			),
			
		);
	}

	return $fields;
}
<?php

function shiftnav_pro_menu_item_settings( $settings ){
	
	$settings['general'][30] = array(
		'id' 		=> 'icon',
		'title'		=> 'Icon',
		'type'		=> 'icon',
		'default' 	=> '',
		'desc'		=> '',
		'ops'		=> shiftnav_get_icon_ops()
	);

	$settings['submenu'][20] = array(
		'id' 		=> 'submenu_type',
		'title'		=> 'Submenu Type',
		'type'		=> 'select',
		'default'	=> 'always',
		'desc'		=> '',
		'ops'		=> array(
						'always'	=>	'Always visible',
						'accordion'	=>	'Accordion',
						'shift'		=>	'Shift',
					),
	);

	return $settings;
}
add_filter( 'shiftnav_menu_item_settings' , 'shiftnav_pro_menu_item_settings' );
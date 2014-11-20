<?php
define('uultra_forms_url',plugin_dir_url(__FILE__ ));
define('uultra_forms_path',plugin_dir_path(__FILE__ ));

	/* functions */
	foreach (glob(uultra_forms_path . 'functions/*.php') as $filename) { require_once $filename; }
	
	/* administration */
	if (is_admin()){
		foreach (glob(uultra_forms_path . 'admin/*.php') as $filename) { include $filename; }
	}
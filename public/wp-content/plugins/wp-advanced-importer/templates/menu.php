<?php
$post = $page = $custompost = $categories = $users = $customtaxonomy = $comments = $eshop = $wpcommerce = $woocommerce = $settings = $support = $dashboard = $export = $mappingtemplate = $filemanager = $schedulemapping = '';
$impCEM = CallWPAdvImporterObj::getInstance();
$settings = $impCEM->getSettings();
$get_pluginData = get_plugin_data(plugin_dir_path( __FILE__ ).'../index.php'); 
$mod = isset($_REQUEST['__module']) ? $_REQUEST['__module'] : '';
if(is_array($settings)){
	foreach($settings as $key){
		$$key = true;
	}
}
if(isset($_POST['post_csv']) && $_POST['post_csv'] == 'Import')
{
	$dashboard = 'selected';
}
else if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	$$action = 'selected';
}
else if(isset($mod) && !empty($mod))
{
	$$mod = 'selected';
}
$menuHTML = "<div class='csv-top-navigation-wrapper' id='header' name='mainNavigation'><ul id='topNavigation'>";
#if($post)
$menuHTML .="<li class=\"navigationMenu $post\"><a href = 'admin.php?page=".WP_CONST_ADVANCED_XML_IMP_SLUG."/index.php&__module=importtype&step=uploadfile' class = 'navigationMenu-link' id='module4'>Imports</a></li>";
#$menuHTML .= "<li class=\"navigationMenu $settings\"><a href = 'admin.php?page=".WP_CONST_ADVANCED_XML_IMP_SLUG."/index.php&__module=settings' class='navigationMenu-link' id='module15'>Settings</a></li>";
$menuHTML .= "<li class=\"navigationMenu $support\"><a href = 'admin.php?page=".WP_CONST_ADVANCED_XML_IMP_SLUG."/index.php&__module=support' class='navigationMenu-link' id='module16'>Support</a></li>";
$tabcount = count(get_option('wpadvxmlimpfreesettings')); 
$menuHTML .= "</ul>";
/*$menuHTML .= "<div style='margin-right:10px;width: 250px;float: right;'>";
$menuHTML .= "<span class='prolinks'><a class='label label-info' href='http://www.smackcoders.com/wp-ultimate-csv-importer-pro.html' target='_blank'>GO PRO NOW</a></span>";
$menuHTML .= "<span class='prolinks'><a class='label label-info' href='http://demo.smackcoders.com/demowpthree/wp-admin/admin.php?page=wp-ultimate-csv-importer-pro/index.php&__module=dashboard' target='_blank'>TRY PRO LIVE DEMO NOW</a></span>";
$menuHTML .= "</div>";*/
$menuHTML .= "</div>";
echo $menuHTML;


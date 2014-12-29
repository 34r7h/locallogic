<?php
/*
Plugin Name: GeoDirectory Autocompleter
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Autocompleter Plugin.
Version: 1.0.3
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/


global $wpdb,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
$geodir_addon_list['geodir_autocompleter_manager'] = 'yes' ;

if(!isset($plugin_prefix))
	$plugin_prefix = 'geodir_';



if (!defined('GEODIRAUTOCOMPLETER_TEXTDOMAIN')) define('GEODIRAUTOCOMPLETER_TEXTDOMAIN','geodir_autocompleter');
$locale = apply_filters('plugin_locale', get_locale(), GEODIRAUTOCOMPLETER_TEXTDOMAIN);
load_textdomain(GEODIRAUTOCOMPLETER_TEXTDOMAIN, WP_LANG_DIR.'/'.GEODIRAUTOCOMPLETER_TEXTDOMAIN.'/'.GEODIRAUTOCOMPLETER_TEXTDOMAIN.'-'.$locale.'.mo');
load_plugin_textdomain(GEODIRAUTOCOMPLETER_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/geodir-autocompleter-languages');
include_once('language.php');


define('AUTOCOMPLETER_PLUGIN_URL',plugins_url('',__FILE__));


if ( is_admin() ) :

	register_activation_hook( __FILE__, 'geodir_autocompleter_activation' ); 
	
	register_uninstall_hook(__FILE__,'geodir_autocompleter_uninstall');
	
	add_filter('geodir_settings_tabs_array','geodir_adminpage_autocompleter',5); 
	
	add_action('geodir_admin_option_form' , 'geodir_autocompleter_options_form', 5);
	
endif;

add_action('admin_init', 'geodir_autocompleter_activation_redirect');

add_action('admin_init', 'geodir_autocompleter_from_submit_handler');

add_action('activated_plugin','geodir_autocompleter_plugin_activated') ;
function geodir_autocompleter_plugin_activated($plugin)
{
	if (!get_option('geodir_installed')) 
	{
		$file = plugin_basename(__FILE__);
		if($file == $plugin) 
		{
			$all_active_plugins = get_option( 'active_plugins', array() );
			if(!empty($all_active_plugins) && is_array($all_active_plugins))
			{
				foreach($all_active_plugins as $key => $plugin)
				{
					if($plugin ==$file)
						unset($all_active_plugins[$key]) ;
				}
			}
			update_option('active_plugins',$all_active_plugins);
			
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', GEODIRAUTOCOMPLETER_TEXTDOMAIN));
	}
	
}



function geodir_autocompleter_activation()
{
	if (get_option('geodir_installed')) 
	{	
		geodir_update_options( geodir_autocompleter_options(), true );
		
		update_option('geodir_autocompleter_matches_label', 's');
		
		add_option('geodir_autocompleter_activation_redirect', 1);
	}
}


function geodir_autocompleter_activation_redirect(){

	if (get_option('geodir_autocompleter_activation_redirect', false))
	{
		delete_option('geodir_autocompleter_activation_redirect');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=autocompleter_fields')); 
	}
	
}


function geodir_autocompleter_uninstall(){
	
	$default_options = geodir_autocompleter_options();
	
	if(!empty($default_options)){
		foreach($default_options as $value){
			if(isset($value['id']) && $value['id'] != '')
				delete_option($value['id'], '');
		}
	}
	
	delete_option('geodir_autocompleter_matches_label', '');
	
}


add_action('wp_ajax_geodir_autocompleter_ajax_action', "geodir_autocompleter_ajax_actions");
add_action( 'wp_ajax_nopriv_geodir_autocompleter_ajax_action', 'geodir_autocompleter_ajax_actions' );

function geodir_autocompleter_ajax_url(){
	
	$gd_post_type = geodir_get_current_posttype();
	
	if($gd_post_type == '')
		$gd_post_type = 'gd_place';
	
	return admin_url('admin-ajax.php?action=geodir_autocompleter_ajax_action&post_type='.$gd_post_type);
}



function geodir_autocompleter_ajax_actions(){
	global $autocompleter_post_type;
	
	
	if(isset($_REQUEST['q']) && $_REQUEST['q'] && isset($_REQUEST['post_type']))
	{
		autocompleters();
	}

	exit;
	
}





function autocompleters()
{
	global $wpdb;
	
	$geodir_terms_autocomplete = "''";
	$gt_posttypes_autocomplete = "''";

	$post_types = geodir_get_posttypes('array');
	
	$post_type_array = array();
	$post_type_tax = array();
	
	$gd_post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'gd_place';
	
	if(!empty($post_types) && is_array($post_types) && array_key_exists($gd_post_type ,$post_types ) )
	{
			if(!empty($post_types[$gd_post_type]) && is_array($post_types[$gd_post_type]) && array_key_exists('taxonomies' , $post_types[$gd_post_type]  ))
			{
				foreach($post_types[$gd_post_type]['taxonomies'] as $geodir_taxonomy)
				{
					$post_type_tax[] = $geodir_taxonomy;
				}
			}
	}
	
	
	if(!empty($post_type_tax))
		$geodir_terms_autocomplete = "'".implode("','", $post_type_tax)."'";
	
		$gt_posttypes_autocomplete = "'". $gd_post_type."'";
	
	$results = (get_option('autocompleter_results')!= false)?get_option('autocompleter_results'):1;
	
	$search = isset($_GET['q']) ? $_GET['q'] : '';
	
	if(strlen($search)){
		switch($results){
			case 1: 
				
				$words1 = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT concat( name, '|', sum( count ) ) name, sum( count ) cnt FROM ".$wpdb->prefix."terms t, ".$wpdb->prefix."term_taxonomy tt WHERE t.term_id = tt.term_id AND t.name LIKE %s AND tt.taxonomy in (".$geodir_terms_autocomplete.") GROUP BY t.term_id ORDER BY cnt DESC",
						array($search.'%')
					)				
				);
				
				
				$words2 = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_title as name FROM $wpdb->posts where post_status='publish' and post_type in (".$gt_posttypes_autocomplete.") and post_date < NOW() and post_title LIKE %s ORDER BY post_title",
						array('%'.$search.'%')
					)
				);  
				
 				$words = array_merge((array)$words1 ,(array)$words2 ); 
				asort($words);
				break;
		} 
		
		foreach ($words as $word){
			if($results > 0){
				$id = isset($word->ID) ? $word->ID : '';
				echo $word->name."|".get_permalink($id)."\n";
			}else{
				echo $word->name."\n";
				}
		}
	}
}


function geodir_autocompleter_init_script() {

	$autocomplete_field_name = get_option('geodir_autocompleter_matches_label');

	if($autocomplete_field_name == '') {
		$autocomplete_field_name = 's';
	}
	
	$default_near_text = NEAR_TEXT;
	if (get_option('geodir_near_field_default_text')) {
		$default_near_text = __(get_option('geodir_near_field_default_text'), GEODIRECTORY_TEXTDOMAIN);
	}
	
	$autosubmit = (get_option('geodir_autocompleter_autosubmit')==1)?'
		function onSelectItem(row,$form){
			if($form.find(\' input[name="snear"]\').val()==\''.addslashes($default_near_text).'\'){
				jQuery(\'input[name="snear"]\').val(\'\');
			}
			if(typeof(jQuery(row).find(\'span\').attr(\'attr\')) != \'undefined\'){
				$form.submit();
			} else {
					$form.submit();
			}
		}':'function onSelectItem(row){jQuery(row).parents("form").find(\'input[name="'.$autocomplete_field_name.'"]\').focus();}';
	$extra = (get_option('autocompleter_show_items')==1)?'
		function formatItem(row) {
			if(row.length == 3){
				var attr = "attr=\"" + row[2] + "\"";
			} else {
				attr = "";
			}
			return "<span "+attr+">" + row[1] + " </span>" + row[0];
		}':'
		function formatItem(row) {
			var attr;
			if(row.length == 3){
				attr = "attr=\"" + row[2] + "\"";
			} else {
				attr = "";
			}
			return row[0] + "<span "+attr+"></span>"
		}';
	$results = (get_option('autocompleter_results')!='')?get_option('autocompleter_results'):1;
	
	if(get_option('geodir_enable_autocompleter')){
		echo '<script type="text/javascript">
			
			jQuery(document).ready(function() {
				jQuery("input[name='.$autocomplete_field_name.']").autocomplete(
					"'.	geodir_autocompleter_ajax_url().'",
					{
						delay:500,
						minChars:1,
						matchSubset:1,
						matchContains:1,
						cacheLength:1,
						formatItem:formatItem,
						onItemSelect:onSelectItem,
						autoFill:false
					}
				);
			});
			
			'.$autosubmit.'
			
			'.$extra.'
	
		</script>';
	}
}

add_action('wp_footer', 'geodir_autocompleter_init_script');


function geodir_adminpage_autocompleter($tabs){
	
	$tabs['autocompleter_fields'] = array( 'label' =>__( 'Autocompleter', GEODIRAUTOCOMPLETER_TEXTDOMAIN ));
	
	return $tabs; 
}


function geodir_autocompleter_options_form($tab){
switch($tab){
		case 'autocompleter_fields':
			geodir_admin_fields( geodir_autocompleter_options() ); ?>
			<p class="submit">
        <input class="button-primary" type="submit" name="geodir_autocompleter_save"  value="<?php _e('Save changes', GEODIRAUTOCOMPLETER_TEXTDOMAIN);?>">
        </p>
			</div> <?php
		break;
	}
}


function geodir_autocompleter_adminmenu(){
	add_options_page('Autocompleter Options', 'Autocompleter', 8, __FILE__, 'geodir_autocompleter_options');
}


function geodir_autocompleter_options($arr = array())
{
	
	$arr[] = array( 'name' => __( 'Autocompleter for GeoDirectory', GEODIRAUTOCOMPLETER_TEXTDOMAIN ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'geodir_autocompleter_options' );
	
	
	$arr[] = array( 'name' => __( 'Autocompleter for GeoDirectory', GEODIRAUTOCOMPLETER_TEXTDOMAIN ), 'type' => 'sectionstart', 'id' => 'geodir_ajax_autocompleter_alert_options');
	
	$arr[] = array(  
			'name' => __( 'Enable autocompleter:', GEODIRAUTOCOMPLETER_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, the autocompleter is enabled.', GEODIRAUTOCOMPLETER_TEXTDOMAIN ),
			'id' 		=> 'geodir_enable_autocompleter',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '1'
		);
	
	$arr[] = array(  
			'name' => __( 'Autosubmit the form on select an option:', GEODIRAUTOCOMPLETER_TEXTDOMAIN ),
			'desc' 		=> __( 'If an option is selected, the search form automatically is triggered.', GEODIRAUTOCOMPLETER_TEXTDOMAIN ),
			'id' 		=> 'geodir_autocompleter_autosubmit',
			'type' 		=> 'checkbox',
			'css' 		=> '',
			'std' 		=> '1'
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_autocompleter_options');
	
	$arr = apply_filters('geodir_autocompleter_general_options' ,$arr );
	
	return $arr;
}


function geodir_autocompleter_from_submit_handler(){
	
	if(isset($_REQUEST['geodir_autocompleter_save']))
		geodir_update_options(geodir_autocompleter_options());
}


function geodir_autocompleter_taxonomies()
{

	$taxonomies_array = array();
	$args = array(
	'public'   => true,
	'_builtin' => false
	); 
	$output = 'names'; // or objects
	$operator = 'or'; // 'and' or 'or'
	$taxonomies = get_taxonomies( $args, $output, $operator ); 
	
	if(!empty($taxonomies)):
	 foreach($taxonomies as $term_que):
	 $taxonomies_array[$term_que] = $term_que;
	 endforeach;
	endif;
	
	return $taxonomies_array;

}

function geodir_autocompleter_post_types()
{
	$post_type_arr = array();
	
	$post_types = geodir_get_posttypes('object');
	
	foreach($post_types as $key => $post_types_obj)
	{
		$post_type_arr[$key] = $post_types_obj->labels->singular_name;
	}
	return 	$post_type_arr;
}


function geodir_autocompleter_script(){
	
	wp_enqueue_script( 'jquery' );
	wp_register_script('geodir-autocompleter-js',AUTOCOMPLETER_PLUGIN_URL.'/js/jquery.autocomplete.js');
	wp_enqueue_script( 'geodir-autocompleter-js' );

}
add_action('wp_enqueue_scripts', 'geodir_autocompleter_script');


function geodir_autocompleter_admin_script(){
	
	if(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'autocompleter_fields'){
		wp_enqueue_script( 'jquery' );
		wp_register_script('geodir-autocompleter-admin-js',AUTOCOMPLETER_PLUGIN_URL.'/js/autocomplete-admin.js');
		wp_enqueue_script( 'geodir-autocompleter-admin-js' );
	}

}
add_action('admin_enqueue_scripts', 'geodir_autocompleter_admin_script');


function geodir_autocompleter_style(){
	
	wp_register_style('autocompleter', AUTOCOMPLETER_PLUGIN_URL.'/css/autocompleter.css');
	wp_enqueue_style('autocompleter');
	
}

add_action( 'wp_enqueue_scripts', 'geodir_autocompleter_style', 10 );
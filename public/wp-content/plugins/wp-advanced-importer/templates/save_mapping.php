<?php
$exclude_array =  $exclude_keys = $get_keys = array();
$post_type     = '';
$get_keys      = get_option('exclude_keys'); 
$post_type     = $_POST['postdata'][0]['post_type'];
$exclude_array = $_POST['postdata'][0]['excludeArray'];
if(!empty($get_keys)) {
	foreach($get_keys as $post_key => $post_val) {
		if(trim($post_key) != $post_type) {      
			$get_keys[$post_type] = $exclude_array;
			$merge_keys = array_merge($get_keys,$get_keys[$post_type]);
			update_option('exclude_keys',$merge_keys);
		}
	}
}
else {  
	$exclude_keys[$post_type] = $exclude_array;
	update_option('exclude_keys',$exclude_keys);

}


print_r(json_encode(($get_keys))); die;



?>

<?php
$res = $user_array = $postmeta_terms =  array(); 
$post_count = $page_count = $custom_count  = $total_rec = $tag_name = ''; 
$file_name = $_POST['postdata'][0]['xml_name'];

$impCE = new WPAdvImporter_includes_helper();
$get_details = $impCE->get_xml_details($file_name);
$get_post_info  = $impCE->get_post_details($get_details);
if(isset($get_post_info))
	$post_count = count($get_post_info); $res['post'] = $post_count;
	$get_page_info  = $impCE->get_page_details($get_details);
if(isset($get_page_info)) 
	$page_count = count($get_page_info); $res['page'] = $page_count;
	$get_custom_info  = $impCE->get_custom_details($get_details);
if(isset($get_custom_info)) 
	$custom_count = count($get_custom_info); $res['custom'] = $custom_count;
	$get_author_info  = $impCE->get_author_details($get_details);
if(isset($get_author_info))
	$author_count = count($get_author_info); $res['author'] = $author_count;
	$res['postcount']  = count($get_details['posts']);
	$res['authorcount']  = count($get_details['authors']);
	$res['total'] = $res['postcount'] + $res['authorcount'];
	print_r(json_encode($res)); die;
	?>

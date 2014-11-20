<?php
class WXR_Handler {
  

 
public function postHandler($post) {
  $checktype = array(); 
  $mapping_section['wp_advanced_importer']['common'] = array();
  $mapping_section['wp_advanced_importer']['common']['type'] = array();
  if (isset ($post['type'][0] ) && $post['type'][0] == 'post' ) {
  $getOption = get_option('wp_advanced_importer');
  $mapping_section['wp_advanced_importer']['common']= $post;
  update_option('wp_advanced_importer',$mapping_section);
  $checktype = get_option('wp_advanced_importer');
  if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] ,TRUE)  &&  in_array('page' ,$checktype['wp_advanced_importer']['common']['type'] ,TRUE) && in_array('custompost',$checktype['wp_advanced_importer']['common']['type'],TRUE ) )
        {  $next = 'pageoption';     }
  else if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE) && in_array('page',$checktype['wp_advanced_importer']['common']['type'], TRUE)){    $next = 'pageoption';  }
  else if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE) && in_array('custompost' , $checktype['wp_advanced_importer']['common']['type'] , TRUE)) { $next = 'customoption'; }
  else if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE)) { $next = 'importoptions'; }

return $next;
}
}




public function pageHandler($page) {
          if(isset($page['type'][0]) && $page['type'][0] == 'page')
              {
              $mapping_section['wp_advanced_importer']['common']= $page;
              update_option('wp_advanced_importer',$mapping_section);
             $checktype = get_option('wp_advanced_importer');
             }
            $checktype = get_option('wp_advanced_importer');
     if(isset($checktype['wp_advanced_importer']['common']['type'][0])  && $checktype['wp_advanced_importer']['common']['type'][0] != 'page') {
        $mapping_section['wp_advance_importer']['common'] = array();  
        $checktype['wp_advance_importer']['common']['type'] = array();
        $getOption = get_option('wp_advanced_importer');
        $mapping_section['wp_advanced_importer']['post_xml'] = $page;
        $mapping_section['wp_advanced_importer'] = array_merge($mapping_section['wp_advanced_importer'],$getOption['wp_advanced_importer']);
        update_option('wp_advanced_importer',$mapping_section);
        $checktype=get_option('wp_advanced_importer');
 }
     if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE ) &&  in_array('page' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE) && in_array('custompost',$checktype['wp_advanced_importer']['common']['type'] ,TRUE))
         {  $next = 'customoption';  }
    if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE ) && in_array('page' ,$checktype['wp_advanced_importer']['common']['type'],TRUE) && in_array('custompost',$checktype['wp_advance_importer'],false))
         {  $next = 'importoptions'; }
    else if(in_array('page' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE) && in_array('custompost' , $checktype['wp_advanced_importer']['common']['type'] , TRUE))
         { $next = 'customoption'; }
    else if(in_array('page',$checktype['wp_advanced_importer']['common']['type'] , TRUE))
         { $next = 'importoptions'; }
    return $next;
}



public function custompostHandler($custompost) {
      $mapping_section['wp_advance_importer']['common'] = array();  
      $checktype['wp_advance_importer']['common']['type'] = array();
      if(isset($_POST['type'][0]) && $_POST['type'][0] == 'custompost') {
            $mapping_section['wp_advanced_importer']['common']= $custompost;
            update_option('wp_advanced_importer',$mapping_section);
            $checktype = get_option('wp_advanced_importer');
            }
             $checktype=get_option('wp_advanced_importer');
             $c=$checktype['wp_advanced_importer']['common']['type'];
 if(isset($c) && (isset($c[0]) && $c[0]== 'post') && (isset($c[1]) && $c[1] == 'custompost') )        {
        $getOption = get_option('wp_advanced_importer');  //third
        $mapping_section['wp_advanced_importer']['post_xml'] = $custompost;
        $mapping_section['wp_advanced_importer'] = array_merge($mapping_section['wp_advanced_importer'],$getOption['wp_advanced_importer']);
        update_option('wp_advanced_importer',$mapping_section);
        $checktype=get_option('wp_advanced_importer');
        }
 if(isset($c) && (isset($c[0]) && $c[0] == 'page') && (isset($c[1]) && $c[1] == 'custompost')  || (isset($c[1]) && $c[1] == 'page') && (isset($c[2]) && $c[2] == 'custompost'))         {
        $getOption = get_option('wp_advanced_importer');
        $mapping_section['wp_advanced_importer']['page_xml'] = $custompost;
        $mapping_section['wp_advanced_importer'] = array_merge($mapping_section['wp_advanced_importer'],$getOption['wp_advanced_importer']);
        update_option('wp_advanced_importer',$mapping_section);
        $checktype=get_option('wp_advanced_importer');
        }
       if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE ) &&  in_array('page' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE) && in_array('custompost',$checktype['wp_advanced_importer']['common']['type'] ,TRUE))
         {
                         $next = 'importoptions';
          }
  else if(in_array('page' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE ) && in_array('custompost' ,$checktype['wp_advanced_importer']['common']['type'] ,TRUE) )
                    {
                    $next = 'importoptions';
                     }
  else if(in_array('post' ,$checktype['wp_advanced_importer']['common']['type'] , TRUE) && in_array('custompost' , $checktype['wp_advanced_importer']['common']['type'] , TRUE))
                    {
                       $next = 'importoptions';
 }
   else if(in_array('custompost' ,$checktype['wp_advanced_importer']['common']['type'] ,TRUE))
                   {
                     $next = 'importoptions';
                    }
       
     return $next;
}



public function importOption($data) {
        $mapping_section['wp_advanced_importer'] = array();  //final
        $mapping_section['wp_advance_importer']['common'] = array();  
        $checktype['wp_advance_importer']['common']['type'] = array();
        $b= array(); 
        $checktype = get_option('wp_advanced_importer');
        $b=$checktype['wp_advanced_importer']['common']['type'];
   if(isset($b) && (isset($b[0]) && $b[0] == 'custompost')  || (isset($b[1]) && $b[1]= 'custompost')   || (isset($b[2]) && $b[2] == 'custompost') )
        {
        $getOption = get_option('wp_advanced_importer');
        $mapping_section['wp_advanced_importer']['custom_xml'] = $data;
        $mapping_section['wp_advanced_importer'] = array_merge($mapping_section['wp_advanced_importer'],$getOption['wp_advanced_importer']);
        update_option('wp_advanced_importer',$mapping_section);
        $checktype=get_option('wp_advanced_importer');
         }              
   if(isset($b) && $b[0] == 'post' )
        {
        $getOption = get_option('wp_advanced_importer');
        $mapping_section['wp_advanced_importer']['post_xml'] = $data;
        $mapping_section['wp_advanced_importer'] = array_merge($mapping_section['wp_advanced_importer'],$getOption['wp_advanced_importer']);
        update_option('wp_advanced_importer',$mapping_section);
        $checktype=get_option('wp_advanced_importer');
         }
   if(isset($b) && isset($b[0]) && $b[0] == 'page'  )
        {
        $getOption = get_option('wp_advanced_importer');
        $mapping_section['wp_advanced_importer']['page_xml'] = $data;
        $mapping_section['wp_advanced_importer'] = array_merge($mapping_section['wp_advanced_importer'],$getOption['wp_advanced_importer']);
        update_option('wp_advanced_importer',$mapping_section);
        $checktype=get_option('wp_advanced_importer');
         } 

      return $checktype;
}
} // class end

?>

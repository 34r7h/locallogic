<?php
global $xoouserultra;
$profile_customizing = array();
$profile_customizing = $xoouserultra->customizer->get_profile_customizing();

//template
$profile_templates = $xoouserultra->customizer->mTemplatesList;
?>

<div class="user-ultra-sect ">

 <h3><?php _e("User's Profile Customizer - Profile Templates ",'xoousers'); //widget ID: 1 ?></h3>
  
  <p><?php _e("Please select the Profile's Template.",'xoousers'); ?></p>
  
            
            
            <?php 
			
			
			$current_template = $xoouserultra->customizer->get_default_profile_template();
            
			foreach($profile_templates as $template )
			{
				
				//check if selected
				$checked="";
				if($current_template==$template["template_id"])
				{
					$checked = 'style';
				
				
				}
				
				
				
			?>

                <div class="uultra_template_block"> 
                
              
                
                <span class="uultra-template-active">
                
                  <?php if($checked!=""){?>
                
                 		<i class="fa fa-check-square-o fa-2"></i> 
                 
                  <?php }?> 
                 
                 
                 </span>
      
                 <h4><?php echo $template["title"] ?></h4>  
                 
                 <img src="<?php echo xoousers_url?>/admin/images/templates/<?php echo $template["snapshot"] ?>"  />
                 <div class="uultra_temp_desc">
                 
                 <p><?php echo $template["description"] ?></p>
                   
                 </div>
                 
                  <div class="uultra_temp_opt">
                  
                  <?php if($checked==""){?>
                                    
                      <p class="btn-find">                  
                         <a href="#" class="uultra-template-user-activate" data-rel="<?php echo $template["template_id"]?>"><?php echo __('Click to Activate', 'xoousers');?> </a>                
                      </p>
                  
                  <?php }else{?>
                  
                  	  <p class="uultra-btn-act">
                      
                      <i class="fa fa-check-square-o fa-2"></i>                  
                         <?php echo __('Active', 'xoousers');?>             
                      </p>
                  
                  
                  
                  
                  <?php }?> 
                  
                  
                 
                 </div>
                 
                </div>
            
            <?php }?> 
  
            
            
            

</div>
<div class="user-ultra-sect ">

 <h3><?php _e("User's Profile Customizer - General Customization ",'xoousers'); //widget ID: 1 ?></h3>
  
  <p><?php _e("Use this section to customize the main structure of the user's profile.",'xoousers'); ?></p>
  
     

            <div class="left_widget_customizer"> 
  
  <h4><?php _e("Main Profile Container ",'xoousers');  ?></h4>  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">

			
              
          <tr>
                <td width="50%"> <?php echo _e('Background Color','xoousers')?></td>
                <td width="50%"><input name="uultra_profile_bg_color" type="text" id="uultra_profile_bg_color" value="<?php echo $profile_customizing['uultra_profile_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              
               
              <tr>
                <td> <?php echo _e('Background Transparent','xoousers')?></td>
                <td><select name="uultra_profile_bg_color_transparent" id="uultra_profile_bg_color_transparent">
               <option value="yes" <?php if($profile_customizing['uultra_profile_bg_color_transparent']=="yes"){ echo 'selected="selected"';}?> >Yes</option>
               <option value="no" <?php if($profile_customizing['uultra_profile_bg_color_transparent']=="no"){ echo 'selected="selected"';}?>>No</option>
             </select>
               </td>
              </tr>
                          
            </table>
            
            
            

</div>

            <div class="left_widget_customizer"> 
  
  <h4><?php _e("Inferior Profile Container ",'xoousers');  ?></h4>  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">

			
              
          <tr>
                <td width="50%"> <?php echo _e('Background Color','xoousers')?></td>
                <td width="50%"><input name="uultra_profile_inferior_bg_color" type="text" id="uultra_profile_inferior_bg_color" value="<?php echo $profile_customizing['uultra_profile_inferior_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              
               
              <tr>
                <td> <?php echo _e('Background Transparent','xoousers')?></td>
                <td><select name="uultra_profile_inferior_bg_color_transparent" id="uultra_profile_inferior_bg_color_transparent">
                <option value="yes" <?php if($profile_customizing['uultra_profile_inferior_bg_color_transparent']=="yes"){ echo 'selected="selected"';}?> >Yes</option>
               <option value="no" <?php if($profile_customizing['uultra_profile_inferior_bg_color_transparent']=="no"){ echo 'selected="selected"';}?>>No</option>
             </select>
               </td>
              </tr>
                          
            </table>
            
            
            

</div>

 <div class="left_widget_customizer"> 
  
  <h4><?php _e("Image Background Color",'xoousers');  ?></h4>  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">

			
              
          <tr>
                <td width="50%"> <?php echo _e('Background Color','xoousers')?></td>
                <td width="50%"><input name="uultra_profile_image_bg_color" type="text" id="uultra_profile_image_bg_color" value="<?php echo $profile_customizing['uultra_profile_image_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
         </tr>
         
         <tr>
                <td  colspan="2"> <?php echo _e("If you set a color the default image won't be displayed.",'xoousers')?></td>
                
         </tr>
              
               
                                       
            </table>
            
            
            

</div>

           
<p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-profile-customizer-save-style"  data-widget="1" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-prof-custom-basic-message"></span>

</p>
</div>
<div class="user-ultra-sect ">

<?php


//get data widget 1
$widget = array();
$widget = $xoouserultra->customizer->get_widget_appearance(1);

// print_r($widget );

?>
  <h3><?php _e("User's Profile Customizer - Widgets ",'xoousers'); //widget ID: 1 ?></h3>
  
  <p><?php _e("Use this section to customize the look & feel of the user's profile. All the widgets can be customized independently.",'xoousers'); ?></p>
  
  
  <div class="left_widget_customizer">
  
  
  <h4><?php _e("Basic Information Widget  ",'xoousers'); //widget ID: 1 ?></h4>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">

			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_1" type="text" id="widget_title_1" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
              
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_1" type="text" id="widget_header_bg_color_1" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              
               
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_1" type="text" id="widget_bg_color_1" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_1" type="text" id="widget_header_text_color_1" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_1" type="text" id="widget_text_color_1" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
                          
            </table>
            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="1" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-1"></span>

</p>

</div>

 <div class="left_widget_customizer">
 <h4><?php _e("My Friends Widget  ",'xoousers'); //widget ID: 2	 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(2);

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">



			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_2" type="text" id="widget_title_2" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
         
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_2" type="text" id="widget_header_bg_color_2" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_2" type="text" id="widget_bg_color_2" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_2" type="text" id="widget_header_text_color_2" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_2" type="text" id="widget_text_color_2" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="2" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-2"></span>

</p>

  </div>
  
  
   <div class="left_widget_customizer">
 <h4><?php _e("My Photos Widget  ",'xoousers'); //widget ID: 3	 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(3);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_3" type="text" id="widget_title_3" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_3" type="text" id="widget_header_bg_color_3" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_3" type="text" id="widget_bg_color_3" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_3" type="text" id="widget_header_text_color_3" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_3" type="text" id="widget_text_color_3" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
             
            
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="3" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-3"></span>

</p>

  </div>
  
  
   <div class="left_widget_customizer">
 <h4><?php _e("My Galleries  ",'xoousers'); //widget ID: 4	 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(4);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_4" type="text" id="widget_title_4" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_4" type="text" id="widget_header_bg_color_4" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_4" type="text" id="widget_bg_color_4" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_4" type="text" id="widget_header_text_color_4" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_4" type="text" id="widget_text_color_4" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
                         
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="4" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-4"></span>

</p>

  </div>
  
  
   <div class="left_widget_customizer">
 <h4><?php _e("My Latest Posts  ",'xoousers'); //widget ID: 5 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(5);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_5" type="text" id="widget_title_5" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_5" type="text" id="widget_header_bg_color_5" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_5" type="text" id="widget_bg_color_5" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_5" type="text" id="widget_header_text_color_5" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_5" type="text" id="widget_text_color_5" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
             
                         
            </table>  
            
            <h4><?php echo _e('Post Types','xoousers')?></h4>
            
            <table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> 	<?php echo _e('Display Post Types','xoousers')?></td>
                <td width="50%"><?php echo $xoouserultra->publisher->get_all_post_types();?>
         </td>
         </tr>  
         
           </table>         
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="5" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-5"></span>

</p>

  </div>
  


   <div class="left_widget_customizer">
 <h4><?php _e("Followers ",'xoousers'); //widget ID: 6 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(6);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_6" type="text" id="widget_title_6" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_6" type="text" id="widget_header_bg_color_6" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_6" type="text" id="widget_bg_color_6" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_6" type="text" id="widget_header_text_color_6" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_6" type="text" id="widget_text_color_6" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
             
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="6" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-6"></span>

</p>

  </div>
  
   <div class="left_widget_customizer">
 <h4><?php _e("My Latest Videos ",'xoousers'); //widget ID: 8 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(8);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">

			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_8" type="text" id="widget_title_8" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>

          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_8" type="text" id="widget_header_bg_color_8" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_8" type="text" id="widget_bg_color_8" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_8" type="text" id="widget_header_text_color_8" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_8" type="text" id="widget_text_color_8" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              
            
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="8" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-8"></span>

</p>

  </div>
  
  
  
     <div class="left_widget_customizer">
 <h4><?php _e("My Wall",'xoousers'); //widget ID: 9 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(9);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_9" type="text" id="widget_title_9" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_9" type="text" id="widget_header_bg_color_9" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_9" type="text" id="widget_bg_color_9" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_9" type="text" id="widget_header_text_color_9" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_9" type="text" id="widget_text_color_9" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
            
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="9" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-9"></span>

</p>

  </div>
  
  
   <div class="left_widget_customizer">
 <h4><?php _e("About / Bio",'xoousers'); //widget ID: 10 ?></h4>
 
 <?php
 $widget = $xoouserultra->customizer->get_widget_appearance(10);
 
 //print_r($widget );

 ?>
  
  
<table width="100%" border="0" cellspacing="0" cellpadding="0">


			<tr>
                <td width="50%"> <?php echo _e("Widget's Title ",'xoousers')?></td>
                <td width="50%"><input name="widget_title_10" type="text" id="widget_title_10" value="<?php echo $widget['widget_title'];?>"  /> 
         </td>
         </tr>
          <tr>
                <td width="50%"> <?php echo _e('Header Background Color','xoousers')?></td>
                <td width="50%"><input name="widget_header_bg_color_10" type="text" id="widget_header_bg_color_10" value="<?php echo $widget['widget_header_bg_color'];?>" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> <?php echo _e('Background Color','xoousers')?></td>
                <td><input name="widget_bg_color_10" type="text" id="widget_bg_color_10" value="<?php echo $widget['widget_bg_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
               <tr>
                <td> <?php echo _e('Header Text Color','xoousers')?></td>
                <td><input name="widget_header_text_color_10" type="text" id="widget_header_text_color_10" value="<?php echo $widget['widget_header_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
              <tr>
                <td> <?php echo _e('Widget Text Color','xoousers')?></td>
                <td><input name="widget_text_color_10" type="text" id="widget_text_color_10" value="<?php echo $widget['widget_text_color'];?>" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
              
            
            </table>            
            
            <p class="submit">
	<input type="button" name="submit"  class="button button-primary uultra-widget-customizer-save"  data-widget="10" value="<?php _e('Save Changes','xoousers'); ?>"  /> <span id="uultra-widget-update-message-10"></span>

</p>

  </div>
</div>


<div class="user-ultra-sect "  style=" display:">

 <h3><?php _e("Modules Activation & Deactivation ",'xoousers');  ?></h3>
  
  <p><?php _e("By default all the modules are active on Users Ultra PRO. You can use this section to activate/deactivate user's functionalities.",'xoousers'); ?></p>
  
   <div class="uultra_modules_acvitation_block_left">
   
   <h4><strong><?php _e("Deactivate the following checked modules:",'xoousers'); ?></strong></h4>
   
   		 <ul class="" id="uultra-user-mod-list">
  
   		</ul>
        
        <p class="submit">
	<input type="button" name="submit"  class="button button-primary " id="uultradmin-save-modules-setting"  data-widget="1" value="<?php _e('Save Changes','xoousers'); ?>"  />&nbsp;<span id="loading-animation-users-module" class="loading-animation-ajax"> <img src="<?php echo xoousers_url?>admin/images/loaderB16.gif" width="16" height="16" /> &nbsp; <?php _e('Saving Changes ...','xoousers'); ?></span> 

</p>
  
   </div>
  
   <div class="uultra_modules_acvitation_block_right">
   
   <h4><strong><?php _e("User's navigator, drag&drop available:",'xoousers'); ?></strong></h4>
   
     
   		 <ul class="" id="uultra-user-menu-option-list">
  
   		</ul>
        
        
  
  </div>
  
     

           
            
           

</div>

<form method="post" action="">
<input type="hidden" name="update_settings" />

<div class="user-ultra-sect ">
  <h3><?php _e('Customizer','xoousers'); ?></h3>
  
  <p><?php _e('Use this section to add custom CSS styles.','xoousers'); ?></p>
  
   <table class="form-table">
<?php 

$this->create_plugin_setting(
        'textarea',
        'xoousersultra_custom_css',
        __('Custom CSS Style','xoousers'),array(),
        __('You can write some custom CSS style coding here','xoousers'),
        __('You can write some custom CSS style coding here','xoousers')
);

?>
  
 
 </table>

  
</div>




<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','xoousers'); ?>"  />

</p>

</form>

<script type="text/javascript">
uultra_reload_user_modules();
uultra_reload_user_menu_customizer();
</script>
<?php
global $uultra_form, $xoouserultra;

$forms = $uultra_form->get_all();

?>
<div class="user-ultra-sect ">
        
      
<form action="" method="post" id="uultra-userslist">
          <input type="hidden" name="add-form" value="add-form" />
        
        <div class="user-ultra-success uultra-notification"><?php _e('Success ','xoousers'); ?></div>
        
         <p><?php _e('This module gives you the capability to setup multiple or separate registration forms. For instance, If you want to have two separate forms or more e.g. Clients, Partners, Sellers, etc. This tool helps you create multiple forms with different fields.','xoousers'); ?></p>
         
        
    <h3><?php _e('Add New Form ','xoousers'); ?></h3>
    
   
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="15%"><?php _e('Name: ','xoousers'); ?></td>
             <td width="85%"><input type="text" id="form_name"  name="form_name"  /></td>
           </tr>
           
                     
            <tr>
             <td width="15%"><?php _e('Role to Assign: ','xoousers'); ?></td>
             <td width="85%"> <?php echo $xoouserultra->role->get_package_roles($selected_package);?> - <?php _e("Subscriber Role will be assigned automatically if you don't select a role ",'xoousers'); ?></td>
           </tr>
           
          
           
          
          </table>
          
           <p>
           <input name="submit" type="submit"  class="button-primary" value="<?php _e('Confirm','xoousers'); ?>"/>
          
    </p>
          
   
        </form>
        
                 <?php
			
			
				
				if (!empty($forms)){
				
				
				?>
       
           <table width="100%" class="wp-list-table widefat fixed posts table-generic">
            <thead>
                <tr>
                    <th width="12%" style="color:# 333"><?php _e('Unique Identifier', 'xoousers'); ?></th>
                    <th width="21%"><?php _e('Name', 'xoousers'); ?></th>
                    <th width="19%"><?php _e('Role', 'xoousers'); ?></th>
                    <th width="13%"><?php _e('Shortcode', 'xoousers'); ?></th>
                    <th width="20%"><?php _e('Actions', 'xoousers'); ?></th>
                </tr>
            </thead>
            
            <tbody>
            
            <?php 
			
				foreach ( $forms as $key => $form )
				{
					
			?>
              

                <tr  id="uu-edit-form-row-<?php echo $key; ?>">
                    <td><?php echo $key; ?></td>
                    <td  id="uu-edit-form-row-name-<?php echo $key; ?>"><?php echo $form['name']; ?></td>
                    <td><?php echo $form['role']; ?></td>
                    <td><?php echo $uultra_form->get_copy_paste_shortocde($key);?></td>
                   <td> <a href="#" class="button uultra-form-del"  id="" data-form="<?php echo $key; ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Delete','xoousers'); ?>
                   </a>  <a href="#" class="button-primary button-secondary uultra-form-edit"  id="" data-form="<?php echo $key ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Edit','xoousers'); ?>
</a> </td>
                </tr>
                
                
                <tr>
                
                 <td colspan="5" ><div id='uu-edit-form-box-<?php echo $key; ?>'></div> </td>
                
                </tr>
                <?php
					}
					
					} else {
			?>
			<p><?php _e('There are no custom forms yet.','xoousers'); ?></p>
			<?php	} ?>

            </tbody>
        </table>
        
        
           <script type="text/javascript">  
		
		      var custom__del_confirmation ="<?php _e('Are you totally sure that you want to delete this form?','xoousers'); ?>";
			  
			  
		
		 </script>
        
             

</div>
<?php
global $uultra_group;

$groups = $uultra_group->get_all();
?>
<div class="user-ultra-sect ">
        
      
<form action="" method="post" id="uultra-userslist">
          <input type="hidden" name="add-group" value="add-group" />
        
        <div class="user-ultra-success uultra-notification"><?php _e('Success ','xoousers'); ?></div>
        
    <h3><?php _e('Add New Group ','xoousers'); ?></h3>
         
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="6%"><?php _e('Name: ','xoousers'); ?></td>
             <td width="94%"><input type="text" id="group_name"  name="group_name"  /></td>
           </tr>
           
            <tr>
             <td width="6%"><?php _e('Description: ','xoousers'); ?></td>
             <td width="94%">
                            <textarea name="group_desc" id="group_desc" cols="45" rows="5"></textarea></td>
           </tr>
          </table>
          
           <p>
           <input name="submit" type="submit"  class="button-primary" value="<?php _e('Confirm','xoousers'); ?>"/>
          
    </p>
          
   
        </form>
        
                 <?php
			
			
				
				if (!empty($groups)){
				
				
				?>
       
           <table width="100%" class="wp-list-table widefat fixed posts table-generic">
            <thead>
                <tr>
                    <th width="4%" style="color:# 333"><?php _e('#', 'xoousers'); ?></th>
                    <th width="12%"><?php _e('Name', 'xoousers'); ?></th>
                    <th width="24%">&nbsp;</th>
                     <th width="19%">&nbsp;</th>
                    <th width="13%">&nbsp;</th>
                    <th width="8%">&nbsp;</th>
                    <th width="20%"><?php _e('Actions', 'xoousers'); ?></th>
                </tr>
            </thead>
            
            <tbody>
            
            <?php 
			
				foreach ( $groups as $c )
				{
					
			?>
              

                <tr  id="uu-edit-cate-row-<?php echo $c->group_id; ?>">
                    <td><?php echo $c->photo_cat_id; ?></td>
                    <td  id="uu-edit-cate-row-name-<?php echo $c->group_id; ?>"><?php echo $c->group_name; ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                     <td>&nbsp;</td>
                   <td> <a href="#" class="button button-secondary user-ultra-btn-red uultra-photocat-del"  id="" data-gal="<?php echo $c->group_id; ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Delete','xoousers'); ?>
                   </a>  <a href="#" class="button button-secondary uultra-photocat-edit"  id="" data-gal="<?php echo $c->group_id; ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Edit','xoousers'); ?>
</a> </td>
                </tr>
                
                
                <tr>
                
                 <td colspan="7" ><div id='uu-edit-cate-box-<?php echo $c->group_id; ?>'></div> </td>
                
                </tr>
                <?php
					}
					
					} else {
			?>
			<p><?php _e('There are no groups yet.','xoousers'); ?></p>
			<?php	} ?>

            </tbody>
        </table>
        
             

</div>
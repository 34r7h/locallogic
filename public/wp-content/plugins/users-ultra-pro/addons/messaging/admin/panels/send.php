<?php
global $uultra_messaging_admin, $xoouserultra;

?>
<div class="user-ultra-sect ">

<h2><?php _e('Send New Message ','xoousers'); ?></h2>
        
      
<form action="" method="post" id="uultra-userslist">
<input type="hidden" name="uultra-send-messages-to-users"  value="uultra-send-messages-to-users" />
          
        
       <p><?php _e('Subject:','xoousers'); ?> (*)</p>
        <p><input type="text" name="uu_subject" value="" /> 
        </p>
    <p><?php _e('Message:','xoousers'); ?> (*)</p>
       <p>
         
         <textarea name="uu_message" id="uu_message" cols="70" rows="8"></textarea>
       </p>
       
       <small><?php _e('Fields with * are required','xoousers'); ?></small>
       
       <div class="uultra-receivers ">
       
       
           <div class="user-ultra-int-mess-col ">
           
        <p><strong> <?php _e('User(s):','xoousers'); ?></strong></p>
        
        <p><select name="listo_to_users[]" id="listo_to_users[]" multiple="multiple" class="uultra-user-list" style="width:97%; height:300px;" data-placeholder="<?php _e('Choose...','xoousers'); ?>">
				<?php
				$users=$uultra_messaging_admin->uultra_get_users();
				foreach($users as $user) {
				?>
				<option value="<?php echo $user->ID; ?>"><?php  echo $xoouserultra->userpanel->get_user_meta_custom( $user->ID, 'display_name'); if ($user->user_email) echo ' ('. $user->user_email . ')'; ?></option>
				<?php } ?>
			</select>
            </p>
			<p><?php _e('You can send messages to either a specific member or multiple members at once by choosing them here.','xoousers'); ?></p>
        </div>
         <div class="user-ultra-int-mess-col ">
            
           <p><strong> <?php _e('Roles:','xoousers'); ?></strong></p>
           <p><?php echo  $uultra_messaging_admin->get_all_roles()?></p>
        </div>
       
       
       </div>
       
      <p> <input name="submit" type="submit"  class="button-primary" value="<?php _e('SEND MESSAGE','xoousers'); ?>"/></p>
       
       
             
</form>
</div>
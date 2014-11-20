<?php
global $uultra_ticket_admin, $xoouserultra;

?>
<div class="user-ultra-sect ">

<h2><?php _e('Create New Ticket ','xoousers'); ?></h2>
        
      
<form action="" method="post" id="uultra-userslist">
<input type="hidden" name="uultra-send-messages-to-users"  value="uultra-send-messages-to-users" />


<div class="uultra-ticket">
          
   
   <div class="user-col-sep">     
       <h4><?php _e('Subject:','xoousers'); ?> (*)</h4>
        <p><input type="text" name="uu_subject" value="" /> 
        </p>
    <h4><?php _e('Message:','xoousers'); ?> (*)</h4>
       <p>
         
        <?php
				$editor_id = 'uu_message';				
				$editor_settings = array('media_buttons' => false , 'textarea_rows' => 15); 
				
				$content = $post->post_content;     
                
                 wp_editor( $content, $editor_id , $editor_settings);
                
                ?>
       </p>
       
       <small><?php _e('Fields with * are required','xoousers'); ?></small>
       
       
       
    </div>       
     <div class="user-col-sep_right">      
     
     <h4><?php _e('Select User:','xoousers'); ?> (*)</h4>   
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input name="uultra_uname" value="" class="uultra-user-searcher" id="uultra_uname"  tabindex="1" placeholder="<?php _e('find your user here ...','xoousers'); ?>"/><input name="itemCode" value="" class="tInput" id="itemCode" tabindex="2"  readonly="readonly" type="hidden"/></td>
    <td>&nbsp;</td>
  </tr>
 
</table>


<h4><?php _e('Assigned To:','xoousers'); ?> (*)</h4>   
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input name="uultra_uname" value="" class="uultra-user-searcher" id="uultra_uname"  tabindex="1" placeholder="<?php _e('find your user here ...','xoousers'); ?>"/><input name="itemCode" value="" class="tInput" id="itemCode" tabindex="2"  readonly="readonly" type="hidden"/></td>
    <td>&nbsp;</td>
  </tr>
 
</table>

<h4><?php _e('Select Category:','xoousers'); ?> (*)</h4>   
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><?php echo $xoouserultra->ticket->get_categories_box();?></td>
    <td>&nbsp;</td>
  </tr>
 
</table>

<h4><?php _e('Select Department:','xoousers'); ?> (*)</h4>   
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><?php echo $xoouserultra->ticket->get_departments_box();?></td>
    <td>&nbsp;</td>
  </tr>
 
</table>

<h4><?php _e('Select Status:','xoousers'); ?> (*)</h4>   
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><?php echo $uultra_ticket_admin->get_status_box();?>
      
     </td>
    <td>&nbsp;</td>
  </tr>
 
</table>


 <p> <input name="submit" type="submit"  class="button-primary" value="<?php _e('CREATE TICKET','xoousers'); ?>"/></p>
			
       
                
       
       </div>
       
     
       
  </div>     
             
</form>
</div>
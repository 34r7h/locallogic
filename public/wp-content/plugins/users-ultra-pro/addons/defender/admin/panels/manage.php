<?php
global $uultra_ip_defender;

$ips = $uultra_ip_defender->get_all();
?>
<div class="user-ultra-sect ">
        
      
<form action="" method="post" id="uultra-userslist">
          <input type="hidden" name="add-ipnumber" value="add-ipnumber" />
        
        <div class="user-ultra-success uultra-notification"><?php _e('Success ','xoousers'); ?></div>
        
    <h3> <?php _e('Add New IP ','xoousers'); ?></h3>
     <p> <?php _e('This module will help you to protect your WordPress against spam attacks. You can use this simple yet useful module to block ip address. Simple way to stop fake account spam! :) ','xoousers'); ?><p>
         
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="11%"><?php _e('IP Number: ','xoousers'); ?></td> 
             <td width="89%"><input type="text" id="ip_number"  name="ip_number"  /></td>
           </tr>
          </table>
          
           <p>
           <input name="submit" type="submit"  class="button-primary" value="<?php _e('Confirm','xoousers'); ?>"/>
          
    </p>
          
   
        </form>
        
                 <?php
			
			
				
				if (!empty($ips)){
				
				
				?>
                
                 <h4> <?php _e(' Black List (Rejected IP List) ','xoousers'); ?></h4>
                
               
       
           <table width="100%" class="wp-list-table widefat fixed posts table-generic">
            <thead>
                <tr>
                    <th width="12%"><?php _e('IP Number', 'xoousers'); ?></th>
                    <th width="24%">&nbsp;</th>
                     <th width="19%">&nbsp;</th>
                    <th width="13%">&nbsp;</th>
                    <th width="8%">&nbsp;</th>
                    <th width="20%"><?php _e('Actions', 'xoousers'); ?></th>
                </tr>
            </thead>
            
            <tbody>
            
            <?php 
			
				foreach ( $ips as $c )
				{
					
			?>
              

                <tr  id="uu-edit-ipnumber-row-<?php echo $c->ip_id; ?>">
                    <td  id="uu-edit-ipnumber-row-name-<?php echo $c->ip_id; ?>"><?php echo $c->ip_number; ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                     <td>&nbsp;</td>
                   <td> <a href="#" class="button button-secondary user-ultra-btn-red uultra-ipnumber-del"  id="" data-gal="<?php echo $c->ip_id; ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Delete','xoousers'); ?>
                   </a>   </td>
                </tr>
                
                
               
                <?php
					}
					
					} else {
			?>
			<p><?php _e('There are no blocked IP numbers yet.','xoousers'); ?></p>
			<?php	} ?>
        </table>
        
             

</div>
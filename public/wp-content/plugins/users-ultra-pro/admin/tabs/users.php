<?php
global $xoouserultra;
$currency_symbol =  $xoouserultra->get_option('paid_membership_symbol');

$howmany = "20";
$year = "";
$month = "";
$day = "";
$status = "";

if(isset($_GET["howmany"]))
{
	$howmany = $_GET["howmany"];		
}

if(isset($_GET["status"]))
{
	$status = $_GET["status"];		
}

if(isset($_GET["month"]))
{
	$month = $_GET["month"];		
}

if(isset($_GET["day"]))
{
	$day = $_GET["day"];		
}

if(isset($_GET["year"]))
{
	$year = $_GET["year"];		
}

if(isset($_GET["uultra_combined_search"]))
{
	$uultra_combined_search = $_GET["uultra_combined_search"];		
}

if(isset($_GET["uultra_meta"]))
{
	$uultra_meta = $_GET["uultra_meta"];		
}

if(isset($_GET["uultra_membership"]))
{
	$uultra_membership = $_GET["uultra_membership"];		
}



$relation = "AND";
$args= array('per_page' => $howmany, 'keyword' => $uultra_combined_search , 'uultra_meta' => $uultra_meta, 'uultra_membership' => $uultra_membership,  'relation' => $relation, 'role' => $role, 'status' => $status, 'sortby' => 'ID', 'order' => 'DESC');
$users = $xoouserultra->userpanel->get_users_filtered($args);

if(isset($_GET["uultra_export_to_csv"]) && $_GET["uultra_export_to_csv"]==1)
{
	$xoouserultra->userpanel->get_downloadable_csv($users);
			
}



		
?>

        
        <div class="user-ultra-sect ">
        
        <form action="" method="get" id="uultra-userslist">
         <input type="hidden" name="page" value="userultra" />
          <input type="hidden" name="tab" value="users" />
        
        <div class="user-ultra-success uultra-notification"><?php _e('Success ','xoousers'); ?></div>
        
        <div class="user-ultra-sect-second user-ultra-rounded" >
        
         <?php echo $xoouserultra->userpanel->get_downloadable_csv_check();?>
        
        <h3><?php _e('Search Users','xoousers'); ?></h3>
         
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="16%"><?php _e('Keywords:','xoousers'); ?></td>
             <td width="18%"><?php _e('Membership:','xoousers'); ?></td>
             <td width="18%"><?php _e('Status:','xoousers'); ?></td>
             <td width="19%"><?php _e('Custom Meta:','xoousers'); ?></td>
             <td width="29%">&nbsp;</td>
             <td width="6%">&nbsp;</td>
           </tr>
           <tr>
             <td><input type="text" name="uultra_combined_search" id="uultra_combined_search" placeholder="<?php _e('write some text here ...','xoousers'); ?>" style="width:100px"/></td>
             <td><select name="uultra_membership" id="uultra_membership">
               <option value="">
                 <?php _e('All Membership','xoousers'); ?>
               </option>
               <?php
					
					
					$allpackages = $xoouserultra->paypal->get_packages_private();
					foreach($allpackages as $package ) 
					{
					 ?>
               				<option value="<?php echo $package->package_id; ?>"> <?php echo $package->package_name; ?> </option>
             		  <?php
					    }
					
					?>
             </select></td>
             <td> <select name="status" id="status">
               <option value="" <?php if($status =="" ) echo 'selected="selected"';?>><?php _e('All','xoousers'); ?></option>
               <option value="active" <?php if("active"==$status ) echo 'selected="selected"';?>><?php _e('Active','xoousers'); ?></option>
               <option value="pending" <?php if("pending"==$status ) echo 'selected="selected"';?>><?php _e('Pending Confirmation','xoousers'); ?></option>
               <option value="pending_admin" <?php if("pending_admin"==$status ) echo 'selected="selected"';?>> <?php _e('Pending Admin','xoousers'); ?></option>
               <option value="pending_payment" <?php if("pending_payment"==$status ) echo 'selected="selected"';?>> <?php _e('Pending Payment','xoousers'); ?></option>
               
          </select> </td>
             <td><select name="uultra_meta" id="uultra_meta">
				<option value="">
					<?php _e('Choose a Meta Key','xoousers'); ?>
				</option>
				
				
					<?php
					$current_user = wp_get_current_user();
					if( $all_meta_for_user = get_user_meta( $current_user->ID ) ) {

					    ksort($all_meta_for_user);

					    foreach($all_meta_for_user as $user_meta => $array) {
					        ?>
					<option value="<?php echo $user_meta; ?>">
						<?php echo $user_meta; ?>
					</option>
					<?php
					    }
					}
					?>
				
		</select></td>
             <td><select name="howmany" id="howmany">
              <option value="20" <?php if(20==$howmany ||$howmany =="" ) echo 'selected="selected"';?>>20</option>
                <option value="40" <?php if(40==$howmany ) echo 'selected="selected"';?>>40</option>
                 <option value="50" <?php if(50==$howmany ) echo 'selected="selected"';?>>50</option>
                  <option value="80" <?php if(80==$howmany ) echo 'selected="selected"';?>>80</option>
                   <option value="100" <?php if(100==$howmany ) echo 'selected="selected"';?>>100</option>
               
          </select>  <button><?php _e('Filter','xoousers'); ?></button>
          <input type="checkbox" name="uultra_export_to_csv" id="uultra_export_to_csv" value="1" /> Export to CSV</td>
             <td>&nbsp;</td>
           </tr>
          </table>
         
                
       
        </div>
        
                
         </form>
         
          <div class="usersultra-paginate top_display"><h3><?php echo $users['total']; ?> <?php _e(' Users ','xoousers'); ?></h3></div>
         
                
         <?php
			
			
				
				if (!empty($users['users'])){
				
				
				?>
                
            
                
          <?php if (isset($users['paginate'])) { ?>
        <div class="usersultra-paginate top_display"><?php echo $users['paginate']; ?></div>
		
		<?php } ?>
        
       
       
           <table width="100%" class="wp-list-table widefat fixed posts table-generic">
            <thead>
                <tr>
                    <th width="6%"><?php _e('ID', 'xoousers'); ?></th>
                    <th width="7%"><?php _e('Avatar', 'xoousers'); ?></th>
                    <th width="11%"><?php _e('IP', 'xoousers'); ?></th>
                     <th width="11%"><?php _e('Role', 'xoousers'); ?></th>
                    <th width="18%"><?php _e('Nick', 'xoousers'); ?></th>
                    <th width="16%"><?php _e('Name', 'xoousers'); ?></th>
                     <th width="15%"><?php _e('User Email', 'xoousers'); ?></th>
                     <th width="9%"><?php _e('Status', 'xoousers'); ?></th>
                    <th width="9%"><?php _e('Registered', 'xoousers'); ?></th>
                    <th width="9%"><?php _e('Actions', 'xoousers'); ?></th>
                </tr>
            </thead>
            
            <tbody>
            
            <?php 
			foreach($users['users'] as $user) {
				
				$user_id = $user->ID;
				
				$u_status =  $xoouserultra->userpanel->get_user_meta_custom($user_id, 'usersultra_account_status');
				$u_ip =  $xoouserultra->userpanel->get_user_meta_custom($user_id, 'uultra_user_registered_ip');
				$u_role =  $xoouserultra->userpanel->get_all_user_roles($user_id);
				
				
									
			?>
                <tr>
                    <td><?php echo $user_id; ?></td>
                    <td><?php echo $xoouserultra->userpanel->get_user_pic( $user_id, 50, 'avatar', null, null)  ?></td>
                     <td><?php echo $u_ip?></td>
                     <td><?php echo $u_role?></td>
                    <td><?php echo $user->user_login?></td>
                     <td><?php echo $user->first_name?></td>
                    <td><?php echo $user->user_email; ?> </td>
                    <td><?php echo  $u_status?></td>
                    <td><?php echo $user->user_registered; ?></td>
                   <td><a class="uultra-btn-deletemessage  uultra-user-see-details" href="#" id="" title="<?php _e('See Data','xoousers'); ?>" user-id="<?php echo  $user_id?>" ><span><i class="fa fa-eye fa-lg"></i></span></a> <a class="uultra-btn-deletemessage  uultra-user-edit-package" href="#" id="" title="<?php _e('Edit User','xoousers'); ?>" user-id="<?php echo  $user_id?>" ><span><i class="fa fa-edit fa-lg"></i></span></a><a class="uultra-btn-deletemessage  uultra-private-user-deletion" href="#" id="" user-id="<?php echo  $user_id?>" ><span><i class="fa fa-times fa-lg"></i></span></a></td>
                </tr>
                
                <tr>
                
                <td colspan="10">
                <div class="uultra-edit-user-admin" id="uu-edit-user-box-<?php echo $user_id?>"></div> </td>
                </tr>
                <?php
					}
					
					} else {
			?>
			<p><?php _e('There are no users yet.','xoousers'); ?></p>
			<?php	} ?>

            </tbody>
        </table>
        


        
        
        
        
        </div>
        
<script>
var message_users_module_delete= "<?php echo _e('Are you really sure that you want to delete this user?','xoousers')?>"
</script>

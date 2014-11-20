<?php
global $uultra_maintenance;

//get amount of different users id from meta field table

$total_from_meta = $uultra_maintenance->get_all_from_meta();

//get amount of users from the users table
$total_from_users = $uultra_maintenance->get_all_from_users();

if($total_from_meta==$total_from_users)
{
	$result = 'ok';
	

}else{
	
	$result = 'nook';


}

?>
<div class="user-ultra-sect ">
        
      
<form action="" method="post" id="uultra-userslist">
        
        <div class="user-ultra-success uultra-notification"><?php _e('Success ','xoousers'); ?></div>
        
    <h3> <?php _e('WP User Meta Fields Cleaning ','xoousers'); ?></h3>
     <p> <?php _e("This module helps you to remove useless user meta from the wp_usermeta
table. It's useful to fix users count issues. This fixes the wrong amount of users displayed in the Users tab as well.",'xoousers'); ?><p>

<p> <?php _e("<strong>IMPORTANT:</strong> This module won't touch your Users Table but the User's Meta Table Only.",'xoousers'); ?><p>

 <h4> <?php _e('Current Integrity Status: ','xoousers'); ?></h4>

<p> <?php _e("USERS FOUND IN USERS TABLE: ",'xoousers'); ?> <strong> <?php echo $total_from_users;?></strong> <p>       

<p> <?php _e("DISTINCT USERS FOUND IN META TABLE: ",'xoousers'); ?><strong>   <?php echo $total_from_meta;?></strong> <p>
          
                     
           <?php if($result =='ok'){?>
           <strong> <?php _e("The tables are synchronized. No action is required. ",'xoousers'); ?></strong> 
           
           
           
            <?php }else{?>
            
                       
             <div class="uuultra-top-noti-admin "><div class="user-ultra-warning"><?php echo _e("We recommend you optimize the tables by clicking on the button below.", 'xoousers')?></div></div>
             
             <p>
           <input name="submit" type="button"  class="button-primary uultra-do-integrity-checks" value="<?php _e('SYNC NOW','xoousers'); ?>"/>
          
    </p>
    
    <div id="uultra-integritycheck-results" class="uultra-integritycheck-results-style"></div>
            
             <?php }?>
           
           
                
          
           
          
   
        </form>
        
         <script type="text/javascript">
		  
		 var mant_confirmation = "<?php _e('Are you totally sure? ','xoousers'); ?>";
		 
		 </script>
                     

</div>
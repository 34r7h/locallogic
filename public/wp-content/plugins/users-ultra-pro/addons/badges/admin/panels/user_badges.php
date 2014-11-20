<?php global $xoouserultra, $uultra_badges; ?>

<form action="" method="post">

<h3><?php _e('View/Delete Member Medallions','xoousers'); ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="badge_user"><?php _e('Find a Member','xoousers'); ?></label></th>
		<td>
			<select name="badge_user" id="badge_user" class="chosen-select" style="width:300px" data-placeholder="">
				<option value=""><?php _e('Select Member...','xoousers'); ?></option>
				<?php
				$users=uultra_badges_admin_users(true);
				foreach($users as $user) {
					
				?>
				<option value="<?php echo $user->ID; ?>"<?php $xoouserultra->uultra_admin_post_value('badge_user', $user->ID, $_POST); ?>><?php echo  $xoouserultra->userpanel->get_user_meta_custom( $user->ID, 'display_name'); if ($user->user_email) echo ' ('. $user->user_email . ')'; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>

<p class="submit">
	<input type="submit" name="find-user-badges" id="find-user-badges" class="button button-primary" value="<?php _e('Find Member Medallions','xoousers'); ?>"  />
</p>

</form>

<?php
if (isset($_POST['badge_user']) && $xoouserultra->user_exists($_POST['badge_user']) ) {

	$user_id = $_POST['badge_user'];
	$badges = get_user_meta($user_id, '_uultra_badges', true);
	
	if (isset($badges) && is_array($badges) && !empty($badges))
	{
		echo '<h3>'.sprintf(__('%s\'s Medallions','xoousers'),  $xoouserultra->userpanel->get_user_meta_custom($user_id, 'display_name') ).'</h3>';
		foreach($badges as $k => $arr) {
			?>
			
			<div class="uultra-user-badge">
				<img src="<?php echo $arr['badge_url']; ?>" alt="" title="<?php echo $arr['badge_title']; ?>" /> <?php echo $arr['badge_title']; ?>
				<a href="#" class="button uultra-delete-badge" data-user="<?php echo $user_id; ?>" data-url="<?php echo $arr['badge_url']; ?>"><?php _e('Delete Medallion','xoousers'); ?></a>
			</div>
			
			<?php
		}
	} else {
		delete_user_meta($user_id,'_uultra_badges');
		echo '<p>'.__('This Member does not have any manually assigned medallions.','xoousers').'</p>';
	}

}
?>
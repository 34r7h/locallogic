<?php global $uultra_badges, $xoouserultra; ?>

<p class="upadmin-highlight"><?php printf(__('If you want to add more medallions, please put your medallions as PNG image in <code>%s</code>. To give a new medallion, or assign a new fulfillment, click on a medallion below to start.','xoousers'), uultra_dg_url . 'badges/'); ?></p>

<form action="" method="post">

<h3><?php  ?></h3>
<table class="form-table">

	<tr valign="top">
		<th scope="row"></th>
		<td>
			<?php echo $uultra_badges->loop_badges(); ?>
		</td>
	</tr>

	<?php if (uultra_badges_admin_edit()){?>
	<input type="hidden" name="badge_url" id="badge_url" value="<?php echo uultra_badges_admin_edit_info('badge_url'); ?>" />
	<?php } else { ?>
	<input type="hidden" name="badge_url" id="badge_url" value="" />
	<?php } ?>
	
	<tr valign="top">
		<th scope="row"><label for="badge_title"><?php _e('Medallion Title','xoousers'); ?></label></th>
		<td>
			<input type="text" name="badge_title" id="badge_title" value="<?php if (uultra_badges_admin_edit()) echo uultra_badges_admin_edit_info('badge_title'); ?>" class="regular-text" />
			<span class="description"><?php _e('The title of medallion will appear when member hovers over it. For example Pro Member, Contributor, Artist etc.','xoousers'); ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="badge_method"><?php _e('Assignment/Fulfillment?','xoousers'); ?></label></th>
		<td>
			<select name="badge_method" id="badge_method" class="chosen-select" style="width:300px" data-placeholder="">
				<option value="manual" <?php if (uultra_badges_admin_edit()) echo 'disabled="disabled"'; ?>><?php _e('Assign medallion to members (manual)','xoousers'); ?></option>
				<option value="achievement"><?php _e('Require fulfillment (automatic)','xoousers'); ?></option>
			</select>
		</td>
	</tr>
	
</table>

<!-- Conditional Fields -->

<table class="form-table" data-type="conditional" rel="manual">
	<tr valign="top">
		<th scope="row"><label for="badge_to_users[]"><?php _e('Choose which members receive this medallion','xoousers'); ?></label></th>
		<td>
			<select name="badge_to_users[]" id="badge_to_users[]" multiple="multiple" class="chosen-select" style="width:500px; height:380px;" data-placeholder="<?php _e('Choose...','xoousers'); ?>">
				<?php
				$users=uultra_badges_admin_users();
				foreach($users as $user) {
				?>
				<option value="<?php echo $user->ID; ?>"><?php  echo $xoouserultra->userpanel->get_user_meta_custom( $user->ID, 'display_name'); if ($user->user_email) echo ' ('. $user->user_email . ')'; ?></option>
				<?php } ?>
			</select>
			<span class="description"><?php _e('Assign this medallion to individual members by selecting them here.','xoousers'); ?></span>
		</td>
	</tr>
</table>

<table class="form-table" data-type="conditional" rel="achievement">
	<tr valign="top">
		<th scope="row"><label><?php _e('Setup Fulfillment','xoousers'); ?></label></th>
		<td>
			<label for="badge_achieved_num"><?php _e('User has completed','xoousers'); ?></label>
			<input type="text" name="badge_achieved_num" id="badge_achieved_num" value="<?php if (uultra_badges_admin_edit()) echo $_GET['bid']; ?>" class="badge_achieved_num" />
			<select name="badge_achieved_type" id="badge_achieved_type" class="chosen-select" style="width:300px" data-placeholder="">
				<option value="any" <?php if ( uultra_badges_admin_edit() ) selected('any', $_GET['btype']); ?> ><?php _e('Posts (Any post type)','xoousers'); ?></option>
				<option value="comments" <?php if ( uultra_badges_admin_edit() ) selected('comments', $_GET['btype']); ?> ><?php _e('Comments','xoousers'); ?></option>
				<?php echo uultra_badges_admin_post_types(); ?>
			</select>
		</td>
	</tr>
</table>

<p class="submit">
	<input type="submit" name="insert-badge" id="insert-badge" class="button button-primary" value="<?php _e('Submit','xoousers'); ?>"  />
</p>

</form>
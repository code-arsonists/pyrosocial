<h2>Friends</h2>
<?php if (!empty($users)): ?>

	<?php echo form_open('admin/users/action'); ?>
			<?php $link_profiles = Settings::get('enable_profiles'); ?>
			<?php dump($users); foreach ($users as $member): ?>
				<div class="lwrap">
					<h4>
					<?php  if ($link_profiles) : ?>
						<?php echo anchor('profiles/members/view/' . $member->id, $member->full_name, 'target="_blank" class="modal-large"'); ?>
					<?php else: ?>
						<?php echo $member->full_name; ?>
					<?php endif; ?>
					</h4>
					<table class="fleft meta clearfix">
						<tr>
							<td><?php echo lang('user_joined_label');?></td>
							<td> : <?php echo format_date($member->created_on); ?></td>
						</tr>
						<tr>
							<th><?php echo lang('user_last_visit_label');?></th>
							<td> : <?php echo ($member->last_login > 0 ? format_date($member->last_login) : lang('user_never_label')); ?></td>
						</tr>
					</table>
					<?php if ($member->id != $this->user->id): ?>
					<div class="fright">
						<?php if ($member->is_friend && $member->is_confirmed == 0): ?>
							<span class="button frequest">Awaiting Confirmation</span>
						<?php elseif ($member->is_friend): ?>
							<a href="#" class="button frequest rem_friend" data-uid="<?php echo $member->id; ?>">- Friendship</a>
						<?php else: ?>
							<a href="#" class="button frequest add_friend" data-uid="<?php echo $member->id; ?>">+ Friendship</a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

<?php echo form_close(); ?>
<script>
jQuery(function($){
	$('.add_friend').live('click', function(){
		var me = $(this);
		var that = jQuery(this).busy();
		$.get('members/req_friendship', {fid: me.attr('data-uid')}, function(d){
			me.html('Awaiting Confirmation');
			that.busy("hide");
			me.removeClass('add_friend');
		}, 'json');
		return false;
	});

});
</script>
<?php else: ?>
	<div class="blank-slate">

		<img src="<?php echo site_url('system/cms/modules/users/img/user.png') ?>" />

		<h2><?php echo lang($this->method == 'index' ? 'user_no_registred' : 'user_no_inactives');?></h2>
	</div>
<?php endif; ?>


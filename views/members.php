<?php if (!$this->input->is_ajax_request()): ?>
<h2>Members</h2>
		<p>
		<b>Filters: </b>
		<span class="fbut mfilter" data-filter="friends">Friends</span>
		<span class="fbut mfilter" data-filter="requests">Requests</span>
		<span class="fbut mfilter" data-filter="myrequests">Awaiting</span>
		<span class="fbut mfilter" data-filter="all">All</span>
		</p>
<?php endif; ?>
<div id="usrlist">
<?php if (!empty($users)): ?>

	<?php echo form_open('admin/users/action'); ?>


			<?php $link_profiles = Settings::get('enable_profiles'); ?>
			<?php foreach ($users as $member): ?>
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
					<?php if ($member->id != $this->current_user->id): ?>
					<div class="f-btn-actions">
						<?php if ($member->is_friend && $member->is_confirmed == 0): ?>

							<?php if ($member->friend_id != $this->current_user->id): ?>
								<a class="f-btn frequest">Awaiting Confirmation</a>
							<?php else: ?>
								<a class="f-btn frequest confirm_friend" data-uid="<?php echo $member->id; ?>">Accept Friendship</a>
								&nbsp; <a class="f-btn frequest reject_friend" data-uid="<?php echo $member->id; ?>">Reject Friendship</a>
							<?php endif; ?>

						<?php elseif ($member->is_friend): ?>
							<a href="#" class="f-btn frequest rem_friend" data-uid="<?php echo $member->id; ?>">- Friendship</a>
						<?php else: ?>
							<a href="#" class="f-btn frequest add_friend" data-uid="<?php echo $member->id; ?>">+ Friendship</a>
						<?php endif; ?>
					</div>
					<?php else: ?>
						<span class="fright">You</span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

<?php echo form_close(); ?>
<?php else: ?>
	<div class="blank-slate">

		<img src="<?php echo site_url('system/cms/modules/users/img/user.png') ?>" />

		<h2>Nothing found..</h2>
	</div>
<?php endif; ?>
</div>
<?php if (!$this->input->is_ajax_request()): ?>

<script>
jQuery(function($){
	//$.noticeAdd({stay:true, stayTime:3000, type: 'notice, error, succes' text:''});

	$('.mfilter').live('click', function(){
		var self = $(this);
		var that = $('#usrlist').busy();
		$('.mfilter').removeClass('sel');
		self.addClass('sel');

		if (self.attr('data-filter') == 'friends') {

		}
		$.post(BASE_URI + 'pyrosocial/friends/index', { <?php echo $this->security->get_csrf_token_name() .': \''. $this->security->get_csrf_hash(); ?>', mfilter: self.attr('data-filter')}, function(d){
			that.busy("hide");
			$('#usrlist').html(d);
			if (typeof Cufon === 'function') Cufon.refresh();
		});
		return false;
	});

	$('.confirm_friend').live('click', function(){
		var me = $(this);
		var that = jQuery(this).busy();
		$.get(BASE_URI + 'pyrosocial/friends/confirm', {fid: me.attr('data-uid')}, function(d){
			me.html('- Friendship');
			me.next('.reject_friend').remove();
			that.busy("hide");
			me.removeClass('confirm_friend');
			me.addClass('rem_friend');
			$.noticeAdd({text:d.data});
		}, 'json');
		return false;
	});

	$('.add_friend').live('click', function(){
		var me = $(this);
		var that = jQuery(this).busy();
		$.get(BASE_URI + 'pyrosocial/friends/request', {fid: me.attr('data-uid')}, function(d){
			me.html('Awaiting Confirmation');
			that.busy("hide");
			me.removeClass('add_friend');
			$.noticeAdd({text:d.data});
		}, 'json');
		return false;
	});
	$('.rem_friend').live('click', function(){
		var me = $(this);
		var that = jQuery(this).busy();
		$.get(BASE_URI + 'pyrosocial/friends/remove', {fid: me.attr('data-uid')}, function(d){
			me.html('+ Friend');
			that.busy("hide");
			me.removeClass('rem_friend');
			me.addClass('add_friend');
			$.noticeAdd({text:'Friend Removed'});
		}, 'json');
		return false;
	});
	$('.reject_friend').live('click', function(){
		var me = $(this);
		var that = jQuery(this).busy();
		$.get(BASE_URI + 'pyrosocial/friends/reject', {fid: me.attr('data-uid')}, function(d){
			me.prev('.confirm_friend').remove();
			me.html('+ Friend');
			that.busy("hide");
			me.removeClass('reject_friend');
			me.addClass('add_friend');
			$.noticeAdd({text:'Friend request canceled'});
		}, 'json');
		return false;
	});
});
</script>
<?php endif; ?>



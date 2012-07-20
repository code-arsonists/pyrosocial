<?php if (isset($streams) && !empty($streams)): ?>

	<?php foreach ($streams AS $stream): ?>
		<!-- STREAM MAIN -->			
		<div class="friends_area" id="record-<?php echo $stream->id ?>">
			<img src="<?php echo gravatar($stream->email, 35, 'x', true); ?>" style="float:left;" alt="" />

			<label style="float:left" class="name">
			   <?php if ($stream->object_type != 'friendship_confirm'): ?>
			   <b><a href="profiles/members/view/<?php echo $stream->user_id ?>"><?php echo $stream->full_name; ?></a></b>
				<?php endif; ?>
				<em><?php echo $stream->body; ?></em>

				<abbr class="timeago" title="<? echo standard_date('DATE_ISO8601', $stream->created_on); ?>"><?php echo timespan($stream->created_on); ?></abbr>
				<span class="stream_like" data-id="<?php echo $stream->id; ?>">Like</span>
			</label>
			<?php if ($stream->user_id == $this->user->id): ?>
				<a href="#" class="delete">X</a>
			<?php endif; ?>
			<br clear="both" />
			<div id="CommentPosted<?php echo $stream->id; ?>" class="scomments">
				<?php
				if (!empty($stream->recent_comments)):
					$comments_row = unserialize($stream->recent_comments);
					if ($stream->num_comments > 3)
					{

						echo '<div class="commentPanel" align="left"><a href="#" class="show_all_comments" data-id="' . $stream->id . '" data-count="' . $stream->num_comments . '">Show all (' . $stream->num_comments . ') comments</a></div>';
					}
					//echo 'count: '. (!empty($comments_row['count'])) ? $comments_row['count']:0;
					//dump($comments_row);
					foreach ($comments_row as $cid => $rows)
					{
						?>
						<div class="commentPanel" id="record-<?php echo $cid; ?>" align="left">
							<img src="<?php echo gravatar($rows['email'], 25, 'x', true); ?>" style="float:left;" class="CommentImg" alt="" />

							<span style="float:left" class="name">
								<b><a href="profiles/members/view/<?php echo $rows['user_id'] ?>"><?php echo $rows['username']; ?></a></b></span>
							<p class="postedComments">
							<?php echo $rows['body']; ?>
							</p>
							<br clear="all" />
							<span class="timeago" title="<? echo standard_date('DATE_ISO8601', $rows['created_on']); ?>" style="margin-left:43px;">
							<?php echo timespan($rows['created_on']); ?>
							</span>
							<?php if ($this->user->id == $rows['user_id']): ?>
								&nbsp;&nbsp;<a href="#" id="CID-<?php echo $cid; ?>" class="c_delete">Delete</a>
						<?php endif; ?>
						</div>
						<?php } ?>
				<?php endif; ?>
			</div>


			<div class="commentBox" align="right" id="commentBox-<?php echo $stream->id ?>">
				<img src="<?php echo gravatar($this->user->email, 25, 'x', true); ?>" class="CommentImg" style="float:left;" alt="" />
				<label id="record-<?php echo $stream->id ?>" class="c-label">
					<textarea class="commentMark" id="commentMark-<?php echo $stream->id ?>" name="commentMark" cols="60"></textarea>
				</label>
				<!--<br clear="all" />
					<a id="SubmitComment" class="small button comment"> Comment</a>//-->
			</div>
		</div>
		<br clear="both" />

	<?php endforeach; ?>

<?php endif; ?>


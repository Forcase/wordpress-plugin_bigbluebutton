<?php
session_start();
if(isset($_SESSION[$room_id . '-livestream']) && $_SESSION[$room_id . '-livestream'] == true): ?>
	<h1>Live Stream</h1>
	<div style="float:left; width:49%">
		<video id='my-video-live' class="video-js vjs-default-skin" width='760' height='400'>
			<source src="LIVE_STREAM_URL">
			<p class='vjs-no-js'>
				To view this video please enable JavaScript, and consider upgrading to a web browser that
				<a href='http://videojs.com/html5-video-support/' target='_blank'>supports HTML5 video</a>
			</p>
		</video>
	</div>
	<div id="container" style=" float:right; width:49%">
		<form method="post" action="" id="contactform">
			<div class="form-group">
				<h2 >Send Question</h2>
				<textarea name="message" rows="15" cols="60" class="form-control" id="message"></textarea>
			</div>
			<button type="submit" class="btn btn-primary send-message">Submit</button>
		</form>
	</div>

<?php else: ?>

<form id="joinroom" method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" class="validate">
	<input type="hidden" name="action" value="join_room">
	<input id="bbb_join_room_id" type="hidden" name="room_id" value="<?php echo $room_id; ?>">
	<input type="hidden" id="bbb_join_room_meta_nonce" name="bbb_join_room_meta_nonce" value="<?php echo $meta_nonce; ?>">
	<input type="hidden" name="REQUEST_URI" value="<?php echo $current_url; ?>">
	<?php if ( ! is_user_logged_in() ) { ?>
		<div id="bbb_join_with_username" class="bbb-join-form-block">
			<label id="bbb_meeting_name_label" class="bbb-join-room-label"><?php esc_html_e( 'Name' ); ?>: </label>
			<input type="text" name="bbb_meeting_username" aria-labelledby="bbb_meeting_name_label" class="bbb-join-room-input">
		</div>
	<?php } ?>
	<?php if ( ! $access_as_moderator && ! $access_as_viewer && $access_using_code ) { ?>
		<div id="bbb_join_with_password" class="bbb-join-form-block">
	<?php } else { ?>
		<div id="bbb_join_with_password" class="bbb-join-form-block" style="display:none;">
	<?php } ?>
			<label id="bbb_meeting_access_code_label" class="bbb-join-room-label"><?php esc_html_e( 'Access Code', 'bigbluebutton' ); ?>: </label>
			<input type="text" name="bbb_meeting_access_code" aria-labelledby="bbb_meeting_access_code_label" class="bbb-join-room-input">
		</div>
		<?php if ( isset( $_REQUEST['password_error'] ) && $_REQUEST['room_id'] == $room_id ) { ?>
			<div class="bbb-error">
				<label><?php esc_html_e( 'The access code you have entered is incorrect. Please try again.', 'bigbluebutton' ); ?></label>
			</div>
		<?php } ?>
	<br>
	<?php if ( isset( $_REQUEST['bigbluebutton_wait_for_mod'] ) && $_REQUEST['room_id'] == $room_id ) { ?>
		<div class="bbb-join-form-block">
			<label id="bbb-wait-for-mod-msg"
				data-room-id="<?php echo $room_id; ?>"
				<?php if ( isset( $_REQUEST['temp_entry_pass'] ) ) { ?>
					data-temp-room-pass="<?php echo $_REQUEST['temp_entry_pass']; ?>"
				<?php } ?>
				<?php if ( isset( $_REQUEST['username'] ) ) { ?>
					data-room-username="<?php echo $_REQUEST['username']; ?>"
				<?php } ?>>
				<?php if ( $heartbeat_available ) { ?>
					<?php esc_html_e( 'The meeting has not started yet. You will be automatically redirected to the meeting when it starts.', 'bigbluebutton' ); ?>
				<?php } else { ?>
					<?php esc_html_e( 'The meeting has not started yet. Please wait for a moderator to start the meeting before joining.', 'bigbluebutton' ); ?>
				<?php } ?>
			</label>
		</div>
	<?php } ?>
	<input class="bbb-button" type="submit" class="button button-primary" value="<?php esc_html_e( 'Join', 'bigbluebutton' ); ?>">
</form>
<?php endif; ?>

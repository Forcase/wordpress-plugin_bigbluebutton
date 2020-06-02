<?php
if(session_id() == ''){
	session_start();
}
if (isset($_SESSION[$room_id . '-livestream']) && $_SESSION[$room_id . '-livestream'] == true): ?>
	<h1>Live Stream</h1>
	<div style="float:left; width:49%">
		<video id='my-video-live' class="video-js vjs-default-skin" width='760' height='400'>
			<source src="<?php echo get_field('bbb_ls_livestream-URL', $room_id); ?>">
			<p class='vjs-no-js'>
				To view this video please enable JavaScript, and consider upgrading to a web browser that
				<a href='http://videojs.com/html5-video-support/' target='_blank'>supports HTML5 video</a>
			</p>
		</video>
	</div>
<!---->
<!--	<div id="container" style=" float:right; width:49%">-->
<!--		<form method="post" action="" id="contactform">-->
<!--			<div class="form-group">-->
<!--				<h2>Send Question</h2>-->
<!--				<textarea name="message" rows="15" cols="60" class="form-control" id="message"></textarea>-->
<!--			</div>-->
<!--			<button type="submit" class="btn btn-primary send-message">Submit</button>-->
<!--		</form>-->
<!--	</div>-->

<?php else: ?>
	<?php if (get_field('bbb_ls_enabled', $room_id) && empty($_REQUEST['join_participant']) && empty($_REQUEST['join_livestream'])): ?>

		<form method="GET" id="joinmodeselection">
			<button class="bbb-button button button-primary" type="submit" name="join_participant" value="1">Als
				Teilnehmer beitreten
			</button>
			<button class="bbb-button button button-primary" type="submit" name="join_livestream" value="1">Livestream
				ansehen
			</button>
		</form>
	<?php elseif (!empty($_REQUEST['join_livestream'])): ?>
		<form id="joinlivestream" method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="validate">
			<input type="hidden" name="action" value="join_room">
			<input type="hidden" name="join_livestream" value="1">
			<input id="bbb_join_room_id" type="hidden" name="room_id" value="<?php echo $room_id; ?>">
			<input type="hidden" id="bbb_join_room_meta_nonce" name="bbb_join_room_meta_nonce"
				   value="<?php echo $meta_nonce; ?>">
			<input type="hidden" name="REQUEST_URI" value="<?php echo $current_url; ?>">
			<div id="bbb_join_with_password" class="bbb-join-form-block">
				<label id="bbb_meeting_access_code_label"
					   class="bbb-join-room-label"><?php esc_html_e('Access Code', 'bigbluebutton'); ?>: </label>
				<input type="password" name="bbb_meeting_access_code" aria-labelledby="bbb_meeting_access_code_label"
					   class="bbb-join-room-input">
				<input class="bbb-button" type="submit" class="button button-primary"
					   value="<?php esc_html_e('Join', 'bigbluebutton'); ?>">
			</div>
			<?php if (isset($_REQUEST['password_error']) && $_REQUEST['room_id'] == $room_id) { ?>
				<div class="bbb-error">
					<label><?php esc_html_e('The access code you have entered is incorrect. Please try again.', 'bigbluebutton'); ?></label>
				</div>
			<?php } ?>
		</form>
	<?php else: ?>

		<form id="joinroom" method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="validate">
			<input type="hidden" name="action" value="join_room">
			<input type="hidden" name="join_participant" value="1">
			<input id="bbb_join_room_id" type="hidden" name="room_id" value="<?php echo $room_id; ?>">
			<input type="hidden" id="bbb_join_room_meta_nonce" name="bbb_join_room_meta_nonce"
				   value="<?php echo $meta_nonce; ?>">
			<input type="hidden" name="REQUEST_URI" value="<?php echo $current_url; ?>">
			<?php if (!is_user_logged_in()) { ?>
				<div id="bbb_join_with_username" class="bbb-join-form-block">
					<label id="bbb_meeting_name_label" class="bbb-join-room-label"><?php esc_html_e('Name'); ?>
						: </label>
					<input type="text" name="bbb_meeting_username" aria-labelledby="bbb_meeting_name_label"
						   class="bbb-join-room-input" <?php echo isset($_REQUEST['username']) ? 'value="' . $_REQUEST['username'] . '"' : ''; ?>>
				</div>
			<?php } ?>
			<?php if (!$access_as_moderator && !$access_as_viewer && $access_using_code) { ?>
			<div id="bbb_join_with_password" class="bbb-join-form-block">
				<?php } else { ?>
				<div id="bbb_join_with_password" class="bbb-join-form-block" style="display:none;">
					<?php } ?>
					<label id="bbb_meeting_access_code_label"
						   class="bbb-join-room-label"><?php esc_html_e('Access Code', 'bigbluebutton'); ?>: </label>
					<input type="password" name="bbb_meeting_access_code"
						   aria-labelledby="bbb_meeting_access_code_label" class="bbb-join-room-input">

				</div>
				<div class="bbb_policies">
					<?php if(get_field('bbb_wp_privacy-policy_required', $room_id)): ?>
					<label for="bbb_accept_privacy_policy">
						<input id="bbb_accept_privacy_policy" type="checkbox"
							   name="bbb_accept_privacy_policy" <?php echo isset($_REQUEST['bbb_accept_privacy_policy']) ? 'checked' : ''; ?>>
						<?php echo get_field('bbb_wp_privacy-policy', $room_id) ?: __("Hiermit akzeptiere ich die Datenschutzrichtline.", "bigbluebutton"); ?>
					</label>
					<?php endif; ?>
					<?php if (get_field('bbb_wp_recording-policy_required', $room_id) && get_field('bbb_record', $room_id) || get_field('bbb_autoStartRecording', $room_id)): ?>
						<label for="bbb_accept_recording_policy">
							<input id="bbb_accept_recording_policy" type="checkbox"
								   name="bbb_accept_recording_policy" <?php echo isset($_REQUEST['bbb_accept_recording_policy']) ? 'checked' : ''; ?>>
							<?php echo get_field('bbb_wp_recording-policy', $room_id) ?: __("Hiermit akzeptiere ich die Aufnahme-Richtlinie.", "bigbluebutton"); ?>
						</label>
					<?php endif; ?>
					<?php if (get_field('bbb_wp_livestream-policy_required', $room_id) && get_field('bbb_ls_enabled', $room_id)): ?>
						<label for="bbb_accept_livestream_policy">
							<input id="bbb_accept_livestream_policy" type="checkbox"
								   name="bbb_accept_livestream_policy" <?php echo isset($_REQUEST['bbb_accept_livestream_policy']) ? 'checked' : ''; ?>>
							<?php echo get_field('bbb_wp_livestream-policy', $room_id) ?: __("Hiermit akzeptiere ich die Livestreaming-Richtlinie.", "bigbluebutton"); ?>
						</label>
					<?php endif; ?>
				</div>
				<?php if (isset($_REQUEST['password_error']) && $_REQUEST['room_id'] == $room_id) { ?>
					<div class="bbb-error">
						<label><?php esc_html_e('The access code you have entered is incorrect. Please try again.', 'bigbluebutton'); ?></label>
					</div>
				<?php } ?>
				<?php if (isset($_REQUEST['privacy_policy_error']) && $_REQUEST['room_id'] == $room_id) { ?>
					<div class="bbb-error">
						<label><?php esc_html_e('Bitte stimmen Sie der Datenschutz Richtlinie zu.', 'bigbluebutton'); ?></label>
					</div>
				<?php } ?>
				<?php if (isset($_REQUEST['recording_policy_error']) && $_REQUEST['room_id'] == $room_id) { ?>
					<div class="bbb-error">
						<label><?php esc_html_e('Bitte stimmen Sie der Aufzeichnungs Richtlinie zu.', 'bigbluebutton'); ?></label>
					</div>
				<?php } ?>
				<?php if (isset($_REQUEST['livestream_policy_error']) && $_REQUEST['room_id'] == $room_id) { ?>
					<div class="bbb-error">
						<label><?php esc_html_e('Bitte stimmen Sie der Livestream Richtlinie zu.', 'bigbluebutton'); ?></label>
					</div>
				<?php } ?>
				<br>
				<?php if (isset($_REQUEST['bigbluebutton_wait_for_mod']) && $_REQUEST['room_id'] == $room_id) { ?>
					<div class="bbb-join-form-block">
						<label id="bbb-wait-for-mod-msg"
							   data-room-id="<?php echo $room_id; ?>"
							<?php if (isset($_REQUEST['temp_entry_pass'])) { ?>
								data-temp-room-pass="<?php echo $_REQUEST['temp_entry_pass']; ?>"
							<?php } ?>
							<?php if (isset($_REQUEST['username'])) { ?>
								data-room-username="<?php echo $_REQUEST['username']; ?>"
							<?php } ?>>
							<?php if ($heartbeat_available) { ?>
								<?php esc_html_e('The meeting has not started yet. You will be automatically redirected to the meeting when it starts.', 'bigbluebutton'); ?>
							<?php } else { ?>
								<?php esc_html_e('The meeting has not started yet. Please wait for a moderator to start the meeting before joining.', 'bigbluebutton'); ?>
							<?php } ?>
						</label>
					</div>
				<?php } ?>
				<input class="bbb-button" type="submit" class="button button-primary"
					   value="<?php esc_html_e('Join', 'bigbluebutton'); ?>">
		</form>
	<?php endif; ?>
<?php endif; ?>

<?php
/**
 * Static API calls to the Bigbluebutton Server.
 *
 * @link       https://blindsidenetworks.com
 * @since      3.0.0
 *
 * @package    Bigbluebutton
 * @subpackage Bigbluebutton/includes
 */

/**
 * Static API calls to the Bigbluebutton Server.
 *
 * This class defines all code necessary to interact with the remote Bigbluebutton server.
 *
 * @since      3.0.0
 * @package    Bigbluebutton
 * @subpackage Bigbluebutton/includes
 * @author     Blindside Networks <contact@blindsidenetworks.com>
 */
class Bigbluebutton_Api
{

	/**
	 * Create new meeting.
	 *
	 * @param Integer $room_id Custom post id of the room the user is creating a meeting for.
	 * @param String $logout_url URL to return to after logging out.
	 *
	 * @return  Integer $return_code|404    HTML response of the bigbluebutton server.
	 * @since   3.0.0
	 *
	 */
	public static function create_meeting($room_id, $logout_url)
	{
		$rid = intval($room_id);

		if (get_post($rid) === false || 'bbb-room' != get_post_type($rid)) {
			return 404;
		}

		// todo: process params from acf

		$name = html_entity_decode(get_the_title($rid));
		$moderator_code = get_post_meta($rid, 'bbb-room-moderator-code', true);
		$viewer_code = get_post_meta($rid, 'bbb-room-viewer-code', true);
		$recordable = get_post_meta($rid, 'bbb-room-recordable', true);
		$meeting_id = get_field('bbb_meetingID', $rid);
		$welcome_message = get_field('welcome_message', $rid);

		$create_params = [
			'meetingID',
			'name',
			'welcome',
			'attendeePW',
			'moderatorPW',
			'dialNumber',
			'voiceBridge',
			'maxParticipants',
			'logoutURL',
			'record',
			'duration',
			'isBreakout',
			'parentMeetingID',
			'sequence',
			'freeJoin',
			'meta',
			'moderatorOnlyMessage',
			'autoStartRecording',
			'allowStartStopRecording',
			'webcamsOnlyForModerator',
			'logo',
			'bannerText',
			'bannerColor',
			'copyright',
			'muteOnStart',
			'allowModsToUnmuteUsers',
			'lockSettingsDisableCam',
			'lockSettingsDisableMic',
			'lockSettingsDisablePrivateChat',
			'lockSettingsDisablePublicChat',
			'lockSettingsDisableNote',
			'lockSettingsLockedLayout',
			'lockSettingsLockOnJoin',
			'lockSettingsLockOnJoinConfigurable',
			'guestPolicy',
		];

		$req_create_params = self::get_acf_req_params($rid, $create_params, 'bbb_');

		$config_params = [
			'defaultMaxUsers',
			'defaultMeetingDuration',
			'maxInactivityTimeoutMinutes',
			'clientLogoutTimerInMinutes',
			'warnMinutesBeforeMax',
			'meetingExpireIfNoUserJoinedInMinutes',
			'meetingExpireWhenLastUserLeftInMinutes',
			'userInactivityInspectTimerInMinutes',
			'userInactivityThresholdInMinutes',
			'userActivitySignResponseDelayInMinutes',
			'disableRecordingDefault',
//			'autoStartRecording',
//			'allowStartStopRecording',
//			'webcamsOnlyForModerator',
//			'muteOnStart',
//			'allowModsToUnmuteUsers',
			'keepEvents',
//			'lockSettingsDisableCam',
//			'lockSettingsDisableMic',
//			'lockSettingsDisablePrivateChat',
//			'lockSettingsDisablePublicChat',
//			'lockSettingsDisableNote',
//			'lockSettingsLockedLayout',
//			'lockSettingsLockOnJoin',
//			'lockSettingsLockOnJoinConfigurable',
		];

		$req_config_params = self::get_acf_req_params($rid, $config_params, 'bbb_cc_', '');

		$user_params = [
			// APP
			'ask_for_feedback_on_logout',
			'auto_join_audio',
			'client_title',
			'force_listen_only',
			'listen_only_mode',
			'skip_check_audio',
			// BRANDING
			'display_branding_area',
			// SHORTCUTS
			'shortcuts',
			// KURENTO
			'auto_share_webcam',
			'preferred_camera_profile',
			'enable_screen_sharing',
			'enable_video',
			'skip_video_preview',
			// WHITEBOARD
			'multi_user_pen_only',
			'presenter_tools',
			'multi_user_tools',
			// SKINNING/THEMMING
			'custom_style',
			'custom_style_url',
			// LAYOUT
			'auto_swap_layout',
			'hide_presentation',
			'show_participants_on_login',
			// OUTSIDE COMMANDS
			'outside_toggle_self_voice',
			'outside_toggle_recording',
		];

		$req_user_params = self::get_acf_req_params($rid, $user_params, 'bbb_ud_', 'userdata-bbb_');

		$meta1 = [
		];

		$req_meta1_params = self::get_acf_req_params($rid, $meta1, 'bbb_meta_', 'meta_bbb-');

		$req_params = array_merge($req_user_params, $req_config_params, $req_create_params, [
			'joinViaHtml5' => 'true',
			'guest' => get_field('bbb_join_guest', $room_id) ? 'true' : 'false',
		]);

		$url = self::build_url('create', $req_params);
		/*$arr_params = array(
			'name' => esc_attr($name),
			'meetingID' => rawurlencode($meeting_id),
			'attendeePW' => rawurlencode($viewer_code),
			'moderatorPW' => rawurlencode($moderator_code),
			'logoutURL' => esc_url($logout_url),
			'record' => $recordable,
			'welcome' => rawurlencode($welcome_message)
		);

		$url = self::build_url('create', $arr_params);*/

		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return 404;
		}

		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'returncode') && 'SUCCESS' == $response->returncode) {
			return 200;
		} elseif (property_exists($response, 'returncode') && 'FAILURE' == $response->returncode) {
			return 403;
		}

		return 500;

	}

	public static function get_acf_req_params($room_id, $params, $acf_prefix, $req_prefix='') {
		$req_params = [];
		foreach ($params as $param) {
			$field = get_field_object($acf_prefix . $param, $room_id);
			if(!$field) {
				// error
				continue;
			}
			$type = $field['type'];
			$value = get_field($acf_prefix . $param, $room_id);
			if(!empty($value) || $type == 'true_false'){
				if($type == 'url') {
					$value = esc_url($value);
				} elseif(is_string($value)) {
					$value = rawurlencode($value);
				} elseif(is_bool($value)) {
					$value = $value ? 'true' : 'false';
				}
				$req_params[$req_prefix . $param] = $value;
			}
		}
		return $req_params;
	}

	/**
	 * Join meeting.
	 *
	 * @param Integer $room_id Custom post id of the room the user is trying to join.
	 * @param String $username Full name of the user trying to join the room.
	 * @param String $password Entry code of the meeting that the user is attempting to join with.
	 * @param String $logout_url URL to return to after logging out.
	 *
	 * @return  String  $url|null   URL to enter the meeting.
	 * @since   3.0.0
	 *
	 */
	public static function get_join_meeting_url($room_id, $username, $password, $logout_url = null)
	{

		$rid = intval($room_id);
		$uname = sanitize_text_field($username);
		$pword = sanitize_text_field($password);
		$lo_url = ($logout_url ? esc_url($logout_url) : get_permalink($rid));

		if (get_post($rid) === false || 'bbb-room' != get_post_type($rid)) {
			return null;
		}

		if (!self::is_meeting_running($rid)) {
			$code = self::create_meeting($rid, $lo_url);
			if (200 !== $code) {
				wp_die(esc_html__('It is currently not possible to create rooms on the server. Please contact support for help.', 'bigbluebutton'));
			}
		}

		// todo: add join params

		$meeting_id = get_field('bbb_meetingID', $rid);
		$arr_params = array(
			'meetingID' => rawurlencode($meeting_id),
			'fullName' => $uname,
			'password' => rawurlencode($pword),
		);

		$user_params = [
			// APP
			'ask_for_feedback_on_logout',
			'auto_join_audio',
			'client_title',
			'force_listen_only',
			'listen_only_mode',
			'skip_check_audio',
			// BRANDING
			'display_branding_area',
			// SHORTCUTS
			'shortcuts',
			// KURENTO
			'auto_share_webcam',
			'preferred_camera_profile',
			'enable_screen_sharing',
			'enable_video',
			'skip_video_preview',
			// WHITEBOARD
			'multi_user_pen_only',
			'presenter_tools',
			'multi_user_tools',
			// SKINNING/THEMMING
			'custom_style',
			'custom_style_url',
			// LAYOUT
			'auto_swap_layout',
			'hide_presentation',
			'show_participants_on_login',
			// OUTSIDE COMMANDS
			'outside_toggle_self_voice',
			'outside_toggle_recording',
		];

		$req_user_params = self::get_acf_req_params($rid, $user_params, 'bbb_ud_', 'userdata-bbb_');

		$arr_params = array_merge($req_user_params, $arr_params, [
			'joinViaHtml5' => 'true',
			// Custom Styles
			'userdata-bbb_custom_style' => 'true',
			'userdata-bbb_custom_style_url' => admin_url('admin-post.php') . '?action=generate_room_css&rid=' . $room_id
		]);

		$url = self::build_url('join', $arr_params);

		return $url;
	}

	/**
	 * Check if meeting is running.
	 *
	 * @param Integer $room_id Custom post id of a room.
	 * @return  Boolean true|false|null     If the meeting is running or not.
	 * @since   3.0.0
	 *
	 */
	public static function is_meeting_running($room_id)
	{

		$rid = intval($room_id);

		if (get_post($rid) === false || 'bbb-room' != get_post_type($rid)) {
			return null;
		}

		$meeting_id = get_field('bbb_meetingID', $rid);
		$arr_params = array(
			'meetingID' => rawurlencode($meeting_id),
		);

		$url = self::build_url('isMeetingRunning', $arr_params);
		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return null;
		}

		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'running') && 'true' == $response->running) {
			return true;
		}

		return false;
	}

	/**
	 * Get all recordings for selected room.
	 *
	 * @param Array $room_ids List of custom post ids for rooms.
	 * @param String $recording_state State of recordings to get.
	 * @return  Array  $recordings             List of recordings for this room.
	 * @since   3.0.0
	 *
	 */
	public static function get_recordings($room_ids, $recording_state = 'published')
	{
		$state = sanitize_text_field($recording_state);
		$recordings = [];
		$meeting_ids = '';

		foreach ($room_ids as $rid) {
			$meeting_ids .= get_post_meta(sanitize_text_field($rid), 'bbb-room-meeting-id', true) . ',';
		}

		substr_replace($meeting_ids, '', -1);

		$arr_params = array(
			'meetingID' => $meeting_ids,
			'state' => $state,
		);

		$url = self::build_url('getRecordings', $arr_params);
		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return $recordings;
		}

		$response = self::response_to_xml($full_response);
		if (property_exists($response, 'recordings') && property_exists($response->recordings, 'recording')) {
			$recordings = $response->recordings->recording;
		}

		return $recordings;
	}

	/**
	 * Publish/unpublish a recording.
	 *
	 * @param String $recording_id The ID of the recording that will be published/unpublished.
	 * @param String $state Set publishing state of the recording.
	 * @return  Integer 200|404|500     Status of the request.
	 * @since   3.0.0
	 *
	 */
	public static function set_recording_publish_state($recording_id, $state)
	{
		$record = sanitize_text_field($recording_id);

		if ('true' != $state && 'false' != $state) {
			return 404;
		}

		$arr_params = array(
			'recordID' => rawurlencode($record),
			'publish' => rawurlencode($state),
		);

		$url = self::build_url('publishRecordings', $arr_params);
		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return 404;
		}
		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'returncode') && 'SUCCESS' == $response->returncode) {
			return 200;
		}
		return 500;
	}

	/**
	 * Protect/unprotect a recording.
	 *
	 * @param String $recording_id The ID of the recording that will be protected/unprotected.
	 * @param String $state Set protected state of the recording.
	 * @return  Integer 200|404|500     Status of the request.
	 * @since   3.0.0
	 *
	 */
	public static function set_recording_protect_state($recording_id, $state)
	{
		$record = sanitize_text_field($recording_id);

		if ('true' != $state && 'false' != $state) {
			return 404;
		}

		$arr_params = array(
			'recordID' => rawurlencode($record),
			'protect' => rawurlencode($state),
		);

		$url = self::build_url('updateRecordings', $arr_params);
		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return 404;
		}
		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'returncode') && 'SUCCESS' == $response->returncode) {
			return 200;
		}
		return 500;
	}

	/**
	 * Delete recording.
	 *
	 * @param String $recording_id ID of the recording that will be deleted.
	 * @return  Integer 200|404|500     Status of the request.
	 * @since   3.0.0
	 *
	 */
	public static function delete_recording($recording_id)
	{
		$record = sanitize_text_field($recording_id);

		$arr_params = array(
			'recordID' => rawurlencode($record),
		);

		$url = self::build_url('deleteRecordings', $arr_params);
		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return 404;
		}
		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'returncode') && 'SUCCESS' == $response->returncode) {
			return 200;
		}
		return 500;
	}

	/**
	 * Change recording meta fields.
	 *
	 * @param String $recording_id ID of the recording that will be edited.
	 * @param String $type Type of meta field that will be changed.
	 * @param String $value Value of the meta field.
	 *
	 * @return  Integer 200|404|500     Status of the request.
	 */
	public static function set_recording_edits($recording_id, $type, $value)
	{
		$record = sanitize_text_field($recording_id);
		$recording_type = sanitize_text_field($type);
		$new_value = sanitize_text_field($value);
		$meta_key = 'meta_recording-' . $recording_type;

		$arr_params = array(
			'recordID' => rawurlencode($record),
			$meta_key => rawurlencode($new_value),
		);

		$url = self::build_url('updateRecordings', $arr_params);
		$full_response = self::get_response($url);

		if (is_wp_error($full_response)) {
			return 404;
		}

		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'returncode') && 'SUCCESS' == $response->returncode) {
			return 200;
		}

		return 500;
	}

	/**
	 * Verify that the endpoint is a BigBlueButton server, the salt is correct, and the server is running.
	 *
	 * @param String $url BigBlueButton URL endpoint to be tested.
	 * @param String $salt BigBlueButton server salt to be tested.
	 *
	 * @return Boolean true|false Whether the BigBlueButton server settings are correctly configured or not.
	 * @since 3.0.0
	 *
	 */
	public static function test_bigbluebutton_server($url, $salt)
	{
		$test_url = $url . 'api/getMeetings?checksum=' . sha1('getMeetings' . $salt);
		$full_response = self::get_response($test_url);

		if (is_wp_error($full_response)) {
			return false;
		}

		$response = self::response_to_xml($full_response);

		if (property_exists($response, 'returncode') && 'SUCCESS' == $response->returncode) {
			return true;
		}

		return false;
	}

	/**
	 * Fetch response from remote url.
	 *
	 * @param String $url URL to get response from.
	 * @return  Array|WP_Error  $response   Server response in array format.
	 * @since   3.0.0
	 *
	 */
	private static function get_response($url)
	{
		$result = wp_remote_get(esc_url_raw($url));
		return $result;
	}

	/**
	 * Convert website response to XML Object.
	 *
	 * @param Array $full_response Website response to convert to XML object.
	 * @return Object $xml                 XML Object of the body.
	 * @since   3.0.0
	 *
	 */
	private static function response_to_xml($full_response)
	{
		try {
			$xml = new SimpleXMLElement(wp_remote_retrieve_body($full_response));
		} catch (Exception $exception) {
			return new stdClass();
		}
		return $xml;
	}

	/**
	 * Returns the complete url for the bigbluebutton server request.
	 *
	 * @param String $request_type Type of request to the bigbluebutton server.
	 * @param Array $args Parameters of the request stored in an array format.
	 * @return  String $url            URL with all parameters and calculated checksum.
	 * @since   3.0.0
	 *
	 */
	private static function build_url($request_type, $args)
	{
		$type = sanitize_text_field($request_type);

		$url_val = strval(get_option('bigbluebutton_url', 'http://test-install.blindsidenetworks.com/bigbluebutton/'));
		$salt_val = strval(get_option('bigbluebutton_salt', '8cd8ef52e8e101574e400365b55e11a6'));

		$url = $url_val . 'api/' . $type . '?';

		$params = http_build_query($args);

		$url .= $params . '&checksum=' . sha1($type . $params . $salt_val);

		return $url;
	}
}

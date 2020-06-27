<?php
/**
 * The shortcode for the plugin.
 *
 * @link       https://blindsidenetworks.com
 * @since      3.0.0
 *
 * @package    Bigbluebutton
 * @subpackage Bigbluebutton/public
 */

/**
 * The shortcode for the plugin.
 *
 * Registers the shortcode and handles displaying the shortcode.
 *
 * @package    Bigbluebutton
 * @subpackage Bigbluebutton/public
 * @author     Blindside Networks <contact@blindsidenetworks.com>
 */
class Bigbluebutton_Public_Shortcode
{

	/**
	 * Register bigbluebutton shortcodes.
	 *
	 * @since   3.0.0
	 */
	public function register_shortcodes()
	{
		add_shortcode('bigbluebutton', array($this, 'display_bigbluebutton_shortcode'));
		add_shortcode('bigbluebutton_recordings', array($this, 'display_bigbluebutton_old_recordings_shortcode'));
		add_shortcode('bigbluebutton_rooms_list', [$this, 'display_bigbluebutton_rooms_shortcode']);
		add_shortcode('bigbluebutton_room_create', [$this, 'display_bigbluebutton_room_create']);
		add_shortcode('bigbluebutton_room_edit', [$this, 'display_bigbluebutton_room_edit']);
	}

	public function get_user_room_count()
	{
		// todo: maybe move this somewhere else
		$query = new WP_Query([
			'post_type' => 'bbb-room',
			'author' => get_current_user_id()
		]);
		return $query->found_posts;
	}

	public function display_bigbluebutton_room_create($atts = [], $content = null)
	{
		// todo: return error message if not logged in
		// todo: bail if max rooms

		// create / edit
		$is_edit = false;
		$room_id = $_GET['edit_room'] ?? false;

		ob_start();

		?>

		<div class="bbb-room-create">
			<div class="bbb-room-count">
				Anzahl der Raume: <?php echo $this->get_user_room_count(); ?>
			</div>
			<form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" class="bbb-create-room">
				<input type="hidden" name="action" value="frontend_create_room">
				<?php if ($room_id): ?>
					<input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
				<?php endif; ?>
				<?php wp_nonce_field('frontend_create_room') ?>
				<label for="">Raumname
					<input type="text" name="room_name" value="<?php $this->output_field_prefill('bbb_name', $room_id); ?>">
				</label>
				<button type="submit">Raum erstellen</button>
			</form>

		</div>

		<?php


		$content .= ob_get_clean();

		return $content;
	}

	public function display_bigbluebutton_room_edit($atts = [], $content = null)
	{
		ob_start();

		?>


		<?php

		$content .= ob_get_clean();

		return $content;
	}

	public function output_field_prefill($name = '', $room_id)
	{
		// todo: if current user can
		if($room_id) {
			echo get_field($name, $room_id);
		}
	}

	public function get_max_rooms(){
		$active_memberships = wc_memberships_get_user_active_memberships();
		$memberships = wc_memberships_get_user_memberships();
		// do something to evaluate the right type of memberships
		if(!empty($memberships)){
			$membership = $memberships[0];
			// active membership
			$is_active = $membership->get_status() === 'active';
			$is_active2 = $membership->is_active() === true;

			$max_rooms = $this->get_max_rooms();

		}

		$membership_plan = $membership->get_plan();
		$plan_id = $membership_plan->get_id();

		return (int) get_field('max_rooms', $plan_id);
	}

	public function display_bigbluebutton_rooms_shortcode($atts = [], $content = null)
	{
		// todo: display the contens of this shortcode only to active subscriptions
		// todo: query rooms created by the current user and output them as a list with link to room

		// todo: build helper function for each restricted feature display shortcode
		$my_romms_post_id = 5;
//		$can_access_my_rooms = ! current_user_can( 'wc_memberships_view_restricted_post_content', $post_id );
		// todo: handler for status change
		// action: 'wc_memberships_user_membership_status_changed membership, old_status, new_status

		$query = new WP_Query([
			'post_type' => 'bbb-room',
			'author' => get_current_user_id()
		]);
		$count = $query->post_count;

		// do some tests here



		ob_start();

		?>

		<?php if($max_rooms): ?>
	Räume: <?php echo $count; ?> von <?php echo $max_rooms; ?>
		<?php endif; ?>

		<?php
		while ($query->have_posts()):
			$query->the_post();
			?>

			<li><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
				<a href="<?php echo 'http://quorato.test/raum-erstellen/' . '?edit_room=' . get_the_ID(); ?>">Raum bearbeiten</a>
			</li>

		<?php

		endwhile;

		wp_reset_postdata();


		$content .= ob_get_clean();

		return $content;

	}

	/**
	 * Handle shortcode attributes.
	 *
	 * @param Array $atts Parameters in the shortcode.
	 * @param String $content Content of the shortcode.
	 *
	 * @return  String $content    Content of the shortcode with rooms and recordings.
	 * @since   3.0.0
	 *
	 */
	public function display_bigbluebutton_shortcode($atts = [], $content = null)
	{
		global $pagenow;
		$type = 'room';
		$author = (int)get_the_author_meta('ID');
		$display_helper = new Bigbluebutton_Display_Helper(plugin_dir_path(__FILE__));

		if (!Bigbluebutton_Tokens_Helper::can_display_room_on_page()) {
			return $content;
		}

		if (array_key_exists('type', $atts) && 'recording' == $atts['type']) {
			$type = 'recording';
			unset($atts['type']);
		}

		$tokens_string = Bigbluebutton_Tokens_Helper::get_token_string_from_atts($atts);

		if ('room' == $type) {
			$content .= Bigbluebutton_Tokens_Helper::join_form_from_tokens_string($display_helper, $tokens_string, $author);
		} elseif ('recording' == $type) {
			$content .= Bigbluebutton_Tokens_Helper::recordings_table_from_tokens_string($display_helper, $tokens_string, $author);
		}
		return $content;
	}

	/**
	 * Shows recordings for the old recordings shortcode format.
	 *
	 * @param Array $atts Parameters in the shortcode.
	 * @param String $content Content of the shortcode.
	 *
	 * @return  String $content    Content of the shortcode with recordings.
	 * @since   3.0.0
	 */
	public function display_bigbluebutton_old_recordings_shortcode($atts = [], $content = null)
	{
		$atts['type'] = 'recording';
		return $this->display_bigbluebutton_shortcode($atts, $content);
	}
}

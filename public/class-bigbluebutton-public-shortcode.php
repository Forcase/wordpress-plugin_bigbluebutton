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
	}

	public function display_bigbluebutton_rooms_shortcode($atts = [], $content = null)
	{
		// todo: query rooms created by the current user and output them as a list with link to room

		$query = new WP_Query([
			'post_type' => 'bbb-room',
			'author' => get_current_user_id()
		]);

		ob_start();

		while ($query->have_posts()):
			$query->the_post();
			?>

			<li><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></li>

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

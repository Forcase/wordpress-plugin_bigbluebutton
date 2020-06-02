<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://blindsidenetworks.com
 * @since      3.0.0
 *
 * @package    Bigbluebutton
 * @subpackage Bigbluebutton/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bigbluebutton
 * @subpackage Bigbluebutton/admin
 * @author     Blindside Networks <contact@blindsidenetworks.com>
 */
class Bigbluebutton_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param String $plugin_name The name of this plugin.
	 * @param String $version The version of this plugin.
	 * @since   3.0.0
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bigbluebutton_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bigbluebutton_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/bigbluebutton-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bigbluebutton_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bigbluebutton_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$translations = array(
			'ajax_url' => admin_url('admin-ajax.php'),
		);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/bigbluebutton-admin.js', array('jquery'), $this->version, false);
		wp_localize_script($this->plugin_name, 'php_vars', $translations);
	}

	/**
	 * Add Rooms as its own menu item on the admin page.
	 *
	 * @since   3.0.0
	 */
	public function create_admin_menu()
	{
		add_menu_page(
			__('Rooms', 'bigbluebutton'), __('Rooms', 'bigbluebutton'), 'activate_plugins', 'bbb_room',
			'', 'dashicons-video-alt2'
		);

		if (current_user_can('manage_categories')) {
			add_submenu_page(
				'bbb_room', __('Rooms', 'bigbluebutton'), __('Categories'), 'activate_plugins',
				'edit-tags.php?taxonomy=bbb-room-category', ''
			);
		}

		add_submenu_page(
			'bbb_room', __('Rooms', 'bigbluebutton'), __('Settings'), 'activate_plugins',
			'bbb-room-server-settings', array($this, 'display_room_server_settings')
		);
	}

	/**
	 * Add filter to highlight custom menu category submenu.
	 *
	 * @param String $parent_file Current parent page that the user is on.
	 * @return  String $parent_file    Custom menu slug.
	 * @since   3.0.0
	 *
	 */
	public function bbb_set_current_menu($parent_file)
	{
		global $submenu_file, $current_screen, $pagenow;

		// Set the submenu as active/current while anywhere in your Custom Post Type.
		if ('bbb-room-category' == $current_screen->taxonomy && 'edit-tags.php' == $pagenow) {
			$submenu_file = 'edit-tags.php?taxonomy=bbb-room-category';
			$parent_file = 'bbb_room';
		}
		return $parent_file;
	}

	/**
	 * Add custom room column headers to rooms list table.
	 *
	 * @param Array $columns Array of existing column headers.
	 * @return  Array $columns    Array of existing column headers and custom column headers.
	 * @since   3.0.0
	 *
	 */
	public function add_custom_room_column_to_list($columns)
	{
		$custom_columns = array(
			'category' => __('Category'),
			'permalink' => __('Permalink'),
			'token' => __('Token', 'bigbluebutton'),
			'moderator-code' => __('Moderator Code', 'bigbluebutton'),
			'viewer-code' => __('Viewer Code', 'bigbluebutton'),
		);

		$columns = array_merge($columns, $custom_columns);

		return $columns;
	}

	/**
	 * Fill in custom column information on rooms list table.
	 *
	 * @param String $column Name of the column.
	 * @param Integer $post_id Room ID of the current room.
	 * @since 3.0.0
	 *
	 */
	public function bbb_room_custom_columns($column, $post_id)
	{
		switch ($column) {
			case 'category':
				$categories = wp_get_object_terms($post_id, 'bbb-room-category', array('fields' => 'names'));
				if (!is_wp_error($categories)) {
					echo esc_attr(implode(', ', $categories));
				}
				break;
			case 'permalink':
				$permalink = (get_permalink($post_id) ? get_permalink($post_id) : '');
				echo '<a href="' . esc_url($permalink) . '" target="_blank">' . esc_url($permalink) . '</a>';
				break;
			case 'token':
				if (metadata_exists('post', $post_id, 'bbb-room-token')) {
					$token = get_post_meta($post_id, 'bbb-room-token', true);
				} else {
					$token = 'z' . esc_attr($post_id);
				}
				echo esc_attr($token);
				break;
			case 'moderator-code':
				echo esc_attr(get_post_meta($post_id, 'bbb-room-moderator-code', true));
				break;
			case 'viewer-code':
				echo esc_attr(get_post_meta($post_id, 'bbb-room-viewer-code', true));
				break;
		}
	}

	/**
	 * Render the server settings page for plugin.
	 *
	 * @since   3.0.0
	 */
	public function display_room_server_settings()
	{
		$change_success = $this->room_server_settings_change();
		$bbb_settings = $this->fetch_room_server_settings();
		$meta_nonce = wp_create_nonce('bbb_edit_server_settings_meta_nonce');
		require_once 'partials/bigbluebutton-settings-display.php';
	}

	/**
	 * Retrieve the room server settings.
	 *
	 * @return  Array   $settings   Room server default and current settings.
	 * @since   3.0.0
	 *
	 */
	public function fetch_room_server_settings()
	{
		$settings = array(
			'bbb_url' => get_option('bigbluebutton_url', 'http://test-install.blindsidenetworks.com/bigbluebutton/'),
			'bbb_salt' => get_option('bigbluebutton_salt', '8cd8ef52e8e101574e400365b55e11a6'),
			'bbb_default_url' => 'http://test-install.blindsidenetworks.com/bigbluebutton/',
			'bbb_default_salt' => '8cd8ef52e8e101574e400365b55e11a6',
		);

		return $settings;
	}

	/**
	 * Show information about new plugin updates.
	 *
	 * @param Array $current_plugin_metadata The plugin metadata of the current version of the plugin.
	 * @param Object $new_plugin_metadata The plugin metadata of the new version of the plugin.
	 * @since   1.4.6
	 *
	 */
	public function bigbluebutton_show_upgrade_notification($current_plugin_metadata, $new_plugin_metadata = null)
	{
		if (!$new_plugin_metadata) {
			$new_plugin_metadata = $this->bigbluebutton_update_metadata($current_plugin_metadata['slug']);
		}
		// Check "upgrade_notice".
		if (isset($new_plugin_metadata->upgrade_notice) && strlen(trim($new_plugin_metadata->upgrade_notice)) > 0) {
			echo '<div style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice:</strong> ';
			echo esc_html(strip_tags($new_plugin_metadata->upgrade_notice)), '</div>';
		}
	}

	/**
	 * Get information about the newest plugin version.
	 *
	 * @param String $plugin_slug The slug of the old plugin version.
	 * @return  Object $new_plugin_metadata    The metadata of the new plugin version.
	 * @since   1.4.6
	 *
	 */
	private function bigbluebutton_update_metadata($plugin_slug)
	{
		$plugin_updates = get_plugin_updates();
		foreach ($plugin_updates as $update) {
			if ($update->update->slug === $plugin_slug) {
				return $update->update;
			}
		}
	}

	/**
	 * Check for room server settings change requests.
	 *
	 * @return  Integer 1|2|3   If the room servers have been changed or not.
	 *                          0 - failure
	 *                          1 - success
	 *                          2 - bad url format
	 *                          3 - bad bigbluebutton settings configuration
	 * @since   3.0.0
	 *
	 */
	private function room_server_settings_change()
	{
		if (!empty($_POST['action']) && 'bbb_general_settings' == $_POST['action'] && wp_verify_nonce(sanitize_text_field($_POST['bbb_edit_server_settings_meta_nonce']), 'bbb_edit_server_settings_meta_nonce')) {
			$bbb_url = sanitize_text_field($_POST['bbb_url']);
			$bbb_salt = sanitize_text_field($_POST['bbb_salt']);

			$bbb_url .= (substr($bbb_url, -1) == '/' ? '' : '/');

			if (!Bigbluebutton_Api::test_bigbluebutton_server($bbb_url, $bbb_salt)) {
				return 3;
			}

			if (substr_compare($bbb_url, 'bigbluebutton/', strlen($bbb_url) - 14) !== 0) {
				return 2;
			}

			update_option('bigbluebutton_url', $bbb_url);
			update_option('bigbluebutton_salt', $bbb_salt);

			return 1;
		}
		return 0;
	}

	/**
	 * Generate missing heartbeat API if missing.
	 *
	 * @since   3.0.0
	 */
	public function check_for_heartbeat_script()
	{
		$bbb_warning_type = 'bbb-missing-heartbeat-api-notice';
		if (!wp_script_is('heartbeat', 'registered') && !get_option('dismissed-' . $bbb_warning_type, false)) {
			$bbb_admin_warning_message = __('BigBlueButton works best with the heartbeat API enabled. Please enable it.', 'bigbluebutton');
			$bbb_admin_notice_nonce = wp_create_nonce($bbb_warning_type);
			require 'partials/bigbluebutton-warning-admin-notice-display.php';
		}
	}

	/**
	 * Hide others rooms if user does not have permission to edit them.
	 *
	 * @param Object $query Query so far.
	 * @return Object $query   Query for rooms.
	 * @since  3.0.0
	 *
	 */
	public function filter_rooms_list($query)
	{
		global $pagenow;

		if ('edit.php' != $pagenow || !$query->is_admin || 'bbb-room' != $query->query_vars['post_type']) {
			return $query;
		}

		if (!current_user_can('edit_others_bbb_rooms')) {
			$query->set('author', get_current_user_id());
		}
		return $query;
	}

	public function add_acf_fields()
	{
		if (function_exists('acf_add_local_field_group')):

			$fields = array(
				'key' => 'group_5ed41e5b73a6a',
				'title' => 'BigBlueButton Room',
				'fields' => array(
					array(
						'key' => 'field_5ed4330a30fd3',
						'label' => 'Create',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed4217236e8b',
						'label' => 'name',
						'name' => 'bbb_name',
						'type' => 'text',
						'instructions' => 'Einen Namen für die Besprechung.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed427cf413f2',
						'label' => 'meetingID',
						'name' => 'bbb_meetingID',
						'type' => 'text',
						'instructions' => 'Eine Besprechungs-ID, die zur Identifizierung dieser Besprechung durch die 3rd-Party-Anwendung verwendet werden kann.

Diese muss für den Server, den Sie anrufen, eindeutig sein: Verschiedene aktive Besprechungen können nicht dieselbe Besprechungs-ID haben.

Wenn Sie eine nicht eindeutige Besprechungs-ID angeben (es läuft bereits eine Besprechung mit derselben Besprechungs-ID), dann ist der Aufruf zum Erstellen erfolgreich, wenn die anderen Parameter im Aufruf zum Erstellen identisch sind (in der Antwort wird jedoch eine Warnmeldung angezeigt). Der Erstellungsaufruf ist idempotent: mehrmaliges Aufrufen hat keine Nebenwirkung. Auf diese Weise kann eine Drittanbieter-Anwendung die Überprüfung vermeiden, ob die Besprechung läuft, und immer vor dem Beitritt zu jedem Benutzer einen Anruf erstellen.

Besprechungs-IDs sollten nur ASCII-Groß-/Kleinbuchstaben, Zahlen, Bindestriche oder Unterstriche enthalten. Eine gute Wahl für die Besprechungs-ID ist es, einen GUID-Wert zu generieren, da dies alles garantiert, dass verschiedene Besprechungen nicht die gleiche Besprechungs-ID haben.',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed42804e8ed1',
						'label' => 'attendeePW',
						'name' => 'bbb_attendeePW',
						'type' => 'text',
						'instructions' => 'Das Passwort, das die Join-URL später als Passwort-Parameter angeben kann, um anzuzeigen, dass der Benutzer als Betrachter beitreten wird. Wenn keine attendeePW angegeben ist, wird beim Aufruf zum Erstellen ein zufällig generiertes attendeePW-Passwort für die Besprechung zurückgegeben.',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed42823e8ed2',
						'label' => 'moderatorPW',
						'name' => 'bbb_moderatorPW',
						'type' => 'text',
						'instructions' => 'Das Kennwort, das der URL beitreten wird, kann später als Kennwortparameter angegeben werden, um den Benutzer als Moderator anzugeben. Wenn keine moderatorPW angegeben wird, gibt create ein zufällig generiertes moderatorPW-Kennwort für die Besprechung zurück.',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed556bcd6dfb',
						'label' => 'wait_for_mod',
						'name' => 'bbb_wp_wait_for_mod',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42c99900ad',
						'label' => 'welcomeMessage',
						'name' => 'bbb_welcome',
						'type' => 'wysiwyg',
						'instructions' => 'Eine Begrüßungsnachricht, die im Chat-Fenster angezeigt wird, wenn der Teilnehmer beitritt. Sie können Schlüsselwörter einschließen (%%CONFNAME%%%, %%DIALNUM%%%, %%CONFNUM%%%), die automatisch ersetzt werden.
Dieser Parameter setzt die StandardeinstellungWelcomeMessage in bigbluebutton.properties außer Kraft.
Die Begrüßungsnachricht hat eine begrenzte Unterstützung für HTML-Formatierung. Seien Sie vorsichtig mit dem Kopieren/Einfügen von HTML aus z.B. MS Word, da es leicht die maximal unterstützte URL-Länge überschreiten kann, wenn es bei einer GET-Anfrage verwendet wird.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Herzlich willkommen zu %%CONFNAME%%!

Um der Konferenz per Audio beizutreten, klicken Sie auf den Telefon-Button. Verwenden Sie ein Headset, um Hintergrundgeräusche für andere zu vermeiden.

Falls Sie Hilfe bei der Verwendung dieser Videokonferenz benötigen, klicken Sie <a href="https://quorato.de/" target="_blank" rel="noopener">hier</a>.

Diese Konferenz wird bereitgetellt von <a href="https://quorato.de/" target="_blank" rel="noopener">quorato</a> mit der Software <a href="https://bigbluebutton.org/" target="_blank" rel="noopener">BigBlueButton</a>.',
						'tabs' => 'all',
						'toolbar' => 'basic',
						'media_upload' => 0,
						'delay' => 0,
					),
					array(
						'key' => 'field_5ed429e756aeb',
						'label' => 'moderatorOnlyMessage',
						'name' => 'bbb_moderatorOnlyMessage',
						'type' => 'wysiwyg',
						'instructions' => 'Anzeige einer Nachricht an alle Moderatoren im öffentlichen Chat.

Der Wert wird auf die gleiche Weise interpretiert wie der Begrüßungsparameter.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Sie sind Moderator dieser Konferenz.
Bitte holen Sie sich Feedback bei den Benutzern',
						'tabs' => 'all',
						'toolbar' => 'basic',
						'media_upload' => 0,
						'delay' => 0,
					),
					array(
						'key' => 'field_5ed42ce76b88f',
						'label' => 'dialNumber',
						'name' => 'bbb_dialNumber',
						'type' => 'select',
						'instructions' => 'Die Einwahlzugangsnummer, die die Teilnehmer mit einem normalen Telefon anrufen können. Sie können eine Standard-Wählnummer über defaultDialAccessNumber in bigbluebutton.properties festlegen',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'0211-74959805110' => '0211-74959805110',
							'0211-74959805111' => '0211-74959805111',
							'0211-74959805112' => '0211-74959805112',
						),
						'default_value' => '0211-74959805110',
						'allow_null' => 1,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'label',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed4283be8ed3',
						'label' => 'voiceBridge',
						'name' => 'bbb_voiceBridge',
						'type' => 'number',
						'instructions' => 'Nummer der Sprachkonferenz für die FreeSWITCH-Sprachkonferenz im Zusammenhang mit dieser Sitzung. Dies muss eine 5-stellige Nummer im Bereich 10000 bis 99999 sein. Wenn Sie Ihrem BigBlueButton-Server eine Telefonnummer hinzufügen, legt dieser Parameter die persönliche Identifikationsnummer (PIN) fest, zu deren Eingabe FreeSWITCH einen Nur-Telefon-Benutzer auffordert. Wenn Sie diesen Bereich ändern möchten, bearbeiten Sie den FreeSWITCH-Wahlplan und defaultNumDigitsForTelVoice von bigbluebutton.properties.

Die VoiceBridge-Nummer muss für jede Besprechung unterschiedlich sein.

Dieser Parameter ist optional. Wenn Sie keine voiceBridge-Nummer angeben, weist BigBlueButton eine zufällige, nicht verwendete Nummer für das Meeting zu.

Wenn Sie eine VoiceBridge-Nummer übergeben, dann müssen Sie sicherstellen, dass jede Besprechung eine eindeutige VoiceBridge-Nummer hat; andernfalls führt die Wiederverwendung derselben VoiceBridge-Nummer für zwei verschiedene Besprechungen dazu, dass Benutzer aus der einen Besprechung als Telefonbenutzer in der anderen Besprechung erscheinen, was für Benutzer in beiden Besprechungen sehr verwirrend ist.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => 10000,
						'max' => 99999,
						'step' => '',
					),
					array(
						'key' => 'field_5ed42855e8ed4',
						'label' => 'maxParticipants',
						'name' => 'bbb_maxParticipants',
						'type' => 'range',
						'instructions' => 'Legen Sie die maximale Anzahl von Benutzern fest, die gleichzeitig an der Konferenz teilnehmen dürfen.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 0,
						'min' => '',
						'max' => 50,
						'step' => '',
						'prepend' => '',
						'append' => '',
					),
					array(
						'key' => 'field_5ed4286fe8ed5',
						'label' => 'logoutURL',
						'name' => 'bbb_logoutURL',
						'type' => 'url',
						'instructions' => 'Die URL, die der BigBlueButton-Client aufruft, nachdem Benutzer auf die OK-Schaltfläche auf der \'Sie wurden abgemeldet\' Nachricht geklickt haben. Dadurch wird der Wert für bigbluebutton.web.logoutURL in bigbluebutton.properties überschrieben.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'https://meet.forcase.de/bbb-room/',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed42886e8ed6',
						'label' => 'record',
						'name' => 'bbb_record',
						'type' => 'true_false',
						'instructions' => 'Die Einstellung \'record=true\' weist den BigBlueButton-Server an, die Medien und Ereignisse in der Sitzung für die spätere Wiedergabe aufzuzeichnen. Die Voreinstellung ist falsch.

Damit eine Wiedergabedatei erzeugt werden kann, muss ein Moderator während der Sitzung mindestens einmal auf die Schaltfläche Start/Stop Recording klicken; andernfalls werden die Aufnahme- und Wiedergabeskripte, wenn keine Aufnahmemarkierungen vorhanden sind, keine Wiedergabedatei erzeugen. Siehe auch die Parameter autoStartRecording und allowStartStopRecording in bigbluebutton.properties.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42a0b56aec',
						'label' => 'autoStartRecording',
						'name' => 'bbb_autoStartRecording',
						'type' => 'true_false',
						'instructions' => 'Ob die Aufzeichnung automatisch gestartet werden soll, wenn der erste Benutzer beitritt (Voreinstellung falsch).

Wenn dieser Parameter wahr ist, wird die Aufnahme-UI in BigBlueButton anfänglich aktiv sein. Moderatoren in der Sitzung können die Aufzeichnung immer noch mit der UI-Steuerung pausieren und neu starten.<br/
HINWEIS: Übergeben Sie autoStartRecording=false nicht und erlauben SieStartStopRecording=false - der Moderator kann dann die Aufzeichnung nicht starten!',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42a2a56aed',
						'label' => 'allowStartStopRecording',
						'name' => 'bbb_allowStartStopRecording',
						'type' => 'true_false',
						'instructions' => 'Erlauben Sie dem Benutzer, die Aufzeichnung zu starten/stoppen. (Voreinstellung true)

Wenn Sie sowohl allowStartStopRecording=false als auch autoStartRecording=true setzen, dann wird die gesamte Länge der Sitzung aufgezeichnet, und die Moderatoren in der Sitzung können die Aufzeichnung nicht anhalten/fortsetzen.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4289be8ed7',
						'label' => 'MeetingDuration',
						'name' => 'bbb_duration',
						'type' => 'range',
						'instructions' => 'Die maximale Dauer (in Minuten) für die Sitzung.

Normalerweise beendet der BigBlueButton-Server die Besprechung, wenn entweder (a) die letzte Person die Besprechung verlässt (es dauert ein oder zwei Minuten, bis der Server die Besprechung aus dem Speicher löscht) oder wenn der Server eine End-API-Anforderung mit der zugehörigen MeetingID erhält (jeder wird gekickt und die Besprechung wird sofort aus dem Speicher gelöscht).

BigBlueButton beginnt mit der Verfolgung der Länge einer Besprechung, wenn diese erstellt wird. Wenn die Dauer einen Wert ungleich Null enthält, beendet der Server die Besprechung sofort, wenn die Länge der Besprechung den Wert der Dauer überschreitet (entspricht dem Empfang einer End-API-Anforderung zu diesem Zeitpunkt).',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 0,
						'min' => '',
						'max' => 180,
						'step' => 15,
						'prepend' => '',
						'append' => '',
					),
					array(
						'key' => 'field_5ed428e656ae6',
						'label' => 'isBreakout',
						'name' => 'bbb_isBreakout',
						'type' => 'true_false',
						'instructions' => 'Muss auf true gesetzt werden, um einen Breakout-Raum zu ermöglichen.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4290156ae7',
						'label' => 'parentMeetingID',
						'name' => 'bbb_parentMeetingID',
						'type' => 'text',
						'instructions' => 'Muss beim Erstellen eines Breakout-Raums angegeben werden, der Elternraum muss in Betrieb sein.',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed428e656ae6',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed4291d56ae8',
						'label' => 'sequence',
						'name' => 'bbb_sequence',
						'type' => 'number',
						'instructions' => 'Die laufende Nummer des Breakout-Raums.',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed428e656ae6',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed4293456ae9',
						'label' => 'freeJoin',
						'name' => 'bbb_freeJoin',
						'type' => 'true_false',
						'instructions' => 'Wenn auf true gesetzt, gibt der Client dem Benutzer die Möglichkeit, die Breakout-Räume auszuwählen, denen er beitreten möchte.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4294e56aea',
						'label' => 'meta_endCallbackUrl',
						'name' => 'bbb_meta_endCallbackUrl',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'https://meet.forcase.de/bbb-room/testraum/',
						'placeholder' => 'https://meet.forcase.de/',
					),
					array(
						'key' => 'field_5ed42a4156aee',
						'label' => 'webcamsOnlyForModerator',
						'name' => 'bbb_webcamsOnlyForModerator',
						'type' => 'true_false',
						'instructions' => 'Die Einstellung webcamsOnlyForModerator=true bewirkt, dass alle Webcams, die von den Zuschauern während dieses Meetings freigegeben werden, nur für Moderatoren angezeigt werden.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed66a12774b8',
						'label' => 'logo',
						'name' => 'bbb_logo',
						'type' => 'url',
						'instructions' => 'Die Einstellung logo=http://www.example.com/my-custom-logo.png ersetzt das Standardlogo im Flash-Client.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed66b26774b9',
						'label' => 'copyright',
						'name' => 'bbb_copyright',
						'type' => 'wysiwyg',
						'instructions' => 'Die Einstellung von copyright=Mein benutzerdefiniertes Copyright ersetzt das Standard-Copyright',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '(C) 2020 Quorato. Diese Service verwendet BigBlueButton und wird nicht von BigBlueButton Inc. unterstützt oder zertifiziert. BigBlueButton und das BigBlueButton-Logo sind Warenzeichen von BigBlueButton Inc.',
						'tabs' => 'all',
						'toolbar' => 'basic',
						'media_upload' => 0,
						'delay' => 0,
					),
					array(
						'key' => 'field_5ed42ac356af3',
						'label' => 'muteOnStart',
						'name' => 'bbb_muteOnStart',
						'type' => 'true_false',
						'instructions' => 'Durch die Einstellung muteOnStart=true werden alle Benutzer stumm geschaltet, wenn die Besprechung beginnt.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42ae37515e',
						'label' => 'allowModsToUnmuteUsers',
						'name' => 'bbb_allowModsToUnmuteUsers',
						'type' => 'true_false',
						'instructions' => 'Standardmäßig allowModsToUnmuteUsers=false. Wenn Sie allowModsToUnmuteUsers=true festlegen, können Moderatoren die Stummschaltung anderer Benutzer in der Besprechung aufheben.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42b0a7515f',
						'label' => 'lockSettingsDisableCam',
						'name' => 'bbb_lockSettingsDisableCam',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreEinstellungenDisableCam=falsch. Durch das Setzen von lockSettingsDisableCam=true wird verhindert, dass Benutzer ihre Kamera in der Besprechung freigeben.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42b2675160',
						'label' => 'lockSettingsDisableMic',
						'name' => 'bbb_lockSettingsDisableMic',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreSettingsDisableMic=falsch. Wenn Sie auf lockSettingsDisableMic=true setzen, können Benutzer nur zum Mithören beitreten.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42b4c75161',
						'label' => 'lockSettingsDisablePrivateChat',
						'name' => 'bbb_lockSettingsDisablePrivateChat',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreEinstellungenDisablePrivateChat=falsch. Wenn Sie die Einstellung auf lockSettingsDisablePrivateChat=true setzen, werden private Chats in der Besprechung deaktiviert.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42b6475162',
						'label' => 'lockSettingsDisablePublicChat',
						'name' => 'bbb_lockSettingsDisablePublicChat',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreEinstellungenDisablePublicChat=false. Durch die Einstellung von lockSettingsDisablePublicChat=true wird der öffentliche Chat in der Besprechung deaktiviert.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42b7975163',
						'label' => 'lockSettingsDisableNote',
						'name' => 'bbb_lockSettingsDisableNote',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreSettingsDisableNote=falsch. Die Einstellung von lockSettingsDisableNote=true deaktiviert Notizen in der Besprechung.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42b8d75164',
						'label' => 'lockSettingsLockedLayout',
						'name' => 'bbb_lockSettingsLockedLayout',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreSettingsLockedLayout=falsch. Wenn Sie auf lockSettingsLockedLayout=true setzen, wird das Layout in der Besprechung gesperrt.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42ba675165',
						'label' => 'lockSettingsLockOnJoin',
						'name' => 'bbb_lockSettingsLockOnJoin',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreEinstellungenLockOnJoin=true. Die Einstellung lockSettingsLockOnJoin=false wendet die Sperreinstellung nicht auf Benutzer an, wenn diese beitreten.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42bc175166',
						'label' => 'lockSettingsLockOnJoinConfigurable',
						'name' => 'bbb_lockSettingsLockOnJoinConfigurable',
						'type' => 'true_false',
						'instructions' => 'Standard-SperreEinstellungenLockOnJoinConfigurable=falsch. Wenn Sie auf lockSettingsLockOnJoinConfigurable=true setzen, können Sie den Parameter lockSettingsLockOnJoin anwenden.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed564626c918',
						'label' => 'hideuserList',
						'name' => 'bbb_hideuserList',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42bf975167',
						'label' => 'guestPolicy',
						'name' => 'bbb_guestPolicy',
						'type' => 'select',
						'instructions' => 'Standard-Gast-Richtlinie:
Legt die Gästerichtlinie für die Besprechung fest. Die Guest-Richtlinie legt fest, ob Benutzer, die eine Beitrittsanfrage mit guest=true senden, der Besprechung beitreten dürfen oder nicht.
Gültige Werte sind ALWAYS_ACCEPT, ALWAYS_DENY, ASK_MODERATOR.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'ALWAYS_ACCEPT' => 'ALWAYS_ACCEPT',
							'ALWAYS_DENY' => 'ALWAYS_DENY',
							'ASK_MODERATOR' => 'ASK_MODERATOR',
						),
						'default_value' => 'ALWAYS_ACCEPT',
						'allow_null' => 1,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'label',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed55d8f4fe20',
						'label' => 'meta',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed56084d6fd1',
						'label' => 'recording-ready-url',
						'name' => 'bbb_meta-recording-ready-url',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed563b58eacb',
						'label' => 'recording-name',
						'name' => 'bbb_meta-recording-name',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed563c88eacc',
						'label' => 'recording-description',
						'name' => 'bbb_meta-recording-description',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed563e38eacd',
						'label' => 'recording-tags',
						'name' => 'bbb_meta-recording-tags',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed55da04fe21',
						'label' => 'origin-version',
						'name' => 'bbb_meta_origin-version',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed563598eac7',
						'label' => 'origin-server-name',
						'name' => 'bbb_meta-origin-server-name',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed563738eac8',
						'label' => 'origin-server-common-name',
						'name' => 'bbb_meta-origin-server-common-name',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed5638d8eac9',
						'label' => 'origin-tag',
						'name' => 'bbb_meta-origin-tag',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed563a28eaca',
						'label' => 'context',
						'name' => 'bbb_meta-context',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed432df30fd2',
						'label' => 'Custom Configuration',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed43a825a47f',
						'label' => 'defaultWelcomeMessageFooter',
						'name' => 'bbb_cc_defaultWelcomeMessageFooter',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'This server is running <a href="http://docs.bigbluebutton.org/" target="_blank"><u>BigBlueButton</u></a>.',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 0,
						'delay' => 0,
					),
					array(
						'key' => 'field_5ed43af51bd5e',
						'label' => 'maxInactivityTimeoutMinutes',
						'name' => 'bbb_cc_maxInactivityTimeoutMinutes',
						'type' => 'number',
						'instructions' => 'Anzahl der verstrichenen Minuten ohne Aktivität. Beendigung der Sitzung. Standardwert Null (0) zum Deaktivieren der Prüfung.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 0,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43b181bd5f',
						'label' => 'clientLogoutTimerInMinutes',
						'name' => 'bbb_cc_clientLogoutTimerInMinutes',
						'type' => 'number',
						'instructions' => 'Anzahl der Minuten bis zum Abmelden des Clients, wenn der Benutzer nicht reagiert.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 0,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43b4272d43',
						'label' => 'warnMinutesBeforeMax',
						'name' => 'bbb_cc_warnMinutesBeforeMax',
						'type' => 'number',
						'instructions' => 'Senden Sie eine Warnung an die Moderatoren, um sie zu warnen, dass die Sitzung aufgrund von Inaktivität beendet wird.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 5,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43b5f72d44',
						'label' => 'meetingExpireIfNoUserJoinedInMinutes',
						'name' => 'bbb_cc_meetingExpireIfNoUserJoinedInMinutes',
						'type' => 'number',
						'instructions' => 'Besprechung beenden, wenn innerhalb eines Zeitraums nach der Erstellung der Besprechung kein Benutzer beigetreten ist.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 5,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43b7672d45',
						'label' => 'meetingExpireWhenLastUserLeftInMinutes',
						'name' => 'bbb_cc_meetingExpireWhenLastUserLeftInMinutes',
						'type' => 'number',
						'instructions' => 'Anzahl der Minuten bis zum Ende der Sitzung, wenn der letzte Benutzer die Sitzung verlassen hat.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 1,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43b8f72d46',
						'label' => 'userInactivityInspectTimerInMinutes',
						'name' => 'bbb_cc_userInactivityInspectTimerInMinutes',
						'type' => 'number',
						'instructions' => 'Benutzerinaktivitäts-Audit-Zeitgeberintervall.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 0,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43bb540b40',
						'label' => 'userInactivityThresholdInMinutes',
						'name' => 'bbb_cc_userInactivityThresholdInMinutes',
						'type' => 'number',
						'instructions' => 'Anzahl der Minuten, in denen ein Benutzer als inaktiv betrachtet wird. Warnmeldung an den Client senden, um zu prüfen, ob er wirklich inaktiv ist.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 30,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43bd040b41',
						'label' => 'userActivitySignResponseDelayInMinutes',
						'name' => 'bbb_cc_userActivitySignResponseDelayInMinutes',
						'type' => 'number',
						'instructions' => 'Anzahl der Minuten, die der Benutzer auf die Inaktivitätswarnung reagieren muss, bevor er abgemeldet wird.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 5,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_5ed43bef40b42',
						'label' => 'disableRecordingDefault',
						'name' => 'bbb_cc_disableRecordingDefault',
						'type' => 'true_false',
						'instructions' => 'Die Aufzeichnung ist standardmäßig deaktiviert:
- true: nicht aufzeichnen, auch wenn der Parameter record param im api-Aufruf auf Aufzeichnung eingestellt ist
- false: wenn ein Datensatz-Param von api übergeben wird, überschreiben Sie diese Vorgabe',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed43c1140b43',
						'label' => 'keepEvents',
						'name' => 'bbb_cc_keepEvents',
						'type' => 'true_false',
						'instructions' => 'Speichert Besprechungsereignisse, auch wenn die Besprechung nicht aufgezeichnet wird.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed434795dbb8',
						'label' => 'User Data',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed44b897aca2',
						'label' => 'ask_for_feedback_on_logout',
						'name' => 'bbb_ud_ask_for_feedback_on_logout',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed44c0634b20',
						'label' => 'auto_join_audio',
						'name' => 'bbb_ud_auto_join_audio',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed44c2734b21',
						'label' => 'client_title',
						'name' => 'bbb_ud_client_title',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'quorato',
						'placeholder' => 'quorato',
						'prepend' => '',
						'append' => '',
						'maxlength' => 10,
					),
					array(
						'key' => 'field_5ed44c63b7a00',
						'label' => 'force_listen_only',
						'name' => 'bbb_ud_force_listen_only',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed450b1b7a01',
						'label' => 'listen_only_mode',
						'name' => 'bbb_ud_listen_only_mode',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed45104de2e8',
						'label' => 'skip_check_audio',
						'name' => 'bbb_ud_skip_check_audio',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed45125de2e9',
						'label' => 'display_branding_area',
						'name' => 'bbb_ud_display_branding_area',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed45138de2ea',
						'label' => 'shortcuts',
						'name' => 'bbb_ud_shortcuts',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed45144de2eb',
						'label' => 'auto_share_webcam',
						'name' => 'bbb_ud_auto_share_webcam',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4515cde2ec',
						'label' => 'preferred_camera_profile',
						'name' => 'bbb_ud_preferred_camera_profile',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4516cde2ed',
						'label' => 'enable_screen_sharing',
						'name' => 'bbb_ud_enable_screen_sharing',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4517bde2ee',
						'label' => 'enable_video',
						'name' => 'bbb_ud_enable_video',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed45193de2ef',
						'label' => 'skip_video_preview',
						'name' => 'bbb_ud_skip_video_preview',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed451a3de2f0',
						'label' => 'multi_user_pen_only',
						'name' => 'bbb_ud_multi_user_pen_only',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed451b2de2f1',
						'label' => 'presenter_tools',
						'name' => 'bbb_ud_presenter_tools',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed451c0de2f2',
						'label' => 'multi_user_tools',
						'name' => 'bbb_ud_multi_user_tools',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed434865dbb9',
						'label' => 'custom_style',
						'name' => 'bbb_ud_custom_style',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed435145dbba',
						'label' => 'custom_style_url',
						'name' => 'bbb_ud_custom_style_url',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed434865dbb9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'https://forcase.de/BBB/Forcase.css',
						'placeholder' => 'https://forcase.de/BBB/Forcase.css',
					),
					array(
						'key' => 'field_5ed451e5de2f3',
						'label' => 'auto_swap_layout',
						'name' => 'bbb_ud_auto_swap_layout',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed451f8de2f4',
						'label' => 'hide_presentation',
						'name' => 'bbb_ud_hide_presentation',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4520bde2f5',
						'label' => 'show_participants_on_login',
						'name' => 'bbb_ud_show_participants_on_login',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4521cde2f6',
						'label' => 'outside_toggle_self_voice',
						'name' => 'bbb_ud_outside_toggle_self_voice',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed45229de2f7',
						'label' => 'outside_toggle_recording',
						'name' => 'bbb_ud_outside_toggle_recording',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed55d494fe1e',
						'label' => 'Join',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed55d674fe1f',
						'label' => 'join_guest',
						'name' => 'bbb_join_guest',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed43cccd87fd',
						'label' => 'Livestream',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed56309a5c11',
						'label' => 'enabled',
						'name' => 'bbb_ls_enabled',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed56e21fd5ad',
						'label' => 'livestreamPW',
						'name' => 'bbb_ls_livestreamPW',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed56309a5c11',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed56eb5fd5ae',
						'label' => 'livestream URL',
						'name' => 'bbb_ls_livestream-URL',
						'type' => 'url',
						'instructions' => 'Die URL des Livestreaming-Servers zum Einbinden in den Player.',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed56309a5c11',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed454867d6c3',
						'label' => 'timezone',
						'name' => 'bbb_ls_TZ',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed56309a5c11',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'Africa/Abidjan' => 'Africa/Abidjan',
							'Africa/Accra' => 'Africa/Accra',
							'Africa/Algiers' => 'Africa/Algiers',
							'Africa/Bissau' => 'Africa/Bissau',
							'Africa/Cairo' => 'Africa/Cairo',
							'Africa/Casablanca' => 'Africa/Casablanca',
							'Africa/Ceuta' => 'Africa/Ceuta',
							'Africa/El_Aaiun' => 'Africa/El_Aaiun',
							'Africa/Johannesburg' => 'Africa/Johannesburg',
							'Africa/Juba' => 'Africa/Juba',
							'Africa/Khartoum' => 'Africa/Khartoum',
							'Africa/Lagos' => 'Africa/Lagos',
							'Africa/Maputo' => 'Africa/Maputo',
							'Africa/Monrovia' => 'Africa/Monrovia',
							'Africa/Nairobi' => 'Africa/Nairobi',
							'Africa/Ndjamena' => 'Africa/Ndjamena',
							'Africa/Tripoli' => 'Africa/Tripoli',
							'Africa/Tunis' => 'Africa/Tunis',
							'Africa/Windhoek' => 'Africa/Windhoek',
							'America/Adak' => 'America/Adak',
							'America/Anchorage' => 'America/Anchorage',
							'America/Araguaina' => 'America/Araguaina',
							'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
							'America/Argentina/Catamarca' => 'America/Argentina/Catamarca',
							'America/Argentina/Cordoba' => 'America/Argentina/Cordoba',
							'America/Argentina/Jujuy' => 'America/Argentina/Jujuy',
							'America/Argentina/La_Rioja' => 'America/Argentina/La_Rioja',
							'America/Argentina/Mendoza' => 'America/Argentina/Mendoza',
							'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio_Gallegos',
							'America/Argentina/Salta' => 'America/Argentina/Salta',
							'America/Argentina/San_Juan' => 'America/Argentina/San_Juan',
							'America/Argentina/San_Luis' => 'America/Argentina/San_Luis',
							'America/Argentina/Tucuman' => 'America/Argentina/Tucuman',
							'America/Argentina/Ushuaia' => 'America/Argentina/Ushuaia',
							'America/Asuncion' => 'America/Asuncion',
							'America/Atikokan' => 'America/Atikokan',
							'America/Bahia' => 'America/Bahia',
							'America/Bahia_Banderas' => 'America/Bahia_Banderas',
							'America/Barbados' => 'America/Barbados',
							'America/Belem' => 'America/Belem',
							'America/Belize' => 'America/Belize',
							'America/Blanc-Sablon' => 'America/Blanc-Sablon',
							'America/Boa_Vista' => 'America/Boa_Vista',
							'America/Bogota' => 'America/Bogota',
							'America/Boise' => 'America/Boise',
							'America/Cambridge_Bay' => 'America/Cambridge_Bay',
							'America/Campo_Grande' => 'America/Campo_Grande',
							'America/Cancun' => 'America/Cancun',
							'America/Caracas' => 'America/Caracas',
							'America/Cayenne' => 'America/Cayenne',
							'America/Chicago' => 'America/Chicago',
							'America/Chihuahua' => 'America/Chihuahua',
							'America/Costa_Rica' => 'America/Costa_Rica',
							'America/Creston' => 'America/Creston',
							'America/Cuiaba' => 'America/Cuiaba',
							'America/Curacao' => 'America/Curacao',
							'America/Danmarkshavn' => 'America/Danmarkshavn',
							'America/Dawson' => 'America/Dawson',
							'America/Dawson_Creek' => 'America/Dawson_Creek',
							'America/Denver' => 'America/Denver',
							'America/Detroit' => 'America/Detroit',
							'America/Edmonton' => 'America/Edmonton',
							'America/Eirunepe' => 'America/Eirunepe',
							'America/El_Salvador' => 'America/El_Salvador',
							'America/Fort_Nelson' => 'America/Fort_Nelson',
							'America/Fortaleza' => 'America/Fortaleza',
							'America/Glace_Bay' => 'America/Glace_Bay',
							'America/Godthab' => 'America/Godthab',
							'America/Goose_Bay' => 'America/Goose_Bay',
							'America/Grand_Turk' => 'America/Grand_Turk',
							'America/Guatemala' => 'America/Guatemala',
							'America/Guayaquil' => 'America/Guayaquil',
							'America/Guyana' => 'America/Guyana',
							'America/Halifax' => 'America/Halifax',
							'America/Havana' => 'America/Havana',
							'America/Hermosillo' => 'America/Hermosillo',
							'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
							'America/Indiana/Knox' => 'America/Indiana/Knox',
							'America/Indiana/Marengo' => 'America/Indiana/Marengo',
							'America/Indiana/Petersburg' => 'America/Indiana/Petersburg',
							'America/Indiana/Tell_City' => 'America/Indiana/Tell_City',
							'America/Indiana/Vevay' => 'America/Indiana/Vevay',
							'America/Indiana/Vincennes' => 'America/Indiana/Vincennes',
							'America/Indiana/Winamac' => 'America/Indiana/Winamac',
							'America/Inuvik' => 'America/Inuvik',
							'America/Iqaluit' => 'America/Iqaluit',
							'America/Jamaica' => 'America/Jamaica',
							'America/Juneau' => 'America/Juneau',
							'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
							'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
							'America/La_Paz' => 'America/La_Paz',
							'America/Lima' => 'America/Lima',
							'America/Los_Angeles' => 'America/Los_Angeles',
							'America/Maceio' => 'America/Maceio',
							'America/Managua' => 'America/Managua',
							'America/Manaus' => 'America/Manaus',
							'America/Martinique' => 'America/Martinique',
							'America/Matamoros' => 'America/Matamoros',
							'America/Mazatlan' => 'America/Mazatlan',
							'America/Menominee' => 'America/Menominee',
							'America/Merida' => 'America/Merida',
							'America/Metlakatla' => 'America/Metlakatla',
							'America/Mexico_City' => 'America/Mexico_City',
							'America/Miquelon' => 'America/Miquelon',
							'America/Moncton' => 'America/Moncton',
							'America/Monterrey' => 'America/Monterrey',
							'America/Montevideo' => 'America/Montevideo',
							'America/Nassau' => 'America/Nassau',
							'America/New_York' => 'America/New_York',
							'America/Nipigon' => 'America/Nipigon',
							'America/Nome' => 'America/Nome',
							'America/Noronha' => 'America/Noronha',
							'America/North_Dakota/Beulah' => 'America/North_Dakota/Beulah',
							'America/North_Dakota/Center' => 'America/North_Dakota/Center',
							'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem',
							'America/Ojinaga' => 'America/Ojinaga',
							'America/Panama' => 'America/Panama',
							'America/Pangnirtung' => 'America/Pangnirtung',
							'America/Paramaribo' => 'America/Paramaribo',
							'America/Phoenix' => 'America/Phoenix',
							'America/Port_of_Spain' => 'America/Port_of_Spain',
							'America/Port-au-Prince' => 'America/Port-au-Prince',
							'America/Porto_Velho' => 'America/Porto_Velho',
							'America/Puerto_Rico' => 'America/Puerto_Rico',
							'America/Rainy_River' => 'America/Rainy_River',
							'America/Rankin_Inlet' => 'America/Rankin_Inlet',
							'America/Recife' => 'America/Recife',
							'America/Regina' => 'America/Regina',
							'America/Resolute' => 'America/Resolute',
							'America/Rio_Branco' => 'America/Rio_Branco',
							'America/Santarem' => 'America/Santarem',
							'America/Santiago' => 'America/Santiago',
							'America/Santo_Domingo' => 'America/Santo_Domingo',
							'America/Sao_Paulo' => 'America/Sao_Paulo',
							'America/Scoresbysund' => 'America/Scoresbysund',
							'America/Sitka' => 'America/Sitka',
							'America/St_Johns' => 'America/St_Johns',
							'America/Swift_Current' => 'America/Swift_Current',
							'America/Tegucigalpa' => 'America/Tegucigalpa',
							'America/Thule' => 'America/Thule',
							'America/Thunder_Bay' => 'America/Thunder_Bay',
							'America/Tijuana' => 'America/Tijuana',
							'America/Toronto' => 'America/Toronto',
							'America/Vancouver' => 'America/Vancouver',
							'America/Whitehorse' => 'America/Whitehorse',
							'America/Winnipeg' => 'America/Winnipeg',
							'America/Yakutat' => 'America/Yakutat',
							'America/Yellowknife' => 'America/Yellowknife',
							'Antarctica/Casey' => 'Antarctica/Casey',
							'Antarctica/Davis' => 'Antarctica/Davis',
							'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
							'Antarctica/Macquarie' => 'Antarctica/Macquarie',
							'Antarctica/Mawson' => 'Antarctica/Mawson',
							'Antarctica/Rothera' => 'Antarctica/Rothera',
							'Antarctica/Syowa' => 'Antarctica/Syowa',
							'Antarctica/Vostok' => 'Antarctica/Vostok',
							'Asia/Almaty' => 'Asia/Almaty',
							'Asia/Amman' => 'Asia/Amman',
							'Asia/Anadyr' => 'Asia/Anadyr',
							'Asia/Aqtau' => 'Asia/Aqtau',
							'Asia/Aqtobe' => 'Asia/Aqtobe',
							'Asia/Ashgabat' => 'Asia/Ashgabat',
							'Asia/Atyrau' => 'Asia/Atyrau',
							'Asia/Baghdad' => 'Asia/Baghdad',
							'Asia/Baku' => 'Asia/Baku',
							'Asia/Bangkok' => 'Asia/Bangkok',
							'Asia/Barnaul' => 'Asia/Barnaul',
							'Asia/Beirut' => 'Asia/Beirut',
							'Asia/Bishkek' => 'Asia/Bishkek',
							'Asia/Brunei' => 'Asia/Brunei',
							'Asia/Chita' => 'Asia/Chita',
							'Asia/Choibalsan' => 'Asia/Choibalsan',
							'Asia/Colombo' => 'Asia/Colombo',
							'Asia/Damascus' => 'Asia/Damascus',
							'Asia/Dhaka' => 'Asia/Dhaka',
							'Asia/Dili' => 'Asia/Dili',
							'Asia/Dubai' => 'Asia/Dubai',
							'Asia/Dushanbe' => 'Asia/Dushanbe',
							'Asia/Famagusta' => 'Asia/Famagusta',
							'Asia/Gaza' => 'Asia/Gaza',
							'Asia/Hebron' => 'Asia/Hebron',
							'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh',
							'Asia/Hong_Kong' => 'Asia/Hong_Kong',
							'Asia/Hovd' => 'Asia/Hovd',
							'Asia/Irkutsk' => 'Asia/Irkutsk',
							'Asia/Jakarta' => 'Asia/Jakarta',
							'Asia/Jayapura' => 'Asia/Jayapura',
							'Asia/Jerusalem' => 'Asia/Jerusalem',
							'Asia/Kabul' => 'Asia/Kabul',
							'Asia/Kamchatka' => 'Asia/Kamchatka',
							'Asia/Karachi' => 'Asia/Karachi',
							'Asia/Kathmandu' => 'Asia/Kathmandu',
							'Asia/Khandyga' => 'Asia/Khandyga',
							'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk',
							'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
							'Asia/Kuching' => 'Asia/Kuching',
							'Asia/Macau' => 'Asia/Macau',
							'Asia/Magadan' => 'Asia/Magadan',
							'Asia/Makassar' => 'Asia/Makassar',
							'Asia/Manila' => 'Asia/Manila',
							'Asia/Novokuznetsk' => 'Asia/Novokuznetsk',
							'Asia/Novosibirsk' => 'Asia/Novosibirsk',
							'Asia/Omsk' => 'Asia/Omsk',
							'Asia/Oral' => 'Asia/Oral',
							'Asia/Pontianak' => 'Asia/Pontianak',
							'Asia/Pyongyang' => 'Asia/Pyongyang',
							'Asia/Qatar' => 'Asia/Qatar',
							'Asia/Qyzylorda' => 'Asia/Qyzylorda',
							'Asia/Riyadh' => 'Asia/Riyadh',
							'Asia/Sakhalin' => 'Asia/Sakhalin',
							'Asia/Samarkand' => 'Asia/Samarkand',
							'Asia/Seoul' => 'Asia/Seoul',
							'Asia/Shanghai' => 'Asia/Shanghai',
							'Asia/Singapore' => 'Asia/Singapore',
							'Asia/Srednekolymsk' => 'Asia/Srednekolymsk',
							'Asia/Taipei' => 'Asia/Taipei',
							'Asia/Tashkent' => 'Asia/Tashkent',
							'Asia/Tbilisi' => 'Asia/Tbilisi',
							'Asia/Tehran' => 'Asia/Tehran',
							'Asia/Thimphu' => 'Asia/Thimphu',
							'Asia/Tokyo' => 'Asia/Tokyo',
							'Asia/Tomsk' => 'Asia/Tomsk',
							'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
							'Asia/Ust-Nera' => 'Asia/Ust-Nera',
							'Asia/Vladivostok' => 'Asia/Vladivostok',
							'Asia/Yakutsk' => 'Asia/Yakutsk',
							'Asia/Yangon' => 'Asia/Yangon',
							'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
							'Asia/Yerevan' => 'Asia/Yerevan',
							'Atlantic/Azores' => 'Atlantic/Azores',
							'Atlantic/Bermuda' => 'Atlantic/Bermuda',
							'Atlantic/Canary' => 'Atlantic/Canary',
							'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
							'Atlantic/Faroe' => 'Atlantic/Faroe',
							'Atlantic/Madeira' => 'Atlantic/Madeira',
							'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
							'Atlantic/South_Georgia' => 'Atlantic/South_Georgia',
							'Atlantic/Stanley' => 'Atlantic/Stanley',
							'Australia/Adelaide' => 'Australia/Adelaide',
							'Australia/Brisbane' => 'Australia/Brisbane',
							'Australia/Broken_Hill' => 'Australia/Broken_Hill',
							'Australia/Currie' => 'Australia/Currie',
							'Australia/Darwin' => 'Australia/Darwin',
							'Australia/Eucla' => 'Australia/Eucla',
							'Australia/Hobart' => 'Australia/Hobart',
							'Australia/Lindeman' => 'Australia/Lindeman',
							'Australia/Melbourne' => 'Australia/Melbourne',
							'Australia/Perth' => 'Australia/Perth',
							'Australia/Sydney' => 'Australia/Sydney',
							'Europe/Amsterdam' => 'Europe/Amsterdam',
							'Europe/Andorra' => 'Europe/Andorra',
							'Europe/Astrakhan' => 'Europe/Astrakhan',
							'Europe/Athens' => 'Europe/Athens',
							'Europe/Belgrade' => 'Europe/Belgrade',
							'Europe/Berlin' => 'Europe/Berlin',
							'Europe/Brussels' => 'Europe/Brussels',
							'Europe/Bucharest' => 'Europe/Bucharest',
							'Europe/Budapest' => 'Europe/Budapest',
							'Europe/Chisinau' => 'Europe/Chisinau',
							'Europe/Copenhagen' => 'Europe/Copenhagen',
							'Europe/Dublin' => 'Europe/Dublin',
							'Europe/Gibraltar' => 'Europe/Gibraltar',
							'Europe/Helsinki' => 'Europe/Helsinki',
							'Europe/Istanbul' => 'Europe/Istanbul',
							'Europe/Kaliningrad' => 'Europe/Kaliningrad',
							'Europe/Kiev' => 'Europe/Kiev',
							'Europe/Kirov' => 'Europe/Kirov',
							'Europe/Lisbon' => 'Europe/Lisbon',
							'Europe/London' => 'Europe/London',
							'Europe/Luxembourg' => 'Europe/Luxembourg',
							'Europe/Madrid' => 'Europe/Madrid',
							'Europe/Malta' => 'Europe/Malta',
							'Europe/Minsk' => 'Europe/Minsk',
							'Europe/Monaco' => 'Europe/Monaco',
							'Europe/Moscow' => 'Europe/Moscow',
							'Asia/Nicosia' => 'Asia/Nicosia',
							'Europe/Oslo' => 'Europe/Oslo',
							'Europe/Paris' => 'Europe/Paris',
							'Europe/Prague' => 'Europe/Prague',
							'Europe/Riga' => 'Europe/Riga',
							'Europe/Rome' => 'Europe/Rome',
							'Europe/Samara' => 'Europe/Samara',
							'Europe/Saratov' => 'Europe/Saratov',
							'Europe/Sofia' => 'Europe/Sofia',
							'Europe/Stockholm' => 'Europe/Stockholm',
							'Europe/Tallinn' => 'Europe/Tallinn',
							'Europe/Tirane' => 'Europe/Tirane',
							'Europe/Ulyanovsk' => 'Europe/Ulyanovsk',
							'Europe/Uzhgorod' => 'Europe/Uzhgorod',
							'Europe/Vienna' => 'Europe/Vienna',
							'Europe/Vilnius' => 'Europe/Vilnius',
							'Europe/Volgograd' => 'Europe/Volgograd',
							'Europe/Warsaw' => 'Europe/Warsaw',
							'Europe/Zaporozhye' => 'Europe/Zaporozhye',
							'Europe/Zurich' => 'Europe/Zurich',
							'Indian/Chagos' => 'Indian/Chagos',
							'Indian/Christmas' => 'Indian/Christmas',
							'Indian/Cocos' => 'Indian/Cocos',
							'Indian/Kerguelen' => 'Indian/Kerguelen',
							'Indian/Mahe' => 'Indian/Mahe',
							'Indian/Maldives' => 'Indian/Maldives',
							'Indian/Mauritius' => 'Indian/Mauritius',
							'Indian/Reunion' => 'Indian/Reunion',
							'Pacific/Apia' => 'Pacific/Apia',
							'Pacific/Auckland' => 'Pacific/Auckland',
							'Pacific/Bougainville' => 'Pacific/Bougainville',
							'Pacific/Chatham' => 'Pacific/Chatham',
							'Pacific/Chuuk' => 'Pacific/Chuuk',
							'Pacific/Easter' => 'Pacific/Easter',
							'Pacific/Efate' => 'Pacific/Efate',
							'Pacific/Enderbury' => 'Pacific/Enderbury',
							'Pacific/Fakaofo' => 'Pacific/Fakaofo',
							'Pacific/Fiji' => 'Pacific/Fiji',
							'Pacific/Funafuti' => 'Pacific/Funafuti',
							'Pacific/Galapagos' => 'Pacific/Galapagos',
							'Pacific/Gambier' => 'Pacific/Gambier',
							'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
							'Pacific/Guam' => 'Pacific/Guam',
							'Pacific/Honolulu' => 'Pacific/Honolulu',
							'Pacific/Kiritimati' => 'Pacific/Kiritimati',
							'Pacific/Kosrae' => 'Pacific/Kosrae',
							'Pacific/Kwajalein' => 'Pacific/Kwajalein',
							'Pacific/Majuro' => 'Pacific/Majuro',
							'Pacific/Marquesas' => 'Pacific/Marquesas',
							'Pacific/Nauru' => 'Pacific/Nauru',
							'Pacific/Niue' => 'Pacific/Niue',
							'Pacific/Norfolk' => 'Pacific/Norfolk',
							'Pacific/Noumea' => 'Pacific/Noumea',
							'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
							'Pacific/Palau' => 'Pacific/Palau',
							'Pacific/Pitcairn' => 'Pacific/Pitcairn',
							'Pacific/Pohnpei' => 'Pacific/Pohnpei',
							'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
							'Pacific/Rarotonga' => 'Pacific/Rarotonga',
							'Pacific/Tahiti' => 'Pacific/Tahiti',
							'Pacific/Tarawa' => 'Pacific/Tarawa',
							'Pacific/Tongatapu' => 'Pacific/Tongatapu',
							'Pacific/Wake' => 'Pacific/Wake',
							'Pacific/Wallis' => 'Pacific/Wallis',
						),
						'default_value' => 'Europe/Berlin',
						'allow_null' => 1,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'label',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed568aa93e9e',
						'label' => 'GDPR-policies',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placement' => 'top',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5ed56a4ed3880',
						'label' => 'privacy-policy_required',
						'name' => 'bbb_wp_privacy-policy_required',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed568c9d387d',
						'label' => 'privacy-policy',
						'name' => 'bbb_wp_privacy-policy',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed56a4ed3880',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Hiermit akzeptiere ich die Datenschutzrichtline.',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 0,
						'delay' => 0,
					),
					array(
						'key' => 'field_5ed56a8cd3881',
						'label' => 'recording-policy_required',
						'name' => 'bbb_wp_recording-policy_required',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed568fed387e',
						'label' => 'recording-policy',
						'name' => 'bbb_wp_recording-policy',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed56a8cd3881',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Hiermit akzeptiere ich die Aufnahme-Richtlinie.',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 0,
						'delay' => 0,
					),
					array(
						'key' => 'field_5ed56abdd3882',
						'label' => 'livestream-policy_required',
						'name' => 'bbb_wp_livestream-policy_required',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed56913d387f',
						'label' => 'livestream-policy',
						'name' => 'bbb_wp_livestream-policy',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5ed56abdd3882',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Hiermit akzeptiere ich die Livestreaming-Richtlinie.',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 0,
						'delay' => 0,
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'bbb-room',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			);

			$colors = [
				'--color-white' => '#FFF',
				'--color-off-white' => '#eaeef1',
				'--color-black' => '#000000',
				'--color-gray' => '#000100',
				'--color-gray-dark' => '#000100',
				'--color-gray-light' => '#7e8588',
				'--color-gray-darkest' => '#0e0f0a',
				'--color-gray-lighter' => '#be1d28',
				'--color-gray-lightest' => '#d4d7da',
				'--color-blue-light' => '#609ed5',
				'--color-blue-lighter' => '#a4c4e4',
				'--color-blue-lightest' => '#eaeff4',
				'--color-primary' => '#be1d28',
				'--color-success' => '#1e8252',
				'--color-danger' => '#d40f14',
				'--color-warning' => '#70247f',
				'--color-link-hover' => '#457abd',
				'--color-transparent' => 'transparent',
				'--color-white-with-transparency' => '#ffffff40',
				'--toolbar-list-bg' => '#d8d8da',
				'--toolbar-list-bg-focus' => '#c8ccd0',
				'--poll-annotation-gray' => '#2d2e2b',
				'--list-item-bg-hover' => '#dce4ed',
				'--poll-blue' => '#306db3',
				'--poll-stats-border-color' => '#d4d7da',
				'--systemMessage-background-color' => '#f9f9fa',
				'--systemMessage-border-color' => '#c5cace',
				'--background-active' => '#eaeaeb',
			];

			$fields['fields'][] = array(
				'key' => 'field_bbb_colors_tab',
				'label' => 'White Label',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			);

			foreach ($colors as $key => $default_value) {
				$fields['fields'][] = array(
					'key' => 'field_bbb' . $key,
					'label' => $key,
					'name' => 'bbb_' . $key,
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => $default_value,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				);
			}

			acf_add_local_field_group($fields);

		endif;
	}
}

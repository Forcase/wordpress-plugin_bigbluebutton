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
class Bigbluebutton_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   3.0.0
	 * @param   String $plugin_name       The name of this plugin.
	 * @param   String $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bigbluebutton-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_scripts() {

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
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bigbluebutton-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'php_vars', $translations );
	}

	/**
	 * Add Rooms as its own menu item on the admin page.
	 *
	 * @since   3.0.0
	 */
	public function create_admin_menu() {
		add_menu_page(
			__( 'Rooms', 'bigbluebutton' ), __( 'Rooms', 'bigbluebutton' ), 'activate_plugins', 'bbb_room',
			'', 'dashicons-video-alt2'
		);

		if ( current_user_can( 'manage_categories' ) ) {
			add_submenu_page(
				'bbb_room', __( 'Rooms', 'bigbluebutton' ), __( 'Categories' ), 'activate_plugins',
				'edit-tags.php?taxonomy=bbb-room-category', ''
			);
		}

		add_submenu_page(
			'bbb_room', __( 'Rooms', 'bigbluebutton' ), __( 'Settings' ), 'activate_plugins',
			'bbb-room-server-settings', array( $this, 'display_room_server_settings' )
		);
	}

	/**
	 * Add filter to highlight custom menu category submenu.
	 *
	 * @since   3.0.0
	 *
	 * @param   String $parent_file    Current parent page that the user is on.
	 * @return  String $parent_file    Custom menu slug.
	 */
	public function bbb_set_current_menu( $parent_file ) {
		global $submenu_file, $current_screen, $pagenow;

		// Set the submenu as active/current while anywhere in your Custom Post Type.
		if ( 'bbb-room-category' == $current_screen->taxonomy && 'edit-tags.php' == $pagenow ) {
			$submenu_file = 'edit-tags.php?taxonomy=bbb-room-category';
			$parent_file  = 'bbb_room';
		}
		return $parent_file;
	}

	/**
	 * Add custom room column headers to rooms list table.
	 *
	 * @since   3.0.0
	 *
	 * @param   Array $columns    Array of existing column headers.
	 * @return  Array $columns    Array of existing column headers and custom column headers.
	 */
	public function add_custom_room_column_to_list( $columns ) {
		$custom_columns = array(
			'category'       => __( 'Category' ),
			'permalink'      => __( 'Permalink' ),
			'token'          => __( 'Token', 'bigbluebutton' ),
			'moderator-code' => __( 'Moderator Code', 'bigbluebutton' ),
			'viewer-code'    => __( 'Viewer Code', 'bigbluebutton' ),
		);

		$columns = array_merge( $columns, $custom_columns );

		return $columns;
	}

	/**
	 * Fill in custom column information on rooms list table.
	 *
	 * @since 3.0.0
	 *
	 * @param   String  $column     Name of the column.
	 * @param   Integer $post_id    Room ID of the current room.
	 */
	public function bbb_room_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'category':
				$categories = wp_get_object_terms( $post_id, 'bbb-room-category', array( 'fields' => 'names' ) );
				if ( ! is_wp_error( $categories ) ) {
					echo esc_attr( implode( ', ', $categories ) );
				}
				break;
			case 'permalink':
				$permalink = ( get_permalink( $post_id ) ? get_permalink( $post_id ) : '' );
				echo '<a href="' . esc_url( $permalink ) . '" target="_blank">' . esc_url( $permalink ) . '</a>';
				break;
			case 'token':
				if ( metadata_exists( 'post', $post_id, 'bbb-room-token' ) ) {
					$token = get_post_meta( $post_id, 'bbb-room-token', true );
				} else {
					$token = 'z' . esc_attr( $post_id );
				}
				echo esc_attr( $token );
				break;
			case 'moderator-code':
				echo esc_attr( get_post_meta( $post_id, 'bbb-room-moderator-code', true ) );
				break;
			case 'viewer-code':
				echo esc_attr( get_post_meta( $post_id, 'bbb-room-viewer-code', true ) );
				break;
		}
	}

	/**
	 * Render the server settings page for plugin.
	 *
	 * @since   3.0.0
	 */
	public function display_room_server_settings() {
		$change_success = $this->room_server_settings_change();
		$bbb_settings   = $this->fetch_room_server_settings();
		$meta_nonce     = wp_create_nonce( 'bbb_edit_server_settings_meta_nonce' );
		require_once 'partials/bigbluebutton-settings-display.php';
	}

	/**
	 * Retrieve the room server settings.
	 *
	 * @since   3.0.0
	 *
	 * @return  Array   $settings   Room server default and current settings.
	 */
	public function fetch_room_server_settings() {
		$settings = array(
			'bbb_url'          => get_option( 'bigbluebutton_url', 'http://test-install.blindsidenetworks.com/bigbluebutton/' ),
			'bbb_salt'         => get_option( 'bigbluebutton_salt', '8cd8ef52e8e101574e400365b55e11a6' ),
			'bbb_default_url'  => 'http://test-install.blindsidenetworks.com/bigbluebutton/',
			'bbb_default_salt' => '8cd8ef52e8e101574e400365b55e11a6',
		);

		return $settings;
	}

	/**
	 * Show information about new plugin updates.
	 *
	 * @since   1.4.6
	 *
	 * @param   Array  $current_plugin_metadata    The plugin metadata of the current version of the plugin.
	 * @param   Object $new_plugin_metadata        The plugin metadata of the new version of the plugin.
	 */
	public function bigbluebutton_show_upgrade_notification( $current_plugin_metadata, $new_plugin_metadata = null ) {
		if ( ! $new_plugin_metadata ) {
			$new_plugin_metadata = $this->bigbluebutton_update_metadata( $current_plugin_metadata['slug'] );
		}
		// Check "upgrade_notice".
		if ( isset( $new_plugin_metadata->upgrade_notice ) && strlen( trim( $new_plugin_metadata->upgrade_notice ) ) > 0 ) {
			echo '<div style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice:</strong> ';
			echo esc_html( strip_tags( $new_plugin_metadata->upgrade_notice ) ), '</div>';
		}
	}

	/**
	 * Get information about the newest plugin version.
	 *
	 * @since   1.4.6
	 *
	 * @param   String $plugin_slug            The slug of the old plugin version.
	 * @return  Object $new_plugin_metadata    The metadata of the new plugin version.
	 */
	private function bigbluebutton_update_metadata( $plugin_slug ) {
		$plugin_updates = get_plugin_updates();
		foreach ( $plugin_updates as $update ) {
			if ( $update->update->slug === $plugin_slug ) {
				return $update->update;
			}
		}
	}

	/**
	 * Check for room server settings change requests.
	 *
	 * @since   3.0.0
	 *
	 * @return  Integer 1|2|3   If the room servers have been changed or not.
	 *                          0 - failure
	 *                          1 - success
	 *                          2 - bad url format
	 *                          3 - bad bigbluebutton settings configuration
	 */
	private function room_server_settings_change() {
		if ( ! empty( $_POST['action'] ) && 'bbb_general_settings' == $_POST['action'] && wp_verify_nonce( sanitize_text_field( $_POST['bbb_edit_server_settings_meta_nonce'] ), 'bbb_edit_server_settings_meta_nonce' ) ) {
			$bbb_url  = sanitize_text_field( $_POST['bbb_url'] );
			$bbb_salt = sanitize_text_field( $_POST['bbb_salt'] );

			$bbb_url .= ( substr( $bbb_url, -1 ) == '/' ? '' : '/' );

			if ( ! Bigbluebutton_Api::test_bigbluebutton_server( $bbb_url, $bbb_salt ) ) {
				return 3;
			}

			if ( substr_compare( $bbb_url, 'bigbluebutton/', strlen( $bbb_url ) - 14 ) !== 0 ) {
				return 2;
			}

			update_option( 'bigbluebutton_url', $bbb_url );
			update_option( 'bigbluebutton_salt', $bbb_salt );

			return 1;
		}
		return 0;
	}

	/**
	 * Generate missing heartbeat API if missing.
	 *
	 * @since   3.0.0
	 */
	public function check_for_heartbeat_script() {
		$bbb_warning_type = 'bbb-missing-heartbeat-api-notice';
		if ( ! wp_script_is( 'heartbeat', 'registered' ) && ! get_option( 'dismissed-' . $bbb_warning_type, false ) ) {
			$bbb_admin_warning_message = __( 'BigBlueButton works best with the heartbeat API enabled. Please enable it.', 'bigbluebutton' );
			$bbb_admin_notice_nonce    = wp_create_nonce( $bbb_warning_type );
			require 'partials/bigbluebutton-warning-admin-notice-display.php';
		}
	}

	/**
	 * Hide others rooms if user does not have permission to edit them.
	 *
	 * @since  3.0.0
	 *
	 * @param  Object $query   Query so far.
	 * @return Object $query   Query for rooms.
	 */
	public function filter_rooms_list( $query ) {
		global $pagenow;

		if ( 'edit.php' != $pagenow || ! $query->is_admin || 'bbb-room' != $query->query_vars['post_type'] ) {
			return $query;
		}

		if ( ! current_user_can( 'edit_others_bbb_rooms' ) ) {
			$query->set( 'author', get_current_user_id() );
		}
		return $query;
	}

	public function add_acf_fields()
	{
		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
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
						'key' => 'field_5ed42c99900ad',
						'label' => 'welcome',
						'name' => 'bbb_welcome',
						'type' => 'textarea',
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
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => '',
						'new_lines' => '',
					),
					array(
						'key' => 'field_5ed42ce76b88f',
						'label' => 'dialNumber',
						'name' => 'bbb_dialNumber',
						'type' => 'text',
						'instructions' => 'Die Einwahlzugangsnummer, die die Teilnehmer mit einem normalen Telefon anrufen können. Sie können eine Standard-Wählnummer über defaultDialAccessNumber in bigbluebutton.properties festlegen',
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
						'key' => 'field_5ed4283be8ed3',
						'label' => 'voiceBridge',
						'name' => 'bbb_voiceBridge',
						'type' => 'text',
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
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ed42855e8ed4',
						'label' => 'maxParticipants',
						'name' => 'bbb_maxParticipants',
						'type' => 'number',
						'instructions' => 'Legen Sie die maximale Anzahl von Benutzern fest, die gleichzeitig an der Konferenz teilnehmen dürfen.',
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
						'min' => '',
						'max' => '',
						'step' => '',
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
						'default_value' => '',
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
						'default_value' => 0,
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4289be8ed7',
						'label' => 'duration',
						'name' => 'bbb_duration',
						'type' => 'number',
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
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
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
						'ui' => 0,
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
						'key' => 'field_5ed4291d56ae8',
						'label' => 'sequence',
						'name' => 'bbb_sequence',
						'type' => 'number',
						'instructions' => 'Die laufende Nummer des Breakout-Raums.',
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
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed4294e56aea',
						'label' => 'meta',
						'name' => 'bbb_meta',
						'type' => 'textarea',
						'instructions' => 'Dies ist ein spezieller Parametertyp (es gibt keinen Parameter, der nur Meta genannt wird).

Sie können beim Erstellen einer Besprechung einen oder mehrere Metadatenwerte übergeben. Diese werden von BigBlueButton gespeichert und können später über die Aufrufe getMeetingInfo und getRecordings abgerufen werden.

Beispiele für die Verwendung der Meta-Parameter sind meta_Presenter=Jane%20Doe, meta_category=FINANCE und meta_TERM=Fall2016.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => '',
						'new_lines' => '',
					),
					array(
						'key' => 'field_5ed429e756aeb',
						'label' => 'moderatorOnlyMessage',
						'name' => 'bbb_moderatorOnlyMessage',
						'type' => 'textarea',
						'instructions' => 'Anzeige einer Nachricht an alle Moderatoren im öffentlichen Chat.

Der Wert wird auf die gleiche Weise interpretiert wie der Begrüßungsparameter.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => '',
						'new_lines' => '',
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
						'ui' => 0,
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
						'default_value' => 0,
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
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
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42a5756aef',
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
						'key' => 'field_5ed42a7356af0',
						'label' => 'bannerText',
						'name' => 'bbb_bannerText',
						'type' => 'textarea',
						'instructions' => 'Setzt den Bannertext im Client.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => '',
						'new_lines' => '',
					),
					array(
						'key' => 'field_5ed42a8b56af1',
						'label' => 'bannerColor',
						'name' => 'bbb_bannerColor',
						'type' => 'text',
						'instructions' => 'Legt die Hintergrundfarbe des Banners im Client fest. Das erforderliche Format ist Farbe hex #FFFFFFFF.',
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
						'key' => 'field_5ed42aae56af2',
						'label' => 'copyright',
						'name' => 'bbb_copyright',
						'type' => 'text',
						'instructions' => 'Die Einstellung copyright=Mein benutzerdefiniertes Copyright ersetzt das Standard-Copyright in der Fußzeile des Flash-Clients.',
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
						'default_value' => 0,
						'ui' => 0,
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
						'ui' => 0,
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
						'ui' => 0,
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
						'ui' => 0,
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
						'ui' => 0,
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
						'ui' => 0,
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
						'ui' => 0,
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
						'ui' => 0,
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
						'default_value' => 0,
						'ui' => 0,
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
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed42bf975167',
						'label' => 'guestPolicy',
						'name' => 'bbb_guestPolicy',
						'type' => 'select',
						'instructions' => 'Standardmäßig guestPolicy=ALWAYS_ACCEPT. Legt die Gästerichtlinie für die Besprechung fest. Die Guest-Richtlinie legt fest, ob Benutzer, die eine Beitrittsanfrage mit guest=true senden, der Besprechung beitreten dürfen oder nicht. Mögliche Werte sind ALWAYS_ACCEPT, ALWAYS_DENY und ASK_MODERATOR.',
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
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'return_format' => 'value',
						'ajax' => 0,
						'placeholder' => '',
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
						'key' => 'field_5ed433313f156',
						'label' => 'defaultDialAccessNumber',
						'name' => 'bbb_cc_defaultDialAccessNumber',
						'type' => 'text',
						'instructions' => 'Default dial access number',
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
						'key' => 'field_5ed434333f157',
						'label' => 'defaultGuestPolicy',
						'name' => 'bbb_cc_defaultGuestPolicy',
						'type' => 'select',
						'instructions' => 'Default Guest Policy
Valid values are ALWAYS_ACCEPT, ALWAYS_DENY, ASK_MODERATOR',
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
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'return_format' => 'value',
						'ajax' => 0,
						'placeholder' => '',
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
						'key' => 'field_5ed434865dbb9',
						'label' => 'bbb_custom_style',
						'name' => 'bbb_ud_userdata-bbb_custom_style',
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
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5ed435145dbba',
						'label' => 'bbb_custom_style_url',
						'name' => 'bbb_ud_userdata-bbb_custom_style_url',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'https://forcase.de/BBB/Forcase.css',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5ed43619a1a5f',
						'label' => '',
						'name' => '',
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
			));

		endif;
	}
}

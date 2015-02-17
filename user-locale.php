<?php
/*
Plugin Name: User Locale
Plugin URI: http://wordpress.org/plugins/user-locale
Description: Allows users to set their own language on their profile page.
Version: 1.0.0
Author: Justin Kopepasah
Author URI: http://kopepasah.com/
License: MIT
License URI: http://opensource.org/licenses/MIT
Text Domain: user-locale
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * User Locale Class
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'User_Locale' ) ) {
	class User_Locale {

		/**
		* @var User_Locale
		* @since 1.0.0
		*/
		private static $instance;

		/**
		* Main User Locale Instance
		*
		* @since 1.0.0
		* @static
		*/
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		* Now under construction.
		*
		* @since 1.0.0
		*/
		public function __construct() {

			// If no user is logged in, there is nothing to do... so bail.
			if ( ! is_user_logged_in() ) {
				return;
			}

			// Hook new fields to the user meta page.
			add_action( 'show_user_profile', array( $this, 'add_option' ), -100 );
			add_action( 'edit_user_profile', array( $this, 'add_option' ), -100 );

			// Save the data when necessary.
			add_action( 'personal_options_update', array( $this, 'update_option' )  );
			add_action( 'edit_user_profile_update', array( $this, 'update_option' ) );

			// Filter the user's locale setting.
			add_filter( 'locale', array( $this, 'filter_locale' ), 0 );

			// Load plugin text domain.
			load_plugin_textdomain( 'user-locale', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		* Filter User Locale
		*
		* @since 1.0.0
		*/
		public function filter_locale( $locale ) {

			/**
			 * Hack for showing the correct language option on Settings > General.
			 * TODO Find a better way to implement this to only show the correct option for the page, not change the language entirely.
			 */
			if ( $GLOBALS['pagenow'] == 'options-general.php' ) {
				return $locale;
			}

			// Get the current user's language setting.
			$user_locale = $this->get_option();

			/*
			 * If the user's locale does not equal the site's locale, update
			 * the locale based on the users setting.
			 */
			if ( $user_locale != $locale ) {
				$locale = $user_locale;
			}

			return $locale;
		}

		/**
		* Add an option to the user setting page.
		*
		* @since 1.0.0
		*/
		public function add_option() {

			// Get the currently available languages.
			$languages = get_available_languages();

			// Get the current user's language setting.
			$locale = $this->get_option();

			if ( ! in_array( $locale, $languages ) ) {
				$locale = '';
			}

			?>
			<table class="form-table">
				<tr>
					<th><label for="user_locale"><?php _e( 'Preferred Language', 'user-locale' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_languages( array(
							'name'         => 'user_locale',
							'id'           => 'user_locale',
							'selected'     => $locale,
							'languages'    => $languages,
							'show_available_translations' => false
						) );
						 ?>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		* Update our option.
		*
		* @since 1.0.0
		*/
		public function update_option( $user_id ) {

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}

			update_usermeta( $user_id, 'user_locale', $_POST['user_locale'] );
		}

		/**
		* Get our option.
		*
		* @since 1.0.0
		*/
		public function get_option() {
			return get_user_option( 'user_locale', get_current_user_id() );
		}
	}
}


/**
* Instantiate this plugin on plugins_loaded hook.
*
* @since 1.0.0
*/
if ( ! function_exists( 'user_locale' ) ) {
	function user_locale() {
		return User_Locale::instance();
	}
	add_action( 'plugins_loaded', 'user_locale' );
}

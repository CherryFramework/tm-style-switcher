<?php
/**
 * Sets up the admin functionality for the plugin.
 *
 * @package    Tm Style Switcher
 * @subpackage Admin
 * @author     Template Monster
 * @license    GPL-3.0+
 * @copyright  2002-2016, Template Monster
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Tm_Style_Switcher_Admin` doesn't exists yet.
if ( ! class_exists( 'Tm_Style_Switcher_Admin' ) ) {

	/**
	 * Tm_Style_Switcher_Admin class
	 */
	class Tm_Style_Switcher_Admin {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Class constructor
		 */
		public function __construct() {

			// Include libraries from the `includes/admin`
			$this->includes();

			// Load the admin menu.
			add_action( 'admin_menu', array( $this, 'menu' ) );

			// Load admin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			// Load admin JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Include libraries from the `includes/admin`
		 */
		public function includes() {}

		/**
		 * Register the admin menu.
		 */
		public function menu() {}

		/**
		 * Register and enqueue admin style sheet.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_styles() {}

		/**
		 * Register and enqueue admin style sheet.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}

}

if ( ! function_exists( 'tm_style_switcher_admin' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tm_style_switcher_admin() {
		return Tm_Style_Switcher_Admin::get_instance();
	}
}

tm_style_switcher_admin();

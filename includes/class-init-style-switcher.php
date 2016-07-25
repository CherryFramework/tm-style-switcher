<?php
/**
 * Init style switcher.
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

// If class `TM_init_style_switcher` doesn't exists yet.
if ( ! class_exists( 'TM_init_style_switcher' ) ) {

	/**
	 * Tm_Style_Switcher_Admin class
	 */
	class TM_init_style_switcher {

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

			add_action( 'customize_register', array( $this, 'init' ), 10 );
		}

		/**
		 * Check to see if we need to do an export or import.
		 * This should be called by the customize_register action.
		 *
		 * @since 1.0.0
		 * @param object $wp_customize An instance of WP_Customize_Manager.
		 * @return void
		 */
		public function init( $wp_customize ) {

		}

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

if ( ! function_exists( 'tm_init_style_switcher' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tm_init_style_switcher() {
		return TM_init_style_switcher::get_instance();
	}
}

tm_init_style_switcher();

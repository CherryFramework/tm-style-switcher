<?php
/**
 * Register new customize control.
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

// If class `TMSS_register_customize_control` doesn't exists yet.
if ( ! class_exists( 'TMSS_register_customize_control' ) ) {

	/**
	 * Tm_Style_Switcher_Admin class
	 */
	class TMSS_register_customize_control {

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

			add_action( 'customize_register', array( $this, 'register' ) );
		}

		/**
		 * Registers the control with the customizer.
		 *
		 * @since 1.0.0
		 * @param object $wp_customize An instance of WP_Customize_Manager.
		 * @return void
		 */
		public function register( $wp_customize ) {

			// Add the export/import section.
			$wp_customize->add_section( 'tmss-section', array(
				'title'    => __( 'Export/Import', 'tm-style-switcher' ),
				'priority' => 10000000
			));

			// Add the export/import setting.
			$wp_customize->add_setting( 'tmss-setting', array(
				'default' => '',
				'type'    => 'none'
			));

			require_once( trailingslashit( TM_STYLE_SWITCHER_DIR ) . 'includes/class-export-import-control.php' );

			// Add the export/import control.
			$wp_customize->add_control( new TMSS_Export_Import_Control(
				$wp_customize,
				'tmss-setting',
				array(
					'section'	=> 'tmss-section',
					'priority'	=> 1
				)
			));

		}

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

if ( ! function_exists( 'tmss_register_customize_control' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tmss_register_customize_control() {
		return TMSS_register_customize_control::get_instance();
	}
}

tmss_register_customize_control();

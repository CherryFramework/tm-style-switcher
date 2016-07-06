<?php
/**
 * Plugin Name: TM Style Switcher
 * Plugin URI:  http://www.cherryframework.com/
 * Description: Plugin for WordPress.
 * Version:     1.0.0
 * Author:      CherryTeam
 * Text Domain: tm-style-swither
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package Tm Style Switcher
 * @author  TemplateMonster
 * @version 1.0.0
 * @license GPL-3.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Tm_Style_Switcher` doesn't exists yet.
if ( ! class_exists( 'Tm_Style_Switcher' ) ) {

	/**
	 * Sets up and initializes the Tm Style Switcher plugin.
	 */
	class Tm_Style_Switcher {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of cherry framework core class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private $core = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// Set the constants needed by the plugin.
			$this->constants();

			// Load the installer core.
			add_action( 'after_setup_theme', require( trailingslashit( __DIR__ ) . 'cherry-framework/setup.php' ), 0 );

			// Load the core functions/classes required by the rest of the theme.
			add_action( 'after_setup_theme', array( $this, 'get_core' ), 1 );

			// Initialization of modules.
			add_action( 'after_setup_theme', array( $this, 'init' ) );

			// Internationalize the text strings used.
			add_action( 'plugins_loaded', array( $this, 'lang' ), 2 );

			// Load the admin files.
			add_action( 'plugins_loaded', array( $this, 'admin_init' ), 3 );

			// Load public-facing style sheet.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			// Load public-facing JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Defines constants for the plugin.
		 *
		 * @since 1.0.0
		 */
		public function constants() {

			/**
			 * Set the version number of the plugin.
			 *
			 * @since 1.0.0
			 */
			define( 'TM_STYLE_SWITCHER_VERSION', '1.0.0' );

			/**
			 * Set constant path to the plugin directory.
			 *
			 * @since 1.0.0
			 */
			define( 'TM_STYLE_SWITCHER_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

			/**
			 * Set constant path to the plugin URI.
			 *
			 * @since 1.0.0
			 */
			define( 'TM_STYLE_SWITCHER_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		}

		/**
		 * Loads the core functions. These files are needed before loading anything else in the
		 * theme because they have required functions for use.
		 *
		 * @since  1.0.0
		 */
		public function get_core() {

			/**
			 * Fires before loads the core theme functions.
			 *
			 * @since 1.0.0
			 */
			do_action( 'tm_style_swither_core_before' );

			global $chery_core_version;

			if ( null !== $this->core ) {
				return $this->core;
			}

			if ( 0 < sizeof( $chery_core_version ) ) {
				$core_paths = array_values( $chery_core_version );
				require_once( $core_paths[0] );
			} else {
				die( 'Class Cherry_Core not found' );
			}

			$this->core = new Cherry_Core( array(
				'modules'  => array(
					'cherry-toolkit' => array(
						'autoload' => false,
					),
					'cherry-js-core' => array(
						'autoload' => true,
					),
					'cherry-ui-elements' => array(
						'autoload' => false,
					),
					'cherry-utility' => array(
						'autoload' => true,
					),
				),
			) );

			return $this->core;
		}

		/**
		 * Run initialization of modules.
		 *
		 * @since 1.0.0
		 */
		public function init() {
		}

		/**
		 * Loads admin files.
		 *
		 * @since 1.0.0
		 */
		public function admin_init() {
			require_once( TM_STYLE_SWITCHER_DIR . 'includes/admin/class.plugin-admin.php' );
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 */
		public function lang() {
			load_plugin_textdomain( 'tm-style-swither', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_styles() {}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {}

		/**
		 * On plugin activation.
		 *
		 * @since 1.0.0
		 */
		public function activation() {}

		/**
		 * On plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		public function deactivation() {}

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

if ( ! function_exists( 'tm_style_switcher' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tm_style_switcher() {
		return Tm_Style_Switcher::get_instance();
	}
}

tm_style_switcher();

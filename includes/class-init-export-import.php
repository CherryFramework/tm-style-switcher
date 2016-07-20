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

// If class `TMSS_init_export_import` doesn't exists yet.
if ( ! class_exists( 'TMSS_init_export_import' ) ) {

	/**
	 * Tm_Style_Switcher_Admin class
	 */
	class TMSS_init_export_import {

		/**
		 * An array of core options that shouldn't be imported.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array $base_options
		 */
		private $base_options = array(
			'blogname',
			'blogdescription',
			'show_on_front',
			'page_on_front',
			'page_for_posts',
		);

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

			add_action( 'wp_ajax_tmss_import_settings', array( $this, 'import_settings' ) );
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
			if ( current_user_can( 'edit_theme_options' ) ) {
				if ( isset( $_REQUEST['tmss-export'] ) ) {
					$this->export_settings( $wp_customize );
				}
			}

			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

				// Load the export/import option class.
				require_once TM_STYLE_SWITCHER_DIR . 'includes/class-update-option.php';
			}

		}

		/**
		 * Export customizer settings.
		 *
		 * @since 1.0.0
		 * @param object $wp_customize An instance of WP_Customize_Manager.
		 * @return void
		 */
		public function export_settings( $wp_customize ) {
			if ( ! wp_verify_nonce( $_REQUEST['tmss-export'], 'tmss-exporting' ) ) {
				return;
			}

			$theme		= get_stylesheet();
			$template	= get_template();
			$charset	= get_option( 'blog_charset' );
			$mods		= get_theme_mods();
			$data		= array(
				'template' => $template,
				'mods'     => $mods ? $mods : array(),
				'options'  => array()
			);

			// Get options from the Customizer API.
			$settings = $wp_customize->settings();

			foreach ( $settings as $key => $setting ) {

				if ( 'option' == $setting->type ) {

					// Don't save widget data.
					if ( stristr( $key, 'widget_' ) ) {
						continue;
					}

					// Don't save sidebar data.
					if ( stristr( $key, 'sidebars_' ) ) {
						continue;
					}

					// Don't save core options.
					if ( in_array( $key, $this->base_options ) ) {
						continue;
					}

					$data['options'][ $key ] = $setting->value();
				}
			}

			// Plugin developers can specify additional option keys to export.
			$option_keys = apply_filters( 'tmss_export_option_keys', array() );

			foreach ( $option_keys as $option_key ) {

				$option_value = get_option( $option_key );

				if ( $option_value ) {
					$data['options'][ $option_key ] = $option_value;
				}
			}

			// Set the download headers.
			header( 'Content-disposition: attachment; filename=' . $theme . '-' . gmdate( "d-m-Y-H:i:s" ) . '-export.json' );
			header( 'Content-Type: application/octet-stream; charset=' . $charset );

			// Encode the export data.
			$data = json_encode( $data );

			echo $data;

			// Start the download.
			die();
		}

		/**
		 * Imports uploaded mods and calls WordPress core customize_save actions so
		 * themes that hook into them can act before mods are saved to the database.
		 *
		 * @since 0.1
		 * @since 0.3 Added $wp_customize param and importing of options.
		 * @access private
		 * @param object $wp_customize An instance of WP_Customize_Manager.
		 * @return void
		 */
/*		public function import_settings( $wp_customize ) {


			// Make sure we have a valid nonce.
			if ( ! wp_verify_nonce( $_REQUEST['tmss-import'], 'tmss-importing' ) ) {
				return;
			}

			// Make sure WordPress upload support is loaded.
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			// Load the export/import option class.
			require_once TM_STYLE_SWITCHER_DIR . 'includes/class-update-option.php';

			// Setup global vars.
			global $wp_customize;
			global $tmss_error;

			// Setup internal vars.
			$tmss_error = false;
			$template  = get_template();
			$overrides = array( 'test_form' => FALSE, 'mimes' => array('json' => 'text/json') );
			$file      = wp_handle_upload( $_FILES['tmss-import-file'], $overrides );

			// Make sure we have an uploaded file.
			if ( isset( $file['error'] ) ) {
				$tmss_error = $file['error'];
				return;
			}

			if ( ! file_exists( $file['file'] ) ) {
				$tmss_error = esc_html__( 'Error importing settings! Please try again.', 'tm-style-switcher' );
				return;
			}

			// Get the upload data.
			$raw  = file_get_contents( $file['file'] );
			$data = json_decode( $raw, true );

			// Remove the uploaded file.
			unlink( $file['file'] );

			// Data checks.
			if ( 'array' != gettype( $data ) ) {
				$tmss_error = esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' );
				return;
			}

			if ( ! isset( $data['template'] ) || ! isset( $data['mods'] ) ) {
				$tmss_error = esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' );
				return;
			}
			if ( $data['template'] != $template ) {
				$tmss_error = esc_html__( 'Error importing settings! The settings you uploaded are not for the current theme.', 'tm-style-switcher' );
				return;
			}


			// Import images.
			if ( isset( $_REQUEST['tmss-import-images'] ) ) {
				$data['mods'] = $this->import_images( $data['mods'] );
			}


			// Import custom options.
			if ( isset( $data['options'] ) ) {

				foreach ( $data['options'] as $option_key => $option_value ) {

					$option = new TM_SS_Update_Option( $wp_customize, $option_key, array(
						'default'		=> '',
						'type'			=> 'option',
						'capability'	=> 'edit_theme_options'
					) );

					$option->import( $option_value );
				}
			}

			// Call the customize_save action.
			do_action( 'customize_save', $wp_customize );

			// Loop through the mods.
			foreach ( $data['mods'] as $key => $val ) {

				// Call the customize_save_ dynamic action.
				do_action( 'customize_save_' . $key, $wp_customize );

				// Save the mod.
				set_theme_mod( $key, $val );
			}

			// Call the customize_save_after action.
			do_action( 'customize_save_after', $wp_customize );
		}*/


		/**
		 * Ajax import options
		 *
		 * @since 4.0.0
		 */
		function import_settings() {

			$validate = check_ajax_referer( 'tmss_import_settings', 'nonce', false );

			// Make sure WordPress upload support is loaded.
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			// Setup global vars.
			global $wp_customize;

			// Setup internal vars.
			$template  = get_template();
			$overrides = array( 'test_form' => FALSE, 'mimes' => array('json' => 'text/json') );
			$file      = wp_handle_upload( $_FILES['tmss-import-file'], $overrides );

			// Make sure we have an uploaded file.
			if ( isset( $file['error'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Make sure we have an uploaded file.', 'tm-style-switcher' ),
					)
				);

				return;
			}

			if ( ! file_exists( $file['file'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please try again.', 'tm-style-switcher' ),
					)
				);
			}

			// Get the upload data.
			$import_data  = file_get_contents( $file['file'] );
			$data = json_decode( $import_data, true );

			// Remove the uploaded file.
			unlink( $file['file'] );

			// Data checks.
			if ( 'array' != gettype( $data ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' ),
					)
				);
			}

			if ( ! isset( $data['template'] ) || ! isset( $data['mods'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' ),
					)
				);
			}

			if ( $data['template'] != $template ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! The settings you uploaded are not for the current theme.', 'tm-style-switcher' ),
					)
				);

				return;
			}

			// Import images.
			if ( isset( $_POST['tmss-import-images'] ) && filter_var( $_POST['tmss-import-images'], FILTER_VALIDATE_BOOLEAN ) ) {
				$data['mods'] = $this->import_images( $data['mods'] );
			}


			// Import custom options.
			if ( isset( $data['options'] ) ) {

				foreach ( $data['options'] as $option_key => $option_value ) {


					/*$option = new TM_SS_Update_Option( $wp_customize, $option_key, array(
						'default'		=> '',
						'type'			=> 'option',
						'capability'	=> 'edit_theme_options'
					) );

					$option->import( $option_value );*/
				}
			}

			// Loop through the mods.
			foreach ( $data['mods'] as $key => $value ) {

				// Save the mod.
				set_theme_mod( $key, $value );
			}

			wp_send_json(
				array(
					'type' => 'success',
					'message' => esc_html__( 'Settings has been imported', 'tm-style-switcher' ),
				)
			);
		}

		/**
		 * Imports images for settings saved as mods.
		 *
		 * @since 1.0.0
		 * @access public
		 * @param array $mods An array of customizer mods.
		 * @return array The mods array with any new import data.
		 */
		public function import_images( $mods ) {
			foreach ( $mods as $key => $val ) {

				if ( $this->is_image_url( $val ) ) {

					$data = $this->sideload_image( $val );

					if ( ! is_wp_error( $data ) ) {

						$mods[ $key ] = $data->url;

						// Handle header image controls.
						if ( isset( $mods[ $key . '_data' ] ) ) {
							$mods[ $key . '_data' ] = $data;
							update_post_meta( $data->attachment_id, '_wp_attachment_is_custom_header', get_stylesheet() );
						}
					}
				}
			}

			return $mods;
		}

		/**
		 * Taken from the core media_sideload_image function and
		 * modified to return an array of data instead of html.
		 *
		 * @since 1.0.0
		 * @access public
		 * @param string $file The image file path.
		 * @return array An array of image data.
		 */
		public function sideload_image( $file ) {
			$data = new stdClass();

			if ( ! function_exists( 'media_handle_sideload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
			}
			if ( ! empty( $file ) ) {

				// Set variables for storage, fix file filename for query strings.
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
				$file_array = array();
				$file_array['name'] = basename( $matches[0] );

				// Download file to temp location.
				$file_array['tmp_name'] = download_url( $file );

				// If error storing temporarily, return the error.
				if ( is_wp_error( $file_array['tmp_name'] ) ) {
					return $file_array['tmp_name'];
				}

				// Do the validation and storage stuff.
				$id = media_handle_sideload( $file_array, 0 );

				// If error storing permanently, unlink.
				if ( is_wp_error( $id ) ) {
					@unlink( $file_array['tmp_name'] );
					return $id;
				}

				// Build the object to return.
				$meta					= wp_get_attachment_metadata( $id );
				$data->attachment_id	= $id;
				$data->url				= wp_get_attachment_url( $id );
				$data->thumbnail_url	= wp_get_attachment_thumb_url( $id );
				$data->height			= $meta['height'];
				$data->width			= $meta['width'];
			}

			return $data;
		}

		/**
		 * Checks to see whether a string is an image url or not.
		 *
		 * @since 1.0.0
		 * @access public
		 * @param string $string The string to check.
		 * @return bool Whether the string is an image url or not.
		 */
		public function is_image_url( $string = '' ) {
			if ( is_string( $string ) ) {

				if ( preg_match( '/\.(jpg|jpeg|png|gif)/i', $string ) ) {
					return true;
				}
			}

			return false;
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

if ( ! function_exists( 'tmss_init_export_import' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tmss_init_export_import() {
		return TMSS_init_export_import::get_instance();
	}
}

tmss_init_export_import();

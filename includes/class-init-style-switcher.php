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

			add_action( 'wp_ajax_tmss_restore_defaults', array( $this, 'restore_defaults' ) );

			add_action( 'wp_ajax_tmss_preset_applying', array( $this, 'preset_applying' ) );
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
			$charset	= get_option( 'blog_charset' );
			$mods		= get_theme_mods();
			$data		= array(
				'theme' => $theme,
				'mods'  => $mods ? $mods : array(),
			);

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
		 * Ajax import options
		 *
		 * @since 4.0.0
		 */
		function import_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'You don\'t have permission to do this', 'tm-style-switcher' ),
					)
				);
			}

			$validate = wp_verify_nonce( $_POST[ 'nonce' ], 'cherry_ajax_nonce' );

			if ( ! $validate ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'You don\'t have permission to do this', 'tm-style-switcher' ),
					)
				);
			}

			// Make sure WordPress upload support is loaded.
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			// Setup global vars.
			global $wp_customize;

			// Setup internal vars.
			$theme     = get_stylesheet();
			$overrides = array( 'test_form' => FALSE, 'mimes' => array('json' => 'text/json') );
			$file      = wp_handle_upload( $_FILES['tmss-import-file'], $overrides );

			// Make sure we have an uploaded file.
			if ( isset( $file['error'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Make sure we have an uploaded file', 'tm-style-switcher' ),
					)
				);

				return;
			}

			if ( ! file_exists( $file['file'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please try again', 'tm-style-switcher' ),
					)
				);
			}

			// Get the upload data.
			$import_data = file_get_contents( $file['file'] );
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

			if ( ! isset( $data['theme'] ) || ! isset( $data['mods'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' ),
					)
				);
			}

			if ( $data['theme'] != $theme ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! The settings you uploaded are not for the current theme.', 'tm-style-switcher' ),
					)
				);

				return;
			}

			// Import images.
			$data['mods'] = $this->import_images( $data['mods'] );

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
		 * Function for restoring to default theme settings
		 *
		 * @return void
		 */
		public function restore_defaults( ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'You don\'t have permission to do this', 'tm-style-switcher' ),
					)
				);
			}

			$validate = wp_verify_nonce( $_POST[ 'nonce' ], 'cherry_ajax_nonce' );

			if ( ! $validate ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'You don\'t have permission to do this', 'tm-style-switcher' ),
					)
				);
			}

			$theme = get_stylesheet();
			$option_name = tm_style_switcher()->get_default_option_field_name( $theme );

			$data = get_option( $option_name );

			if ( ! $data ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'The default setting is not found', 'tm-style-switcher' ),
					)
				);
			}

			// Loop through the mods.
			foreach ( $data['mods'] as $key => $value ) {

				// Save the mod.
				set_theme_mod( $key, $value );
			}

			wp_send_json(
				array(
					'type'    => 'success',
					'message' => esc_html__( 'Settings has been restored', 'tm-style-switcher' ),
				)
			);
		}

		/**
		 * Preset applying function
		 *
		 * @since 4.0.0
		 */
		function preset_applying() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'You don\'t have permission to do this', 'tm-style-switcher' ),
					)
				);
			}

			$validate = wp_verify_nonce( $_POST[ 'nonce' ], 'cherry_ajax_nonce' );

			if ( ! $validate ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'You don\'t have permission to do this', 'tm-style-switcher' ),
					)
				);
			}

			$preset_id = $_POST[ 'preset' ];
			$theme = get_stylesheet();

			if ( 'default_preset' !== $preset_id ) {
				$file_name = tm_style_switcher()->preset_list[ $preset_id ]['json_path'];
				$file_content = self::get_contents( $file_name );
				$file_content = ! is_wp_error( $file_content ) ? $file_content : '{}';

				$data = json_decode( $file_content, true );
			} else {
				$option_name = tm_style_switcher()->get_default_option_field_name( $theme );
				$data = get_option( $option_name );
			}

			// Data checks.
			if ( 'array' != gettype( $data ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' ),
					)
				);
			}

			if ( ! isset( $data['theme'] ) || ! isset( $data['mods'] ) ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! Please check that you uploaded a customizer export file.', 'tm-style-switcher' ),
					)
				);
			}

			if ( $data['theme'] !== $theme ) {
				wp_send_json(
					array(
						'type'    => 'error',
						'message' => esc_html__( 'Error importing settings! The settings you uploaded are not for the current theme.', 'tm-style-switcher' ),
					)
				);

				return;
			}

			// Import images.
			$data['mods'] = $this->import_images( $data['mods'] );

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
		 * Read template (static).
		 *
		 * @since  1.0.0
		 * @return bool|WP_Error|string - false on failure, stored text on success.
		 */
		public static function get_contents( $file ) {

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				include_once( ABSPATH . '/wp-admin/includes/file.php' );
			}

			WP_Filesystem();
			global $wp_filesystem;

			// Check for existence
			if ( ! $wp_filesystem->exists( $file ) ) {
				return false;
			}

			// Read the file.
			$content = $wp_filesystem->get_contents( $file );

			if ( ! $content ) {
				return new WP_Error( 'reading_error', 'Error when reading file' ); // Return error object.
			}

			return $content;
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

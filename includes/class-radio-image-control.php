<?php
/**
 * Iconpicker customizer control
 *
 * @package    Cherry_Framework
 * @subpackage Modules/Customizer
 * @author     Cherry Team <cherryframework@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016, Cherry Team
 * @link       http://www.cherryframework.com/
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( class_exists( 'WP_Customize_Control' ) ) {

	/**
	 * Iconpicker control for customizer
	 */
	class TMSS_Radio_Image_Control extends WP_Customize_Control {

		/**
		 * UI instance
		 *
		 * @var object
		 */
		private $radio_image_control = null;

		/**
		 * Render the control's content.
		 */
		public function render_content() {
			?>
			<div class="tmss-control-wrapper">
				<div class="tmss-customize-control">
					<span class="customize-control-title">
						<?php echo esc_html__( 'Style Presets', 'tm-style-switcher' ); ?>
					</span>
					<span class="description customize-control-description">
						<?php echo esc_html__( 'Select the preset styles available', 'tm-style-switcher' ); ?>
					</span>
					<?php
						echo str_replace(
							'id="' . $this->id . '"',
							'id="' . $this->id . '" ' . $this->get_link(),
							$this->radio_image_control->render()
						);
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Enqueue assets
		 */
		public function enqueue() {

			$core       = tm_style_switcher()->get_core();
			$ui_builder = $core->init_module(
				'cherry-ui-elements',
				array( 'ui_elements' => array( 'radio' ) )
			);

			$args = array(
				'id'        => $this->id,
				'name'      => $this->id,
				'value'     => $this->value(),
				'options'			=> array(
					'radio-1' => array(
						'label' => 'Radio 1',
						'img_src'	=> '',
						'slave'		=> '',
					),
					'radio-2' => array(
						'label' => 'Radio 2',
						'img_src'	=> '',
						'slave'		=> '',
					),
					'radio-3' => array(
						'label' => 'Radio 3',
						'img_src'	=> '',
						'slave'		=> '',
					),
				),
			);

			$this->radio_image_control = $ui_builder->get_ui_element_instance( 'radio', $args );
			$this->radio_image_control->enqueue_assets();
		}
	}

}

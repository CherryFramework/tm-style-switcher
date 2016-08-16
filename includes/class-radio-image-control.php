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
				<div class="tmss-customize-control tmss-presets">
					<span class="customize-control-title">
						<?php echo esc_html__( 'Style Presets', 'tm-style-switcher' ); ?>
					</span>
					<span class="description customize-control-description">
						<?php echo esc_html__( 'Select the preset styles available', 'tm-style-switcher' ); ?>
					</span>
					<div class="tmss-presets__list">
					<?php
						if ( ! empty( tm_style_switcher()->preset_list ) ) {
							$default_label = esc_html__( 'Default settings', 'tm-style-switcher' );
							?>
							<div class="tmss-presets__item" data-preset="default_preset">
								<div class="tmss-presets__inner">
									<img class="tmss-presets__image" src="<?php echo tm_style_switcher()->get_default_image(); ?>" alt="<?php echo $default_label; ?> " data-preset="default_preset">
									<span class="tmss-presets__label"><?php echo $default_label; ?></span>
								</div>
							</div>
							<?php
							foreach ( tm_style_switcher()->preset_list as $preset_id => $preset_data ) {
								?>
								<div class="tmss-presets__item" data-preset="<?php echo $preset_id; ?>">
									<div class="tmss-presets__inner">
										<img class="tmss-presets__image" src="<?php echo $preset_data['image_url']; ?>" alt="<?php echo $preset_data['label']; ?>" data-preset="<?php echo $preset_id; ?>">
										<span class="tmss-presets__label"><?php echo $preset_data['label']; ?></span>
									</div>
								</div><?php
							}
						}
					?>
					</div>
					<div class="tmss-customize-notice-container"></div>
				</div>
			</div>
			<div id="style-switch-confirm" class="confirm-message" title="<?php echo esc_html__( 'Are you sure?', 'tm-style-switcher' ); ?>">
				<p><?php echo esc_html__( 'Unsaved settings will be ignored, page will be refreshed.', 'tm-style-switcher' ) ?></p>
			</div>
			<?php
		}
	}

}

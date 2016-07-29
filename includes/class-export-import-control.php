<?php

/**
 * A customizer control for rendering the export/import form.
 *
 * @since 1.0.0
 */
final class TMSS_Export_Import_Control extends WP_Customize_Control {

	/**
	 * Renders the control content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function render_content() { ?>
		<div class="tmss-control-wrapper">
			<div class="tmss-customize-control">
				<span class="customize-control-title">
					<?php echo esc_html__( 'Export', 'tm-style-switcher' ); ?>
				</span>
				<span class="description customize-control-description">
					<?php echo esc_html__( 'Click the button below to export the customization settings for this theme.', 'tm-style-switcher' ); ?>
				</span>
				<div class="button tmss-export-button"><?php echo esc_html__( 'Export', 'tm-style-switcher' ); ?></div>
				<div class="tmss-customize-notice-container"></div>
			</div>
			<div class="tmss-customize-control">
				<span class="customize-control-title">
					<?php echo esc_html__( 'Import', 'tm-style-switcher' ); ?>
				</span>
				<span class="description customize-control-description">
					<?php echo esc_html__( 'Upload a file to import customization settings for this theme.', 'tm-style-switcher' ); ?>
				</span>
				<div class="tmss-import-controls">
					<input type="file" name="tmss-import-file" class="tmss-import-file" />
				</div>
				<div class="button tmss-import-button"><?php echo esc_html__( 'Import', 'tm-style-switcher'  ); ?></div>
				<div class="tmss-customize-notice-container"></div>
			</div>
			<div class="tmss-customize-control">
				<span class="customize-control-title">
					<?php echo esc_html__( 'Default theme settings', 'tm-style-switcher' ); ?>
				</span>
				<span class="description customize-control-description">
					<?php echo esc_html__( 'Return the default theme settings(settings that are relevant to the plugin activation or activation of new themes).', 'tm-style-switcher' ); ?>
				</span>
				<div class="button tmss-restore-settings-button"><?php echo esc_html__( 'Restore settings', 'tm-style-switcher' ); ?></div>
				<div class="tmss-customize-notice-container"></div>
			</div>
		</div><?php
	}
}
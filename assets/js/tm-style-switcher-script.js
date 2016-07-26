( function( $ ) {
	"use strict";

	CherryJsCore.utilites.namespace('tm_style_switcher_scripts');
	CherryJsCore.tm_style_switcher_scripts = {
		init: function () {
			$( document ).on( 'ready', this.constructor.bind( this ) );
		},

		constructor: function () {
			$( '#customize-theme-controls' ).on( 'click', '.tmss-export-button', this.exportSettings.bind( this ) );
			$( '#customize-theme-controls' ).on( 'click', '.tmss-import-button', this.ajaxImportSettings.bind( this ) );
			$( '#customize-theme-controls' ).on( 'click', '.tmss-restore-settings-button', this.ajaxRestoreDefaults.bind( this ) );
			$( '.tmss-presets-list' ).on( 'click', '.cherry-radio-input ', this.ajaxPresetSwitch.bind( this ) );
		},

		exportSettings: function() {
			var self           = this,
				target         = event.target;

			self.noticeCreate( target, 'info', tmssMessages.downloadStarted );

			window.location.href = tmssConfig.customizerURL + '?tmss-export=' + tmssConfig.exportNonce;
		},

		ajaxImportSettings: function() {
			var self           = this,
				target         = event.target,
				$input         = $( '.tmss-import-file' ),
				file           = $input[0].files[0],
				filePath       = $input.val(),
				nonce          = cherry_ajax,
				formData       = new FormData(),
				isImportImages = $( '.tmss-import-images input:checked' );

			formData.append( 'action', 'tmss_import_settings' );
			formData.append( 'nonce', nonce );
			formData.append( 'tmss-import-file', file );
			formData.append( 'tmss-import-images', isImportImages[0] ? true : false );

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: formData,
				contentType: false,
				processData: false,
				cache: false,
				beforeSend: function( jqXHR ) {
					if ( !file ) {
						jqXHR.abort();
						self.noticeCreate( target, 'error', tmssMessages.emptyImportFile );
					} else {
						self.noticeCreate( target, 'info', tmssMessages.willBeRestored );
					}
				},
				success: function(response){
					self.noticeCreate( target, response.type, response.message );
					setTimeout( function () {
						window.location.reload();
					}, 2000 );
				},

			});
		},

		ajaxRestoreDefaults: function() {
			var self     = this,
				target   = event.target,
				nonce    = cherry_ajax,
				formData = new FormData();

			formData.append( 'action', 'tmss_restore_defaults' );
			formData.append( 'nonce', nonce );

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: formData,
				contentType: false,
				processData: false,
				cache: false,
				success: function( response ) {
					self.noticeCreate( target, 'info', tmssMessages.willBeRestored );
					setTimeout( function () {
						window.location.reload();
					}, 2000 );
				}
			});
		},

		ajaxPresetSwitch: function() {
			var self           = this,
				target         = event.target,
				nonce          = cherry_ajax,
				formData       = new FormData();

			formData.append( 'action', 'tmss_import_settingss' );
			formData.append( 'nonce', nonce );

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: formData,
				contentType: false,
				processData: false,
				cache: false,
				beforeSend: function( jqXHR ) {

				},
				success: function(response){
					self.noticeCreate( target, response.type, response.message );
					setTimeout( function () {
						window.location.reload();
					}, 2000 );
				},

			});
		},

		noticeCreate: function( target, type, message ) {
			var $noticeInstance,
				$noticeContainer       = $( target ).closest( '.tmss-customize-control' ).find( '.tmss-customize-notice-container' ),
				timeoutId,
				noticeType;

			$noticeInstance = $( '<div class="notice-box ' + type + '-notice" data-type="' + type + '"><span class="dashicons"></span><div class="inner-text"><span>' + message + '</span></div></div>' );
			noticeType = $noticeInstance.data('type');

			$( '.notice-box[data-type="' + noticeType + '"]', $noticeContainer ).slideUp( 300, function() {
				$( this ).remove();
			});

			$noticeContainer.prepend( $noticeInstance );


			//$( '.notice-box:nth-child(n + 3)', $noticeContainer ).slideUp( 300, function() {

			/*$( '.notice-box:not(:first-child)', $noticeContainer ).slideUp( 300, function() {
				$( this ).remove();
			});*/

			$noticeInstance.slideDown( 300, function() {
				$( this ).addClass( 'show-state' );
				timeoutId = setTimeout( function () {
					$noticeInstance.slideUp( 300, function() {
						$noticeInstance.remove();
						clearTimeout( timeoutId );
					});
				}, 4000 );
			} );

		} // end noticeCreate

	}

	CherryJsCore.tm_style_switcher_scripts.init();
} ( jQuery ) );

( function( $ ) {
	"use strict";

	CherryJsCore.utilites.namespace('tm_style_switcher_scripts');
	CherryJsCore.tm_style_switcher_scripts = {
		init: function () {
			var self = this;

			$( document ).on( 'ready', self.constructor( self ) );
		},

		constructor: function ( self ) {
			$( '#customize-theme-controls' ).on( 'click', '.tmss-export-button', self.exportSettings );
			//$( '#customize-theme-controls' ).on( 'click', '.tmss-import-button', self.importSettings );
			$( '#customize-theme-controls' ).on( 'click', '.tmss-import-button', self.ajaxImportSettings );
		},

		exportSettings: function() {
			window.location.href = TMSSConfig.customizerURL + '?tmss-export=' + TMSSConfig.exportNonce;
		},

		importSettings: function() {
			var win            = $( window ),
				body           = $( 'body' ),
				form           = $( '<form class="tmss-form" method="POST" enctype="multipart/form-data"></form>' ),
				controls       = $( '.tmss-import-controls' ),
				file           = $( 'input[name=tmss-import-file]' ),
				message        = $( '.tmss-uploading' );

			if ( '' == file.val() ) {
				alert( TMSSl10n.emptyImport );
			} else {
				win.off( 'beforeunload' );
				body.append( form );
				form.append( controls );
				message.show();
				form.submit();
			}
		},

		ajaxImportSettings: function() {
			var $input         = $( '.tmss-import-file' ),
				file           = $input[0].files[0],
				filePath       = $input.val(),
				nonce          = $( '#import-settings-nonce' ).val(),
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
				beforeSend: function(){

				},
				success: function(response){
					console.log(response);
					//window.location.reload();
				},

			});
		}
	}

	CherryJsCore.tm_style_switcher_scripts.init();
} ( jQuery ) );

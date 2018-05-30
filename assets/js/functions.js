;(function( $, window, document, undefined ) {
	var $win = $(window);
	var $doc = $(document);

	$doc.ready(function() {
		$( '.migrator .checkbox label' ).on( 'click' , function(){
			$( this ).toggleClass( 'active' );
		} );

		$( '.form-content .file-field input:file' ).change(function() {
			var $this = $( this );

			if ( $this[0].files[0] ) {
				$this.parent().find( 'span' ).html( $this[0].files[0].name );
			}
		});

		var fileField = $( '#database-file' );

		fileField.on( 'change' , function( e ){
			if ( !vaidateProjectUpload( fileField ) ) {
				$( '.migrator .btn-secondary' ).attr( 'disabled' , true );
			} else {
				$( '.migrator .btn-secondary' ).attr( 'disabled' , false );

			}
		});

		var $form = $( 'form.migrator' );

		$form.on( 'submit' , function (e) {
			e.preventDefault();

			var fileField = $form.find( '#database-file' );
			var urlField = $form.find( '#new-url' );
			var prefixField = $form.find( '#prefix' );
			var submitButton = $form.find( '.btn-secondary' );

			vaidateProjectUpload( fileField );
			validateURL( urlField );

			if ( $form.find( '.error' ).length === 0 ) {
				submitButton.attr( 'disabled' , true );
				$( 'body' ).addClass( 'form-loading' );

				var fileFieldValue = document.getElementById( 'database-file' ).files[0].name;

				var formData = new FormData( this );

				formData.append( 'action' , 'migrate' );
				formData.append( 'prefix' , prefix );
				formData.append( 'file' , fileFieldValue );

				var currUrl = window.location.href;
				var targetUrlArr = currUrl.split( '/' );
				targetUrlArr.splice( -1, 1, 'index.php?ajax=1' );
				var targetUrl = targetUrlArr.join( '/' );

				$.ajax({
					type: 'post' ,
					url: targetUrl,
					contentType: false,
					processData: false,
					data: formData,
					success: function ( response ) {
						var $result = $.parseJSON( response );

						if ( $result.status === 'error' ) {
							$( '.error-box' ).show().delay(2000).fadeOut();
							$( '.error-box' ).text( $result.message );
							$( '.migrator :input' ).attr( 'disabled' , false);
							$( 'body' ).removeClass( 'form-loading' );
						} else {
							$( '.migrator :input' ).attr( 'disabled' , true);
							var url = window.location.href;
							url += '?fn=' + $result.file_name + '&zip=' + $result.zip;

							window.location.href = url;

							$( '.migrator :input' ).attr( 'disabled' , false);
							$( 'body' ).removeClass( 'form-loading' );
						}
					}
				} );
			} else {
				e.preventDefault();
			}
		} );
	});

	function vaidateProjectUpload(field) {
		var isValid = true;

		if ( field.val() !== '' ) {
			var fieldExt = field.val().split( '.' ).pop().toLowerCase();

			if( $.inArray( fieldExt, [ 'sql' , 'zip' ] ) == -1 ) {
				field.parent().addClass( 'error' );
				isValid = false;
				$( '.error-box' ).show().delay(2000).fadeOut();
				$( '.error-box' ).text( 'Please upload .sql or zip file with .sql in it.' );
			} else {
				field.parent().removeClass( 'error' );
			}
		}

		return isValid;
	}

	function validateURL(field) {
		var isValid = true;
		var value = field.val().trim();
		var urlregex = /^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+(.*?))(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/;

		if ( urlregex.test(value)) {
			field.parent().removeClass( 'error' );
		} else {
			field.parent().addClass( 'error' );
			$( '.error-box' ).show().delay(2000).fadeOut();
			$( '.error-box' ).text( 'Please enter valid URL address' );
			isValid = false;
		}

		return isValid;
	}

})(jQuery, window, document);

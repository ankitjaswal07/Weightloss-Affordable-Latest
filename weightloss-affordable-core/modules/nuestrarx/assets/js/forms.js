/**
 * NuestraRx — inquiry forms (patients, providers, pharmacies).
 *
 * @package NuestraRx_Core
 */

( function () {
	'use strict';

	var config = window.NRX_FORMS || {};
	var i18n = config.i18n || {};

	function $( selector, context ) {
		return ( context || document ).querySelector( selector );
	}

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest( '[data-nrx-inquiry-form]' );
		if ( ! form ) {
			return;
		}
		event.preventDefault();

		var wrap = form.closest( '[data-nrx-inquiry]' ) || form;
		var error = $('[data-nrx-form-error]', wrap );
		var success = $('[data-nrx-form-success]', wrap );
		var button = $('[data-nrx-submit]', form );
		var label = $('[data-nrx-submit-label]', form );
		var original = label ? label.textContent : '';

		if ( error ) {
			error.hidden = true;
			error.textContent = '';
		}
		if ( success ) {
			success.hidden = true;
			success.textContent = '';
		}

		// Native required checks first, but with visible feedback.
		if ( form.checkValidity && ! form.checkValidity() ) {
			var invalid = form.querySelector( ':invalid' );
			if ( invalid ) {
				invalid.focus();
			}
			if ( error ) {
				error.textContent = i18n.invalid || 'Revisa los campos marcados e inténtalo de nuevo.';
				error.hidden = false;
			}
			return;
		}

		var data = new FormData( form );
		data.append( 'action', 'nrx_inquiry_submit' );
		data.append( 'nonce', config.nonce || '' );

		if ( button ) {
			button.disabled = true;
		}
		if ( label ) {
			label.textContent = i18n.sending || 'Enviando…';
		}

		fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				if ( json && json.success ) {
					form.reset();
					if ( success ) {
						success.textContent = ( json.data && json.data.message ) || i18n.thanks || 'Gracias. Te responderemos pronto.';
						success.hidden = false;
						success.setAttribute( 'tabindex', '-1' );
						success.focus( { preventScroll: true } );
					}
					if ( window.NRX && window.NRX.toast ) {
						window.NRX.toast( ( json.data && json.data.message ) || 'Mensaje enviado' );
					}
					return;
				}
				if ( error ) {
					error.textContent = ( json && json.data && json.data.message ) || i18n.error || 'No pudimos enviar tu mensaje.';
					error.hidden = false;
				}
			} )
			.catch( function () {
				if ( error ) {
					error.textContent = i18n.error || 'No pudimos enviar tu mensaje.';
					error.hidden = false;
				}
			} )
			.finally( function () {
				if ( button ) {
					button.disabled = false;
				}
				if ( label ) {
					label.textContent = original;
				}
			} );
	} );
}() );

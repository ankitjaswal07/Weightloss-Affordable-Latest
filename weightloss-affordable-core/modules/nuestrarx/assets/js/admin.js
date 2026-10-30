/**
 * NuestraRx Core — admin repeaters and guards.
 *
 * @package NuestraRx_Core
 */

( function () {
	'use strict';

	function $$( selector, context ) {
		return Array.prototype.slice.call( ( context || document ).querySelectorAll( selector ) );
	}

	/**
	 * Highest numeric index used by rows in a repeater.
	 *
	 * @param {HTMLElement} repeater Repeater tbody.
	 * @return {number} Next index.
	 */
	function nextIndex( repeater ) {
		var max = -1;
		$$( '[name]', repeater ).forEach( function ( input ) {
			var match = ( input.getAttribute( 'name' ) || '' ).match( /\[(\d+)\]/ );
			if ( match ) {
				max = Math.max( max, parseInt( match[1], 10 ) );
			}
		} );
		return max + 1;
	}

	/**
	 * Find the template that belongs to a repeater.
	 *
	 * @param {HTMLTableSectionElement} repeater Repeater body.
	 * @param {string}                  key      Repeater key.
	 * @return {HTMLTemplateElement|null} Template.
	 */
	function templateFor( repeater, key ) {
		var template = repeater.parentNode.querySelector( 'template[data-nrx-template="' + key + '"]' );
		if ( template ) {
			return template;
		}
		var container = repeater.closest( '.nrx-step, .nrx-quiz-settings, .nrx-card, form' );
		return container ? container.querySelector( 'template[data-nrx-template="' + key + '"]' ) : null;
	}

	document.addEventListener( 'click', function ( event ) {
		var add = event.target.closest( '.nrx-add-row' );
		if ( add ) {
			event.preventDefault();
			var key = add.getAttribute( 'data-target' );
			var scope = add.closest( '.nrx-step, .nrx-quiz-settings, .nrx-card, form' ) || document;
			var repeater = scope.querySelector( '[data-nrx-repeater="' + key + '"]' );
			if ( ! repeater ) {
				return;
			}
			var template = templateFor( repeater, key );
			if ( ! template || ! template.content ) {
				return;
			}
			var index = nextIndex( repeater );
			var html = template.innerHTML.replace( /__i__/g, String( index ) );
			var holder = document.createElement( 'tbody' );
			holder.innerHTML = html;
			while ( holder.firstElementChild ) {
				repeater.appendChild( holder.firstElementChild );
			}
			var first = repeater.lastElementChild && repeater.lastElementChild.querySelector( 'input, select, textarea' );
			if ( first ) {
				first.focus( { preventScroll: false } );
			}
			return;
		}

		var remove = event.target.closest( '.nrx-remove-row' );
		if ( remove ) {
			event.preventDefault();
			var row = remove.closest( 'tr' );
			if ( ! row ) {
				return;
			}
			var body = row.parentNode;
			var phrase = '¿Eliminar esta fila?';
			if ( ! window.confirm( phrase ) ) {
				return;
			}
			row.parentNode.removeChild( row );
			// Keep at least one visible row for repeaters the server expects.
			if ( ! body.querySelector( 'tr' ) ) {
				var trigger = body.closest( '.nrx-step, .nrx-quiz-settings, .nrx-card, form' );
				var addButton = trigger ? trigger.querySelector( '.nrx-add-row' ) : null;
				if ( addButton ) {
					addButton.click();
				}
			}
		}
	} );

	/**
	 * Warn before leaving a dirty quiz form.
	 */
	var form = document.querySelector( '.nrx-quiz-form' );
	if ( form ) {
		var dirty = false;
		form.addEventListener( 'input', function () {
			dirty = true;
		} );
		form.addEventListener( 'submit', function () {
			dirty = false;
		} );
		window.addEventListener( 'beforeunload', function ( event ) {
			if ( ! dirty ) {
				return;
			}
			event.preventDefault();
			event.returnValue = '';
		} );
	}

	/**
	 * Serialize the quiz builder into one JSON field before submitting.
	 *
	 * A full 17-step quiz has well over a thousand inputs once every option,
	 * field, checkbox, condition and rule is expanded, and PHP's default
	 * max_input_vars of 1000 silently truncates the POST: steps disappear
	 * without any error. Sending a single JSON field keeps the request small
	 * whatever the quiz grows to. Without JavaScript the plain form still
	 * posts, exactly as before.
	 */
	var quizForm = document.querySelector( '.nrx-quiz-form' );

	/**
	 * Turn a field name such as nrx_quiz[steps][0][title] into path segments.
	 *
	 * @param {string} name Field name.
	 * @return {Array<string>} Segments.
	 */
	function namePath( name ) {
		var path = [];
		name.replace( /([^\[\]]+)|\[(\d*)\]/g, function ( match, bare, index ) {
			path.push( bare !== undefined ? bare : index );
			return match;
		} );
		return path;
	}

	/**
	 * Assign a value into a nested object following a path.
	 *
	 * @param {Object} target Nested object.
	 * @param {Array}  path   Segments.
	 * @param {*}      value  Value.
	 */
	function assignPath( target, path, value ) {
		var node = target;
		for ( var i = 0; i < path.length - 1; i++ ) {
			var key = path[ i ];
			if ( node[ key ] === undefined || node[ key ] === null || typeof node[ key ] !== 'object' ) {
				node[ key ] = /^\d+$/.test( path[ i + 1 ] ) ? [] : {};
			}
			node = node[ key ];
		}
		var last = path[ path.length - 1 ];
		if ( last === '' || last === undefined ) {
			node.push( value );
		} else {
			node[ last ] = value;
		}
	}

	/**
	 * Collect every named control in the form.
	 *
	 * @param {HTMLFormElement} form Quiz form.
	 * @return {Object} Structured payload.
	 */
	function collectQuiz( form ) {
		var payload = {};
		$$( 'input[name^="nrx_quiz"], select[name^="nrx_quiz"], textarea[name^="nrx_quiz"]', form ).forEach( function ( field ) {
			if ( field.disabled || ! field.name || 'nrx_quiz_action' === field.name ) {
				return;
			}
			if ( ( field.type === 'checkbox' || field.type === 'radio' ) && ! field.checked ) {
				return;
			}
			var value = field.value;
			var path = namePath( field.name );
			if ( path[0] !== 'nrx_quiz' ) {
				return;
			}
			path.shift();
			if ( field.multiple ) {
				value = $$( 'option', field ).filter( function ( option ) {
					return option.selected;
				} ).map( function ( option ) {
					return option.value;
				} );
			}
			assignPath( payload, path, value );
		} );
		return payload;
	}

	if ( quizForm ) {
		quizForm.addEventListener( 'submit', function () {
			if ( quizForm.getAttribute( 'data-nrx-json' ) === '1' ) {
				return;
			}
			var field = quizForm.querySelector( 'input[name="nrx_quiz_payload"]' );
			if ( ! field ) {
				return;
			}
			var payload = collectQuiz( quizForm );
			field.value = JSON.stringify( payload );
			quizForm.setAttribute( 'data-nrx-json', '1' );

			// Remove the individual fields from the submission. They stay in the
			// DOM (and stay editable) until the page navigates away. The button
			// that triggered the submit keeps its name so the action survives.
			$$( 'input[name^="nrx_quiz"], select[name^="nrx_quiz"], textarea[name^="nrx_quiz"]', quizForm ).forEach( function ( input ) {
				if ( input !== field ) {
					input.disabled = true;
				}
			} );
		} );
	}

	/**
	 * Destructive actions need an explicit confirmation.
	 */
	$$( '[data-nrx-confirm]' ).forEach( function ( element ) {
		element.addEventListener( 'click', function ( event ) {
			if ( ! window.confirm( element.getAttribute( 'data-nrx-confirm' ) ) ) {
				event.preventDefault();
			}
		} );
	} );

	/**
	 * Toggle the step type hint: only the visible panel matters.
	 */
	$$( '.nrx-step-type' ).forEach( function ( select ) {
		select.addEventListener( 'change', function () {
			var step = select.closest( '.nrx-step' );
			if ( ! step ) {
				return;
			}
			var note = step.querySelector( '.nrx-type-note' );
			if ( note ) {
				note.textContent = 'Tipo cambiado a "' + select.options[ select.selectedIndex ].text + '". Guarda el borrador para que aparezcan los campos correspondientes.';
				note.hidden = false;
			}
		} );
	} );
}() );

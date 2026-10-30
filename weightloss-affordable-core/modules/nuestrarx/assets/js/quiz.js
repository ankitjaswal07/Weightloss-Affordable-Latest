/**
 * NuestraRx — eligibility quiz.
 *
 * Server-renders every step; this file decides which step is visible,
 * validates it, keeps the progress bar honest, mirrors the clinical
 * conditions used by nrx_quiz_step_visible() and submits over AJAX.
 *
 * @package NuestraRx_Core
 */

( function () {
	'use strict';

	var root = document.querySelector( '[data-nrx-quiz]' );
	if ( ! root ) {
		return;
	}

	var config = window.NRX_QUIZ || {};
	var i18n = config.i18n || {};
	var form = root.querySelector( '[data-nrx-quiz-form]' );
	var shell = root.querySelector( '.nrx-quiz-shell' );
	var intro = root.querySelector( '.nrx-quiz-intro' );
	var resultBox = root.querySelector( '[data-nrx-result]' );
	var nextButton = root.querySelector( '[data-nrx-next]' );
	var nextLabel = root.querySelector( '[data-nrx-next-label]' );
	var backButton = root.querySelector( '[data-nrx-back]' );
	var errorBox = root.querySelector( '[data-nrx-error]' );
	var phaseLabel = root.querySelector( '[data-nrx-phase]' );
	var countLabel = root.querySelector( '[data-nrx-progress-count]' );
	var progressBar = root.querySelector( '[data-nrx-progressbar]' );
	var progressFill = root.querySelector( '[data-nrx-progressbar-fill]' );
	var startTime = Date.now();
	var sending = false;
	var current = 0;

	var steps = slice( root.querySelectorAll( '[data-nrx-step]' ) );
	var parsed = {};
	try {
		parsed = JSON.parse( root.querySelector( '[data-nrx-quiz-config]' ).textContent );
	} catch ( e ) {
		parsed = { steps: [], minBmi: parseFloat( config.minBmi ) || 25 };
	}

	var stepConfig = {};
	( parsed.steps || [] ).forEach( function ( step ) {
		if ( step && step.id ) {
			stepConfig[ step.id ] = step;
		}
	} );

	var minBmi = parseFloat( parsed.minBmi || config.minBmi || 25 );
	var unavailable = ( config.unavailable || [] ).map( function ( code ) {
		return String( code ).toUpperCase();
	} );

	function slice( list ) {
		return Array.prototype.slice.call( list );
	}

	function $( selector, context ) {
		return ( context || root ).querySelector( selector );
	}

	function $$( selector, context ) {
		return slice( ( context || root ).querySelectorAll( selector ) );
	}

	function stepId( step ) {
		return step.getAttribute( 'data-nrx-step' );
	}

	function stepConf( step ) {
		return stepConfig[ stepId( step ) ] || {
			id: stepId( step ),
			type: step.getAttribute( 'data-type' ) || 'radio',
			field: step.getAttribute( 'data-field' ) || '',
			required: step.getAttribute( 'data-required' ) === '1'
		};
	}

	/* ------------------------------------------------------------- answers */

	function readAnswers() {
		var answers = {};
		$$( 'input, select, textarea', form ).forEach( function ( input ) {
			var name = input.getAttribute( 'name' ) || '';
			var match = name.match( /^answers\[([^\]]+)\](\[\])?$/ );
			if ( ! match ) {
				return;
			}
			var key = match[1];
			var multiple = !! match[2] || input.type === 'checkbox';
			if ( input.type === 'radio' ) {
				if ( ! input.checked ) {
					return;
				}
				answers[ key ] = input.value;
				return;
			}
			if ( input.type === 'checkbox' ) {
				if ( ! input.checked ) {
					return;
				}
				if ( multiple ) {
					if ( ! Array.isArray( answers[ key ] ) ) {
						answers[ key ] = [];
					}
					answers[ key ].push( input.value );
				} else {
					answers[ key ] = input.value === '1' ? 1 : input.value;
				}
				return;
			}
			if ( input.type === 'hidden' && key === 'bmi' ) {
				answers[ key ] = input.value;
				return;
			}
			if ( '' !== input.value ) {
				answers[ key ] = input.value;
			}
		} );
		return answers;
	}

	/* ------------------------------------------------------------- branching */

	function ageFromDate( value ) {
		var parts = String( value || '' ).split( '-' );
		if ( parts.length !== 3 ) {
			return null;
		}
		var birth = new Date( Number( parts[0] ), Number( parts[1] ) - 1, Number( parts[2] ) );
		if ( isNaN( birth.getTime() ) ) {
			return null;
		}
		var now = new Date();
		var age = now.getFullYear() - birth.getFullYear();
		var month = now.getMonth() - birth.getMonth();
		if ( month < 0 || ( month === 0 && now.getDate() < birth.getDate() ) ) {
			age--;
		}
		return age < 0 ? null : age;
	}

	function conditionMet( condition, answers ) {
		var field = condition.field || '';
		var operator = condition.operator || 'equals';
		var expected = condition.value === undefined ? '' : String( condition.value );
		var actual = Object.prototype.hasOwnProperty.call( answers, field ) ? answers[ field ] : null;
		var isArray = Array.isArray( actual );
		var actualString = isArray ? actual.map( String ) : ( actual === null ? '' : String( actual ) );

		switch ( operator ) {
			case 'is_set':
				return ! ( actual === null || actual === '' || ( isArray && actual.length === 0 ) );
			case 'is_empty':
				return actual === null || actual === '' || ( isArray && actual.length === 0 );
			case 'equals':
				return isArray ? actualString.indexOf( expected ) !== -1 : actualString === expected;
			case 'not_equals':
				return isArray ? actualString.indexOf( expected ) === -1 : actualString !== expected;
			case 'in':
				var list = expected.split( ',' ).map( function ( item ) {
					return item.trim();
				} );
				return isArray ? actualString.some( function ( item ) {
					return list.indexOf( item ) !== -1;
				} ) : list.indexOf( actualString ) !== -1;
			case 'not_in':
				var list2 = expected.split( ',' ).map( function ( item ) {
					return item.trim();
				} );
				return isArray ? ! actualString.some( function ( item ) {
					return list2.indexOf( item ) !== -1;
				} ) : list2.indexOf( actualString ) === -1;
			case 'includes':
				return isArray && actualString.indexOf( expected ) !== -1;
			case 'not_includes':
				return ! isArray || actualString.indexOf( expected ) === -1;
			case 'gt':
				return actual !== null && actual !== '' && ! isNaN( actual ) && parseFloat( actual ) > parseFloat( expected );
			case 'gte':
				return actual !== null && actual !== '' && ! isNaN( actual ) && parseFloat( actual ) >= parseFloat( expected );
			case 'lt':
				return actual !== null && actual !== '' && ! isNaN( actual ) && parseFloat( actual ) < parseFloat( expected );
			case 'lte':
				return actual !== null && actual !== '' && ! isNaN( actual ) && parseFloat( actual ) <= parseFloat( expected );
			case 'age_under':
				var ageUnder = ageFromDate( actualString );
				return ageUnder !== null && ageUnder < parseInt( expected, 10 );
			case 'age_over':
				var ageOver = ageFromDate( actualString );
				return ageOver !== null && ageOver > parseInt( expected, 10 );
			case 'state_unavailable':
				return '' !== actualString && unavailable.indexOf( String( actualString ).toUpperCase() ) !== -1;
		}
		return false;
	}

	function isVisible( step, answers ) {
		var conf = stepConf( step );
		var conditions = conf.conditions || [];
		if ( ! conditions.length ) {
			return true;
		}
		var match = conf.condition_match === 'any' ? 'any' : 'all';
		var results = conditions.map( function ( condition ) {
			return conditionMet( condition, answers );
		} );
		return match === 'any' ? results.indexOf( true ) !== -1 : results.indexOf( false ) === -1;
	}

	function visibleSteps( answers ) {
		return steps.filter( function ( step ) {
			return isVisible( step, answers );
		} );
	}

	/* ------------------------------------------------------------- progress */

	function paint( answers, focus ) {
		var list = visibleSteps( answers || readAnswers() );
		if ( ! list.length ) {
			list = steps.slice( 0, 1 );
		}
		if ( current >= list.length ) {
			current = list.length - 1;
		}
		if ( current < 0 ) {
			current = 0;
		}

		steps.forEach( function ( step ) {
			step.hidden = true;
			step.classList.remove( 'is-current' );
		} );

		var active = list[ current ];
		active.hidden = false;
		active.classList.add( 'is-current' );

		var conf = stepConf( active );
		var total = list.length;
		var percent = Math.round( ( ( current + 1 ) / total ) * 100 );

		if ( phaseLabel ) {
			var phase = active.querySelector( '.nrx-step-phase' );
			phaseLabel.textContent = phase ? phase.textContent : '';
		}
		if ( countLabel ) {
			countLabel.textContent = ( i18n.step || 'Paso' ) + ' ' + ( current + 1 ) + ' ' + ( i18n.of || 'de' ) + ' ' + total;
		}
		if ( progressBar ) {
			progressBar.setAttribute( 'aria-valuenow', String( percent ) );
		}
		if ( progressFill ) {
			progressFill.style.width = percent + '%';
		}
		if ( backButton ) {
			backButton.hidden = current === 0;
		}
		if ( nextLabel ) {
			var last = current === total - 1;
			nextLabel.textContent = last
				? ( i18n.submit || 'Enviar mi evaluación' )
				: ( i18n.next || 'Continuar' );
		}
		if ( nextButton ) {
			nextButton.classList.toggle( 'is-last', current === total - 1 );
			nextButton.className = nextButton.className.replace( /\s?is-consent\b/, '' );
			if ( conf.type === 'consent' ) {
				nextButton.classList.add( 'is-consent' );
			}
		}
		clearError();

		if ( focus ) {
			var legend = active.querySelector( '.nrx-step-title' );
			if ( legend ) {
				legend.setAttribute( 'tabindex', '-1' );
				legend.focus( { preventScroll: true } );
			}
			var top = root.getBoundingClientRect().top;
			if ( top < -20 || top > 140 ) {
				root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			}
		}
	}

	/* ------------------------------------------------------------- validation */

	function message( key ) {
		return i18n[ key ] || '';
	}

	function setError( fieldKey, text, target ) {
		var holder = $( '[data-error-for="' + fieldKey + '"]' );
		if ( holder ) {
			holder.textContent = text;
			holder.hidden = false;
		}
		if ( target ) {
			target.setAttribute( 'aria-invalid', 'true' );
			var field = target.closest( '.nrx-field' );
			if ( field ) {
				field.classList.add( 'has-error' );
			}
		}
	}

	function clearFieldError( fieldKey ) {
		var holder = $( '[data-error-for="' + fieldKey + '"]' );
		if ( holder ) {
			holder.textContent = '';
			holder.hidden = true;
		}
		$$( '[name="answers[' + fieldKey + ']"], [name="answers[' + fieldKey + '][]"]' ).forEach( function ( input ) {
			input.removeAttribute( 'aria-invalid' );
			var field = input.closest( '.nrx-field' );
			if ( field ) {
				field.classList.remove( 'has-error' );
			}
		} );
	}

	function clearError() {
		if ( errorBox ) {
			errorBox.hidden = true;
			errorBox.textContent = '';
		}
	}

	function showBanner( text ) {
		if ( ! errorBox ) {
			return;
		}
		errorBox.textContent = text;
		errorBox.hidden = false;
		errorBox.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
	}

	function validate( step ) {
		var conf = stepConf( step );
		var answers = readAnswers();
		var errors = 0;

		$$( '[data-error-for]', step ).forEach( function ( node ) {
			node.hidden = true;
			node.textContent = '';
		} );
		$$( '.nrx-field.has-error', step ).forEach( function ( node ) {
			node.classList.remove( 'has-error' );
		} );
		$$( '[aria-invalid="true"]', step ).forEach( function ( node ) {
			node.removeAttribute( 'aria-invalid' );
		} );

		var type = conf.type;

		if ( type === 'radio' || type === 'select' || type === 'state' ) {
			var key = type === 'state' ? 'state' : ( conf.field || '' );
			if ( conf.required && ! answers[ key ] ) {
				var input = $( '[name="answers[' + key + ']"]', step );
				setError( key, message( 'choose' ) || 'Elige una opción para continuar.', input );
				errors++;
			}
		}

		if ( type === 'checkboxes' ) {
			var ckey = conf.field || '';
			if ( conf.required && ( ! answers[ ckey ] || ! answers[ ckey ].length ) ) {
				setError( ckey, message( 'chooseMany' ) || 'Selecciona al menos una opción.', null );
				errors++;
			}
		}

		if ( type === 'measurements' ) {
			var ft = parseFloat( answers.height_ft || 0 );
			var inch = parseFloat( answers.height_in || 0 );
			var lb = parseFloat( answers.weight_lb || 0 );
			if ( ft < 3 || ft > 8 || inch < 0 || inch > 11 || lb < 70 || lb > 700 ) {
				setError( 'height_ft', 'Revisa tu estatura y peso (adultos: 3–8 pies, 70–700 lb).', $( '[name="answers[height_ft]"]', step ) );
				errors++;
			}
		}

		if ( type === 'fields' ) {
			( conf.fields || [] ).forEach( function ( sub ) {
				var value = answers[ sub.key ];
				var isEmpty = value === undefined || value === '' || value === null;
				var input = $( '[name="answers[' + sub.key + ']"]', step );
				var label = sub.required ? ( message( 'required' ) || 'Completa este campo para continuar.' ) : '';
				if ( sub.required && isEmpty ) {
					setError( sub.key, label, input );
					errors++;
					return;
				}
				if ( isEmpty ) {
					return;
				}
				if ( sub.type === 'email' && ! /.+@.+\..+/.test( String( value ) ) ) {
					setError( sub.key, message( 'required' ) || 'Enter a valid email address.', input );
					errors++;
				}
				if ( sub.type === 'tel' ) {
					var digits = String( value ).replace( /\D/g, '' );
					if ( digits.length === 11 && digits.charAt( 0 ) === '1' ) {
						digits = digits.slice( 1 );
					}
					if ( digits.length !== 10 ) {
						setError( sub.key, message( 'phone' ) || 'Escribe un teléfono válido de 10 dígitos.', input );
						errors++;
					}
				}
				if ( sub.type === 'date' ) {
					var age = ageFromDate( value );
					if ( age === null ) {
						setError( sub.key, message( 'date' ) || 'Escribe una fecha válida.', input );
						errors++;
					} else if ( age < 18 ) {
						setError( sub.key, message( 'underage' ) || 'Debes tener al menos 18 años.', input );
						errors++;
					}
				}
				if ( sub.pattern ) {
					try {
						var rx = new RegExp( sub.pattern );
						if ( ! rx.test( String( value ) ) ) {
							setError( sub.key, 'El formato no es válido.', input );
							errors++;
						}
					} catch ( e ) {}
				}
			} );
		}

		if ( type === 'consent' ) {
			( conf.checkboxes || [] ).forEach( function ( box ) {
				if ( box.required && ! answers[ box.key ] ) {
					setError( box.key, message( 'consent' ) || 'Marca esta casilla para continuar.', null );
					errors++;
				}
			} );
		}

		if ( ( type === 'text' || type === 'textarea' || type === 'number' || type === 'date' ) && conf.required ) {
			var tkey = conf.field || '';
			if ( ! answers[ tkey ] ) {
				setError( tkey, message( 'required' ) || 'Completa este campo para continuar.', $( '[name="answers[' + tkey + ']"]', step ) );
				errors++;
			}
		}

		if ( errors ) {
			showBanner( message( 'review' ) || 'Revisa las respuestas marcadas.' );
			var first = $( '[aria-invalid="true"]', step ) || $( '.nrx-field-error:not([hidden])', step );
			if ( first ) {
				first.focus && first.focus( { preventScroll: true } );
				first.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
			return false;
		}
		return true;
	}

	/* ------------------------------------------------------------- BMI */

	function updateBmi() {
		var card = root.querySelector( '[data-nrx-bmi-card]' );
		if ( ! card ) {
			return;
		}
		var answers = readAnswers();
		var ft = parseFloat( answers.height_ft || 0 );
		var inch = parseFloat( answers.height_in || 0 );
		var lb = parseFloat( answers.weight_lb || 0 );
		var value = card.querySelector( '[data-nrx-bmi]' );
		var status = card.querySelector( '[data-nrx-bmi-status]' );
		var hidden = card.querySelector( '[data-nrx-bmi-input]' );
		var totalInches = ( ft * 12 ) + inch;

		if ( ! totalInches || ! lb ) {
			value.textContent = '—';
			status.textContent = '';
			hidden.value = '';
			card.classList.remove( 'is-low', 'is-ok' );
			return;
		}

		var bmi = ( 703 * lb ) / ( totalInches * totalInches );
		var rounded = Math.round( bmi * 10 ) / 10;
		hidden.value = rounded.toFixed( 1 );
		value.textContent = rounded.toFixed( 1 ) + ' ' + ( i18n.bmiValue || 'IMC' );
		card.classList.toggle( 'is-low', rounded < minBmi );
		card.classList.toggle( 'is-ok', rounded >= minBmi );
		status.textContent = rounded < minBmi
			? ( message( 'bmiLow' ) || 'Tu IMC está por debajo del mínimo.' )
			: '';
	}

	/* ------------------------------------------------------------- states */

	function updateStates() {
		$$( 'select[data-nrx-state], select[name="answers[state]"]', form ).forEach( function ( select ) {
			var note = select.closest( '.nrx-field' ) && select.closest( '.nrx-field' ).querySelector( '[data-nrx-state-note]' );
			if ( ! note ) {
				var parent = select.closest( '.nrx-composite' ) || select.closest( '.nrx-step' );
				note = parent ? parent.querySelector( '[data-nrx-state-note]' ) : null;
			}
			if ( ! note ) {
				return;
			}
			var code = ( select.value || '' ).toUpperCase();
			if ( ! code ) {
				note.hidden = true;
				note.textContent = '';
				return;
			}
			var blocked = unavailable.indexOf( code ) !== -1;
			note.hidden = false;
			note.textContent = blocked
				? ( message( 'stateUnavailable' ) || 'Este estado aún no está disponible.' )
				: ( message( 'stateAvailable' ) || '' );
			note.classList.toggle( 'is-bad', blocked );
			note.classList.toggle( 'is-ok', ! blocked );
		} );
	}

	/* ------------------------------------------------------------- exclusive */

	function enforceExclusive( input ) {
		var grid = input.closest( '.nrx-choice-grid' );
		if ( ! grid ) {
			return;
		}
		if ( input.checked && input.getAttribute( 'data-exclusive' ) === '1' ) {
			$$( 'input', grid ).forEach( function ( other ) {
				if ( other !== input && other.checked ) {
					other.checked = false;
				}
			} );
			return;
		}
		if ( input.checked ) {
			$$( 'input[data-exclusive="1"]', grid ).forEach( function ( exclusive ) {
				if ( exclusive.checked && exclusive !== input ) {
					exclusive.checked = false;
				}
			} );
		}
	}

	/* ------------------------------------------------------------- submit */

	function submit( declined ) {
		if ( sending ) {
			return;
		}
		sending = true;
		clearError();
		if ( nextButton ) {
			nextButton.classList.add( 'is-loading' );
			nextButton.disabled = true;
		}

		var elapsed = Math.round( ( Date.now() - startTime ) / 1000 );
		var wait = Math.max( 0, 9 - elapsed ) * 1000;
		var payload = new FormData();
		payload.append( 'action', 'nrx_quiz_submit' );
		payload.append( 'nonce', config.nonce || '' );
		var answers = readAnswers();
		var treatmentInput = document.querySelector( '[data-nrx-selected-treatment]' );
		var planInput = document.querySelector( '[data-nrx-selected-plan]' );
		var params = new URLSearchParams( window.location.search );
		answers.selected_treatment = ( treatmentInput && treatmentInput.value ) || params.get( 'treatment' ) || '';
		answers.selected_plan = ( planInput && planInput.value ) || params.get( 'plan' ) || '';
		payload.append( 'answers', JSON.stringify( answers ) );
		payload.append( 'elapsed', String( elapsed + Math.round( wait / 1000 ) ) );
		if ( declined ) {
			payload.append( 'declined', '1' );
		}

		var send = function () {
			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: payload
			} )
				.then( function ( response ) {
					return response.json().then( function ( json ) {
						return { ok: response.ok, json: json };
					} );
				} )
				.then( function ( result ) {
					var json = result.json || {};
					if ( json.success ) {
						finish( json.data || {} );
						return;
					}
					var data = json.data || {};
					if ( data.fields ) {
						Object.keys( data.fields ).forEach( function ( key ) {
							setError( key, data.fields[ key ], null );
						} );
						showBanner( data.message || message( 'review' ) );
					} else {
						showBanner( data.message || message( 'network' ) );
					}
					reset();
				} )
				.catch( function () {
					showBanner( message( 'network' ) );
					reset();
				} );
		};

		if ( wait ) {
			window.setTimeout( send, wait );
		} else {
			send();
		}
	}

	function reset() {
		sending = false;
		if ( nextButton ) {
			nextButton.classList.remove( 'is-loading' );
			nextButton.disabled = false;
		}
	}

	function finish( data ) {
		sending = false;
		if ( data.redirect ) {
			window.location.href = data.redirect;
			return;
		}
		if ( shell ) {
			shell.hidden = true;
		}
		if ( intro ) {
			intro.hidden = true;
		}
		if ( nextButton ) {
			nextButton.classList.remove( 'is-loading' );
			nextButton.disabled = false;
		}
		if ( resultBox ) {
			resultBox.innerHTML = data.html || '';
			resultBox.hidden = false;
			root.classList.add( 'nrx-quiz-complete' );
			var title = resultBox.querySelector( '[data-nrx-result-title]' );
			if ( title ) {
				title.focus( { preventScroll: true } );
			}
			root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			if ( config.analytics && window.dataLayer ) {
				window.dataLayer.push( { event: 'nrx_quiz_complete', outcome: data.outcome || '' } );
			}
		}
	}

	function restart() {
		form.reset();
		steps.forEach( function ( step ) {
			$$( '[data-error-for]', step ).forEach( function ( node ) {
				node.hidden = true;
				node.textContent = '';
			} );
		} );
		current = 0;
		root.classList.remove( 'nrx-quiz-complete' );
		if ( resultBox ) {
			resultBox.hidden = true;
			resultBox.innerHTML = '';
		}
		if ( shell ) {
			shell.hidden = false;
		}
		if ( intro ) {
			intro.hidden = false;
		}
		updateBmi();
		updateStates();
		paint( readAnswers(), true );
	}

	/* ------------------------------------------------------------- wiring */

	form.addEventListener( 'change', function ( event ) {
		var target = event.target;
		if ( target.type === 'checkbox' || target.type === 'radio' ) {
			enforceExclusive( target );
		}
		var match = ( target.getAttribute( 'name' ) || '' ).match( /^answers\[([^\]]+)\]/ );
		if ( match ) {
			clearFieldError( match[1] );
		}
		if ( target.name === 'answers[height_ft]' || target.name === 'answers[height_in]' || target.name === 'answers[weight_lb]' ) {
			updateBmi();
		}
		if ( target.hasAttribute && ( target.hasAttribute( 'data-nrx-state' ) || target.name === 'answers[state]' ) ) {
			updateStates();
		}
		paint( readAnswers(), false );
	} );

	form.addEventListener( 'input', function ( event ) {
		var target = event.target;
		if ( target.name === 'answers[height_ft]' || target.name === 'answers[height_in]' || target.name === 'answers[weight_lb]' ) {
			updateBmi();
		}
		var match = ( target.getAttribute( 'name' ) || '' ).match( /^answers\[([^\]]+)\]/ );
		if ( match ) {
			clearFieldError( match[1] );
		}
	} );

	form.addEventListener( 'submit', function ( event ) {
		event.preventDefault();
	} );

	form.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Enter' && event.target.tagName !== 'TEXTAREA' ) {
			event.preventDefault();
			nextButton && nextButton.click();
		}
	} );

	nextButton && nextButton.addEventListener( 'click', function () {
		var answers = readAnswers();
		var list = visibleSteps( answers );
		var step = list[ current ] || steps[ 0 ];
		if ( ! validate( step ) ) {
			return;
		}
		if ( current < list.length - 1 ) {
			current++;
			paint( readAnswers(), true );
			if ( config.analytics && window.dataLayer ) {
				window.dataLayer.push( { event: 'nrx_quiz_step', step: stepId( step ) } );
			}
			return;
		}
		submit( false );
	} );

	backButton && backButton.addEventListener( 'click', function () {
		if ( current > 0 ) {
			current--;
			paint( readAnswers(), true );
		}
	} );

	root.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( '[data-nrx-decline]' ) ) {
			event.preventDefault();
			submit( true );
			return;
		}
		if ( event.target.closest( '[data-nrx-restart]' ) ) {
			event.preventDefault();
			restart();
		}
	} );

	paint( readAnswers(), false );
	updateBmi();
	updateStates();
}() );

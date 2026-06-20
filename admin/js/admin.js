/**
 * Tymeslot admin: connection check + live embed generator.
 *
 * The snippet is generated server-side (REST) from the same engine the
 * shortcode and block use, so the preview never drifts from real output.
 */
( function () {
	'use strict';

	var cfg = window.TymeslotAdmin || {};
	var i18n = cfg.i18n || {};

	function post( url, body ) {
		return fetch( url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce,
			},
			body: JSON.stringify( body || {} ),
		} ).then( function ( res ) {
			if ( ! res.ok ) {
				throw new Error( 'HTTP ' + res.status );
			}
			return res.json();
		} );
	}

	/* ----------------------------------------------------------------- *
	 * Setup tab: connection / embeddability check.
	 * ----------------------------------------------------------------- */
	function initConnectionCheck() {
		var btn = document.getElementById( 'tymeslot-check-btn' );
		var out = document.getElementById( 'tymeslot-check-result' );
		if ( ! btn || ! out ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			var username = ( document.getElementById( 'tymeslot-username' ) || {} ).value || '';
			var instance = ( document.getElementById( 'tymeslot-instance' ) || {} ).value || '';

			btn.disabled = true;
			out.hidden = false;
			out.className = 'tymeslot-check-result is-pending';
			out.textContent = i18n.checking || 'Checking…';

			post( cfg.restCheck, { username: username, instance_url: instance } )
				.then( function ( data ) {
					renderCheck( out, data );
				} )
				.catch( function () {
					out.className = 'tymeslot-check-result is-error';
					out.textContent = i18n.reqError || 'The check could not be completed.';
				} )
				.finally( function () {
					btn.disabled = false;
				} );
		} );
	}

	function renderCheck( out, data ) {
		var ok = data.status === 'ok';
		var warn = data.status === 'not_allowlisted';
		out.className =
			'tymeslot-check-result ' +
			( ok ? 'is-ok' : warn ? 'is-warn' : 'is-error' );

		var icon = ok ? '✓' : warn ? '!' : '×';
		var msg = document.createElement( 'p' );
		msg.innerHTML =
			'<span class="tymeslot-check-result__icon">' +
			icon +
			'</span>' +
			escapeHtml( data.message || '' );
		out.innerHTML = '';
		out.appendChild( msg );

		if ( warn && cfg.instanceUrl ) {
			var link = document.createElement( 'a' );
			link.href = cfg.instanceUrl + '/dashboard/embed';
			link.target = '_blank';
			link.rel = 'noopener noreferrer';
			link.className = 'button button-secondary';
			link.textContent = 'Open Embed → Security';
			out.appendChild( link );
		}
	}

	/* ----------------------------------------------------------------- *
	 * Generator tab: live snippet + preview.
	 * ----------------------------------------------------------------- */
	function initGenerator() {
		var root = document.getElementById( 'tymeslot-generator' );
		if ( ! root ) {
			return;
		}

		var codeEl = document.getElementById( 'tymeslot-snippet-code' );
		var frameWrap = document.getElementById( 'tymeslot-preview-frame' );
		var copyBtn = document.getElementById( 'tymeslot-copy-btn' );
		var modeInputs = root.querySelectorAll( 'input[name="tymeslot-mode"]' );

		var fields = {
			username: document.getElementById( 'tymeslot-gen-username' ),
			theme: document.getElementById( 'tymeslot-gen-theme' ),
			primary_color: document.getElementById( 'tymeslot-gen-color' ),
			locale: document.getElementById( 'tymeslot-gen-locale' ),
			layout: document.getElementById( 'tymeslot-gen-layout' ),
			initial_height: document.getElementById( 'tymeslot-gen-height' ),
			max_width: document.getElementById( 'tymeslot-gen-width' ),
			label: document.getElementById( 'tymeslot-gen-label' ),
		};

		function currentMode() {
			for ( var i = 0; i < modeInputs.length; i++ ) {
				if ( modeInputs[ i ].checked ) {
					return modeInputs[ i ].value;
				}
			}
			return 'inline';
		}

		function applyModeVisibility( mode ) {
			root.querySelectorAll( '[data-mode-only]' ).forEach( function ( el ) {
				el.style.display = el.getAttribute( 'data-mode-only' ) === mode ? '' : 'none';
			} );
			root.querySelectorAll( '[data-mode-hide]' ).forEach( function ( el ) {
				el.style.display =
					el.getAttribute( 'data-mode-hide' ).split( ' ' ).indexOf( mode ) === -1 ? '' : 'none';
			} );
			root.querySelectorAll( '[data-mode-show]' ).forEach( function ( el ) {
				el.style.display =
					el.getAttribute( 'data-mode-show' ).split( ' ' ).indexOf( mode ) !== -1 ? '' : 'none';
			} );

			root.querySelectorAll( '.tymeslot-mode' ).forEach( function ( label ) {
				var input = label.querySelector( 'input' );
				label.classList.toggle( 'is-active', input && input.checked );
			} );
		}

		function collect() {
			var body = { mode: currentMode() };
			Object.keys( fields ).forEach( function ( key ) {
				if ( fields[ key ] ) {
					body[ key ] = fields[ key ].value;
				}
			} );
			return body;
		}

		var timer = null;
		function refresh() {
			var mode = currentMode();
			applyModeVisibility( mode );

			post( cfg.restSnippet, collect() )
				.then( function ( data ) {
					codeEl.textContent = data.snippet || '';
					updatePreview( data.snippet || '', mode );
				} )
				.catch( function () {
					codeEl.textContent = '';
				} );
		}

		function debounced() {
			window.clearTimeout( timer );
			timer = window.setTimeout( refresh, 250 );
		}

		function updatePreview( snippet, mode ) {
			frameWrap.innerHTML = '';
			if ( ! snippet ) {
				return;
			}
			// Render the snippet inside a sandboxed iframe so its embed.js
			// runs exactly as it would on a real page.
			var iframe = document.createElement( 'iframe' );
			iframe.className = 'tymeslot-preview__iframe';
			iframe.setAttribute( 'title', 'Tymeslot preview' );
			frameWrap.appendChild( iframe );

			var doc = iframe.contentWindow.document;
			doc.open();
			doc.write(
				'<!DOCTYPE html><html><head><meta charset="utf-8">' +
					'<meta name="viewport" content="width=device-width, initial-scale=1">' +
					'<style>body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;padding:16px;}</style>' +
					'</head><body>' +
					snippet +
					'</body></html>'
			);
			doc.close();
		}

		modeInputs.forEach( function ( input ) {
			input.addEventListener( 'change', refresh );
		} );
		Object.keys( fields ).forEach( function ( key ) {
			if ( fields[ key ] ) {
				fields[ key ].addEventListener( 'input', debounced );
				fields[ key ].addEventListener( 'change', debounced );
			}
		} );

		if ( copyBtn ) {
			copyBtn.addEventListener( 'click', function () {
				var text = codeEl.textContent || '';
				navigator.clipboard.writeText( text ).then( function () {
					var original = copyBtn.getAttribute( 'data-default-label' );
					copyBtn.textContent = i18n.copied || 'Copied!';
					window.setTimeout( function () {
						copyBtn.textContent = original;
					}, 1600 );
				} );
			} );
		}

		refresh();
	}

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str;
		return div.innerHTML;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initConnectionCheck();
		initGenerator();
	} );
} )();

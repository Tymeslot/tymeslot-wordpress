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
	 * Setup tab: live embedding-status probe.
	 *
	 * We detect whether this site may embed the booking page the same way a
	 * real visitor's browser would: load the actual booking page in an
	 * iframe and watch for the "tymeslot-resize" postMessage that the
	 * Tymeslot embed runtime fires once it loads inside a frame.
	 *
	 * Why a browser probe and not a server-side request:
	 *   - It tests the true end-to-end path (CSP frame-ancestors + the embed
	 *     auth handshake) from this site's real origin — exactly what a
	 *     visitor hits. A server-side wp_remote_get can't, and silently
	 *     fails when WordPress runs somewhere that can't reach the instance
	 *     (e.g. a Docker network), which would be a misleading false alarm.
	 *   - No server-side fetch of a user-supplied URL means no SSRF surface.
	 *
	 * DEPENDENCY (cross-repo contract):
	 *   The success signal is the message **type string "tymeslot-resize"**,
	 *   posted by Tymeslot Core's `assets/js/iframe_embed.js`. This is NOT a
	 *   private detail — Core's own `embed.js` runtime keys off the same
	 *   message to size every embed, so it is a stable public protocol. If
	 *   that message type is ever renamed in Core, update TS_READY_MESSAGE
	 *   below to match. See the matching note in iframe_embed.js.
	 * ----------------------------------------------------------------- */
	var TS_READY_MESSAGE = 'tymeslot-resize';
	var PROBE_TIMEOUT_MS = 9000;

	function initEmbedStatus() {
		var statusEl = document.getElementById( 'tymeslot-embed-status' );
		if ( ! statusEl ) {
			return;
		}

		var btn = document.getElementById( 'tymeslot-check-btn' );
		var frameWrap = document.getElementById( 'tymeslot-livetest-frame' );
		var steps = document.getElementById( 'tymeslot-fix-steps' );
		var host = statusEl.getAttribute( 'data-host' ) || cfg.siteHost || 'this site';

		function fieldValue( id, fallback ) {
			var el = document.getElementById( id );
			var val = el && el.value ? el.value.trim() : '';
			return val || fallback || '';
		}

		function setStatus( state, text ) {
			statusEl.className = 'tymeslot-embed-status is-' + state;
			statusEl.querySelector( '.tymeslot-embed-status__text' ).textContent = text;
			var icons = { pending: '…', ok: '✓', blocked: '!', unreachable: '×', config: '!' };
			statusEl.querySelector( '.tymeslot-embed-status__icon' ).textContent = icons[ state ] || '…';
			if ( steps ) {
				steps.open = state === 'blocked';
			}
		}

		function run() {
			// Prefer the (possibly unsaved) field values so the user can test
			// before saving; fall back to the saved settings.
			var instance = fieldValue( 'tymeslot-instance', cfg.instanceUrl ).replace( /\/+$/, '' );
			var username = fieldValue( 'tymeslot-username', cfg.username );

			if ( btn ) {
				btn.disabled = true;
			}
			setStatus( 'pending', i18n.checking || 'Checking…' );

			probeEmbed( instance, username, frameWrap, function ( result ) {
				if ( btn ) {
					btn.disabled = false;
				}
				switch ( result ) {
					case 'ok':
						setStatus( 'ok', 'Embedding is enabled — your booking page loads on ' + host + '.' );
						break;
					case 'no_username':
						setStatus( 'config', 'Add your Tymeslot username above, then re-check.' );
						break;
					case 'bad_config':
						setStatus( 'config', 'Enter a valid instance URL above, then re-check.' );
						break;
					case 'unreachable':
						setStatus( 'unreachable', 'Couldn’t reach your Tymeslot instance at ' + instance + '. Check the instance URL.' );
						break;
					default:
						setStatus( 'blocked', host + ' can’t embed your booking page yet. Add it under Embed → Security (or check the username), then re-check.' );
				}
			} );
		}

		if ( btn ) {
			btn.addEventListener( 'click', run );
		}
		run();
	}

	/**
	 * Load the booking page in an iframe and resolve how the embed behaves.
	 * Resolves with: 'ok' | 'blocked' | 'unreachable' | 'no_username' |
	 * 'bad_config'.
	 *
	 * @param {string}      instance  Instance base URL.
	 * @param {string}      username  Booking username.
	 * @param {HTMLElement} frameWrap Container to render the live test into.
	 * @param {Function}    done      Called once with the result string.
	 */
	function probeEmbed( instance, username, frameWrap, done ) {
		var origin;
		try {
			origin = new URL( instance ).origin;
		} catch ( e ) {
			done( 'bad_config' );
			return;
		}
		if ( ! username ) {
			done( 'no_username' );
			return;
		}

		var settled = false;
		var reachable = false;

		function finish( result ) {
			if ( settled ) {
				return;
			}
			settled = true;
			window.clearTimeout( timer );
			window.removeEventListener( 'message', onMessage );
			done( result );
		}

		// Success: a "tymeslot-resize" message from the instance origin means
		// the booking page's JS ran inside the frame — i.e. framing succeeded.
		function onMessage( e ) {
			if ( e.origin !== origin ) {
				return;
			}
			if ( e.data && typeof e.data === 'object' && e.data.type === TS_READY_MESSAGE ) {
				finish( 'ok' );
			}
		}
		window.addEventListener( 'message', onMessage );

		// Reachability runs in the browser (works even when the WP server
		// can't reach the instance). A no-cors fetch resolves when the host
		// answers and rejects on a connection/DNS failure.
		fetch( origin + '/embed.js', { mode: 'no-cors', cache: 'no-store' } )
			.then( function () {
				reachable = true;
			} )
			.catch( function () {
				/* leave reachable=false; the timeout reports 'unreachable' */
			} );

		// The visible live test = the real booking page. On success the user
		// sees the booker; when blocked the frame stays empty — a self-
		// explanatory signal alongside the status badge.
		if ( frameWrap ) {
			frameWrap.innerHTML = '';
		}
		var iframe = document.createElement( 'iframe' );
		iframe.title = 'Tymeslot booking test';
		iframe.className = 'tymeslot-livetest__iframe';
		iframe.src =
			origin +
			'/' +
			encodeURIComponent( username ) +
			'?embed=1&parent-origin=' +
			encodeURIComponent( window.location.origin );
		if ( frameWrap ) {
			frameWrap.appendChild( iframe );
		} else {
			iframe.style.cssText = 'position:absolute;left:-99999px;width:360px;height:480px;border:0;';
			document.body.appendChild( iframe );
		}

		var timer = window.setTimeout( function () {
			finish( reachable ? 'blocked' : 'unreachable' );
		}, PROBE_TIMEOUT_MS );
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

	document.addEventListener( 'DOMContentLoaded', function () {
		initEmbedStatus();
		initGenerator();
	} );
} )();

/**
 * Tymeslot embed outcome detector (shared).
 *
 * Watches a booking iframe and decides whether it genuinely embedded the
 * booking page, was rejected by the instance (and fell back to a redirect),
 * or is unreachable. Used by both the front-end embed guard and the admin
 * "live embedding status" probe so the two can never disagree.
 *
 * Why this is not just "did we get one resize message":
 *   The booking page's STATIC (disconnected) render posts a single
 *   `tymeslot-resize` before the connected LiveView mount runs. When the
 *   embedding origin is not allow-listed, that connected mount redirects the
 *   iframe to the instance's marketing homepage. So a naive "first resize =
 *   success" check reports a false positive for a blocked domain. We instead
 *   require the booking page to render AND stay put: a second `load` event on
 *   the iframe is the redirect navigating away, which we treat as a rejection.
 *
 * Signals (all keyed to the instance origin):
 *   - `tymeslot-resize`        → the booking page rendered inside the frame.
 *   - a 2nd iframe `load`      → the frame navigated away (rejected fallback).
 *   - `tymeslot-embed-blocked` → explicit rejection notice (newer instances).
 *
 * The `tymeslot-resize` / `tymeslot-embed-blocked` message-type strings are a
 * public cross-repo contract with Core's iframe_embed.js / embed-blocked
 * notice. Do not rename without updating Core. See admin/js/admin.js too.
 */
( function () {
	'use strict';

	var READY_MESSAGE = 'tymeslot-resize';
	var BLOCKED_MESSAGE = 'tymeslot-embed-blocked';

	/**
	 * Watch an iframe and report its embedding outcome exactly once.
	 *
	 * @param {HTMLIFrameElement} iframe   The booking iframe to observe.
	 * @param {Object}            opts     { origin, settleMs, timeoutMs }.
	 * @param {Function}          onResult Called once with { status }, where
	 *                                     status is 'ok' | 'blocked' |
	 *                                     'unreachable'.
	 * @return {Function} Cleanup function (also runs automatically on decide).
	 */
	function watch( iframe, opts, onResult ) {
		opts = opts || {};

		var origin = opts.origin;
		var settleMs = opts.settleMs || 1500;
		var timeoutMs = opts.timeoutMs || 9000;

		var loadCount = 0;
		var confirmed = false;
		var decided = false;
		var settleTimer = null;
		var hardTimer = null;

		function decide( status ) {
			if ( decided ) {
				return;
			}
			decided = true;
			cleanup();
			onResult( { status: status } );
		}

		function onLoad() {
			loadCount++;
			// The booking page is the first load. A second load means the frame
			// navigated away — the rejected-embed redirect to the homepage.
			if ( loadCount >= 2 ) {
				decide( 'blocked' );
			}
		}

		function onMessage( e ) {
			if ( e.origin !== origin ) {
				return;
			}

			var data = e.data;
			if ( ! data || typeof data !== 'object' ) {
				return;
			}

			if ( data.type === BLOCKED_MESSAGE ) {
				decide( 'blocked' );
				return;
			}

			if ( data.type !== READY_MESSAGE ) {
				return;
			}

			// When several embeds share a page, attribute the message to this
			// iframe. contentWindow access is same-tab (not cross-origin) and
			// safe; guard it anyway.
			try {
				if ( iframe.contentWindow && e.source && e.source !== iframe.contentWindow ) {
					return;
				}
			} catch ( err ) {} // eslint-disable-line no-empty

			confirmed = true;

			// The booking page rendered. Give it a settle window: if it does not
			// navigate away (no 2nd load) it's a genuine, allowed embed.
			if ( ! settleTimer ) {
				settleTimer = setTimeout( function () {
					if ( loadCount < 2 ) {
						decide( 'ok' );
					}
				}, settleMs );
			}
		}

		function onTimeout() {
			if ( loadCount >= 2 ) {
				decide( 'blocked' );
			} else if ( confirmed ) {
				decide( 'ok' );
			} else {
				// Nothing ever rendered in the frame — unreachable instance,
				// wrong username, or a hard CSP frame-ancestors block.
				decide( 'unreachable' );
			}
		}

		function cleanup() {
			window.removeEventListener( 'message', onMessage );
			iframe.removeEventListener( 'load', onLoad );
			if ( settleTimer ) {
				clearTimeout( settleTimer );
			}
			if ( hardTimer ) {
				clearTimeout( hardTimer );
			}
		}

		iframe.addEventListener( 'load', onLoad );
		window.addEventListener( 'message', onMessage );
		hardTimer = setTimeout( onTimeout, timeoutMs );

		return cleanup;
	}

	window.TymeslotEmbedDetect = { watch: watch };
}() );

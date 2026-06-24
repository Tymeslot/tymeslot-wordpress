/**
 * Tymeslot front-end embed guard.
 *
 * Tymeslot blocks embedding by default: a booking page will only frame on a
 * site whose domain the account has allow-listed (Embed → Security). When the
 * domain is NOT allow-listed, the instance redirects the iframe to its own
 * marketing homepage — which would otherwise render *inside the customer's
 * page*. That is the worst possible failure mode, so this guard prevents it.
 *
 * For every embed it overlays a loading cover on the iframe and only lifts it
 * once the booking page is confirmed genuinely embedded (see embed-detect.js).
 * If the embed is rejected (the frame navigates away) or never loads, the
 * cover stays, the iframe is hidden, and a clear message is shown in its
 * place — with an extra, actionable hint for logged-in administrators. The
 * Tymeslot homepage never becomes visible.
 *
 * The snippet markup is byte-identical to Core's generator and is never
 * touched; this guard attaches to the iframe embed.js builds inside the
 * standard `#tymeslot-booking` / `[data-tymeslot-inline]` container (and the
 * `#tymeslot-modal` overlay for popup/floating modes).
 */
( function () {
	'use strict';

	var cfg = window.TymeslotEmbedGuard || {};
	var detect = window.TymeslotEmbedDetect;

	// Without an origin or the detector there is nothing safe to do; bail
	// rather than risk interfering with a working embed.
	if ( ! cfg.origin || ! detect ) {
		return;
	}

	var texts = cfg.texts || {};
	var watchOpts = {
		origin: cfg.origin,
		settleMs: cfg.settleMs || 1500,
		timeoutMs: cfg.timeoutMs || 9000,
	};

	var IFRAME_SELECTOR = 'iframe[title="Booking Widget"]';
	var INLINE_SELECTOR = '#tymeslot-booking, [data-tymeslot-inline]';

	/**
	 * Build the failure message shown in place of the booking page.
	 *
	 * @param {string} status 'blocked' or 'unreachable'.
	 * @return {HTMLElement}
	 */
	function buildMessage( status ) {
		var box = document.createElement( 'div' );
		box.className = 'tymeslot-guard-message';

		var text = document.createElement( 'p' );
		text.className = 'tymeslot-guard-message__text';
		text.textContent =
			'unreachable' === status
				? texts.unreachable || 'The booking page could not be loaded.'
				: texts.unavailable || 'Booking is currently unavailable.';
		box.appendChild( text );

		// Allow-list guidance is only useful (and only safe to surface) to the
		// site administrator, never to visitors.
		if ( cfg.isAdmin && texts.adminHint ) {
			var admin = document.createElement( 'div' );
			admin.className = 'tymeslot-guard-message__admin';

			var hint = document.createElement( 'p' );
			hint.textContent = texts.adminHint;
			admin.appendChild( hint );

			if ( cfg.securityUrl && texts.adminCta ) {
				var cta = document.createElement( 'a' );
				cta.className = 'tymeslot-guard-message__cta';
				cta.href = cfg.securityUrl;
				cta.target = '_blank';
				cta.rel = 'noopener noreferrer';
				cta.textContent = texts.adminCta;
				admin.appendChild( cta );
			}

			box.appendChild( admin );
		}

		return box;
	}

	/**
	 * Build the loading cover shown over the iframe until it is confirmed.
	 *
	 * @return {HTMLElement}
	 */
	function buildCover() {
		var cover = document.createElement( 'div' );
		cover.className = 'tymeslot-guard-cover';

		var spinner = document.createElement( 'div' );
		spinner.className = 'tymeslot-guard-spinner';
		cover.appendChild( spinner );

		if ( texts.loading ) {
			var label = document.createElement( 'span' );
			label.className = 'tymeslot-guard-loading';
			label.textContent = texts.loading;
			cover.appendChild( label );
		}

		return cover;
	}

	/**
	 * Attach the guard to a single booking iframe.
	 *
	 * @param {HTMLIFrameElement} iframe The booking iframe.
	 */
	function guard( iframe ) {
		if ( iframe.dataset.tymeslotGuarded ) {
			return;
		}
		iframe.dataset.tymeslotGuarded = 'true';

		// Overlay embed.js's wrapper (already position:relative). Be defensive
		// in case the host is statically positioned.
		var host = iframe.parentNode || iframe;
		if ( host.nodeType === 1 ) {
			var pos = window.getComputedStyle( host ).position;
			if ( 'static' === pos ) {
				host.style.position = 'relative';
			}
		}

		var cover = buildCover();
		host.appendChild( cover );

		detect.watch( iframe, watchOpts, function ( result ) {
			if ( 'ok' === result.status ) {
				if ( cover.parentNode ) {
					cover.parentNode.removeChild( cover );
				}
				return;
			}

			// Rejected or unreachable: hide the frame so the instance's
			// fallback page can never paint, and show our own message.
			iframe.style.visibility = 'hidden';
			iframe.style.opacity = '0';
			cover.classList.add( 'tymeslot-guard-cover--failed' );
			cover.textContent = '';
			cover.appendChild( buildMessage( result.status ) );
		} );
	}

	/**
	 * Watch a container that will (or already does) hold a booking iframe and
	 * guard the iframe as soon as embed.js inserts it.
	 *
	 * @param {HTMLElement} container Inline container or modal root.
	 */
	function watchForIframe( container ) {
		var existing = container.querySelector( IFRAME_SELECTOR );
		if ( existing ) {
			guard( existing );
			return;
		}

		var observer = new MutationObserver( function () {
			var iframe = container.querySelector( IFRAME_SELECTOR );
			if ( iframe ) {
				observer.disconnect();
				guard( iframe );
			}
		} );
		observer.observe( container, { childList: true, subtree: true } );
	}

	function initInline() {
		var containers = document.querySelectorAll( INLINE_SELECTOR );
		Array.prototype.forEach.call( containers, watchForIframe );
	}

	/**
	 * Popup and floating modes inject `#tymeslot-modal` on demand. Watch the
	 * body so each modal's iframe is guarded the moment it opens.
	 */
	function initModal() {
		var observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				Array.prototype.forEach.call( mutation.addedNodes, function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					var modal =
						'tymeslot-modal' === node.id
							? node
							: node.querySelector && node.querySelector( '#tymeslot-modal' );
					if ( modal ) {
						watchForIframe( modal );
					}
				} );
			} );
		} );
		observer.observe( document.body, { childList: true, subtree: true } );
	}

	function start() {
		initInline();
		if ( document.body ) {
			initModal();
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );

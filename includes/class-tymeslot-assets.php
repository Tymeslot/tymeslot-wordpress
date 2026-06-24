<?php
/**
 * Front-end asset loading: the Tymeslot embed runtime (`embed.js`) plus the
 * plugin's own embed guard.
 *
 * `embed.js` is loaded from the configured instance (never bundled) so it
 * always matches the booking page it talks to. The guard (detector + guard
 * script + styles) is plugin-owned and shipped locally. Everything is
 * registered up front but only enqueued when a shortcode or block actually
 * renders, keeping it off pages that don't use it.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and conditionally enqueues the embed runtime and guard.
 */
class Tymeslot_Assets {

	const HANDLE        = 'tymeslot-embed';
	const DETECT_HANDLE = 'tymeslot-embed-detect';
	const GUARD_HANDLE  = 'tymeslot-embed-guard';

	/**
	 * Whether enqueue has already been requested this request.
	 *
	 * @var bool
	 */
	private static $enqueued = false;

	/**
	 * Register the embed runtime and guard handles for on-demand enqueue.
	 *
	 * @return void
	 */
	public static function register() {
		$src = Tymeslot_Settings::instance_url() . '/embed.js';

		// Version param is omitted: embed.js is owned by the instance, which
		// handles its own cache-busting; appending our plugin version would
		// fight the instance's caching headers.
		wp_register_script( self::HANDLE, $src, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion

		// The guard's outcome detector, shared with the admin probe.
		wp_register_script(
			self::DETECT_HANDLE,
			TYMESLOT_URL . 'assets/js/embed-detect.js',
			array(),
			TYMESLOT_VERSION,
			true
		);

		// The front-end guard: overlays a cover and replaces a rejected embed
		// with a message instead of letting the instance homepage render.
		wp_register_script(
			self::GUARD_HANDLE,
			TYMESLOT_URL . 'assets/js/embed-guard.js',
			array( self::DETECT_HANDLE ),
			TYMESLOT_VERSION,
			true
		);

		wp_register_style(
			self::GUARD_HANDLE,
			TYMESLOT_URL . 'assets/css/embed-guard.css',
			array(),
			TYMESLOT_VERSION
		);
	}

	/**
	 * Enqueue the embed runtime and guard (idempotent within a request).
	 *
	 * Safe to call from within shortcode/block render callbacks: the scripts
	 * print in the footer.
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( self::$enqueued ) {
			return;
		}

		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			self::register();
		}

		wp_enqueue_script( self::HANDLE );
		wp_enqueue_script( self::GUARD_HANDLE );
		wp_enqueue_style( self::GUARD_HANDLE );
		self::localize_guard();

		self::$enqueued = true;
	}

	/**
	 * Pass the guard its runtime configuration: the instance origin to trust
	 * for embed messages, timing, the viewer's admin status (gating the
	 * allow-list hint), and translatable copy.
	 *
	 * @return void
	 */
	private static function localize_guard() {
		$instance = Tymeslot_Settings::instance_url();

		wp_localize_script(
			self::GUARD_HANDLE,
			'TymeslotEmbedGuard',
			array(
				'origin'      => self::instance_origin( $instance ),
				'securityUrl' => $instance . '/dashboard/embed',
				'isAdmin'     => current_user_can( 'manage_options' ),
				'settleMs'    => 1500,
				'timeoutMs'   => 9000,
				'texts'       => array(
					'loading'     => __( 'Loading booking page…', 'tymeslot' ),
					'unavailable' => __( 'Booking is currently unavailable. Please try again shortly.', 'tymeslot' ),
					'unreachable' => __( 'The booking page could not be loaded.', 'tymeslot' ),
					'adminHint'   => __( 'This site isn’t allowed to embed your Tymeslot booking page yet. In your Tymeslot dashboard, open Embed → Security and add this site’s domain to the allowed embed domains.', 'tymeslot' ),
					'adminCta'    => __( 'Open Embed → Security', 'tymeslot' ),
				),
			)
		);
	}

	/**
	 * Reduce an instance URL to its origin (scheme://host[:port]) — the value
	 * the guard compares incoming postMessage origins against.
	 *
	 * @param string $url Instance URL.
	 * @return string Origin, or the trimmed URL if it can't be parsed.
	 */
	private static function instance_origin( $url ) {
		$parts = wp_parse_url( $url );

		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return untrailingslashit( $url );
		}

		$origin = $parts['scheme'] . '://' . $parts['host'];

		if ( ! empty( $parts['port'] ) ) {
			$origin .= ':' . $parts['port'];
		}

		return $origin;
	}
}

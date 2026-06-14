<?php
/**
 * Front-end asset loading: the Tymeslot embed runtime (`embed.js`).
 *
 * The script is loaded from the configured instance (never bundled) so it
 * always matches the booking page it talks to. It is registered up front
 * but only enqueued when a shortcode or block actually renders, keeping it
 * off pages that don't use it.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and conditionally enqueues the embed runtime.
 */
class Tymeslot_Assets {

	const HANDLE = 'tymeslot-embed';

	/**
	 * Whether enqueue has already been requested this request.
	 *
	 * @var bool
	 */
	private static $enqueued = false;

	/**
	 * Register the embed script handle so it can be enqueued on demand.
	 *
	 * @return void
	 */
	public static function register() {
		$src = Tymeslot_Settings::instance_url() . '/embed.js';

		// Version param is omitted: embed.js is owned by the instance, which
		// handles its own cache-busting; appending our plugin version would
		// fight the instance's caching headers.
		wp_register_script( self::HANDLE, $src, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Enqueue the embed runtime (idempotent within a request).
	 *
	 * Safe to call from within shortcode/block render callbacks: the script
	 * prints in the footer with `async`.
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
		self::$enqueued = true;
	}
}

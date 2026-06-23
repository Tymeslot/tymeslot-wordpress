<?php
/**
 * Embed facade — the single entry point for turning a set of attributes
 * into a rendered snippet and loading the runtime it needs.
 *
 * Every surface (shortcode, block, REST preview) goes through here so the
 * "render the snippet, then enqueue embed.js unless it's a plain link"
 * policy lives in exactly one place.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders embeds and manages their runtime dependency.
 */
class Tymeslot_Embed {

	/**
	 * Render a snippet for output on the front end, enqueuing the embed
	 * runtime when the chosen mode needs it.
	 *
	 * @param string              $mode One of inline|popup|floating|link.
	 * @param array<string,mixed> $args Raw attributes for the snippet engine.
	 * @return string The snippet, or '' when it can't be built (no username).
	 */
	public static function render( $mode, array $args ) {
		$mode    = Tymeslot_Settings::sanitize_mode( $mode );
		$snippet = Tymeslot_Snippet::render( $mode, $args );

		if ( '' === $snippet ) {
			return '';
		}

		if ( self::needs_runtime( $mode ) ) {
			Tymeslot_Assets::enqueue();
		}

		return $snippet;
	}

	/**
	 * Build a snippet without touching the page's asset queue. Used for the
	 * admin generator preview, where embed.js loads inside the preview
	 * iframe from the snippet itself, not on the admin page.
	 *
	 * @param string              $mode One of inline|popup|floating|link.
	 * @param array<string,mixed> $args Raw attributes for the snippet engine.
	 * @return string
	 */
	public static function preview( $mode, array $args ) {
		return Tymeslot_Snippet::render( Tymeslot_Settings::sanitize_mode( $mode ), $args );
	}

	/**
	 * Whether a mode needs the embed.js runtime. The direct link is a plain
	 * anchor; every other mode is driven by embed.js.
	 *
	 * @param string $mode Sanitised mode.
	 * @return bool
	 */
	public static function needs_runtime( $mode ) {
		return 'link' !== Tymeslot_Settings::sanitize_mode( $mode );
	}
}

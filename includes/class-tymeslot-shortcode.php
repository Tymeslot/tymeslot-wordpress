<?php
/**
 * The `[tymeslot]` shortcode.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a booking embed from shortcode attributes.
 *
 * Example:
 *   [tymeslot username="sarah" mode="popup" theme="2" color="#14b8a6"
 *             locale="de" layout="column" height="700" width="1000"
 *             label="Book a call"]
 */
class Tymeslot_Shortcode {

	const TAG = 'tymeslot';

	/**
	 * Register the shortcode.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Render callback.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'username' => '',
				'mode'     => 'inline',
				'theme'    => '',
				'color'    => '',
				'locale'   => '',
				'layout'   => '',
				'height'   => '',
				'width'    => '',
				'label'    => '',
			),
			$atts,
			self::TAG
		);

		$mode    = Tymeslot_Settings::sanitize_mode( $atts['mode'] );
		$snippet = Tymeslot_Snippet::render(
			$mode,
			array(
				'username'       => $atts['username'],
				'theme'          => $atts['theme'],
				'primary_color'  => $atts['color'],
				'locale'         => $atts['locale'],
				'layout'         => $atts['layout'],
				'initial_height' => $atts['height'],
				'max_width'      => $atts['width'],
				'label'          => $atts['label'],
			)
		);

		if ( '' === $snippet ) {
			return self::missing_username_notice();
		}

		// The direct-link snippet is a plain anchor — no runtime needed.
		if ( 'link' !== $mode ) {
			Tymeslot_Assets::enqueue();
		}

		return $snippet;
	}

	/**
	 * Helpful inline notice shown to editors when no username is configured.
	 * Only rendered for users who can edit posts; visitors see nothing.
	 *
	 * @return string
	 */
	private static function missing_username_notice() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return '<p class="tymeslot-shortcode-notice" style="padding:12px 16px;border:1px dashed #14b8a6;border-radius:8px;color:#0f766e;background:#f0fdfa;font-size:14px;">'
			. esc_html__( 'Tymeslot: add a username to this shortcode (or set a default in Settings → Tymeslot) to show your booking page.', 'tymeslot' )
			. '</p>';
	}
}

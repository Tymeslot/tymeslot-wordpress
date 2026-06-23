<?php
/**
 * Snippet engine — the single source of truth for the embed markup.
 *
 * This is a faithful PHP port of the Tymeslot Core dashboard generator
 * `lib/tymeslot_web/live/dashboard/embed_settings/helpers.ex` (`embed_code/2`).
 * Output is byte-identical to the in-app generator for the default button
 * and link labels, so a snippet produced here behaves exactly like one
 * copied from the Tymeslot dashboard.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

// This file's entire job is to produce copyable <script src="…/embed.js">
// snippet TEXT for users to paste — these are output strings, not scripts the
// plugin loads, so the enqueue sniff does not apply. The runtime itself is
// loaded via wp_enqueue_script() in class-tymeslot-assets.php.
// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript

/**
 * Generates inline / popup / floating / link embed snippets.
 */
class Tymeslot_Snippet {

	/**
	 * Default popup button label (matches Core).
	 */
	const DEFAULT_BUTTON_LABEL = 'Book a Meeting';

	/**
	 * Default direct-link text (matches Core).
	 */
	const DEFAULT_LINK_LABEL = 'Schedule a meeting';

	/**
	 * Render a snippet for the given mode from a raw argument map.
	 *
	 * @param string              $mode One of inline|popup|floating|link.
	 * @param array<string,mixed> $args Raw attributes (username, theme, ...).
	 * @return string HTML snippet, or '' for an unknown/incomplete request.
	 */
	public static function render( $mode, array $args ) {
		$mode = Tymeslot_Settings::sanitize_mode( $mode );
		$opts = self::normalize( $args );

		if ( '' === $opts['username'] ) {
			return '';
		}

		switch ( $mode ) {
			case 'popup':
				return self::popup( $opts );
			case 'floating':
				return self::floating( $opts );
			case 'link':
				return self::link( $opts );
			case 'inline':
			default:
				return self::inline( $opts );
		}
	}

	/**
	 * Normalise raw args into the sanitised option set the builders expect.
	 *
	 * Pulls per-attribute defaults from saved settings so a bare shortcode
	 * or freshly inserted block inherits the site-wide appearance.
	 *
	 * @param array<string,mixed> $args Raw attributes.
	 * @return array<string,mixed>
	 */
	public static function normalize( array $args ) {
		$base     = Tymeslot_Settings::instance_url();
		$username = Tymeslot_Settings::sanitize_username(
			self::pick( $args, 'username', Tymeslot_Settings::get( 'username', '' ) )
		);

		return array(
			'username'       => $username,
			'base_url'       => $base,
			'booking_url'    => '' !== $username ? $base . '/' . $username : '',
			'theme'          => Tymeslot_Settings::sanitize_theme( self::pick( $args, 'theme', Tymeslot_Settings::get( 'theme', '' ) ) ),
			'primary_color'  => Tymeslot_Settings::sanitize_color( self::pick( $args, 'primary_color', Tymeslot_Settings::get( 'primary_color', '' ) ) ),
			'locale'         => Tymeslot_Settings::sanitize_locale( self::pick( $args, 'locale', Tymeslot_Settings::get( 'locale', '' ) ) ),
			'layout'         => self::clean_layout( self::pick( $args, 'layout', Tymeslot_Settings::get( 'layout', 'column' ) ) ),
			'initial_height' => Tymeslot_Settings::sanitize_int_in_range( self::pick( $args, 'initial_height', Tymeslot_Settings::get( 'initial_height', '' ) ), Tymeslot_Settings::MIN_DIMENSION, Tymeslot_Settings::MAX_DIMENSION ),
			'max_width'      => Tymeslot_Settings::sanitize_int_in_range( self::pick( $args, 'max_width', Tymeslot_Settings::get( 'max_width', '' ) ), Tymeslot_Settings::MIN_DIMENSION, Tymeslot_Settings::MAX_DIMENSION ),
			'label'          => self::sanitize_label( self::pick( $args, 'label', '' ) ),
		);
	}

	/**
	 * Inline `<div>` embed (mirrors Core `embed_code("inline", ...)`).
	 *
	 * @param array<string,mixed> $o Normalised options.
	 * @return string
	 */
	private static function inline( $o ) {
		$attrs = implode(
			'',
			array(
				self::data_attr( 'locale', $o['locale'] ),
				self::data_attr( 'theme', $o['theme'] ),
				self::data_attr( 'primary-color', $o['primary_color'] ),
				self::data_attr( 'layout', self::layout_override( $o['layout'] ) ),
				self::data_attr( 'initial-height', self::int_str( $o['initial_height'] ) ),
				self::data_attr( 'max-width', self::int_str( $o['max_width'] ) ),
			)
		);

		return "<!-- Tymeslot Inline -->\n"
			. '<div id="tymeslot-booking" data-username="' . esc_attr( $o['username'] ) . '"' . $attrs . "></div>\n"
			. '<script src="' . self::esc_src( $o['base_url'] ) . '/embed.js" async></script>';
	}

	/**
	 * Popup button embed (mirrors Core `embed_code("popup", ...)`).
	 *
	 * @param array<string,mixed> $o Normalised options.
	 * @return string
	 */
	private static function popup( $o ) {
		$js_options = self::build_js_options( $o );
		$label      = '' !== $o['label'] ? $o['label'] : self::DEFAULT_BUTTON_LABEL;

		return "<!-- Tymeslot Popup -->\n"
			. '<button onclick="if(window.TymeslotBooking){TymeslotBooking.open(\'' . $o['username'] . '\'' . $js_options . ")}else{alert('Booking system is currently unavailable.')}\">"
			. esc_html( $label ) . "</button>\n"
			. '<script src="' . self::esc_src( $o['base_url'] ) . '/embed.js" async></script>';
	}

	/**
	 * Floating-button embed (mirrors Core `embed_code("floating", ...)`).
	 *
	 * @param array<string,mixed> $o Normalised options.
	 * @return string
	 */
	private static function floating( $o ) {
		$js_options = self::build_js_options( $o );
		$src        = self::esc_src( $o['base_url'] );

		return "<!-- Tymeslot Floating Button -->\n"
			. '<script src="' . $src . "/embed.js\" async></script>\n"
			. "<script>\n"
			. "  (function() {\n"
			. "    var init = function() {\n"
			. "      if (window.TymeslotBooking) {\n"
			. "        TymeslotBooking.initFloating('" . $o['username'] . "'" . $js_options . ");\n"
			. "      } else {\n"
			. "        setTimeout(init, 100);\n"
			. "      }\n"
			. "    };\n"
			. "    init();\n"
			. "  })();\n"
			. '</script>';
	}

	/**
	 * Direct-link embed (mirrors Core `embed_code("link", ...)`).
	 *
	 * @param array<string,mixed> $o Normalised options.
	 * @return string
	 */
	private static function link( $o ) {
		$query = ( 'column' === self::layout_override( $o['layout'] ) ) ? '?layout=column' : '';
		$label = '' !== $o['label'] ? $o['label'] : self::DEFAULT_LINK_LABEL;

		return '<a href="' . self::esc_src( $o['booking_url'] ) . $query . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Build the JS options object passed as the 2nd argument to
	 * `TymeslotBooking.open` / `initFloating`.
	 *
	 * Key order is fixed to match the Core map's runtime enumeration order
	 * on the booking server (verified byte-for-byte against the live
	 * dashboard generator: layout, locale, theme, primaryColor, maxWidth).
	 * The order is cosmetic — embed.js reads a plain JS object — so a future
	 * Erlang/OTP map-ordering change would only affect snippet whitespace,
	 * never behaviour. Empty values are dropped; `maxWidth` is numeric
	 * (unquoted), the rest are single-quoted strings. Returns '' when empty.
	 *
	 * @param array<string,mixed> $o Normalised options.
	 * @return string
	 */
	private static function build_js_options( $o ) {
		$pairs = array(
			'layout'       => self::quote_str( self::layout_override( $o['layout'] ) ),
			'locale'       => self::quote_str( $o['locale'] ),
			'theme'        => self::quote_str( $o['theme'] ),
			'primaryColor' => self::quote_str( $o['primary_color'] ),
			'maxWidth'     => self::int_str( $o['max_width'] ),
		);

		$parts = array();
		foreach ( $pairs as $key => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}
			$parts[] = $key . ': ' . $value;
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return ', {' . implode( ', ', $parts ) . '}';
	}

	/**
	 * Emit ` data-name="value"` or '' for empty values.
	 *
	 * @param string      $name  Attribute name (without the `data-` prefix).
	 * @param string|null $value Attribute value.
	 * @return string
	 */
	private static function data_attr( $name, $value ) {
		if ( null === $value || '' === $value ) {
			return '';
		}

		// Values are already validated to a safe character set; esc_attr() is
		// belt-and-braces for the HTML-attribute context and a no-op for every
		// valid value, so it never changes the snippet a user copies.
		return ' data-' . $name . '="' . esc_attr( $value ) . '"';
	}

	/**
	 * Sanitise layout for the snippet emitter: returns `column`, `default`,
	 * or '' (absent). Unlike the settings sanitiser, an empty/invalid value
	 * stays empty here so an unset layout emits nothing — matching Core.
	 *
	 * @param mixed $value Raw layout.
	 * @return string
	 */
	private static function clean_layout( $value ) {
		$value = trim( (string) $value );

		if ( 'column' === $value || 'default' === $value ) {
			return $value;
		}

		return '';
	}

	/**
	 * Layout is only emitted when `column`; `default` matches the server
	 * default and produces no attribute/option (matches Core).
	 *
	 * @param string $value Sanitised layout.
	 * @return string|null `column` or null.
	 */
	private static function layout_override( $value ) {
		return ( 'column' === $value ) ? 'column' : null;
	}

	/**
	 * Wrap a non-empty string in single quotes for the JS options object.
	 *
	 * @param string|null $value Value.
	 * @return string|null Quoted value, or the original empty/null.
	 */
	private static function quote_str( $value ) {
		if ( null === $value || '' === $value ) {
			return $value;
		}

		return "'" . $value . "'";
	}

	/**
	 * Stringify an int option, or '' when unset.
	 *
	 * @param int|null $value Value.
	 * @return string
	 */
	private static function int_str( $value ) {
		return ( null === $value ) ? '' : (string) $value;
	}

	/**
	 * Escape a URL for an href/src context.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	private static function esc_src( $url ) {
		return esc_url( $url );
	}

	/**
	 * Sanitise a custom button/link label: plain text, no markup.
	 *
	 * @param mixed $value Raw label.
	 * @return string
	 */
	private static function sanitize_label( $value ) {
		return trim( wp_strip_all_tags( (string) $value ) );
	}

	/**
	 * Read a key from args, treating empty string as "use the fallback".
	 *
	 * @param array<string,mixed> $args     Source array.
	 * @param string              $key      Key to read.
	 * @param mixed               $fallback Value when missing or blank.
	 * @return mixed
	 */
	private static function pick( $args, $key, $fallback ) {
		if ( ! array_key_exists( $key, $args ) ) {
			return $fallback;
		}

		$value = $args[ $key ];

		if ( null === $value || '' === $value ) {
			return $fallback;
		}

		return $value;
	}
}

// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript

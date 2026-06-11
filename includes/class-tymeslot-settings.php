<?php
/**
 * Plugin settings: storage, defaults, sanitisation, and the constant
 * lookup tables (themes, locales, layouts) mirrored from Tymeslot Core.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings store backed by a single option array.
 */
class Tymeslot_Settings {

	const OPTION = 'tymeslot_settings';
	const GROUP  = 'tymeslot_settings_group';

	/**
	 * Default cloud instance. Self-hosters override this in Setup.
	 */
	const DEFAULT_INSTANCE = 'https://tymeslot.app';

	/**
	 * Booking themes, mirrored from `lib/tymeslot/themes/catalog.ex`.
	 *
	 * @return array<string,string> Map of theme id => label.
	 */
	public static function themes() {
		return array(
			'1' => __( 'Quill — elegant glassmorphism', 'tymeslot' ),
			'2' => __( 'Rhythm — immersive video background', 'tymeslot' ),
		);
	}

	/**
	 * Supported locales, mirrored from Core `config :tymeslot, :locales`.
	 *
	 * @return array<string,string> Map of locale code => native name.
	 */
	public static function locales() {
		return array(
			'en' => 'English',
			'de' => 'Deutsch',
			'uk' => 'Українська',
			'fr' => 'Français',
			'it' => 'Italiano',
		);
	}

	/**
	 * Layout options, mirrored from Core `themes/core/context.ex`.
	 *
	 * @return array<string,string> Map of layout value => label.
	 */
	public static function layouts() {
		return array(
			'column'  => __( 'Column — adapts to any container width', 'tymeslot' ),
			'default' => __( 'Default — centred, self-contained card', 'tymeslot' ),
		);
	}

	/**
	 * Embed modes offered to the user.
	 *
	 * @return array<string,string> Map of mode => label.
	 */
	public static function modes() {
		return array(
			'inline'   => __( 'Inline', 'tymeslot' ),
			'popup'    => __( 'Popup', 'tymeslot' ),
			'floating' => __( 'Floating button', 'tymeslot' ),
			'link'     => __( 'Direct link', 'tymeslot' ),
		);
	}

	/**
	 * Factory defaults for the option array.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return array(
			'instance_url'   => self::DEFAULT_INSTANCE,
			'username'       => '',
			'theme'          => '',
			'primary_color'  => '',
			'locale'         => '',
			'layout'         => 'column',
			'initial_height' => 700,
			'max_width'      => 1000,
		);
	}

	/**
	 * Merge stored settings over defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function all() {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Read a single setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback if unset.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$all = self::all();

		return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	/**
	 * The configured instance base URL, trailing slash stripped.
	 *
	 * @return string
	 */
	public static function instance_url() {
		$url = self::get( 'instance_url', self::DEFAULT_INSTANCE );

		return untrailingslashit( $url );
	}

	/**
	 * Persist defaults on first activation without clobbering existing values.
	 *
	 * @return void
	 */
	public static function seed_defaults() {
		if ( false === get_option( self::OPTION, false ) ) {
			add_option( self::OPTION, self::defaults() );
		}
	}

	/**
	 * Register the setting with the Settings API.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Sanitise the whole option array on save.
	 *
	 * Validation mirrors the Core sanitizers in
	 * `lib/tymeslot_web/live/dashboard/embed_settings/helpers.ex` so the
	 * plugin can never persist a value the booking page would reject.
	 *
	 * @param mixed $input Raw posted values.
	 * @return array<string,mixed>
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		$out      = array();

		// Instance URL: must be a valid http(s) URL, else fall back to cloud.
		$url               = isset( $input['instance_url'] ) ? esc_url_raw( trim( (string) $input['instance_url'] ) ) : '';
		$out['instance_url'] = '' !== $url ? untrailingslashit( $url ) : self::DEFAULT_INSTANCE;

		$out['username']      = self::sanitize_username( isset( $input['username'] ) ? $input['username'] : '' );
		$out['theme']         = self::sanitize_theme( isset( $input['theme'] ) ? $input['theme'] : '' );
		$out['primary_color'] = self::sanitize_color( isset( $input['primary_color'] ) ? $input['primary_color'] : '' );
		$out['locale']        = self::sanitize_locale( isset( $input['locale'] ) ? $input['locale'] : '' );
		$out['layout']        = self::sanitize_layout( isset( $input['layout'] ) ? $input['layout'] : 'column' );

		$out['initial_height'] = self::sanitize_int_in_range( isset( $input['initial_height'] ) ? $input['initial_height'] : '', 200, 2000, $defaults['initial_height'] );
		$out['max_width']      = self::sanitize_int_in_range( isset( $input['max_width'] ) ? $input['max_width'] : '', 200, 2000, $defaults['max_width'] );

		return $out;
	}

	/**
	 * Sanitise a Tymeslot username. Conservative slug: lowercase letters,
	 * digits, hyphen and underscore, matching Core's public handle format.
	 *
	 * @param mixed $value Raw username.
	 * @return string Empty string if invalid.
	 */
	public static function sanitize_username( $value ) {
		$value = strtolower( trim( (string) $value ) );
		$value = ltrim( $value, '@' );

		return preg_match( '/^[a-z0-9][a-z0-9_-]{0,59}$/', $value ) ? $value : '';
	}

	/**
	 * Theme id — digits only (matches Core).
	 *
	 * @param mixed $value Raw theme.
	 * @return string Empty string if invalid.
	 */
	public static function sanitize_theme( $value ) {
		$value = trim( (string) $value );

		return preg_match( '/^\d+$/', $value ) ? $value : '';
	}

	/**
	 * Hex colour `#RGB` or `#RRGGBB` (matches Core).
	 *
	 * @param mixed $value Raw colour.
	 * @return string Empty string if invalid.
	 */
	public static function sanitize_color( $value ) {
		$value = trim( (string) $value );

		return preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value ) ? $value : '';
	}

	/**
	 * Locale code (matches Core's permissive IETF check).
	 *
	 * @param mixed $value Raw locale.
	 * @return string Empty string if invalid.
	 */
	public static function sanitize_locale( $value ) {
		$value = trim( (string) $value );

		return preg_match( '/^[a-z]{2}(-[a-zA-Z0-9]+)?$/', $value ) ? $value : '';
	}

	/**
	 * Layout value — strict allowlist (matches Core).
	 *
	 * @param mixed $value Raw layout.
	 * @return string `column` or `default`.
	 */
	public static function sanitize_layout( $value ) {
		$value = trim( (string) $value );

		return array_key_exists( $value, self::layouts() ) ? $value : 'column';
	}

	/**
	 * Mode value — strict allowlist.
	 *
	 * @param mixed $value Raw mode.
	 * @return string A valid mode, defaulting to `inline`.
	 */
	public static function sanitize_mode( $value ) {
		$value = trim( (string) $value );

		return array_key_exists( $value, self::modes() ) ? $value : 'inline';
	}

	/**
	 * Integer clamped to an inclusive range (matches Core: out-of-range or
	 * non-numeric is treated as unset rather than clamped).
	 *
	 * @param mixed    $value   Raw value.
	 * @param int      $min     Minimum.
	 * @param int      $max     Maximum.
	 * @param int|null $default Returned when value is unset/invalid.
	 * @return int|null
	 */
	public static function sanitize_int_in_range( $value, $min, $max, $default = null ) {
		if ( '' === $value || null === $value ) {
			return $default;
		}

		if ( ! preg_match( '/^-?\d+$/', (string) $value ) ) {
			return $default;
		}

		$n = (int) $value;

		return ( $n >= $min && $n <= $max ) ? $n : $default;
	}
}

<?php
/**
 * Admin: menu, settings page, fields, assets, and dashboard notices.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds the branded Tymeslot admin experience.
 */
class Tymeslot_Admin {

	const SLUG = 'tymeslot';

	/**
	 * Hook the admin surfaces.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_setup_notice' ) );
		add_filter( 'plugin_action_links_' . TYMESLOT_BASENAME, array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Register the top-level menu with the brand mark.
	 *
	 * @return void
	 */
	public static function add_menu() {
		add_menu_page(
			__( 'Tymeslot', 'tymeslot' ),
			__( 'Tymeslot', 'tymeslot' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render_page' ),
			self::menu_icon(),
			58
		);
	}

	/**
	 * Add a Settings shortcut on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ) . '">'
			. esc_html__( 'Settings', 'tymeslot' ) . '</a>';
		array_unshift( $links, $settings );

		return $links;
	}

	/**
	 * Enqueue admin styles/scripts only on our page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'tymeslot-admin',
			TYMESLOT_URL . 'admin/css/admin.css',
			array(),
			TYMESLOT_VERSION
		);

		wp_enqueue_script(
			'tymeslot-admin',
			TYMESLOT_URL . 'admin/js/admin.js',
			array( 'wp-api-fetch' ),
			TYMESLOT_VERSION,
			true
		);

		wp_localize_script(
			'tymeslot-admin',
			'TymeslotAdmin',
			array(
				'restCheck'   => esc_url_raw( rest_url( Tymeslot_Rest::NAMESPACE_V1 . Tymeslot_Rest::CHECK_ROUTE ) ),
				'restSnippet' => esc_url_raw( rest_url( Tymeslot_Rest::NAMESPACE_V1 . Tymeslot_Rest::SNIPPET_ROUTE ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'instanceUrl' => Tymeslot_Settings::instance_url(),
				'embedDocs'   => Tymeslot_Settings::instance_url() . '/docs/embed',
				'i18n'        => array(
					'checking'   => __( 'Checking…', 'tymeslot' ),
					'copied'     => __( 'Copied!', 'tymeslot' ),
					'copy'       => __( 'Copy code', 'tymeslot' ),
					'reqError'   => __( 'The check could not be completed. Please try again.', 'tymeslot' ),
					'noUsername' => __( 'Set a username first.', 'tymeslot' ),
				),
			)
		);
	}

	/**
	 * Render the tabbed settings page.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs = array(
			'setup'     => __( 'Setup', 'tymeslot' ),
			'generator' => __( 'Embed generator', 'tymeslot' ),
			'help'      => __( 'Help', 'tymeslot' ),
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.
		$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'setup';
		if ( ! array_key_exists( $active, $tabs ) ) {
			$active = 'setup';
		}

		$settings = Tymeslot_Settings::all();

		require TYMESLOT_PATH . 'admin/views/page.php';
	}

	/**
	 * Show a one-time-ish nudge to finish setup when no username is saved.
	 *
	 * @return void
	 */
	public static function maybe_setup_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen && 'toplevel_page_' . self::SLUG === $screen->id ) {
			return; // Don't nag on our own page.
		}

		if ( '' !== Tymeslot_Settings::get( 'username', '' ) ) {
			return;
		}

		$url = admin_url( 'admin.php?page=' . self::SLUG );
		echo '<div class="notice notice-info is-dismissible"><p>'
			. wp_kses_post(
				sprintf(
					/* translators: %s: settings page URL. */
					__( '<strong>Tymeslot</strong> is almost ready — <a href="%s">add your booking username</a> to start embedding your scheduling page.', 'tymeslot' ),
					esc_url( $url )
				)
			)
			. '</p></div>';
	}

	/**
	 * Allowed-tag map for sanitising inline brand SVGs with wp_kses().
	 *
	 * @return array<string,array<string,bool>>
	 */
	public static function svg_kses() {
		$attrs = array(
			'xmlns'         => true,
			'viewbox'       => true,
			'width'         => true,
			'height'        => true,
			'fill'          => true,
			'd'             => true,
			'id'            => true,
			'x1'            => true,
			'y1'            => true,
			'x2'            => true,
			'y2'            => true,
			'offset'        => true,
			'stop-color'    => true,
			'stop-opacity'  => true,
			'style'         => true,
			'class'         => true,
			'cx'            => true,
			'cy'            => true,
			'r'             => true,
			'opacity'       => true,
			'gradientunits' => true,
		);

		return array(
			'svg'            => $attrs,
			'defs'           => $attrs,
			'lineargradient' => $attrs,
			'radialgradient' => $attrs,
			'stop'           => $attrs,
			'path'           => $attrs,
			'circle'         => $attrs,
			'rect'           => $attrs,
			'g'              => $attrs,
			'text'           => $attrs,
		);
	}

	/**
	 * Inline-SVG data URI for the menu icon (brand mark, monochrome so it
	 * tints with the admin colour scheme).
	 *
	 * @return string
	 */
	private static function menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">'
			. '<path d="M 60 60 L 125 60 Q 138 60 138 73 L 138 77 Q 138 90 125 90 L 85 90 Q 75 90 75 80 L 75 70 Q 75 60 85 60 Z"/>'
			. '<path d="M 80 95 Q 68 95 68 107 L 68 113 Q 68 125 80 125 L 115 125 Q 128 125 128 112 L 128 108 Q 128 95 115 95 Z"/>'
			. '<path d="M 115 130 Q 128 130 128 143 L 128 147 Q 128 160 115 160 L 65 160 Q 52 160 52 147 L 52 143 Q 52 130 65 130 Z"/>'
			. '</svg>';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Benign: encoding a static inline SVG as a data URI for the admin menu icon.
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}
}

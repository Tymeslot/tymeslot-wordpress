<?php
/**
 * REST controller — the plugin's admin-only HTTP surface.
 *
 * Owns route registration and request/response handling. The actual work
 * is delegated: embeddability diagnostics to Tymeslot_Connection, snippet
 * generation to Tymeslot_Embed. This keeps the HTTP layer thin and the
 * logic layers free of WP_REST_Request coupling (and easy to unit test).
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and handles the plugin's REST routes.
 */
class Tymeslot_Rest {

	const NAMESPACE_V1  = 'tymeslot/v1';
	const CHECK_ROUTE   = '/check';
	const SNIPPET_ROUTE = '/snippet';

	/**
	 * Hook route registration.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Capability gate shared by every route — admin only.
	 *
	 * @return bool
	 */
	public static function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Register the routes.
	 *
	 * @return void
	 */
	public static function register_routes() {
		$string_arg = array( 'type' => 'string' );

		register_rest_route(
			self::NAMESPACE_V1,
			self::CHECK_ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_check' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'username'     => $string_arg,
					'instance_url' => $string_arg,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			self::SNIPPET_ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_snippet' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'mode'           => $string_arg,
					'username'       => $string_arg,
					'theme'          => $string_arg,
					'primary_color'  => $string_arg,
					'locale'         => $string_arg,
					'layout'         => $string_arg,
					'initial_height' => $string_arg,
					'max_width'      => $string_arg,
					'label'          => $string_arg,
				),
			)
		);
	}

	/**
	 * Run the embeddability diagnostic for the posted (or saved) values.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function handle_check( $request ) {
		$instance = self::resolve_instance( $request->get_param( 'instance_url' ) );

		$username = $request->get_param( 'username' );
		$username = ( null !== $username && '' !== $username ) ? $username : Tymeslot_Settings::get( 'username', '' );
		$username = Tymeslot_Settings::sanitize_username( $username );

		return new WP_REST_Response( Tymeslot_Connection::check( $instance, $username ), 200 );
	}

	/**
	 * Regenerate a snippet for the admin live generator.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function handle_snippet( $request ) {
		$mode = (string) $request->get_param( 'mode' );

		$snippet = Tymeslot_Embed::preview(
			$mode,
			array(
				'username'       => $request->get_param( 'username' ),
				'theme'          => $request->get_param( 'theme' ),
				'primary_color'  => $request->get_param( 'primary_color' ),
				'locale'         => $request->get_param( 'locale' ),
				'layout'         => $request->get_param( 'layout' ),
				'initial_height' => $request->get_param( 'initial_height' ),
				'max_width'      => $request->get_param( 'max_width' ),
				'label'          => $request->get_param( 'label' ),
			)
		);

		return new WP_REST_Response(
			array(
				'mode'    => Tymeslot_Settings::sanitize_mode( $mode ),
				'snippet' => $snippet,
			),
			200
		);
	}

	/**
	 * Resolve and validate a posted instance URL, falling back to the saved
	 * instance. Only http(s) schemes are accepted.
	 *
	 * Note: private/localhost hosts are intentionally NOT blocked. Self-
	 * hosting Tymeslot on an internal network is a first-class use case, and
	 * this endpoint is already restricted to administrators (who can make
	 * arbitrary requests by other means), so an SSRF block here would cost
	 * legitimate users more than it protects.
	 *
	 * @param string|null $raw Posted instance_url.
	 * @return string Sanitised base URL with no trailing slash.
	 */
	private static function resolve_instance( $raw ) {
		$raw = is_string( $raw ) ? trim( $raw ) : '';

		if ( '' === $raw ) {
			return Tymeslot_Settings::instance_url();
		}

		$url = esc_url_raw( $raw, array( 'http', 'https' ) );

		return '' !== $url ? untrailingslashit( $url ) : Tymeslot_Settings::instance_url();
	}
}

<?php
/**
 * REST controller — the plugin's admin-only HTTP surface.
 *
 * Owns route registration and request/response handling, delegating snippet
 * generation to Tymeslot_Embed. Keeping the HTTP layer thin leaves the logic
 * layers free of WP_REST_Request coupling.
 *
 * (Embeddability is detected entirely in the browser — see the live probe in
 * admin/js/admin.js — so there is deliberately no server-side check route.)
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and handles the plugin's REST routes.
 */
class Tymeslot_Rest {

	const NAMESPACE_V1  = 'tymeslot/v1';
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
			self::SNIPPET_ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_snippet' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'mode'           => $string_arg,
					'username'       => $string_arg,
					'theme'          => $string_arg,
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
}

<?php
/**
 * Connection & embeddability check.
 *
 * Probes the configured instance for the configured username and inspects
 * the framing headers (CSP `frame-ancestors` / `X-Frame-Options`) to tell
 * the user whether THIS WordPress site is allowed to embed the booking
 * page. This is the plugin's main support-deflection feature: Tymeslot
 * blocks embedding by default until the site's domain is added to the
 * account's allowed-embed-domains list.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pure embeddability diagnostic. Given an instance URL and username, probes
 * the booking page and reports whether this site may embed it. No HTTP/REST
 * coupling — the REST layer (Tymeslot_Rest) calls check() and serialises the
 * result, which keeps this logic straightforward to test in isolation.
 */
class Tymeslot_Connection {

	/**
	 * Run the diagnostic.
	 *
	 * @param string $instance Instance base URL (no trailing slash).
	 * @param string $username Booking username.
	 * @return array<string,mixed> Structured result for the UI.
	 */
	public static function check( $instance, $username ) {
		$site_host = self::site_host();

		$result = array(
			'site_host'  => $site_host,
			'instance'   => $instance,
			'username'   => $username,
			'reachable'  => false,
			'account_ok' => false,
			'embeddable' => false,
			'status'     => 'error',
			'message'    => '',
		);

		if ( '' === $username ) {
			$result['status']  = 'no_username';
			$result['message'] = __( 'Add your Tymeslot username to run the check.', 'tymeslot' );
			return $result;
		}

		$url = $instance . '/' . rawurlencode( $username ) . '?embed=1&parent-origin=' . rawurlencode( home_url() );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 12,
				'redirection' => 3,
				'headers'     => array( 'Referer' => home_url() ),
			)
		);

		if ( is_wp_error( $response ) ) {
			$result['status'] = 'unreachable';
			/* translators: %s: error detail. */
			$result['message'] = sprintf( __( 'Could not reach your Tymeslot instance: %s', 'tymeslot' ), $response->get_error_message() );
			return $result;
		}

		$code                = (int) wp_remote_retrieve_response_code( $response );
		$result['reachable'] = true;

		if ( 404 === $code ) {
			$result['status'] = 'account_not_found';
			/* translators: %s: username. */
			$result['message'] = sprintf( __( 'The instance is reachable, but no booking page was found for “%s”. Check the username.', 'tymeslot' ), $username );
			return $result;
		}

		if ( $code < 200 || $code >= 400 ) {
			$result['status'] = 'http_error';
			/* translators: %d: HTTP status code. */
			$result['message'] = sprintf( __( 'The instance returned an unexpected response (HTTP %d).', 'tymeslot' ), $code );
			return $result;
		}

		$result['account_ok'] = true;

		$csp   = self::header_value( $response, 'content-security-policy' );
		$xfo   = self::header_value( $response, 'x-frame-options' );
		$frame = self::frame_ancestors( $csp );

		$result['frame_ancestors'] = $frame;
		$result['embeddable']      = self::host_allowed( $site_host, $frame, $xfo );

		if ( $result['embeddable'] ) {
			$result['status']  = 'ok';
			$result['message'] = __( 'All set — your booking page can be embedded on this site.', 'tymeslot' );
		} else {
			$result['status']  = 'not_allowlisted';
			$result['message'] = sprintf(
				/* translators: %s: this site's host name. */
				__( 'Your account does not yet allow embedding on “%s”. Add this domain in Tymeslot → Embed → Security, then re-check.', 'tymeslot' ),
				$site_host
			);
		}

		return $result;
	}

	/**
	 * This site's host (no port), lower-cased.
	 *
	 * @return string
	 */
	private static function site_host() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		return $host ? strtolower( $host ) : '';
	}

	/**
	 * Retrieve a response header value as a string.
	 *
	 * @param array|WP_Error $response Response.
	 * @param string         $name     Header name (lower-case).
	 * @return string
	 */
	private static function header_value( $response, $name ) {
		$value = wp_remote_retrieve_header( $response, $name );

		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		return strtolower( (string) $value );
	}

	/**
	 * Extract the `frame-ancestors` source list from a CSP header.
	 *
	 * @param string $csp Lower-cased CSP header value.
	 * @return array<int,string> Tokens, or [] when the directive is absent.
	 */
	private static function frame_ancestors( $csp ) {
		if ( '' === $csp ) {
			return array();
		}

		foreach ( explode( ';', $csp ) as $directive ) {
			$directive = trim( $directive );
			if ( 0 === strpos( $directive, 'frame-ancestors' ) ) {
				$tokens = preg_split( '/\s+/', $directive );
				array_shift( $tokens ); // Drop the directive name.

				return array_values( array_filter( array_map( 'trim', $tokens ) ) );
			}
		}

		return array();
	}

	/**
	 * Decide whether $host may frame the booking page given the headers.
	 *
	 * Mirrors Core's matching: bare and `www.` variants are interchangeable,
	 * and `*.example.com` matches any subdomain.
	 *
	 * @param string            $host  Site host.
	 * @param array<int,string> $frame frame-ancestors tokens.
	 * @param string            $xfo   X-Frame-Options value (lower-case).
	 * @return bool
	 */
	private static function host_allowed( $host, $frame, $xfo ) {
		if ( '' === $host ) {
			return false;
		}

		// When CSP frame-ancestors is present, browsers use it and ignore XFO.
		if ( ! empty( $frame ) ) {
			foreach ( $frame as $token ) {
				if ( "'none'" === $token || "'self'" === $token ) {
					continue;
				}

				$token_host = self::token_host( $token );
				if ( '' === $token_host ) {
					continue;
				}

				if ( self::hosts_match( $host, $token_host ) ) {
					return true;
				}
			}

			return false;
		}

		// No frame-ancestors directive: fall back to X-Frame-Options.
		if ( '' === $xfo ) {
			// No framing restriction advertised at all — assume embeddable.
			return true;
		}

		// DENY or SAMEORIGIN both block a third-party site.
		return false;
	}

	/**
	 * Reduce a frame-ancestors token to a bare host (strip scheme/port/path).
	 *
	 * @param string $token Source token.
	 * @return string
	 */
	private static function token_host( $token ) {
		$token = trim( $token, "'" );
		$token = preg_replace( '#^[a-z][a-z0-9+.-]*://#', '', $token );
		$token = preg_replace( '#[:/].*$#', '', $token );

		return strtolower( (string) $token );
	}

	/**
	 * Host equality with www-variant and wildcard handling.
	 *
	 * @param string $host    Site host.
	 * @param string $pattern frame-ancestors host pattern.
	 * @return bool
	 */
	private static function hosts_match( $host, $pattern ) {
		if ( 0 === strpos( $pattern, '*.' ) ) {
			$suffix = substr( $pattern, 1 ); // ".example.com"
			return (bool) preg_match( '/' . preg_quote( $suffix, '/' ) . '$/', $host ) && ltrim( $suffix, '.' ) !== $host;
		}

		$strip_www = function ( $h ) {
			return preg_replace( '/^www\./', '', $h );
		};

		return $strip_www( $host ) === $strip_www( $pattern );
	}
}

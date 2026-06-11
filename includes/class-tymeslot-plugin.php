<?php
/**
 * Plugin bootstrap / service container.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin singleton. Loads dependencies and wires up the public,
 * block, and admin surfaces.
 */
final class Tymeslot_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Tymeslot_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Boot the plugin (idempotent).
	 *
	 * @return Tymeslot_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->load_dependencies();
			self::$instance->init_hooks();
		}

		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {}

	/**
	 * Require the class files.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-settings.php';
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-snippet.php';
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-assets.php';
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-shortcode.php';
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-block.php';
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-connection.php';

		if ( is_admin() ) {
			require_once TYMESLOT_PATH . 'includes/class-tymeslot-admin.php';
		}
	}

	/**
	 * Register WordPress hooks for each surface.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( 'Tymeslot_Settings', 'register' ) );
		add_action( 'init', array( 'Tymeslot_Shortcode', 'register' ) );
		add_action( 'init', array( 'Tymeslot_Block', 'register' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );

		Tymeslot_Connection::register();

		if ( is_admin() ) {
			Tymeslot_Admin::register();
		}
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'tymeslot', false, dirname( TYMESLOT_BASENAME ) . '/languages' );
	}
}

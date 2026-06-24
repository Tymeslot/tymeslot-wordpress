<?php
/**
 * Plugin Name:       Tymeslot
 * Plugin URI:        https://tymeslot.app/docs/wordpress
 * Description:        Add your Tymeslot booking page to WordPress with a shortcode, a Gutenberg block, or a floating button — no code required. Works with Tymeslot Cloud and self-hosted instances.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Tymeslot
 * Author URI:        https://tymeslot.app
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tymeslot
 * Domain Path:       /languages
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

define( 'TYMESLOT_VERSION', '1.0.0' );
define( 'TYMESLOT_FILE', __FILE__ );
define( 'TYMESLOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'TYMESLOT_URL', plugin_dir_url( __FILE__ ) );
define( 'TYMESLOT_BASENAME', plugin_basename( __FILE__ ) );

require_once TYMESLOT_PATH . 'includes/class-tymeslot-plugin.php';

/**
 * Boot the plugin once all other plugins are loaded.
 */
add_action( 'plugins_loaded', array( 'Tymeslot_Plugin', 'instance' ) );

/**
 * Seed sensible defaults on activation so the first-run experience is clean.
 */
register_activation_hook(
	__FILE__,
	function () {
		require_once TYMESLOT_PATH . 'includes/class-tymeslot-settings.php';
		Tymeslot_Settings::seed_defaults();
	}
);

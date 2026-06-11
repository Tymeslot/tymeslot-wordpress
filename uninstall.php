<?php
/**
 * Uninstall cleanup. Runs when the user deletes the plugin from WordPress.
 *
 * @package Tymeslot
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'tymeslot_settings' );

// Multisite: clean each site's option too.
if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'tymeslot_settings' );
		restore_current_blog();
	}
}

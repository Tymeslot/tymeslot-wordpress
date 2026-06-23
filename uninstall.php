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
	$tymeslot_site_ids = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $tymeslot_site_ids as $tymeslot_site_id ) {
		switch_to_blog( $tymeslot_site_id );
		delete_option( 'tymeslot_settings' );
		restore_current_blog();
	}
}

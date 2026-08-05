<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schedules daily activity cleanup if it is not already scheduled.
 */
function aal_maybe_schedule_cleanup() {
	if ( wp_next_scheduled( 'aal_cleanup_old_activity' ) ) {
		return;
	}

	wp_schedule_event(
		time() + HOUR_IN_SECONDS,
		'daily',
		'aal_cleanup_old_activity'
	);
}
add_action( 'plugins_loaded', 'aal_maybe_schedule_cleanup', 20, 0 );

/**
 * Deletes activity rows older than the configured retention period.
 */
function aal_cleanup_old_activity() {
	global $wpdb;

	$retention_days = (int) get_option( 'aal_retention_days', 30 );

	if ( $retention_days < 1 ) {
		$retention_days = 30;
	}

	$cutoff = current_datetime()
		->modify( sprintf( '-%d days', $retention_days ) )
		->format( 'Y-m-d H:i:s' );

	$table_name = $wpdb->prefix . 'aal_activity';

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $table_name
			WHERE requested_at < %s",
			$cutoff
		)
	);
}
add_action( 'aal_cleanup_old_activity', 'aal_cleanup_old_activity', 10, 0 );

/**
 * Clears scheduled plugin events when the plugin is deactivated.
 */
function aal_deactivate() {
	wp_clear_scheduled_hook( 'aal_cleanup_old_activity' );
}

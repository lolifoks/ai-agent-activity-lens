<?php
/**
 * Cleanup on plugin uninstall: drops tables, options, user meta, and transients.
 *
 * @package AI_Agent_Activity_Lens
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

wp_clear_scheduled_hook( 'aal_cleanup_old_activity' );

delete_option( 'aal_db_version' );
delete_option( 'aal_requests_per_minute' );
delete_option( 'aal_retention_days' );

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-time uninstall cleanup of plugin-owned user meta.
$wpdb->delete(
	$wpdb->usermeta,
	array(
		'meta_key' => 'aal_agent_credentials',
	),
	array( '%s' )
);
// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

$table_name = $wpdb->prefix . 'aal_activity';

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is derived from the trusted WordPress database prefix.
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

$transient_prefix         = '_transient_aal_ratelimit_';
$transient_timeout_prefix = '_transient_timeout_aal_ratelimit_';

$like_transient = $wpdb->esc_like( $transient_prefix ) . '%';
$like_timeout   = $wpdb->esc_like( $transient_timeout_prefix ) . '%';

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s
		OR option_name LIKE %s",
		$like_transient,
		$like_timeout
	)
);
// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
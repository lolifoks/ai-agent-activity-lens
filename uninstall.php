<?php
/**
 * Uninstall AI Agent Activity Lens.
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

$wpdb->delete(
	$wpdb->usermeta,
	array(
		'meta_key' => 'aal_agent_credentials',
	),
	array( '%s' )
);

$table_name = $wpdb->prefix . 'aal_activity';

$wpdb->query(
	"DROP TABLE IF EXISTS $table_name"
);

$transient_prefix         = '_transient_aal_ratelimit_';
$transient_timeout_prefix = '_transient_timeout_aal_ratelimit_';

$like_transient = $wpdb->esc_like( $transient_prefix ) . '%';
$like_timeout   = $wpdb->esc_like( $transient_timeout_prefix ) . '%';

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s
		OR option_name LIKE %s",
		$like_transient,
		$like_timeout
	)
);
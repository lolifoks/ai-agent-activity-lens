<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates or upgrades the custom activity table.
 */
function aal_activate() {
	global $wpdb;

	$table_name      = $wpdb->prefix . 'aal_activity';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NULL DEFAULT NULL,
		credential_uuid VARCHAR(36) NULL DEFAULT NULL,
		method VARCHAR(10) NOT NULL DEFAULT '',
		route VARCHAR(255) NOT NULL DEFAULT '',
		status_code SMALLINT UNSIGNED NOT NULL DEFAULT 0,
		duration_ms INT UNSIGNED NOT NULL DEFAULT 0,
		ip_address VARCHAR(45) NOT NULL DEFAULT '',
		requested_at DATETIME NOT NULL,
		PRIMARY KEY (id),
		KEY requested_at (requested_at),
		KEY user_id (user_id),
		KEY credential_uuid (credential_uuid),
		KEY route (route)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'aal_db_version', AAL_VERSION );
}

/**
 * Ensures the installed database schema matches the plugin version.
 */
function aal_maybe_upgrade_schema() {
	$installed_version = get_option( 'aal_db_version', '0.0.0' );

	if ( version_compare( $installed_version, AAL_VERSION, '>=' ) ) {
		return;
	}

	aal_activate();
}
add_action( 'plugins_loaded', 'aal_maybe_upgrade_schema', 10, 0 );

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Records the REST request start time.
 *
 * @param mixed           $result  Response used to short-circuit the request.
 * @param WP_REST_Server  $server  REST server instance.
 * @param WP_REST_Request $request REST request instance.
 * @return mixed Original result.
 */
function aal_capture_start( $result, $server, $request ) {
	global $aal_request_start_time;

	$aal_request_start_time = microtime( true );

	return $result;
}
add_filter( 'rest_pre_dispatch', 'aal_capture_start', 10, 3 );

/**
 * Logs an Application Password authenticated REST request.
 *
 * @param WP_HTTP_Response $response Result to send to the client.
 * @param WP_REST_Server   $server   REST server instance.
 * @param WP_REST_Request  $request  REST request instance.
 * @return WP_HTTP_Response Original response.
 */
function aal_log_request( $response, $server, $request ) {
	global $wpdb;
	global $aal_authenticated_credential_uuid;
	global $aal_request_start_time;

	if ( empty( $aal_authenticated_credential_uuid ) ) {
		return $response;
	}

	$start_time = isset( $aal_request_start_time )
		? (float) $aal_request_start_time
		: microtime( true );

	$duration_ms = (int) round(
		( microtime( true ) - $start_time ) * 1000
	);

	$status = 200;

	if ( is_object( $response ) && method_exists( $response, 'get_status' ) ) {
		$status = (int) $response->get_status();
	}

	$wpdb->insert(
		$wpdb->prefix . 'aal_activity',
		array(
			'user_id'         => get_current_user_id() ?: null,
			'credential_uuid' => $aal_authenticated_credential_uuid,
			'method'          => $request->get_method(),
			'route'           => $request->get_route(),
			'status_code'     => $status,
			'duration_ms'     => $duration_ms,
			'ip_address'      => aal_get_ip_address(),
			'requested_at'    => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
	);

	return $response;
}
add_filter( 'rest_post_dispatch', 'aal_log_request', 10, 3 );

/**
 * Returns the visitor IP address.
 *
 * @return string Visitor IP address or an empty string.
 */
function aal_get_ip_address() {
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return '';
}

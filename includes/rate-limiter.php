<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks whether a user's Application Password is tagged as an AI agent.
 *
 * @param int    $user_id         WordPress user ID.
 * @param string $credential_uuid Application Password UUID.
 * @return bool Whether the credential is tagged.
 */
function aal_is_credential_tagged( $user_id, $credential_uuid ) {
	if ( $user_id < 1 || empty( $credential_uuid ) ) {
		return false;
	}

	$tagged_credentials = get_user_meta(
		$user_id,
		'aal_agent_credentials',
		true
	);

	if ( ! is_array( $tagged_credentials ) ) {
		return false;
	}

	return in_array( $credential_uuid, $tagged_credentials, true );
}

/**
 * Applies a per-minute rate limit to tagged AI-agent credentials.
 *
 * @param mixed           $result  Response used to short-circuit the request.
 * @param WP_REST_Server  $server  REST server instance.
 * @param WP_REST_Request $request REST request instance.
 * @return mixed Original result or a rate-limit error.
 */
function aal_enforce_rate_limit( $result, $server, $request ) {
	global $aal_authenticated_credential_uuid;
	global $aal_authenticated_user_id;

	if ( null !== $result ) {
		return $result;
	}

	if ( empty( $aal_authenticated_credential_uuid ) ) {
		return $result;
	}

	if (
		empty( $aal_authenticated_user_id )
		|| ! aal_is_credential_tagged(
			(int) $aal_authenticated_user_id,
			$aal_authenticated_credential_uuid
		)
	) {
		return $result;
	}

	$limit = (int) get_option( 'aal_requests_per_minute', 60 );

	if ( $limit < 1 ) {
		$limit = 60;
	}

	$transient_key = 'aal_ratelimit_' . $aal_authenticated_credential_uuid;
	$rate_data     = get_transient( $transient_key );
	$current_time  = time();

	if (
		false === $rate_data
		|| ! is_array( $rate_data )
		|| empty( $rate_data['window_started'] )
		|| (int) $rate_data['window_started'] + MINUTE_IN_SECONDS <= $current_time
	) {
		$rate_data = array(
			'count'          => 0,
			'window_started' => $current_time,
		);
	}

	$rate_data['count'] = (int) $rate_data['count'] + 1;

	$window_ends_in = max(
		1,
		(int) $rate_data['window_started'] + MINUTE_IN_SECONDS - $current_time
	);

	set_transient( $transient_key, $rate_data, $window_ends_in );

	if ( $rate_data['count'] > $limit ) {
		return new WP_Error(
			'aal_rate_limit_exceeded',
			__(
				'This AI-agent credential has exceeded its request limit.',
				'ai-agent-activity-lens'
			),
			array(
				'status'      => 429,
				'retry_after' => $window_ends_in,
				'limit'       => $limit,
			)
		);
	}

	return $result;
}
add_filter( 'rest_pre_dispatch', 'aal_enforce_rate_limit', 20, 3 );

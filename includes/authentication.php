<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Captures the Application Password used for authentication.
 *
 * These values exist only for the duration of the current PHP request.
 *
 * @param WP_User $user Authenticated WordPress user.
 * @param array   $item Application Password record.
 */
function aal_capture_application_password( $user, $item ) {
	global $aal_authenticated_credential_uuid;
	global $aal_authenticated_user_id;

	if ( empty( $item['uuid'] ) ) {
		return;
	}

	$aal_authenticated_credential_uuid = sanitize_text_field( $item['uuid'] );
	$aal_authenticated_user_id         = (int) $user->ID;
}
add_action(
	'application_password_did_authenticate',
	'aal_capture_application_password',
	10,
	2
);

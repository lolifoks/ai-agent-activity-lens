<?php
/**
 * Privacy functionality.
 *
 * @package AI_Agent_Activity_Lens
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds suggested privacy-policy content for site administrators.
 */
function aal_add_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = '<p class="privacy-policy-tutorial">'
		. esc_html__(
			'AI Agent Activity Lens records activity performed through WordPress Application Passwords.',
			'ai-agent-activity-lens'
		)
		. '</p>';

	$content .= '<p>'
		. esc_html__(
			'When an external tool accesses the WordPress REST API using an Application Password, we may store the WordPress user ID, Application Password UUID, request method, REST route, response status, request duration, source IP address, and timestamp.',
			'ai-agent-activity-lens'
		)
		. '</p>';

	$content .= '<p>'
		. esc_html__(
			'This information is stored in the local WordPress database for security monitoring and troubleshooting. It is not transmitted to an external service by this plugin.',
			'ai-agent-activity-lens'
		)
		. '</p>';

	$content .= '<p>'
		. esc_html__(
			'Activity records are retained for the period configured by a site administrator and are then deleted automatically.',
			'ai-agent-activity-lens'
		)
		. '</p>';

	wp_add_privacy_policy_content(
		__( 'AI Agent Activity Lens', 'ai-agent-activity-lens' ),
		wp_kses_post( $content )
	);
}
add_action( 'admin_init', 'aal_add_privacy_policy_content', 10, 0 );

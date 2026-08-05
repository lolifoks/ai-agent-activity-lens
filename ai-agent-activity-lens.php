<?php
/**
 * Plugin Name: AI Agent Activity Lens
 * Description: Observability and guardrails for AI agents acting on WordPress through Application Passwords and the MCP Adapter. Logs REST requests, shows an admin dashboard, and rate-limits tagged credentials.
 * Version: 0.2.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Marija Lekić
 * License: GPL-2.0-or-later
 * Text Domain: ai-agent-activity-lens
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AAL_VERSION', '0.2.0' );
define( 'AAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once AAL_PLUGIN_DIR . 'includes/database.php';
require_once AAL_PLUGIN_DIR . 'includes/authentication.php';
require_once AAL_PLUGIN_DIR . 'includes/activity-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/rate-limiter.php';
require_once AAL_PLUGIN_DIR . 'includes/admin-dashboard.php';
require_once AAL_PLUGIN_DIR . 'includes/settings.php';
require_once AAL_PLUGIN_DIR . 'includes/user-profile.php';
require_once AAL_PLUGIN_DIR . 'includes/retention.php';
require_once AAL_PLUGIN_DIR . 'includes/privacy.php';

register_activation_hook( __FILE__, 'aal_activate' );
register_deactivation_hook( __FILE__, 'aal_deactivate' );

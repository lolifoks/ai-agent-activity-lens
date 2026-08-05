<?php
/**
 * Plugin settings and settings-page rendering.
 *
 * @package AI_Agent_Activity_Lens
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Registers plugin settings and fields.
 */
function aal_register_settings() {
	register_setting(
		'aal_settings',
		'aal_requests_per_minute',
		array(
			'type'              => 'integer',
			'default'           => 60,
			'sanitize_callback' => 'aal_sanitize_rate_limit',
		)
	);

	register_setting(
		'aal_settings',
		'aal_retention_days',
		array(
			'type'              => 'integer',
			'default'           => 30,
			'sanitize_callback' => 'aal_sanitize_retention_days',
		)
	);

	add_settings_section(
		'aal_rate_limit_section',
		__( 'Rate limiting', 'ai-agent-activity-lens' ),
		'aal_render_rate_limit_section',
		'ai-agent-activity-lens-settings'
	);

	add_settings_field(
		'aal_requests_per_minute',
		__( 'Requests per minute', 'ai-agent-activity-lens' ),
		'aal_render_rate_limit_field',
		'ai-agent-activity-lens-settings',
		'aal_rate_limit_section'
	);

	add_settings_section(
		'aal_retention_section',
		__( 'Activity retention', 'ai-agent-activity-lens' ),
		'aal_render_retention_section',
		'ai-agent-activity-lens-settings'
	);

	add_settings_field(
		'aal_retention_days',
		__( 'Keep activity for', 'ai-agent-activity-lens' ),
		'aal_render_retention_field',
		'ai-agent-activity-lens-settings',
		'aal_retention_section'
	);
}
add_action( 'admin_init', 'aal_register_settings', 10, 0 );

/**
 * Sanitizes the requests-per-minute setting.
 *
 * @param mixed $value Submitted setting value.
 * @return int Valid requests-per-minute limit.
 */
function aal_sanitize_rate_limit( $value ) {
	$value = absint( $value );

	if ( $value < 1 ) {
		return 60;
	}

	return min( $value, 10000 );
}

/**
 * Sanitizes the activity-retention setting.
 *
 * @param mixed $value Submitted retention value.
 * @return int Valid retention period in days.
 */
function aal_sanitize_retention_days( $value ) {
	$value = absint( $value );

	if ( $value < 1 ) {
		return 30;
	}

	return min( $value, 3650 );
}

/**
 * Renders the rate-limit section description.
 */
function aal_render_rate_limit_section() {
	echo '<p>';
	echo esc_html__(
		'The limit applies separately to each tagged AI-agent credential.',
		'ai-agent-activity-lens'
	);
	echo '</p>';
}

/**
 * Renders the rate-limit field.
 */
function aal_render_rate_limit_field() {
	$value = (int) get_option( 'aal_requests_per_minute', 60 );
	?>
	<input
		type="number"
		name="aal_requests_per_minute"
		id="aal_requests_per_minute"
		value="<?php echo esc_attr( $value ); ?>"
		min="1"
		max="10000"
		step="1"
		class="small-text"
	/>
	<p class="description">
		<?php echo esc_html__( 'Requests above this limit receive an HTTP 429 response.', 'ai-agent-activity-lens' ); ?>
	</p>
	<?php
}

/**
 * Renders the retention section description.
 */
function aal_render_retention_section() {
	echo '<p>';
	echo esc_html__(
		'Activity rows older than the selected period are deleted automatically once per day.',
		'ai-agent-activity-lens'
	);
	echo '</p>';
}

/**
 * Renders the retention field.
 */
function aal_render_retention_field() {
	$value = (int) get_option( 'aal_retention_days', 30 );
	?>
	<input
		type="number"
		name="aal_retention_days"
		id="aal_retention_days"
		value="<?php echo esc_attr( $value ); ?>"
		min="1"
		max="3650"
		step="1"
		class="small-text"
	/>
	<span><?php echo esc_html__( 'days', 'ai-agent-activity-lens' ); ?></span>
	<p class="description">
		<?php echo esc_html__( 'The default is 30 days. The maximum is 3650 days.', 'ai-agent-activity-lens' ); ?>
	</p>
	<?php
}

/**
 * Registers the plugin settings page.
 */
function aal_register_settings_page() {
	add_options_page(
		__( 'AI Agent Activity Lens Settings', 'ai-agent-activity-lens' ),
		__( 'AI Activity Lens', 'ai-agent-activity-lens' ),
		'manage_options',
		'ai-agent-activity-lens-settings',
		'aal_render_settings_page'
	);
}
add_action( 'admin_menu', 'aal_register_settings_page', 10, 0 );

/**
 * Renders the plugin settings page.
 */
function aal_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__(
				'You do not have permission to access this page.',
				'ai-agent-activity-lens'
			)
		);
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'AI Agent Activity Lens Settings', 'ai-agent-activity-lens' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'aal_settings' );
			do_settings_sections( 'ai-agent-activity-lens-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

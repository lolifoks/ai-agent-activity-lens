<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders AI-agent credential settings on a user profile.
 *
 * @param WP_User $profile_user User whose profile is being edited.
 */
function aal_render_agent_credentials_field( $profile_user ) {
	if ( ! current_user_can( 'edit_user', $profile_user->ID ) ) {
		return;
	}

	if ( ! wp_is_application_passwords_available_for_user( $profile_user ) ) {
		return;
	}

	$application_passwords = WP_Application_Passwords::get_user_application_passwords(
		$profile_user->ID
	);

	$tagged_credentials = get_user_meta(
		$profile_user->ID,
		'aal_agent_credentials',
		true
	);

	if ( ! is_array( $tagged_credentials ) ) {
		$tagged_credentials = array();
	}
	?>
	<h2><?php echo esc_html__( 'AI Agent Activity Lens', 'ai-agent-activity-lens' ); ?></h2>

	<?php wp_nonce_field( 'aal_save_agent_credentials', 'aal_agent_credentials_nonce' ); ?>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php echo esc_html__( 'AI-agent credentials', 'ai-agent-activity-lens' ); ?></th>
			<td>
				<?php if ( empty( $application_passwords ) ) : ?>
					<p><?php echo esc_html__( 'This user does not have any Application Passwords.', 'ai-agent-activity-lens' ); ?></p>
				<?php else : ?>
					<fieldset>
						<legend class="screen-reader-text">
							<?php echo esc_html__( 'Select Application Passwords used by AI agents', 'ai-agent-activity-lens' ); ?>
						</legend>

						<?php foreach ( $application_passwords as $application_password ) : ?>
							<?php
							if ( empty( $application_password['uuid'] ) || empty( $application_password['name'] ) ) {
								continue;
							}

							$credential_uuid = $application_password['uuid'];
							$field_id        = 'aal_agent_credential_' . sanitize_html_class( $credential_uuid );
							?>
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<input
									type="checkbox"
									name="aal_agent_credentials[]"
									id="<?php echo esc_attr( $field_id ); ?>"
									value="<?php echo esc_attr( $credential_uuid ); ?>"
									<?php checked( in_array( $credential_uuid, $tagged_credentials, true ) ); ?>
								/>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: Application Password name. */
										__( '%s is used by an AI agent', 'ai-agent-activity-lens' ),
										$application_password['name']
									)
								);
								?>
							</label>
							<br />
						<?php endforeach; ?>
					</fieldset>

					<p class="description">
						<?php echo esc_html__( 'Tagged credentials can be filtered and rate-limited by AI Agent Activity Lens.', 'ai-agent-activity-lens' ); ?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'aal_render_agent_credentials_field', 10, 1 );
add_action( 'edit_user_profile', 'aal_render_agent_credentials_field', 10, 1 );

/**
 * Saves AI-agent credential selections from a user profile.
 *
 * @param int $user_id User whose profile is being updated.
 */
function aal_save_agent_credentials_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	if (
		! isset( $_POST['aal_agent_credentials_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['aal_agent_credentials_nonce'] ) ),
			'aal_save_agent_credentials'
		)
	) {
		return;
	}

	$selected_credentials = array();

	if ( isset( $_POST['aal_agent_credentials'] ) && is_array( $_POST['aal_agent_credentials'] ) ) {
		$selected_credentials = array_map(
			'sanitize_text_field',
			wp_unslash( $_POST['aal_agent_credentials'] )
		);
	}

	$application_passwords  = WP_Application_Passwords::get_user_application_passwords( $user_id );
	$valid_credential_uuids = array();

	foreach ( $application_passwords as $application_password ) {
		if ( ! empty( $application_password['uuid'] ) ) {
			$valid_credential_uuids[] = $application_password['uuid'];
		}
	}

	$selected_credentials = array_values(
		array_intersect( $selected_credentials, $valid_credential_uuids )
	);

	if ( empty( $selected_credentials ) ) {
		delete_user_meta( $user_id, 'aal_agent_credentials' );
		return;
	}

	update_user_meta( $user_id, 'aal_agent_credentials', $selected_credentials );
}
add_action( 'personal_options_update', 'aal_save_agent_credentials_field', 10, 1 );
add_action( 'edit_user_profile_update', 'aal_save_agent_credentials_field', 10, 1 );

/**
 * Removes a deleted Application Password from the tagged credential list.
 *
 * @param int   $user_id User whose Application Password was deleted.
 * @param array $item    Deleted Application Password record.
 */
function aal_remove_deleted_agent_credential( $user_id, $item ) {
	if ( empty( $item['uuid'] ) ) {
		return;
	}

	$tagged_credentials = get_user_meta( $user_id, 'aal_agent_credentials', true );

	if ( ! is_array( $tagged_credentials ) ) {
		return;
	}

	$tagged_credentials = array_values(
		array_diff( $tagged_credentials, array( $item['uuid'] ) )
	);

	if ( empty( $tagged_credentials ) ) {
		delete_user_meta( $user_id, 'aal_agent_credentials' );
		return;
	}

	update_user_meta( $user_id, 'aal_agent_credentials', $tagged_credentials );
}
add_action( 'wp_delete_application_password', 'aal_remove_deleted_agent_credential', 10, 2 );

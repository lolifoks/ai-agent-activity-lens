<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the AI Activity admin menu page.
 */
function aal_register_admin_menu() {
	add_menu_page(
		__( 'AI Agent Activity Lens', 'ai-agent-activity-lens' ),
		__( 'AI Activity', 'ai-agent-activity-lens' ),
		'manage_options',
		'ai-agent-activity-lens',
		'aal_render_activity_page',
		'dashicons-visibility',
		80
	);
}
add_action( 'admin_menu', 'aal_register_admin_menu', 10, 0 );

/**
 * Returns all Application Password UUIDs tagged as AI-agent credentials.
 *
 * @return string[] Tagged credential UUIDs.
 */
function aal_get_tagged_credential_uuids() {
	global $wpdb;

	$stored_values = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT meta_value
			FROM {$wpdb->usermeta}
			WHERE meta_key = %s",
			'aal_agent_credentials'
		)
	);

	$credential_uuids = array();

	foreach ( $stored_values as $stored_value ) {
		$user_credentials = maybe_unserialize( $stored_value );

		if ( ! is_array( $user_credentials ) ) {
			continue;
		}

		foreach ( $user_credentials as $credential_uuid ) {
			if ( is_string( $credential_uuid ) && '' !== $credential_uuid ) {
				$credential_uuids[] = sanitize_text_field( $credential_uuid );
			}
		}
	}

	return array_values( array_unique( $credential_uuids ) );
}

/**
 * Renders the AI Activity admin page.
 */
function aal_render_activity_page() {
	global $wpdb;

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__(
				'You do not have permission to access this page.',
				'ai-agent-activity-lens'
			)
		);
	}

	$table_name = $wpdb->prefix . 'aal_activity';
	$per_page   = 20;

	$current_page = isset( $_GET['aal_page'] )
		? max( 1, absint( $_GET['aal_page'] ) )
		: 1;

	$only_tagged = (
		isset( $_GET['aal_only_tagged'] )
		&& '1' === sanitize_text_field( wp_unslash( $_GET['aal_only_tagged'] ) )
	);

	$offset       = ( $current_page - 1 ) * $per_page;
	$where_clause = '';
	$query_values = array();

	if ( $only_tagged ) {
		$tagged_credentials = aal_get_tagged_credential_uuids();

		if ( empty( $tagged_credentials ) ) {
			$where_clause = 'WHERE 1 = 0';
		} else {
			$placeholders = implode(
				', ',
				array_fill( 0, count( $tagged_credentials ), '%s' )
			);

			$where_clause = "WHERE credential_uuid IN ($placeholders)";
			$query_values = $tagged_credentials;
		}
	}

	$count_sql = "SELECT COUNT(*)
		FROM $table_name
		$where_clause";

	if ( empty( $query_values ) ) {
		$total_items = (int) $wpdb->get_var( $count_sql );
	} else {
		$total_items = (int) $wpdb->get_var(
			$wpdb->prepare( $count_sql, $query_values )
		);
	}

	$total_pages = (int) ceil( $total_items / $per_page );

	$rows_sql = "SELECT
			id,
			user_id,
			credential_uuid,
			method,
			route,
			status_code,
			duration_ms,
			ip_address,
			requested_at
		FROM $table_name
		$where_clause
		ORDER BY requested_at DESC, id DESC
		LIMIT %d OFFSET %d";

	$rows_query_values   = $query_values;
	$rows_query_values[] = $per_page;
	$rows_query_values[] = $offset;

	$rows = $wpdb->get_results(
		$wpdb->prepare( $rows_sql, $rows_query_values ),
		ARRAY_A
	);

	$showing_count = count( $rows );
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'AI Agent Activity Lens', 'ai-agent-activity-lens' ); ?></h1>

		<form method="get">
			<input type="hidden" name="page" value="ai-agent-activity-lens" />

			<label for="aal-only-tagged">
				<input
					type="checkbox"
					name="aal_only_tagged"
					id="aal-only-tagged"
					value="1"
					<?php checked( $only_tagged ); ?>
				/>
				<?php
				echo esc_html__(
					'Show tagged AI-agent credentials only',
					'ai-agent-activity-lens'
				);
				?>
			</label>

			<?php
			submit_button(
				__( 'Filter', 'ai-agent-activity-lens' ),
				'secondary',
				'',
				false
			);
			?>
		</form>

		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: displayed requests, 2: total requests. */
					__(
						'Showing %1$s of %2$s REST requests.',
						'ai-agent-activity-lens'
					),
					number_format_i18n( $showing_count ),
					number_format_i18n( $total_items )
				)
			);
			?>
		</p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col"><?php echo esc_html__( 'Timestamp', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Method', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Route', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Status', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Duration', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'User', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Credential', 'ai-agent-activity-lens' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'IP Address', 'ai-agent-activity-lens' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr>
						<td colspan="8">
							<?php echo esc_html__( 'No REST requests have been logged yet.', 'ai-agent-activity-lens' ); ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<?php
						$user_name       = __( 'Anonymous', 'ai-agent-activity-lens' );
						$credential_name = $row['credential_uuid'];

						if ( ! empty( $row['user_id'] ) ) {
							$user = get_userdata( (int) $row['user_id'] );

							if ( $user ) {
								$user_name = $user->display_name;
							}
						}

						if ( ! empty( $row['user_id'] ) && ! empty( $row['credential_uuid'] ) ) {
							$credential = WP_Application_Passwords::get_user_application_password(
								(int) $row['user_id'],
								$row['credential_uuid']
							);

							if ( is_array( $credential ) && ! empty( $credential['name'] ) ) {
								$credential_name = $credential['name'];
							}
						}
						?>
						<tr>
							<td><?php echo esc_html( $row['requested_at'] ); ?></td>
							<td><?php echo esc_html( $row['method'] ); ?></td>
							<td><code><?php echo esc_html( $row['route'] ); ?></code></td>
							<td><?php echo esc_html( $row['status_code'] ); ?></td>
							<td>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: request duration in milliseconds. */
										__( '%s ms', 'ai-agent-activity-lens' ),
										number_format_i18n( $row['duration_ms'] )
									)
								);
								?>
							</td>
							<td><?php echo esc_html( $user_name ); ?></td>
							<td><?php echo esc_html( $credential_name ); ?></td>
							<td><?php echo esc_html( $row['ip_address'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php
					$pagination_args = array(
						'page'     => 'ai-agent-activity-lens',
						'aal_page' => '%#%',
					);

					if ( $only_tagged ) {
						$pagination_args['aal_only_tagged'] = '1';
					}

					$pagination_base = add_query_arg(
						$pagination_args,
						admin_url( 'admin.php' )
					);

					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => $pagination_base,
								'format'    => '',
								'current'   => $current_page,
								'total'     => $total_pages,
								'prev_text' => __( 'Previous', 'ai-agent-activity-lens' ),
								'next_text' => __( 'Next', 'ai-agent-activity-lens' ),
							)
						)
					);
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

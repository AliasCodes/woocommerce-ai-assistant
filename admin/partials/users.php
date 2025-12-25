<?php
/**
 * Admin Users Page
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db = new WP_AI_DB();

// Pagination
$per_page = 100;
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$offset = ( $current_page - 1 ) * $per_page;

// Search
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

// Get users
$users = $db->get_all_users( $per_page, $offset, $search );
$total_users = $db->get_users_count( $search );
$total_pages = ceil( $total_users / $per_page );

?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<form method="get" class="search-form" style="float:right;">
		<input type="hidden" name="page" value="wp-ai-assistant-users">
		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search users...', 'wp-ai-assistant' ); ?>">
		<button type="submit" class="button"><?php esc_html_e( 'Search', 'wp-ai-assistant' ); ?></button>
	</form>
	
	<hr class="wp-header-end">
	
	<div class="tablenav top">
		<div class="alignleft actions">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="wp_ai_export_users">
				<?php wp_nonce_field( 'wp_ai_export_users' ); ?>
				<button type="submit" class="button button-secondary"><?php esc_html_e( 'Export to CSV', 'wp-ai-assistant' ); ?></button>
			</form>
		</div>
		
		<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s user', '%s users', $total_users, 'wp-ai-assistant' ), number_format_i18n( $total_users ) ) ); ?></span>
			<?php
			echo paginate_links( array(
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => $total_pages,
				'current'   => $current_page,
			) );
			?>
		</div>
		<?php endif; ?>
	</div>
	
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-primary"><?php esc_html_e( 'Name', 'wp-ai-assistant' ); ?></th>
				<th scope="col" class="manage-column"><?php esc_html_e( 'Email', 'wp-ai-assistant' ); ?></th>
				<th scope="col" class="manage-column"><?php esc_html_e( 'Phone', 'wp-ai-assistant' ); ?></th>
				<th scope="col" class="manage-column"><?php esc_html_e( 'IP Address', 'wp-ai-assistant' ); ?></th>
				<th scope="col" class="manage-column"><?php esc_html_e( 'First Seen', 'wp-ai-assistant' ); ?></th>
				<th scope="col" class="manage-column"><?php esc_html_e( 'Last Active', 'wp-ai-assistant' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $users ) ) : ?>
			<tr>
				<td colspan="6" style="text-align:center;padding:40px;">
					<?php esc_html_e( 'No users found.', 'wp-ai-assistant' ); ?>
				</td>
			</tr>
			<?php else : ?>
				<?php foreach ( $users as $user ) : ?>
				<tr>
					<td class="column-primary" data-colname="<?php esc_attr_e( 'Name', 'wp-ai-assistant' ); ?>">
						<strong><?php echo esc_html( $user['name'] ); ?></strong>
					</td>
					<td data-colname="<?php esc_attr_e( 'Email', 'wp-ai-assistant' ); ?>">
						<?php echo esc_html( $user['email'] ?: '—' ); ?>
					</td>
					<td data-colname="<?php esc_attr_e( 'Phone', 'wp-ai-assistant' ); ?>">
						<?php echo esc_html( $user['phone'] ?: '—' ); ?>
					</td>
					<td data-colname="<?php esc_attr_e( 'IP Address', 'wp-ai-assistant' ); ?>">
						<code><?php echo esc_html( $user['ip_address'] ?: '—' ); ?></code>
					</td>
					<td data-colname="<?php esc_attr_e( 'First Seen', 'wp-ai-assistant' ); ?>">
						<?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $user['created_at'] ) ); ?>
					</td>
					<td data-colname="<?php esc_attr_e( 'Last Active', 'wp-ai-assistant' ); ?>">
						<?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $user['updated_at'] ) ); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	
	<?php if ( $total_pages > 1 ) : ?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s user', '%s users', $total_users, 'wp-ai-assistant' ), number_format_i18n( $total_users ) ) ); ?></span>
			<?php
			echo paginate_links( array(
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => $total_pages,
				'current'   => $current_page,
			) );
			?>
		</div>
	</div>
	<?php endif; ?>
</div>


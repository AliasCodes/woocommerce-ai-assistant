<?php
/**
 * Admin Chat History Page
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db = new WP_AI_DB();

// Pagination
$per_page = 50;
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$offset = ( $current_page - 1 ) * $per_page;

// Filter by user
$user_filter = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : '';

// Get sessions
$sessions = $db->get_sessions( $per_page, $offset, $user_filter );

// Get selected session messages
$selected_session = isset( $_GET['session_id'] ) ? sanitize_text_field( $_GET['session_id'] ) : '';
$messages = array();
if ( $selected_session ) {
	$messages = $db->get_messages_by_session( $selected_session );
}

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<hr class="wp-header-end">
	
	<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
		
		<!-- Sessions List -->
		<div>
			<h2><?php esc_html_e( 'Recent Sessions', 'wp-ai-assistant' ); ?></h2>
			
			<div style="background: white; border: 1px solid #ccc; border-radius: 4px;">
				<?php if ( empty( $sessions ) ) : ?>
				<p style="padding: 20px; text-align: center; color: #666;">
					<?php esc_html_e( 'No chat sessions found.', 'wp-ai-assistant' ); ?>
				</p>
				<?php else : ?>
					<?php foreach ( $sessions as $session ) : ?>
					<div style="padding: 15px; border-bottom: 1px solid #eee; <?php echo $selected_session === $session['id'] ? 'background: #f0f0f1;' : ''; ?>">
						<a href="<?php echo esc_url( add_query_arg( 'session_id', $session['id'] ) ); ?>" style="text-decoration: none; color: inherit; display: block;">
							<div style="font-weight: 600; margin-bottom: 5px;">
								<?php echo esc_html( $session['name'] ); ?>
								<?php if ( $session['email'] ) : ?>
								<span style="font-weight: normal; color: #666; font-size: 0.9em;">(<?php echo esc_html( $session['email'] ); ?>)</span>
								<?php endif; ?>
							</div>
							<div style="font-size: 0.85em; color: #666;">
								<?php
								/* translators: %d: number of messages */
								echo esc_html( sprintf( _n( '%d message', '%d messages', $session['message_count'], 'wp-ai-assistant' ), $session['message_count'] ) );
								?>
								&bull;
								<?php echo esc_html( human_time_diff( strtotime( $session['last_activity_at'] ), current_time( 'timestamp' ) ) ); ?>
								<?php esc_html_e( 'ago', 'wp-ai-assistant' ); ?>
							</div>
						</a>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			
			<?php if ( count( $sessions ) >= $per_page ) : ?>
			<div style="margin-top: 10px; text-align: center;">
				<a href="<?php echo esc_url( add_query_arg( 'paged', $current_page + 1 ) ); ?>" class="button">
					<?php esc_html_e( 'Load More', 'wp-ai-assistant' ); ?>
				</a>
			</div>
			<?php endif; ?>
		</div>
		
		<!-- Messages View -->
		<div>
			<h2><?php esc_html_e( 'Conversation', 'wp-ai-assistant' ); ?></h2>
			
			<div style="background: white; border: 1px solid #ccc; border-radius: 4px; padding: 20px; min-height: 400px; max-height: 600px; overflow-y: auto;">
				<?php if ( empty( $selected_session ) ) : ?>
				<p style="text-align: center; color: #666; padding: 40px;">
					<?php esc_html_e( 'Select a session to view messages', 'wp-ai-assistant' ); ?>
				</p>
				<?php elseif ( empty( $messages ) ) : ?>
				<p style="text-align: center; color: #666; padding: 40px;">
					<?php esc_html_e( 'No messages in this session', 'wp-ai-assistant' ); ?>
				</p>
				<?php else : ?>
					<?php foreach ( $messages as $message ) : ?>
					<div style="margin-bottom: 20px; display: flex; gap: 10px; <?php echo $message['role'] === 'user' ? 'flex-direction: row-reverse;' : ''; ?>">
						<div style="width: 36px; height: 36px; border-radius: 50%; background: <?php echo $message['role'] === 'user' ? '#64748b' : '#667eea'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; flex-shrink: 0;">
							<?php echo $message['role'] === 'user' ? esc_html( substr( $message['name'], 0, 2 ) ) : 'AI'; ?>
						</div>
						<div style="max-width: 70%; padding: 12px 16px; border-radius: 12px; background: <?php echo $message['role'] === 'user' ? '#667eea' : '#f0f0f1'; ?>; color: <?php echo $message['role'] === 'user' ? 'white' : 'black'; ?>;">
							<div style="line-height: 1.5; word-wrap: break-word;">
								<?php echo esc_html( $message['message'] ); ?>
							</div>
							<div style="font-size: 11px; margin-top: 4px; opacity: 0.7;">
								<?php echo esc_html( mysql2date( get_option( 'time_format' ), $message['created_at'] ) ); ?>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		
	</div>
</div>

<style>
.wrap h2 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 10px;
}
</style>


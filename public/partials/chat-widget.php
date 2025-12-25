<?php
/**
 * Chat widget HTML template
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'wp_ai_assistant_settings', array() );
$position = isset( $settings['widget_position'] ) ? $settings['widget_position'] : 'bottom-right';
$greeting = isset( $settings['greeting_message'] ) ? $settings['greeting_message'] : __( 'Hi! How can I help you today?', 'wp-ai-assistant' );

// Support bilingual greeting
$locale = get_locale();
if ( $locale === 'fa_IR' && isset( $settings['greeting_message_fa'] ) ) {
	$greeting = $settings['greeting_message_fa'];
}
?>

<!-- Chat Widget Container -->
<div id="wp-ai-chat-widget" class="wp-ai-chat-widget" data-position="<?php echo esc_attr( $position ); ?>">
	
	<!-- Chat Bubble (Collapsed State) -->
	<div id="wp-ai-chat-bubble" class="wp-ai-chat-bubble">
		<svg class="wp-ai-chat-icon" viewBox="0 0 24 24" fill="currentColor">
			<path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
		</svg>
		<span class="wp-ai-badge" id="wp-ai-unread-badge" style="display:none;">1</span>
		<span class="wp-ai-bubble-text"><?php esc_html_e( 'Intelligent Assistant', 'wp-ai-assistant' ); ?></span>
	</div>
	
	<!-- Chat Window (Expanded State) -->
	<div id="wp-ai-chat-window" class="wp-ai-chat-window" style="display:none;">
		
		<!-- Header -->
		<div class="wp-ai-chat-header">
			<div class="wp-ai-chat-title">
				<svg class="wp-ai-header-icon" viewBox="0 0 24 24" fill="currentColor">
					<circle cx="12" cy="12" r="10"/>
				</svg>
				<span><?php esc_html_e( 'AI Assistant', 'wp-ai-assistant' ); ?></span>
			</div>
			<div class="wp-ai-chat-actions">
				<button id="wp-ai-minimize" class="wp-ai-action-btn" aria-label="<?php esc_attr_e( 'Minimize', 'wp-ai-assistant' ); ?>">
					<svg viewBox="0 0 24 24" fill="currentColor">
						<path d="M19 13H5v-2h14v2z"/>
					</svg>
				</button>
				<button id="wp-ai-close" class="wp-ai-action-btn" aria-label="<?php esc_attr_e( 'Close', 'wp-ai-assistant' ); ?>">
					<svg viewBox="0 0 24 24" fill="currentColor">
						<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
					</svg>
				</button>
			</div>
		</div>
		
		<!-- User ID Form (shown for first-time visitors) -->
		<div id="wp-ai-user-form" class="wp-ai-user-form" style="display:none;">
			<h3><?php esc_html_e( 'Welcome! Let\'s get started', 'wp-ai-assistant' ); ?></h3>
			<p><?php esc_html_e( 'Please provide your details to begin chatting', 'wp-ai-assistant' ); ?></p>
			<form id="wp-ai-submit-user-form">
				<input type="text" name="name" placeholder="<?php esc_attr_e( 'Your Name*', 'wp-ai-assistant' ); ?>" required>
				<input type="email" name="email" placeholder="<?php esc_attr_e( 'Email Address', 'wp-ai-assistant' ); ?>">
				<input type="tel" name="phone" placeholder="<?php esc_attr_e( 'Phone Number', 'wp-ai-assistant' ); ?>">
				<button type="submit" class="wp-ai-btn-primary"><?php esc_html_e( 'Start Chatting', 'wp-ai-assistant' ); ?></button>
			</form>
		</div>
		
		<!-- Chat Messages Area -->
		<div id="wp-ai-messages" class="wp-ai-messages">
			<div class="wp-ai-message wp-ai-assistant-message">
				<div class="wp-ai-message-avatar">AI</div>
				<div class="wp-ai-message-content">
					<p><?php echo esc_html( $greeting ); ?></p>
					<span class="wp-ai-message-time"><?php esc_html_e( 'Just now', 'wp-ai-assistant' ); ?></span>
				</div>
			</div>
		</div>
		
		<!-- Typing Indicator -->
		<div id="wp-ai-typing" class="wp-ai-typing" style="display:none;">
			<div class="wp-ai-typing-indicator">
				<span></span><span></span><span></span>
			</div>
			<span class="wp-ai-typing-text"><?php esc_html_e( 'AI is typing...', 'wp-ai-assistant' ); ?></span>
		</div>
		
		<!-- Input Area -->
		<div class="wp-ai-input-area">
			<textarea 
				id="wp-ai-message-input" 
				class="wp-ai-input" 
				placeholder="<?php esc_attr_e( 'Type your message...', 'wp-ai-assistant' ); ?>"
				rows="1"
				maxlength="1000"
			></textarea>
			<button id="wp-ai-send-btn" class="wp-ai-send-btn" aria-label="<?php esc_attr_e( 'Send message', 'wp-ai-assistant' ); ?>">
				<svg viewBox="0 0 24 24" fill="currentColor">
					<path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
				</svg>
			</button>
		</div>
		
		<!-- Footer -->
		<div class="wp-ai-footer">
			<span><?php esc_html_e( 'Powered by AI Assistant', 'wp-ai-assistant' ); ?></span>
		</div>
	</div>
</div>


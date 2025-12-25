<?php
/**
 * Plugin activation class
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_Activator {
	
	/**
	 * Activate plugin
	 */
	public static function activate() {
		// Create database tables
		require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-db.php';
		$db = new WP_AI_DB();
		$db->create_tables();
		
		// Set default options
		$default_settings = array(
			'api_key'             => '',
			'api_url'             => 'http://localhost:3000',
			'project_id'          => '',
			'widget_position'     => 'bottom-right',
			'widget_enabled'      => true,
			'greeting_message'    => __( 'Hi! How can I help you today?', 'wp-ai-assistant' ),
			'greeting_message_fa' => 'سلام! چطور می‌تونم کمکتون کنم؟',
			'placeholder_text'    => __( 'Type your message...', 'wp-ai-assistant' ),
			'placeholder_text_fa' => 'پیام خود را بنویسید...',
			'primary_color'       => '#667eea',
			'forbidden_words'     => '',
			'collect_email'       => true,
			'collect_phone'       => true,
			'show_timestamp'      => true,
			'enable_emojis'       => false,
			'rate_limit'          => 60,
		);
		
		// Only set defaults if not already configured
		if ( ! get_option( 'wp_ai_assistant_settings' ) ) {
			add_option( 'wp_ai_assistant_settings', $default_settings );
		}
		
		// Store activation time
		add_option( 'wp_ai_assistant_activation_time', current_time( 'mysql' ) );
		
		// Store version
		add_option( 'wp_ai_assistant_version', WP_AI_ASSISTANT_VERSION );
		
		// Schedule cleanup cron job (daily)
		if ( ! wp_next_scheduled( 'wp_ai_assistant_cleanup_sessions' ) ) {
			wp_schedule_event( time(), 'daily', 'wp_ai_assistant_cleanup_sessions' );
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}


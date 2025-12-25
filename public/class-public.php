<?php
/**
 * Public-facing functionality
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_Public {
	
	/**
	 * @var WP_AI_DB Database instance
	 */
	private $db;
	
	/**
	 * @var WP_AI_API_Client API client instance
	 */
	private $api_client;
	
	/**
	 * @var WP_AI_Filter Filter instance
	 */
	private $filter;
	
	/**
	 * @var array Rate limit tracker
	 */
	private $rate_limits = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db         = new WP_AI_DB();
		$this->api_client = new WP_AI_API_Client();
		$this->filter     = new WP_AI_Filter();
		
		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'render_chat_widget' ) );
		
		// AJAX handlers (for logged in and non-logged in users)
		add_action( 'wp_ajax_wp_ai_save_user', array( $this, 'ajax_save_user' ) );
		add_action( 'wp_ajax_nopriv_wp_ai_save_user', array( $this, 'ajax_save_user' ) );
		
		add_action( 'wp_ajax_wp_ai_send_message', array( $this, 'ajax_send_message' ) );
		add_action( 'wp_ajax_nopriv_wp_ai_send_message', array( $this, 'ajax_send_message' ) );
		
		add_action( 'wp_ajax_wp_ai_save_message', array( $this, 'ajax_save_message' ) );
		add_action( 'wp_ajax_nopriv_wp_ai_save_message', array( $this, 'ajax_save_message' ) );
		
		add_action( 'wp_ajax_wp_ai_get_history', array( $this, 'ajax_get_history' ) );
		add_action( 'wp_ajax_nopriv_wp_ai_get_history', array( $this, 'ajax_get_history' ) );
		
		// Cleanup cron
		add_action( 'wp_ai_assistant_cleanup_sessions', array( $this, 'cleanup_old_sessions' ) );
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		
		// Check if widget is enabled
		if ( ! isset( $settings['widget_enabled'] ) || ! $settings['widget_enabled'] ) {
			return;
		}
		
		// Enqueue CSS
		wp_enqueue_style(
			'wp-ai-assistant-frontend',
			WP_AI_ASSISTANT_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			WP_AI_ASSISTANT_VERSION
		);
		
		// Add RTL support if needed
		if ( is_rtl() ) {
			wp_add_inline_style(
				'wp-ai-assistant-frontend',
				'body { direction: rtl; } .wp-ai-chat-widget { direction: rtl; }'
			);
		}
		
		// Enqueue JS
		wp_enqueue_script(
			'wp-ai-assistant-chat',
			WP_AI_ASSISTANT_PLUGIN_URL . 'assets/js/chat-widget.js',
			array(),
			WP_AI_ASSISTANT_VERSION,
			true
		);
		
		// Localize script
		wp_localize_script(
			'wp-ai-assistant-chat',
			'wpAiChatConfig',
			array(
				'apiUrl'    => isset( $settings['api_url'] ) ? $settings['api_url'] : '',
				'apiKey'    => isset( $settings['api_key'] ) ? $settings['api_key'] : '',
				'projectId' => isset( $settings['project_id'] ) ? $settings['project_id'] : '',
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'wp_ai_chat_nonce' ),
				'settings'  => array(
					'position'        => isset( $settings['widget_position'] ) ? $settings['widget_position'] : 'bottom-right',
					'primaryColor'    => isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#667eea',
					'greetingMessage' => isset( $settings['greeting_message'] ) ? $settings['greeting_message'] : __( 'Hi! How can I help you today?', 'wp-ai-assistant' ),
				),
			)
		);
	}
	
	/**
	 * Render chat widget
	 */
	public function render_chat_widget() {
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		
		if ( ! isset( $settings['widget_enabled'] ) || ! $settings['widget_enabled'] ) {
			return;
		}
		
		include WP_AI_ASSISTANT_PLUGIN_DIR . 'public/partials/chat-widget.php';
	}
	
	/**
	 * AJAX: Save user
	 */
	public function ajax_save_user() {
		check_ajax_referer( 'wp_ai_chat_nonce', 'nonce' );
		
		$name  = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		
		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Name is required', 'wp-ai-assistant' ) ) );
		}
		
		$result = $this->db->save_user( array(
			'name'  => $name,
			'email' => $email,
			'phone' => $phone,
		) );
		
		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save user', 'wp-ai-assistant' ) ) );
		}
	}
	
	/**
	 * AJAX: Send message
	 */
	public function ajax_send_message() {
		check_ajax_referer( 'wp_ai_chat_nonce', 'nonce' );
		
		$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
		$user_id    = isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : '';
		
		if ( empty( $message ) || empty( $session_id ) || empty( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required fields', 'wp-ai-assistant' ) ) );
		}
		
		// Check message length
		if ( strlen( $message ) > 1000 ) {
			wp_send_json_error( array( 'message' => __( 'Message is too long (max 1000 characters)', 'wp-ai-assistant' ) ) );
		}
		
		// Filter message
		if ( ! $this->filter->is_allowed( $message ) ) {
			wp_send_json_error( array( 'message' => __( 'Message contains forbidden content', 'wp-ai-assistant' ) ) );
		}
		
		// Get user from DB
		$user = $this->db->get_user_by_cookie( $user_id );
		if ( ! $user ) {
			wp_send_json_error( array( 'message' => __( 'User not found', 'wp-ai-assistant' ) ) );
		}
		
		// Check rate limit
		if ( ! $this->check_rate_limit( $user['id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'You are sending messages too quickly. Please wait a moment.', 'wp-ai-assistant' ) ) );
		}
		
		// Update or create session
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		$project_id = isset( $settings['project_id'] ) ? $settings['project_id'] : '';
		$this->db->upsert_session( $session_id, $user['id'], $project_id );
		
		// Send to backend API
		$response = $this->api_client->send_message( $session_id, $user['id'], $message );
		
		if ( $response && isset( $response['success'] ) && $response['success'] ) {
			wp_send_json_success( array(
				'response'  => $response['response'],
				'sessionId' => $response['sessionId'],
				'metadata'  => isset( $response['metadata'] ) ? $response['metadata'] : array(),
			) );
		} else {
			$error_message = isset( $response['error'] ) ? $response['error'] : __( 'Unknown error occurred', 'wp-ai-assistant' );
			wp_send_json_error( array( 'message' => $error_message ) );
		}
	}
	
	/**
	 * AJAX: Save message to DB
	 */
	public function ajax_save_message() {
		check_ajax_referer( 'wp_ai_chat_nonce', 'nonce' );
		
		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
		$user_id    = isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : '';
		$role       = isset( $_POST['role'] ) ? sanitize_text_field( $_POST['role'] ) : '';
		$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		
		if ( empty( $session_id ) || empty( $user_id ) || empty( $role ) || empty( $message ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required fields', 'wp-ai-assistant' ) ) );
		}
		
		// Validate role
		if ( ! in_array( $role, array( 'user', 'assistant' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid role', 'wp-ai-assistant' ) ) );
		}
		
		$user = $this->db->get_user_by_cookie( $user_id );
		if ( ! $user ) {
			wp_send_json_error( array( 'message' => __( 'User not found', 'wp-ai-assistant' ) ) );
		}
		
		$result = $this->db->save_message( $session_id, $user['id'], $role, $message );
		
		if ( $result ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save message', 'wp-ai-assistant' ) ) );
		}
	}
	
	/**
	 * AJAX: Get chat history
	 */
	public function ajax_get_history() {
		check_ajax_referer( 'wp_ai_chat_nonce', 'nonce' );
		
		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
		
		if ( empty( $session_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Session ID required', 'wp-ai-assistant' ) ) );
		}
		
		$messages = $this->db->get_chat_history( $session_id );
		
		wp_send_json_success( array( 'messages' => $messages ) );
	}
	
	/**
	 * Check rate limit for user
	 *
	 * @param int $user_id User ID
	 * @return bool
	 */
	private function check_rate_limit( $user_id ) {
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		$rate_limit = isset( $settings['rate_limit'] ) ? (int) $settings['rate_limit'] : 60;
		
		// Get rate limit from transient
		$key = 'wp_ai_rate_limit_' . $user_id;
		$count = get_transient( $key );
		
		if ( $count === false ) {
			// First message in the hour
			set_transient( $key, 1, HOUR_IN_SECONDS );
			return true;
		}
		
		if ( $count >= $rate_limit ) {
			return false;
		}
		
		// Increment count
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return true;
	}
	
	/**
	 * Cleanup old sessions (cron job)
	 */
	public function cleanup_old_sessions() {
		$deleted = $this->db->cleanup_old_sessions( 90 );
		
		// Log cleanup
		error_log( sprintf( 'WP AI Assistant: Cleaned up %d old sessions', $deleted ) );
	}
}


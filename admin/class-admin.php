<?php
/**
 * Admin functionality
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_Admin {
	
	/**
	 * @var WP_AI_DB Database instance
	 */
	private $db;
	
	/**
	 * @var WP_AI_API_Client API client instance
	 */
	private $api_client;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db         = new WP_AI_DB();
		$this->api_client = new WP_AI_API_Client();
		
		// Hooks
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_wp_ai_test_connection', array( $this, 'test_connection' ) );
		add_action( 'admin_post_wp_ai_export_users', array( $this, 'export_users' ) );
	}
	
	/**
	 * Add menu pages
	 */
	public function add_menu_pages() {
		// Main menu
		add_menu_page(
			__( 'WP AI Assistant', 'wp-ai-assistant' ),
			__( 'AI Assistant', 'wp-ai-assistant' ),
			'manage_options',
			'wp-ai-assistant',
			array( $this, 'render_analytics_page' ),
			'dashicons-format-chat',
			30
		);
		
		// Analytics (same as main page)
		add_submenu_page(
			'wp-ai-assistant',
			__( 'Analytics', 'wp-ai-assistant' ),
			__( 'Analytics', 'wp-ai-assistant' ),
			'manage_options',
			'wp-ai-assistant',
			array( $this, 'render_analytics_page' )
		);
		
		// Users
		add_submenu_page(
			'wp-ai-assistant',
			__( 'Chat Users', 'wp-ai-assistant' ),
			__( 'Users', 'wp-ai-assistant' ),
			'manage_options',
			'wp-ai-assistant-users',
			array( $this, 'render_users_page' )
		);
		
		// Chat History
		add_submenu_page(
			'wp-ai-assistant',
			__( 'Chat History', 'wp-ai-assistant' ),
			__( 'Chat History', 'wp-ai-assistant' ),
			'manage_options',
			'wp-ai-assistant-chats',
			array( $this, 'render_chats_page' )
		);
		
		// Settings
		add_submenu_page(
			'wp-ai-assistant',
			__( 'Settings', 'wp-ai-assistant' ),
			__( 'Settings', 'wp-ai-assistant' ),
			'manage_options',
			'wp-ai-assistant-settings',
			array( $this, 'render_settings_page' )
		);
	}
	
	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on our admin pages
		if ( strpos( $hook, 'wp-ai-assistant' ) === false ) {
			return;
		}
		
		// Enqueue admin CSS
		wp_enqueue_style(
			'wp-ai-assistant-admin',
			WP_AI_ASSISTANT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_AI_ASSISTANT_VERSION
		);
		
		// Enqueue admin JS
		wp_enqueue_script(
			'wp-ai-assistant-admin',
			WP_AI_ASSISTANT_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WP_AI_ASSISTANT_VERSION,
			true
		);
		
		// Localize script
		wp_localize_script(
			'wp-ai-assistant-admin',
			'wpAiAdminConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wp_ai_admin_nonce' ),
			)
		);
		
		// Enqueue Chart.js for analytics
		if ( $hook === 'toplevel_page_wp-ai-assistant' ) {
			wp_enqueue_script(
				'chartjs',
				'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
				array(),
				'4.4.0',
				true
			);
		}
	}
	
	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'wp_ai_assistant_settings', 'wp_ai_assistant_settings', array(
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );
	}
	
	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		
		$sanitized['api_key']             = isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : '';
		$sanitized['api_url']             = isset( $input['api_url'] ) ? esc_url_raw( $input['api_url'] ) : '';
		$sanitized['project_id']          = isset( $input['project_id'] ) ? sanitize_text_field( $input['project_id'] ) : '';
		$sanitized['widget_position']     = isset( $input['widget_position'] ) ? sanitize_text_field( $input['widget_position'] ) : 'bottom-right';
		$sanitized['widget_enabled']      = isset( $input['widget_enabled'] );
		$sanitized['greeting_message']    = isset( $input['greeting_message'] ) ? sanitize_text_field( $input['greeting_message'] ) : '';
		$sanitized['greeting_message_fa'] = isset( $input['greeting_message_fa'] ) ? sanitize_text_field( $input['greeting_message_fa'] ) : '';
		$sanitized['placeholder_text']    = isset( $input['placeholder_text'] ) ? sanitize_text_field( $input['placeholder_text'] ) : '';
		$sanitized['placeholder_text_fa'] = isset( $input['placeholder_text_fa'] ) ? sanitize_text_field( $input['placeholder_text_fa'] ) : '';
		$sanitized['primary_color']       = isset( $input['primary_color'] ) ? sanitize_hex_color( $input['primary_color'] ) : '#667eea';
		$sanitized['forbidden_words']     = isset( $input['forbidden_words'] ) ? sanitize_textarea_field( $input['forbidden_words'] ) : '';
		$sanitized['collect_email']       = isset( $input['collect_email'] );
		$sanitized['collect_phone']       = isset( $input['collect_phone'] );
		$sanitized['show_timestamp']      = isset( $input['show_timestamp'] );
		$sanitized['enable_emojis']       = isset( $input['enable_emojis'] );
		$sanitized['rate_limit']          = isset( $input['rate_limit'] ) ? absint( $input['rate_limit'] ) : 60;
		
		return $sanitized;
	}
	
	/**
	 * Render analytics page
	 */
	public function render_analytics_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-ai-assistant' ) );
		}
		
		include WP_AI_ASSISTANT_PLUGIN_DIR . 'admin/partials/analytics.php';
	}
	
	/**
	 * Render users page
	 */
	public function render_users_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-ai-assistant' ) );
		}
		
		include WP_AI_ASSISTANT_PLUGIN_DIR . 'admin/partials/users.php';
	}
	
	/**
	 * Render chats page
	 */
	public function render_chats_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-ai-assistant' ) );
		}
		
		include WP_AI_ASSISTANT_PLUGIN_DIR . 'admin/partials/chats.php';
	}
	
	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-ai-assistant' ) );
		}
		
		include WP_AI_ASSISTANT_PLUGIN_DIR . 'admin/partials/settings.php';
	}
	
	/**
	 * Test API connection
	 */
	public function test_connection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'wp-ai-assistant' ) );
		}
		
		check_admin_referer( 'wp_ai_test_connection' );
		
		$result = $this->api_client->test_connection();
		
		if ( $result['success'] ) {
			add_settings_error(
				'wp_ai_assistant_messages',
				'wp_ai_test_connection',
				$result['message'],
				'success'
			);
		} else {
			add_settings_error(
				'wp_ai_assistant_messages',
				'wp_ai_test_connection',
				$result['message'],
				'error'
			);
		}
		
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		
		$redirect = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
		wp_safe_redirect( $redirect );
		exit;
	}
	
	/**
	 * Export users to CSV
	 */
	public function export_users() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'wp-ai-assistant' ) );
		}
		
		check_admin_referer( 'wp_ai_export_users' );
		
		$users = $this->db->get_all_users( 10000, 0 );
		
		// Set headers for CSV download
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=chat-users-' . date( 'Y-m-d' ) . '.csv' );
		
		// Create file pointer
		$output = fopen( 'php://output', 'w' );
		
		// Add BOM for UTF-8
		fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) );
		
		// Add headers
		fputcsv( $output, array( 'ID', 'Name', 'Email', 'Phone', 'Created At', 'Updated At' ) );
		
		// Add data
		foreach ( $users as $user ) {
			fputcsv( $output, array(
				$user['id'],
				$user['name'],
				$user['email'],
				$user['phone'],
				$user['created_at'],
				$user['updated_at'],
			) );
		}
		
		fclose( $output );
		exit;
	}
}


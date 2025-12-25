<?php
/**
 * Session management class
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_Session {
	
	/**
	 * @var string Current session ID
	 */
	private $session_id;
	
	/**
	 * @var WP_AI_DB Database instance
	 */
	private $db;
	
	/**
	 * Constructor
	 *
	 * @param string $session_id Session ID
	 */
	public function __construct( $session_id = '' ) {
		$this->db = new WP_AI_DB();
		
		if ( ! empty( $session_id ) ) {
			$this->session_id = $session_id;
		}
	}
	
	/**
	 * Get current session ID
	 *
	 * @return string
	 */
	public function get_session_id() {
		return $this->session_id;
	}
	
	/**
	 * Create new session
	 *
	 * @param int $user_id User ID
	 * @param string $project_id Project ID
	 * @return string Session ID
	 */
	public function create_session( $user_id, $project_id = '' ) {
		$this->session_id = wp_generate_uuid4();
		
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		if ( empty( $project_id ) && isset( $settings['project_id'] ) ) {
			$project_id = $settings['project_id'];
		}
		
		$this->db->upsert_session( $this->session_id, $user_id, $project_id );
		
		return $this->session_id;
	}
	
	/**
	 * Get or create session
	 *
	 * @param string $session_id Existing session ID (optional)
	 * @param int $user_id User ID
	 * @param string $project_id Project ID
	 * @return string Session ID
	 */
	public function get_or_create( $session_id, $user_id, $project_id = '' ) {
		if ( ! empty( $session_id ) ) {
			$this->session_id = $session_id;
			
			// Update last activity
			$settings = get_option( 'wp_ai_assistant_settings', array() );
			if ( empty( $project_id ) && isset( $settings['project_id'] ) ) {
				$project_id = $settings['project_id'];
			}
			
			$this->db->upsert_session( $session_id, $user_id, $project_id );
		} else {
			$this->session_id = $this->create_session( $user_id, $project_id );
		}
		
		return $this->session_id;
	}
	
	/**
	 * Get session info from cookie
	 *
	 * @return string|false Session ID or false
	 */
	public function get_session_from_cookie() {
		if ( isset( $_COOKIE['wp_ai_session_id'] ) ) {
			return sanitize_text_field( $_COOKIE['wp_ai_session_id'] );
		}
		
		return false;
	}
	
	/**
	 * Save session ID to cookie
	 *
	 * @param string $session_id Session ID
	 * @param int $expiry Expiry time in seconds (default: 24 hours)
	 */
	public function save_session_to_cookie( $session_id, $expiry = 86400 ) {
		setcookie(
			'wp_ai_session_id',
			$session_id,
			time() + $expiry,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true // HTTP only
		);
	}
	
	/**
	 * Get chat history for current session
	 *
	 * @param int $limit Maximum messages to retrieve
	 * @return array
	 */
	public function get_history( $limit = 50 ) {
		if ( empty( $this->session_id ) ) {
			return array();
		}
		
		return $this->db->get_chat_history( $this->session_id, $limit );
	}
	
	/**
	 * Check if session is valid
	 *
	 * @param string $session_id Session ID to check
	 * @return bool
	 */
	public function is_valid( $session_id ) {
		if ( empty( $session_id ) ) {
			return false;
		}
		
		$history = $this->db->get_chat_history( $session_id, 1 );
		return ! empty( $history );
	}
	
	/**
	 * Generate session ID
	 *
	 * @return string
	 */
	public static function generate_id() {
		return wp_generate_uuid4();
	}
}


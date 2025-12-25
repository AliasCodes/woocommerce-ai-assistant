<?php
/**
 * API Client for backend communication
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_API_Client {
	
	/**
	 * @var string API URL
	 */
	private $api_url;
	
	/**
	 * @var string API Key
	 */
	private $api_key;
	
	/**
	 * @var string Project ID
	 */
	private $project_id;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		$this->api_url    = isset( $settings['api_url'] ) ? rtrim( $settings['api_url'], '/' ) : 'http://localhost:3000';
		$this->api_key    = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		$this->project_id = isset( $settings['project_id'] ) ? $settings['project_id'] : '';
	}
	
	/**
	 * Send message to backend API
	 *
	 * @param string $session_id Session ID
	 * @param int $user_id User ID (internal WordPress user)
	 * @param string $message User message
	 * @param array $conversation_history Conversation history
	 * @return array API response
	 */
	public function send_message( $session_id, $user_id, $message, $conversation_history = array() ) {
		// Check if API key is configured
		if ( empty( $this->api_key ) ) {
			return array(
				'success' => false,
				'error'   => __( 'API key not configured. Please contact the site administrator.', 'wp-ai-assistant' ),
			);
		}
		
		// Prepare payload
		$payload = array(
			'sessionId' => $session_id,
			'message'   => $message,
			'userId'    => (string) $user_id,
			'metadata'  => array(
				'platform' => 'wordpress',
				'siteUrl'  => get_site_url(),
				'locale'   => get_locale(),
			),
		);
		
		// Prepare headers
		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-Key'    => $this->api_key,
			'X-Origin'     => get_site_url(),
		);
		
		// Add signature if secret is defined
		if ( defined( 'WP_AI_WEBHOOK_SECRET' ) && WP_AI_WEBHOOK_SECRET ) {
			$headers['X-Signature'] = $this->generate_signature( wp_json_encode( $payload ) );
		}
		
		// Make request
		$response = wp_remote_post(
			$this->api_url . '/v1/chat',
			array(
				'headers' => $headers,
				'body'    => wp_json_encode( $payload ),
				'timeout' => 30,
			)
		);
		
		// Handle errors
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: Error message */
					__( 'Connection error: %s', 'wp-ai-assistant' ),
					$response->get_error_message()
				),
			);
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		// Handle HTTP errors
		if ( $response_code !== 200 ) {
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Unknown error occurred', 'wp-ai-assistant' );
			return array(
				'success' => false,
				'error'   => $error_message,
			);
		}
		
		// Return successful response
		if ( isset( $data['success'] ) && $data['success'] ) {
			return array(
				'success'   => true,
				'response'  => isset( $data['data']['response'] ) ? $data['data']['response'] : '',
				'sessionId' => isset( $data['data']['sessionId'] ) ? $data['data']['sessionId'] : $session_id,
				'metadata'  => isset( $data['data']['metadata'] ) ? $data['data']['metadata'] : array(),
			);
		}
		
		return array(
			'success' => false,
			'error'   => __( 'Invalid response from server', 'wp-ai-assistant' ),
		);
	}
	
	/**
	 * Generate HMAC signature for request validation
	 *
	 * @param string $payload JSON payload
	 * @return string HMAC signature
	 */
	private function generate_signature( $payload ) {
		$secret = defined( 'WP_AI_WEBHOOK_SECRET' ) ? WP_AI_WEBHOOK_SECRET : 'default-secret';
		return hash_hmac( 'sha256', $payload, $secret );
	}
	
	/**
	 * Validate API key (optional health check)
	 *
	 * @return bool True if valid, false otherwise
	 */
	public function validate_api_key() {
		if ( empty( $this->api_key ) ) {
			return false;
		}
		
		// Try a simple validation request
		$response = wp_remote_get(
			$this->api_url . '/v1/health',
			array(
				'headers' => array(
					'X-API-Key' => $this->api_key,
				),
				'timeout' => 10,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$code = wp_remote_retrieve_response_code( $response );
		return $code === 200;
	}
	
	/**
	 * Test connection to backend
	 *
	 * @return array Test result
	 */
	public function test_connection() {
		if ( empty( $this->api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key is not configured', 'wp-ai-assistant' ),
			);
		}
		
		if ( empty( $this->api_url ) ) {
			return array(
				'success' => false,
				'message' => __( 'API URL is not configured', 'wp-ai-assistant' ),
			);
		}
		
		// Try to connect
		$response = wp_remote_get(
			$this->api_url . '/v1/health',
			array(
				'timeout' => 10,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: Error message */
					__( 'Connection failed: %s', 'wp-ai-assistant' ),
					$response->get_error_message()
				),
			);
		}
		
		$code = wp_remote_retrieve_response_code( $response );
		
		if ( $code === 200 ) {
			return array(
				'success' => true,
				'message' => __( 'Connection successful!', 'wp-ai-assistant' ),
			);
		}
		
		return array(
			'success' => false,
			'message' => sprintf(
				/* translators: %d: HTTP status code */
				__( 'Server returned status code: %d', 'wp-ai-assistant' ),
				$code
			),
		);
	}
}


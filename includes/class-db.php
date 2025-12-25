<?php
/**
 * Database operations class
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_DB {
	
	/**
	 * @var wpdb WordPress database object
	 */
	private $wpdb;
	
	/**
	 * @var string Users table name
	 */
	private $users_table;
	
	/**
	 * @var string Messages table name
	 */
	private $messages_table;
	
	/**
	 * @var string Sessions table name
	 */
	private $sessions_table;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb            = $wpdb;
		$this->users_table     = $wpdb->prefix . 'ai_chat_users';
		$this->messages_table  = $wpdb->prefix . 'ai_chat_messages';
		$this->sessions_table  = $wpdb->prefix . 'ai_chat_sessions';
	}
	
	/**
	 * Create database tables
	 */
	public function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		$charset_collate = $this->wpdb->get_charset_collate();
		
		// Users table
		$sql_users = "CREATE TABLE {$this->users_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			email VARCHAR(255) NULL,
			phone VARCHAR(50) NULL,
			cookie_id VARCHAR(255) NOT NULL UNIQUE,
			ip_address VARCHAR(45) NULL,
			user_agent TEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			INDEX idx_cookie_id (cookie_id),
			INDEX idx_email (email),
			INDEX idx_created_at (created_at)
		) $charset_collate;";
		
		// Messages table
		$sql_messages = "CREATE TABLE {$this->messages_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id VARCHAR(255) NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			role ENUM('user', 'assistant') NOT NULL,
			message TEXT NOT NULL,
			metadata TEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			INDEX idx_session_id (session_id),
			INDEX idx_user_id (user_id),
			INDEX idx_created_at (created_at)
		) $charset_collate;";
		
		// Sessions table
		$sql_sessions = "CREATE TABLE {$this->sessions_table} (
			id VARCHAR(255) NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			project_id VARCHAR(255) NOT NULL,
			started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			last_activity_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			ended_at DATETIME NULL,
			message_count INT DEFAULT 0,
			PRIMARY KEY (id),
			INDEX idx_user_id (user_id),
			INDEX idx_last_activity (last_activity_at)
		) $charset_collate;";
		
		dbDelta( $sql_users );
		dbDelta( $sql_messages );
		dbDelta( $sql_sessions );
	}
	
	/**
	 * Save user
	 *
	 * @param array $data User data
	 * @return array|false
	 */
	public function save_user( $data ) {
		$cookie_id = wp_generate_uuid4();
		
		$result = $this->wpdb->insert(
			$this->users_table,
			array(
				'name'       => sanitize_text_field( $data['name'] ),
				'email'      => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : null,
				'phone'      => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : null,
				'cookie_id'  => $cookie_id,
				'ip_address' => $this->get_client_ip(),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		
		if ( $result ) {
			return array(
				'id'        => $this->wpdb->insert_id,
				'cookie_id' => $cookie_id,
			);
		}
		
		return false;
	}
	
	/**
	 * Get user by cookie ID
	 *
	 * @param string $cookie_id Cookie ID
	 * @return array|null
	 */
	public function get_user_by_cookie( $cookie_id ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->users_table} WHERE cookie_id = %s",
				$cookie_id
			),
			ARRAY_A
		);
	}
	
	/**
	 * Save message
	 *
	 * @param string $session_id Session ID
	 * @param int $user_id User ID
	 * @param string $role Message role (user/assistant)
	 * @param string $message Message content
	 * @param mixed $metadata Additional metadata
	 * @return int|false
	 */
	public function save_message( $session_id, $user_id, $role, $message, $metadata = null ) {
		$result = $this->wpdb->insert(
			$this->messages_table,
			array(
				'session_id' => $session_id,
				'user_id'    => $user_id,
				'role'       => $role,
				'message'    => $message,
				'metadata'   => $metadata ? wp_json_encode( $metadata ) : null,
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);
		
		if ( $result ) {
			// Increment message count in session
			$this->wpdb->query(
				$this->wpdb->prepare(
					"UPDATE {$this->sessions_table} SET message_count = message_count + 1 WHERE id = %s",
					$session_id
				)
			);
			return $this->wpdb->insert_id;
		}
		
		return false;
	}
	
	/**
	 * Get chat history
	 *
	 * @param string $session_id Session ID
	 * @param int $limit Maximum messages to retrieve
	 * @return array
	 */
	public function get_chat_history( $session_id, $limit = 50 ) {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->messages_table} 
				WHERE session_id = %s 
				ORDER BY created_at ASC 
				LIMIT %d",
				$session_id,
				$limit
			),
			ARRAY_A
		);
	}
	
	/**
	 * Create or update session
	 *
	 * @param string $session_id Session ID
	 * @param int $user_id User ID
	 * @param string $project_id Project ID
	 * @return bool
	 */
	public function upsert_session( $session_id, $user_id, $project_id ) {
		$existing = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->sessions_table} WHERE id = %s",
				$session_id
			)
		);
		
		if ( $existing ) {
			// Update last activity
			return $this->wpdb->update(
				$this->sessions_table,
				array( 'last_activity_at' => current_time( 'mysql' ) ),
				array( 'id' => $session_id ),
				array( '%s' ),
				array( '%s' )
			) !== false;
		} else {
			// Create new session
			return $this->wpdb->insert(
				$this->sessions_table,
				array(
					'id'         => $session_id,
					'user_id'    => $user_id,
					'project_id' => $project_id,
				),
				array( '%s', '%d', '%s' )
			) !== false;
		}
	}
	
	/**
	 * Get all users (for admin)
	 *
	 * @param int $limit Limit
	 * @param int $offset Offset
	 * @param string $search Search term
	 * @return array
	 */
	public function get_all_users( $limit = 100, $offset = 0, $search = '' ) {
		$where = '';
		$params = array();
		
		if ( ! empty( $search ) ) {
			$where = " WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s";
			$search_term = '%' . $this->wpdb->esc_like( $search ) . '%';
			$params = array( $search_term, $search_term, $search_term, $limit, $offset );
		} else {
			$params = array( $limit, $offset );
		}
		
		$sql = "SELECT * FROM {$this->users_table} 
				{$where}
				ORDER BY created_at DESC 
				LIMIT %d OFFSET %d";
		
		return $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, $params ),
			ARRAY_A
		);
	}
	
	/**
	 * Get users count
	 *
	 * @param string $search Search term
	 * @return int
	 */
	public function get_users_count( $search = '' ) {
		if ( ! empty( $search ) ) {
			$search_term = '%' . $this->wpdb->esc_like( $search ) . '%';
			return (int) $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->users_table} 
					WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s",
					$search_term,
					$search_term,
					$search_term
				)
			);
		}
		
		return (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->users_table}" );
	}
	
	/**
	 * Get analytics data
	 *
	 * @param int $days Number of days to analyze
	 * @return array
	 */
	public function get_analytics( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		
		$total_messages = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->messages_table} 
				WHERE created_at >= %s",
				$start_date
			)
		);
		
		$total_users = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->users_table} 
				WHERE created_at >= %s",
				$start_date
			)
		);
		
		$total_sessions = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->sessions_table} 
				WHERE started_at >= %s",
				$start_date
			)
		);
		
		$avg_messages_per_session = $total_sessions > 0 ? round( $total_messages / $total_sessions, 1 ) : 0;
		
		// Daily message counts
		$daily_stats = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DATE(created_at) as date, COUNT(*) as count 
				FROM {$this->messages_table} 
				WHERE created_at >= %s 
				GROUP BY DATE(created_at) 
				ORDER BY date ASC",
				$start_date
			),
			ARRAY_A
		);
		
		return array(
			'total_messages'          => $total_messages,
			'total_users'             => $total_users,
			'total_sessions'          => $total_sessions,
			'avg_messages_per_session' => $avg_messages_per_session,
			'daily_stats'             => $daily_stats,
		);
	}
	
	/**
	 * Get all sessions with user info
	 *
	 * @param int $limit Limit
	 * @param int $offset Offset
	 * @param string $user_id Filter by user ID
	 * @return array
	 */
	public function get_sessions( $limit = 50, $offset = 0, $user_id = '' ) {
		$where = '';
		$params = array();
		
		if ( ! empty( $user_id ) ) {
			$where = " WHERE s.user_id = %d";
			$params = array( $user_id, $limit, $offset );
		} else {
			$params = array( $limit, $offset );
		}
		
		$sql = "SELECT s.*, u.name, u.email 
				FROM {$this->sessions_table} s
				LEFT JOIN {$this->users_table} u ON s.user_id = u.id
				{$where}
				ORDER BY s.last_activity_at DESC 
				LIMIT %d OFFSET %d";
		
		return $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, $params ),
			ARRAY_A
		);
	}
	
	/**
	 * Get messages by session
	 *
	 * @param string $session_id Session ID
	 * @return array
	 */
	public function get_messages_by_session( $session_id ) {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT m.*, u.name 
				FROM {$this->messages_table} m
				LEFT JOIN {$this->users_table} u ON m.user_id = u.id
				WHERE m.session_id = %s 
				ORDER BY m.created_at ASC",
				$session_id
			),
			ARRAY_A
		);
	}
	
	/**
	 * Clean up old sessions
	 *
	 * @param int $days Sessions older than this will be deleted
	 * @return int Number of sessions deleted
	 */
	public function cleanup_old_sessions( $days = 90 ) {
		$cutoff_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		
		// Get sessions to delete
		$sessions_to_delete = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->sessions_table} WHERE last_activity_at < %s",
				$cutoff_date
			)
		);
		
		if ( empty( $sessions_to_delete ) ) {
			return 0;
		}
		
		// Delete messages for these sessions
		$placeholders = implode( ', ', array_fill( 0, count( $sessions_to_delete ), '%s' ) );
		$this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM {$this->messages_table} WHERE session_id IN ({$placeholders})",
				$sessions_to_delete
			)
		);
		
		// Delete sessions
		$result = $this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM {$this->sessions_table} WHERE last_activity_at < %s",
				$cutoff_date
			)
		);
		
		return $result;
	}
	
	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				return sanitize_text_field( $_SERVER[ $key ] );
			}
		}
		
		return '0.0.0.0';
	}
}


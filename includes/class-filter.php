<?php
/**
 * Word filtering class
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_Filter {
	
	/**
	 * @var array Forbidden words
	 */
	private $forbidden_words = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		$words_string = isset( $settings['forbidden_words'] ) ? $settings['forbidden_words'] : '';
		
		if ( ! empty( $words_string ) ) {
			$this->forbidden_words = array_map( 'trim', explode( ',', strtolower( $words_string ) ) );
			$this->forbidden_words = array_filter( $this->forbidden_words ); // Remove empty values
		}
	}
	
	/**
	 * Check if message is allowed
	 *
	 * @param string $message Message to check
	 * @return bool True if allowed, false otherwise
	 */
	public function is_allowed( $message ) {
		if ( empty( $this->forbidden_words ) ) {
			return true;
		}
		
		$message_lower = strtolower( $message );
		
		foreach ( $this->forbidden_words as $word ) {
			if ( empty( $word ) ) {
				continue;
			}
			
			if ( strpos( $message_lower, $word ) !== false ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Filter/replace forbidden words with asterisks
	 *
	 * @param string $message Message to filter
	 * @return string Filtered message
	 */
	public function filter_message( $message ) {
		if ( empty( $this->forbidden_words ) ) {
			return $message;
		}
		
		foreach ( $this->forbidden_words as $word ) {
			if ( empty( $word ) ) {
				continue;
			}
			
			$replacement = str_repeat( '*', mb_strlen( $word ) );
			$message = str_ireplace( $word, $replacement, $message );
		}
		
		return $message;
	}
	
	/**
	 * Get list of forbidden words
	 *
	 * @return array
	 */
	public function get_forbidden_words() {
		return $this->forbidden_words;
	}
	
	/**
	 * Add word to forbidden list
	 *
	 * @param string $word Word to add
	 * @return bool
	 */
	public function add_forbidden_word( $word ) {
		$word = trim( strtolower( $word ) );
		
		if ( empty( $word ) || in_array( $word, $this->forbidden_words, true ) ) {
			return false;
		}
		
		$this->forbidden_words[] = $word;
		$this->save_forbidden_words();
		
		return true;
	}
	
	/**
	 * Remove word from forbidden list
	 *
	 * @param string $word Word to remove
	 * @return bool
	 */
	public function remove_forbidden_word( $word ) {
		$word = trim( strtolower( $word ) );
		$key = array_search( $word, $this->forbidden_words, true );
		
		if ( $key === false ) {
			return false;
		}
		
		unset( $this->forbidden_words[ $key ] );
		$this->save_forbidden_words();
		
		return true;
	}
	
	/**
	 * Save forbidden words to settings
	 */
	private function save_forbidden_words() {
		$settings = get_option( 'wp_ai_assistant_settings', array() );
		$settings['forbidden_words'] = implode( ',', $this->forbidden_words );
		update_option( 'wp_ai_assistant_settings', $settings );
	}
}


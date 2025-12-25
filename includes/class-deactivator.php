<?php
/**
 * Plugin deactivation class
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_AI_Deactivator {
	
	/**
	 * Deactivate plugin
	 */
	public static function deactivate() {
		// Remove scheduled hooks
		wp_clear_scheduled_hook( 'wp_ai_assistant_cleanup_sessions' );
		
		// Clear transients
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_ai_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_ai_%'" );
		
		// Flush rewrite rules
		flush_rewrite_rules();
		
		// Note: We don't delete user data on deactivation, only on uninstall
	}
}


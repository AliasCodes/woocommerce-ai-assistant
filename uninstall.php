<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WP_AI_Assistant
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Define table names
$users_table    = $wpdb->prefix . 'ai_chat_users';
$messages_table = $wpdb->prefix . 'ai_chat_messages';
$sessions_table = $wpdb->prefix . 'ai_chat_sessions';

// Drop tables
$wpdb->query( "DROP TABLE IF EXISTS {$messages_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$sessions_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$users_table}" );

// Delete options
delete_option( 'wp_ai_assistant_settings' );
delete_option( 'wp_ai_assistant_version' );
delete_option( 'wp_ai_assistant_activation_time' );

// Clear transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_ai_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_ai_%'" );

// Clear scheduled hooks
wp_clear_scheduled_hook( 'wp_ai_assistant_cleanup_sessions' );


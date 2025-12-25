<?php
/**
 * Plugin Name: WooCommerce AI Assistant
 * Plugin URI: https://webtamino.com
 * Description: AI-powered chat assistant for your WordPress website with bilingual support (English/Persian)
 * Version: 1.0.0
 * Author: AliasCodes
 * Author URI: https://github.com/AliasCodes
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-ai-assistant
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'WP_AI_ASSISTANT_VERSION', '1.0.0' );
define( 'WP_AI_ASSISTANT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_AI_ASSISTANT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_AI_ASSISTANT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Activation hook
 */
register_activation_hook( __FILE__, 'wp_ai_assistant_activate' );
function wp_ai_assistant_activate() {
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-activator.php';
	WP_AI_Activator::activate();
}

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, 'wp_ai_assistant_deactivate' );
function wp_ai_assistant_deactivate() {
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-deactivator.php';
	WP_AI_Deactivator::deactivate();
}

/**
 * Include core classes
 */
function wp_ai_assistant_load_classes() {
	// Core classes
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-db.php';
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-api-client.php';
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-session.php';
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'includes/class-filter.php';
	
	// Admin and public classes
	if ( is_admin() ) {
		require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'admin/class-admin.php';
	}
	require_once WP_AI_ASSISTANT_PLUGIN_DIR . 'public/class-public.php';
}

/**
 * Initialize plugin
 */
add_action( 'plugins_loaded', 'wp_ai_assistant_init' );
function wp_ai_assistant_init() {
	// Load text domain
	load_plugin_textdomain(
		'wp-ai-assistant',
		false,
		dirname( WP_AI_ASSISTANT_PLUGIN_BASENAME ) . '/languages'
	);
	
	// Load classes
	wp_ai_assistant_load_classes();
	
	// Initialize admin
	if ( is_admin() ) {
		new WP_AI_Admin();
	}
	
	// Initialize public
	new WP_AI_Public();
}

/**
 * Add settings link on plugin page
 */
add_filter( 'plugin_action_links_' . WP_AI_ASSISTANT_PLUGIN_BASENAME, 'wp_ai_assistant_action_links' );
function wp_ai_assistant_action_links( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=wp-ai-assistant-settings' ) . '">' . __( 'Settings', 'wp-ai-assistant' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}


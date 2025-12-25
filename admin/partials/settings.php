<?php
/**
 * Admin Settings Page
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'wp_ai_assistant_settings', array() );
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<?php settings_errors( 'wp_ai_assistant_messages' ); ?>
	
	<form method="post" action="options.php">
		<?php
		settings_fields( 'wp_ai_assistant_settings' );
		?>
		
		<table class="form-table" role="presentation">
			<tbody>
				<!-- API Configuration Section -->
				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'API Configuration', 'wp-ai-assistant' ); ?></h2>
					</th>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="api_url"><?php esc_html_e( 'API URL', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="url" id="api_url" name="wp_ai_assistant_settings[api_url]" value="<?php echo esc_attr( isset( $settings['api_url'] ) ? $settings['api_url'] : 'http://localhost:3000' ); ?>" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'Backend API URL (e.g., http://localhost:3000 or https://api.yourdomain.com)', 'wp-ai-assistant' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="api_key"><?php esc_html_e( 'API Key', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="text" id="api_key" name="wp_ai_assistant_settings[api_key]" value="<?php echo esc_attr( isset( $settings['api_key'] ) ? $settings['api_key'] : '' ); ?>" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'Your API access key from the WordPress Assistant platform', 'wp-ai-assistant' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="project_id"><?php esc_html_e( 'Project ID', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="text" id="project_id" name="wp_ai_assistant_settings[project_id]" value="<?php echo esc_attr( isset( $settings['project_id'] ) ? $settings['project_id'] : '' ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Your project ID (optional)', 'wp-ai-assistant' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"></th>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
							<input type="hidden" name="action" value="wp_ai_test_connection">
							<?php wp_nonce_field( 'wp_ai_test_connection' ); ?>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Test Connection', 'wp-ai-assistant' ); ?></button>
						</form>
					</td>
				</tr>
				
				<!-- Widget Settings Section -->
				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'Widget Settings', 'wp-ai-assistant' ); ?></h2>
					</th>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="widget_enabled"><?php esc_html_e( 'Enable Chat Widget', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<label for="widget_enabled">
							<input type="checkbox" id="widget_enabled" name="wp_ai_assistant_settings[widget_enabled]" value="1" <?php checked( isset( $settings['widget_enabled'] ) ? $settings['widget_enabled'] : true, true ); ?>>
							<?php esc_html_e( 'Show chat widget on website', 'wp-ai-assistant' ); ?>
						</label>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="widget_position"><?php esc_html_e( 'Widget Position', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<select id="widget_position" name="wp_ai_assistant_settings[widget_position]">
							<option value="bottom-right" <?php selected( isset( $settings['widget_position'] ) ? $settings['widget_position'] : 'bottom-right', 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'wp-ai-assistant' ); ?></option>
							<option value="bottom-left" <?php selected( isset( $settings['widget_position'] ) ? $settings['widget_position'] : '', 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'wp-ai-assistant' ); ?></option>
							<option value="top-right" <?php selected( isset( $settings['widget_position'] ) ? $settings['widget_position'] : '', 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'wp-ai-assistant' ); ?></option>
							<option value="top-left" <?php selected( isset( $settings['widget_position'] ) ? $settings['widget_position'] : '', 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'wp-ai-assistant' ); ?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="primary_color"><?php esc_html_e( 'Primary Color', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="color" id="primary_color" name="wp_ai_assistant_settings[primary_color]" value="<?php echo esc_attr( isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#667eea' ); ?>">
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="greeting_message"><?php esc_html_e( 'Greeting Message (English)', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="text" id="greeting_message" name="wp_ai_assistant_settings[greeting_message]" value="<?php echo esc_attr( isset( $settings['greeting_message'] ) ? $settings['greeting_message'] : 'Hi! How can I help you today?' ); ?>" class="regular-text">
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="greeting_message_fa"><?php esc_html_e( 'Greeting Message (Persian)', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="text" id="greeting_message_fa" name="wp_ai_assistant_settings[greeting_message_fa]" value="<?php echo esc_attr( isset( $settings['greeting_message_fa'] ) ? $settings['greeting_message_fa'] : 'سلام! چطور می‌تونم کمکتون کنم؟' ); ?>" class="regular-text" dir="rtl">
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="placeholder_text"><?php esc_html_e( 'Placeholder Text (English)', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="text" id="placeholder_text" name="wp_ai_assistant_settings[placeholder_text]" value="<?php echo esc_attr( isset( $settings['placeholder_text'] ) ? $settings['placeholder_text'] : 'Type your message...' ); ?>" class="regular-text">
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="placeholder_text_fa"><?php esc_html_e( 'Placeholder Text (Persian)', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="text" id="placeholder_text_fa" name="wp_ai_assistant_settings[placeholder_text_fa]" value="<?php echo esc_attr( isset( $settings['placeholder_text_fa'] ) ? $settings['placeholder_text_fa'] : 'پیام خود را بنویسید...' ); ?>" class="regular-text" dir="rtl">
					</td>
				</tr>
				
				<!-- Content Moderation Section -->
				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'Content Moderation', 'wp-ai-assistant' ); ?></h2>
					</th>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="forbidden_words"><?php esc_html_e( 'Forbidden Words', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<textarea id="forbidden_words" name="wp_ai_assistant_settings[forbidden_words]" rows="3" class="large-text"><?php echo esc_textarea( isset( $settings['forbidden_words'] ) ? $settings['forbidden_words'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Comma-separated list of words to block (e.g., spam, badword)', 'wp-ai-assistant' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="rate_limit"><?php esc_html_e( 'Rate Limit', 'wp-ai-assistant' ); ?></label>
					</th>
					<td>
						<input type="number" id="rate_limit" name="wp_ai_assistant_settings[rate_limit]" value="<?php echo esc_attr( isset( $settings['rate_limit'] ) ? $settings['rate_limit'] : 60 ); ?>" min="1" max="1000" class="small-text">
						<span><?php esc_html_e( 'messages per hour per user', 'wp-ai-assistant' ); ?></span>
					</td>
				</tr>
				
				<!-- User Data Collection Section -->
				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'User Data Collection', 'wp-ai-assistant' ); ?></h2>
					</th>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Collect Information', 'wp-ai-assistant' ); ?>
					</th>
					<td>
						<label for="collect_email">
							<input type="checkbox" id="collect_email" name="wp_ai_assistant_settings[collect_email]" value="1" <?php checked( isset( $settings['collect_email'] ) ? $settings['collect_email'] : true, true ); ?>>
							<?php esc_html_e( 'Email Address', 'wp-ai-assistant' ); ?>
						</label>
						<br>
						<label for="collect_phone">
							<input type="checkbox" id="collect_phone" name="wp_ai_assistant_settings[collect_phone]" value="1" <?php checked( isset( $settings['collect_phone'] ) ? $settings['collect_phone'] : true, true ); ?>>
							<?php esc_html_e( 'Phone Number', 'wp-ai-assistant' ); ?>
						</label>
					</td>
				</tr>
				
				<!-- Display Options Section -->
				<tr>
					<th colspan="2">
						<h2><?php esc_html_e( 'Display Options', 'wp-ai-assistant' ); ?></h2>
					</th>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Show Features', 'wp-ai-assistant' ); ?>
					</th>
					<td>
						<label for="show_timestamp">
							<input type="checkbox" id="show_timestamp" name="wp_ai_assistant_settings[show_timestamp]" value="1" <?php checked( isset( $settings['show_timestamp'] ) ? $settings['show_timestamp'] : true, true ); ?>>
							<?php esc_html_e( 'Message Timestamps', 'wp-ai-assistant' ); ?>
						</label>
						<br>
						<label for="enable_emojis">
							<input type="checkbox" id="enable_emojis" name="wp_ai_assistant_settings[enable_emojis]" value="1" <?php checked( isset( $settings['enable_emojis'] ) ? $settings['enable_emojis'] : false, true ); ?>>
							<?php esc_html_e( 'Emoji Picker', 'wp-ai-assistant' ); ?>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		
		<?php submit_button( __( 'Save Settings', 'wp-ai-assistant' ) ); ?>
	</form>
</div>


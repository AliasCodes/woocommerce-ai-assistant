<?php
/**
 * Admin Analytics Page
 *
 * @package WP_AI_Assistant
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db = new WP_AI_DB();

// Get time period filter
$period = isset( $_GET['period'] ) ? sanitize_text_field( $_GET['period'] ) : '30';
$period_days = intval( $period );

// Get analytics data
$analytics = $db->get_analytics( $period_days );

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<hr class="wp-header-end">
	
	<!-- Period Filter -->
	<div style="margin: 20px 0;">
		<a href="<?php echo esc_url( add_query_arg( 'period', '7' ) ); ?>" class="button <?php echo $period === '7' ? 'button-primary' : ''; ?>">
			<?php esc_html_e( 'Last 7 Days', 'wp-ai-assistant' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'period', '30' ) ); ?>" class="button <?php echo $period === '30' ? 'button-primary' : ''; ?>">
			<?php esc_html_e( 'Last 30 Days', 'wp-ai-assistant' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'period', '90' ) ); ?>" class="button <?php echo $period === '90' ? 'button-primary' : ''; ?>">
			<?php esc_html_e( 'Last 90 Days', 'wp-ai-assistant' ); ?>
		</a>
	</div>
	
	<!-- Summary Cards -->
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
		
		<!-- Total Messages -->
		<div style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<div style="display: flex; align-items: center; gap: 15px;">
				<div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
					<span class="dashicons dashicons-format-chat" style="color: white; font-size: 24px; width: 24px; height: 24px;"></span>
				</div>
				<div>
					<div style="font-size: 28px; font-weight: bold; color: #1e293b;">
						<?php echo esc_html( number_format_i18n( $analytics['total_messages'] ) ); ?>
					</div>
					<div style="color: #64748b; font-size: 14px;">
						<?php esc_html_e( 'Total Messages', 'wp-ai-assistant' ); ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Total Users -->
		<div style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<div style="display: flex; align-items: center; gap: 15px;">
				<div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
					<span class="dashicons dashicons-groups" style="color: white; font-size: 24px; width: 24px; height: 24px;"></span>
				</div>
				<div>
					<div style="font-size: 28px; font-weight: bold; color: #1e293b;">
						<?php echo esc_html( number_format_i18n( $analytics['total_users'] ) ); ?>
					</div>
					<div style="color: #64748b; font-size: 14px;">
						<?php esc_html_e( 'Total Users', 'wp-ai-assistant' ); ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Total Sessions -->
		<div style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<div style="display: flex; align-items: center; gap: 15px;">
				<div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
					<span class="dashicons dashicons-admin-comments" style="color: white; font-size: 24px; width: 24px; height: 24px;"></span>
				</div>
				<div>
					<div style="font-size: 28px; font-weight: bold; color: #1e293b;">
						<?php echo esc_html( number_format_i18n( $analytics['total_sessions'] ) ); ?>
					</div>
					<div style="color: #64748b; font-size: 14px;">
						<?php esc_html_e( 'Chat Sessions', 'wp-ai-assistant' ); ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Average Messages -->
		<div style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<div style="display: flex; align-items: center; gap: 15px;">
				<div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
					<span class="dashicons dashicons-chart-bar" style="color: white; font-size: 24px; width: 24px; height: 24px;"></span>
				</div>
				<div>
					<div style="font-size: 28px; font-weight: bold; color: #1e293b;">
						<?php echo esc_html( number_format_i18n( $analytics['avg_messages_per_session'], 1 ) ); ?>
					</div>
					<div style="color: #64748b; font-size: 14px;">
						<?php esc_html_e( 'Avg. Messages/Session', 'wp-ai-assistant' ); ?>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	
	<!-- Daily Activity Chart -->
	<div style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
		<h2 style="margin-top: 0;"><?php esc_html_e( 'Daily Activity', 'wp-ai-assistant' ); ?></h2>
		<canvas id="activityChart" style="max-height: 300px;"></canvas>
	</div>
	
	<!-- Quick Links -->
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
		
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-ai-assistant-users' ) ); ?>" style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; text-decoration: none; color: inherit; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-groups" style="font-size: 24px; color: #667eea;"></span>
				<div>
					<div style="font-weight: 600; color: #1e293b;"><?php esc_html_e( 'View Users', 'wp-ai-assistant' ); ?></div>
					<div style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Manage chat users', 'wp-ai-assistant' ); ?></div>
				</div>
			</div>
		</a>
		
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-ai-assistant-chats' ) ); ?>" style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; text-decoration: none; color: inherit; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-format-chat" style="font-size: 24px; color: #10b981;"></span>
				<div>
					<div style="font-weight: 600; color: #1e293b;"><?php esc_html_e( 'Chat History', 'wp-ai-assistant' ); ?></div>
					<div style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'View conversations', 'wp-ai-assistant' ); ?></div>
				</div>
			</div>
		</a>
		
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-ai-assistant-settings' ) ); ?>" style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; text-decoration: none; color: inherit; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-admin-settings" style="font-size: 24px; color: #f59e0b;"></span>
				<div>
					<div style="font-weight: 600; color: #1e293b;"><?php esc_html_e( 'Settings', 'wp-ai-assistant' ); ?></div>
					<div style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Configure plugin', 'wp-ai-assistant' ); ?></div>
				</div>
			</div>
		</a>
		
	</div>
	
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	if (typeof Chart === 'undefined') {
		return;
	}
	
	const ctx = document.getElementById('activityChart');
	if (!ctx) {
		return;
	}
	
	const dailyStats = <?php echo wp_json_encode( $analytics['daily_stats'] ); ?>;
	
	const labels = dailyStats.map(stat => stat.date);
	const data = dailyStats.map(stat => parseInt(stat.count));
	
	new Chart(ctx, {
		type: 'line',
		data: {
			labels: labels,
			datasets: [{
				label: '<?php echo esc_js( __( 'Messages', 'wp-ai-assistant' ) ); ?>',
				data: data,
				borderColor: '#667eea',
				backgroundColor: 'rgba(102, 126, 234, 0.1)',
				fill: true,
				tension: 0.4
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: {
					display: false
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						precision: 0
					}
				}
			}
		}
	});
});
</script>


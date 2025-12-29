/**
 * Admin JavaScript
 *
 * @package WP_AI_Assistant
 */

(function($) {
	'use strict';
	
	$(document).ready(function() {
		
		// Color picker initialization (if available)
		if ($.fn.wpColorPicker) {
			$('input[type="color"]').wpColorPicker();
		}
		
		// Confirmation dialogs
		$('.wp-ai-confirm').on('click', function(e) {
			if (!confirm($(this).data('confirm'))) {
				e.preventDefault();
				return false;
			}
		});
		
		// AJAX test connection
		$('#wp-ai-test-connection-btn').on('click', function(e) {
			e.preventDefault();
			
			const $btn = $(this);
			const $result = $('#wp-ai-test-result');
			const originalText = $btn.text();
			
			$btn.prop('disabled', true).text('Testing...');
			$result.html('<span style="color: #999;">⏳ Testing connection...</span>');
			
			$.post(wpAiAdminConfig.ajaxUrl, {
				action: 'wp_ai_test_connection',
				nonce: wpAiAdminConfig.nonce
			}, function(response) {
				if (response.success) {
					$result.html('<span style="color: #46b450;">✅ ' + response.data.message + '</span>');
				} else {
					$result.html('<span style="color: #dc3232;">❌ ' + response.data.message + '</span>');
				}
			}).fail(function(xhr) {
				let errorMsg = 'Connection failed. Please check your settings.';
				if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					errorMsg = xhr.responseJSON.data.message;
				}
				$result.html('<span style="color: #dc3232;">❌ ' + errorMsg + '</span>');
			}).always(function() {
				$btn.prop('disabled', false).text(originalText);
			});
		});
		
		// Auto-save indicator
		let saveTimeout;
		$('.wp-ai-auto-save input, .wp-ai-auto-save textarea, .wp-ai-auto-save select').on('change', function() {
			clearTimeout(saveTimeout);
			$('.wp-ai-save-indicator').text('Saving...').show();
			
			saveTimeout = setTimeout(function() {
				$('.wp-ai-save-indicator').text('Saved!');
				setTimeout(function() {
					$('.wp-ai-save-indicator').fadeOut();
				}, 2000);
			}, 1000);
		});
		
		// Copy to clipboard
		$('.wp-ai-copy').on('click', function(e) {
			e.preventDefault();
			
			const text = $(this).data('copy');
			const $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(text).select();
			document.execCommand('copy');
			$temp.remove();
			
			const $btn = $(this);
			const originalText = $btn.text();
			$btn.text('Copied!');
			
			setTimeout(function() {
				$btn.text(originalText);
			}, 2000);
		});
		
		// Search filter
		let searchTimeout;
		$('.wp-ai-search-filter').on('keyup', function() {
			clearTimeout(searchTimeout);
			const searchTerm = $(this).val().toLowerCase();
			
			searchTimeout = setTimeout(function() {
				$('.wp-ai-searchable').each(function() {
					const text = $(this).text().toLowerCase();
					$(this).toggle(text.indexOf(searchTerm) > -1);
				});
			}, 300);
		});
		
	});
	
})(jQuery);


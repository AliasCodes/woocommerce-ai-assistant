/**
 * Chat Widget JavaScript
 *
 * @package WP_AI_Assistant
 */

(function() {
	'use strict';
	
	const WPAIChat = {
		// Configuration
		config: {
			apiUrl: '',
			apiKey: '',
			projectId: '',
			ajaxUrl: '',
			nonce: '',
		},
		
		// State
		state: {
			sessionId: null,
			userId: null,
			isOpen: false,
			isTyping: false,
			messageQueue: [],
		},
		
		// DOM Elements
		elements: {},
		
		/**
		 * Initialize
		 */
		init: function() {
			// Get config from localized script
			if (typeof wpAiChatConfig !== 'undefined') {
				this.config = wpAiChatConfig;
			}
			
			this.cacheDOMElements();
			this.bindEvents();
			this.checkUserIdentification();
		},
		
		/**
		 * Cache DOM elements
		 */
		cacheDOMElements: function() {
			this.elements = {
				widget: document.getElementById('wp-ai-chat-widget'),
				bubble: document.getElementById('wp-ai-chat-bubble'),
				window: document.getElementById('wp-ai-chat-window'),
				messages: document.getElementById('wp-ai-messages'),
				input: document.getElementById('wp-ai-message-input'),
				sendBtn: document.getElementById('wp-ai-send-btn'),
				userForm: document.getElementById('wp-ai-user-form'),
				userFormSubmit: document.getElementById('wp-ai-submit-user-form'),
				typing: document.getElementById('wp-ai-typing'),
				minimize: document.getElementById('wp-ai-minimize'),
				close: document.getElementById('wp-ai-close'),
			};
		},
		
		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			// Toggle chat window
			if (this.elements.bubble) {
				this.elements.bubble.addEventListener('click', this.toggleChat.bind(this));
			}
			
			if (this.elements.minimize) {
				this.elements.minimize.addEventListener('click', this.toggleChat.bind(this));
			}
			
			if (this.elements.close) {
				this.elements.close.addEventListener('click', this.closeChat.bind(this));
			}
			
			// Send message
			if (this.elements.sendBtn) {
				this.elements.sendBtn.addEventListener('click', this.sendMessage.bind(this));
			}
			
			if (this.elements.input) {
				this.elements.input.addEventListener('keypress', function(e) {
					if (e.key === 'Enter' && !e.shiftKey) {
						e.preventDefault();
						this.sendMessage();
					}
				}.bind(this));
				
				// Auto-resize textarea
				this.elements.input.addEventListener('input', this.autoResizeTextarea.bind(this));
			}
			
			// User form submission
			if (this.elements.userFormSubmit) {
				this.elements.userFormSubmit.addEventListener('submit', function(e) {
					e.preventDefault();
					this.submitUserForm();
				}.bind(this));
			}
		},
		
		/**
		 * Check if user is identified
		 */
		checkUserIdentification: function() {
			const cookieId = this.getCookie('wp_ai_user_id');
			
			if (!cookieId) {
				// Show user form
				if (this.elements.userForm) {
					this.elements.userForm.style.display = 'flex';
				}
				if (this.elements.messages) {
					this.elements.messages.style.display = 'none';
				}
				if (this.elements.input && this.elements.input.parentElement) {
					this.elements.input.parentElement.style.display = 'none';
				}
			} else {
				this.state.userId = cookieId;
				this.createSession();
				this.loadChatHistory();
			}
		},
		
		/**
		 * Submit user identification form
		 */
		submitUserForm: function() {
			const formData = new FormData(this.elements.userFormSubmit);
			const userData = {
				name: formData.get('name'),
				email: formData.get('email'),
				phone: formData.get('phone'),
			};
			
			// Disable submit button
			const submitBtn = this.elements.userFormSubmit.querySelector('button[type="submit"]');
			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.textContent = submitBtn.textContent + '...';
			}
			
			fetch(this.config.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wp_ai_save_user',
					nonce: this.config.nonce,
					name: userData.name,
					email: userData.email || '',
					phone: userData.phone || '',
				}),
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					this.state.userId = data.data.cookie_id;
					this.setCookie('wp_ai_user_id', data.data.cookie_id, 365);
					
					// Hide form, show chat
					if (this.elements.userForm) {
						this.elements.userForm.style.display = 'none';
					}
					if (this.elements.messages) {
						this.elements.messages.style.display = 'flex';
					}
					if (this.elements.input && this.elements.input.parentElement) {
						this.elements.input.parentElement.style.display = 'flex';
					}
					
					this.createSession();
				} else {
					alert(data.data.message || 'Error saving your information. Please try again.');
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = submitBtn.textContent.replace('...', '');
					}
				}
			})
			.catch(error => {
				console.error('Error submitting user form:', error);
				alert('Connection error. Please try again.');
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = submitBtn.textContent.replace('...', '');
				}
			});
		},
		
		/**
		 * Create session
		 */
		createSession: function() {
			this.state.sessionId = this.generateUUID();
		},
		
		/**
		 * Toggle chat window
		 */
		toggleChat: function() {
			this.state.isOpen = !this.state.isOpen;
			
			if (this.state.isOpen) {
				if (this.elements.window) {
					this.elements.window.style.display = 'flex';
				}
				if (this.elements.bubble) {
					this.elements.bubble.style.display = 'none';
				}
				if (this.elements.input) {
					this.elements.input.focus();
				}
				this.scrollToBottom();
			} else {
				if (this.elements.window) {
					this.elements.window.style.display = 'none';
				}
				if (this.elements.bubble) {
					this.elements.bubble.style.display = 'flex';
				}
			}
		},
		
		/**
		 * Close chat
		 */
		closeChat: function() {
			this.state.isOpen = false;
			if (this.elements.window) {
				this.elements.window.style.display = 'none';
			}
			if (this.elements.bubble) {
				this.elements.bubble.style.display = 'flex';
			}
		},
		
		/**
		 * Send message
		 */
		sendMessage: function() {
			if (!this.elements.input) return;
			
			const message = this.elements.input.value.trim();
			
			if (!message) return;
			
			// Clear input
			this.elements.input.value = '';
			this.autoResizeTextarea();
			
			// Add user message to UI
			this.addMessage('user', message);
			
			// Show typing indicator
			this.showTypingIndicator();
			
			// Disable send button
			if (this.elements.sendBtn) {
				this.elements.sendBtn.disabled = true;
			}
			
			// Send to backend
			this.callBackendAPI(message)
				.then(response => {
					this.hideTypingIndicator();
					
					if (response.success) {
						this.addMessage('assistant', response.data.response);
						
						// Save to database
						this.saveMessage('user', message);
						this.saveMessage('assistant', response.data.response);
					} else {
						this.addMessage('assistant', response.data.message || 'Sorry, I encountered an error. Please try again.');
					}
				})
				.catch(error => {
					console.error('Error sending message:', error);
					this.hideTypingIndicator();
					this.addMessage('assistant', 'Connection error. Please check your internet and try again.');
				})
				.finally(() => {
					if (this.elements.sendBtn) {
						this.elements.sendBtn.disabled = false;
					}
				});
		},
		
		/**
		 * Call backend API
		 */
		callBackendAPI: function(message) {
			return fetch(this.config.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wp_ai_send_message',
					nonce: this.config.nonce,
					message: message,
					session_id: this.state.sessionId,
					user_id: this.state.userId,
				}),
			})
			.then(response => response.json());
		},
		
		/**
		 * Add message to UI
		 */
		addMessage: function(role, content) {
			if (!this.elements.messages) return;
			
			const messageDiv = document.createElement('div');
			messageDiv.className = 'wp-ai-message wp-ai-' + role + '-message';
			
			const avatar = document.createElement('div');
			avatar.className = 'wp-ai-message-avatar';
			avatar.textContent = role === 'user' ? 'You' : 'AI';
			
			const contentDiv = document.createElement('div');
			contentDiv.className = 'wp-ai-message-content';
			
			const paragraph = document.createElement('p');
			paragraph.textContent = content;
			
			const time = document.createElement('span');
			time.className = 'wp-ai-message-time';
			time.textContent = 'Just now';
			
			contentDiv.appendChild(paragraph);
			contentDiv.appendChild(time);
			
			messageDiv.appendChild(avatar);
			messageDiv.appendChild(contentDiv);
			
			this.elements.messages.appendChild(messageDiv);
			this.scrollToBottom();
		},
		
		/**
		 * Save message to database
		 */
		saveMessage: function(role, content) {
			fetch(this.config.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wp_ai_save_message',
					nonce: this.config.nonce,
					session_id: this.state.sessionId,
					user_id: this.state.userId,
					role: role,
					message: content,
				}),
			}).catch(error => {
				console.error('Error saving message:', error);
			});
		},
		
		/**
		 * Load chat history
		 */
		loadChatHistory: function() {
			if (!this.state.sessionId) return;
			
			fetch(this.config.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wp_ai_get_history',
					nonce: this.config.nonce,
					session_id: this.state.sessionId,
				}),
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data.messages && data.data.messages.length > 0) {
					// Clear existing messages (except greeting)
					if (this.elements.messages) {
						this.elements.messages.innerHTML = '';
					}
					
					// Add historical messages
					data.data.messages.forEach(msg => {
						this.addMessage(msg.role, msg.message);
					});
				}
			})
			.catch(error => {
				console.error('Error loading chat history:', error);
			});
		},
		
		/**
		 * Show typing indicator
		 */
		showTypingIndicator: function() {
			this.state.isTyping = true;
			if (this.elements.typing) {
				this.elements.typing.style.display = 'flex';
			}
			this.scrollToBottom();
		},
		
		/**
		 * Hide typing indicator
		 */
		hideTypingIndicator: function() {
			this.state.isTyping = false;
			if (this.elements.typing) {
				this.elements.typing.style.display = 'none';
			}
		},
		
		/**
		 * Auto-resize textarea
		 */
		autoResizeTextarea: function() {
			if (!this.elements.input) return;
			
			this.elements.input.style.height = 'auto';
			this.elements.input.style.height = this.elements.input.scrollHeight + 'px';
		},
		
		/**
		 * Scroll to bottom
		 */
		scrollToBottom: function() {
			if (!this.elements.messages) return;
			
			setTimeout(() => {
				this.elements.messages.scrollTop = this.elements.messages.scrollHeight;
			}, 100);
		},
		
		/**
		 * Generate UUID
		 */
		generateUUID: function() {
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
				const r = Math.random() * 16 | 0;
				const v = c === 'x' ? r : (r & 0x3 | 0x8);
				return v.toString(16);
			});
		},
		
		/**
		 * Set cookie
		 */
		setCookie: function(name, value, days) {
			const expires = new Date(Date.now() + days * 864e5).toUTCString();
			document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
		},
		
		/**
		 * Get cookie
		 */
		getCookie: function(name) {
			return document.cookie.split('; ').reduce((r, v) => {
				const parts = v.split('=');
				return parts[0] === name ? decodeURIComponent(parts[1]) : r;
			}, '');
		},
	};
	
	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			WPAIChat.init();
		});
	} else {
		WPAIChat.init();
	}
	
})();


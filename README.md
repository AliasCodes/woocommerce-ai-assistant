# WooCommerce AI Assistant

An AI-powered chat assistant plugin for WordPress with bilingual support (English/Persian).

## Description

WooCommerce AI Assistant brings the power of artificial intelligence to your WordPress website, providing an intelligent chat assistant that can help your visitors with product inquiries, order placement, and general support. The plugin features a beautiful, responsive chat widget that works seamlessly with WooCommerce and all major themes.

## Features

- 🤖 **AI-Powered Conversations** - Natural language understanding and intelligent responses
- 🌍 **Bilingual Support** - Full English and Persian/Farsi support with RTL layout
- 🛒 **WooCommerce Integration** - Product search, recommendations, and order creation
- 💬 **Modern Chat Widget** - Beautiful, responsive design that works on all devices
- 📊 **Analytics Dashboard** - Track conversations, users, and engagement metrics
- 🎨 **Fully Customizable** - Colors, position, greeting messages, and more
- 🔒 **Secure** - API key authentication, rate limiting, and input validation
- ⚡ **Fast & Lightweight** - Optimized performance with minimal page load impact
- 🔧 **Elementor Compatible** - Works seamlessly with Elementor and major page builders

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- WooCommerce 5.0+ (optional but recommended)
- Active subscription to WordPress Assistant platform

## Installation

### From WordPress Admin

1. Download the `wp-ai-assistant.zip` file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Extract the zip file
2. Upload the `wp-ai-assistant` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

### After Installation

1. Go to **AI Assistant → Settings**
2. Enter your API Key and API URL
3. Configure widget settings (position, colors, greeting message)
4. Click "Save Settings"
5. The chat widget will automatically appear on your website

## Configuration

### API Settings

- **API URL**: Your backend API URL (`https://api.webtamino.com`)
- **API Key**: Your access key from the WordPress Assistant platform
- **Project ID**: Optional project identifier

### Widget Settings

- **Enable/Disable**: Toggle chat widget visibility
- **Position**: Bottom-right, Bottom-left, Top-right, Top-left
- **Primary Color**: Customize widget color scheme
- **Greeting Message**: Custom welcome message (English & Persian)
- **Placeholder Text**: Input field placeholder (English & Persian)

### Content Moderation

- **Forbidden Words**: Comma-separated list of words to block
- **Rate Limit**: Maximum messages per hour per user (default: 60)

### User Data Collection

- **Collect Email**: Ask users for email address
- **Collect Phone**: Ask users for phone number

## Usage

### For Website Visitors

1. Click the floating chat bubble on the website
2. Enter your name (and optionally email/phone) on first visit
3. Start chatting with the AI assistant
4. Ask questions about products, services, or place orders
5. The AI will provide intelligent responses and assistance

### For Administrators

#### View Analytics

- Go to **AI Assistant → Analytics**
- See total messages, users, sessions
- View daily activity charts
- Track engagement metrics

#### Manage Users

- Go to **AI Assistant → Users**
- View all chat users
- Search and filter users
- Export user list to CSV

#### View Chat History

- Go to **AI Assistant → Chat History**
- Browse recent chat sessions
- Click on a session to view full conversation
- Monitor customer interactions

## Multilingual Support

The plugin fully supports both English and Persian/Farsi languages:

### Automatic Language Detection

- The plugin detects your WordPress language setting
- Persian language (`fa_IR`) enables RTL layout automatically
- All UI elements are translated

### Manual Language Switching

1. Go to WordPress **Settings → General**
2. Change **Site Language** to Persian (فارسی)
3. Save changes
4. The plugin interface will switch to Persian

## WooCommerce Integration

When WooCommerce is installed and active:

- AI can search products by name, category, or price
- Recommend products based on budget and features
- Help customers create orders directly through chat
- Provide product information and availability

## Customization

### CSS Customization

Add custom CSS to your theme to override default styles:

```css
/* Change widget position */
.wp-ai-chat-widget {
  bottom: 30px !important;
  right: 30px !important;
}

/* Customize colors */
.wp-ai-chat-bubble {
  background: linear-gradient(
    135deg,
    #your-color 0%,
    #your-color-dark 100%
  ) !important;
}
```

### PHP Hooks & Filters

```php
// Modify chat widget output
add_filter( 'wp_ai_widget_enabled', function( $enabled ) {
    // Disable on specific pages
    if ( is_page( 'checkout' ) ) {
        return false;
    }
    return $enabled;
});

// Customize greeting message dynamically
add_filter( 'wp_ai_greeting_message', function( $message ) {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        return "Hi {$user->display_name}! How can I help you today?";
    }
    return $message;
});
```

## Troubleshooting

### Chat widget not appearing

1. Check if widget is enabled in **Settings**
2. Clear browser cache and WordPress cache
3. Verify no JavaScript errors in browser console
4. Check theme compatibility

### API connection failed

1. Verify API URL is correct
2. Check API Key is valid
3. Use "Test Connection" button in Settings
4. Ensure server can reach the API URL

### Messages not saving

1. Check database tables were created
2. Verify database permissions
3. Look for errors in WordPress debug log
4. Ensure MySQL version is 5.7+

### Persian text not displaying correctly

1. Ensure WordPress locale is set to `fa_IR`
2. Check that .mo translation file exists
3. Clear cache and reload page
4. Verify UTF-8 encoding

## Performance

The plugin is highly optimized for performance:

- ✅ Total asset size: ~80KB (CSS + JS)
- ✅ Page load impact: <50ms
- ✅ Database queries: Optimized with indexes
- ✅ Caching: Transients for API responses
- ✅ CDN compatible: All assets can be cached

## Security

Security features built-in:

- 🔒 WordPress nonce verification on all AJAX requests
- 🔒 Input sanitization and output escaping
- 🔒 SQL injection prevention with prepared statements
- 🔒 XSS protection
- 🔒 CSRF protection
- 🔒 Rate limiting per user
- 🔒 API key encryption
- 🔒 Capability checks for admin functions

## Frequently Asked Questions

**Q: Do I need a WooCommerce store?**
A: Yes, the plugin works with any WordPress site. WooCommerce integration is a dependency.

**Q: Can I use my own AI model?**
A: The plugin connects to our backend API. Custom AI models are not supported in v1.0.

**Q: Is the chat data stored locally?**
A: Yes, all chat history is stored in your WordPress database.

**Q: Can I customize the widget appearance?**
A: Yes, through Settings and custom CSS.

**Q: Does it work on mobile devices?**
A: Yes, the widget is fully responsive and mobile-optimized.

**Q: Can I disable the widget on specific pages?**
A: Yes, use the `wp_ai_widget_enabled` filter hook.

**Q: How do I get an API key?**
A: Sign up at https://ai.webtamino.com to get your API key.

## Support

For support and documentation:

- **Website**: https://webtamino.com
- **Documentation**: https://webtamino.com/plugins/woocommerce-ai-assistant/docs
- **Support**: https://webtamino.com/support
- **Email**: support@webtamino.com

## Changelog

### 1.0.0 (2024-01-01)

- Initial release
- AI-powered chat widget
- Bilingual support (English/Persian)
- WooCommerce integration
- Admin panel with analytics
- User management
- Chat history
- Customizable settings
- Word filtering
- Rate limiting
- Elementor compatibility

## Credits

Developed by Webtamino(AliasCodes)

## License

GPL-2.0+

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

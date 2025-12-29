# Plugin Requirements & Checks

WP AI Assistant automatically checks for required dependencies and displays warnings if they are not met.

## Required Dependencies

### 1. WooCommerce (Required)

**Status**: ❌ Critical

**Why Required**: The plugin needs WooCommerce to:

- Search and recommend products
- Display product information
- Create orders through chat
- Access product catalog

**Error Message**:

```
⚠️ WooCommerce is not installed or activated!
WP AI Assistant requires WooCommerce to provide product recommendations and order creation.
```

**How to Fix**:

1. **Install WooCommerce**:

   - Go to **Plugins → Add New**
   - Search for "WooCommerce"
   - Click "Install Now" → "Activate"

2. **Or activate existing installation**:
   - Go to **Plugins**
   - Find "WooCommerce"
   - Click "Activate"

### 2. WordPress REST API (Required)

**Status**: ❌ Critical

**Why Required**: The plugin uses REST API to:

- Communicate with the backend
- Send/receive chat messages
- Sync data in real-time

**Error Message**:

```
⚠️ WordPress REST API is disabled!
WP AI Assistant requires the REST API to function properly.
```

**How to Fix**:

**Check if REST API is disabled**:

```bash
# Test REST API
curl https://yoursite.com/wp-json/

# Should return JSON, not an error
```

**Common causes**:

1. **Plugin blocking REST API**:

   - Disable security plugins temporarily
   - Check: iThemes Security, Wordfence, etc.

2. **Theme blocking REST API**:

   - Switch to default theme temporarily
   - Check theme's functions.php

3. **Custom code blocking**:

   ```php
   // Remove this from wp-config.php or functions.php
   add_filter('rest_enabled', '__return_false');
   ```

4. **Server configuration**:
   - Check .htaccess file
   - Ensure mod_rewrite is enabled

### 3. WooCommerce REST API (Recommended)

**Status**: ⚠️ Warning (Not Critical)

**Why Recommended**: Enhanced functionality for:

- Advanced product searches
- Order management
- Inventory checking
- API-based operations

**Warning Message**:

```
⚠️ WooCommerce REST API may not be properly configured!
For full functionality, ensure WooCommerce REST API is enabled.
```

**How to Fix**:

1. Go to **WooCommerce → Settings**
2. Click **Advanced** tab
3. Click **REST API** section
4. Verify API is accessible
5. Create API keys if needed (for advanced features)

## Automatic Checks

The plugin automatically checks these requirements on **ALL admin pages** of the plugin:

### Admin Pages Showing Notices:

- ✅ AI Assistant → Analytics
- ✅ AI Assistant → Users
- ✅ AI Assistant → Chat History
- ✅ AI Assistant → Settings

### When Checks Run:

- On page load of any plugin admin page
- After plugin activation
- After WooCommerce is activated/deactivated
- Checks are cached for performance

## Visual Examples

### Error Notice (WooCommerce Missing)

```
┌─────────────────────────────────────────────────────────────┐
│ ⚠️ WooCommerce is not installed or activated!              │
│                                                             │
│ WP AI Assistant requires WooCommerce to provide product    │
│ recommendations and order creation.                        │
│                                                             │
│ [Install WooCommerce] or [Activate from Plugins page]     │
└─────────────────────────────────────────────────────────────┘
```

### Error Notice (REST API Disabled)

```
┌─────────────────────────────────────────────────────────────┐
│ ⚠️ WordPress REST API is disabled!                         │
│                                                             │
│ WP AI Assistant requires the REST API to function          │
│ properly. Please enable it or check if any plugin/theme    │
│ is blocking it.                                             │
└─────────────────────────────────────────────────────────────┘
```

### Warning Notice (WooCommerce API)

```
┌─────────────────────────────────────────────────────────────┐
│ ⚠️ WooCommerce REST API may not be properly configured!   │
│                                                             │
│ For full functionality, ensure WooCommerce REST API is     │
│ enabled. [Check WooCommerce Settings]                      │
└─────────────────────────────────────────────────────────────┘
```

## Dismissible Notices

All requirement notices are **dismissible** but will:

- Reappear on next page load if issue persists
- Disappear automatically once requirement is met
- Show only on plugin admin pages (not entire WordPress admin)

## Technical Implementation

### Check Functions

```php
// Check if WooCommerce is active
private function is_woocommerce_active() {
    return class_exists('WooCommerce');
}

// Check if WordPress REST API is enabled
private function is_rest_api_enabled() {
    return apply_filters('rest_enabled', true) !== false;
}

// Check if WooCommerce REST API is enabled
private function is_woocommerce_rest_api_enabled() {
    $namespaces = rest_get_server()->get_namespaces();
    return in_array('wc/v3', $namespaces);
}
```

### Hook Integration

```php
// Admin notices hook
add_action('admin_notices', array($this, 'show_requirement_notices'));
```

## Troubleshooting

### Issue: Notice shows but WooCommerce is installed

**Solution**:

1. Verify WooCommerce is activated (not just installed)
2. Check for WooCommerce errors in debug.log
3. Try deactivating and reactivating WooCommerce

### Issue: REST API notice shows but API works

**Solution**:

1. Clear WordPress cache
2. Check for filter conflicts:
   ```php
   // Temporarily add to functions.php
   var_dump(apply_filters('rest_enabled', true));
   ```
3. Test REST API directly:
   ```bash
   curl -I https://yoursite.com/wp-json/
   # Should return 200 OK
   ```

### Issue: Notices don't disappear after fixing

**Solution**:

1. Clear browser cache (Ctrl+Shift+Del)
2. Clear WordPress object cache
3. Hard refresh page (Ctrl+F5)

## Disabling Checks (Not Recommended)

If you need to disable these checks temporarily:

```php
// Add to wp-config.php
define('WP_AI_SKIP_REQUIREMENT_CHECKS', true);
```

⚠️ **Warning**: Disabling checks may cause the plugin to malfunction if requirements are not met.

## Support

If you continue to see requirement notices after fixing the issues:

1. **Check WordPress debug log**:

   ```php
   // Enable in wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. **Test requirements manually**:

   - Visit: `/wp-json/wc/v3/products`
   - Should show products or auth error (not 404)

3. **Contact support**:
   - Email: support@wordpress-assistant.com
   - Include: WordPress version, WooCommerce version, error logs

## Benefits of Requirement Checks

✅ **Prevents Plugin Errors**: Catches issues before they cause problems
✅ **Saves Support Time**: Users fix issues before contacting support
✅ **Better UX**: Clear instructions on what's needed
✅ **Proactive Monitoring**: Alerts admins immediately when something breaks
✅ **Bilingual Support**: Notices show in user's language (English/Persian)

## Summary

| Requirement          | Status   | Impact                | Dismissible |
| -------------------- | -------- | --------------------- | ----------- |
| WooCommerce          | Critical | Plugin won't work     | Yes         |
| WordPress REST API   | Critical | Chat won't function   | Yes         |
| WooCommerce REST API | Warning  | Reduced functionality | Yes         |

All checks are automatically performed on plugin admin pages to ensure optimal functionality.

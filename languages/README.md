# Translation Files

This directory contains translation files for WP AI Assistant.

## Files

- `wp-ai-assistant.pot` - Translation template file
- `wp-ai-assistant-fa_IR.po` - Persian/Farsi translation
- `wp-ai-assistant-fa_IR.mo` - Compiled Persian/Farsi translation (binary)

## Compiling .po to .mo

To compile the Persian translation file to the binary .mo format:

### Using msgfmt (GNU gettext)

```bash
msgfmt -o wp-ai-assistant-fa_IR.mo wp-ai-assistant-fa_IR.po
```

### Using Poedit

1. Open `wp-ai-assistant-fa_IR.po` in Poedit
2. Click "File" → "Save"
3. Poedit will automatically generate the .mo file

### Using WP-CLI

```bash
wp i18n make-mo languages/
```

## Adding New Languages

1. Copy `wp-ai-assistant.pot` to `wp-ai-assistant-{locale}.po`
2. Translate all strings in the .po file
3. Compile to .mo format using one of the methods above
4. Place both .po and .mo files in this directory

## Supported Languages

- English (default)
- Persian/Farsi (fa_IR)

## Testing Translations

1. Install the language pack in WordPress
2. Change WordPress language to Persian (Settings → General → Site Language)
3. Clear any caching plugins
4. Visit your site to see the Persian translations

## RTL Support

RTL (Right-to-Left) support is automatically enabled for Persian and other RTL languages through the plugin's CSS.


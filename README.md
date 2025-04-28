# Every.org Nonprofit Profile WordPress Plugin

## Description

This WordPress plugin provides an enhanced Gutenberg block and a shortcode to easily display nonprofit profiles from Every.org on your website. It connects to the Every.org API to fetch nonprofit information and offers various display styles and customization options.

## Features

* **Gutenberg Block:** An intuitive block for searching and embedding nonprofit profiles directly within the WordPress editor.
* **Shortcode:** Use the `[nonprofit_profile]` shortcode for embedding profiles in classic editor or other areas.
* **Every.org API Integration:** Fetches up-to-date nonprofit data including name, description, logo, location, and tags.
* **Multiple Display Styles:** Choose from Standard, Card, Featured, and Minimal display templates.
* **Customizable:** Control description length, visibility of tags/description, button text, and button color.
* **Admin Settings:** Configure your Every.org API key and set default display options.
* **API Connection Test:** Verify your API key connection directly from the settings page.
* **REST API Endpoints:** Provides endpoints for searching nonprofits, browsing by cause, and fetching details.
* **Caching:** Caches API responses to improve performance and reduce API calls.
* **Frontend Enhancements:** Includes optional features like donation progress bars and tag tooltips (requires `frontend.js`).
* **TinyMCE Button:** Adds a button to the classic editor for easy shortcode generation.

## Requirements

* WordPress 5.0 or higher (for Gutenberg block support)
* PHP 7.0 or higher
* An Every.org Public API Key (Get one from [Every.org](https://every.org))

## Installation

1. Download the plugin files (or clone the repository).
2. Upload the `every-org-nonprofit-profile` folder to your `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to `Settings` > `Every.org API`.
5. Enter your Every.org Public API Key and save the settings.
6. Test the API connection using the button provided on the settings page.

## Usage

### Gutenberg Block

1. Edit a post or page using the WordPress block editor.
2. Click the '+' icon to add a new block.
3. Search for "Nonprofit Profile" or find it under the "Nonprofit Tools" category.
4. Use the search bar within the block placeholder to find nonprofits by name or cause.
5. Select a nonprofit from the search results.
6. Customize the appearance using the block settings in the Inspector Controls sidebar (styles, button text/color, content visibility).

### Shortcode

You can also embed profiles using the shortcode `[nonprofit_profile]`.

**Basic Usage:**

```shortcode
[nonprofit_profile id="everydotorg"]
```

**Available Attributes:**

* `id` (required): The ID (slug or EIN) of the nonprofit on Every.org.
* `style`: The display style (`standard`, `card`, `featured`, `minimal`). Defaults to the style set in plugin settings or `standard`.
* `cta_text`: Text for the donation button. Defaults to the text set in plugin settings or "Donate Now".
* `cta_color`: Hex color code for the donation button (e.g., `#007bff`).
* `show_description`: `true` or `false`. Whether to display the description. Defaults to `true`.
* `description_length`: Maximum number of characters for the description snippet. Defaults to `150`.
* `show_tags`: `true` or `false`. Whether to display the nonprofit tags. Defaults to `true`.
* `show_stats`: `true` or `false`. *(Currently used for potential future donation progress display)*. Defaults to `true`.

**Example with attributes:**

```shortcode
[nonprofit_profile id="doctorswithoutborders" style="card" cta_text="Support Now" cta_color="#ff5733" show_description="false"]
```

A TinyMCE button is available in the classic editor to help generate this shortcode.

## Settings

Access the plugin settings under `Settings` > `Every.org API` in the WordPress admin area.

* **Public API Key:** Your required key from Every.org Partners.
* **Default Display Settings:** Set the default block style and CTA button text for new profiles.
* **Connection Test:** Verify that your API key is working correctly.

## API Integration

The plugin integrates with the Every.org API v0.2 to fetch nonprofit data. It uses the following endpoints:

* **Search Nonprofits:** `GET /v0.2/search/:searchTerm`
  * Supports filtering by causes
  * Limited to 50 results per request
  * Real-time search results

* **Browse Nonprofits:** `GET /v0.2/browse/:cause`
  * Paginated results (up to 100 per page)
  * Filter by cause category
  * Includes pagination metadata

* **Nonprofit Details:** `GET /v0.2/nonprofit/:identifier`
  * Supports slugs, EINs, or nonprofit IDs
  * Returns detailed nonprofit information
  * Includes tags and cause categories

### Rate Limits

The plugin respects Every.org's API rate limits:
* Nonprofit details: 100 requests/minute/key
* Search: 500 requests/minute/key
* Browse: 500 requests/minute/key

The plugin implements caching to minimize API calls and stay within these limits.

## Templates

The plugin uses PHP template files located in the `templates/` directory to render the different profile styles.

* `nonprofit-standard.php`
* `nonprofit-card.php`
* `nonprofit-featured.php`
* `nonprofit-minimal.php`

These templates can be overridden by copying them into your theme's directory within a folder named `enpb-templates`. For example: `wp-content/themes/your-theme/enpb-templates/nonprofit-standard.php`.

Helper functions for use within templates:

* `enpb_process_description($description, $length)`: Trims and cleans the description text.
* `enpb_format_location($location)`: Formats the location string.
* `enpb_get_donation_url($profile_url)`: Generates the direct donation URL.

## Frontend Features (`frontend.js`)

The `assets/js/frontend.js` file provides optional enhancements:

* **Analytics Tracking:** Sends events to Google Analytics or GTM when donation buttons are clicked (if analytics tools are present).
* **Tag Tooltips:** Adds simple descriptive tooltips when hovering over nonprofit tags.
* **Donation Progress Bar:** Can display a progress bar if `data-donation-goal` and `data-donation-current` attributes are present on the `.enpb-nonprofit-profile` element (Requires manual data attribute addition or extension).
* **Card Hover Effects:** Adds subtle hover animations to card and featured styles.
* **Expandable Descriptions:** Provides "Read more" / "Show less" functionality for descriptions exceeding the specified length in standard and card styles.

## Files Included

* `every-org-nonprofit-profile.php`: Main plugin file.
* `includes/`: Contains core PHP functionality.
    * `api.php`: Handles REST API endpoints and Every.org communication.
    * `settings.php`: Manages the admin settings page.
    * `shortcode.php`: Implements the shortcode functionality.
    * `templates.php`: Manages template loading and helper functions.
* `assets/`: Contains CSS and JavaScript files.
    * `css/`: Stylesheets for the editor and frontend.
    * `js/`: JavaScript for the block editor, frontend interactions, and TinyMCE button.
* `templates/`: Contains the default HTML templates for profile styles.

## Support

For support, please:
1. Check the [Every.org API documentation](https://docs.every.org)
2. Visit the [WordPress.org plugin support forum](https://wordpress.org/support/plugin/every-org-nonprofit-profile)
3. Contact Every.org support at partners@every.org

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name] in partnership with Every.org.


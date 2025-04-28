<?php
/*
Plugin Name: Enhanced Every.org Nonprofit Profile
Description: An improved Gutenberg block to display nonprofit profiles from Every.org with enhanced UX
Version: 2.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define constants
define('ENPB_API_KEY_OPTION', 'enpb_api_key');
define('ENPB_VERSION', '2.0');
define('ENPB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ENPB_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include other files
require_once ENPB_PLUGIN_DIR . 'includes/settings.php';
require_once ENPB_PLUGIN_DIR . 'includes/api.php';
require_once ENPB_PLUGIN_DIR . 'includes/shortcode.php';
require_once ENPB_PLUGIN_DIR . 'includes/templates.php';

// Register block category
function enpb_register_category($categories) {
    return array_merge(
        $categories,
        [
            [
                'slug' => 'nonprofit-tools',
                'title' => __('Nonprofit Tools', 'enpb'),
                'icon' => 'heart',
            ],
        ]
    );
}
add_filter('block_categories_all', 'enpb_register_category', 10, 1);

// Enqueue scripts and styles for the editor
function enpb_enqueue_editor_assets() {
    wp_enqueue_script(
        'enpb-block',
        ENPB_PLUGIN_URL . 'assets/js/block.js',
        ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-api-fetch', 'wp-data', 'wp-i18n'],
        ENPB_VERSION,
        true
    );
    
    wp_enqueue_style(
        'enpb-editor-style',
        ENPB_PLUGIN_URL . 'assets/css/editor.css',
        [],
        ENPB_VERSION
    );
    
    // Localize script with API key
    wp_localize_script('enpb-block', 'enpbData', [
        'apiKey' => get_option(ENPB_API_KEY_OPTION, ''),
        'strings' => [
            'searchPlaceholder' => __('Search for nonprofits by name or cause...', 'enpb'),
            'loading' => __('Loading...', 'enpb'),
            'noResults' => __('No nonprofits found. Try a different search term.', 'enpb'),
            'apiError' => __('Error connecting to Every.org API. Please check your API key.', 'enpb'),
            'selectNonprofit' => __('Select a nonprofit', 'enpb'),
            'donateNow' => __('Donate Now', 'enpb')
        ]
    ]);
}
add_action('enqueue_block_editor_assets', 'enpb_enqueue_editor_assets');

// Enqueue scripts and styles for the frontend
function enpb_enqueue_frontend_assets() {
    wp_enqueue_style(
        'enpb-style',
        ENPB_PLUGIN_URL . 'assets/css/style.css',
        [],
        ENPB_VERSION
    );
    
    // Only load frontend script if there's a nonprofit block on the page
    if (has_block('enpb/nonprofit-profile') || has_shortcode(get_the_content(), 'nonprofit_profile')) {
        wp_enqueue_script(
            'enpb-frontend',
            ENPB_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            ENPB_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enpb_enqueue_frontend_assets');

// Register block
function enpb_register_blocks() {
    register_block_type('enpb/nonprofit-profile', [
        'editor_script' => 'enpb-block',
        'editor_style' => 'enpb-editor-style',
        'style' => 'enpb-style',
        'attributes' => [
            'nonprofitId' => ['type' => 'string'],
            'name' => ['type' => 'string'],
            'description' => ['type' => 'string'],
            'logoUrl' => ['type' => 'string'],
            'profileUrl' => ['type' => 'string'],
            'coverImageUrl' => ['type' => 'string'],
            'location' => ['type' => 'string'],
            'tags' => ['type' => 'array', 'default' => []],
            'blockStyle' => ['type' => 'string', 'default' => 'standard'],
            'ctaText' => ['type' => 'string', 'default' => 'Donate Now'],
            'ctaColor' => ['type' => 'string', 'default' => '#007bff'],
            'showStats' => ['type' => 'boolean', 'default' => true],
            'showTags' => ['type' => 'boolean', 'default' => true],
            'showDescription' => ['type' => 'boolean', 'default' => true],
            'descriptionLength' => ['type' => 'number', 'default' => 150],
        ],
        'render_callback' => 'enpb_render_nonprofit_block',
    ]);
}
add_action('init', 'enpb_register_blocks');

// Block rendering function
function enpb_render_nonprofit_block($attributes) {
    if (empty($attributes['nonprofitId'])) {
        return '<div class="enpb-error">No nonprofit selected.</div>';
    }
    
    // Check if we need to get additional data
    if (!isset($attributes['tags']) || empty($attributes['coverImageUrl'])) {
        $api_key = get_option(ENPB_API_KEY_OPTION);
        $nonprofit_data = enpb_fetch_nonprofit_data($attributes['nonprofitId'], $api_key);
        
        if ($nonprofit_data && isset($nonprofit_data['data'])) {
            // Merge with existing attributes
            $attributes = array_merge($attributes, [
                'tags' => isset($nonprofit_data['data']['tags']) ? $nonprofit_data['data']['tags'] : [],
                'coverImageUrl' => isset($nonprofit_data['data']['coverImageUrl']) ? $nonprofit_data['data']['coverImageUrl'] : '',
                'location' => isset($nonprofit_data['data']['location']) ? $nonprofit_data['data']['location'] : '',
            ]);
        }
    }
    
    // Load the appropriate template based on style
    $style = isset($attributes['blockStyle']) ? $attributes['blockStyle'] : 'standard';
    return enpb_get_template('nonprofit-' . $style, $attributes);
}

// Helper function to fetch nonprofit data
function enpb_fetch_nonprofit_data($nonprofit_id, $api_key) {
    if (empty($api_key)) {
        return false;
    }
    
    $url = "https://partners.every.org/v0.2/nonprofit/{$nonprofit_id}?apiKey={$api_key}";
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['data'])) {
        return false;
    }
    
    return $data;
}

// Add link to settings from plugin page
function enpb_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=enpb-settings">' . __('Settings', 'enpb') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'enpb_add_settings_link');

<?php
/**
 * API handling functions for Every.org integration
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register REST API routes
 */
function enpb_register_api_routes() {
    register_rest_route('enpb/v1', '/search', [
        'methods' => 'GET',
        'callback' => 'enpb_search_nonprofits',
        'permission_callback' => '__return_true',
        'args' => [
            'query' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'causes' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'limit' => [
                'required' => false,
                'default' => 10,
                'sanitize_callback' => 'absint',
            ],
        ],
    ]);
    
    register_rest_route('enpb/v1', '/nonprofit/(?P<id>[a-zA-Z0-9-_]+)', [
        'methods' => 'GET',
        'callback' => 'enpb_get_nonprofit',
        'permission_callback' => '__return_true',
    ]);
    
    register_rest_route('enpb/v1', '/causes', [
        'methods' => 'GET',
        'callback' => 'enpb_get_causes',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'enpb_register_api_routes');

/**
 * Search for nonprofits
 */
function enpb_search_nonprofits(WP_REST_Request $request) {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API key is not set', ['status' => 403]);
    }
    
    $query = $request->get_param('query');
    if (empty($query)) {
        return new WP_Error('no_query', 'Search query is required', ['status' => 400]);
    }
    
    // Build the query URL
    $url = "https://partners.every.org/v0.2/search/{$query}?apiKey={$api_key}";
    
    // Add optional parameters
    $causes = $request->get_param('causes');
    $limit = $request->get_param('limit');
    
    if (!empty($causes)) {
        $url .= "&causes={$causes}";
    }
    
    if (!empty($limit)) {
        $url .= "&limit={$limit}";
    }
    
    // Make the request with longer timeout
    $response = wp_remote_get($url, [
        'timeout' => 15, // Longer timeout for potentially slow API
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Cache the results
    set_transient('enpb_search_' . md5($url), $data, 3600); // Cache for 1 hour
    
    return rest_ensure_response($data);
}

/**
 * Get nonprofit details
 */
function enpb_get_nonprofit(WP_REST_Request $request) {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API key is not set', ['status' => 403]);
    }
    
    $id = $request['id'];
    
    // Check cache first
    $cached = get_transient('enpb_nonprofit_' . $id);
    if ($cached !== false) {
        return rest_ensure_response($cached);
    }
    
    $url = "https://partners.every.org/v0.2/nonprofit/{$id}?apiKey={$api_key}";
    $response = wp_remote_get($url, [
        'timeout' => 15, // Longer timeout
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Process and enhance the nonprofit data
    if (isset($data['data'])) {
        // Format description if too long
        if (isset($data['data']['description']) && strlen($data['data']['description']) > 300) {
            $data['data']['shortDescription'] = wp_trim_words($data['data']['description'], 30, '...');
        } else if (isset($data['data']['description'])) {
            $data['data']['shortDescription'] = $data['data']['description'];
        }
        
        // Add additional metadata about the nonprofit if available
    }
    
    // Cache the results
    set_transient('enpb_nonprofit_' . $id, $data, 3600 * 24); // Cache for 24 hours
    
    return rest_ensure_response($data);
}

/**
 * Get list of causes for filtering
 */
function enpb_get_causes(WP_REST_Request $request) {
    // Common causes from Every.org
    $causes = [
        'animals' => __('Animals', 'enpb'),
        'arts-culture' => __('Arts and Culture', 'enpb'),
        'community-development' => __('Community Development', 'enpb'),
        'education' => __('Education', 'enpb'),
        'environment' => __('Environment', 'enpb'),
        'health' => __('Health', 'enpb'),
        'human-services' => __('Human Services', 'enpb'),
        'international' => __('International', 'enpb'),
        'religion' => __('Religion', 'enpb'),
        'research-public-policy' => __('Research and Public Policy', 'enpb'),
    ];
    
    return rest_ensure_response(['causes' => $causes]);
}

/**
 * Clear API cache when settings are updated
 */
function enpb_clear_api_cache() {
    global $wpdb;
    
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_enpb_search_%',
            '_transient_enpb_nonprofit_%'
        )
    );
}
add_action('update_option_' . ENPB_API_KEY_OPTION, 'enpb_clear_api_cache');

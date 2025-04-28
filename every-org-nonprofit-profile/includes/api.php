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
    
    register_rest_route('enpb/v1', '/browse', [
        'methods' => 'GET',
        'callback' => 'enpb_browse_nonprofits',
        'permission_callback' => '__return_true',
        'args' => [
            'cause' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'page' => [
                'required' => false,
                'default' => 1,
                'sanitize_callback' => 'absint',
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
    
    register_rest_route('enpb/v1', '/fundraiser/(?P<nonprofit_id>[a-zA-Z0-9-_]+)/(?P<fundraiser_id>[a-zA-Z0-9-_]+)', [
        'methods' => 'GET',
        'callback' => 'enpb_get_fundraiser',
        'permission_callback' => '__return_true',
    ]);
    
    register_rest_route('enpb/v1', '/fundraiser/(?P<nonprofit_id>[a-zA-Z0-9-_]+)/(?P<fundraiser_id>[a-zA-Z0-9-_]+)/raised', [
        'methods' => 'GET',
        'callback' => 'enpb_get_fundraiser_raised',
        'permission_callback' => '__return_true',
    ]);
    
    register_rest_route('enpb/v1', '/fundraiser', [
        'methods' => 'POST',
        'callback' => 'enpb_create_fundraiser',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'nonprofitId' => [
                'required' => true,
                'type' => 'string',
            ],
            'title' => [
                'required' => true,
                'type' => 'string',
            ],
            'description' => [
                'required' => false,
                'type' => 'string',
            ],
            'startDate' => [
                'required' => false,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'endDate' => [
                'required' => false,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'goal' => [
                'required' => false,
                'type' => 'integer',
            ],
            'raisedOffline' => [
                'required' => false,
                'type' => 'integer',
            ],
            'currency' => [
                'required' => false,
                'type' => 'string',
                'default' => 'USD',
            ],
            'imageBase64' => [
                'required' => false,
                'type' => 'string',
            ],
        ],
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
    
    // Build the query URL with the correct API endpoint
    $url = "https://partners.every.org/v0.2/search/{$query}";
    
    // Add API key as URL parameter
    $url .= "?apiKey={$api_key}";
    
    // Add optional parameters
    $limit = $request->get_param('limit');
    if (!empty($limit)) {
        // Ensure limit doesn't exceed maximum of 50
        $limit = min((int)$limit, 50);
        $url .= "&take={$limit}";
    }
    
    $causes = $request->get_param('causes');
    if (!empty($causes)) {
        $url .= "&causes={$causes}";
    }
    
    // Make the request (GET only for public endpoints)
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'api_error',
            'Every.org API returned an error: ' . wp_remote_retrieve_response_message($response),
            ['status' => $response_code]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Validate response structure
    if (!isset($data['nonprofits']) || !is_array($data['nonprofits'])) {
        return new WP_Error('invalid_response', 'Invalid response structure from API', ['status' => 500]);
    }
    
    // Cache the results
    set_transient('enpb_search_' . md5($url), $data, 3600); // Cache for 1 hour
    
    return rest_ensure_response($data);
}

/**
 * Browse nonprofits by cause
 */
function enpb_browse_nonprofits(WP_REST_Request $request) {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API key is not set', ['status' => 403]);
    }
    
    $cause = $request->get_param('cause');
    if (empty($cause)) {
        return new WP_Error('no_cause', 'Cause is required', ['status' => 400]);
    }
    
    // Build the URL with the correct API endpoint
    $url = "https://partners.every.org/v0.2/browse/{$cause}";
    
    // Add API key as URL parameter
    $url .= "?apiKey={$api_key}";
    
    // Add pagination parameters
    $page = $request->get_param('page');
    if (!empty($page)) {
        $url .= "&page={$page}";
    }
    
    $limit = $request->get_param('limit');
    if (!empty($limit)) {
        // Ensure limit doesn't exceed maximum of 100
        $limit = min((int)$limit, 100);
        $url .= "&take={$limit}";
    }
    
    // Make the request
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'api_error',
            'Every.org API returned an error: ' . wp_remote_retrieve_response_message($response),
            ['status' => $response_code]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Validate response structure
    if (!isset($data['nonprofits']) || !is_array($data['nonprofits'])) {
        return new WP_Error('invalid_response', 'Invalid response structure from API', ['status' => 500]);
    }
    
    // Cache the results
    set_transient('enpb_browse_' . md5($url), $data, 3600); // Cache for 1 hour
    
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
    if (empty($id)) {
        return new WP_Error('invalid_id', 'Nonprofit ID is required', ['status' => 400]);
    }
    
    // Check cache first
    $cached = get_transient('enpb_nonprofit_' . $id);
    if ($cached !== false) {
        return rest_ensure_response($cached);
    }
    
    // Build the URL with the correct API endpoint
    $url = "https://partners.every.org/v0.2/nonprofit/{$id}?apiKey={$api_key}";
    
    // Make the request (GET only for public endpoints)
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'api_error',
            'Every.org API returned an error: ' . wp_remote_retrieve_response_message($response),
            ['status' => $response_code]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Validate response structure
    if (!isset($data['data']['nonprofit']) || !is_array($data['data']['nonprofit'])) {
        return new WP_Error('invalid_response', 'Invalid response structure from API', ['status' => 500]);
    }
    
    // Process and enhance the nonprofit data
    $nonprofit = $data['data']['nonprofit'];
    
    // Format description if too long
    if (isset($nonprofit['description']) && strlen($nonprofit['description']) > 300) {
        $nonprofit['shortDescription'] = wp_trim_words($nonprofit['description'], 30, '...');
    } else if (isset($nonprofit['description'])) {
        $nonprofit['shortDescription'] = $nonprofit['description'];
    }
    
    // Add additional metadata
    $nonprofit['donationUrl'] = isset($nonprofit['profileUrl']) ? $nonprofit['profileUrl'] : '';
    $nonprofit['websiteUrl'] = isset($nonprofit['websiteUrl']) ? $nonprofit['websiteUrl'] : '';
    $nonprofit['logoUrl'] = isset($nonprofit['logoUrl']) ? $nonprofit['logoUrl'] : '';
    $nonprofit['coverImageUrl'] = isset($nonprofit['coverImageUrl']) ? $nonprofit['coverImageUrl'] : '';
    
    $data['data']['nonprofit'] = $nonprofit;
    
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

/**
 * Get fundraiser details
 */
function enpb_get_fundraiser(WP_REST_Request $request) {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API key is not set', ['status' => 403]);
    }
    
    $nonprofit_id = $request->get_param('nonprofit_id');
    $fundraiser_id = $request->get_param('fundraiser_id');
    
    if (empty($nonprofit_id) || empty($fundraiser_id)) {
        return new WP_Error('invalid_params', 'Nonprofit ID and Fundraiser ID are required', ['status' => 400]);
    }
    
    // Check cache first
    $cache_key = "enpb_fundraiser_{$nonprofit_id}_{$fundraiser_id}";
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return rest_ensure_response($cached);
    }
    
    // Build the URL with the correct API endpoint
    $url = "https://partners.every.org/v0.2/nonprofit/{$nonprofit_id}/fundraiser/{$fundraiser_id}?apiKey={$api_key}";
    
    // Make the request
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'api_error',
            'Every.org API returned an error: ' . wp_remote_retrieve_response_message($response),
            ['status' => $response_code]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Cache the results
    set_transient($cache_key, $data, 3600); // Cache for 1 hour
    
    return rest_ensure_response($data);
}

/**
 * Get fundraiser raised amount
 */
function enpb_get_fundraiser_raised(WP_REST_Request $request) {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API key is not set', ['status' => 403]);
    }
    
    $nonprofit_id = $request->get_param('nonprofit_id');
    $fundraiser_id = $request->get_param('fundraiser_id');
    
    if (empty($nonprofit_id) || empty($fundraiser_id)) {
        return new WP_Error('invalid_params', 'Nonprofit ID and Fundraiser ID are required', ['status' => 400]);
    }
    
    // Check cache first
    $cache_key = "enpb_fundraiser_raised_{$nonprofit_id}_{$fundraiser_id}";
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return rest_ensure_response($cached);
    }
    
    // Build the URL with the correct API endpoint
    $url = "https://partners.every.org/v0.2/nonprofit/{$nonprofit_id}/fundraiser/{$fundraiser_id}/raised?apiKey={$api_key}";
    
    // Make the request
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to fetch data from Every.org: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'api_error',
            'Every.org API returned an error: ' . wp_remote_retrieve_response_message($response),
            ['status' => $response_code]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    // Cache the results
    set_transient($cache_key, $data, 3600); // Cache for 1 hour
    
    return rest_ensure_response($data);
}

/**
 * Create a new fundraiser
 */
function enpb_create_fundraiser(WP_REST_Request $request) {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    $private_key = get_option('enpb_private_key');
    
    if (empty($api_key) || empty($private_key)) {
        return new WP_Error('no_api_key', 'API keys are not set', ['status' => 403]);
    }
    
    $nonprofit_id = $request->get_param('nonprofitId');
    $title = $request->get_param('title');
    $description = $request->get_param('description');
    $start_date = $request->get_param('startDate');
    $end_date = $request->get_param('endDate');
    $goal = $request->get_param('goal');
    $raised_offline = $request->get_param('raisedOffline');
    $currency = $request->get_param('currency');
    $image_base64 = $request->get_param('imageBase64');
    
    if (empty($nonprofit_id) || empty($title)) {
        return new WP_Error('invalid_params', 'Nonprofit ID and title are required', ['status' => 400]);
    }
    
    // Build the request body
    $body = [
        'nonprofitId' => $nonprofit_id,
        'title' => $title,
        'description' => $description,
        'startDate' => $start_date,
        'endDate' => $end_date,
        'goal' => $goal,
        'raisedOffline' => $raised_offline,
        'currency' => $currency ?: 'USD',
    ];
    
    if (!empty($image_base64)) {
        $body['imageBase64'] = $image_base64;
    }
    
    // Make the request
    $response = wp_remote_post('https://partners.every.org/v0.2/fundraiser', [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $private_key),
        ],
        'body' => json_encode($body),
    ]);
    
    if (is_wp_error($response)) {
        return new WP_Error(
            'api_error', 
            'Failed to create fundraiser: ' . $response->get_error_message(), 
            ['status' => 500]
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'api_error',
            'Every.org API returned an error: ' . wp_remote_retrieve_response_message($response),
            ['status' => $response_code]
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid response from API', ['status' => 500]);
    }
    
    return rest_ensure_response($data);
}

/**
 * Generate an Every.org donate link with optional parameters
 * 
 * @param string $nonprofit_identifier The nonprofit's slug or EIN
 * @param array $params Optional parameters for the donate link
 * @return string The generated donate link
 */
function enpb_generate_donate_link($nonprofit_identifier, $params = []) {
    // Base URL
    $url = "https://www.every.org/{$nonprofit_identifier}#donate";
    
    // Add parameters if provided
    if (!empty($params)) {
        $query_params = [];
        
        // Amount
        if (isset($params['amount'])) {
            $query_params['amount'] = $params['amount'];
        }
        
        // Suggested amounts
        if (isset($params['suggestedAmounts']) && is_array($params['suggestedAmounts'])) {
            $query_params['suggestedAmounts'] = implode(',', array_slice($params['suggestedAmounts'], 0, 5));
        }
        
        // Minimum value
        if (isset($params['min_value'])) {
            $query_params['min_value'] = $params['min_value'];
        }
        
        // Frequency
        if (isset($params['frequency']) && in_array($params['frequency'], ['ONCE', 'MONTHLY'])) {
            $query_params['frequency'] = $params['frequency'];
        }
        
        // Donor information
        if (isset($params['email'])) {
            $query_params['email'] = $params['email'];
        }
        if (isset($params['first_name'])) {
            $query_params['first_name'] = $params['first_name'];
        }
        if (isset($params['last_name'])) {
            $query_params['last_name'] = $params['last_name'];
        }
        
        // Description
        if (isset($params['description'])) {
            $query_params['description'] = $params['description'];
        }
        
        // Modal settings
        if (isset($params['no_exit'])) {
            $query_params['no_exit'] = $params['no_exit'];
        }
        
        // Redirect URLs
        if (isset($params['success_url'])) {
            $query_params['success_url'] = $params['success_url'];
        }
        if (isset($params['exit_url'])) {
            $query_params['exit_url'] = $params['exit_url'];
        }
        
        // Partner information
        if (isset($params['partner_donation_id'])) {
            $query_params['partner_donation_id'] = $params['partner_donation_id'];
        }
        if (isset($params['partner_metadata'])) {
            $query_params['partner_metadata'] = $params['partner_metadata'];
        }
        
        // Share info settings
        if (isset($params['require_share_info'])) {
            $query_params['require_share_info'] = $params['require_share_info'];
        }
        if (isset($params['share_info'])) {
            $query_params['share_info'] = $params['share_info'];
        }
        
        // Designation
        if (isset($params['designation'])) {
            $query_params['designation'] = $params['designation'];
        }
        
        // Webhook token
        if (isset($params['webhook_token'])) {
            $query_params['webhook_token'] = $params['webhook_token'];
        }
        
        // Theme color
        if (isset($params['theme_color'])) {
            $query_params['theme_color'] = $params['theme_color'];
        }
        
        // Payment methods
        if (isset($params['method'])) {
            $allowed_methods = ['card', 'bank', 'paypal', 'venmo', 'pay', 'crypto', 'stocks', 'daf', 'gift'];
            $methods = is_array($params['method']) ? $params['method'] : explode(',', $params['method']);
            $valid_methods = array_intersect($methods, $allowed_methods);
            if (!empty($valid_methods)) {
                $query_params['method'] = implode(',', $valid_methods);
            }
        }
        
        // Add query parameters to URL
        if (!empty($query_params)) {
            $url = add_query_arg($query_params, $url);
        }
    }
    
    return $url;
}

/**
 * Get donation URL for a nonprofit
 * 
 * @param string $nonprofit_id The nonprofit's ID (slug or EIN)
 * @param array $params Optional parameters for the donate link
 * @return string The donation URL
 */
function enpb_get_donation_url($nonprofit_id, $params = []) {
    return enpb_generate_donate_link($nonprofit_id, $params);
}

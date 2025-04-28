<?php
/**
 * Shortcode functionality
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register the nonprofit profile shortcode
 */
function enpb_register_shortcode() {
    add_shortcode('nonprofit_profile', 'enpb_nonprofit_profile_shortcode');
}
add_action('init', 'enpb_register_shortcode');

/**
 * Shortcode handler for nonprofit profiles
 * 
 * @param array $atts Shortcode attributes
 * @return string Rendered shortcode content
 */
function enpb_nonprofit_profile_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts([
        'id' => '',
        'style' => get_option('enpb_default_style', 'standard'),
        'cta_text' => get_option('enpb_default_cta_text', __('Donate Now', 'enpb')),
        'cta_color' => '#007bff',
        'show_description' => 'true',
        'description_length' => 150,
        'show_tags' => 'true',
        'show_stats' => 'true',
    ], $atts, 'nonprofit_profile');
    
    // Validate nonprofit ID
    if (empty($atts['id'])) {
        return '<div class="enpb-error">' . __('Error: Nonprofit ID is required.', 'enpb') . '</div>';
    }
    
    // Convert string booleans to actual booleans
    $atts['show_description'] = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
    $atts['show_tags'] = filter_var($atts['show_tags'], FILTER_VALIDATE_BOOLEAN);
    $atts['show_stats'] = filter_var($atts['show_stats'], FILTER_VALIDATE_BOOLEAN);
    $atts['description_length'] = absint($atts['description_length']);
    
    // Get API key
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return '<div class="enpb-error">' . __('Error: Every.org API key not configured.', 'enpb') . '</div>';
    }
    
    // Try to get from cache first
    $cached_data = get_transient('enpb_nonprofit_' . $atts['id']);
    $nonprofit_data = false;
    
    if ($cached_data !== false) {
        $nonprofit_data = $cached_data;
    } else {
        // Fetch nonprofit data
        $url = "https://partners.every.org/v0.2/nonprofit/{$atts['id']}?apiKey={$api_key}";
        $response = wp_remote_get($url, [
            'timeout' => 15,
        ]);
        
        if (is_wp_error($response)) {
            return '<div class="enpb-error">' . 
                sprintf(__('Error fetching nonprofit data: %s', 'enpb'), $response->get_error_message()) . 
                '</div>';
        }
        
        $body = wp_remote_retrieve_body($response);
        $nonprofit_data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($nonprofit_data['data']['nonprofit'])) {
            return '<div class="enpb-error">' . __('Error: Invalid response from Every.org API.', 'enpb') . '</div>';
        }
        
        // Cache the results
        set_transient('enpb_nonprofit_' . $atts['id'], $nonprofit_data, 3600 * 24); // Cache for 24 hours
    }
    
    // Extract nonprofit data from the response
    $nonprofit = $nonprofit_data['data']['nonprofit'];
    $tags = isset($nonprofit_data['data']['nonprofitTags']) ? $nonprofit_data['data']['nonprofitTags'] : [];
    
    // Prepare attributes for template
    $template_atts = [
        'nonprofitId' => $nonprofit['id'],
        'name' => $nonprofit['name'],
        'description' => $nonprofit['description'],
        'descriptionLong' => $nonprofit['descriptionLong'],
        'logoUrl' => $nonprofit['logoUrl'],
        'coverImageUrl' => $nonprofit['coverImageUrl'],
        'profileUrl' => $nonprofit['profileUrl'],
        'websiteUrl' => $nonprofit['websiteUrl'],
        'location' => $nonprofit['locationAddress'],
        'ein' => $nonprofit['ein'],
        'nteeCode' => $nonprofit['nteeCode'],
        'nteeCodeMeaning' => $nonprofit['nteeCodeMeaning'],
        'tags' => array_map(function($tag) {
            return [
                'name' => $tag['tagName'],
                'title' => $tag['title'],
                'category' => $tag['causeCategory'],
                'imageUrl' => $tag['tagImageUrl'],
                'url' => $tag['tagUrl']
            ];
        }, $tags),
        'blockStyle' => $atts['style'],
        'ctaText' => $atts['cta_text'],
        'ctaColor' => $atts['cta_color'],
        'showStats' => $atts['show_stats'],
        'showTags' => $atts['show_tags'],
        'showDescription' => $atts['show_description'],
        'descriptionLength' => $atts['description_length'],
    ];
    
    // Render the template
    return enpb_get_template('nonprofit-' . $atts['style'], $template_atts);
}

/**
 * Add shortcode generator to TinyMCE
 */
function enpb_add_tinymce_button() {
    // Check user permissions
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }
    
    // Check if WYSIWYG is enabled
    if ('true' == get_user_option('rich_editing')) {
        add_filter('mce_external_plugins', 'enpb_add_tinymce_plugin');
        add_filter('mce_buttons', 'enpb_register_tinymce_button');
    }
}
add_action('admin_init', 'enpb_add_tinymce_button');

/**
 * Register new button in the editor
 */
function enpb_register_tinymce_button($buttons) {
    array_push($buttons, 'enpb_shortcode_button');
    return $buttons;
}

/**
 * Add the button script
 */
function enpb_add_tinymce_plugin($plugin_array) {
    $plugin_array['enpb_shortcode_button'] = ENPB_PLUGIN_URL . 'assets/js/tinymce-button.js';
    return $plugin_array;
}

/**
 * Add popup for the TinyMCE button
 */
function enpb_tinymce_popup() {
    // Only load on post edit screens
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'page'])) {
        return;
    }
    
    // Get API key
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        return;
    }
    
    // Get default styles
    $default_style = get_option('enpb_default_style', 'standard');
    $default_cta_text = get_option('enpb_default_cta_text', __('Donate Now', 'enpb'));
    
    ?>
    <div id="enpb-shortcode-popup" style="display:none;">
        <div class="enpb-popup-content">
            <h2><?php _e('Insert Nonprofit Profile', 'enpb'); ?></h2>
            
            <div class="enpb-popup-search">
                <label for="enpb-search-input">
                    <?php _e('Search for a nonprofit:', 'enpb'); ?>
                </label>
                <input type="text" id="enpb-search-input" placeholder="<?php esc_attr_e('Enter nonprofit name...', 'enpb'); ?>" />
                <button type="button" id="enpb-search-button" class="button button-primary">
                    <?php _e('Search', 'enpb'); ?>
                </button>
                <div id="enpb-search-results"></div>
                <div id="enpb-loading" style="display:none;"><?php _e('Loading...', 'enpb'); ?></div>
            </div>
            
            <div id="enpb-selected-nonprofit" style="display:none;">
                <h3><?php _e('Selected Nonprofit:', 'enpb'); ?> <span id="enpb-selected-name"></span></h3>
                <input type="hidden" id="enpb-selected-id" value="" />
                
                <div class="enpb-options">
                    <div class="enpb-option">
                        <label for="enpb-style"><?php _e('Display Style:', 'enpb'); ?></label>
                        <select id="enpb-style">
                            <option value="standard" <?php selected($default_style, 'standard'); ?>>
                                <?php _e('Standard', 'enpb'); ?>
                            </option>
                            <option value="card" <?php selected($default_style, 'card'); ?>>
                                <?php _e('Card', 'enpb'); ?>
                            </option>
                            <option value="featured" <?php selected($default_style, 'featured'); ?>>
                                <?php _e('Featured', 'enpb'); ?>
                            </option>
                            <option value="minimal" <?php selected($default_style, 'minimal'); ?>>
                                <?php _e('Minimal', 'enpb'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="enpb-option">
                        <label for="enpb-cta-text"><?php _e('Button Text:', 'enpb'); ?></label>
                        <input type="text" id="enpb-cta-text" value="<?php echo esc_attr($default_cta_text); ?>" />
                    </div>
                    
                    <div class="enpb-option">
                        <label for="enpb-cta-color"><?php _e('Button Color:', 'enpb'); ?></label>
                        <input type="color" id="enpb-cta-color" value="#007bff" />
                    </div>
                    
                    <div class="enpb-option">
                        <label>
                            <input type="checkbox" id="enpb-show-description" checked />
                            <?php _e('Show Description', 'enpb'); ?>
                        </label>
                    </div>
                    
                    <div class="enpb-option">
                        <label>
                            <input type="checkbox" id="enpb-show-tags" checked />
                            <?php _e('Show Tags', 'enpb'); ?>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="enpb-popup-buttons">
                <button type="button" id="enpb-insert-shortcode" class="button button-primary" disabled>
                    <?php _e('Insert Shortcode', 'enpb'); ?>
                </button>
                <button type="button" id="enpb-cancel" class="button">
                    <?php _e('Cancel', 'enpb'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <style>
        #enpb-shortcode-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            max-width: 90%;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            z-index: 100000;
        }
        
        .enpb-popup-content h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .enpb-popup-search {
            margin-bottom: 20px;
        }
        
        .enpb-popup-search label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        #enpb-search-input {
            width: 70%;
            padding: 8px;
        }
        
        #enpb-search-button {
            padding: 8px 12px;
            vertical-align: middle;
        }
        
        #enpb-search-results {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            display: none;
        }
        
        .enpb-search-result {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .enpb-search-result:hover {
            background: #f5f5f5;
        }
        
        .enpb-search-result:last-child {
            border-bottom: none;
        }
        
        #enpb-selected-nonprofit {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .enpb-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .enpb-option label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .enpb-option input[type="text"],
        .enpb-option select {
            width: 100%;
        }
        
        .enpb-popup-buttons {
            text-align: right;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .enpb-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
        }
    </style>
    
    <script>
        jQuery(document).ready(function($) {
            // Show popup when button is clicked
            $(document).on('click', '#enpb_shortcode_button', function() {
                $('body').append('<div class="enpb-popup-overlay"></div>');
                $('#enpb-shortcode-popup').show();
            });
            
            // Hide popup
            function hidePopup() {
                $('.enpb-popup-overlay').remove();
                $('#enpb-shortcode-popup').hide();
                $('#enpb-search-results').empty().hide();
                $('#enpb-selected-nonprofit').hide();
                $('#enpb-insert-shortcode').prop('disabled', true);
            }
            
            // Cancel button
            $('#enpb-cancel').on('click', hidePopup);
            
            // Close popup when clicking outside
            $(document).on('click', '.enpb-popup-overlay', hidePopup);
            
            // Search button click
            $('#enpb-search-button').on('click', function() {
                const query = $('#enpb-search-input').val().trim();
                if (query.length < 2) {
                    alert('<?php _e('Please enter at least 2 characters to search.', 'enpb'); ?>');
                    return;
                }
                
                $('#enpb-loading').show();
                $('#enpb-search-results').hide().empty();
                
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'enpb_search_nonprofits',
                        query: query,
                        limit: 10
                    },
                    success: function(response) {
                        $('#enpb-loading').hide();
                        
                        if (response.success && response.data && response.data.nonprofits && response.data.nonprofits.length > 0) {
                            const results = response.data.nonprofits;
                            
                            results.forEach(function(nonprofit) {
                                const resultItem = $('<div class="enpb-search-result" data-id="' + nonprofit.id + '"></div>');
                                
                                let resultContent = '<strong>' + nonprofit.name + '</strong>';
                                
                                if (nonprofit.location) {
                                    resultContent += ' <span class="enpb-location">(' + nonprofit.location + ')</span>';
                                }
                                
                                resultItem.html(resultContent);
                                $('#enpb-search-results').append(resultItem);
                            });
                            
                            $('#enpb-search-results').show();
                        } else {
                            $('#enpb-search-results').html('<p><?php _e('No nonprofits found. Try a different search term.', 'enpb'); ?></p>').show();
                        }
                    },
                    error: function() {
                        $('#enpb-loading').hide();
                        $('#enpb-search-results').html('<p class="enpb-error"><?php _e('Error connecting to API. Please try again.', 'enpb'); ?></p>').show();
                    }
                });
            });
            
            // Search on Enter key
            $('#enpb-search-input').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#enpb-search-button').click();
                    e.preventDefault();
                }
            });
            
            // Select a nonprofit
            $(document).on('click', '.enpb-search-result', function() {
                const id = $(this).data('id');
                const name = $(this).find('strong').text();
                
                $('#enpb-selected-id').val(id);
                $('#enpb-selected-name').text(name);
                $('#enpb-selected-nonprofit').show();
                $('#enpb-insert-shortcode').prop('disabled', false);
                
                $('#enpb-search-results').hide();
            });
            
            // Insert shortcode
            $('#enpb-insert-shortcode').on('click', function() {
                const id = $('#enpb-selected-id').val();
                const style = $('#enpb-style').val();
                const ctaText = $('#enpb-cta-text').val();
                const ctaColor = $('#enpb-cta-color').val();
                const showDescription = $('#enpb-show-description').is(':checked') ? 'true' : 'false';
                const showTags = $('#enpb-show-tags').is(':checked') ? 'true' : 'false';
                
                let shortcode = '[nonprofit_profile id="' + id + '"';
                
                // Only add attributes that are different from defaults
                if (style !== '<?php echo esc_js($default_style); ?>') {
                    shortcode += ' style="' + style + '"';
                }
                
                if (ctaText !== '<?php echo esc_js($default_cta_text); ?>') {
                    shortcode += ' cta_text="' + ctaText + '"';
                }
                
                if (ctaColor !== '#007bff') {
                    shortcode += ' cta_color="' + ctaColor + '"';
                }
                
                if (showDescription !== 'true') {
                    shortcode += ' show_description="false"';
                }
                
                if (showTags !== 'true') {
                    shortcode += ' show_tags="false"';
                }
                
                shortcode += ']';
                
                // Insert the shortcode to editor
                window.send_to_editor(shortcode);
                
                // Close the popup
                hidePopup();
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'enpb_tinymce_popup');

/**
 * AJAX handler for searching nonprofits
 */
function enpb_ajax_search_nonprofits() {
    $query = isset($_REQUEST['query']) ? sanitize_text_field($_REQUEST['query']) : '';
    $limit = isset($_REQUEST['limit']) ? absint($_REQUEST['limit']) : 10;
    
    if (empty($query)) {
        wp_send_json_error(['message' => __('Search query is required', 'enpb')]);
    }
    
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        wp_send_json_error(['message' => __('API key is not set', 'enpb')]);
    }
    
    $url = "https://partners.every.org/v0.2/search/{$query}?apiKey={$api_key}&limit={$limit}";
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => __('Invalid response from API', 'enpb')]);
    }
    
    wp_send_json_success($data);
}
add_action('wp_ajax_enpb_search_nonprofits', 'enpb_ajax_search_nonprofits');

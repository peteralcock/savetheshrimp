<?php
/**
 * Settings page and functionality
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Add Settings page to admin menu
 */
function enpb_settings_page() {
    add_options_page(
        __('Every.org Nonprofit Profiles', 'enpb'),
        __('Every.org API', 'enpb'),
        'manage_options',
        'enpb-settings',
        'enpb_settings_page_content'
    );
}
add_action('admin_menu', 'enpb_settings_page');

/**
 * Settings page content
 */
function enpb_settings_page_content() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Show success message if settings were updated
    if (isset($_GET['settings-updated'])) {
        add_settings_error(
            'enpb_messages',
            'enpb_message',
            __('Settings Saved', 'enpb'),
            'updated'
        );
    }
    
    // Debug mode for connection test
    $debug_mode = isset($_GET['debug']) ? true : false;
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="enpb-admin-header">
            <h2><?php _e('Connect your website to Every.org', 'enpb'); ?></h2>
            <p>
                <?php _e('This plugin connects to the Every.org API to display nonprofit profiles and donation options on your website.', 'enpb'); ?>
                <a href="https://partners.every.org" target="_blank"><?php _e('Get your API keys here', 'enpb'); ?></a>.
            </p>
        </div>
        
        <?php settings_errors('enpb_messages'); ?>
        
        <div class="enpb-admin-content">
            <div class="enpb-admin-main">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('enpb_settings');
                    do_settings_sections('enpb-settings');
                    submit_button(__('Save Settings', 'enpb'));
                    ?>
                </form>
                
                <?php if (!empty(get_option(ENPB_API_KEY_OPTION))): ?>
                    <div class="enpb-connection-test">
                        <h3><?php _e('Test Connection', 'enpb'); ?></h3>
                        <button type="button" class="button enpb-test-connection">
                            <?php _e('Test API Connection', 'enpb'); ?>
                        </button>
                        <div class="enpb-connection-result"></div>
                        
                        <?php if ($debug_mode): ?>
                            <div class="enpb-debug-info">
                                <h4><?php _e('Debug Information', 'enpb'); ?></h4>
                                <pre><?php 
                                    $api_key = get_option(ENPB_API_KEY_OPTION);
                                    $url = "https://partners.every.org/v0.2/search/red-cross?apiKey={$api_key}";
                                    $response = wp_remote_get($url);
                                    
                                    if (is_wp_error($response)) {
                                        echo "Error: " . $response->get_error_message();
                                    } else {
                                        $status = wp_remote_retrieve_response_code($response);
                                        echo "Status: " . $status . "\n\n";
                                        
                                        if ($status === 200) {
                                            $body = wp_remote_retrieve_body($response);
                                            $data = json_decode($body, true);
                                            echo "Response: \n";
                                            print_r($data);
                                        } else {
                                            echo "Response: " . wp_remote_retrieve_body($response);
                                        }
                                    }
                                ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="enpb-shortcode-generator">
                        <h3><?php _e('Shortcode Generator', 'enpb'); ?></h3>
                        <div class="enpb-shortcode-form">
                            <div class="enpb-form-row">
                                <label for="enpb-nonprofit-search"><?php _e('Search for a Nonprofit:', 'enpb'); ?></label>
                                <div class="enpb-search-container">
                                    <input type="text" id="enpb-nonprofit-search" class="regular-text" placeholder="<?php _e('Enter nonprofit name or cause...', 'enpb'); ?>">
                                    <button type="button" class="button" id="enpb-search-nonprofit"><?php _e('Search', 'enpb'); ?></button>
                                </div>
                                <div id="enpb-search-results" class="enpb-search-results" style="display: none;">
                                    <div class="enpb-results-list"></div>
                                </div>
                            </div>
                            
                            <div class="enpb-form-row">
                                <label for="enpb-nonprofit-id"><?php _e('Nonprofit ID (slug or EIN):', 'enpb'); ?></label>
                                <input type="text" id="enpb-nonprofit-id" class="regular-text" placeholder="e.g., fur-pets-sake-pet-center">
                                <p class="description"><?php _e('You can use either the nonprofit\'s slug or EIN number.', 'enpb'); ?></p>
                            </div>
                            
                            <div class="enpb-form-row">
                                <label for="enpb-style"><?php _e('Display Style:', 'enpb'); ?></label>
                                <select id="enpb-style" class="regular-text">
                                    <option value="standard"><?php _e('Standard', 'enpb'); ?></option>
                                    <option value="card"><?php _e('Card', 'enpb'); ?></option>
                                    <option value="featured"><?php _e('Featured', 'enpb'); ?></option>
                                    <option value="minimal"><?php _e('Minimal', 'enpb'); ?></option>
                                </select>
                            </div>
                            
                            <div class="enpb-form-row">
                                <label for="enpb-cta-text"><?php _e('Button Text:', 'enpb'); ?></label>
                                <input type="text" id="enpb-cta-text" class="regular-text" value="<?php echo esc_attr(get_option('enpb_default_cta_text', __('Donate Now', 'enpb'))); ?>">
                            </div>
                            
                            <div class="enpb-form-row">
                                <label for="enpb-cta-color"><?php _e('Button Color:', 'enpb'); ?></label>
                                <input type="color" id="enpb-cta-color" value="#007bff">
                            </div>
                            
                            <div class="enpb-form-row">
                                <label>
                                    <input type="checkbox" id="enpb-show-description" checked>
                                    <?php _e('Show Description', 'enpb'); ?>
                                </label>
                            </div>
                            
                            <div class="enpb-form-row">
                                <label>
                                    <input type="checkbox" id="enpb-show-tags" checked>
                                    <?php _e('Show Tags', 'enpb'); ?>
                                </label>
                            </div>
                            
                            <div class="enpb-form-row">
                                <label for="enpb-description-length"><?php _e('Description Length:', 'enpb'); ?></label>
                                <input type="number" id="enpb-description-length" class="small-text" value="150" min="0" max="1000">
                            </div>
                            
                            <div class="enpb-form-row">
                                <button type="button" class="button button-primary" id="enpb-generate-shortcode">
                                    <?php _e('Generate Shortcode', 'enpb'); ?>
                                </button>
                            </div>
                            
                            <div class="enpb-shortcode-result" style="display: none;">
                                <h4><?php _e('Generated Shortcode:', 'enpb'); ?></h4>
                                <div class="enpb-shortcode-preview">
                                    <code id="enpb-shortcode-output"></code>
                                    <button type="button" class="button enpb-copy-shortcode">
                                        <?php _e('Copy', 'enpb'); ?>
                                    </button>
                                </div>
                                <p class="description"><?php _e('Paste this shortcode into any post or page to display the nonprofit profile.', 'enpb'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="enpb-admin-sidebar">
                <div class="enpb-sidebar-widget">
                    <h3><?php _e('How to Use', 'enpb'); ?></h3>
                    <ol>
                        <li><?php _e('Enter your Every.org API keys', 'enpb'); ?></li>
                        <li><?php _e('Add the Nonprofit Profile block to any page or post', 'enpb'); ?></li>
                        <li><?php _e('Search for nonprofits and select one to display', 'enpb'); ?></li>
                        <li><?php _e('Configure display options in the block settings', 'enpb'); ?></li>
                    </ol>
                    <p><?php _e('You can also use the shortcode:', 'enpb'); ?></p>
                    <code>[nonprofit_profile id="your-nonprofit-id"]</code>
                </div>
                
                <div class="enpb-sidebar-widget">
                    <h3><?php _e('Need Help?', 'enpb'); ?></h3>
                    <p><?php _e('For more information about connecting with Every.org:', 'enpb'); ?></p>
                    <ul>
                        <li><a href="https://docs.every.org" target="_blank"><?php _e('API Documentation', 'enpb'); ?></a></li>
                        <li><a href="https://every.org/contact" target="_blank"><?php _e('Contact Every.org', 'enpb'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .enpb-admin-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .enpb-admin-content {
            display: flex;
            gap: 30px;
        }
        
        .enpb-admin-main {
            flex: 2;
        }
        
        .enpb-admin-sidebar {
            flex: 1;
        }
        
        .enpb-sidebar-widget {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .enpb-connection-test,
        .enpb-shortcode-generator {
            margin-top: 30px;
            padding: 20px;
            background: #f1f1f1;
            border-radius: 5px;
        }
        
        .enpb-connection-result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 3px;
            display: none;
        }
        
        .enpb-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .enpb-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .enpb-debug-info {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        
        .enpb-debug-info pre {
            background: #23282d;
            color: #eee;
            padding: 15px;
            overflow: auto;
            max-height: 300px;
        }
        
        .enpb-shortcode-form {
            margin-top: 20px;
        }
        
        .enpb-form-row {
            margin-bottom: 15px;
        }
        
        .enpb-form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .enpb-shortcode-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        
        .enpb-shortcode-preview code {
            flex: 1;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .enpb-copy-shortcode {
            white-space: nowrap;
        }
        
        .enpb-search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .enpb-search-results {
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .enpb-result-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        
        .enpb-result-item:hover {
            background: #f5f5f5;
        }
        
        .enpb-result-item h4 {
            margin: 0 0 5px 0;
        }
        
        .enpb-result-item p {
            margin: 0;
            color: #666;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Test connection
        $('.enpb-test-connection').on('click', function() {
            const result = $('.enpb-connection-result');
            result.removeClass('enpb-success enpb-error').text('<?php _e('Testing connection...', 'enpb'); ?>').show();
            
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'enpb_test_connection'
                },
                success: function(response) {
                    if (response.success) {
                        result.addClass('enpb-success').html('<p><strong><?php _e('Connection successful!', 'enpb'); ?></strong></p><p>' + response.data.message + '</p>');
                    } else {
                        result.addClass('enpb-error').html('<p><strong><?php _e('Connection failed!', 'enpb'); ?></strong></p><p>' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    result.addClass('enpb-error').text('<?php _e('An error occurred while testing the connection.', 'enpb'); ?>');
                }
            });
        });
        
        // Search nonprofits
        $('#enpb-search-nonprofit').on('click', function() {
            const searchTerm = $('#enpb-nonprofit-search').val();
            if (!searchTerm) {
                alert('<?php _e('Please enter a search term', 'enpb'); ?>');
                return;
            }
            
            const resultsList = $('.enpb-results-list');
            resultsList.html('<p><?php _e('Searching...', 'enpb'); ?></p>');
            $('#enpb-search-results').show();
            
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'enpb_search_nonprofits',
                    query: searchTerm
                },
                success: function(response) {
                    if (response.success && response.data.nonprofits) {
                        const nonprofits = response.data.nonprofits;
                        if (nonprofits.length === 0) {
                            resultsList.html('<p><?php _e('No nonprofits found.', 'enpb'); ?></p>');
                            return;
                        }
                        
                        let html = '';
                        nonprofits.forEach(function(nonprofit) {
                            html += `
                                <div class="enpb-result-item" data-slug="${nonprofit.slug}" data-ein="${nonprofit.ein}">
                                    <h4>${nonprofit.name}</h4>
                                    <p>${nonprofit.description || ''}</p>
                                    <small>${nonprofit.location || ''}</small>
                                </div>
                            `;
                        });
                        resultsList.html(html);
                    } else {
                        resultsList.html('<p><?php _e('Error searching nonprofits.', 'enpb'); ?></p>');
                    }
                },
                error: function() {
                    resultsList.html('<p><?php _e('Error searching nonprofits.', 'enpb'); ?></p>');
                }
            });
        });
        
        // Select nonprofit from search results
        $(document).on('click', '.enpb-result-item', function() {
            const slug = $(this).data('slug');
            $('#enpb-nonprofit-id').val(slug);
            $('#enpb-search-results').hide();
        });
        
        // Generate shortcode
        $('#enpb-generate-shortcode').on('click', function() {
            const nonprofitId = $('#enpb-nonprofit-id').val();
            if (!nonprofitId) {
                alert('<?php _e('Please enter a Nonprofit ID', 'enpb'); ?>');
                return;
            }
            
            const style = $('#enpb-style').val();
            const ctaText = $('#enpb-cta-text').val();
            const ctaColor = $('#enpb-cta-color').val();
            const showDescription = $('#enpb-show-description').is(':checked');
            const showTags = $('#enpb-show-tags').is(':checked');
            const descriptionLength = $('#enpb-description-length').val();
            
            let shortcode = `[nonprofit_profile id="${nonprofitId}"`;
            
            if (style !== 'standard') {
                shortcode += ` style="${style}"`;
            }
            
            if (ctaText !== '<?php echo esc_js(get_option('enpb_default_cta_text', __('Donate Now', 'enpb'))); ?>') {
                shortcode += ` cta_text="${ctaText}"`;
            }
            
            if (ctaColor !== '#007bff') {
                shortcode += ` cta_color="${ctaColor}"`;
            }
            
            if (!showDescription) {
                shortcode += ' show_description="false"';
            }
            
            if (!showTags) {
                shortcode += ' show_tags="false"';
            }
            
            if (descriptionLength !== '150') {
                shortcode += ` description_length="${descriptionLength}"`;
            }
            
            shortcode += ']';
            
            $('#enpb-shortcode-output').text(shortcode);
            $('.enpb-shortcode-result').show();
        });
        
        // Copy shortcode
        $('.enpb-copy-shortcode').on('click', function() {
            const shortcode = $('#enpb-shortcode-output').text();
            navigator.clipboard.writeText(shortcode).then(function() {
                const button = $(this);
                const originalText = button.text();
                button.text('<?php _e('Copied!', 'enpb'); ?>');
                setTimeout(function() {
                    button.text(originalText);
                }, 2000);
            }.bind(this));
        });
    });
    </script>
    <?php
}

/**
 * Register settings
 */
function enpb_register_settings() {
    // Register API key settings
    register_setting(
        'enpb_settings',
        ENPB_API_KEY_OPTION,
        [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]
    );
    
    register_setting(
        'enpb_settings',
        'enpb_private_key',
        [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]
    );
    
    // Add API settings section
    add_settings_section(
        'enpb_api_settings',
        __('API Settings', 'enpb'),
        'enpb_api_settings_section_callback',
        'enpb-settings'
    );
    
    // Add API key fields
    add_settings_field(
        'enpb_api_key',
        __('Public API Key', 'enpb'),
        'enpb_api_key_field_callback',
        'enpb-settings',
        'enpb_api_settings',
        [
            'label_for' => ENPB_API_KEY_OPTION,
            'class' => 'enpb-api-key-field',
        ]
    );
    
    add_settings_field(
        'enpb_private_key',
        __('Private API Key', 'enpb'),
        'enpb_private_key_field_callback',
        'enpb-settings',
        'enpb_api_settings',
        [
            'label_for' => 'enpb_private_key',
            'class' => 'enpb-private-key-field',
        ]
    );
    
    // Register display settings section
    register_setting(
        'enpb_settings',
        'enpb_default_style',
        [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'standard',
        ]
    );
    
    register_setting(
        'enpb_settings',
        'enpb_default_cta_text',
        [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Donate Now', 'enpb'),
        ]
    );
    
    add_settings_section(
        'enpb_display_section',
        __('Default Display Settings', 'enpb'),
        'enpb_display_section_callback',
        'enpb_settings'
    );
    
    add_settings_field(
        'enpb_default_style',
        __('Default Block Style', 'enpb'),
        'enpb_default_style_field',
        'enpb_settings',
        'enpb_display_section',
        [
            'label_for' => 'enpb_default_style',
            'class' => 'enpb-default-style-field',
        ]
    );
    
    add_settings_field(
        'enpb_default_cta_text',
        __('Default CTA Text', 'enpb'),
        'enpb_default_cta_text_field',
        'enpb_settings',
        'enpb_display_section',
        [
            'label_for' => 'enpb_default_cta_text',
            'class' => 'enpb-default-cta-text-field',
        ]
    );
}
add_action('admin_init', 'enpb_register_settings');

/**
 * API settings section description
 */
function enpb_api_settings_section_callback() {
    ?>
    <p>
        <?php _e('Enter your Every.org API keys below. The public key is required for searching and viewing nonprofits. The private key is required for creating fundraisers.', 'enpb'); ?>
        <a href="https://partners.every.org" target="_blank"><?php _e('Get your API keys here', 'enpb'); ?></a>.
    </p>
    <?php
}

/**
 * Display section description
 */
function enpb_display_section_callback($args) {
    ?>
    <p><?php _e('Set default display options for nonprofit profiles.', 'enpb'); ?></p>
    <?php
}

/**
 * Public API key field
 */
function enpb_api_key_field_callback($args) {
    $api_key = get_option(ENPB_API_KEY_OPTION, '');
    ?>
    <input type="text" 
           id="<?php echo esc_attr(ENPB_API_KEY_OPTION); ?>" 
           name="<?php echo esc_attr(ENPB_API_KEY_OPTION); ?>" 
           value="<?php echo esc_attr($api_key); ?>" 
           class="regular-text"
           placeholder="<?php esc_attr_e('Enter your Every.org Public API key', 'enpb'); ?>"
    />
    <p class="description">
        <?php _e('Required for searching and viewing nonprofits.', 'enpb'); ?>
    </p>
    <?php
}

/**
 * Private API key field
 */
function enpb_private_key_field_callback($args) {
    $private_key = get_option('enpb_private_key', '');
    ?>
    <input type="password" 
           id="enpb_private_key" 
           name="enpb_private_key" 
           value="<?php echo esc_attr($private_key); ?>" 
           class="regular-text"
           placeholder="<?php esc_attr_e('Enter your Every.org Private API key', 'enpb'); ?>"
    />
    <p class="description">
        <?php _e('Required for creating fundraisers. This key should be kept secure.', 'enpb'); ?>
    </p>
    <?php
}

/**
 * Default style field
 */
function enpb_default_style_field($args) {
    $default_style = get_option('enpb_default_style', 'standard');
    ?>
    <select id="enpb_default_style" name="enpb_default_style">
        <option value="standard" <?php selected($default_style, 'standard'); ?>><?php _e('Standard', 'enpb'); ?></option>
        <option value="card" <?php selected($default_style, 'card'); ?>><?php _e('Card', 'enpb'); ?></option>
        <option value="featured" <?php selected($default_style, 'featured'); ?>><?php _e('Featured', 'enpb'); ?></option>
        <option value="minimal" <?php selected($default_style, 'minimal'); ?>><?php _e('Minimal', 'enpb'); ?></option>
    </select>
    <p class="description">
        <?php _e('Choose the default display style for nonprofit profiles.', 'enpb'); ?>
    </p>
    <?php
}

/**
 * Default CTA text field
 */
function enpb_default_cta_text_field($args) {
    $default_cta_text = get_option('enpb_default_cta_text', __('Donate Now', 'enpb'));
    ?>
    <input type="text" 
           id="enpb_default_cta_text" 
           name="enpb_default_cta_text" 
           value="<?php echo esc_attr($default_cta_text); ?>" 
           class="regular-text"
    />
    <p class="description">
        <?php _e('Default text for the donation button.', 'enpb'); ?>
    </p>
    <?php
}

/**
 * AJAX handler for testing API connection
 */
function enpb_test_connection_callback() {
    $api_key = get_option(ENPB_API_KEY_OPTION);
    $private_key = get_option('enpb_private_key');
    
    if (empty($api_key)) {
        wp_send_json_error([
            'message' => __('Public API key is not set. Please enter your Every.org Public API key and save settings.', 'enpb')
        ]);
    }
    
    // Test public API key
    $url = "https://partners.every.org/v0.2/search/red-cross?apiKey={$api_key}";
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => sprintf(
                __('Error connecting to Every.org API: %s', 'enpb'),
                $response->get_error_message()
            )
        ]);
    }
    
    $status = wp_remote_retrieve_response_code($response);
    
    if ($status !== 200) {
        wp_send_json_error([
            'message' => sprintf(
                __('Error from Every.org API (Status %s): %s', 'enpb'),
                $status,
                wp_remote_retrieve_body($response)
            )
        ]);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error([
            'message' => __('Invalid response from API (JSON parse error)', 'enpb')
        ]);
    }
    
    if (!isset($data['nonprofits']) || empty($data['nonprofits'])) {
        wp_send_json_error([
            'message' => __('API connection successful, but no nonprofits found in the test query.', 'enpb')
        ]);
    }
    
    $message = sprintf(
        __('Successfully connected to Every.org API! Found %d nonprofits in test query.', 'enpb'),
        count($data['nonprofits'])
    );
    
    // If private key is set, test it too
    if (!empty($private_key)) {
        $message .= ' ' . __('Private API key is set and ready for fundraiser creation.', 'enpb');
    } else {
        $message .= ' ' . __('Private API key is not set. Fundraiser creation will not be available.', 'enpb');
    }
    
    wp_send_json_success([
        'message' => $message
    ]);
}
add_action('wp_ajax_enpb_test_connection', 'enpb_test_connection_callback');

/**
 * AJAX handler for nonprofit search
 */
function enpb_search_nonprofits_ajax() {
    // Check nonce for security
    check_ajax_referer('enpb_nonce', 'nonce');
    
    // Get search query
    $query = isset($_GET['query']) ? sanitize_text_field($_GET['query']) : '';
    if (empty($query)) {
        wp_send_json_error([
            'message' => __('Search query is required', 'enpb')
        ]);
    }
    
    // Get API key
    $api_key = get_option(ENPB_API_KEY_OPTION);
    if (empty($api_key)) {
        wp_send_json_error([
            'message' => __('API key is not set', 'enpb')
        ]);
    }
    
    // Build API URL
    $url = "https://partners.every.org/v0.2/search/{$query}?apiKey={$api_key}";
    
    // Make request
    $response = wp_remote_get($url, [
        'timeout' => 15,
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => sprintf(
                __('Error connecting to Every.org API: %s', 'enpb'),
                $response->get_error_message()
            )
        ]);
    }
    
    $status = wp_remote_retrieve_response_code($response);
    if ($status !== 200) {
        wp_send_json_error([
            'message' => sprintf(
                __('Error from Every.org API (Status %s): %s', 'enpb'),
                $status,
                wp_remote_retrieve_body($response)
            )
        ]);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error([
            'message' => __('Invalid response from API (JSON parse error)', 'enpb')
        ]);
    }
    
    if (!isset($data['nonprofits']) || !is_array($data['nonprofits'])) {
        wp_send_json_error([
            'message' => __('Invalid response structure from API', 'enpb')
        ]);
    }
    
    // Cache the results
    set_transient('enpb_search_' . md5($url), $data, 3600); // Cache for 1 hour
    
    wp_send_json_success($data);
}
add_action('wp_ajax_enpb_search_nonprofits', 'enpb_search_nonprofits_ajax');

/**
 * Enqueue admin scripts
 */
function enpb_enqueue_admin_scripts($hook) {
    // Only load on our settings page
    if ($hook !== 'settings_page_enpb-settings') {
        return;
    }
    
    wp_enqueue_script(
        'enpb-admin',
        ENPB_PLUGIN_URL . 'assets/js/admin.js',
        ['jquery'],
        ENPB_VERSION,
        true
    );
    
    wp_localize_script('enpb-admin', 'enpbAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('enpb_nonce'),
        'strings' => [
            'searching' => __('Searching...', 'enpb'),
            'noResults' => __('No nonprofits found.', 'enpb'),
            'error' => __('Error searching nonprofits.', 'enpb'),
            'copied' => __('Copied!', 'enpb'),
            'copy' => __('Copy', 'enpb'),
        ]
    ]);
}
add_action('admin_enqueue_scripts', 'enpb_enqueue_admin_scripts');
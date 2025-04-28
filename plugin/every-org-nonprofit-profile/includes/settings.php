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
                <a href="https://partners.every.org" target="_blank"><?php _e('Get your API key here', 'enpb'); ?></a>.
            </p>
        </div>
        
        <?php settings_errors('enpb_messages'); ?>
        
        <div class="enpb-admin-content">
            <div class="enpb-admin-main">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('enpb_settings');
                    do_settings_sections('enpb_settings');
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
                <?php endif; ?>
            </div>
            
            <div class="enpb-admin-sidebar">
                <div class="enpb-sidebar-widget">
                    <h3><?php _e('How to Use', 'enpb'); ?></h3>
                    <ol>
                        <li><?php _e('Enter your Every.org API key', 'enpb'); ?></li>
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
                        <li><a href="https://partners.every.org/docs" target="_blank"><?php _e('API Documentation', 'enpb'); ?></a></li>
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
        
        .enpb-connection-test {
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
    </style>
    
    <script>
    jQuery(document).ready(function($) {
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
    });
    </script>
    <?php
}

/**
 * Register settings
 */
function enpb_register_settings() {
    register_setting(
        'enpb_settings',
        ENPB_API_KEY_OPTION,
        [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]
    );
    
    add_settings_section(
        'enpb_main_section',
        __('API Settings', 'enpb'),
        'enpb_main_section_callback',
        'enpb_settings'
    );
    
    add_settings_field(
        'enpb_api_key',
        __('Public API Key', 'enpb'),
        'enpb_api_key_field',
        'enpb_settings',
        'enpb_main_section',
        [
            'label_for' => ENPB_API_KEY_OPTION,
            'class' => 'enpb-api-key-field',
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
 * Main section description
 */
function enpb_main_section_callback($args) {
    ?>
    <p><?php _e('Enter your Every.org API key to connect to their services.', 'enpb'); ?></p>
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
 * API key field
 */
function enpb_api_key_field($args) {
    $api_key = get_option(ENPB_API_KEY_OPTION, '');
    ?>
    <input type="text" 
           id="<?php echo esc_attr(ENPB_API_KEY_OPTION); ?>" 
           name="<?php echo esc_attr(ENPB_API_KEY_OPTION); ?>" 
           value="<?php echo esc_attr($api_key); ?>" 
           class="regular-text"
           placeholder="<?php esc_attr_e('Enter your Every.org API key', 'enpb'); ?>"
    />
    <p class="description">
        <?php _e('Don\'t have an API key? Get one from', 'enpb'); ?> 
        <a href="https://partners.every.org" target="_blank">Every.org Partners</a>
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
    
    if (empty($api_key)) {
        wp_send_json_error([
            'message' => __('API key is not set. Please enter your Every.org API key and save settings.', 'enpb')
        ]);
    }
    
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
    
    wp_send_json_success([
        'message' => sprintf(
            __('Successfully connected to Every.org API! Found %d nonprofits in test query.', 'enpb'),
            count($data['nonprofits'])
        )
    ]);
}
add_action('wp_ajax_enpb_test_connection', 'enpb_test_connection_callback');
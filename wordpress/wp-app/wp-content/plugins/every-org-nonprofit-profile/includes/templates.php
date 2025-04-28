<?php
/**
 * Template handling functions
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Get template HTML for the specified style
 * 
 * @param string $template_name Template name without .php extension
 * @param array $attributes Attributes for the template
 * @return string HTML output
 */
function enpb_get_template($template_name, $attributes = []) {
    // Default fallback template
    $default_template = 'nonprofit-standard';
    
    // Check if the requested template exists
    $template_file = ENPB_PLUGIN_DIR . 'templates/' . $template_name . '.php';
    
    if (!file_exists($template_file)) {
        $template_file = ENPB_PLUGIN_DIR . 'templates/' . $default_template . '.php';
    }
    
    // Extract attributes to variables for the template
    extract($attributes);
    
    // Start output buffering
    ob_start();
    include $template_file;
    return ob_get_clean();
}

/**
 * Process description text
 * 
 * @param string $description The description text
 * @param int $length Maximum length of the description
 * @return string Processed description
 */
function enpb_process_description($description, $length = 150) {
    if (empty($description)) {
        return '';
    }
    
    if (strlen($description) <= $length) {
        return $description;
    }
    
    return wp_trim_words($description, 30, '...');
}

/**
 * Format location string
 * 
 * @param string $location The location string
 * @return string Formatted location
 */
function enpb_format_location($location) {
    if (empty($location)) {
        return '';
    }
    
    return str_replace(',', ', ', $location);
}

/**
 * Create template directory and default templates on plugin activation
 */
function enpb_create_template_directory() {
    $template_dir = ENPB_PLUGIN_DIR . 'templates';
    
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
    }
    
    // Create standard template
    $standard_template = <<<'EOT'
<?php 
/**
 * Standard nonprofit profile template
 * 
 * Available variables:
 * $nonprofitId - ID of the nonprofit
 * $name - Name of the nonprofit
 * $description - Full description
 * $logoUrl - URL of the logo
 * $profileUrl - URL of the profile page
 * $coverImageUrl - URL of the cover image
 * $location - Location of the nonprofit
 * $tags - Array of tags
 * $blockStyle - Selected block style
 * $ctaText - Text for the CTA button
 * $ctaColor - Color for the CTA button
 * $showStats - Whether to show stats
 * $showTags - Whether to show tags
 * $showDescription - Whether to show description
 * $descriptionLength - Max length for description
 */

// Process description
$processed_description = $showDescription ? enpb_process_description($description, $descriptionLength) : '';

// Get donation URL
$donationUrl = enpb_generate_donate_link($nonprofitId, ['text' => $ctaText]);

// Format location
$formatted_location = enpb_format_location($location);
?>
<div class="enpb-nonprofit-profile enpb-style-standard">
    <div class="enpb-header">
        <?php if (!empty($logoUrl)) : ?>
            <div class="enpb-logo">
                <img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr($name); ?> logo" class="enpb-logo-img" />
            </div>
        <?php endif; ?>
        
        <div class="enpb-title">
            <h3 class="enpb-name"><?php echo esc_html($name); ?></h3>
            
            <?php if (!empty($formatted_location)) : ?>
                <div class="enpb-location">
                    <span class="enpb-location-icon">📍</span>
                    <span class="enpb-location-text"><?php echo esc_html($formatted_location); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($showDescription && !empty($processed_description)) : ?>
        <div class="enpb-description">
            <p><?php echo esc_html($processed_description); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($showTags && !empty($tags)) : ?>
        <div class="enpb-tags">
            <?php foreach ($tags as $tag) : ?>
                <span class="enpb-tag"><?php echo esc_html($tag); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="enpb-footer">
        <a href="<?php echo esc_url($donationUrl); ?>" 
           target="_blank" 
           rel="noopener noreferrer"
           class="enpb-donate-button"
           style="background-color: <?php echo esc_attr($ctaColor); ?>">
            <?php echo esc_html($ctaText); ?>
        </a>
        
        <?php if (!empty($profileUrl)) : ?>
            <a href="<?php echo esc_url($profileUrl); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               class="enpb-learn-more">
                <?php _e('Learn More', 'enpb'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
EOT;

    // Create card template
    $card_template = <<<'EOT'
<?php 
/**
 * Card nonprofit profile template
 */

// Process description
$processed_description = $showDescription ? enpb_process_description($description, $descriptionLength) : '';

// Get donation URL
$donationUrl = enpb_generate_donate_link($nonprofitId, ['text' => $ctaText]);

// Use cover image if available, otherwise use logo
$image_url = !empty($coverImageUrl) ? $coverImageUrl : $logoUrl;
?>
<div class="enpb-nonprofit-profile enpb-style-card">
    <?php if (!empty($image_url)) : ?>
        <div class="enpb-card-image">
            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>" />
            
            <?php if (!empty($logoUrl) && !empty($coverImageUrl)) : ?>
                <div class="enpb-card-logo">
                    <img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr($name); ?> logo" />
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="enpb-card-content">
        <h3 class="enpb-card-title"><?php echo esc_html($name); ?></h3>
        
        <?php if ($showDescription && !empty($processed_description)) : ?>
            <p class="enpb-card-description"><?php echo esc_html($processed_description); ?></p>
        <?php endif; ?>
        
        <?php if ($showTags && !empty($tags) && count($tags) > 0) : ?>
            <div class="enpb-card-tags">
                <?php 
                // Display up to 3 tags
                $displayed_tags = array_slice($tags, 0, 3); 
                foreach ($displayed_tags as $tag) : 
                ?>
                    <span class="enpb-card-tag"><?php echo esc_html($tag); ?></span>
                <?php endforeach; ?>
                
                <?php if (count($tags) > 3) : ?>
                    <span class="enpb-card-tag enpb-more-tag">+<?php echo count($tags) - 3; ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="enpb-card-actions">
            <a href="<?php echo esc_url($donationUrl); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               class="enpb-card-donate"
               style="background-color: <?php echo esc_attr($ctaColor); ?>">
                <?php echo esc_html($ctaText); ?>
            </a>
        </div>
    </div>
</div>
EOT;

    // Create featured template
    $featured_template = <<<'EOT'
<?php 
/**
 * Featured nonprofit profile template
 */

// Process description
$processed_description = $showDescription ? enpb_process_description($description, 300) : '';

// Get donation URL
$donationUrl = enpb_generate_donate_link($nonprofitId, ['text' => $ctaText]);

// Format location
$formatted_location = enpb_format_location($location);
?>
<div class="enpb-nonprofit-profile enpb-style-featured">
    <div class="enpb-featured-header">
        <?php if (!empty($coverImageUrl)) : ?>
            <div class="enpb-featured-cover" style="background-image: url('<?php echo esc_url($coverImageUrl); ?>')">
                <div class="enpb-featured-overlay"></div>
                
                <?php if (!empty($logoUrl)) : ?>
                    <div class="enpb-featured-logo">
                        <img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr($name); ?> logo" />
                    </div>
                <?php endif; ?>
                
                <h2 class="enpb-featured-name"><?php echo esc_html($name); ?></h2>
                
                <?php if (!empty($formatted_location)) : ?>
                    <div class="enpb-featured-location">
                        <span><?php echo esc_html($formatted_location); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="enpb-featured-header-simple">
                <?php if (!empty($logoUrl)) : ?>
                    <div class="enpb-featured-logo-simple">
                        <img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr($name); ?> logo" />
                    </div>
                <?php endif; ?>
                
                <div class="enpb-featured-title">
                    <h2 class="enpb-featured-name"><?php echo esc_html($name); ?></h2>
                    
                    <?php if (!empty($formatted_location)) : ?>
                        <div class="enpb-featured-location">
                            <span><?php echo esc_html($formatted_location); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="enpb-featured-content">
        <?php if ($showDescription && !empty($processed_description)) : ?>
            <div class="enpb-featured-description">
                <p><?php echo esc_html($processed_description); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($showTags && !empty($tags)) : ?>
            <div class="enpb-featured-tags">
                <?php foreach ($tags as $tag) : ?>
                    <span class="enpb-featured-tag"><?php echo esc_html($tag); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="enpb-featured-actions">
            <a href="<?php echo esc_url($donationUrl); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               class="enpb-featured-donate"
               style="background-color: <?php echo esc_attr($ctaColor); ?>">
                <?php echo esc_html($ctaText); ?>
            </a>
            
            <?php if (!empty($profileUrl)) : ?>
                <a href="<?php echo esc_url($profileUrl); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="enpb-featured-learn-more">
                    <?php _e('Learn More', 'enpb'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
EOT;

    // Create minimal template
    $minimal_template = <<<'EOT'
<?php 
/**
 * Minimal nonprofit profile template
 */

// Get donation URL
$donationUrl = enpb_generate_donate_link($nonprofitId, ['text' => $ctaText]);
?>
<div class="enpb-nonprofit-profile enpb-style-minimal">
    <div class="enpb-minimal-content">
        <?php if (!empty($logoUrl)) : ?>
            <div class="enpb-minimal-logo">
                <img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr($name); ?> logo" />
            </div>
        <?php endif; ?>
        
        <div class="enpb-minimal-info">
            <h3 class="enpb-minimal-name"><?php echo esc_html($name); ?></h3>
            
            <?php if ($showDescription && !empty($description)) : ?>
                <p class="enpb-minimal-description">
                    <?php echo esc_html(enpb_process_description($description, 100)); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="enpb-minimal-action">
            <a href="<?php echo esc_url($donationUrl); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               class="enpb-minimal-donate"
               style="color: <?php echo esc_attr($ctaColor); ?>; border-color: <?php echo esc_attr($ctaColor); ?>">
                <?php echo esc_html($ctaText); ?>
            </a>
        </div>
    </div>
</div>
EOT;

    // Write templates to files
    file_put_contents($template_dir . '/nonprofit-standard.php', $standard_template);
    file_put_contents($template_dir . '/nonprofit-card.php', $card_template);
    file_put_contents($template_dir . '/nonprofit-featured.php', $featured_template);
    file_put_contents($template_dir . '/nonprofit-minimal.php', $minimal_template);
}

// Create templates on plugin activation
register_activation_hook(ENPB_PLUGIN_DIR . 'every-org-nonprofit-profile.php', 'enpb_create_template_directory');

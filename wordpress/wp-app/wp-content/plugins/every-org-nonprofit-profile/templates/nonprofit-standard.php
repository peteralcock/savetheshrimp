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
$donationUrl = enpb_get_donation_url($profileUrl);

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
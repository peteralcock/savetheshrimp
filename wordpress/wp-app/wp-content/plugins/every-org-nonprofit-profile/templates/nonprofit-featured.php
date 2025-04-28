<?php 
/**
 * Featured nonprofit profile template
 */

// Process description
$processed_description = $showDescription ? enpb_process_description($description, 300) : '';

// Get donation URL
$donationUrl = enpb_get_donation_url($profileUrl);

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
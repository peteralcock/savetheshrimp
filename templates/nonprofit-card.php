<?php 
/**
 * Card nonprofit profile template
 */

// Process description
$processed_description = $showDescription ? enpb_process_description($description, $descriptionLength) : '';

// Get donation URL
$donationUrl = enpb_get_donation_url($profileUrl);

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
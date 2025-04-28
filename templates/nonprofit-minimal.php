<?php 
/**
 * Minimal nonprofit profile template
 */

// Get donation URL
$donationUrl = enpb_get_donation_url($profileUrl);
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
<?php
/**
 * Standard template for nonprofit profile
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure URLs are properly formatted
$profileUrl = !empty($nonprofit['profileUrl']) ? esc_url($nonprofit['profileUrl']) : '';
$websiteUrl = !empty($nonprofit['websiteUrl']) ? esc_url($nonprofit['websiteUrl']) : '';
?>

<div class="enpb-nonprofit-profile enpb-standard">
    <?php if (!empty($nonprofit['coverImageUrl'])): ?>
        <div class="enpb-cover-image">
            <img src="<?php echo esc_url($nonprofit['coverImageUrl']); ?>" alt="<?php echo esc_attr($nonprofit['name']); ?> cover image">
        </div>
    <?php endif; ?>

    <div class="enpb-content">
        <div class="enpb-header">
            <?php if (!empty($nonprofit['logoUrl'])): ?>
                <div class="enpb-logo">
                    <img src="<?php echo esc_url($nonprofit['logoUrl']); ?>" alt="<?php echo esc_attr($nonprofit['name']); ?> logo">
                </div>
            <?php endif; ?>

            <div class="enpb-title">
                <h2><?php echo esc_html($nonprofit['name']); ?></h2>
                <?php if (!empty($nonprofit['ein'])): ?>
                    <p class="enpb-ein">EIN: <?php echo esc_html($nonprofit['ein']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($nonprofit['description'])): ?>
            <div class="enpb-description">
                <?php echo wp_kses_post($nonprofit['description']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($nonprofit['descriptionLong'])): ?>
            <div class="enpb-description-long">
                <?php echo wp_kses_post($nonprofit['descriptionLong']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($nonprofit['location'])): ?>
            <div class="enpb-location">
                <h3><?php _e('Location', 'enpb'); ?></h3>
                <p><?php echo esc_html($nonprofit['location']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($nonprofit['nteeCode'])): ?>
            <div class="enpb-ntee">
                <h3><?php _e('NTEE Code', 'enpb'); ?></h3>
                <p><?php echo esc_html($nonprofit['nteeCode']); ?></p>
                <?php if (!empty($nonprofit['nteeCodeMeaning'])): ?>
                    <p class="enpb-ntee-meaning">
                        <?php echo esc_html($nonprofit['nteeCodeMeaning']['majorMeaning']); ?> &gt; 
                        <?php echo esc_html($nonprofit['nteeCodeMeaning']['decileMeaning']); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($nonprofit['tags']) && is_array($nonprofit['tags'])): ?>
            <div class="enpb-tags">
                <h3><?php _e('Causes', 'enpb'); ?></h3>
                <div class="enpb-tags-list">
                    <?php foreach ($nonprofit['tags'] as $tag): ?>
                        <?php if (is_array($tag) && isset($tag['title'])): ?>
                            <a href="<?php echo esc_url($tag['url']); ?>" class="enpb-tag" target="_blank" rel="noopener noreferrer">
                                <?php if (!empty($tag['imageUrl'])): ?>
                                    <img src="<?php echo esc_url($tag['imageUrl']); ?>" alt="<?php echo esc_attr($tag['title']); ?>" class="enpb-tag-icon">
                                <?php endif; ?>
                                <span><?php echo esc_html($tag['title']); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="enpb-actions">
            <?php if ($websiteUrl): ?>
                <a href="<?php echo $websiteUrl; ?>" class="enpb-website-button" target="_blank" rel="noopener noreferrer">
                    <?php _e('Visit Website', 'enpb'); ?>
                </a>
            <?php endif; ?>

            <?php if ($profileUrl): ?>
                <a href="<?php echo $profileUrl; ?>" class="enpb-donate-button" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html($nonprofit['ctaText']); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div> 
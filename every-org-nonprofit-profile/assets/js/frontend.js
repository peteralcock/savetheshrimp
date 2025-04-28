/**
 * Frontend JavaScript for Enhanced Every.org Nonprofit Profile
 */

(function($) {
    'use strict';
    
    // Initialize all nonprofit profiles
    function initNonprofitProfiles() {
        $('.enpb-nonprofit-profile').each(function() {
            const $profile = $(this);
            
            // Add analytics tracking to donation buttons
            $profile.find('.enpb-donate-button, .enpb-card-donate, .enpb-featured-donate, .enpb-minimal-donate').on('click', function() {
                trackDonationClick($profile.data('nonprofit-id'), $profile.data('nonprofit-name'));
            });
            
            // Add tooltips to tags
            $profile.find('.enpb-tag, .enpb-card-tag, .enpb-featured-tag').each(function() {
                const $tag = $(this);
                const tagName = $tag.text();
                
                // Don't add tooltip to the "+X" tag
                if (!tagName.startsWith('+')) {
                    $tag.addClass('enpb-tooltip');
                    $tag.append('<span class="enpb-tooltip-text">' + getTagDescription(tagName) + '</span>');
                }
            });
            
            // If this profile has donation progress data, initialize it
            if ($profile.data('donation-goal') && $profile.data('donation-current')) {
                initDonationProgress($profile);
            }
            
            // Add expandable description if needed
            if ($profile.hasClass('enpb-style-standard') || $profile.hasClass('enpb-style-card')) {
                const $description = $profile.find('.enpb-description, .enpb-card-description');
                
                if ($description.length && $description.data('full-text')) {
                    initExpandableDescription($description);
                }
            }
        });
    }
    
    // Track donation clicks (for analytics)
    function trackDonationClick(nonprofitId, nonprofitName) {
        // Check if Google Analytics exists
        if (typeof ga !== 'undefined') {
            ga('send', 'event', 'Nonprofit Profile', 'Donation Click', nonprofitName || nonprofitId);
        }
        
        // Check if Google Tag Manager exists
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                'event': 'donation_click',
                'nonprofit_id': nonprofitId,
                'nonprofit_name': nonprofitName || nonprofitId
            });
        }
    }
    
    // Initialize donation progress bar
    function initDonationProgress($profile) {
        const donationGoal = parseFloat($profile.data('donation-goal'));
        const donationCurrent = parseFloat($profile.data('donation-current'));
        
        if (isNaN(donationGoal) || isNaN(donationCurrent) || donationGoal <= 0) {
            return;
        }
        
        // Calculate percentage with max of 100%
        const percentage = Math.min(Math.round((donationCurrent / donationGoal) * 100), 100);
        
        // Create progress bar elements
        const $progressContainer = $('<div class="enpb-donation-progress-container"></div>');
        const $progressBar = $('<div class="enpb-donation-progress"><div class="enpb-donation-bar" style="width: 0%"></div></div>');
        const $progressStats = $('<div class="enpb-donation-stats"></div>');
        
        // Format currency
        const formatCurrency = function(amount) {
            return '$' + amount.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        };
        
        // Add current amount and goal
        $progressStats.append('<span>' + formatCurrency(donationCurrent) + ' raised</span>');
        $progressStats.append('<span>Goal: ' + formatCurrency(donationGoal) + '</span>');
        
        // Append to the profile
        $progressContainer.append($progressBar).append($progressStats);
        
        // Find the right place to add the progress bar based on style
        if ($profile.hasClass('enpb-style-standard')) {
            $progressContainer.insertBefore($profile.find('.enpb-footer'));
        } else if ($profile.hasClass('enpb-style-card')) {
            $progressContainer.insertBefore($profile.find('.enpb-card-actions'));
        } else if ($profile.hasClass('enpb-style-featured')) {
            $progressContainer.insertBefore($profile.find('.enpb-featured-actions'));
        } else if ($profile.hasClass('enpb-style-minimal')) {
            // For minimal style, don't show progress
            return;
        }
        
        // Animate the progress bar after a short delay
        setTimeout(function() {
            $progressBar.find('.enpb-donation-bar').css('width', percentage + '%');
        }, 300);
    }
    
    // Initialize expandable description
    function initExpandableDescription($description) {
        const shortText = $description.text();
        const fullText = $description.data('full-text');
        
        // Only proceed if there's a difference
        if (shortText === fullText) {
            return;
        }
        
        // Add expand/collapse functionality
        const $expandLink = $('<a href="#" class="enpb-expand-description">Read more</a>');
        $description.append(' ').append($expandLink);
        
        let expanded = false;
        
        $expandLink.on('click', function(e) {
            e.preventDefault();
            
            if (expanded) {
                $description.html(shortText + ' ');
                $expandLink.text('Read more');
                $description.append($expandLink);
            } else {
                $description.html(fullText + ' ');
                $expandLink.text('Show less');
                $description.append($expandLink);
            }
            
            expanded = !expanded;
        });
    }
    
    // Get description for tag (simplified version)
    function getTagDescription(tag) {
        // This could be expanded with real tag descriptions from API
        const tagDescriptions = {
            'Education': 'Organizations focused on teaching and learning',
            'Health': 'Organizations working on health and wellness',
            'Environment': 'Organizations working to protect our planet',
            'Animals': 'Organizations helping animals and wildlife',
            'Arts': 'Organizations supporting arts and culture',
            'Community': 'Organizations building stronger communities',
            'Research': 'Organizations advancing scientific research',
            'Human Rights': 'Organizations advocating for human rights',
            'Disaster Relief': 'Organizations responding to disasters',
            'Poverty': 'Organizations fighting poverty',
            'Hunger': 'Organizations addressing food insecurity',
        };
        
        return tagDescriptions[tag] || 'Organizations in this category';
    }
    
    // Add hover effects for cards
    function addCardHoverEffects() {
        $('.enpb-style-card, .enpb-style-featured').hover(
            function() {
                $(this).css('transform', 'translateY(-3px)');
                $(this).css('transition', 'transform 0.3s ease');
                $(this).css('box-shadow', '0 8px 20px rgba(0, 0, 0, 0.12)');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
                $(this).css('box-shadow', '');
            }
        );
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initNonprofitProfiles();
        addCardHoverEffects();
        
        // Also handle profiles added dynamically through AJAX
        $(document).on('enpb_profile_loaded', function() {
            initNonprofitProfiles();
            addCardHoverEffects();
        });
    });
    
})(jQuery);

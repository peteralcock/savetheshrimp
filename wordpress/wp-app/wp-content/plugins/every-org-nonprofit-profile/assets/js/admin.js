jQuery(document).ready(function($) {
    // Test connection
    $('.enpb-test-connection').on('click', function() {
        const result = $('.enpb-connection-result');
        result.removeClass('enpb-success enpb-error').text(enpbAdmin.strings.searching).show();
        
        $.ajax({
            url: enpbAdmin.ajaxurl,
            data: {
                action: 'enpb_test_connection'
            },
            success: function(response) {
                if (response.success) {
                    result.addClass('enpb-success').html('<p><strong>' + enpbAdmin.strings.success + '</strong></p><p>' + response.data.message + '</p>');
                } else {
                    result.addClass('enpb-error').html('<p><strong>' + enpbAdmin.strings.error + '</strong></p><p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                result.addClass('enpb-error').text(enpbAdmin.strings.error);
            }
        });
    });
    
    // Search nonprofits
    $('#enpb-search-nonprofit').on('click', function() {
        const searchTerm = $('#enpb-nonprofit-search').val();
        if (!searchTerm) {
            alert(enpbAdmin.strings.enterSearchTerm);
            return;
        }
        
        const resultsList = $('.enpb-results-list');
        resultsList.html('<p>' + enpbAdmin.strings.searching + '</p>');
        $('#enpb-search-results').show();
        
        $.ajax({
            url: enpbAdmin.ajaxurl,
            data: {
                action: 'enpb_search_nonprofits',
                query: searchTerm,
                nonce: enpbAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.nonprofits) {
                    const nonprofits = response.data.nonprofits;
                    if (nonprofits.length === 0) {
                        resultsList.html('<p>' + enpbAdmin.strings.noResults + '</p>');
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
                    resultsList.html('<p>' + enpbAdmin.strings.error + '</p>');
                }
            },
            error: function() {
                resultsList.html('<p>' + enpbAdmin.strings.error + '</p>');
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
            alert(enpbAdmin.strings.enterNonprofitId);
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
        
        if (ctaText !== enpbAdmin.defaultCtaText) {
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
            button.text(enpbAdmin.strings.copied);
            setTimeout(function() {
                button.text(originalText);
            }, 2000);
        }.bind(this));
    });
}); 
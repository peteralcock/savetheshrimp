/**
 * TinyMCE Button for Nonprofit Profile Shortcode
 */
(function() {
    tinymce.PluginManager.add('enpb_shortcode_button', function(editor, url) {
        editor.addButton('enpb_shortcode_button', {
            icon: 'heart',
            tooltip: 'Insert Nonprofit Profile',
            onclick: function() {
                // Show popup dialog
                const $overlay = jQuery('<div class="enpb-popup-overlay"></div>');
                jQuery('body').append($overlay);
                jQuery('#enpb-shortcode-popup').show();
            }
        });
        
        return {
            getMetadata: function() {
                return {
                    name: 'Nonprofit Profile',
                    url: 'https://every.org'
                };
            }
        };
    });
})();

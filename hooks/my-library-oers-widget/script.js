// on click event bind on .rate-this-resource-link
jQuery(document).ready(function($) {
    jQuery('.rate-this-resource-link').on('click', function(e) {
        //e.preventDefault();
        var $this = jQuery(this);
        // get id from patern id="rid-0000"
        var id = $this.attr('id').split('-')[1];
        window.oer_id = id;
        // get parent element with .rate-this-resource-container class
        var $container = $this.closest('.rate-this-resource-container');
        // get oer-title attribute from parent element
        var title = $container.attr('oer-title');
        window.oer_title = title;        
    });

    jQuery(document).on('elementor/popup/show', (event, popupId, popupDocument) => {
        jQuery('#curriki-review-title :last').text(window.oer_title);
        jQuery('#review-resource-id').val(window.oer_id);
    });
});
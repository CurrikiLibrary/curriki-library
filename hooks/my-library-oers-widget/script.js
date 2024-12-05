// on click event bind on .rate-this-resource-link
jQuery(document).ready(function($) {

    jQuery('.social-share-link').on('click', function(e) {
        e.preventDefault();
        var $this = jQuery(this);
        // get following attributes: oer-title, oer-url, oer-id
        var title = $this.attr('oer-title');
        var url = $this.attr('oer-url');
        var id = $this.attr('oer-id');
        // log to console
        var oer_share_data = {
            title: title,
            url: url,
            id: id
        };
        window.oer_share_data = oer_share_data;
    });

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

        // check if window.oer_share_data is as property
        if (window.oer_share_data) {            
            // on click event bind on #social-share-links
            var data_settings = JSON.parse(jQuery('#social-share-links').attr('data-settings'));
             
            
            // get following attributes: oer-title, oer-url, oer-id
            var title = window.oer_share_data.title;
            var url = window.oer_share_data.url;
            var id = window.oer_share_data.id;
            data_settings.share_url.url = url;
            jQuery('#social-share-links').attr('data-settings', JSON.stringify(data_settings))
            
        }
    });
});
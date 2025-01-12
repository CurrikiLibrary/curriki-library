jQuery(document).ready(function($) {
    
    jQuery('#type-filter-search-results li a').on('click', function(event) {
        // prevent default action
        event.preventDefault();
        var resourcetypefield = jQuery(this).attr('resourcetypefield');
        resourcetypefield = resourcetypefield === 'all' ? '' : resourcetypefield;
        setResourceField('resourcetypefield', resourcetypefield)
    });

    jQuery('#sort-search-results li a').on('click', function(event) {
        // prevent default action
        event.preventDefault();
        var sortfield = jQuery(this).attr('sortfield');
        setResourceField('sortfield', sortfield)
    });
    
    jQuery('.social-share-link').on('click', function(e) {
        e.preventDefault();
        var $this = jQuery(this);
        var oer_share_data = $this.attr('oer-data');
        window.oer_share_data = JSON.parse(oer_share_data);
    });

    jQuery('.more-info-link').on('click', function(e) {
        e.preventDefault();
        var $this = jQuery(this);
        // get following attributes: oer-data
        var oer_data = $this.attr('oer-data');
        window.oer_data = JSON.parse(oer_data);
    });

    jQuery(document).on('elementor/popup/show', (event, popupId, popupDocument) => {
        if (window.oer_data) {
            jQuery('#more-info-title h3').text(window.oer_data.title)
            jQuery('#more-info-subjectarea').html(window.oer_data.subsubjectarea);
            // set educationlevel
            jQuery('#more-info-educationlevel').html(window.oer_data.educationlevel);
            // set instructiontype
            jQuery('#more-info-instructiontype').html(window.oer_data.instructiontype);
            // license-image
            jQuery('#more-info-license-image img').attr('src', window.oer_data.licenseimage);
            // more-info-license-link
            jQuery('#more-info-license-link a').attr('href', window.oer_data.licenselink);
        }

        if (window.oer_share_data) {
            var data_settings = JSON.parse(jQuery('#social-share-links').attr('data-settings'));
            var title = window.oer_share_data.title;
            var url = window.oer_share_data.url;
            var id = window.oer_share_data.id;
            data_settings.share_url.url = url;
            jQuery('#social-share-links').attr('data-settings', JSON.stringify(data_settings))
        }
    });

});
<?php

function oer_page_shortcode_fun($atts) {

    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'oer-page');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));


    global $oerPageData;
    // if property exists in the $oerPageData object, return the value
    if ($oerPageData && key_exists($property, $oerPageData)) {
        $output = $oerPageData[$property];
    } else {
        $output = 'No resource data found.';
    } 

    return $output;

    /*
    // Display the result
    if ($oerPageData) {
        if ($property == 'content') {
            $output = $oerPageData->$property;
        } elseif ($property == 'pageurl') {
            $output = site_url('oer/' . $oerPageData->$property);
        } elseif ($property == 'educationlevels') {
            $resource_educationlevels = [];
            $education_levels = getOerEducationlevels();
            $oerPageData_el = $wpdb->get_results('select * from resource_educationlevels where resourceid = ' . $oerPageData->resourceid, ARRAY_A);
            if (isset($oerPageData_el) and count($oerPageData_el) > 0)
                foreach ($oerPageData_el as $r)
                    $resource_educationlevels[] = $r['educationlevelid'];

            $oer_education_levels = [];
            foreach ($education_levels as $l) {
                if ( count(array_intersect($resource_educationlevels, $l['arlevels'])) > 0 ) {
                    $oer_education_levels[] = $l['title'];
                }
            }
            $oer_education_levels = implode(', ', $oer_education_levels);
            $output = $oer_education_levels;

            // apply length limit
            if ($length > 0 && strlen($output) > $length) {
                $output = substr($output, 0, $length) . '...';
            }
        } else {
            $output = esc_html(strip_tags(html_entity_decode($oerPageData->$property)));
            // Truncate the output if it exceeds the specified length
            if ($length > 0 && strlen($output) > $length) {
                $output = substr($output, 0, $length) . '...';
            }
        }

        return $output;
    } else {
        return 'No resource data found.';
    }
    */
}

add_action('init', function() {
    add_shortcode('oer-page', 'oer_page_shortcode_fun');
});
?>
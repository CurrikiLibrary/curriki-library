<?php
require_once 'oer_shortcode_columns.php';

function oer_shortcode($atts) {
    // Define the valid columns
    $valid_columns = getOerColumns();
    $valid_metadata = getOerMetadata();
    
    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'oer');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $property = $property == 'url' ? 'pageurl' : $property;

    $slug = esc_sql($atts['slug']);
    $length = intval(trim($atts['length']));

    // Validate the 'property' attribute
    if (!in_array($property, $valid_columns) && !in_array($property, $valid_metadata)) {
        return 'Invalid property specified.';
    }

    // Ensure 'slug' is provided
    if (empty($atts['slug'])) {
        return 'OER slug is required.';
    }

    global $wpdb;
    $result = null;
    // if $property is in the $valid_columns array, query the resources table
    if (in_array($property, $valid_columns)) {
        // Query the database
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `$property` FROM `resources` WHERE `pageurl` = %s LIMIT 1",
                $slug
            )
        );
    } elseif (in_array($property, $valid_metadata)) {
        // Query the database
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `resourceid` FROM `resources` WHERE `pageurl` = %s LIMIT 1",
                $slug
            )
        );
    }

    // Display the result
    if ($result) {
        if ($property == 'content') {
            $output = $result->$property;
        } elseif ($property == 'pageurl') {
            $output = site_url('oer/' . $result->$property);
        } elseif ($property == 'educationlevels') {
            $resource_educationlevels = [];
            $education_levels = getOerEducationlevels();
            $result_el = $wpdb->get_results('select * from resource_educationlevels where resourceid = ' . $result->resourceid, ARRAY_A);
            if (isset($result_el) and count($result_el) > 0)
                foreach ($result_el as $r)
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
            $output = esc_html(strip_tags(html_entity_decode($result->$property)));
            // Truncate the output if it exceeds the specified length
            if ($length > 0 && strlen($output) > $length) {
                $output = substr($output, 0, $length) . '...';
            }
        }

        return $output;
    } else {
        return 'No resource data found.';
    }
}

?>
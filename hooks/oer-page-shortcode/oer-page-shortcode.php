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
    if ($oerPageData && $property == 'content') {
        $oerPageData_desc .= isset($oerPageData['description']) ? $oerPageData['description'] : "";
        $content = (empty($oerPageData['content']) ? $oerPageData_desc : $oerPageData['content']);
        $output = $content;
    } elseif ($oerPageData && $property == 'curriki-review') {
        $curriki_review = "";
        if (isset($oerPageData['reviewstatus']) && $oerPageData['reviewstatus'] == 'reviewed' && $oerPageData['reviewrating'] != null && $oerPageData['reviewrating'] >= 0) {
            $curriki_review = '<div class="hidden-qtip">'.__('On a scale of 0 to 3','curriki').'</div>';
        } elseif (isset($oerPageData['reviewstatus']) && $oerPageData['reviewstatus'] == 'reviewed' && $oerPageData['reviewrating'] != null && $oerPageData['reviewrating'] < 0) {
            $curriki_review = '<div class="hidden-qtip">'.__('Commented','curriki').'</div>';
        } elseif (isset($oerPageData['partner']) && $oerPageData['partner'] == 'T') {
            $curriki_review = '<div class="hidden-qtip"><strong>\'P\'</strong> - '.__('This is a trusted Partner resource','curriki').'</div>';
        } elseif (isset($oerPageData['partner']) && $oerPageData['partner'] == 'C') {
            $curriki_review = '<div class="hidden-qtip"><strong>\'C\'</strong> - '.__('Curriki rating','curriki').'</div>';
        } else {
            $curriki_review = '<div class="hidden-qtip"><strong>\'NR\'</strong> - '.__('This resource has not been rated','curriki').'</div>';
            $do_nominate = true;
        }
        $output = $curriki_review;
    } elseif ($oerPageData && $property == 'curriki-rating') {
        $curriki_rating = '';
        if (isset($oerPageData['reviewstatus']) && $oerPageData['reviewstatus'] == 'reviewed' && $oerPageData['reviewrating'] != null && $oerPageData['reviewrating'] >= 0) {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">' . $oerPageData['reviewrating'] . '/3.0</span>';
        } elseif (isset($oerPageData['reviewstatus']) && $oerPageData['reviewstatus'] == 'reviewed' && $oerPageData['reviewrating'] != null && $oerPageData['reviewrating'] < 0) {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">-</span>';
        } elseif (isset($oerPageData['partner']) && $oerPageData['partner'] == 'T') {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">P</span>';
        } elseif (isset($oerPageData['partner']) && $oerPageData['partner'] == 'C') {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">C</span>';
        } else {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">NR</span>';
        }
        $output = $curriki_rating;
    } elseif ($oerPageData && $property == 'memberrating-stars') {
        if ((int) $oerPageData['memberrating'] == 0)
            $stars = '<i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
        elseif ((int) $oerPageData['memberrating'] == 1)
            $stars = '<i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
        elseif ((int) $oerPageData['memberrating'] == 2)
            $stars = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
        elseif ((int) $oerPageData['memberrating'] == 3)
            $stars = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
        elseif ((int) $oerPageData['memberrating'] == 4)
            $stars = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i>';
        elseif ((int) $oerPageData['memberrating'] == 5)
            $stars = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
        $output = $stars;
    } elseif ($oerPageData && key_exists($property, $oerPageData) && $property == 'pageurl') {
        $output = '<a href="' . esc_url(site_url('oer/' . $oerPageData[$property])) . '">' . esc_html(esc_url(site_url('oer/' . $oerPageData[$property]))) . '</a>';
    } elseif ($oerPageData && key_exists($property, $oerPageData)) {
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
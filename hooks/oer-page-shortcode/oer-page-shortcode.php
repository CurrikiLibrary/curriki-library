<?php

function oer_page_shortcode_fun($atts) {

    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0,
        'ul_class' => '',
        'li_icon_class' => '',
        'heading_tag' => 'h4',
        'heading_class' => '',
        'heading_icon_class' => '',
    ], $atts, 'oer-page');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));
    
    global $oerPageData;
    global $oerPageDataById;
    // if property exists in the $oerPageData object, return the value
    if ($oerPageDataById && $property == 'in-collection') {
        $resource = $oerPageDataById;
        $resource_content = 'Not in collection';
        $output = '';
        // if ul_class and heading_class are set, make relevant variables with values
        $ul_class = $atts['ul_class'] ? ' class="' . esc_attr($atts['ul_class']) . '"' : '';
        $li_icon_class = $atts['li_icon_class'] ? ' class="' . esc_attr($atts['li_icon_class']) . '"' : '';
        $heading_tag = $atts['heading_tag'] ? esc_attr($atts['heading_tag']) : 'h4';
        $heading_class = $atts['heading_class'] ? ' class="' . esc_attr($atts['heading_class']) . '"' : '';
        $heading_icon_class = $atts['heading_icon_class'] ? ' class="' . esc_attr($atts['heading_icon_class']) . '"' : '';

        if (!isset($_GET['viewer']) && (isset($resource["toc_persist"]) && count($resource["toc_persist"]) > 0 || (isset($resource['collection']) && count($resource['collection']) > 0))) {
            if(!empty($resource['collections_resource_blogngs_to'])) {                
                $resource_content = '<ul'. $ul_class .'>';
                foreach($resource['collections_resource_blogngs_to'] as $resourceItem){
                    $url = site_url() . "/oer/".$resourceItem->pageurl;
                    $resource_content .= '<li><a href="' . $url . '"><i'. $li_icon_class .'></i> ' . $resourceItem->title . '</a></li>';
                }
                $resource_content .= '</ul>';
            }

            $output = $resource_content;
        }
    } elseif ($oerPageDataById && $property == 'toc') {
        $resource = $oerPageDataById;
        $resource_content = '';
        $output = '';
        // if ul_class and heading_class are set, make relevant variables with values
        $ul_class = $atts['ul_class'] ? ' class="' . esc_attr($atts['ul_class']) . '"' : '';
        $li_icon_class = $atts['li_icon_class'] ? ' class="' . esc_attr($atts['li_icon_class']) . '"' : '';
        $heading_tag = $atts['heading_tag'] ? esc_attr($atts['heading_tag']) : 'h4';
        $heading_class = $atts['heading_class'] ? ' class="' . esc_attr($atts['heading_class']) . '"' : '';
        $heading_icon_class = $atts['heading_icon_class'] ? ' class="' . esc_attr($atts['heading_icon_class']) . '"' : '';

        if (!isset($_GET['viewer']) && (isset($resource["toc_persist"]) && count($resource["toc_persist"]) > 0 || (isset($resource['collection']) && count($resource['collection']) > 0))) {
            
            $toc_persist_rids = $resource["toc_persist_rids"];
            foreach ($resource["toc_persist"] as $toc_persist) {
                //$table_of_content = $resource["resources_table_of_content"];
                $persist_rids = $toc_persist_rids;
                $table_of_content = $toc_persist;
    
                $rid = $toc_persist->main_resource_resources["resource"]->resourceid;
                //unset($persist_rids[$rid]);
                $persist_rids[] = $rid;
                $persist_rids = array_unique($persist_rids);
                $mrid = implode("-", $persist_rids);
                if ((isset($resource['collection']) && count($resource['collection']) > 0) || $table_of_content->main_resource_resources["collections"] > 0) {
                    if ($table_of_content->main_resource_resources["collections"] > 0) {
                        $resource_content .= '<' . $heading_tag . $heading_class . '><span' . $heading_icon_class . '></span> ' . htmlentities($table_of_content->main_resource_resources["resource"]->title) . '</' . $heading_tag . '>';

                        $resource_content .= '<ul' . $ul_class . '>';
                        foreach ($table_of_content->main_resource_resources["collections"] as $collection) {
                            $url_toc = get_bloginfo('url') . '/oer/' . $collection['pageurl'] . '/?mrid=' . $mrid;
                            $url_toc .= isset($_GET['oer-only']) && $_GET['oer-only'] == 'true' ? '&oer-only=true':'';

                            if (
                                isset($collection["lp_object"]) && $collection["lp_object"] !== ''
                                && isset($collection["lp_object_id"]) && $collection["lp_object_id"] > 0
                                && isset($collection["lp_course_id"]) && $collection["lp_course_id"] > 0
                            ) {
                                $url_toc .= "&lp_object=" . $collection["lp_object"] . "&lp_object_id=" . $collection["lp_object_id"] . "&lp_course_id=" . $collection["lp_course_id"];
                            }
                            $resource_content .= '<li><a href="' . $url_toc . '"><i'. $li_icon_class .'></i> ' . htmlentities($collection['title']) . '</a></li>';
                        }
                        $resource_content .= '</ul>';
                    }
                }
            }

            if (isset($resource['collection']) && count($resource['collection']) > 0) {
                $persist_rids = $toc_persist_rids;
                $rid = $resource['resourceid'];
                $persist_rids[] = $rid;
                $persist_rids = array_unique($persist_rids);
                $mrid = implode("-", $persist_rids);
                $resource_content .= '<' . $heading_tag . $heading_class . '><span' . $heading_icon_class . '></span> ' . htmlentities($resource['title']) . '</' . $heading_tag . '>';

                $resource_content .= '<ul' . $ul_class . '>';
                foreach ($resource['collection'] as $collection) {
                    $url_toc = get_bloginfo('url') . '/oer/' . $collection['pageurl'] . "/?mrid=" . $mrid;
                    $url_toc .= isset($_GET['oer-only']) && $_GET['oer-only'] == 'true' ? '&oer-only=true':'';
                    if (
                        isset($collection["lp_object"]) && $collection["lp_object"] !== ''
                        && isset($collection["lp_object_id"]) && $collection["lp_object_id"] > 0
                        && isset($collection["lp_course_id"]) && $collection["lp_course_id"] > 0
                    ) {
                        $url_toc .= "&lp_object=" . $collection["lp_object"] . "&lp_object_id=" . $collection["lp_object_id"] . "&lp_course_id=" . $collection["lp_course_id"];
                    }
                    $resource_content .= '<li><a href="' . $url_toc . '"><i' . $li_icon_class . '></i> ' . htmlentities($collection['title']) . '</a></li>';
                }
                $resource_content .= '</ul>';
            }
            $output = $resource_content;
        }
    } elseif ($oerPageData && $property == 'content') {
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
        $output = $oerPageData['memberrating'];
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
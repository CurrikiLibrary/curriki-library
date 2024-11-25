<?php

function my_library_oer_shortcode_fun($atts) {

    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'my-library-oer');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));
    
    global $myLibraryOerData;
   
    // if property exists in the $myLibraryOerData object, return the value
    if ($property == 'rate-this-resource-form') {
        require_once dirname(__DIR__) . '/core/curriki-customized.php';
        $output = curriki_library_scripts();
    } elseif ($myLibraryOerData && $property == 'custom-attributes') {
        $output = 'oer-title|' . $myLibraryOerData['title'];
        //$output .= '\noer:id' . $myLibraryOerData['resourceid'];
    } elseif ($myLibraryOerData && $property == 'resourceid') {
        $output = $myLibraryOerData['resourceid'];
    } elseif ($myLibraryOerData && $property == 'rate-this-resource-link') {
        //$library = '<a href="#rate-this-resource-popup" onclick="jQuery(\'#rate_resource-dialog\').show(); jQuery(\'#review-resource-id\').val(' . $collection->resourceid . '); jQuery(\'.curriki-review-title\').html(\'' . ($collection->title ? $collection->title : __('Go to Collection', 'curriki')) . '\'); setInterval(function () {jQuery( \'#rate_resource-dialog\' ).center_fn()}, 1);">' . __('Rate this resource', 'curriki') . '</a>';
        $output = '<a class="rate-this-resource-link" id="rid-' . $myLibraryOerData['resourceid'] . '" href="#rate-this-resource-popup">' . __('Rate this resource', 'curriki') . '</a>';
    } elseif ($myLibraryOerData && $property == 'rate-this-resource-link-script') {
        // script to click on .rate-this-resource-link and get id based on rid- prefix
        //$output = '<script>jQuery(document).ready(function() { jQuery(".rate-this-resource-link").click(function() { var rid = jQuery(this).attr("id").replace("rid-", ""); window.selected_rid = rid; }); });</script>';
        //$output = '<script>jQuery(document).ready(function() { jQuery(".rate-this-resource-link").click(function() { var rid = jQuery(this).attr("id").replace("rid-", ""); window.selected_rid = rid; }); });</script>';
    } elseif ($myLibraryOerData && $property == 'member-rating') {
        $output = $myLibraryOerData['member_rating'] ? intval($myLibraryOerData['member_rating']) : 0;
    } elseif ($myLibraryOerData && $property == 'author-url') {
        $output = esc_url($myLibraryOerData['author']['contributor_url']);
    } elseif ($myLibraryOerData && $property == 'author-link') {
        $output = '<a href="' . esc_url($myLibraryOerData['author']['contributor_url']) . '">More from this member</a>';
    } elseif ($myLibraryOerData && $property == 'author-location') {
        $output = $myLibraryOerData['author']['location'];
    } elseif ($myLibraryOerData && $property == 'author-name') {
        $output = $myLibraryOerData['author']['name'];
    } elseif ($myLibraryOerData && $property == 'createdate') {
        $output = date('M d, Y', strtotime($myLibraryOerData['createdate']));
    } elseif ($myLibraryOerData && $property == 'url') {
        $output = site_url() . '/oer/' . $myLibraryOerData['pageurl'];
    } elseif ($myLibraryOerData && $property == 'contributor') {
        $output = $myLibraryOerData['contributor'];
    } elseif ($myLibraryOerData && $property == 'title') {
        $output = $myLibraryOerData['title'];
    } elseif ($myLibraryOerData && $property == 'content') {
        $myLibraryOerData_desc .= isset($myLibraryOerData['description']) ? $myLibraryOerData['description'] : "";
        $content = (empty($myLibraryOerData['content']) ? $myLibraryOerData_desc : $myLibraryOerData['content']);
        $output = $content;
    } elseif ($myLibraryOerData && $property == 'curriki-rating') {
        // $output = $myLibraryOerData['curriki_rating']['review_rating'];
        $curriki_rating = '';
        if (isset($myLibraryOerData['curriki_rating']['reviewstatus']) && $myLibraryOerData['curriki_rating']['reviewstatus'] == 'reviewed' && $myLibraryOerData['curriki_rating']['reviewrating'] != null && $myLibraryOerData['curriki_rating']['reviewrating'] >= 0) {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">' . $myLibraryOerData['curriki_rating']['reviewrating'] . '/3.0</span>';
        } elseif (isset($myLibraryOerData['curriki_rating']['reviewstatus']) && $myLibraryOerData['curriki_rating']['reviewstatus'] == 'reviewed' && $myLibraryOerData['curriki_rating']['reviewrating'] != null && $myLibraryOerData['curriki_rating']['reviewrating'] < 0) {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">-</span>';
        } elseif (isset($myLibraryOerData['curriki_rating']['partner']) && $myLibraryOerData['curriki_rating']['partner'] == 'T') {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">P</span>';
        } elseif (isset($myLibraryOerData['curriki_rating']['partner']) && $myLibraryOerData['curriki_rating']['partner'] == 'C') {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">C</span>';
        } else {
            $curriki_rating .= '<span class="rating-points curriki-rating-title-text tooltip-rating">NR</span>';
        }
        $output = $curriki_rating;
    } elseif ($myLibraryOerData && $property == 'memberrating-stars') {
        $output = $myLibraryOerData['member_rating_stars'];
    } else {
        $output = 'No resource data found.';
    } 

    return $output;
}

add_action('init', function() {
    add_shortcode('my-library-oer', 'my_library_oer_shortcode_fun');
});
?>
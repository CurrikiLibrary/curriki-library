<?php

function oer_page_resource_shortcode_fun($atts) {

    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'oer-page-resource');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));
    
    global $oerPageResourceData;
   
    // if property exists in the $oerPageResourceData object, return the value
    if ($oerPageResourceData && $property == 'contributor') {
        $output = $oerPageResourceData['contributor'];
    } elseif ($oerPageResourceData && $property == 'title') {
        $output = $oerPageResourceData['title'];
    } elseif ($oerPageResourceData && $property == 'content') {
        $oerPageResourceData_desc .= isset($oerPageResourceData['description']) ? $oerPageResourceData['description'] : "";
        $content = (empty($oerPageResourceData['content']) ? $oerPageResourceData_desc : $oerPageResourceData['content']);
        $output = $content;
    } elseif ($oerPageResourceData && $property == 'curriki-rating') {
        $output = $oerPageResourceData['curriki_rating']['review_rating'];
    } elseif ($oerPageResourceData && $property == 'memberrating-stars') {
        $output = $oerPageResourceData['member_rating_stars'];
    } else {
        $output = 'No resource data found.';
    } 

    return $output;
}

add_action('init', function() {
    add_shortcode('oer-page-resource', 'oer_page_resource_shortcode_fun');
});
?>
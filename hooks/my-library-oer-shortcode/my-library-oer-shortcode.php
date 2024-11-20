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
    if ($myLibraryOerData && $property == 'member-rating') {
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
        $output = $myLibraryOerData['curriki_rating']['review_rating'];
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
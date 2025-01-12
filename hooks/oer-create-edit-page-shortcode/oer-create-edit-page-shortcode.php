<?php
function oer_create_edit_shortcode_fun($atts) {

    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'oer-create-edit-page');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));
    
    $output = '';
    // if property exists in the $myLibraryOerData object, return the value
    if ($property == 'wizard') {
        require_once plugin_dir_path( __FILE__ ) . 'oer-create-edit-wizard.php';
        $output = oer_create_edit_wizard();
    } elseif ($property == 'course-wizard') {
        require_once plugin_dir_path( __FILE__ ) . 'oer-course-create-edit-wizard.php';
        $output = oer_course_create_edit_wizard();
    }

    return $output;
}

add_action('init', function() {
    add_shortcode('oer-create-edit-page', 'oer_create_edit_shortcode_fun');
});
?>
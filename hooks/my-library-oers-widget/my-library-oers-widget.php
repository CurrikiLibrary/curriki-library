<?php

function register_elementor_my_library_oers_widget($widgets_manager) {
    require_once(__DIR__ . '/functions.php');
    wp_enqueue_script('my-library-oers-widget', plugins_url('script.js', __FILE__), [], '1.0.0', true);
     // Localize the script with new data
     wp_localize_script('my-library-oers-widget', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    require_once(__DIR__ . '/CurMyLibraryOersWidget.class.php');
    $widgets_manager->register_widget_type(new \CurMyLibraryOersWidget());
}
add_action('elementor/widgets/register', 'register_elementor_my_library_oers_widget');


function my_library_oers_widget_before_render( $element ) {
    if (is_object($element) && get_class($element) === 'Elementor\Widget_Icon') {
        // var_dump($element->get_settings('_css_classes'));
        global $myLibraryOerData;
        if ($myLibraryOerData['type'] === 'resource' && $element->get_settings('_css_classes') === 'collection-item') {
            $element->add_render_attribute('_wrapper', 'style', 'display: none;');
        }
        if ($myLibraryOerData['type'] === 'collection' && $element->get_settings('_css_classes') === 'resource-item') {
            $element->add_render_attribute('_wrapper', 'style', 'display: none;');
        }
    }
}
add_action( 'elementor/frontend/before_render', 'my_library_oers_widget_before_render' );

?>
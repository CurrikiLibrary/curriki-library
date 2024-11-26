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

?>
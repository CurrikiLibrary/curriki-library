<?php

function register_elementor_oer_page_widget() {
    require_once(__DIR__ . '/class-oer-page-widget.php');
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor_OER_Page_Widget());

    // require_once(__DIR__ . '/class-oer-page-query.php');
    // \Elementor\Plugin::instance()->modules_manager->get_modules('elementor-pro')->query_control_module->register_query_var(new \Elementor_OER_Page_Query());
}
add_action('elementor/init', 'register_elementor_oer_page_widget');

?>
<?php

function register_elementor_oer_page_resources_widget($widgets_manager) {
    require_once(__DIR__ . '/CurOerPageResourcesWidget.class.php');
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \CurOerPageResourcesWidget());
}
add_action('elementor/widgets/register', 'register_elementor_oer_page_resources_widget');

?>
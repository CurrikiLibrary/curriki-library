<?php

function register_elementor_oer_page_widget($widgets_manager) {
    require_once(__DIR__ . '/CurOerPageWidget.class.php');
    $widgets_manager->register( new \CurOERPageWidget() );
}
add_action('elementor/widgets/register', 'register_elementor_oer_page_widget');

?>
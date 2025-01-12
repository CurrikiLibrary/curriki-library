<?php

function register_search_page_results_widget($widgets_manager) {
    require_once(__DIR__ . '/SearchPageResultsWidget.class.php');
    $widgets_manager->register( new \SearchPageResultsWidget() );
}
add_action('elementor/widgets/register', 'register_search_page_results_widget');
?>
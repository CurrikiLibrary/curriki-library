<?php

function register_search_page_widget($widgets_manager) {
    require_once(__DIR__ . '/SearchPageWidget.class.php');
    $widgets_manager->register( new \SearchPageWidget() );
}
add_action('elementor/widgets/register', 'register_search_page_widget');

function search_page_widget_before_render($element) {
    if ("SearchPageWidget" === get_class($element)) {
        wp_enqueue_script('search-module-script', plugins_url('/core/modules/search/js/script.js', __DIR__), array('jquery'), false, true);
        wp_enqueue_script('search-page-script', plugins_url('script.js', __FILE__), array('jquery'), false, true);

        wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-bundle', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), false, true);
        require_once('search-page-functions.php');
    }
}
add_action( 'elementor/frontend/before_render', 'search_page_widget_before_render' );
?>
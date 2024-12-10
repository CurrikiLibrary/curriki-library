<?php

function oer_page_init() {
    wp_enqueue_script('oer-page-scripts', plugins_url('script.js', __FILE__), array('jquery'), false, true);
    wp_enqueue_style('qtip', plugins_url('/core/oer/assets/js/qtip2_v2.2.1/jquery.qtip.min.css', __DIR__), null, false, false);
    wp_enqueue_style('questions-css', plugins_url('/core/oer/assets/css/questions_tinymce.css', __DIR__));
    wp_enqueue_script('qtip', plugins_url('/core/oer/assets/js/qtip2_v2.2.1/jquery.qtip.min.js', __DIR__), array('jquery'), false, true);
    wp_enqueue_script('page-resource', plugins_url('/core/oer/assets/js/page-resource.js', __DIR__), array('jquery'), false, true);
    wp_enqueue_style('collection-css', plugins_url('/core/oer/assets/css/collection-page/collection.css', __DIR__));
}

function register_elementor_oer_page_widget($widgets_manager) {
    oer_page_init();
    require_once(__DIR__ . '/CurOerPageWidget.class.php');
    $widgets_manager->register( new \CurOERPageWidget() );
}
add_action('elementor/widgets/register', 'register_elementor_oer_page_widget');

function oer_toc_display_check( $element ) {
    if ($element->get_settings('_element_id') === 'oer-toc') {
        global $oerPageDataById;
        if ($oerPageDataById["type"] === "resource") {
            $element->add_render_attribute('_wrapper', 'style', 'display: none;');
        }
    }
}
add_action( 'elementor/frontend/before_render', 'oer_toc_display_check' );

function oer_page_widget_after_display( $element ) {
    if ($element->get_name() === 'oer-page-widget') {
        wp_enqueue_style('oer-custom-style', plugins_url('/core/oer/assets/js/oer-custom-script/oer-custom-style.css', dirname(__FILE__)));
        wp_enqueue_script('oer-custom-script', plugins_url('/core/oer/assets/js/oer-custom-script/oer-custom-script.js', dirname(__FILE__)), array(), false, true);
    }
}
add_action( 'elementor/frontend/after_render', 'oer_page_widget_after_display' );


?>
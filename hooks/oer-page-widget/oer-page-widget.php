<?php

function register_elementor_oer_page_widget($widgets_manager) {
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

?>
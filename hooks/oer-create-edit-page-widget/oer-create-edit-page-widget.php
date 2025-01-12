<?php

function oer_create_edit_page_after_load_scripts() {
    //Angular and Custom JS Script Loaded
    wp_register_script('ng-ctrlr', plugins_url('/core/oer/assets/js/angular_controllers.js?v=1', __DIR__));
    $translation_array = cur_angular_controllers_translations();
    wp_localize_script('ng-ctrlr', 'ml_obj', $translation_array);
    wp_enqueue_script("ng-ctrlr");
}

function oer_create_edit_page_init() {
    require_once realpath(__DIR__ . '/../') . '/core/translation-functions.php';
    wp_enqueue_style('main-css', plugins_url('/core/oer/assets/css/style.css', __DIR__));
    wp_enqueue_style('resource-legacy-css', plugins_url('/core/oer/assets/css/legacy.css', __DIR__));
    wp_enqueue_style('nprog-css',  plugins_url('/core/oer/assets/css/nprogress.css', __DIR__) , null, false, 'all'); // Add the styles first, in the <head> (last parameter false, true = bottom of page!)
    wp_enqueue_style('jquery-qtip-css', plugins_url('/core/oer/assets/js/qtip2_v2.2.1/jquery.qtip.min.css?ver=4.2.2', __DIR__));
    wp_enqueue_style('questions-css', plugins_url('/core/oer/assets/css/questions_tinymce.css', __DIR__));
    wp_enqueue_style('bootstrap-css', plugins_url('/core/oer/assets/css/questions/bootstrap.css', __DIR__));
    wp_enqueue_style('jquery-fancybox-css', plugins_url('/core/oer/assets/js/fancybox_v2.1.5/jquery.fancybox.css?v=2.1.5', __DIR__));
    
    wp_enqueue_script('angular', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js', array('jquery'), false, true);
    wp_enqueue_script('angular-sanitize', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular-sanitize.min.js', array('angular'), false, true);

    
    wp_enqueue_script('nprog-js', plugins_url('/core/oer/assets/js/nprogress.js', __DIR__), array('angular'), false, true); // Not using imagesLoaded? :( Okay... then this.
    //qtip Plugin Loaded
    
    wp_enqueue_script('jquery-qtip-js', plugins_url('/core/oer/assets/js/qtip2_v2.2.1/jquery.qtip.min.js?ver=4.2.2', __DIR__), 'jquery.qtip', '4.2.2');

    //bootstrap js
    wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', 'jquery', '2.1.5');

    
    wp_enqueue_script('jquery-fancybox-js', plugins_url('/core/oer/assets/js/fancybox_v2.1.5/jquery.fancybox.pack.js?v=2.1.5', __DIR__), 'jquery.fancybox', '2.1.5');

    wp_enqueue_script('jquery-mousewheel-js', plugins_url('/core/oer/assets/js/fancybox_v2.1.5/jquery.mousewheel-3.0.6.pack.js', __DIR__), 'jquery.mousewheel', '3.0.6');

    //Angular and tinymce plugin Loaded
    wp_enqueue_script('tinymce', plugins_url('/core/oer/assets/js/tinymce_4.3.2_jquery/tinymce.min.js', __DIR__), array('ng-ctrlr'), false, true);

    $tinymce_lang = "en";
    if (defined('ICL_LANGUAGE_CODE'))
        $tinymce_lang = ICL_LANGUAGE_CODE;

    wp_register_script('page-create-resource', plugins_url('/core/oer/assets/js/page-create-resource.js?v=1', __DIR__), array('ng-ctrlr'));
    $ml_arr_page_create_resource = array(
        'description_ml' => __('Enter descriptions to help others discover your work.', 'curriki'),
        'education_level_ml' => __('Select only the education levels that apply to your resource to help others discover your work.', 'curriki'),
        'keywords_ml' => __('Enter comma separated keywords to help others discover your work.'),
        'resource_type_ml' => __('Select the type of resource you are sharing.'),
        'alignment_ml' => __('Select alignments for your resource.'),
        'privileges_ml' => __('Select Private to keep your material in "draft" until it is ready to be released into the repository for general use.'),
        'license_ml' => __('Please be sure you have read and understand the Terms of Service and that you have the rights to contribute this content.'),
        'language_ml' => __('Select language for the content you added.'),
        'settings_ml' => __('Additional Information about your resource.', 'curriki'),
        'tinymce_lang' => $tinymce_lang
    );
    wp_localize_script('page-create-resource', 'pcr_ml_obj', $ml_arr_page_create_resource);
    wp_enqueue_script('page-create-resource');
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), false, true);
    wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', null, false, true);
}

function register_elementor_oer_create_edit_page_widget($widgets_manager) {
    require_once(__DIR__ . '/CurOerCreateEditPageWidget.class.php');
    $widgets_manager->register( new \CurOerCreateEditPageWidget() );
}
add_action('elementor/widgets/register', 'register_elementor_oer_create_edit_page_widget');

function oer_create_edit_page_after_render( $element ) {
    if (is_object($element) && get_class($element) === 'CurOerCreateEditPageWidget') {
        oer_create_edit_page_after_load_scripts();
    }
}
add_action( 'elementor/frontend/after_render', 'oer_create_edit_page_after_render' );


function oer_create_edit_page_before_render( $element ) {
    if (is_object($element) && get_class($element) === 'CurOerCreateEditPageWidget') {
        oer_create_edit_page_init();
    }
}
add_action( 'elementor/frontend/before_render', 'oer_create_edit_page_before_render' );


/*
function oer_page_widget_after_display( $element ) {
    if ($element->get_name() === 'oer-page-widget') {
        wp_enqueue_style('oer-custom-style', plugins_url('/core/oer/assets/js/oer-custom-script/oer-custom-style.css', dirname(__FILE__)));
        wp_enqueue_script('oer-custom-script', plugins_url('/core/oer/assets/js/oer-custom-script/oer-custom-script.js', dirname(__FILE__)), array(), false, true);
    }
}
add_action( 'elementor/frontend/after_render', 'oer_page_widget_after_display' );
*/

?>
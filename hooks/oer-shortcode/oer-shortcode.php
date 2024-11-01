<?php
require_once 'oer_shortcode_fun.php';
add_action('init', function() {
    if(function_exists('oer_shortcode_fun')) {
        add_shortcode('oer', 'oer_shortcode_fun');
    }
});
?>
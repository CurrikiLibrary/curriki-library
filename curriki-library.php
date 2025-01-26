<?php
/**
 * Plugin Name: Curriki Library
 * Description: Search and Explore the Open Educational Resources.
 */

if (!defined('ABSPATH')) exit;

// Check if Elementor is active
function cur_check_for_elementor_dependency() {
    // Check if Elementor is installed and active
    if (!did_action('elementor/loaded')) {
        add_action('admin_notices', 'cur_show_elementor_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate the plugin
    } else {
        require_once 'hooks/index.php';
    }
}
add_action('plugins_loaded', 'cur_check_for_elementor_dependency');

// Show an admin notice if Elementor is not active
function cur_show_elementor_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('The Curriki Library plugin requires Elementor to be installed and activated.', 'elementor-resource-query'); ?></p>
    </div>
    <?php
}

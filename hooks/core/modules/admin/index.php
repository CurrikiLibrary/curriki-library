<?php
require_once 'group_libraries/functions.php';

// Initialize Admin Menus
function init_admin_menus() {
    add_menu_page(
        __('Curriki Library', 'curriki-library'),  // Page title
        __('Curriki Library', 'curriki-library'),  // Menu title
        'manage_options',                 // Capability
        'curriki_library_admin',                  // Menu slug
        'curriki_library_admin_page',             // Callback function
        'dashicons-book',                         // Icon
        25                                        // Position
    );

    add_submenu_page(
        'curriki_library_admin',                  // Parent slug (matches menu slug from add_menu_page)
        __('Group Libraries', 'curriki-library'), // Page title
        __('Group Libraries', 'curriki-library'), // Menu title
        'manage_options',                         // Capability
        'group_libraries_options',                // Menu slug
        'group_libraries_settings_page'              // Callback function
    );
}
add_action('admin_menu', 'init_admin_menus');

// Admin Page Callback for Curriki Library
function curriki_library_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Curriki Library', 'curriki-library'); ?></h1>
        <p><?php _e('Welcome to the Curriki Library plugin admin page!', 'curriki-library'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=group_libraries_options'); ?>"><?php _e('Group Libraries', 'curriki-library'); ?></a>
    </div>
    <?php
}

?>
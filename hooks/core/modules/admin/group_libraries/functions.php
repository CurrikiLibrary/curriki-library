<?php


// Admin Page Callback for Group Libraries
function group_libraries_settings_page() {
    require_once 'view.php';
}

// Initialize Settings for Group Libraries
function group_libraries_initialize_settings() {

    register_setting(
        'group_libraries_settings_group',            // Option group
        'library_template_setting',                  // Option name for Library Template
        [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]
    );

    // Add a settings section
    add_settings_section(
        'group_libraries_settings_section',         // Section ID
        __('General Settings', 'curriki-library'),  // Section title
        'group_libraries_settings_section_callback', // Callback
        'group_libraries_options'                  // Page slug
    );

    add_settings_field(
        'library_template_setting_field',            // Field ID
        __('Template', 'curriki-library'),   // Field title
        'library_template_settings_field_callback',  // Callback
        'group_libraries_options',                  // Page slug
        'group_libraries_settings_section'          // Section ID
    );
}
add_action('admin_init', 'group_libraries_initialize_settings');

// Settings Section Callback
function group_libraries_settings_section_callback() {
    echo '<p>' . __('Configure settings for Group Libraries.', 'curriki-library') . '</p>';
}

// Library Template Field Callback
function library_template_settings_field_callback() {
    $template_options = cur_get_library_template_options();
    $value = get_option('library_template_setting', '');
    ?>
    <p class="description"><?php _e('Select template.', 'curriki-library'); ?></p>
    <!-- <input type="text" name="library_template_setting" value="<?php // echo esc_attr($value); ?>" class="regular-text"> -->
    <select name="library_template_setting">
        <?php foreach ($template_options as $key => $option) : ?>
            <option value="<?php echo $key; ?>" <?php selected($value, $key); ?>><?php echo $option; ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function cur_get_library_template_options() {
    if (class_exists('Elementor\Plugin')) {
        $templates = \Elementor\Plugin::$instance->templates_manager->get_templates();
        $options = [];
        foreach ($templates as $template) {
            $options[$template['template_id']] = $template['title'];
        }
        return $options;    
    } else {
        return [];
    }
}


?>
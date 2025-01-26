<div class="wrap">
    <h1><?php _e('Group Libraries Settings', 'curriki-library'); ?></h1>
    <form method="post" action="options.php">
        <?php
        // Output security fields for the registered setting
        settings_fields('group_libraries_settings_group');
        // Output settings sections
        do_settings_sections('group_libraries_options');
        // Submit button
        submit_button();
        ?>
    </form>
</div>
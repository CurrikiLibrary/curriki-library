<?php
function curriki_manage_db_setup() {
    global $wpdb;

    // Table name with the WordPress table prefix
    $table_name = $wpdb->prefix . 'resources_courses';

    // SQL query to create the table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        course_id INT(11) NOT NULL,
        section_id INT(11) NULL,
        lesson_id INT(11) NULL,
        quiz_id INT(11) NULL,
        course_object_type VARCHAR(50) NULL,
        resource_id INT(11) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    // Include the WordPress file to execute SQL queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);    
}
?>
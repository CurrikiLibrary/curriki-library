<?php
function handle_resource_rating() {
    global $wpdb;

    // Check if the required POST variables are set
    if (!isset($_POST['resource_id']) || !isset($_POST['rating']) || !isset($_POST['comments'])) {
        echo "Missing required parameters.";
        wp_die();
    }

    $resource_id = intval($_POST['resource_id']);
    $user_id = get_current_user_id();
    $comments = sanitize_text_field($_POST['comments']);
    $rating = intval($_POST['rating']);

    // Check if the user has already posted a review for this resource
    $q = $wpdb->prepare("SELECT resourceid FROM comments WHERE resourceid = %d AND userid = %d", $resource_id, $user_id);
    $rid = $wpdb->get_var($q);

    if ($rid > 0) {
        echo "You have already posted a review for this.";
    } else {
        // Insert the new review
        $wpdb->insert(
            'comments',
            array(
                'resourceid' => $resource_id,
                'userid' => $user_id,
                'comment' => $comments,
                'rating' => $rating,
                'commentdate' => current_time('mysql')
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%d',
                '%s'
            )
        );

        // Calculate the average rating
        $q_avg_rating = $wpdb->prepare("SELECT AVG(rating) FROM comments WHERE resourceid = %d", $resource_id);
        $avg_rating = $wpdb->get_var($q_avg_rating);

        // Update the resource with the new average rating
        $wpdb->update(
            'resources',
            array('memberrating' => $avg_rating),
            array('resourceid' => $resource_id),
            array('%d'),
            array('%d')
        );

        echo "1";
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}

add_action('wp_ajax_resource_rating', 'handle_resource_rating');
add_action('wp_ajax_nopriv_resource_rating', 'handle_resource_rating');
?>
<?php

function cur_get_resource() {
    global $wpdb;
    if (empty($_REQUEST['pageurl']))
        $_REQUEST['pageurl'] = '';
    if (empty($_REQUEST['rid']))
        $_REQUEST['rid'] = '';
    $pageurl = str_replace("/", "", $_REQUEST['pageurl']);
    $rid = str_replace("/", "", $_REQUEST['rid']);
    if ($rid != '') {
        $query = 'SELECT resourceid, title, description, pageurl FROM resources WHERE resourceid = ' . $rid;
    } else {
        $query = 'SELECT resourceid, title, description, pageurl FROM resources WHERE pageurl = "' . $pageurl . '"';
    }
    $resource = $wpdb->get_row($query);
    return $resource;
}

add_filter('wpseo_title', function($title) {
    if (is_singular()) {
        global $post;
        if ($post->post_name == 'oer') {
            $resource = cur_get_resource();
            return $resource->title . ' | ' . get_bloginfo('name');
        }
    }
    return $title;
});

add_filter('wpseo_metadesc', function($description) {
    if (is_singular()) {
        // get resource description and remove html tags
        if (is_singular()) {
            global $post;
            if ($post->post_name == 'oer') {
                $resource = cur_get_resource();
                return wp_trim_words(strip_tags($resource->description), 100);
            }
        }
    }
    return $description;
});

add_filter('wpseo_canonical', function($canonical) {
    if (is_singular()) {
        global $post;
        if ($post->post_name == 'oer') {
            $resource = cur_get_resource();
            return site_url('/oer/' . $resource->pageurl);
        }
    }
    return $canonical;
});

add_filter('wpseo_opengraph_title', function($title) {
    if (is_singular()) {
        global $post;
        if ($post->post_name == 'oer') {
            $resource = cur_get_resource();
            return $resource->title;
        }
    }
    return $title;
});

add_filter('wpseo_opengraph_desc', function($description) {
    if (is_singular()) {
        global $post;
        if ($post->post_name == 'oer') {
            $resource = cur_get_resource();
            return wp_trim_words(strip_tags($resource->description), 100);
        }
    }
    return $description;
});

add_filter('wpseo_opengraph_url', function($url) {
    if (is_singular()) {
        global $post;
        if ($post->post_name == 'oer') {
            $resource = cur_get_resource();
            return site_url('/oer/' . $resource->pageurl);
        }
    }
    return $url;
});


?>
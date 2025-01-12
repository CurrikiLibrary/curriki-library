<?php
function search_page_result_shortcode_fun($atts) {

    if (!function_exists('search_pagination_data')) {
        require_once(realpath(__DIR__ . '/../search-page-widget/search-page-functions.php'));
    }

    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'search-result');

    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));
    $output = "";
    global $searchResult;
    if ($property == 'pagination') {
        global $search;
        if (!$search->request['phrase']) {
            $output = '';
        } else {
            $pagination_data = search_pagination_data();
            $firstURL = $pagination_data['firstURL'];
            $prevURL = $pagination_data['prevURL'];
            $startPagination = $pagination_data['startPagination'];
            $endPagination = $pagination_data['endPagination'];
            $page = $pagination_data['page'];
            $url = $pagination_data['url'];
            $nextURL = $pagination_data['nextURL'];
            $lastURL = $pagination_data['lastURL'];
            $size = $pagination_data['size'];
            $pagination_html = '<ul class="search-pagination">
                                    <li class="page-item"><a class="page-link pagination-gray" href="'.$firstURL.'"><i class="fa fa-angle-double-left"></i> </a></li>
                                    <li class="page-item"><a class="page-link pagination-gray" href="'.$prevURL.'"><i class="fa fa-angle-left"></i> Back</a></li>';
                                    for ($i = $startPagination; $i < $endPagination; $i++) {
                                        $pageURL = str_replace("&start=" . $search->request['start'], "&start=" . intval($i * $size), $url);
                                        $pagination_html .= '<li class="page-item ' . ($page == $i ? 'active' : '' ) . '"><a class="page-link" href="' . $pageURL . '"> ' . intval($i + 1) . ' </a></li>';
                                    }
                                    $pagination_html .= '
                                    <li class="page-item"><span class="page-sep">...</span></li>
                                    <li class="page-item"><a class="page-link pagination-gray" href="'.$nextURL.'">Next <i class="fa fa-angle-right"></i></a></li>
                                    <li class="page-item"><a class="page-link pagination-gray" href="'.$lastURL.'"> <i class="fa fa-angle-double-right"></i></a></li>
                                </ul>';
            $output = $pagination_html;   
        }
    } elseif ($property == 'custom-attributes') {
        $oer_data = array(
            'title' => isset($searchResult["title"]) ? $searchResult["title"] : 'No title',
            'subsubjectarea' => isset($searchResult["subsubjectarea"]) ? implode('<br />', $searchResult["subsubjectarea"]) : 'No subject area',
            'educationlevel' => isset($searchResult["educationlevel"]) ? implode(', ', $searchResult["educationlevel"]) : 'No education level',
            'instructiontype' => isset($searchResult["instructiontype"]) ? implode('<br />', $searchResult["instructiontype"]) : 'No instruction type',
            'licenselink' => isset($searchResult["license"]['link']) ? $searchResult["license"]['link'] : 'No license link',
            'licenseimage' => isset($searchResult["license"]['image']) ? $searchResult["license"]['image'] : 'No license image',
            'url' => isset($searchResult["url"]) ? $searchResult["url"] : 'No URL',
        );
        $output = 'oer-data|' . json_encode($oer_data) . "\r\n";
    } elseif ($property == 'thumbnail') {
        if (isset($searchResult["thumb_image"])) {
            $img_tag = '<img decoding="async" src="' . $searchResult["thumb_image"] . '" title="' . $searchResult["title"] . '" alt="' . $searchResult["title"] . '" loading="lazy">';
        } else {
            $img_tag = 'No thumb image';
        }
        $output = $img_tag;
    } elseif ($property == 'curriki-rating') {
        $output = isset($searchResult["curriki_rating"]) ? $searchResult["curriki_rating"] : 0;
    } elseif ($property == 'member-rating') {
        $output = isset($searchResult["member_rating"]) ? $searchResult["member_rating"] : 0;
    } elseif ($property == 'description') {
        $output = isset($searchResult["description"]) ? $searchResult["description"] : 'No author description';
    } elseif ($property == 'author-avatar') {
        if (isset($searchResult["author"]["avatar"])) {
            $img_tag = '<img decoding="async" src="' . $searchResult["author"]["avatar"] . '" title="' . $searchResult["title"] . '" alt="' . $searchResult["title"] . '" loading="lazy">';
        } else {
            $img_tag = 'No thumb image';
        }
        $output = $img_tag;
    } elseif ($property == 'author-name-by') {
        $output = isset($searchResult["author"]["name"]) ? 'By ' . $searchResult["author"]["name"] : 'No author name';
    } elseif ($property == 'author-name') {
        $output = isset($searchResult["author"]["name"]) ? $searchResult["author"]["name"] : 'No author name';
    } elseif ($property == 'url') {
        $output = isset($searchResult["url"]) ? $searchResult["url"] : 'No URL';
    } elseif ($property == 'title') {
        $output = isset($searchResult["title"]) ? $searchResult["title"] : 'No title';
    } else {
        $output = 'Invalid property specified.';
    }
    return $output;
}


add_action('init', function() {
    add_shortcode('search-result', 'search_page_result_shortcode_fun');
});

?>
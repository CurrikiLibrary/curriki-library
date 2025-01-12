<?php
function search_page_shortcode_fun($atts) {
    // Extract attributes and set defaults
    $atts = shortcode_atts([
        'property' => '',
        'slug' => '',
        'length' => 0
    ], $atts, 'search-page');
    
    // Sanitize the inputs
    $property = esc_sql($atts['property']);
    $length = intval(trim($atts['length']));
    $output = "";
    if ($property == 'newsearch') {
        global $search;
        $output = stripslashes(htmlspecialchars($search->newSearchURL));
    } elseif ($property == 'searchfilters') {
        $output = '';
        ob_start();
        require_once realpath(__DIR__ . '/../../') . '/hooks/core/modules/search/views/search_advance_options_slide.php';
        $output = ob_get_clean();
    } elseif ($property == 'searchinput') {
        global $search;
        $output = '<input class="form-control" type="text" name="phrase" placeholder="Enter Keyword" value="'. $search->request['phrase'] .'">';
    } elseif ($property == 'searchformstart') {
        global $search;
        $form = ''; // '<form action="'.$search->search_page_url.'" method="GET" id="search_form" target="'.$search->search_target.'">';
                    if(!isset($search->request['type']) || $search->request['type'] == 'Resource') {
                        $form .= '<input type="hidden" name="size" value="10" >';
                    }
                    if(!isset($search->request['type']) || $search->request['type'] == 'Group') {
                        $form .= '<input type="hidden" name="size" value="16" >';
                    }
                    $form .= '<input type="hidden" name="type" id="type" value="'.(isset($search->request['type']) ? stripslashes(htmlspecialchars($search->request['type'])) : 'Resource').'">';
                    $form .= '<input type="hidden" name="start" id="start" value="'.(isset($search->request['start']) ? stripslashes(htmlspecialchars($search->request['start'])) : "0").'">';
                    $form .= '<input type="hidden" name="partnerid" id="partnerid" value="'.($search->partnerid ? stripslashes(htmlspecialchars($search->partnerid)) : '1').'" >';
                    $form .= '<input type="hidden" name="searchall" id="searchall" value="'.(isset($search->request['searchall']) ? stripslashes(htmlspecialchars($search->request['searchall'])) : "").'">';
                    $form .= '<input type="hidden" name="viewer" id="viewer" value="'.(isset($search->request['viewer']) ? stripslashes(htmlspecialchars($search->request['viewer'])) : "").'">';
                    
                    if (isset($search->request['search_target']) AND $search->request['search_target'] == 'curriki')
                        $search->branding = 'curriki';
                    
                    $form .= '<input type="hidden" name="branding" id="branding" value="'.(stripslashes(htmlspecialchars($search->branding))).'">';
                    $form .= '<input id="sortfield" type="hidden" name="sort" value="'.(isset($search->request['sort']) ? stripslashes(htmlspecialchars($search->request['sort'])) : 'rank1 desc').'">';
                    $form .= '<input id="approvalstatusfield" type="hidden" name="approvalstatus" value="'.(isset($_REQUEST['approvalstatus']) ? $_REQUEST['approvalstatus'] : '').'">';
                    $form .= '<input id="resourcetypefield" type="hidden" name="resourcetype" value="'.(isset($search->request['resourcetype']) ? $search->request['resourcetype'] : '').'">';

        $output = $form;
    } elseif ($property == 'searchphrase') {
        global $search;
        $output = $search->request['phrase'] === '' ? 'No search phrase' : $search->request['phrase'];
    } elseif ($property == 'resultcount') {
        global $search;
        $output = number_format($search->status['found'], 0);
    } else {
        $output = 'Invalid property specified.';
    }
    return $output;    
}

add_action('init', function() {
    add_shortcode('search-page', 'search_page_shortcode_fun');
});

?>
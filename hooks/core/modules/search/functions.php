<?php

function search_results_header_data() {
  global $search;

  $search_results = [
      'found' => isset($search->status['found']) ? $search->status['found'] : 0,
      'phrase' => trim(stripslashes(htmlspecialchars($search->request['phrase']))),
      'suggestedPhrase' => !empty($search->request['suggestedPhrase']) && $search->request['suggestedPhrase'] != $search->request['phrase'] ? stripslashes(htmlspecialchars($search->request['suggestedPhrase'])) : null,
      'suggestedPhraseURL' => !empty($search->request['suggestedPhraseURL']) ? $search->request['suggestedPhraseURL'] : null,
      'type' => isset($search->request['type']) ? $search->request['type'] : null,
      'compact' => isset($search->request['compact']) ? urldecode($search->request['compact']) == 'true' : false,
      'approvalstatus' => isset($_REQUEST['approvalstatus']) ? ucfirst($_REQUEST['approvalstatus']) : 'All',
      'sort' => isset($search->request['sort']) ? urldecode($search->request['sort']) : null,
      'resourcetype' => isset($search->request['resourcetype']) ? ucfirst($search->request['resourcetype']) : 'All',
      'is_admin' => isset($search->current_user->caps['administrator']),
  ];

  $search_results['sort_options'] = [
      'rank1 desc' => __('All Records', 'curriki'),
      'title asc' => __('Title [A-Z]', 'curriki'),
      'title desc' => __('Title [Z-A]', 'curriki'),
      'createdate desc' => __('Newest first', 'curriki'),
      'createdate asc' => __('Oldest first', 'curriki'),
      'memberrating desc,title asc' => __('Member rating', 'curriki'),
      'reviewrating desc,title asc' => __('Curriki rating', 'curriki'),
      'aligned desc,title asc' => __('Standards aligned', 'curriki'),
  ];

  $search_results['approval_status_options'] = [
      '' => __('All', 'curriki'),
      'approved' => __('Approved', 'curriki'),
      'rejected' => __('Rejected', 'curriki'),
      'pending' => __('Pending', 'curriki'),
  ];

  $search_results['resourcetype_options'] = [
      '' => __('All', 'curriki'),
      'resource' => __('Resource', 'curriki'),
      'collection' => __('Collection', 'curriki'),
  ];
  // Now you can use $search_results array in your custom shortcode
  return $search_results;
}

function search_input_widget_data() {
  global $search;

  $current_language = "eng";
  if (defined('ICL_LANGUAGE_CODE')) {
      $current_language = cur_get_current_language(ICL_LANGUAGE_CODE);
  }

  $language = ($current_language !== "eng") ? $current_language : "";
  if (isset($search->request['search_target']) AND $search->request['search_target'] == 'curriki')
    $search->branding = 'curriki';
  $search_input_data = [
      'current_language' => $current_language,
      'language' => $language,
      'search_library_class' => (!isset($search->request['type']) || $search->request['type'] == 'Resource') ? '' : 'collapsed',
      'accordion_class' => (!isset($search->request['type']) || $search->request['type'] == 'Resource') ? 'in' : '',
      'phrase' => isset($search->request['phrase']) ? stripslashes(htmlspecialchars($search->request['phrase'])) : '',
      'studentfacing' => isset($_REQUEST['studentfacing']) ? $_REQUEST['studentfacing'] : 'T',
      'studentfacing_checked' => isset($_REQUEST['studentfacing']) && $_REQUEST['studentfacing'] == 'T' ? 'checked="checked"' : '',
      'type' => isset($search->request['type']) ? stripslashes(htmlspecialchars($search->request['type'])) : 'Resource',
      'start' => isset($search->request['start']) ? stripslashes(htmlspecialchars($search->request['start'])) : "0",
      'partnerid' => $search->partnerid ? stripslashes(htmlspecialchars($search->partnerid)) : '1',
      'searchall' => isset($search->request['searchall']) ? stripslashes(htmlspecialchars($search->request['searchall'])) : "",
      'viewer' => isset($search->request['viewer']) ? stripslashes(htmlspecialchars($search->request['viewer'])) : "",
      'branding' => isset($search->request['search_target']) && $search->request['search_target'] == 'curriki' ? 'curriki' : stripslashes(htmlspecialchars($search->branding)),
      'sort' => isset($search->request['sort']) ? stripslashes(htmlspecialchars($search->request['sort'])) : 'rank1 desc',
      'approvalstatus' => isset($_REQUEST['approvalstatus']) ? $_REQUEST['approvalstatus'] : '',
      'resourcetype' => isset($search->request['resourcetype']) ? $search->request['resourcetype'] : '',
  ];

  // Now you can use $search_input_data array in your custom shortcode
  return $search_input_data;
}

function search_pagination_data() {
  global $search;
  $size = $search->request['size'];
  $start = $search->request['start'];
  $found = $search->status['found'] > 10000 ? 10000 : $search->status['found'];
  $page = $start ? round($start / $size) : 0;
  $pages = $found / $size;

  $startPagination = 0;
  $endPagination = 10;
  $totalPagination = 10;

  if ($page - round($totalPagination / 2) < 0) {
      $startPagination = 0;
  } else {
      $startPagination = $page - round($totalPagination / 2);
  }
  if ($page + round($totalPagination / 2) > $pages) {
      $endPagination = $pages;
  } else {
      $endPagination = $page + round($totalPagination / 2);
  }

  $url = get_bloginfo('url') . "/" . $_SERVER['REQUEST_URI'];
  if (!strpos($url, "&start=")) {
      $url .= "&start=0";
      $search->request['start'] = 0;
  }
  $firstURL = str_replace("&start=" . $search->request['start'], "&start=0", $url);
  $prevURL = str_replace("&start=" . $search->request['start'], "&start=" . intval($start - $totalPagination), $url);
  $nextURL = str_replace("&start=" . $search->request['start'], "&start=" . intval($start + $totalPagination), $url);
  $lastURL = str_replace("&start=" . $search->request['start'], "&start=" . intval($found - $totalPagination), $url);

  $pagination_data = [
      'size' => $size,
      'start' => $start,
      'found' => $found,
      'page' => $page,
      'pages' => $pages,
      'startPagination' => $startPagination,
      'endPagination' => $endPagination,
      'totalPagination' => $totalPagination,
      'url' => $url,
      'firstURL' => $firstURL,
      'prevURL' => $prevURL,
      'nextURL' => $nextURL,
      'lastURL' => $lastURL,
      'pageURLs' => []
  ];

  for ($i = $startPagination; $i < $endPagination; $i++) {
      $pageURL = str_replace("&start=" . $search->request['start'], "&start=" . intval($i * $size), $url);
      $pagination_data['pageURLs'][] = [
          'url' => $pageURL,
          'number' => intval($i + 1),
          'active' => $page == $i
      ];
  }

  // Now you can use $pagination_data array in your custom shortcode 
  return $pagination_data;
}

function search_page_userGrades($level) {
  if (!is_array($level)) return;
  global $search;
  $return = array();
  foreach ($search->educationlevels as $key => $value) {
      $levels = explode("|", $value['levelidentifiers']);
      foreach ($level as $k => $v) {
          if (in_array($v, $levels) && !in_array($v, $return)) {
              $return[] = $value['title'];
          }
      }
  }
  return implode(", ", array_unique($return));
}

function search_page_showLicense($row, $type) {
  if ($type == 'link') {
      return $row['licenseurl'];
  } else if ($type == 'image') {
      return site_url() . '/wp-content/themes/genesis-curriki/images/licenses/' . str_replace(" ", "-", $row['license']) . '.png';
  }
}

function search_page_showDescription($row) {
  global $search;
  $return = stripslashes($row['description'] ? $row['description'] : $row['title']);
  if (strlen($return) > 300)
      $return = substr($return, 0, 300) . ' <a class="underline" href="' . $search->OER_page_url . $row['url'] . '" target="_blank">more</a>';
  return $return;
}

function search_page_showContent($row) {
  global $search;
  $return = stripslashes($row['content'] ? $row['content'] : $row['title']);
  $doc = new DOMDocument();
  $return = mb_convert_encoding($return, 'HTML-ENTITIES', 'UTF-8');
  $doc->loadHTML($return);
  $return = $doc->saveHTML();
  return $return;
}

function search_result_resources_data() {
  global $search;
  global $wpdb;

  $theme_url = plugins_url('/', realpath(__DIR__ . '/../'));
  $current_language = "eng";
  if (defined('ICL_LANGUAGE_CODE')) {
    $current_language = cur_get_current_language(ICL_LANGUAGE_CODE);
  }

  $results = [];
  foreach ($search->response as $row) {
      $title = '';
      $style = '';
      if (isset($search->current_user->caps['administrator'])) {
          $style = 'background:#FFF';
          $title = 'title="Approved Resource"';
          if ($row['currentApprovalStatus'] != '' && $row['currentApprovalStatus'] != $row['approvalstatus']) {
              $style = 'border:5px dashed #7fc41a;'; // green border
              $title = 'title="Scheduled For Removal"';
          } else if ($row['approvalstatus'] == 'rejected') {
              $style = 'background:#ffbfbf'; // pink background
              $title = 'title="Rejected Resource"';
          }
          if ($row['approvalstatus'] == 'pending') {
              $style = 'background:#c9f1ff'; // sky blue background
              $title = 'title="Pending Resource"';
          }
      }

      $resourceThumbImage = $theme_url . 'images/subjects/Arts/General.jpg';
      $resourceSubject = '';
      $resourceSubjectArea = '';
      $resourceSubjectAreaExt = 'png';
      if (isset($row['subsubjectarea'])) {
          $resourceSubjectAreaArray = explode(' > ', $row['subsubjectarea'][0]);
          $resourceSubject = preg_replace('/\PL/u', '', $resourceSubjectAreaArray[0]);
          $resourceSubjectArea = preg_replace('/\PL/u', '', $resourceSubjectAreaArray[1]);

          if ($resourceSubject == 'Arts' || $resourceSubject == 'CareerTechnicalEducation') {
              $resourceSubjectAreaExt = 'jpg';
          }

          $resourceThumbImage = $theme_url . 'images/subjects/' . $resourceSubject . '/' . $resourceSubjectArea . '.' . $resourceSubjectAreaExt;
      }

      $results[] = [
          'title' => $row['title'] ? $row['title'] : "Go To Resource",
          'url' => $search->OER_page_url . $row['url'],
          'thumb_image' => $row['thumb_image'] ? urldecode($row['thumb_image']) : $resourceThumbImage,
          'description' => search_page_showDescription($row),
          'member_rating' => $row['memberrating'],
          'curriki_rating' => $row['reviewrating'],
          'author' => [
              'name' => $row['fullname'] ? $row['fullname'] : "N/A",
              'avatar' => $row['avatarfile'] ? 'https://currikicdn.s3-us-west-2.amazonaws.com/avatars/' . $row['avatarfile'] : get_stylesheet_directory_uri() . 'images/user-icon-sample.png',
              'location' => $row['userlocation'],
              'member_type' => $row['usermembertype'],
              'user_nicename' => $row['usernicename']
          ],
          'approval_status' => $row['approvalstatus'],
          'current_approval_status' => $row['currentApprovalStatus'],
          'resource_views' => intval($row['resourceviews']),
          'collections' => intval($row['collections']),
          'subsubjectarea' => $row['subsubjectarea'],
          'educationlevel' => $row['educationlevel'],
          'instructiontype' => $row['instructiontype'],
          'license' => [
              'link' => search_page_showLicense($row, 'link'),
              'image' => search_page_showLicense($row, 'image')
          ]
      ];
  }

  return $results;
}
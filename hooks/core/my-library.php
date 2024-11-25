<?php
require_once 'curriki-customized.php';
require_once 'common-functions.php';

function curriki_user_my_library() {
    global $wpdb;
    $myid = get_current_user_id();
    $q_me = "SELECT * FROM users WHERE userid = '" . $myid . "'";
    $me = $wpdb->get_row($q_me);
    $myname = $me->firstname . ' ' . $me->lastname;
    $mylocation = $me->city;
    if (!empty($mylocation))
        $mylocation .= ', ' . $me->state;
    if (!empty($mylocation))
        $mylocation .= ', ' . $me->country;
    if (!empty($me->uniqueavatarfile))
        $myphoto = "https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/" . $me->uniqueavatarfile;
    else
        $myphoto = get_stylesheet_directory_uri() . '/images/user-icon-sample.png';

    if(!empty($_GET['library_sorting'])) {
        $_SESSION['library_sorting'] = $_GET['library_sorting'];
    } elseif(!empty($_SESSION['library_sorting'])) {
        $_GET['library_sorting'] = $_SESSION['library_sorting'];
    }

    $_SESSION['library_search_phrase'] = $_GET['library_search_phrase'];

    $q_resources = cur_get_my_library_resources_query($_GET['library_sorting'], $myid, $_GET['library_search_phrase']);
    $resources = $wpdb->get_results($q_resources);
    $total_resources = count($resources);
    unset($resources);
    if (empty($_GET['page_no']) or $_GET['page_no'] < 1)
        $_GET['page_no'] = 1;
    $start = (10 * $_GET['page_no']) - 10;

    $q_resources .= " limit $start, 10";
    $resources = $wpdb->get_results($q_resources);

    $user_library = [];

    $user_library['breadcrumbs'] = [
        'Resource Library' => __('Resource Library', 'curriki'),
        'My Library' => __('My Library', 'curriki')
    ];

    $user_library['actions'] = [
        'new_collection' => get_bloginfo('url') . '/create-resource/?type=collection',
        'upload_resource' => get_bloginfo('url') . '/create-resource',
        'organize_collections' => get_bloginfo('url') . '/organize-collections',
        'search_phrase' => $_GET['library_search_phrase'],
        'sorting' => curriki_library_sorting('my', 'top', $_GET['library_sorting'])
    ];

    $user_library['resources'] = [];

    $user = wp_get_current_user();                                        
    $isAdmin = is_array($user->roles) && in_array('administrator', $user->roles) ? true : false;            
    global $wpdb;
    foreach ($resources as $collection) {
        $contributor_user = $wpdb->get_row("select * from cur_users where ID = {$collection->contributorid}");
        $title_styles = "";
        if($isAdmin && $collection->active === 'F'){
            $title_styles = " in_active_resource_tile";
        }                

        $myname = $collection->firstname . ' ' . $collection->lastname;
        $mylocation = $collection->city;
        if (!empty($mylocation))
            $mylocation .= ', ' . $collection->state;
        if (!empty($mylocation))
            $mylocation .= ', ' . $collection->country;
        if (!empty($collection->uniqueavatarfile))
            $myphoto = "https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/" . $collection->uniqueavatarfile;
        else
            $myphoto = get_stylesheet_directory_uri() . '/images/user-icon-sample.png';

        $fav_coll_q = "SELECT ce.resourceid , ce.collectionid , r.title , (SELECT rc.title FROM resources rc WHERE rc.resourceid = ce.collectionid AND rc.title = 'Favorites') as collection_title
                        FROM resources r 
                        right join collectionelements ce on ce.resourceid = r.resourceid
                            where r.resourceid = {$collection->resourceid} GROUP BY collection_title";
        $rs_coll_rcd = $wpdb->get_results($fav_coll_q);
        $cntr = 0;
        foreach ($rs_coll_rcd as $rcd) {
            if (property_exists($rcd, "collection_title") && $rcd->collection_title == NULL) {
                unset($rs_coll_rcd[$cntr]);
            }
            $cntr++;
        }

        $rs_coll_rcd = reset($rs_coll_rcd);

        $resource_data = [
            'type' => $collection->type,
            'title' => $collection->title ? $collection->title : __('Go to Collection', 'curriki'),
            'resourceid' => $collection->resourceid,
            'author' => [
                'name' => $myname,
                'location' => $mylocation,
                'photo' => $myphoto,
                'contributor_url' => get_bloginfo('url') . '/user-library/?user=' . $contributor_user->user_nicename
            ],
            'reviewstatus' => $collection->reviewstatus,
            'member_rating' => $collection->memberrating,
            'review_rating' => $collection->reviewrating,
            'contribution_date' => date('M d, Y', strtotime($collection->contributiondate)),
            'editable' => property_exists($collection, "editable") && isset($collection->editable) && $collection->editable === 'T',
            'createdate' => $collection->createdate,
            'pageurl' => $collection->pageurl,
        ];

        $user_library['resources'][] = $resource_data;
    }

    $user_library['pagination'] = library_pagination(get_bloginfo('url') . '/my-library?library_sorting=' . $_GET['library_sorting'], $_GET['page_no'], ceil($total_resources / 10));

    return $user_library;
}

/*
function curriki_user_my_library() {
    global $wpdb;
    $myid = get_current_user_id();
    $q_me = "SELECT * FROM users WHERE userid = '" . $myid . "'";
    $me = $wpdb->get_row($q_me);
    $myname = $me->firstname . ' ' . $me->lastname;
    $mylocation = $me->city;
    if (!empty($mylocation))
        $mylocation .= ', ' . $me->state;
    if (!empty($mylocation))
        $mylocation .= ', ' . $me->country;
    if (!empty($me->uniqueavatarfile))
        $myphoto = "https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/" . $me->uniqueavatarfile;
    else
        $myphoto = get_stylesheet_directory_uri() . '/images/user-icon-sample.png';
    //$q_sp = "CALL get_resource_descendants(11433, 1, 20, 'temp_res_descendants-".$myid."');";
    //$wpdb->query($q_sp);
    //$_GET['library_sorting']

    if(!empty($_GET['library_sorting'])) {
        $_SESSION['library_sorting'] = $_GET['library_sorting'];
    } elseif(!empty($_SESSION['library_sorting'])) {
        $_GET['library_sorting'] = $_SESSION['library_sorting'];
    }

    $_SESSION['library_search_phrase'] = $_GET['library_search_phrase'];

    $q_resources = cur_get_my_library_resources_query($_GET['library_sorting'], $myid, $_GET['library_search_phrase']);

    $resources = $wpdb->get_results($q_resources);
    $total_resources = count($resources);
    unset($resources);
    if (empty($_GET['page_no']) or $_GET['page_no'] < 1)
        $_GET['page_no'] = 1;
    $start = (10 * $_GET['page_no']) - 10;

    $q_resources .= " limit $start, 10";
    $resources = $wpdb->get_results($q_resources);
       
    echo '<div class="user-library-content clearfix"><div class="wrap container_12">';

    // Access
    $user_library = '';

    $user_library .= '<div class="user-library-breadcrumbs breadcrumbs grid_12">' . __('Resource Library', 'curriki') . ' > ' . __('My Library', 'curriki') . '</div>';

    $user_library .= '<div class="actions-row grid_12 clearfix">';
    $user_library .= '<div class="grid_8 alpha">';
    $user_library .= '<button class="small-button green-button" onclick="window.location=\'' . get_bloginfo('url') . '/create-resource/?type=collection\';"><span class="fa fa-search"></span> ' . __('New Collection', 'curriki') . '</button>';
    $user_library .= '<button class="small-button green-button" onclick="window.location=\'' . get_bloginfo('url') . '/create-resource\';"><span class="fa fa-plus-circle"></span> ' . __('Upload Resource', 'curriki') . '</button>';
    $user_library .= '<button class="small-button green-button" onclick="window.location=\'' . get_bloginfo('url') . '/organize-collections\'"><span class="fa fa-list"></span> ' . __('Organize Collections', 'curriki') . '</button>';
    $user_library .= '<div class="search-resources rounded-borders-full"><form action="" method="GET">';
    $user_library .= '<div class="search-input"><input name="library_search_phrase" type="text" placeholder="' . __('Start Searching', 'curriki') . '" value="' . $_GET['library_search_phrase'] . '" /></div>';
    $user_library .= '<div class="search-button rounded-borders-right"><button type="submit"><span class="search-button-icon fa fa-search"></span></button></div>';
    $user_library .= '</form></div>';
    $user_library .= '</div>';
    $user_library .= '<div class="search-dropdown grid_4 omega">' . curriki_library_sorting('my', 'top', $_GET['library_sorting']) . '</div>';
    $user_library .= '</div>';

    $user_library .= '<div class="clearfix grid_12">';

    $user = wp_get_current_user();                                        
    $isAdmin = is_array($user->roles) && in_array('administrator', $user->roles) ? true : false;            
    $library = '';
    foreach ($resources as $collection) {
        
        $title_styles = "";
        if($isAdmin && $collection->active === 'F'){
            $title_styles = " in_active_resource_tile";
        }                
        
        $myname = $collection->firstname . ' ' . $collection->lastname;
        $mylocation = $collection->city;
        if (!empty($mylocation))
            $mylocation .= ', ' . $collection->state;
        if (!empty($mylocation))
            $mylocation .= ', ' . $collection->country;
        if (!empty($collection->uniqueavatarfile))
            $myphoto = "https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/" . $collection->uniqueavatarfile;
        else
            $myphoto = get_stylesheet_directory_uri() . '/images/user-icon-sample.png';

        $fav_coll_q = "SELECT ce.resourceid , ce.collectionid , r.title , (SELECT rc.title FROM resources rc WHERE rc.resourceid = ce.collectionid AND rc.title = 'Favorites') as collection_title
                        FROM resources r 
                        right join collectionelements ce on ce.resourceid = r.resourceid
                            where r.resourceid = {$collection->resourceid} GROUP BY collection_title";
        $rs_coll_rcd = $wpdb->get_results($fav_coll_q);
        $cntr = 0;
        foreach ($rs_coll_rcd as $rcd) {
            if (property_exists($rcd, "collection_title") && $rcd->collection_title == NULL) {
                unset($rs_coll_rcd[$cntr]);
            }
            $cntr++;
        }

        $rs_coll_rcd = reset($rs_coll_rcd);

        // Collection - First Level
        $library .= '<div class="library-asset rounded-borders-full border-grey library-collection">';
        if ($collection->type == 'collection')
            $type_class = "fa-folder";
        elseif ($collection->type == 'resource')
            $type_class = "fa-image";
        else
            $type_class = "fa-folder-open";
        $library .= '<div class="library-icon"><span class="fa ' . $type_class . '"></span></div>';
        $library .= '<div class="library-title vertical-align'.$title_styles.'"><a href="' . get_bloginfo('url') . '/oer/?rid=' . $collection->resourceid . '">' . ($collection->title ? $collection->title : __('Go to Collection', 'curriki')) . '</a></div>';
        $library .= '<div class="library-author vertical-align">';
        $library .= '<img src="' . $myphoto . '" alt="member-name" />';
        $library .= '<div class="library-author-info">';
        $library .= '<span class="member-name name">' . $myname . '</span>';
        $contributor_user = $wpdb->get_row("select * from cur_users where ID = {$collection->contributorid}");
        $library .= '<span class="more-from-member name vertical-align"><a href="' . get_bloginfo('url') . '/user-library/?user=' . $contributor_user->user_nicename . '">' . __('More from this member', 'curriki') . '</a></span>';
        $library .= '<span class="location">' . $mylocation . '</span>';

        $library .= '</div>';

        $library .= '</div>';
        $library .= '<div class="library-rating rating vertical-align"><span class="member-rating-title">' . __('Member Rating', 'curriki') . '</span>';
        $library .= curriki_member_rating($collection->memberrating);
        $library .= '<a href="javascript:;" onclick="jQuery(\'#rate_resource-dialog\').show(); jQuery(\'#review-resource-id\').val(' . $collection->resourceid . '); jQuery(\'.curriki-review-title\').html(\'' . ($collection->title ? $collection->title : __('Go to Collection', 'curriki')) . '\'); setInterval(function () {jQuery( \'#rate_resource-dialog\' ).center_fn()}, 1);">' . __('Rate this resource', 'curriki') . '</a>';
        $library .= '</div>';
        $reviewrating = round($collection->reviewrating);
        if ($reviewrating > 0) {
            $library .= '<div class="library-curriki-rating curriki-rating vertical-align"><span class="curriki-rating-title">' . __('Curriki Rating', 'curriki') . '</span><span class="rating-badge">' . $reviewrating . '</span></div>';
        } elseif ($reviewrating == 0 && isset($collection->partner) && $collection->partner == 'T') {
            $library .= '<div class="library-curriki-rating curriki-rating vertical-align"><span class="curriki-rating-title">' . __('Curriki Rating', 'curriki') . '</span><span class="rating-badge-nr">P</span></div>';
        } else {
            $library .= '<div class="library-curriki-rating curriki-rating vertical-align"><span class="curriki-rating-title">' . __('Curriki Rating', 'curriki') . '</span><span class="rating-badge-nr">NR</span></div>';
        }

        $library .= '<div class="library-date vertical-align">' . date('M d, Y', strtotime($collection->contributiondate)) . '</div>';
        $library .= '<div class="library-actions vertical-align">';

        $show_edit_option = property_exists($collection, "editable") && isset($collection->editable) && $collection->editable === 'T' ? true : false;
        if ($show_edit_option) {
            $library .= '<a href="' . get_bloginfo('url') . '/create-resource/?resourceid=' . $collection->resourceid . '"><span class="fa fa-edit"></span> <span>' . __('Edit', 'curriki') . '</span></a>';
        }
        // if ($collection->Favorite != 'Contributions')
        //     $library .= '<a href="#" onclick="remove_collection(' . (property_exists($rs_coll_rcd, "resourceid") && $rs_coll_rcd->resourceid ? $rs_coll_rcd->resourceid : 0) . ',' . (property_exists($rs_coll_rcd, "collectionid") && $rs_coll_rcd->collectionid ? $rs_coll_rcd->collectionid : 0) . ',this)"><span class="fa fa-trash"></span> <span>' . __('Remove', 'curriki') . '</span></a>';
        // $library .= '<a href="' . get_bloginfo('url') . '/create-resource/?resourceid=' . $collection->resourceid . '&copy=1"><span class="fa fa-copy"></span> <span>' . __('Duplicate', 'curriki') . '</span></a>';

        if ($collection->type == 'collection')
            $library .= '<a href="javascript:;" class="organize-collections-title" id="organize-collections-title-'.$collection->resourceid.'"><span class="fa fa-list"></span> <span>' . __('Organize', 'curriki') . '</span></a>';

        $library .= curriki_sharethis($collection->resourceid, ($collection->title ? $collection->title : __('Go to Collection', 'curriki')));
        $library .= '</div>';
        $library .= '</div>';
    }

    // Resource - Closed Folder - Second Level			
    $user_library .= $library;

    $user_library .= '</div>';

    $user_library .= library_pagination(get_bloginfo('url') . '/my-library?library_sorting=' . $_GET['library_sorting'], $_GET['page_no'], ceil($total_resources / 10));

    $user_library .= '<div class="actions-row grid_12 clearfix">';
    $user_library .= '<div class="grid_8 alpha">';
    // $user_library .= '<button class="small-button green-button" onclick="window.location=\'' . get_bloginfo('url') . '/create-resource/?type=collection\';"><span class="fa fa-search"></span> ' . __('New Collection', 'curriki') . '</button>';
    // $user_library .= '<button class="small-button green-button" onclick="window.location=\'' . get_bloginfo('url') . '/create-resource\';"><span class="fa fa-plus-circle"></span> ' . __('Upload Resource', 'curriki') . '</button>';
    //    $user_library .= '<button class="small-button green-button" onclick="window.location=\'' . get_bloginfo('url') . '/organize-collections\'"><span class="fa fa-list"></span> ' . __('Organize Collections', 'curriki') . '</button>';
    $user_library .= '</div>';
    $user_library .= '<div class="search-dropdown grid_4 omega">' . curriki_library_sorting('my', 'bottom', $_GET['library_sorting']) . '</div>';
    $user_library .= '</div>';

    echo $user_library;

    echo '</div></div>';
}*/
?>
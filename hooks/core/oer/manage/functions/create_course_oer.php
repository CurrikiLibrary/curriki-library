<?php
add_action('wp_ajax_nopriv_create_resource_course', 'ajax_create_resource_course');
add_action('wp_ajax_create_resource_course', 'ajax_create_resource_course');

function ajax_create_resource_course() {

    $resource_course_data = array('course_id' => null, 'section_id' => null, 'lesson_id' => null, 'quiz_id' => null, 'course_object_type' => null, 'resource_id' => null);
    if (isset($_REQUEST['course'])) {
        $resource_course_data['course_id'] = $_REQUEST['course'];
        $_REQUEST['resource_type'] = 'collection';
    }
    if (isset($_REQUEST['section'])) {
        $resource_course_data['section_id'] = $_REQUEST['section'];
        $_REQUEST['resource_type'] = 'collection';
    }
    if (isset($_REQUEST['lesson'])) {
        $resource_course_data['lesson_id'] = $_REQUEST['lesson'];
    }
    if (isset($_REQUEST['quiz'])) {
        $resource_course_data['quiz_id'] = $_REQUEST['quiz'];
    }
    if (isset($_REQUEST['course_object_type'])) {
        $resource_course_data['course_object_type'] = $_REQUEST['course_object_type'];
    }

    $apiCall = false;
    if (isset($_SESSION['api_call'] )) {
       $submit = true;
       $apiCall = true;
	   unset($_SESSION['api_call']);
    }

    $submit = true;
    $apiCall = true;
    if(!$submit){
        echo json_encode(['msg'=>'Wrong Data']);
        wp_die();
    }
    
    $curriki_recommender = isset($GLOBALS['curriki_recommender']) ? $GLOBALS['curriki_recommender'] : null;    
    $site_url = (is_ssl() ? "https://":"http://") . $_SERVER["HTTP_HOST"];    
    ob_clean();
    global $wpdb;    
    $data = array();
    $gkeywords = array();
    $current_user = wp_get_current_user();

    // ensure create mode
    $_REQUEST['resourceid'] = '';

    ///$current_user->ID = 10000;
    $_REQUEST['education_levels'] = explode('|', implode('|', (isset($_REQUEST['education_levels']) ? $_REQUEST['education_levels'] : array())));

    if (isset($_REQUEST['subjectarea']) && is_array($_REQUEST['subjectarea']))
        foreach ($wpdb->get_results("SELECT * from subjectareas where subjectareaid in (" . implode(',', $_REQUEST['subjectarea']) . ")", ARRAY_A) as $row)
            $gkeywords[] .= $row['displayname'];

    if (isset($_REQUEST['education_levels']) && is_array($_REQUEST['education_levels']))
    {
        $in_keys = implode(',', $_REQUEST['education_levels']);        
        if( strlen($in_keys) > 0){
            foreach ($wpdb->get_results("SELECT * from educationlevels where levelid in (" . $in_keys . ")", ARRAY_A) as $row)
            {
                $gkeywords[] .= 'Grade ' . $row['identifier'];
            }
        }
    }
    
    $resourceTitle = str_replace(array('\"', "\'", '\/'), array('"', "'", '/'), $_REQUEST['title']);
    $resourceDescription = str_replace(array('\"', "\'", '\/'), array('"', "'", '/'), $_REQUEST['description']);
    $resourceKeywords = str_replace(array('\"', "\'", '\/'), array('"', "'", '/'), $_REQUEST['keywords']);
    $contents = str_replace(array('\"', "\'", '\/'), array('"', "'", '/'), $_REQUEST['content']);

    $data['licenseid'] = $_REQUEST['licenseid'];
    $data['description'] = trim($resourceDescription);
    $data['title'] = substr(trim($resourceTitle), 0, 499);
    $data['keywords'] = trim($resourceKeywords);
    $data['generatedkeywords'] = $resourceTitle . ' ' . implode(',', $gkeywords); //concatenate title, each subjectarea and each 'grade' + educationlevel
    $data['language'] = substr($_REQUEST['language'], 0, 3);
    $data['content'] = $contents;
    $data['mediatype'] = substr($_REQUEST['mediatype'], 0, 10); //you'll have to determine from what is entered in the tinymce.  Options are (audio, document, image, external (link), text, video, mixed).  If only one of those are entered, then use that type.  If more than one type is entered use 'mixed'
    $data['aligned'] = (isset($_REQUEST['statements']) && is_array($_REQUEST['statements']) && count($_REQUEST['statements']) ? 'T' : 'F');
    $data['access'] = substr($_REQUEST['access'], 0, 10);
    $data['studentfacing'] = (isset($_REQUEST['studentfacing']) ? 'T' : 'F');
    $data['topofsearch'] = (isset($_REQUEST['topofsearch']) ? 'T' : 'F');
    $data['partner'] = (isset($_REQUEST['partner']) ? 'T' : 'F');
    $data['lasteditorid'] = $current_user->ID;
    $data['lasteditdate'] = date('Y-m-d H:i:s');
    $data['approvalStatus'] = 'pending';
    $data['approvalStatusDate'] = date('Y-m-d H:i:s');
    
    if (isset($current_user->caps['administrator']) || $apiCall) {
        $data['approvalStatus'] = 'approved';
        $data['active'] = (isset($_REQUEST['active']) ? 'T' : 'F');
    } else {
        $data['active'] = 'T';
    }

    //BP Activity log values
    $component = "resources";
    $profile_url = $site_url . "/members/" . $current_user->data->user_nicename;
    $user_display_name = $current_user->data->display_name;
    $resource_activity_content = $resourceDescription;

    if (isset($_REQUEST['resourceid']) && !empty($_REQUEST['resourceid'])) {
        $res_id = $_REQUEST['resourceid'];
        //        $resource = $wpdb->get_row("SELECT * FROM resources where resourceid = '" . $res_id . "'");       //prepared statement added
        $resource = $wpdb->get_row( $wpdb->prepare( 
                "
                        SELECT * FROM resources where resourceid = %d
                ", 
                $res_id
        ));
    }

    
    if (!empty($resource) && ($resource->contributorid == $current_user->ID OR $current_user->caps['administrator'] OR in_array("resourceEditor", $current_user->roles))) {
        //=== reset cloud index if user is editing spam resouces ===
        if($resource->spam === 'T'){
            $data['spam'] = 'F';
            $data['remove'] = 'F';
            $data['indexrequired'] = 'T';
            $data['indexrequireddate'] = date('Y-m-d H:i:s');
            $data['active'] = 'T';
        }
        
        // === preparing query types ===
        foreach ($data as $i => $v) {
            $type[] = '%s';
            $data[$i] = stripcslashes($v);
        }

        $wpdb->update('resources', $data, array('resourceid' => $res_id), $type);
        if($resource->type === "collection"){            
            $data['enable-user-registration'] = isset($_REQUEST['enable-user-registration']) && $_REQUEST['enable-user-registration'] == 1 ? 1 : 0;
            oerEnableUserRegistration($res_id, $data, 'collection', "program");
        }
        $wpdb->delete('resource_subjectareas', array('resourceid' => $res_id));
        $wpdb->delete('resource_educationlevels', array('resourceid' => $res_id));
        $wpdb->delete('resource_instructiontypes', array('resourceid' => $res_id));
        $wpdb->delete('resource_statements', array('resourceid' => $res_id));
        $wpdb->delete('lti_resources', array('resourceid' => $res_id));
        $wpdb->delete('resource_thumbs', array('resourceid' => $res_id));
        //$wpdb->delete('resourcefiles', array('resourceid' => $res_id));

        $pageurl = $data['pageurl'] = $resource->pageurl;
        $bpActType = "resource_udpate";
        $resource_activity_title = '<a href="' . $site_url . '/oer/' . $data['pageurl'] . '">' . $resourceTitle . '</a>';
        $bpActAction = '<a href="' . $profile_url . '">' . $user_display_name . '</a> updated ' . $resource_activity_title;
    } else {
        $data['pageurl'] = $data['title'] ? $data['title'] : substring($data['description'], 1, 30);
        $data['pageurl'] = substr($data['pageurl'] = str_replace(array(' ', ',', ':', '.', '(', ')', '\'', '?', '/', '+', '\\', '--', '&', '#', '"'), array('-', '-', '-', '', '-', '-', '-', '-', '-', '-', '-', '-', 'and', '-', '-'), $data['pageurl']), 0, 499); //If this result is not unique across all resources then concat (the above, '-', cast(resourceid as char(30))) which puts the resourceid at the end and will guarantee uniqueness.
        $data['contributorid'] = $current_user->ID;
        $data['contributiondate'] = date('Y-m-d H:i:s');
        $data['createdate'] = date('Y-m-d H:i:s');
        $data['type'] = substr($_REQUEST['resource_type'], 0, 10);
        foreach ($data as $i => $v)
            $type[] = '%s';

        $wpdb->insert('resources', $data, $type);
        
        $res_id = $wpdb->insert_id;

        if($data['type'] === "collection"){
            $data['enable-user-registration'] = isset($_REQUEST['enable-user-registration']) && $_REQUEST['enable-user-registration'] == 1 ? 1 : 0;
            oerEnableUserRegistration($res_id, $data, $data['type'], "program");
        }        

        $dup_title = $wpdb->get_row("SELECT count(*) CNT FROM resources where pageurl = '" . $data['pageurl'] . "' and  resourceid != '" . $res_id . "'")->CNT;
        if ($dup_title) {
            $data['pageurl'] = $data['pageurl'] . '-' . $res_id;
            $wpdb->update('resources', array('pageurl' => $data['pageurl']), array('resourceid' => $res_id));
        }
        $pageurl = $data['pageurl'];
        $bpActType = "resource_insert";
        $resource_activity_title = '<a href="' . $site_url . '/oer/' . $pageurl . '">' . $resourceTitle . '</a>';
        $bpActAction = '<a href="' . $profile_url . '">' . $user_display_name . '</a> created ' . $resource_activity_title;

        if (isset($_REQUEST['groupid']) && intval($_REQUEST['groupid']) > 0)
            $wpdb->insert('group_resources', array('groupid' => intval($_REQUEST['groupid']), 'resourceid' => $res_id), array('%d', '%d'));

        if (isset($_REQUEST['prid']) && intval($_REQUEST['prid']) > 0)
            $wpdb->insert('collectionelements', array('collectionid' => intval($_REQUEST['prid']), 'resourceid' => $res_id), array('%d', '%d'));
    }

    //===== CHecking for spam ====
    foreach ($wpdb->get_results("SELECT phrase FROM censorphrases", ARRAY_A) as $word) {
        if( is_phrase_consored($data['title'], trim($word['phrase'])) || is_phrase_consored($data['content'],  trim($word['phrase'])) || is_phrase_consored($data['keywords'], trim($word['phrase'])) ) {
                        
            // Prepared statement added            
            $wpdb->query($wpdb->prepare(
                    "
                        update resources
                        set spam = 'T',
                        remove = 'T',
                        indexrequired = 'T',
                        indexrequireddate = current_timestamp(),
                        active = 'F'
                        where resourceid = %d
                    ", $res_id
            ));
            //echo $wpdb->last_query;
            break;
        }
    }

    //Adding User Activity Log
    if ($data['access'] != 'private') {
        $activity_id = bp_activity_add(array(
            'action' => $bpActAction,
            'content' => $resource_activity_content,
            'component' => $component,
            'type' => $bpActType,
        ));
    }

    //Setting Subject Areas
    $resource_subjectareas_list = [];
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['subjectarea']) && is_array($_REQUEST['subjectarea'])){
        foreach ($_REQUEST['subjectarea'] as $row) {
            $data['subjectareaid'] = $row;
            $resource_subjectareas_list[] = $data;
            $wpdb->insert('resource_subjectareas', $data);
        }
    }    
    
    //Setting Education Levels
    $resource_educationlevels_list = [];
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['education_levels']) && is_array($_REQUEST['education_levels'])){
        foreach ($_REQUEST['education_levels'] as $row) {
            if( strlen($row) > 0 ){
                $data['educationlevelid'] = $row;
                $resource_educationlevels_list[] = $data;
                $wpdb->insert('resource_educationlevels', $data);
            }
        }
    }            
    
    //Setting Instruction Types
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['instructiontypes']) && is_array($_REQUEST['instructiontypes']))
        foreach ($_REQUEST['instructiontypes'] as $row) {
            $data['instructiontypeid'] = $row;
            $wpdb->insert('resource_instructiontypes', $data);
        }

    //Setting Statements
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['statements']) && is_array($_REQUEST['statements']))
        foreach ($_REQUEST['statements'] as $row) {
            $data['statementid'] = $row;
            $data['alignmentdate'] = date('Y-m-d H:i:s');
            $data['userid'] = $current_user->ID;
            $wpdb->insert('resource_statements', $data);
        }
        
        
    //Inserting LTI resource
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['lti_id']) && isset($_REQUEST['type_id'])) {
        $data['type_id'] = $_REQUEST['type_id'];
        $data['lti_id'] = $_REQUEST['lti_id'];
        $wpdb->insert('lti_resources', $data);
    }
    
    //Inserting Resource Thumbnail
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['resource_thumb_hidden'])) {
        $data['thumb_image'] = $_REQUEST['resource_thumb_hidden'];
        $wpdb->insert('resource_thumbs', $data);
    }


    //Questions
    
        
    $res_content = $wpdb->get_results($wpdb->prepare("SELECT content FROM resources WHERE resourceid = %d",  $res_id))[0]->content;

    $newDom = new DOMDocument();
    libxml_use_internal_errors(TRUE); //disable libxml errors

    // $newDom->loadHTML(mb_convert_encoding($contents, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $newDom->loadHTML(htmlentities($contents, ENT_QUOTES, 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    libxml_clear_errors();

    $main_xpath = new DOMXPath(@$newDom);
    if (!$main_xpath) {
        echo "\nCouldnt find main_xpath\n";
        die();
    }
    
    $question_wrapper = $main_xpath->query('//form[contains(@class, "question_front_form")]');
    
    if($question_wrapper->length > 0) {
        
        $res_content_new = putQuestionData($question_wrapper, $newDom, $main_xpath, $res_id);
        if($res_content_new != null){
            $res_content = $res_content_new;
        }
        
    }
    


    //Setting ResourceFiles
    include_once(dirname(dirname(__DIR__)) . '/libs/functions.php');
    $data = array('resourceid' => $res_id);
    if (isset($_REQUEST['resourcefiles']) && is_array($_REQUEST['resourcefiles'])) {
        $resourcefiles_list = [];
        foreach ($_REQUEST['resourcefiles'] as $row) {
            $file = json_decode(stripcslashes($row));
            if (!strpos($contents, $file->url) && !strpos($contents, $file->s3path)) {
                // Delete Normal File
                deleteFileS3((array) $file);
                // Delete Video Files, Poster, Transcoded Stuff [Pending]
                // Delete LodeStar Unzipped Folder [Pending]
                $wpdb->delete('resourcefiles', array('fileid' => intval($file->fileid)), array('%d'));
            } elseif (!$file->fileid) {
                //                print_r($file);
                $data['fileid'] = NULL;
                $data['filename'] = substr($file->filename, 0, 500);
                $data['uploaddate'] = $file->uploaddate ? $file->uploaddate : date('Y-m-d H:i:s');
                $data['uniquename'] = substr($file->uniquename, 0, 500);
                $data['ext'] = substr($file->ext, 0, 10);
                $data['active'] = "T";
                $data['folder'] = substr($file->folder, 0, 100);
                $data['SDFstatus'] = $file->SDFstatus;
                $data['transcoded'] = $file->transcoded;
                $data['s3path'] = substr($file->url, 0, 200);
                $data['lodestar'] = substr($file->lodestar, 0, 250);
                $wpdb->insert('resourcefiles', $data, array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));                
                
                $data['fileid'] = intval($wpdb->insert_id);                
                $data['tempactive'] = isset($data['tempactive']) ? $data['tempactive'] : 'F';
                $resourcefiles_list[] = $data;
                uploadLodeStarS3((array) $file);
            }
        }
        
    } elseif (strpos($contents, 'archivecurrikicdn.s3-us-west-2.amazonaws.com')) {
        $newDom = new DOMDocument();
        libxml_use_internal_errors(TRUE); //disable libxml errors

        @$newDom->loadHTML($contents);
        libxml_clear_errors();

        $main_xpath = new DOMXPath(@$newDom);
        if (!$main_xpath) {
            echo "\nCouldnt find main_xpath\n";
            wp_die();
        }
        $a_row = $main_xpath->query('//a[contains(@href, "https://archivecurrikicdn.s3-us-west-2.amazonaws.com/")]');
        $i_row = $main_xpath->query('//iframe[contains(@src, "archivecurrikicdn.s3-us-west-2.amazonaws.com")]');
        $img_row = $main_xpath->query('//img[contains(@src, "archivecurrikicdn.s3-us-west-2.amazonaws.com")]');
        $vid_row = $main_xpath->query('//video//source[contains(@type, "video/mp4")][contains(@src, "archivecurrikicdn.s3-us-west-2.amazonaws.com")]');

        $urls_arr = array();
        foreach ($a_row as $a) {
            $urls_arr[] = $a->getAttribute('href');
        }
        foreach ($i_row as $i) {
            $i_src = urldecode($i->getAttribute('src'));
            $parts = parse_url($i_src);
            parse_str($parts['query'], $query);
            if (isset($query['url']) && $query['url'] != '') {
                $urls_arr[] = $query['url'];
            }
        }

        foreach ($img_row as $img) {
            $urls_arr[] = urldecode($img->getAttribute('src'));
        }
        foreach ($vid_row as $vid) {
            $urls_arr[] = urldecode($vid->getAttribute('src'));
        }
        $urls_arr = array_unique($urls_arr);
        foreach ($urls_arr as $href) {
            $trimmed_href = str_replace('https://archivecurrikicdn.s3-us-west-2.amazonaws.com/', '', $href);
            $exploded_href = explode("/", $trimmed_href);
            if (count($exploded_href) == 2) {
                $folder = $exploded_href[0];
                $uniquename = $exploded_href[1];
            //                print_r($exploded_href);
                $sql = "SELECT * FROM resourcefiles WHERE uniquename LIKE '$uniquename' LIMIT 1";

                $results = $wpdb->get_results($sql);
                if (count($results) > 0) {
                    $resourcefiles_list = [];
                    foreach ($results as $file) {
                        $data['fileid'] = NULL;
                        $data['filename'] = substr($file->filename, 0, 500);
                        $data['uploaddate'] = $file->uploaddate ? $file->uploaddate : date('Y-m-d H:i:s');
                        $data['uniquename'] = substr($file->uniquename, 0, 500);
                        $data['ext'] = substr($file->ext, 0, 10);
                        $data['active'] = "T";
                        $data['folder'] = substr($file->folder, 0, 100);
                        $data['SDFstatus'] = $file->SDFstatus;
                        $data['transcoded'] = $file->transcoded;
                        $data['s3path'] = substr($file->s3path, 0, 200);
                        $data['lodestar'] = substr($file->lodestar, 0, 250);
                        $sql = "SELECT fileid FROM resourcefiles WHERE uniquename LIKE '$uniquename' AND resourceid = " . $data['resourceid'] . " LIMIT 1";

                        $res = $wpdb->get_results($sql);

                        if ($wpdb->num_rows == 0) {
                            $wpdb->insert('resourcefiles', $data, array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
                            $data['fileid'] = intval($wpdb->insert_id);                
                            $data['tempactive'] = isset($data['tempactive']) ? $data['tempactive'] : 'F';
                            $resourcefiles_list[] = $data;
                        }
                    }
                    
                } else {
                    $resourcefiles_list = [];
                    $data['fileid'] = NULL;
                    $data['filename'] = substr($uniquename, 0, 500);
                    $data['uploaddate'] = date('Y-m-d H:i:s');
                    $data['uniquename'] = substr($uniquename, 0, 500);
                    $data['ext'] = substr(end(explode('.', $uniquename)), 0, 10);
                    $data['active'] = "T";
                    $data['folder'] = substr($folder . '/', 0, 100);
                    $data['SDFstatus'] = '';
                    $data['transcoded'] = 'T';
                    $data['s3path'] = $href;
                    $data['lodestar'] = '';                    
                    $wpdb->insert('resourcefiles', $data, array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));                    
                    $data['fileid'] = intval($wpdb->insert_id);                
                    $data['tempactive'] = isset($data['tempactive']) ? $data['tempactive'] : 'F';
                    $resourcefiles_list[] = $data;                                                            
                }
            }
        }
    }

    $resource_course_data['resource_id'] = $res_id;
    if (isset($resource_course_data['course_id'])) {
        // insert row into 'resources_courses' table
        $table_name = $wpdb->prefix . 'resources_courses';
        $resource_course = $wpdb->insert($table_name, $resource_course_data);
        if (!$resource_course) {
            $resource_course_data = null;
        }
    }
    

    //Returning Output
    echo json_encode(array('resourceid' => $res_id, 'pageurl' => $pageurl, 'content'=>$res_content, 'resource_course'=>$resource_course_data));
    wp_die(); // this is required to terminate immediately and return a proper response
}
?>
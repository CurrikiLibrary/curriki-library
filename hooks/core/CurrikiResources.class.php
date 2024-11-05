<?php

/*
  Description: All curriki resource functions are added to this class.
  Author: Waqar Muneer
 */

class CurrikiResources {

  function prepareCollectionDataSet($data) {
    return array(
      "resourceid" => $data['resourceid'],
      "title" => $data['title'],
      "content" => $data['content'],
      "description" => $data['description'],
      "reviewstatus" => "none",
      "reviewrating" => null,
      "standardsalignment" => null,
      "standardsalignmentcomment" => null,
      "subjectmatter" => null,
      "subjectmattercomment" => null,
      "supportsteaching" => null,
      "supportsteachingcomment" => null,
      "assessmentsquality" => null,
      "assessmentsqualitycomment" => null,
      "interactivityquality" => null,
      "interactivityqualitycomment" => null,
      "instructionalquality" => null,
      "instructionalqualitycomment" => null,
      "deeperlearning" => null,
      "deeperlearningcomment" => null,
      "memberrating" => null,
      "partner" => "F",
      "pageurl" => $data['pageurl'],
      "contributorid_Name" => "Curriki Learn",
      "thumb_image" => null,
      "lp_object" => $data['lp_object'],
      "lp_object_id" => $data['lp_object_id'],
      "lp_course_id" => $data["lp_course_id"]
    ); 
  }

  function prepareResourceDataSet($data) {
    $resource = array(
      "partner" => "F",
      "resourceid" => $data["ID"],
      "title" => $data["post_title"],
      "description" => $lp_description,
      "content" => $data["post_content"],
      'resourcetype' => $data["resourcetype"],
      "type" => $data["resourcetype"],
      "reviewstatus" => "none",
      "reviewrating" => null,
      "standardsalignment" => null,
      "standardsalignmentcomment" => null,
      "subjectmatter" => null,
      "subjectmattercomment" => null,
      "supportsteaching" => null,
      "supportsteachingcomment" => null,
      "assessmentsquality" => null,
      "assessmentsqualitycomment" => null,
      "interactivityquality" => null,
      "interactivityqualitycomment" => null,
      "instructionalquality" => null,
      "instructionalqualitycomment" => null,
      "deeperlearning" => null,
      "deeperlearningcomment" => null,
      "memberrating" => null,
      "pageurl" => $data["post_name"],
      "contributiondate" => $data["post_date"],
      "studentfacing" => "",
      "contentdisplayok" => null,
      "reviewresource" => null,
      "oldurl" => null,
      "mediatype" => "external",
      "keywords" => "AI, ai, artificial intelligence, Artificial Intelligence, AI, artificial intelligence",
      "generatedkeywords" => null,
      "approvalStatus" => "approved",
      "userid" => $data["post_author"],
      "display_name" => "Curriki Learn",
      "blogs" => "currikilibrary.org",
      "city" => "Cupertino",
      "state" => "California",
      "country" => "US",
      "organization" => "Curriki Learn",
      "registerdate" => "2017-03-14 14:55:14",
      "uniqueavatarfile" => "5dd4114fbc1c1.jpg",
      "fileid" => null,
      "uniquename" => null,
      "folder" => null,
      "resourcechecked" => "F",
      "resourcecheckrequestnote" => null,
      "resourcechecknote" => null,
      "license" => "CC BY-NC-SA",
      "resource_active" => "T",
      "lp_object" => $data["lp_object"],
      "lp_object_id" => $data["lp_object_id"],
      "lp_course_id" => $data["lp_course_id"]
    );
    return $resource;
  }

  function getLpPostByPageUrl($pageurl, $lp_object=null, $lp_object_id=0, $lp_course_id=0) {
    // get wp query by slug where condition is post_name
    global $wpdb;
    $resource = false;
    $q = "SELECT * FROM {$wpdb->prefix}posts WHERE post_name = '" . trim( rtrim($pageurl, '/') ) . "'";
    $row = $wpdb->get_row($q);
    if ($row) {
      // $lp_description = strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/i', '', $row->post_content));
      $lp_description = strip_tags(substr($row->post_content, 0, 900));
      $lp_description = strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $lp_description));
      $lp_description = strip_tags(preg_replace('/\s*[^{}]+\{[^{}]*\}\s*/', '', $lp_description));

      $resourcetype = 'collection';
      if ($row->post_type === 'lp_lesson') {
          $resourcetype = 'resource';
      }

      $data = (array) $row;
      $data["resourcetype"] = $resourcetype;
      $data["lp_object"] = $lp_object;
      $data["lp_object_id"] = $lp_object_id;
      $data["lp_course_id"] = $lp_course_id;
      $resource = $this->prepareResourceDataSet($data);

      if (function_exists('learn_press_get_course') && $resourcetype === 'collection') {
        $course = learn_press_get_course( $row->ID );
        foreach ($course->get_sections_data_arr() as $data) {
          $data['resourceid'] = $data['section_id'];
          $data['title'] = $data['section_name'];
          $data["content"] = $data['section_description'];
          $data["description"] = $data['section_description'];
          $data["pageurl"] = $data['section_name'];
          $data["lp_course_id"] = $row->ID;
          $data['lp_object'] = 'lp_section';
          $data["lp_object_id"] = $data['section_id'];
          $resource["collection"][] = $this->prepareCollectionDataSet($data);
        }
      }      
    } else if (!is_null($lp_object) && $lp_object === 'lp_section' && $lp_object_id != '' && $lp_course_id != '') {
      // var_dump($lp_object);
      // var_dump($lp_object_id);
      // var_dump($lp_course_id);
      $course = learn_press_get_course( $lp_course_id );
      $sections = $course->get_sections_data_arr();
      // echo "<pre>";
      // print_r($sections);
      // filter $sections by $lp_object_id being the section_id
      $section = array_filter($sections, function($section) use ($lp_object_id) {
        return $section['section_id'] == $lp_object_id;
      });
      
      if ($section) {
        $section = array_shift($section);
        $resourcetype = 'collection';
        $data = [];
        $data["ID"] = $section["section_id"];
        $data["post_title"] = $section["section_name"];
        $data["post_content"] = $section["section_description"];
        $data["post_name"] = $section["section_name"];
        $data["post_date"] = "2024-07-05";
        $data["post_author"] = 1;
        $data["resourcetype"] = $resourcetype;
        $data["lp_object"] = $lp_object;
        $data["lp_object_id"] = $lp_object_id;
        $data["lp_course_id"] = $lp_course_id;
        $resource = $this->prepareResourceDataSet($data);  

        foreach ($section["items"] as $section_lesson) {
          $q = "SELECT * FROM {$wpdb->prefix}posts WHERE id = '" . $section_lesson->id . "'";
          $lesson_row = $wpdb->get_row($q);

          $lp_description = strip_tags(substr($lesson_row->post_content, 0, 900));
          $lp_description = strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $lp_description));
          $lp_description = strip_tags(preg_replace('/\s*[^{}]+\{[^{}]*\}\s*/', '', $lp_description));
          $data['resourceid'] = $lesson_row->ID;
          $data['title'] = $lesson_row->post_title;
          $data["content"] = $lesson_row->post_content;
          $data["description"] = $lp_description;
          $data["pageurl"] = $lesson_row->post_name;
          $data["lp_course_id"] = $lp_course_id;
          $data['section_name'] = $lesson_row->post_title;
          $data['lp_object'] = 'lp_lesson';
          $data["lp_object_id"] = $lesson_row->ID;
          $resource["collection"][] = $this->prepareCollectionDataSet($data);
        }
      }
    }

    return $resource;
  }

  function getResourceById($resourceid = 0, $pageurl = '', $all = false) {
    global $wpdb;

    if ($resourceid) {
      $query = 'SELECT r.*, rt.thumb_image, lan.displayname AS languageName, l.displayname AS licenseName, cu.`display_name` AS contributorid_Name, rf.fileid, rf.uniquename, rf.folder FROM `resources` AS r LEFT JOIN resource_thumbs AS rt ON r.resourceid = rt.resourceid LEFT JOIN `languages` AS lan ON (lan.language = r.language) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `cur_users` AS cu ON (cu.ID = r.contributorid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`resourceid` = "' . $resourceid . '"';
    } else {
      $query = 'SELECT r.*, rt.thumb_image, lan.displayname AS languageName, l.displayname AS licenseName, cu.`display_name` AS contributorid_Name, rf.fileid, rf.uniquename, rf.folder FROM `resources` AS r LEFT JOIN resource_thumbs AS rt ON r.resourceid = rt.resourceid LEFT JOIN `languages` AS lan ON (lan.language = r.language) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `cur_users` AS cu ON (cu.ID = r.contributorid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`pageurl` = "' . $pageurl . '"';
    }

    $resource = $wpdb->get_row($query);
    if (is_object($resource)) {
      $resource = (array) $resource;

      if ($all) {
        $type = self::getResourceTypeById($resource['resourceid']);
        
        if ($type)
          $resource = array_merge($resource, array('typeName' => $type));

        $collection = self::getResourceCollectionById($resource['resourceid']);
        if ($collection)
          $resource = array_merge($resource, array('collection' => $collection));

        //[start] ==========  FETCHING TABLE OF CONTENTS ==========        
        $toc_persist = array();
        $toc_persist_rids = array();
        if (isset($_GET["mrid"])) {
          $mrid_param = explode("-", $_GET["mrid"]);
          if (in_array($resource['resourceid'], $mrid_param)) {
            $pos = array_search($resource['resourceid'], $mrid_param);
            unset($mrid_param[$pos]);
          }

          foreach ($mrid_param as $mrid) {
            $rid_to_fetech_collection = 0;
            $resources_table_of_content = new stdClass();
            $resources_table_of_content->main_resource_resources = array();
            $resources_table_of_content->current_resource_resources = array();

            $toc_persist_rids[] = $mrid;
            $rid_to_fetech_collection = $mrid;
            $query_r = 'SELECT r.* FROM `resources` AS r WHERE r.`resourceid` = "' . $rid_to_fetech_collection . '"';
            $resource_obj = $wpdb->get_row($query_r);
            $r_data = array(
                "resource" => $resource_obj,
                "collections" => self::getResourceCollectionById($rid_to_fetech_collection)
            );
            $resources_table_of_content->main_resource_resources = $r_data;
            $toc_persist[] = $resources_table_of_content;
          }
        }

        $resource = array_merge($resource, array('toc_persist' => $toc_persist));
        if (in_array($resource['resourceid'], $toc_persist_rids)) {
          //$pos = array_search($resource['resourceid'], $toc_persist_rids);
          //unset($toc_persist_rids[$pos]);
        }
        $resource = array_merge($resource, array('toc_persist_rids' => $toc_persist_rids));
        //[END] ==========  FETCHING TABLE OF CONTENTS ==========


        $subjects = self::getResourceSubjectById($resource['resourceid']);
        if ($subjects)
          $resource = array_merge($resource, array('subjects' => $subjects));

        $educationlevels = self::getResourceEducationLevelsById($resource['resourceid']);
        if ($educationlevels)
          $resource = array_merge($resource, array('educationlevels' => $educationlevels));

        $standards = self::getResourceStandardsById($resource['resourceid']);
        if ($standards)
          $resource = array_merge($resource, array('standards' => $standards));

        $comments = self::getResourceCommentsById($resource['resourceid']);
        //if ($comments)
        $resource = array_merge($resource, array('comments' => $comments));

        if ($userid = get_current_user_id())
          $currentUser = self::getUserNameById($userid);
        if (isset($currentUser))
          $resource = array_merge($resource, array('currentUser' => $currentUser));

        //==== get collection current resource belongs to ========
        $collectionsResourceBlogngsTo = self::getCollectionsResourceBlogngsTo($resource['resourceid']);
        $resource = array_merge($resource, array('collections_resource_blogngs_to' => $collectionsResourceBlogngsTo));
      }

      return $resource;
    } else {
      $resource = false;
      if (isset($_GET['pageurl'])) {
        
        if (
            isset($_GET['lp_object']) && $_GET['lp_object'] != ''
            && isset($_GET['lp_object_id']) && $_GET['lp_object_id'] != ''
            && isset($_GET['lp_course_id']) && $_GET['lp_course_id'] != ''
        ) {
          $lp_object = $_GET['lp_object'];
          $lp_object_id = $_GET['lp_object_id'];
          $lp_course_id = $_GET['lp_course_id'];
          $resource = $this->getLpPostByPageUrl($_GET['pageurl'], $lp_object, $lp_object_id, $lp_course_id);
        } else {
          $resource = $this->getLpPostByPageUrl($_GET['pageurl']);
        }
        
        $toc_persist = array();
        $toc_persist_rids = array();
        if (isset($_GET["mrid"])) {
          $mrid_param = explode("-", $_GET["mrid"]);
          if (in_array($resource['resourceid'], $mrid_param)) {
            $pos = array_search($resource['resourceid'], $mrid_param);
            unset($mrid_param[$pos]);
          }

          foreach ($mrid_param as $mrid) {
            $rid_to_fetech_collection = 0;
            $resources_table_of_content = new stdClass();
            $resources_table_of_content->main_resource_resources = array();
            $resources_table_of_content->current_resource_resources = array();

            $toc_persist_rids[] = $mrid;
            $rid_to_fetech_collection = $mrid;
            
            // if current resource id section then get the course as a parent
            if ( isset($_GET['lp_object']) && $_GET['lp_object'] === 'lp_section' ) {
              $resource_collection = [];
              $course = learn_press_get_course( $lp_course_id );
              foreach ($course->get_sections_data_arr() as $data) {
                $data['resourceid'] = $data['section_id'];
                $data['title'] = $data['section_name'];
                $data["content"] = $data['section_description'];
                $data["description"] = $data['section_description'];
                $data["pageurl"] = $data['section_name'];
                $data["lp_course_id"] = $lp_course_id;
                $data['lp_object'] = 'lp_section';
                $data["lp_object_id"] = $data['section_id'];
                $resource_collection[] = $this->prepareCollectionDataSet($data);
              }  

              
              $q = "SELECT * FROM {$wpdb->prefix}posts WHERE id = '" . $lp_course_id . "'";
              $course_row = $wpdb->get_row($q);
              $data = (array) $course_row;
              $data["resourcetype"] = 'collection';
              $data["post_title"] = $course_row->post_title;
              $lp_object = $_GET['lp_object'];
              $lp_object_id = $_GET['lp_object_id'];
              $lp_course_id = $_GET['lp_course_id'];
              $data["lp_object"] = $lp_object;
              $data["lp_object_id"] = $lp_object_id;
              $data["lp_course_id"] = $lp_course_id;
              $resource_obj = (object) $this->prepareResourceDataSet($data);
              $r_data = array(
                  "resource" => $resource_obj,
                  "collections" => $resource_collection
              );
              $resources_table_of_content->main_resource_resources = $r_data;
              $toc_persist[] = $resources_table_of_content;
              $resource = array_merge($resource, array('toc_persist' => $toc_persist));
              $resource = array_merge($resource, array('toc_persist_rids' => $toc_persist_rids));

            } else if ( isset($_GET['lp_object']) && $_GET['lp_object'] === 'lp_lesson' && $rid_to_fetech_collection == $_GET['lp_course_id'] ) {
              $resource_collection = [];
              $course = learn_press_get_course( $_GET['lp_course_id'] );
              foreach ($course->get_sections_data_arr() as $data) {
                $data['resourceid'] = $data['section_id'];
                $data['title'] = $data['section_name'];
                $data["content"] = $data['section_description'];
                $data["description"] = $data['section_description'];
                $data["pageurl"] = $data['section_name'];
                $data["lp_course_id"] = $lp_course_id;
                $data['lp_object'] = 'lp_section';
                $data["lp_object_id"] = $data['section_id'];
                $resource_collection[] = $this->prepareCollectionDataSet($data);
              }
              
              $q = "SELECT * FROM {$wpdb->prefix}posts WHERE id = '" . $_GET['lp_course_id'] . "'";
              $course_row = $wpdb->get_row($q);
              $data = (array) $course_row;
              $data["resourcetype"] = 'collection';
              $data["post_title"] = $course_row->post_title;
              $lp_object = $_GET['lp_object'];
              $lp_object_id = $_GET['lp_object_id'];
              $lp_course_id = $_GET['lp_course_id'];
              $data["lp_object"] = $lp_object;
              $data["lp_object_id"] = $lp_object_id;
              $data["lp_course_id"] = $lp_course_id;
              $resource_obj = (object) $this->prepareResourceDataSet($data);
              $r_data = array(
                  "resource" => $resource_obj,
                  "collections" => $resource_collection
              );
              $resources_table_of_content->main_resource_resources = $r_data;
              $toc_persist[] = $resources_table_of_content;
              $resource = array_merge($resource, array('toc_persist' => $toc_persist));
              $resource = array_merge($resource, array('toc_persist_rids' => $toc_persist_rids)); 
            } else if ( isset($_GET['lp_object']) && $_GET['lp_object'] === 'lp_lesson' && $rid_to_fetech_collection != $_GET['lp_course_id'] ) {
                $resource_collection = [];
                $course = learn_press_get_course( $_GET['lp_course_id'] );
                $sections = $course->get_sections_data_arr();
                $section = array_filter($sections, function($section) use ($rid_to_fetech_collection) {
                  return $section['section_id'] == $rid_to_fetech_collection;
                });
                $section = array_shift($section);

                foreach ($section['items'] as $section_lesson) {
                  $q = "SELECT * FROM {$wpdb->prefix}posts WHERE id = '" . $section_lesson->id . "'";
                  $lesson_row = $wpdb->get_row($q);
                  $data['resourceid'] = $lesson_row->ID;
                  $data['title'] = $lesson_row->post_title;
                  $data["content"] = $lesson_row->post_content;
                  $data["description"] = $lesson_row->post_content;
                  $data["pageurl"] = $lesson_row->post_name;
                  $data["lp_course_id"] = $lp_course_id;
                  $data['lp_object'] = 'lp_lesson';
                  $data["lp_object_id"] = $lp_object_id;
                  $resource_collection[] = $this->prepareCollectionDataSet($data);
                }

                $data["resourcetype"] = 'collection';
                $data["post_title"] = $section["section_name"];
                $lp_object = $_GET['lp_object'];
                $lp_object_id = $_GET['lp_object_id'];
                $lp_course_id = $_GET['lp_course_id'];
                $data["lp_object"] = $lp_object;
                $data["lp_object_id"] = $lp_object_id;
                $data["lp_course_id"] = $lp_course_id;
                $resource_obj = (object) $this->prepareResourceDataSet($data);
                $r_data = array(
                    "resource" => $resource_obj,
                    "collections" => $resource_collection
                );
                $resources_table_of_content->main_resource_resources = $r_data;
                $toc_persist[] = $resources_table_of_content;
                $resource = array_merge($resource, array('toc_persist' => $toc_persist));
                $resource = array_merge($resource, array('toc_persist_rids' => $toc_persist_rids)); 
                
            }
            
          }
        }
      }
      return $resource;
    }
      
  }

  function getPreviewResourceById($resourceid = 0, $pageurl = '', $all = false) {
    global $wpdb;
    
    
    
    if ($resourceid) {
      $query = 'SELECT r.*, lan.displayname AS languageName, l.displayname AS licenseName, cu.`display_name` AS contributorid_Name, rf.fileid, rf.uniquename, rf.folder FROM `preview_resources` AS r LEFT JOIN `languages` AS lan ON (lan.language = r.language) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `cur_users` AS cu ON (cu.ID = r.contributorid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`resourceid` = "' . $resourceid . '"';
    } else {
      $query = 'SELECT r.*, lan.displayname AS languageName, l.displayname AS licenseName, cu.`display_name` AS contributorid_Name, rf.fileid, rf.uniquename, rf.folder FROM `preview_resources` AS r LEFT JOIN `languages` AS lan ON (lan.language = r.language) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `cur_users` AS cu ON (cu.ID = r.contributorid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`pageurl` = "' . $pageurl . '"';
    }
    
    $resource = $wpdb->get_row($query);
    

    if (is_object($resource)) {
      $resource = (array) $resource;
      
      if ($all) {
          $r_it_arr = unserialize($resource['resource_instructiontypes']);
        $type = self::getPreviewResourceTypeById($r_it_arr);
        
        if ($type)
          $resource = array_merge($resource, array('typeName' => $type));
          
        //[start] ==========  FETCHING TABLE OF CONTENTS ==========        
        $toc_persist = array();
        $toc_persist_rids = array();
        if (/*0*/isset($_GET["mrid"])) {
          $mrid_param = explode("-", $_GET["mrid"]);
          if (in_array($resource['resourceid'], $mrid_param)) {
            $pos = array_search($resource['resourceid'], $mrid_param);
            unset($mrid_param[$pos]);
          }

          foreach ($mrid_param as $mrid) {
            $rid_to_fetech_collection = 0;
            $resources_table_of_content = new stdClass();
            $resources_table_of_content->main_resource_resources = array();
            $resources_table_of_content->current_resource_resources = array();

            $toc_persist_rids[] = $mrid;
            $rid_to_fetech_collection = $mrid;
            $query_r = 'SELECT r.* FROM `resources` AS r WHERE r.`resourceid` = "' . $rid_to_fetech_collection . '"';
            $resource_obj = $wpdb->get_row($query_r);
            $r_data = array(
                "resource" => $resource_obj,
                "collections" => self::getResourceCollectionById($rid_to_fetech_collection)
            );
            $resources_table_of_content->main_resource_resources = $r_data;
            $toc_persist[] = $resources_table_of_content;
          }
        }

        $resource = array_merge($resource, array('toc_persist' => $toc_persist));
        if (in_array($resource['resourceid'], $toc_persist_rids)) {
          //$pos = array_search($resource['resourceid'], $toc_persist_rids);
          //unset($toc_persist_rids[$pos]);
        }
        $resource = array_merge($resource, array('toc_persist_rids' => $toc_persist_rids));
        //[END] ==========  FETCHING TABLE OF CONTENTS ==========

        $subjectareas = unserialize($resource['subjectareas']);
        $subjects = self::getPreviewResourceSubjectById($subjectareas);
        
        if ($subjects)
          $resource = array_merge($resource, array('subjects' => $subjects));

        $educationlevels_arr = unserialize($resource['education_levels']);
        $educationlevels = self::getPreviewResourceEducationLevelsById($educationlevels_arr);

        if ($educationlevels)
          $resource = array_merge($resource, array('educationlevels' => $educationlevels));

        $resource_statementids = unserialize($resource['resource_statementids']);
        $standards = self::getPreviewResourceStandardsById($resource_statementids);
        
        if ($standards)
          $resource = array_merge($resource, array('standards' => $standards));

        $comments = self::getResourceCommentsById($resource['resourceid']);
        //if ($comments)
        $resource = array_merge($resource, array('comments' => $comments));

        if ($userid = get_current_user_id())
          $currentUser = self::getUserNameById($userid);
        if (isset($currentUser))
          $resource = array_merge($resource, array('currentUser' => $currentUser));

        //==== get collection current resource belongs to ========
        
        $collectionsResourceBlogngsTo = self::getPreviewCollectionsResourceBlogngsTo($resource['editresourceid']);
        if($collectionsResourceBlogngsTo != null){
            $resource = array_merge($resource, array('collections_resource_blogngs_to' => $collectionsResourceBlogngsTo));
        }
      }

      return $resource;
    } else {
      return false;
    }
  }
  
  function getCollectionsResourceBlogngsTo($resourceid) {
    global $wpdb;
    $query_rc = "
                    select title, pageurl
                    from resources r
                    inner join collectionelements ce on r.resourceid = ce.collectionid
                    where ce.resourceid = {$resourceid}  
                    and r.title <> 'Favorites'
                    and r.active = 'T'
                    and r.access = 'public'
                    ";
    return $wpdb->get_results($query_rc);
  }

  function getPreviewCollectionsResourceBlogngsTo($resourceid) {
    global $wpdb;
    if($resourceid != "") {
        $query_rc = "
                    select title, pageurl
                    from resources r
                    where r.resourceid = {$resourceid}  
                    and r.title <> 'Favorites'
                    and r.active = 'T'
                    and r.access = 'public'
                    ";
        return $wpdb->get_results($query_rc);
    } else {
        return null;
    }
  }

  function getResourceTypeById($resourceid) {
    global $wpdb;


    $query = 'SELECT it.displayname AS typeName FROM instructiontypes AS it LEFT JOIN `resource_instructiontypes` AS rit ON (rit.instructiontypeid = it.instructiontypeid) WHERE rit.resourceid = "' . $resourceid . '"';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = array('typeName' => $res->typeName);

    return $result;
  }

  function getPreviewResourceTypeById($r_it_arr) {
    global $wpdb;
    $result = array();
    if(is_array($r_it_arr) && count($r_it_arr) > 0){
        $r_it = implode(",", $r_it_arr);
        $query = "SELECT * FROM instructiontypes WHERE instructiontypeid IN ($r_it)";
    //    $query = 'SELECT it.displayname AS typeName FROM instructiontypes AS it LEFT JOIN `resource_instructiontypes` AS rit ON (rit.instructiontypeid = it.instructiontypeid) WHERE rit.resourceid = "' . $resourceid . '"';


        $results = $wpdb->get_results($query);

        if (count($results) > 0)
          foreach ($results AS $res)
            $result[] = array('typeName' => $res->displayname);
    }
    return $result;
  }

  function getResourceCollectionById($resourceid) {
    global $wpdb;


    $query = 'SELECT r.resourceid, r.title, r.content, r.description,r.reviewstatus, r.reviewrating, r.standardsalignment,r.standardsalignmentcomment,r.subjectmatter,r.subjectmattercomment,r.supportsteaching,r.supportsteachingcomment,r.assessmentsquality,r.assessmentsqualitycomment,r.interactivityquality,r.interactivityqualitycomment,r.instructionalquality,r.instructionalqualitycomment,r.deeperlearning,r.deeperlearningcomment, r.memberrating, r.partner, r.pageurl, cu.`display_name` AS contributorid_Name, rt.thumb_image FROM collectionelements AS ce LEFT JOIN `resources` AS r ON (ce.resourceid = r.resourceid) LEFT JOIN resource_thumbs AS rt ON r.resourceid = rt.resourceid LEFT JOIN `cur_users` AS cu ON (cu.ID = r.contributorid) WHERE ce.collectionid = "' . $resourceid . '" AND r.active = "T" order by displayseqno';
    
    $result = array();
    $results = $wpdb->get_results($query,ARRAY_A);
    //$results = $wpdb->get_results($query);
    /*
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = array('resourceid' => $res->resourceid, 'title' => $res->title, 'content' => $res->content, 'description' => $res->description, 'reviewstatus' => $res->reviewstatus, 'reviewrating' => $res->reviewrating, 'memberrating' => $res->memberrating, 'contributorid_Name' => $res->contributorid_Name, 'partner' => $res->partner, 'pageurl' => $res->pageurl);
    */        
    //return $result;
    return $results;
  }

  function getPreviewResourceCollectionById($resourceid) {
    global $wpdb;


    $query = 'SELECT r.resourceid, r.title, r.content, r.description,r.reviewstatus, r.reviewrating, r.standardsalignment,r.standardsalignmentcomment,r.subjectmatter,r.subjectmattercomment,r.supportsteaching,r.supportsteachingcomment,r.assessmentsquality,r.assessmentsqualitycomment,r.interactivityquality,r.interactivityqualitycomment,r.instructionalquality,r.instructionalqualitycomment,r.deeperlearning,r.deeperlearningcomment, r.memberrating, r.partner, r.pageurl, cu.`display_name` AS contributorid_Name FROM collectionelements AS ce LEFT JOIN `resources` AS r ON (ce.resourceid = r.resourceid) LEFT JOIN `cur_users` AS cu ON (cu.ID = r.contributorid) WHERE ce.collectionid = "' . $resourceid . '" order by displayseqno';
    
    $result = array();
    $results = $wpdb->get_results($query,ARRAY_A);
    //$results = $wpdb->get_results($query);
    /*
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = array('resourceid' => $res->resourceid, 'title' => $res->title, 'content' => $res->content, 'description' => $res->description, 'reviewstatus' => $res->reviewstatus, 'reviewrating' => $res->reviewrating, 'memberrating' => $res->memberrating, 'contributorid_Name' => $res->contributorid_Name, 'partner' => $res->partner, 'pageurl' => $res->pageurl);
    */        
    //return $result;
    return $results;
  }

  function getResourceSubjectById($resourceid) {
    global $wpdb;

    $query = 'SELECT CONCAT(s.displayname, " > " ,sa.displayname) AS displayname, sa.subjectareaid FROM `resource_subjectareas` AS rs LEFT JOIN `subjectareas` AS sa ON (rs.`subjectareaid` = sa.`subjectareaid`) inner join subjects s on sa.subjectid = s.subjectid WHERE rs.`resourceid` = "' . $resourceid . '"';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = $res->displayname;

    return $result;
  }

  function getPreviewResourceSubjectById($subjectareas) {
    global $wpdb;
    $result = [];
    
    if(is_array($subjectareas) && count($subjectareas) > 0){
        $subjectareas = implode(",", $subjectareas);

        $query = "SELECT CONCAT(s.displayname, ' > ' ,sa.displayname) AS displayname FROM subjectareas as sa LEFT JOIN subjects as s ON s.subjectid = sa.subjectid WHERE sa.subjectareaid IN ($subjectareas)";

        $result = array();
        $results = $wpdb->get_results($query);
        if (count($results) > 0)
          foreach ($results AS $res)
            $result[] = $res->displayname;
    }
    return $result;
  }

  function getResourceEducationLevelsById($resourceid) {
    global $wpdb;

    $query = 'SELECT e.`levelid`, e.`displayname` FROM `resource_educationlevels` AS el LEFT JOIN `educationlevels` AS e ON (el.`educationlevelid` = e.`levelid`) WHERE el.`resourceid` = "' . $resourceid . '"';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = $res->displayname;

    return $result;
  }

  function getPreviewResourceEducationLevelsById($educationlevels_arr) {
    global $wpdb;

    $result = [];
    if(isset($educationlevels_arr) && count($educationlevels_arr) == 1 && $educationlevels_arr[0] == ""){
        return $result;
    }
    $educationlevels = implode(",", $educationlevels_arr);
    $query = "Select * from educationlevels WHERE levelid IN ($educationlevels)";
    //    $query = 'SELECT e.`levelid`, e.`displayname` FROM `resource_educationlevels` AS el LEFT JOIN `educationlevels` AS e ON (el.`educationlevelid` = e.`levelid`) WHERE el.`resourceid` = "' . $resourceid . '"';
    
    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = $res->displayname;

    return $result;
  }

  function getResourceStandardsById($resourceid) {
    global $wpdb;

    $query = 'select s.notation, st.title, s.description from resource_statements rs inner join statements s on rs.statementid = s.statementid inner join standards st on s.standardid = st.standardid where resourceid = "' . $resourceid . '"';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = array('notation' => $res->notation, 'title' => $res->title, 'description' => $res->description);

    return $result;
  }
  
  function getPreviewResourceStandardsById($resource_statementids) {
    global $wpdb;
    $result = [];
    if(is_array($resource_statementids) && count($resource_statementids) > 0){
        $resource_statementids = implode(",", $resource_statementids);
        
        $query = "select s.notation, st.title, s.description from statements s inner join standards st on s.standardid = st.standardid where s.statementid IN ($resource_statementids)";
        //    $query = 'select s.notation, st.title, s.description from resource_statements rs inner join statements s on rs.statementid = s.statementid inner join standards st on s.standardid = st.standardid where resourceid = "' . $resourceid . '"';

        $result = array();
        $results = $wpdb->get_results($query);
        if (count($results) > 0)
          foreach ($results AS $res)
            $result[] = array('notation' => $res->notation, 'title' => $res->title, 'description' => $res->description);
    }
    return $result;
  }

  function getResourceCommentsById($resourceid) {
    global $wpdb;

    $query = 'SELECT c.*, cu.display_name, u.uniqueavatarfile FROM `comments` AS c LEFT JOIN `cur_users` AS cu ON (cu.`ID` = c.`userid`) LEFT JOIN `users` AS u ON (u.userid = cu.ID) WHERE c.`resourceid` = "' . $resourceid . '" ORDER BY c.commentdate DESC';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[] = array('userid' => $res->userid, 'display_name' => $res->display_name, 'uniqueavatarfile' => $res->uniqueavatarfile, 'rating' => $res->rating, 'date' => $res->commentdate, 'comment' => $res->comment);

    return $result;
  }

  function getUserNameById($userid) {
    global $wpdb;

    $query = 'SELECT cu.display_name, u.uniqueavatarfile FROM `cur_users` AS cu LEFT JOIN `users` AS u ON (u.userid = cu.ID) WHERE cu.`ID` = "' . $userid . '"';
    $user = $wpdb->get_row($query);
    if (is_object($user))
      return array('display_name' => $user->display_name, 'uniqueavatarfile' => $user->uniqueavatarfile);
    else
      return false;
  }

  function getResourceUserById($resourceid = 0, $pageurl = '') {
    global $wpdb;
    
    if ($resourceid)
      $query = 'SELECT r.partner, r.resourceid, r.title, r.description, r.content, r.type, r.reviewstatus, r.reviewrating, r.standardsalignment,r.standardsalignmentcomment,r.subjectmatter,r.subjectmattercomment,r.supportsteaching,r.supportsteachingcomment,r.assessmentsquality,r.assessmentsqualitycomment,r.interactivityquality,r.interactivityqualitycomment,r.instructionalquality,r.instructionalqualitycomment,r.deeperlearning,r.deeperlearningcomment, r.memberrating, r.pageurl, r.contributiondate, r.reviewstatus, r.studentfacing,  r.contentdisplayok, r.reviewresource, r.oldurl, r.mediatype, r.keywords, r.generatedkeywords, r.approvalStatus , u.userid, cu.display_name, u.blogs, u.city, u.state, u.country, u.organization, u.registerdate, u.uniqueavatarfile, rf.fileid, rf.uniquename, rf.folder,r.resourcechecked,r.resourcecheckrequestnote,r.resourcechecknote,l.name AS license, r.active as resource_active FROM `resources` AS r LEFT JOIN `users` AS u ON (u.userid = r.contributorid) LEFT JOIN `cur_users` AS cu ON (cu.ID = u.userid) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`resourceid` = "' . $resourceid . '"';
    else
      $query = 'SELECT r.partner, r.resourceid, r.title, r.description, r.content, r.type, r.reviewstatus, r.reviewrating, r.standardsalignment,r.standardsalignmentcomment,r.subjectmatter,r.subjectmattercomment,r.supportsteaching,r.supportsteachingcomment,r.assessmentsquality,r.assessmentsqualitycomment,r.interactivityquality,r.interactivityqualitycomment,r.instructionalquality,r.instructionalqualitycomment,r.deeperlearning,r.deeperlearningcomment, r.memberrating, r.pageurl, r.contributiondate, r.reviewstatus, r.studentfacing,  r.contentdisplayok, r.reviewresource, r.oldurl, r.mediatype, r.keywords, r.generatedkeywords, r.approvalStatus , u.userid, cu.display_name, u.blogs, u.city, u.state, u.country, u.organization, u.registerdate, u.uniqueavatarfile, rf.fileid, rf.uniquename, rf.folder,r.resourcechecked,r.resourcecheckrequestnote,r.resourcechecknote,l.name AS license, r.active as resource_active FROM `resources` AS r LEFT JOIN `users` AS u ON (u.userid = r.contributorid) LEFT JOIN `cur_users` AS cu ON (cu.ID = u.userid) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`pageurl` = "' . $pageurl . '"';
   
    $resource = $wpdb->get_row($query);      
    
    if (is_object($resource)) {
      return (array) $resource;
    } else {
      $resource = false;
      if (isset($_GET['pageurl'])) {
        if (
            isset($_GET['lp_object']) && $_GET['lp_object'] != ''
            && isset($_GET['lp_object_id']) && $_GET['lp_object_id'] != ''
            && isset($_GET['lp_course_id']) && $_GET['lp_course_id'] != ''
        ) {
          $lp_object = $_GET['lp_object'];
          $lp_object_id = $_GET['lp_object_id'];
          $lp_course_id = $_GET['lp_course_id'];
          $resource = $this->getLpPostByPageUrl($_GET['pageurl'], $lp_object, $lp_object_id, $lp_course_id);
        } else {
          $resource = $this->getLpPostByPageUrl($_GET['pageurl']);
        }
      }
      return $resource;
    }
  }

  function getResourceUserByIdForResourceViews($resourceid = 0, $pageurl = '') {
    global $wpdb;

    if ($resourceid)
      $query = 'SELECT r.resourceid FROM `resources` AS r WHERE r.`resourceid` = "' . $resourceid . '"';
    else
      $query = 'SELECT r.resourceid FROM `resources` AS r WHERE r.`pageurl` = "' . $pageurl . '"';

    $resource = $wpdb->get_row($query);    
    if (is_object($resource)) {
      return (array) $resource;
    } else
      return false;
  }

  function getPreviewResourceUserById($resourceid = 0, $pageurl = '') {
    global $wpdb;

    if ($resourceid)
      $query = 'SELECT r.partner, r.resourceid, r.title, r.description, r.content, r.type, r.reviewstatus, r.reviewrating, r.standardsalignment,r.standardsalignmentcomment,r.subjectmatter,r.subjectmattercomment,r.supportsteaching,r.supportsteachingcomment,r.assessmentsquality,r.assessmentsqualitycomment,r.interactivityquality,r.interactivityqualitycomment,r.instructionalquality,r.instructionalqualitycomment,r.deeperlearning,r.deeperlearningcomment, r.memberrating, r.pageurl, r.contributiondate, r.reviewstatus, r.studentfacing,  r.contentdisplayok, r.reviewresource, r.oldurl, r.mediatype, r.keywords, r.generatedkeywords , u.userid, cu.display_name, u.blogs, u.city, u.state, u.country, u.organization, u.registerdate, u.uniqueavatarfile, rf.fileid, rf.uniquename, rf.folder,r.resourcechecked,r.resourcecheckrequestnote,r.resourcechecknote,l.name AS license FROM `preview_resources` AS r LEFT JOIN `users` AS u ON (u.userid = r.contributorid) LEFT JOIN `cur_users` AS cu ON (cu.ID = u.userid) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`resourceid` = "' . $resourceid . '"';
    else
      $query = 'SELECT r.partner, r.resourceid, r.title, r.description, r.content, r.type, r.reviewstatus, r.reviewrating, r.standardsalignment,r.standardsalignmentcomment,r.subjectmatter,r.subjectmattercomment,r.supportsteaching,r.supportsteachingcomment,r.assessmentsquality,r.assessmentsqualitycomment,r.interactivityquality,r.interactivityqualitycomment,r.instructionalquality,r.instructionalqualitycomment,r.deeperlearning,r.deeperlearningcomment, r.memberrating, r.pageurl, r.contributiondate, r.reviewstatus, r.studentfacing,  r.contentdisplayok, r.reviewresource, r.oldurl, r.mediatype, r.keywords, r.generatedkeywords , u.userid, cu.display_name, u.blogs, u.city, u.state, u.country, u.organization, u.registerdate, u.uniqueavatarfile, rf.fileid, rf.uniquename, rf.folder,r.resourcechecked,r.resourcecheckrequestnote,r.resourcechecknote,l.name AS license FROM `preview_resources` AS r LEFT JOIN `users` AS u ON (u.userid = r.contributorid) LEFT JOIN `cur_users` AS cu ON (cu.ID = u.userid) LEFT JOIN `licenses` AS l ON (l.licenseid = r.licenseid) LEFT JOIN `resourcefiles` AS rf ON (rf.resourceid = r.resourceid) WHERE r.`pageurl` = "' . $pageurl . '"';

    $resource = $wpdb->get_row($query);    
    if (is_object($resource)) {
      return (array) $resource;
    } else
      return false;
  }

  function getMediatypes() {
    global $wpdb;

    $query = 'SELECT mediatype, displayname FROM mediatypes WHERE active = "T"';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[$res->mediatype] = $res->displayname;

    return $result;
  }

  function setResourceReview() {
    global $wpdb;

    $_updateValues = array(
        'content' => stripslashes_deep($_POST['content']),
        'studentfacing' => $_POST['studentfacing'],
        'contentdisplayok' => $_POST['contentdisplayok'],
        'reviewresource' => $_POST['reviewresource'],
        'mediatype' => $_POST['mediatype'],
        'oldurl' => $_POST['oldurl']
    );

    $wpdb->update('resources', $_updateValues, array('resourceid' => (int) $_POST['resourceid']));
  }

  function setResourceComments($resourceid, $comment, $rating) {
    global $wpdb;

    $userid = get_current_user_id();
    if (!$userid)
      $userid = '10000';
    
    $data = array('resourceid' => $resourceid, 'userid' => $userid, 'comment' => $comment, 'rating' => $rating, 'commentdate' => date('Y-m-d h:i:s'));    
    $wpdb->insert('comments', $data , array('%d', '%d', '%s', '%d', '%s'));    
    $curriki_recommender = isset($GLOBALS['curriki_recommender']) ? $GLOBALS['curriki_recommender'] : null;    
    if( $curriki_recommender ){                
        //$curriki_recommender->resource_repository->saveResourceComments($data); 
    }
  }

  function setMemberRating($resourceid) {
    global $wpdb;

    $query = 'select sum(rating)/count(*) AS total, sum(rating) AS rating, count(*) AS total_comments from comments where resourceid = "' . $resourceid . '" and rating is not null';

    $rating = $wpdb->get_row($query);
    if (is_object($rating) && $rating->total_comments > 0) {
      $total = round($rating->total);
      $wpdb->update('resources', array('memberrating' => $total), array('resourceid' => $resourceid));
    }
  }

  function setResourceFileDownload($fileid) {
    global $wpdb;

    $userid = get_current_user_id();
    if (!$userid)
      $userid = '10000';
    
    $data = array('fileid' => $fileid, 'userid' => $userid, 'downloaddate' => date('Y-m-d h:i:s'));
    $wpdb->insert('filedownloads', $data , array('%d', '%d', '%s'));    
    
    $data = array('downloadid' => $wpdb->insert_id) + $data;
    $curriki_recommender = isset($GLOBALS['curriki_recommender']) ? $GLOBALS['curriki_recommender'] : null;
    if( $curriki_recommender && intval($wpdb->insert_id) > 0 ){
        try{
            //$curriki_recommender->resource_repository->saveFileDownloads($data);
        } catch (Exception $ex) {}
    }
  }

  function addToMyLibrary($resourceid) {
    global $wpdb;

    $userid = get_current_user_id();

    $query = 'select resourceid from resources where contributorid = "' . $userid . '" and type = "collection" and title = "Favorites"';
    $resource = $wpdb->get_row($query);
    if (!is_object($resource)) {
      $query = "INSERT INTO resources (`licenseid`,`contributorid`,`contributiondate`,`description`,`title`,`keywords`,`generatedkeywords`,`language`,`lasteditorid`,`lasteditdate`,`currikilicense`,`fullname`,`externalurl`,`resourcechecked`,`oldurl`,`content`,`newcontent`,`logView`,`resourcecheckrequestnote`,`resourcecheckdate`,`resourcecheckid`,`resourcechecknote`,`studentfacing`,`source`,`reviewstatus`,`lastreviewdate`,`reviewedbyid`,`reviewrating`,`technicalcompleteness`,`contentaccuracy`,`pedagogy`,`ratingcomment`,`standardsalignment`,`standardsalignmentcomment`,`subjectmatter`,`subjectmattercomment`,`supportsteaching`,`supportsteachingcomment`,`assessmentsquality`,`assessmentsqualitycomment`,`interactivityquality`,`interactivityqualitycomment`,`instructionalquality`,`instructionalqualitycomment`,`deeperlearning`,`deeperlearningcomment`,`partner`,`createdate`,`type`,`featured`,`page`,`active`,`public`,`xwd_id`,`originalcontent`,`mediatype`,`access`,`memberrating`,`aligned`,`resourcename`,`pageurl`,`indexed`,`lastindexdate`,`indexrequired`,`indexrequireddate`) SELECT `licenseid`,$userid AS `contributorid`,`contributiondate`,`description`,'Favorites' AS `title`,`keywords`,`generatedkeywords`,`language`,`lasteditorid`,`lasteditdate`,`currikilicense`,`fullname`,`externalurl`,`resourcechecked`,`oldurl`,`content`,`newcontent`,`logView`,`resourcecheckrequestnote`,`resourcecheckdate`,`resourcecheckid`,`resourcechecknote`,`studentfacing`,`source`,`reviewstatus`,`lastreviewdate`,`reviewedbyid`,`reviewrating`,`technicalcompleteness`,`contentaccuracy`,`pedagogy`,`ratingcomment`,`standardsalignment`,`standardsalignmentcomment`,`subjectmatter`,`subjectmattercomment`,`supportsteaching`,`supportsteachingcomment`,`assessmentsquality`,`assessmentsqualitycomment`,`interactivityquality`,`interactivityqualitycomment`,`instructionalquality`,`instructionalqualitycomment`,`deeperlearning`,`deeperlearningcomment`,`partner`,`createdate`,`type`,`featured`,`page`,`active`,`public`,`xwd_id`,`originalcontent`,`mediatype`,`access`,`memberrating`,`aligned`,`resourcename`,`pageurl`,`indexed`,`lastindexdate`,`indexrequired`,`indexrequireddate` FROM resources WHERE resourceid = '$resourceid'";

      $wpdb->query($query);
      $new_resourceid = $wpdb->insert_id;

      $query = "INSERT INTO collectionelements (`collectionid`, `resourceid`, `displayseqno`) VALUE ($new_resourceid,$new_resourceid,1)";
      $wpdb->query($query);
    }
  }

  function setResourceInappropriate($resourceid) {
    global $wpdb;

    $wpdb->update('resources', array('resourcechecked' => 'Q'), array('resourceid' => $resourceid));
  }

  function setResourceReviewed($resourceid) {
    global $wpdb;

    $wpdb->update('resources', array('reviewstatus' => 'submitted'), array('resourceid' => $resourceid));
  }

  function setResourceViews($resourceid , $visitid = 0) {
    global $wpdb;    
    $userid = get_current_user_id();
    if (!$userid){
      $userid = '10000';
    }
    $data = array('userid' => $userid, 'resourceid' => $resourceid, 'viewdate' => date('Y-m-d H:i:s'), 'sitename' => 'curriki' ,'visitid' => $visitid);
    $wpdb->insert('resourceviews', $data, array('%d', '%d', '%s', '%s' , '%d'));    
  }

  function getJurisdiction() {
    global $wpdb;

    $query = 'select standardid, title, jurisdictioncode from standards where active = "T" order by jurisdictioncode, title';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[$res->jurisdictioncode][$res->standardid] = $res->title;

    return $result;
  }

  function getAssociatedStatements($resourceid) {
    global $wpdb;

    $query = 'SELECT s.statementid, s.description FROM `statements` AS s RIGHT JOIN `resource_statements` AS rs ON (s.`statementid` = rs.`statementid`) WHERE rs.`resourceid` = "' . $resourceid . '"';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[$res->statementid] = array('statementid' => $res->statementid, 'description' => $res->description);

    return $result;
  }

  function getStatement($statementid) {
    global $wpdb;

    $query = 'SELECT * FROM `statements` WHERE `active` LIKE "T" AND `statementid` = "' . $statementid . '"';

    $result = $wpdb->get_row($query);
    if (is_object($result))
      return (array) $result;
    else
      return false;
  }

  function getStatements($standardid) {
    global $wpdb;
    $query = "SELECT  CAST(hi.statementid AS CHAR) AS treeitem, parentid, level, description
              FROM    (SELECT hierarchy_connect_by_parent_eq_prior_id(statementid) AS statementid, @level AS level
                      FROM    (SELECT  @start_with := (select statementid from statements where standardid = '$standardid' and parentid is null),
                                      @id := @start_with,
                                      @level := 0) vars, statements
                      WHERE   @id IS NOT NULL) ho
              JOIN    statements hi ON hi.statementid = ho.statementid WHERE hi.standardid = '$standardid'";
    
    $levels = array();
    $results = $wpdb->get_results($query);
    
    if (count($results) > 0)
      foreach ($results AS $res) {
        $levels[$res->level][$res->treeitem] = (array) $res;
      }
    
    for ($i = count($levels); $i > 0; $i--) {
      foreach ($levels[$i] as $res) {
        $levels[$i - 1][$res['parentid']]['children'][] = $res;
      }
    }
    return $levels[1];
  }

  function getJurisdictionStandards($standardid) {
    global $wpdb;

    $query = 'select standardid, title, jurisdictioncode from standards where active = "T" AND `standardid` = "' . $standardid . '" order by title';

    $result = array();
    $results = $wpdb->get_results($query);
    if (count($results) > 0)
      foreach ($results AS $res)
        $result[$res->jurisdictioncode][$res->standardid] = $res->title;

    return $result;
  }

  function saveResourceStatement($resourceid, $statementid) {
    global $wpdb;

    $userid = get_current_user_id();
    if (!$userid)
      $userid = '10000';

    $wpdb->insert('resource_statements', array('resourceid' => $resourceid, 'statementid' => $statementid, 'userid' => $userid, 'alignmentdate' => date('Y-m-d h:i:s')), array('%d', '%d', '%d', '%s'));
  }

  function removeResourceStatement($resourceid, $statementid) {
    global $wpdb;
    $wpdb->delete('resource_statements', array('resourceid' => $resourceid, 'statementid' => $statementid), array('%d', '%d'));
  }

}

<?php
global $group_loop_source,$group_user,$bp;
$group_loop_source = null;
$group_user = null;

function curr_set_global_vars()
{
    
    global $group_loop_source,$group_user,$bp;     
    if(isset($_POST['action']) && $_POST['action'] == 'groups_filter' && $_POST['object'] == 'groups' && isset($_POST['page']) )
    {       
        if( is_array( $bp->unfiltered_uri ) && in_array("groups", $bp->unfiltered_uri) && !in_array("members", $bp->unfiltered_uri) )
        {            
            $group_loop_source = "groups";               
        }elseif( is_array( $bp->unfiltered_uri ) && in_array("groups", $bp->unfiltered_uri) && in_array("members", $bp->unfiltered_uri) )
        {
            $group_loop_source = "members";           
            if($bp->displayed_user->userdata)
            {                
                $group_user = $bp->displayed_user->userdata;
            }
        }
    }
}

add_action('wp_ajax_nopriv_get_user_library_collection', 'ajax_get_user_library_collection');
add_action('wp_ajax_get_user_library_collection', 'ajax_get_user_library_collection');

function ajax_get_user_library_collection() {
    
  global $wpdb;
  $user_id = get_current_user_id(); //123653
  
  $res = array();
  
    //var_dump($_REQUEST["libraryTopTreeSelectedValue"]);die;
  
  //and gr.groupid is null

  
    if($_POST["libraryTopTreeSelectedValue"] == "My Collections")
    {
        /*$sql = "
        select * from
              (select c.resourceid as RID , c.title as Collection, r.title as Resource, ce.displayseqno , 'My Collections' as Source, c.lasteditdate LastEditDate
                  from resources c
                      left outer join collectionelements ce on c.resourceid = ce.collectionid
                      left outer join resources r on ce.resourceid = r.resourceid
                      
                      left join group_resources gr on gr.resourceid = r.resourceid

              where c.type = 'collection'
                  and c.contributorid = $user_id
                  and c.active = 'T'
                                    
              group by Collection) as a";*/
        $sql = "
        select * from
              (select c.resourceid as `key` , REPLACE(c.title,'\\\','') as title, r.title as Resource, ce.displayseqno , 'My Collections' as Source, c.lasteditdate LastEditDate , true as folder, true as lazy
                  from resources c
                      left outer join collectionelements ce on c.resourceid = ce.collectionid
                      left outer join resources r on ce.resourceid = r.resourceid
                      
                      left join group_resources gr on gr.resourceid = r.resourceid

              where c.type = 'collection'
                  and c.contributorid = $user_id
                  and c.active = 'T'
                                    
              group by title) as a";
        
        $sql.= " order by Source asc,";
        
        
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'most_recent')
        {
            //$sql.= " order by LastEditDate desc";
            $sql.= "LastEditDate desc";
        }
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'a_to_z')
        {
            $sql.= "title asc";
        }
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'z_to_a')
        {
            $sql.= "title desc";
        }
                
        $res = $wpdb->get_results($sql);
    }
    //$sql .= " union all ";
    if($_POST["libraryTopTreeSelectedValue"] == "My Groups" && $_POST["selected_group"] == 0)
    {
        //$my_groups_rs = groups_get_groups( array( 'user_id' => $user_id ) );                
        //$my_groups = $my_groups_rs["groups"];        
        
        $sql_g ="select cbg.id as id,cbg.name as name , c.lasteditdate LastEditDate
                       from cur_bp_groups cbg
                        inner join cur_bp_groups_members cbgm on cbgm.group_id = cbg.id
                        left join group_resources gr on gr.groupid = cbg.id
                        left join resources c on gr.resourceid = c.resourceid
                       where
                        cbgm.user_id = $user_id and cbgm.is_confirmed = 1
                        group by cbgm.group_id order by 
                ";
        
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'most_recent')
        {
            //$sql.= " order by LastEditDate desc";
            $sql_g.= "LastEditDate desc";
        }
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'a_to_z')
        {
            $sql_g.= "name asc";
        }
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'z_to_a')
        {
            $sql_g.= "name desc";
        }
        /*$sql_g ="select cbg.id as id,cbg.name as name , c.lasteditdate LastEditDate
                from cur_bp_groups cbg
                        inner join cur_bp_groups_members cbgm on cbgm.group_id = cbg.id
                        inner join group_resources gr on gr.groupid = cbg.id
                        inner join resources c on gr.resourceid = c.resourceid
                        left outer join collectionelements ce on c.resourceid = ce.collectionid
                        left outer join resources r on ce.resourceid = r.resourceid
                where c.type = 'collection'
                        and c.active = 'T'
                        and cbgm.user_id = $user_id			
                group by cbgm.group_id order by LastEditDate desc";*/
        
        $res_g = $wpdb->get_results($sql_g);
        $my_groups = $res_g;
        
        $group_arr = array();        
        foreach ($my_groups as $group)
        {
            $g = new stdClass();
            $g->key = $group->id;
            $g->title = $group->name;
            $g->Source = "My Groups";            
            $g->folder = true;            
            $g->lazy = true;            
            $group_arr[] = $g;
        }
        //$group_ids = implode(',', $group_ids_arr);
        $res = $group_arr;
    }
    
    if($_POST["libraryTopTreeSelectedValue"] == "My Groups" && $_POST["selected_group"] > 0)
    {        
      
        $group_id = $_POST["selected_group"];
        
      
        /*
        $sql .=" 
                    SELECT r.resourceid as `key`, r.contributorid, r.title as Collection, r.title , 'My Collections' as Source, r.lasteditdate LastEditDate, gr.groupid as groupid , r.type as type , (select count(ce.resourceid) from collectionelements ce where ce.collectionid = r.resourceid) as resourcescount , true as folder, true as lazy
                    FROM group_resources as gr
                       left join resources as r on gr.resourceid = r.resourceid            
                    where groupid = $group_id
                        and type = 'collection'
                        and r.contributorid in (
                                                    SELECT user_id
                                                            FROM cur_bp_groups_members gm														
                                                    where 
                                                            gm.group_id = $group_id
                                                            and gm.is_banned = 0
                                                            and gm.is_confirmed = 1
                                                )
                    UNION   
                       SELECT r.resourceid as `key`, r.contributorid, r.title as Collection, r.title , 'My Collections' as Source, r.lasteditdate LastEditDate, gr.groupid as groupid , r.type as type , (select count(ce.resourceid) from collectionelements ce where ce.collectionid = r.resourceid) as resourcescount , false as folder, false as lazy
                       FROM group_resources as gr
                          left join resources as r on gr.resourceid = r.resourceid            
                       where groupid = $group_id
                           and type = 'resource'                    
                           and r.contributorid in (
                                                        SELECT user_id
                                                                FROM cur_bp_groups_members gm														
                                                        where 
                                                                gm.group_id = $group_id
                                                                and gm.is_banned = 0
                                                                and gm.is_confirmed = 1
                                                   )
             ";
        */
        $sql .="                     
            select * from(
                    SELECT r.resourceid as `key`, r.contributorid, r.title as Collection, REPLACE(r.title,'\\\','') as title , 'My Collections' as Source, r.lasteditdate LastEditDate, gr.groupid as groupid ,r.type as type , 
                    case when gm.user_id is null then false else true end as folder,
                    case when gm.user_id is null then false else true end as lazy
                    FROM group_resources as gr
                    inner join resources as r on gr.resourceid = r.resourceid
                    left outer join (select distinct user_id from cur_bp_groups_members where group_id = $group_id) gm on gm.user_id = r.contributorid     
                    where gr.groupid = $group_id
                    and type = 'collection'                      
                    UNION 
                    SELECT r.resourceid as `key`, r.contributorid, r.title as Collection, REPLACE(r.title,'\\\','') as title , 'My Collections' as Source, r.lasteditdate LastEditDate, gr.groupid as groupid ,r.type as type , false, false as lazy
                    FROM group_resources as gr
                    inner join resources as r on gr.resourceid = r.resourceid          
                    where groupid = $group_id
                    and type = 'resource'
                ) as a
             ";
        
        $sql.= " order by folder desc, type asc";
        /*
        $sql.= " order by Source asc,";
        
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'most_recent')
        {
            //$sql.= " order by LastEditDate desc";
            $sql.= "LastEditDate desc";
        }
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'a_to_z')
        {
            $sql.= "Collection asc";
        }
        if( isset($_POST['sort_by']) && $_POST['sort_by'] == 'z_to_a')
        {
            $sql.= "Collection desc";
        }
        */
        
        $res = $wpdb->get_results($sql);
        
        $group_resources_arr = array();
        foreach ($res as $gc) {                        
            if($gc->folder == 0)
            {
                unset($gc->folder);
            }
            if($gc->lazy == 0)
            {
                unset($gc->lazy);
            }
            $group_resources_arr[] = $gc;
        }        
        $res = $group_resources_arr; 
        
        
        if(count($res) == 0)
        {
            $no_record_obj = new stdClass();
            $no_record_obj->title = "No Record Found !";
            $no_record_obj->no_record = 1;
            $res[] = $no_record_obj;
        }
        
    }

  /*$wpdb->show_errors();
  $wpdb->print_error();
  die;*/
  
  echo json_encode($res);  
  //echo "<pre";
  //var_dump($_REQUEST);  
  wp_die(); 
}

add_action('wp_ajax_nopriv_get_user_library_collection_resources', 'ajax_get_user_library_collection_resources');
add_action('wp_ajax_get_user_library_collection_resources', 'ajax_get_user_library_collection_resources');

function ajax_get_user_library_collection_resources() {
  global $wpdb;
  $rid = $_REQUEST["rid"];
  
  /*
  $sql = "
            select r.resourceid as RID , ce.resourceid as ColRid , r.title as Resource, ce.displayseqno from collectionelements ce
                join resources r on ce.resourceid = r.resourceid
            and ce.collectionid IN ($rid)
                order by ce.displayseqno asc
         ";
  */
  
  //========== Re-arranging the sort order ============
  /*$sql_order_a = "set @ordval := 0";
  $sql_order_b = "update collectionelements set `displayseqno` = (select @ordval := @ordval + 1) where collectionid=$rid order by displayseqno asc;";
  $wpdb->query($sql_order_a);
  $wpdb->query($sql_order_b);
  */
  $sql = "
            select r.resourceid as `key` , ce.resourceid as ColRid , REPLACE(r.title,'\\\','') as title, ce.displayseqno, r.type
            from collectionelements ce
                join resources r on ce.resourceid = r.resourceid
            and ce.collectionid IN ($rid)
                order by r.type, ce.displayseqno asc
         ";  
  $res = $wpdb->get_results($sql);
  
  if(count($res) > 0)
  {
      $rs = array();
      foreach($res as $r)
      {
          if($r->type === "collection")
          {
              $r->folder = 1;
              $r->lazy = 1;
              $r->ExpandableNode = 1;
          }
          $r->ExtendedNodeType = $r->type;          
          $r->ExtendedNode = 1;          
          $rs[] = $r;
      }
      $res = $rs;
  }
  elseif(count($res) == 0)
  {
      $no_record_obj = new stdClass();
      $no_record_obj->title = "No Record Found !";
      $no_record_obj->no_record = 1;
      $no_record_obj->ExtendedNode = 1;
      $res[] = $no_record_obj;
  }
  
  echo json_encode($res);  
  //echo "<pre";
  //var_dump($_REQUEST);  
  //echo $wpdb->last_query;
  wp_die(); 
}


add_action('wp_ajax_nopriv_profile_password_change', 'ajax_profile_password_change');
add_action('wp_ajax_profile_password_change', 'ajax_profile_password_change');
function ajax_profile_password_change() {
    
    if(get_current_user_id() > 0)
    {
        if(isset($_POST['newpassword']) && isset($_POST['confirmpassword']) && $_POST['newpassword'] == $_POST['confirmpassword'])
        {
            global $current_user;            
            $username = $current_user->data->user_login;
            //wp_set_password($_POST['newpassword'], get_current_user_id());                       
            global $wpdb;
            $hash = wp_hash_password( $_POST['newpassword'] );
            $wpdb->update($wpdb->users, array('user_pass' => $hash), array('ID' => get_current_user_id()) );            
            echo json_encode(array("message"=>"Password Changed Successfully!"));
        }else{
            echo json_encode(array("message"=>"Invalid Password Given"));
        }
    }else{
        echo json_encode(array("message"=>"Invalid Request"));
    }
    wp_die();
}


add_action('wp_ajax_nopriv_add_user_library_collection_resource', 'ajax_add_user_library_collection_resource');
add_action('wp_ajax_add_user_library_collection_resource', 'ajax_add_user_library_collection_resource');

function ajax_add_user_library_collection_resource() {
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    global $wpdb;
    $collection_resources = $_POST["collection_resources"];
    
    $hit_node = $_POST["hit_node"];
    $new_node = $_POST["new_node"];
    //$displayseqno_selected = $hit_node["data"]["displayseqno"];
    $collectionid = $collection_resources["key"];
    $resourceid = isset($new_node["resourceid"]) ? $new_node["resourceid"] : 0;        
    
    $source = $_POST["source"];
    
    $collection_resources_arr = $collection_resources["children"];
    
        
    if($source === "My Collections" || $source === "ExpandableNode")
    {        
        $query_rcd = "DELETE FROM collectionelements WHERE collectionid=$collectionid";                        
        $wpdb->query($query_rcd);            
        
        $cntr = 0;
        foreach ($collection_resources_arr as $col_rs)
        {        
            $rid = isset($col_rs["key"]) ? $col_rs["key"] : 0;
            if(isset($col_rs["title"]) && isset($new_node["title"]) && $col_rs["title"] == $new_node["title"])
            {
                $rid = $resourceid;                            
            }            
            
            $rs_row = $wpdb->get_row("select * from resources where resourceid = $rid");
            if($rs_row){
                $wpdb->update('resources', ['indexrequired'=>'T'], ['resourceid'=>$rid]);
                $wpdb->insert( 'collectionelements', array(
                              "collectionid"=> $collectionid , 
                              'resourceid' => $rid,
                              'displayseqno' => $cntr
                         ));
            }
                                     
            $cntr++;
        }        
    }
    
    if($source === "My Groups")
    {   
        
        /*
        $group_id = $collection_resources["key"];
        $query_rcd = "DELETE FROM group_resources                       
                      left join resources as r on gr.resourceid = r.resourceid            
                       where groupid = $group_id                            
                            and r.contributorid in (
                                                        SELECT user_id
                                                                FROM cur_bp_groups_members gm														
                                                        where 
                                                                gm.group_id = $group_id
                                                                and gm.is_banned = 0
                                                                and gm.is_confirmed = 1
                                                   )";
        $wpdb->query($query_rcd);    
        
        $cntr = 0;
        foreach ($collection_resources_arr as $col_rs)
        {        
            $rid = $col_rs["key"];
            if($col_rs["title"] == $new_node["title"])
            {
                $rid = $resourceid;            
            }

            $wpdb->insert('group_resources', array(
                              'groupid'=> $group_id, 
                              'resourceid' => $rid
                     ));

            $cntr++;
        }
        */
        
        $group_id = $collection_resources["key"];        
        $resourceid = $new_node["resourceid"];
        
        $query_rcd = "DELETE FROM group_resources where groupid = $group_id and resourceid = $resourceid";                               
        $wpdb->query($query_rcd);    
        
        $wpdb->insert('group_resources', array(
                              'groupid'=> $group_id, 
                              'resourceid' => $resourceid
                     ));
        
    }
    
    wp_die();
}

add_action('wp_ajax_nopriv_save_statements', 'ajax_save_statements');
add_action('wp_ajax_save_statements', 'ajax_save_statements');

function ajax_save_statements() {
    
  global $wpdb;
  
  $res = new CurrikiResources();  
  $sate_ids = $_POST["sate_ids"];  
  $sate_ids = is_array($sate_ids) ? $sate_ids : array();  
  $sate_ids = array_unique($sate_ids);
  $rid = $_POST['rid'];
  
  
  if( intval($rid) > 0)
  {
    $query_del = "DELETE FROM resource_statements WHERE resourceid IN ($rid)";
    $wpdb->query($query_del);
  }
  
  foreach ($sate_ids as $sid)
  {      
     $res->saveResourceStatement((int)$rid, (int)$sid );      
  }
  wp_die(); 
}


add_action('wp_ajax_nopriv_load_statements', 'ajax_load_statements');
add_action('wp_ajax_load_statements', 'ajax_load_statements');

function ajax_load_statements() {    
    $res = new CurrikiResources();    
    $resource = $res->getResourceById((int) $_POST['rid'], rtrim($_POST['pageurl'], '/'), true);
    echo json_encode($resource);
    wp_die(); 
}

// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

add_action('wp_ajax_nopriv_resource_content_link_log', 'ajax_resource_content_link_log');
add_action('wp_ajax_resource_content_link_log', 'ajax_resource_content_link_log');

function ajax_resource_content_link_log() {

    global $wpdb;

    if ( isset( $_POST['nonce'] ) &&  isset( $_POST['resource_id'] ) && wp_verify_nonce( $_POST['nonce'], 'resource_content_link_log_' . $_POST['resource_id'] ) ) {

        $logEntry = array (
            "resourceid"=> $_POST['resource_id'],
            'url' => $_POST['url']
        );

        $userId = get_current_user_id();
        if ($userId)
            $logEntry["userid"] = $userId;

        $ipAddress = get_client_ip();
        if ($ipAddress != "UNKNOWN")
            $logEntry["ipv4"] = ip2long($ipAddress);

        global $wpdb;
        $wpdb->insert( 'resource_content_link_logs', $logEntry);
    }
    exit();
}

add_action('wp_ajax_nopriv_delete_resource_collection', 'ajax_delete_resource_collection');
add_action('wp_ajax_delete_resource_collection', 'ajax_delete_resource_collection');
function ajax_delete_resource_collection() {    
    global $wpdb;
    $r = $wpdb->delete( 'collectionelements', array( 'resourceid' => $_POST['resourceid'] , 'collectionid' => $_POST['collectionid'] ), array( '%d' , '%d' ) );
    if($r)    
    {
        echo "1";
    }  else {
        echo "0";
    }
    wp_die(); 
}


add_action('wp_ajax_nopriv_cur_widget_search_input_presist', 'ajax_cur_widget_search_input_presist');
add_action('wp_ajax_cur_widget_search_input_presist', 'ajax_cur_widget_search_input_presist');
function ajax_cur_widget_search_input_presist() {    
    global $wpdb;
    //echo $_POST["searchInputWdg"];
    $_SESSION["wdg_sr_subject"] = $_POST["subject"];
    $_SESSION["wdg_sr_subjectarea"] = $_POST["subjectarea"];
    $_SESSION["wdg_sr_education_level"] = $_POST["education_level"];
    $_SESSION["wdg_sr_type"] = $_POST["type"];    
    $_SESSION["wdg_sr_rating"] = $_POST["rating"];    
    //echo "SESSION DATA = ";
    //var_dump( $_POST["rating"] );
    wp_die();    
}

//=== Handel donation modal ============
add_action('wp_ajax_nopriv_dn_modal_handel', 'ajax_dn_modal_handel');
add_action('wp_ajax_dn_modal_handel', 'dn_modal_handel');
function dn_modal_handel() {
    $act = $_POST["act"];
    
    if(!isset( $_SESSION["opendn"] ))
    {
        //echo date("h:i:s");
        $_SESSION["opendn"] = 1;
        $_SESSION["opendntime"] = date("h:i:s");
    }    
    
    //== seceonds spends
    //echo  strtotime( date("h:i:s") ) - strtotime( $_SESSION["opendntime"] );
    $seceonds_spends = strtotime( date("h:i:s") ) - strtotime( $_SESSION["opendntime"] );
    $minutes_spends = $seceonds_spends / 60;
    
    $output = array(
                    "seceonds_spends" =>$seceonds_spends,
                    "minutes_spends" =>$minutes_spends,
                    "act" =>$act,
                   );
    echo json_encode($output);    
    wp_die();   
}

//=== Handel donation modal close ============
add_action('wp_ajax_nopriv_dn_modal_handel_close', 'ajax_dn_modal_handel_close');
add_action('wp_ajax_dn_modal_handel_close', 'dn_modal_handel_close');

function dn_modal_handel_close() {
    
    if(isset($_SESSION["opendntime"]))
    {
        $act = $_POST["act"];            
        $_SESSION["opendntime"] = date("h:i:s");

        //== seceonds spends
        //echo  strtotime( date("h:i:s") ) - strtotime( $_SESSION["opendntime"] );
        $seceonds_spends = strtotime( date("h:i:s") ) - strtotime( $_SESSION["opendntime"] );
        $minutes_spends = $seceonds_spends / 60;

        $output = array(
                        "seceonds_spends" =>$seceonds_spends,
                        "minutes_spends" =>$minutes_spends,
                        "act" =>$act,
                       );
        echo json_encode($output);    
    }  else {
        $output = array(                        
                        "act" =>"error",
                       );
        echo json_encode($output);    
    }
    wp_die();   
}

function striposa($haystack, $needles=array(), $offset=0) {
    $chr = array();
    if(is_array($needles))
      foreach($needles as $needle) {            
        if (stripos(strtolower($haystack), $needle) !== false) {                            
            $chr[$needle] = 1;
        }
      }
    if(empty($chr)) 
        return false;
    else
        return true;    
}

/*
 * Ajax for checking recaptcha
 */

 function validate_recaptcha() {
 
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
		/*
        $recaptcha_val = $_REQUEST['recaptcha_val'];
        // Now we'll return it to the javascript function
        // Anything outputted will be returned in the response
        if($recaptcha_val){
            $_SESSION['g-recaptcha-response'] = $recaptcha_val;
            $_SESSION['i-am-human'] = $recaptcha_val;
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false]);
        }
        // If you're debugging, it might be useful to see what was sent in the $_REQUEST
        // print_r($_REQUEST);
		*/

		$token = $_REQUEST['token'];
		$secret = GOOGLE_RECAPTCHA_SECRET_KEY;

		$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$token);
		$responseData = json_decode($verifyResponse);
		if ($responseData->success) {
			wp_send_json_success($responseData);
		} else {
			wp_send_json_error($responseData);
		}
    }
     
    // Always die in functions echoing ajax content
	wp_send_json_error();
}
 
add_action( 'wp_ajax_nopriv_validate_recaptcha', 'validate_recaptcha' );
add_action( 'wp_ajax_validate_recaptcha', 'validate_recaptcha' );

// include("custom-functions-a.php");
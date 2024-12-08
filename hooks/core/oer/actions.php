<?php

function cur_sm_addsitemap($gsg)
{ 
    
    //============= [start] Build Sitemap Index for Resouces =================
    global $wpdb;
    $resources_query = "select count(resourceid)
            from resources
            where active = 'T'
            and ifnull(access, 'public') <> 'private'
            and title <> 'Favorites' order by resourceid asc";        
    $resources_count = $wpdb->get_var($resources_query);            
    //var_dump( $resources_count );    
    $items_per_page = 10000;
    $total_pages = (int) ceil( $resources_count / $items_per_page );
    //var_dump($total_pages);
    
    $page = 1;        
    // build query
    for($i=0; $i<$total_pages; $i++)
    {
        $page_no = $i+1;
        $offset = ($page_no - 1) * $items_per_page + 1;
        
        $from = $offset;
        $to = $from + ($items_per_page-1);
        //echo "( $page_no [from:$from - to:$to] )";        
        $gsg->AddSitemap("resources-$from-$to", null, time());
    }
    //============= [end] Build Sitemap Index for Resouces =================
    

    //============= [start] Build Sitemap Index for Groups =================
    $groups_query = "select count(id) from {$wpdb->prefix}bp_groups where status = 'public' order by id asc";
    $groups_count = $wpdb->get_var($groups_query);
    //var_dump( $groups_count );
    $items_per_page_g = 10000;
    $total_pages_g = (int) ceil( $groups_count / $items_per_page_g );
    //var_dump($total_pages_g);
    
    $page_g = 1;        
    // build query
    for($i=0; $i<$total_pages_g; $i++)
    {
        $page_no_g = $i+1;
        $offset_g = ($page_no_g - 1) * $items_per_page_g + 1;
        
        $from_g = $offset_g;
        $to_g = $from_g + ($items_per_page_g-1);
        //echo "( $page_no_g [from_g:$from_g - to:$to_g] )";        
        $gsg->AddSitemap("groups-$from_g-$to_g", null, time());
    }
    //============= [end] Build Sitemap Index for Groups =================   
}
add_action("sm_build_index", "cur_sm_addsitemap" , 10, 1);


function cur_sm_build_content($gsg, $type, $params)
{
    
    $params = explode("-", $params);
    $limit = 10000;
    $offset = ((int)$params[0] - 1);
   
    if($type == "resources" && count($params) > 0)
    {
        //var_dump($params);
        global $wpdb;
        $resources_query = "SELECT resourceid,pageurl
                FROM resources
                WHERE active = 'T'
                AND ifnull(access, 'public') <> 'private'
                AND title <> 'Favorites' ORDER BY resourceid asc LIMIT $limit OFFSET $offset";        
        $resources = $wpdb->get_results($resources_query, OBJECT);          
        //var_dump($resources);        
        foreach ($resources as $resource)
        {
            $gsg->AddUrl( site_url()."/oer/". urlencode($resource->pageurl) ,time(),"daily",0.5);
        }
    }
    
    if($type == "groups" && count($params) > 0)
    {
        //var_dump($params);
        global $wpdb;
        $groups_query = "SELECT slug FROM {$wpdb->prefix}bp_groups WHERE status = 'public' ORDER BY id asc LIMIT $limit OFFSET $offset";
        $groups = $wpdb->get_results($groups_query, OBJECT);          
        //var_dump($groups);        
        foreach ($groups as $group)
        {
            $gsg->AddUrl( site_url()."/groups/".urlencode($group->slug)."/" ,time(),"daily",0.5);
        }
    }
}
add_action("sm_build_content", "cur_sm_build_content" , 10, 3);



function curr_get_avatar( $avatar, $id_or_email, $size ) {
    $id = $id = (int) $id_or_email;
    if($id > 0)
    {
        //$avatar = '<img src="http://www.google.comxx'.$id.'" alt="' . get_the_author() . '" width="' . $size . 'px" height="' . $size . 'px" />';
        global $wpdb;                                 
        $q_userinfo = "select * from users where userid = '".$id."'";        
        $userinfo = $wpdb->get_row($q_userinfo);                                                                               
        if(!isset($userinfo)){
            $profile = get_user_meta($id,"profile",true);    
            $profile = isset($profile) ? json_decode($profile) : null; 
            $gender_img = isset($profile) ? "-".$profile->gender : "";
            $avatar = '<img alt="" class="avatar user-'.  $id.'-avatar avatar-'.'user-icon'.' photo" src="'.get_stylesheet_directory_uri().'/images/user-icon-sample'.$gender_img.'.png">';
        }elseif( !isset($userinfo->uniqueavatarfile) ){
            $profile = get_user_meta($id,"profile",true);    
            $profile = isset($profile) ? json_decode($profile) : null; 
            $gender_img = isset($profile) ? "-".$profile->gender : "";
            $avatar = '<img alt="" class="avatar user-'.  $id.'-avatar avatar-'.'user-icon'.' photo" src="'.get_stylesheet_directory_uri().'/images/user-icon-sample'.$gender_img.'.png">';
        }else{
            $avatar = '<img alt="" class="avatar user-'.$userinfo->uniqueavatarfile.'-avatar avatar-'.$userinfo->uniqueavatarfile.' photo" src="https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/'.$userinfo->uniqueavatarfile.'">';
        }
    }
    return $avatar;
}
// add_filter( 'get_avatar', 'curr_get_avatar', 10, 3 );

// add_action( 'genesis_meta', 'curr_meta_tags' );
function curr_meta_tags() {
        
    global $post,$bp;        
    if(is_array($bp->unfiltered_uri)  && $bp->unfiltered_uri[0] == "oer")
    {            
         if (isset($_GET['rid']) || isset($_GET['pageurl'])) {
             
            global $resourceUserGlobal;             
            //$res = new CurrikiResources();
            //$resourceUser = $res->getResourceUserById((int) $_GET['rid'], rtrim($_GET['pageurl'], '/'));                
            $resourceUser = $resourceUserGlobal;
            echo '<meta name="description" content="'. strip_tags(htmlentities(trim($resourceUser["description"]))).'" />';
            echo '<meta name="keywords" content="'. htmlentities($resourceUser["keywords"]). ( strlen($resourceUser["keywords"]) > 0 ? ', ' : '' ) .htmlentities($resourceUser["generatedkeywords"]) . '" />';
     
            
            $current_language = "eng";
            $current_language_slug = "";
            if( defined('ICL_LANGUAGE_CODE') )
            {
                $current_language = cur_get_current_language(ICL_LANGUAGE_CODE); 
                $current_language_slug = ( ICL_LANGUAGE_CODE == "en" ? "":"/".ICL_LANGUAGE_CODE );
            }
  
            if( isset($_GET["pageurl"]) && strlen($_GET["pageurl"]) > 0 )
            {
                echo '<link rel="canonical" href="'.site_url().$current_language_slug.'/oer/'.$_GET['pageurl'].'" />';
            }
            if( isset($_GET["rid"]) && strlen($_GET["rid"]) > 0 )
            {
                echo '<link rel="canonical" href="'.site_url().$current_language_slug.'/oer/'.$resourceUser["pageurl"].'" />';
            }                        
          }            
    }    
}

function cur_resource_title(){
    global $post,$bp;       
    if(is_array($bp->unfiltered_uri) && $bp->unfiltered_uri[0] == "oer")
    {                
        if (isset($_GET['rid']) || isset($_GET['pageurl'])) {            
            
            global $resourceUserGlobal;                                    
            //$res = new CurrikiResources();                
            //$resourceUser = $res->getResourceUserById((int) $_GET['rid'], rtrim($_GET['pageurl'], '/'));            
            $resourceUser = $resourceUserGlobal;
            $title = $resourceUser['title'];   
            $post->post_title = $title;
        }
    }
    
    if(is_array($bp->unfiltered_uri)  && $bp->unfiltered_uri[0] == "groups" && bp_current_component() == "groups")
    {                                   
        if( gettype($bp) === "object" && gettype($bp->groups) === "object" && property_exists($bp->groups, "current_group") && is_object($bp->groups->current_group) && property_exists($post, "post_title") && property_exists($bp->groups->current_group, "name") )
        {            
            $post->post_title = $bp->groups->current_group->name;
        }
    }
    
}
// add_filter('genesis_title','cur_resource_title' , 2000);
?>
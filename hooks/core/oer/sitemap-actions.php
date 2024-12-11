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
        $gsg->add_sitemap("resources-$from-$to", null, time());
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
        $gsg->add_sitemap("groups-$from_g-$to_g", null, time());
    }
    //============= [end] Build Sitemap Index for Groups =================   
}
add_action("sm_build_index", "cur_sm_addsitemap" , 10, 1);


function cur_sm_build_content($gsg, $type, $params)
{
    var_dump($type); die();
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
            $gsg->add_url( site_url()."/oer/". urlencode($resource->pageurl) ,time(),"daily",0.5);
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
            $gsg->add_url( site_url()."/groups/".urlencode($group->slug)."/" ,time(),"daily",0.5);
        }
    }
}
add_action("sm_build_content", "cur_sm_build_content" , 10, 3);


?>
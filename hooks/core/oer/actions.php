<?php

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
<?php
/* @include("curriki_resources.php");
@include("curriki_clever.php");
@include("search.php");
@include("helpers/translation-functions.php");
@include("helpers/common-functions.php");
@include("unload-plugins.php");
 */

class CurrikiCountVisitorsWidget extends WP_Widget {

  function __construct() {
    // Instantiate the parent object
    parent::__construct(false, 'Count Visitors Widget');
  }

  function widget($args, $instance) {
    // Widget output
    global $wpdb;
    $q = "SELECT visitors FROM sites WHERE sitename = 'curriki'";
    $v = $wpdb->get_var($q);
    echo number_format($v, '0', '.', ',');
    //echo '100000';
  }

  function update($new_instance, $old_instance) {
    // Save widget options
  }

  function form($instance) {
    // Output admin widget options form
  }

}

function getCurrikiStats($field) {
  global $wpdb;
  $q = "SELECT $field FROM sites WHERE sitename = 'curriki'";
  $v = $wpdb->get_var($q);
  return number_format($v, '0', '.', ',');
}

// run it before the headers and cookies are sent

function curriki_user_login_screen($error = '') {
  $dashboard_page = 6015;
  if (is_user_logged_in()) {
    $redirect_url = get_permalink($dashboard_page);
    curriki_redirect($redirect_url);
    return "You are already Logged in!";
  } else {
    ob_start();
    @include("curriki_user_login_screen.php");
    $login_screen = ob_get_contents();
    ob_end_clean();
    return $login_screen;
  }
}

add_shortcode("user_login_screen", "curriki_user_login_screen");

function fn_curriki_forgot_password() {
  /* if($_POST['reset_email']){
    global $wpdb;
    $q = $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_email = %s ", $_POST['reset_email']);
    $user = $wpdb->get_row($q);
    //print_r($user);
    if(is_object($user)){
    //$user->user_email = "sajid_154@hotmail.com";
    $reset = md5($user->user_email);
    update_user_meta($user->ID, "forgot_password_value", $reset);
    ob_start();
    @include("password_reset_mail_content.php");
    echo "Your username is: ".$user->user_name."<br />Please click <a href='".get_bloginfo('url')."/reset-password?reset=".$reset."'>here</a> to reset your password.";
    $password_reset_mail_content = ob_get_contents();
    ob_end_clean();
    wp_mail("sajidpersonal@hotmail.com", "Password Reset", 'test password reset');
    wp_mail($user->user_email, "Password Reset", $password_reset_mail_content);
    wp_mail("sajidpersonal@hotmail.com", "Password Reset", $password_reset_mail_content);
    $reset_screen = "Please check your email address and follow instructions to reset password.";
    //return $password_reset_mail_content;
    }
    else{
    $reset_screen = "We don't have this email address registered!";
    }
    }else{
    ob_start();
    @include("curriki_forgot_password.php");
    $reset_screen = ob_get_contents();
    ob_end_clean();
    }
    return $reset_screen; */
}

if (!empty($_GET['curriki_ajax_action']) and $_GET['curriki_ajax_action'] == 'reset_email') {
  if ($_POST['reset_email']) {
    global $wpdb;
    $q = $wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_email = %s ", $_POST['reset_email']);
    $user = $wpdb->get_row($q);
    if (is_object($user)) {
      //$user->user_email = "sajid_154@hotmail.com";
      $reset = md5($user->user_email);
      update_user_meta($user->ID, "forgot_password_value", $reset);
      ob_start();
      @include("password_reset_mail_content.php");
      echo "Your username is: " . $user->user_login . ". Please click this link " . get_bloginfo('url') . "/reset-password?reset=" . $reset . " to reset your password.";
      $password_reset_mail_content = ob_get_contents();
      ob_end_clean();
      wp_mail($user->user_email, "Password Reset", $password_reset_mail_content);
      //wp_mail("sajidpersonal@hotmail.com", "Password Reset", $password_reset_mail_content);
      $reset_screen = "1";
    } else {
      $reset_screen = __("We don't have this email address registered!","curriki");
    }
  } else {
    $reset_screen = __("Please enter your email address below.","curriki");
  }
  echo $reset_screen;
  die;
}

add_shortcode("curriki_forgot_password", "fn_curriki_forgot_password");

function fn_curriki_reset_password() {    
    
  global $wpdb;
  $q = $wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE meta_value = %s ", $_GET['reset']);
  $user = $wpdb->get_row($q);
  if (!is_object($user)) {
    return;
  } else {
    if ($_POST['pwd']) {
      if ($_POST['pwd'] == $_POST['confirm_pwd']) {
        wp_set_password($_POST['pwd'], $user->user_id);
        
        //updating password in users table
        $wpdb->query( $wpdb->prepare("UPDATE users
                    SET
                    ifneeded = AES_ENCRYPT(%s, '".AES_KEY."'), 
                    indexrequired = 'T',
                    indexrequireddate = %s
                    where userid = %d",
            $_POST['pwd'],
            date('Y-m-d H:i:s'),
            $user->user_id
            )
    );
        
        delete_user_meta($user->user_id, "forgot_password_value", $_GET['reset']);
        return "Password changed.";
      } else {
        ob_start();
        echo "Password and confirm Password don't match";
        @include("curriki_reset_password.php");
        $screen = ob_get_contents();
        ob_end_clean();
        return $screen;
      }
    }
    ob_start();
    @include("curriki_reset_password.php");
    $screen = ob_get_contents();
    ob_end_clean();
    return $screen;
  }
}

add_shortcode("curriki_reset_password", "fn_curriki_reset_password");

function curriki_user_signup_screen($error = '') {
  if (!empty($_POST) and empty($_POST['curriki_errors']))
    return "Registration complete.";
  $dashboard_page = 6015;
  if (is_user_logged_in()) {
    $redirect_url = get_permalink($dashboard_page);
    curriki_redirect($redirect_url);
    return "You are already Logged in!";
  } else {
    ob_start();
    @include("curriki_user_signup_screen.php");
    $signup_screen = ob_get_contents();
    ob_end_clean();
    return $signup_screen;
  }
}

add_shortcode("curriki_user_signup", "curriki_user_signup_screen");

function curriki_custom_newsletter() {
  if (empty($_POST['signup_newsletter']))
    return;
  global $wpdb;
  $dashboard_page = 6015;
  if (empty($_POST['nl_name']))
    $errors[] = "Name is required.";
  if (empty($_POST['nl_email']))
    $errors[] = "Email is required.";
  if (!empty($_POST['nl_email'])) {
    $wpdb->prepare("SELECT * FROM newsletters WHERE email = %s ", $_POST['nl_email']);
    $q_newsletter_email = $wpdb->prepare("SELECT * FROM newsletters WHERE email =  %s", $_POST['nl_email']);
    $newsletter_email = $wpdb->get_row($q_newsletter_email);
    if (!empty($newsletter_email))
      $errors[] = "This email has already been signup for newsletter.";
  }


  if (empty($errors)) {
    $wpdb->insert(
            'newsletters', array(
        'name' => $_POST['nl_name'],
        'email' => $_POST['nl_email']
            ), array(
        '%s',
        '%s'
            )
    );
    //curriki_redirect(get_permalink($dashboard_page));
    return "Thanks for signing up!";
  } else {
    $_POST['curriki_errors'] = $errors;
    curriki_newsletter_screen($errors);
  }
}

// run it before the headers and cookies are sent

function curriki_newsletter_screen($error = '') {
  if (!empty($_POST) and empty($_POST['curriki_errors']))
    return "You have been signed up for newsletter.";
  //return "newsletter";
  ob_start();
  @include("curriki_user_newsletter_screen.php");
  $signup_screen = ob_get_contents();
  ob_end_clean();
  return $signup_screen;
}

add_shortcode("curriki_newsletter", "curriki_newsletter_screen");

if (isset($_GET['test_gapi'])) {
  ?>
  <form method="post" action="">
    <input type="text" name="email" placeholder="Email" value="<?php echo $_POST['email'] ?>" />
    <input type="text" name="password" placeholder="Password" value="<?php echo $_POST['password'] ?>" />
    <input type="submit" value="Send" />
  </form>
  <?php
  include "gapi.class.php";
  //$ga = new gapi('rgreenawalt@curriki.org','Stagewin1!');
  //$profileId = 1841781;
  $ga = new gapi($_POST['email'], $_POST['password']);

  $ga->requestReportData(145141242, array('browser', 'browserVersion'), array('pageviews', 'visits'));

  foreach ($ga->getResults() as $result) {
    echo '<strong>' . $result . '</strong><br />';
    echo 'Pageviews: ' . $result->getPageviews() . ' ';
    echo 'Visits: ' . $result->getVisits() . '<br />';
  }

  echo '<p>Total pageviews: ' . $ga->getPageviews() . ' total visits: ' . $ga->getVisits() . '</p>';
  die;
}

if (isset($_GET['test_newgapi'])) {
  // api dependencies
  require 'vendor/autoload.php';

  // create client object and set app name
  $client = new Google_Client();
  $client->setApplicationName('Curriki'); // name of your app
  // set assertion credentials
  $client->setAssertionCredentials(
          new Google_Auth_AssertionCredentials(
          '298993209434-82tqk9pn83cit80ifhqmaupubdgflmqd@developer.gserviceaccount.com', // email you added to GA
          array('https://www.googleapis.com/auth/analytics.readonly'),
          //file_get_contents('vendor/keys/client_secret_298993209434-82tqk9pn83cit80ifhqmaupubdgflmqd.apps.googleusercontent.com.json') // keyfile you downloaded
          '{"web":{"auth_uri":"https://accounts.google.com/o/oauth2/auth","client_secret":"y5juDWQhl0dQNPKXh2PrTj8U","token_uri":"https://accounts.google.com/o/oauth2/token","client_email":"298993209434-82tqk9pn83cit80ifhqmaupubdgflmqd@developer.gserviceaccount.com","redirect_uris":["http://cg.curriki.org/oauth2callback"],"client_x509_cert_url":"https://www.googleapis.com/robot/v1/metadata/x509/298993209434-82tqk9pn83cit80ifhqmaupubdgflmqd@developer.gserviceaccount.com","client_id":"298993209434-82tqk9pn83cit80ifhqmaupubdgflmqd.apps.googleusercontent.com","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","javascript_origins":["http://cg.curriki.org"]}}'
          )
  );

  // other settings
  $client->setClientId('298993209434-82tqk9pn83cit80ifhqmaupubdgflmqd.apps.googleusercontent.com'); // from API console
  // create service and get data
  $service = new Google_Service_Analytics($client);
  echo '<pre>';
  var_dump($service->management_accounts->listManagementAccounts());
  die;
}

class CurrikiNewsletterWidget extends WP_Widget {

  function __construct() {
    // Instantiate the parent object
    parent::__construct(false, 'Newsletter');
  }

  function widget($args, $instance) {
    //echo "newsletter";
    echo curriki_newsletter_screen();
  }

  function update($new_instance, $old_instance) {
    // Save widget options
  }

  function form($instance) {
    // Output admin widget options form
  }

}

function curriki_redirect($url = "") {
  if (empty($url))
    return;
  echo '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
}

function curriki_redirect_login() {
  //$login_page_url = get_permalink(212);
  curriki_redirect(home_url() . '?modal=login');
}

function curriki_show_featured_item($item = 'homepagealigned') {
    
  $current_language = "eng";
  $current_language_slug = "";
  if( defined('ICL_LANGUAGE_CODE') )
  {
      $current_language = cur_get_current_language(ICL_LANGUAGE_CODE); 
      $current_language_slug = ( ICL_LANGUAGE_CODE == "en" ? "":"/".ICL_LANGUAGE_CODE );
  }
    
  global $wpdb;
  $groups = '';
  $cur_date = date('Y-m-d H:i:s');
  if ($item == 'homepagequote')
    $location = "quote";
  else
    $location = $item;

  $q_featured_items = "SELECT * FROM featureditems WHERE location = '$location' "
          . "AND (active = 'T' OR active = '1') "
          . "AND featuredstartdate < '" . $cur_date . "' AND featuredenddate > '" . $cur_date . "' AND displayseqno != '' ORDER BY displayseqno ASC";
  
  if($current_language!='eng')
  {    
    $q_featured_items = cur_featureditems_ml_query($current_language,$location,$cur_date);        
  }
  
  $featured_items = $wpdb->get_results($q_featured_items);
  $ic = "";
  if (count($featured_items) > 0)
    $site_url = site_url();
  if ($item == 'dashboarduser') {
    $ic .= '<ul>';
    foreach ($featured_items as $fi) {
      $ic .= '<li class="member">';
      $q_user = "SELECT * FROM users u inner join cur_users cu on cu.ID = u.userid WHERE userid = '" . $fi->itemid . "'";
      $user = $wpdb->get_row($q_user);

      if (isset($user) && isset($user->uniqueavatarfile)) {
        $ic .= '<img class="border-grey" src="' . 'https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/' . $user->uniqueavatarfile . '" alt="member-name" />';
      } else {

        $profile = get_user_meta($user->userid, "profile", true);
        $profile = isset($profile) ? json_decode($profile) : null;
        $gender_img = isset($profile) ? "-" . $profile->gender : "";
        $ic .= '<img class="border-grey" src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample' . $gender_img . '.png' . '" alt="member-name" />';
      }
      
      //$user_display_name = $user->firstname . ' ' . $user->lastname;      
      $user_display_name = $fi->displaytitle;      
      $ic .= '<div class="member-info"><span class="member-name name"><A href="javascript:void(0);">' . $user_display_name . '</a></span><span class="occupation">' . __(UCWords($user->membertype),"curriki") . '</span><span class="location">' . $user->city . ', ' . $user->state . ', ' . $user->country . '</span></div>';
      $ic .= '</li>';
    }
    $ic .= '</ul>';
  } elseif ($item == 'partner') {
        
    $ic .= '<ul>';
    foreach ($featured_items as $fi) {
      $ic .= '<li class="partner"><a href="' . $fi->link . '">';
      if (!empty($fi->image))
        $ic .= '<img class="border-grey partners_img" src="' . $fi->image . '" alt="' . $fi->featuredtext . '" />';
      else
        $ic .= '<img class="border-grey partners_img" src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" alt="partner-name" />';
      $ic .= '</a><div class="partner-info"><span class="partner-name name"><a href="' . $fi->link . '">' . __($fi->featuredtext,'curriki') . '</a></span></div>';
      $ic .= '</li>';
    }
    $ic .= '</ul>';
  } elseif ($item == 'homepagepartner') {
    $ic .= '<div class="owl-carousel owl-theme">';
    foreach ($featured_items as $fi) {
      $itemUrl = '';

      if ($fi->itemidtype == 'collection') {
        $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
        $resource = $wpdb->get_row($q_resource);
        $itemUrl = get_bloginfo('url') . '/oer/' . $resource->pageurl;
      } else if ($fi->itemidtype == 'community') {
        $q_community = "SELECT * FROM communities WHERE communityid = '" . $fi->itemid . "'";
        $community = $wpdb->get_row($q_community);
        $itemUrl = get_bloginfo('url') . '/community/' . $community->url;
      }

      $ic .= '<a class="item partner-logo" href="' . $itemUrl . '">';
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" width="303" height="194" alt="' . $fi->featuredtext . '" />';
      else
        $ic .= '<img src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" width="303" height="194" alt="partner-name" />';
      $ic .= '</a>';
    }

    $ic .= '</div>';
  }elseif ($item == 'dashboardresource') {
    $homepageCollectionItems = '';

    $education_levels = array(
      array('title' => __('Preschool (Ages 0-4)', 'curriki'), 'levels' => '8|9', 'arlevels' => array(8, 9)),
      array('title' => __('Kindergarten-Grade 2 (Ages 5-7) ', 'curriki'), 'levels' => '3|4', 'arlevels' => array(3, 4)),
      array('title' => __('Grades 3-5 (Ages 8-10)', 'curriki'), 'levels' => '5|6|7', 'arlevels' => array(5, 6, 7)),
      array('title' => __('Grades 6-8 (Ages 11-13)', 'curriki'), 'levels' => '11|12|13', 'arlevels' => array(11, 12, 13)),
      array('title' => __('Grades 9-10 (Ages 14-16)', 'curriki'), 'levels' => '15|16', 'arlevels' => array(15, 16)),
      array('title' => __('Grades 11-12 (Ages 16-18)', 'curriki'), 'levels' => '17|18', 'arlevels' => array(17, 18)),
      array('title' => __('College & Beyond', 'curriki'), 'levels' => '23|24|25', 'arlevels' => array(23, 24, 25)),
      array('title' => __('Professional Development', 'curriki'), 'levels' => '19|20', 'arlevels' => array(19, 20)),
      array('title' => __('Special Education', 'curriki'), 'levels' => '26|21', 'arlevels' => array(26, 21)),
    );

    foreach ($featured_items as $fi) {
      $educationlevelid = array();
      $itemUrl = '';

      $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
      $resource = $wpdb->get_row($q_resource);
      $itemUrl = get_bloginfo('url') . '/oer/' . $resource->pageurl;

      foreach ($wpdb->get_results("SELECT * FROM resource_educationlevels WHERE resourceid = '" . $fi->itemid . "'", ARRAY_A) as $row)
      {
        $educationlevelid[] = $row['educationlevelid'];
      }

      $educationlevels = '';
      foreach ($education_levels  as $education_level)
      {
        $commonCount = count(array_intersect($education_level['arlevels'], $educationlevelid));

        if ($commonCount > 0) {
          if ($educationlevels) {
            $educationlevels .= ', '.$education_level['title'];
            break;
          }

          $educationlevels = $education_level['title'];
        }
      }

      $homepageCollectionItems .= '<div class="item">';
        $homepageCollectionItems .= '<div class="item-thumbnail">';
          $homepageCollectionItems .= '<a href="' . $itemUrl . '">';
            if (!empty($fi->image))
              $homepageCollectionItems .= '<img class="w-100" src="' . $fi->image . '" width="303" height="207" alt="' . $fi->displaytitle . '">';
            else
              $homepageCollectionItems .= '<img class="w-100" src="' . $site_url . '/wp-content/uploads/2015/03/user-icon-sample.png" width="303" height="207" alt="' . $fi->displaytitle . '">';
          $homepageCollectionItems .= '</a>';
        $homepageCollectionItems .= '</div>';
        $homepageCollectionItems .= '<div class="item-body">';
          $homepageCollectionItems .= '<div class="info-text-body">';
            $homepageCollectionItems .= '<h4 class="info-title"><a href="' . $itemUrl . '">' . $fi->displaytitle . '</a></h4>';
            $homepageCollectionItems .= '<p>' . $educationlevels . '</p>';
          $homepageCollectionItems .= '</div>';

        $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '</div>';
    }

    $ic .= '<div class="owl-carousel owl-theme dashboardresource">';
    $ic .= $homepageCollectionItems;
    $ic .= '</div>';
  } elseif ($item == 'homepagecollection') {
    $homepageCollectionItems = '';

    $education_levels = array(
      array('title' => __('Preschool (Ages 0-4)', 'curriki'), 'levels' => '8|9', 'arlevels' => array(8, 9)),
      array('title' => __('Kindergarten-Grade 2 (Ages 5-7) ', 'curriki'), 'levels' => '3|4', 'arlevels' => array(3, 4)),
      array('title' => __('Grades 3-5 (Ages 8-10)', 'curriki'), 'levels' => '5|6|7', 'arlevels' => array(5, 6, 7)),
      array('title' => __('Grades 6-8 (Ages 11-13)', 'curriki'), 'levels' => '11|12|13', 'arlevels' => array(11, 12, 13)),
      array('title' => __('Grades 9-10 (Ages 14-16)', 'curriki'), 'levels' => '15|16', 'arlevels' => array(15, 16)),
      array('title' => __('Grades 11-12 (Ages 16-18)', 'curriki'), 'levels' => '17|18', 'arlevels' => array(17, 18)),
      array('title' => __('College & Beyond', 'curriki'), 'levels' => '23|24|25', 'arlevels' => array(23, 24, 25)),
      array('title' => __('Professional Development', 'curriki'), 'levels' => '19|20', 'arlevels' => array(19, 20)),
      array('title' => __('Special Education', 'curriki'), 'levels' => '26|21', 'arlevels' => array(26, 21)),
    );

    foreach ($featured_items as $fi) {
      $educationlevelid = array();
      $itemUrl = '';
      $memberrating = 0;
      $reviewrating = 0;
      $communityTagline = '';

      if ($fi->itemidtype == 'collection') {
        $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
        $resource = $wpdb->get_row($q_resource);
        $itemUrl = get_bloginfo('url') . '/oer/' . $resource->pageurl;
        $memberrating = $resource->memberrating ? $resource->memberrating : 0;
        $reviewrating = $resource->reviewrating ? $resource->reviewrating : 0;

        foreach ($wpdb->get_results("SELECT * FROM resource_educationlevels WHERE resourceid = '" . $fi->itemid . "'", ARRAY_A) as $row)
        {
          $educationlevelid[] = $row['educationlevelid'];
        }
      } else if ($fi->itemidtype == 'community') {
        $q_community = "SELECT * FROM communities WHERE communityid = '" . $fi->itemid . "'";
        $community = $wpdb->get_row($q_community);
        $itemUrl = get_bloginfo('url') . '/community/' . $community->url;
        $communityTagline = $community->tagline;

        $resourceid = array();
        foreach ($wpdb->get_results("SELECT * FROM community_collections WHERE communityid = '" . $fi->itemid . "'", ARRAY_A) as $row)
        {
          $resourceid[] = $row['resourceid'];
        }

        foreach ($wpdb->get_results("SELECT * FROM resource_educationlevels WHERE resourceid IN (" . implode(',', $resourceid) . ")", ARRAY_A) as $row)
        {
          $educationlevelid[] = $row['educationlevelid'];
        }
      }

      $educationlevels = '';
      foreach ($education_levels  as $education_level)
      {
        $commonCount = count(array_intersect($education_level['arlevels'], $educationlevelid));

        if ($commonCount > 0) {
          if ($educationlevels) {
            $educationlevels .= ', '.$education_level['title'];
            break;
          }

          $educationlevels = $education_level['title'];
        }
      }

      $homepageCollectionItems .= '<div class="item">';
        $homepageCollectionItems .= '<div class="item-thumbnail">';
          $homepageCollectionItems .= '<a href="' . $itemUrl . '">';
            if (!empty($fi->image))
              $homepageCollectionItems .= '<img class="w-100" src="' . $fi->image . '" width="303" height="207" alt="' . $fi->displaytitle . '">';
            else
              $homepageCollectionItems .= '<img class="w-100" src="' . $site_url . '/wp-content/uploads/2015/03/user-icon-sample.png" width="303" height="207" alt="' . $fi->displaytitle . '">';
          $homepageCollectionItems .= '</a>';
        $homepageCollectionItems .= '</div>';
        $homepageCollectionItems .= '<div class="item-body">';
          $homepageCollectionItems .= '<div class="info-text-body">';
            $homepageCollectionItems .= '<h4 class="info-title"><a href="' . $itemUrl . '">' . $fi->displaytitle . '</a></h4>';
            $homepageCollectionItems .= '<p>' . $educationlevels . '</p>';
          $homepageCollectionItems .= '</div>';

          $homepageCollectionItems .= '<footer class="card-footer">';

          if ($fi->itemidtype == 'collection') {
            $homepageCollectionItems .= '<div class="member-rating">';
              $homepageCollectionItems .= '<span class="rating-stars">';

              for ($count = 1; $count <= 5; $count ++) {
                if ($count <= $memberrating) {
                  $homepageCollectionItems .= '<span class="fa fa-star"></span>';
                } else {
                  $homepageCollectionItems .= '<span class="fa fa-star-o"></span>';
                }
              }

              $homepageCollectionItems .= '</span>';
              $homepageCollectionItems .= '<span class="member-rating-title">Member Rating</span>';
            $homepageCollectionItems .= '</div>';

            $homepageCollectionItems .= '<div class="curriki-rating">';
              $homepageCollectionItems .= '<span class="rating-point">' . $reviewrating . '</span>';
              $homepageCollectionItems .= '<span class="curriki-rating-title">Curriki Rating</span>';
            $homepageCollectionItems .= '</div>';
          } else if ($fi->itemidtype == 'community') {
            $homepageCollectionItems .= '<div class="col-sm-12">';
            $homepageCollectionItems .= $communityTagline;
            $homepageCollectionItems .= '</div>';
          }

          $homepageCollectionItems .= '</footer>';

        $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '</div>';
    }

    $ic .= '<div class="owl-carousel owl-theme">';
    $ic .= $homepageCollectionItems;
    $ic .= '</div>';
  } elseif ($item == 'homepageresource') {
    foreach ($featured_items as $fi) {
      $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
      $resource = $wpdb->get_row($q_resource);
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" class="circle border-white" />';
      else
        $ic .= '<img src="' . $site_url . '/wp-content/uploads/2015/03/user-icon-sample.png" class="circle border-white" />';
      $ic .= '<div class="side-tab-title"><a href="' . get_bloginfo('url') . '/oer/' . $resource->pageurl . '">' . $fi->displaytitle . '</a></div>';
      $ic .= __($fi->featuredtext,'curriki') . '<div class="clear">&nbsp;</div>';
    }
  }elseif ($item == 'homepagealigned') {
    foreach ($featured_items as $fi) {
      $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
      $resource = $wpdb->get_row($q_resource);
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" class="circle border-white" />';
      else
        $ic .= '<img src="' . $site_url . '/wp-content/uploads/2015/03/user-icon-sample.png" class="circle border-white" />';
      $ic .= '<div class="side-tab-title"><a href="' . get_bloginfo('url') . '/oer/' . $resource->pageurl . '">' . __($fi->displaytitle,'curriki') . '</a></div>';
      $ic .= __($fi->featuredtext,'curriki') . '<div class="clear">&nbsp;</div>';
    }
  }elseif ($item == 'quote') {
    ob_start();
    echo '<div id="content" class="activity" role="main">';
    gconnect_locate_template(array('activity/activity-loop.php'), true);
    echo '</div><!-- .activity -->';

    /* if ( is_user_logged_in() ) {
      echo bp_loggedin_user_domain();
      } */
    $activity = ob_get_contents();
    ob_end_clean();
    $ic .= $activity;
    /* foreach($featured_items as $fi){
      $member_activity .= '<div class="group-activity-card page-activity-card">';
      $member_activity .= '<div class="group-activity-member page-activity-member">';
      $member_activity .= '<img class="border-grey circle" src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" alt="member-name" />';
      $member_activity .= '</div>';
      $member_activity .= '<div class="group-activity page-activity">';
      $member_activity .= '<div class="group-activity-header page-activity-header">';
      $member_activity .= '<div class="group-activity-info page-activity-info">';
      $member_activity .= '<a href="#">Firstname Lastname</a> contributed to <a href="#">This Group Name</a>';
      $member_activity .= '</div>';
      $member_activity .= '<div class="group-activity-time page-activity-time">';
      $member_activity .= 'August 14, 2014  5:15 PM EST';
      $member_activity .= '</div>';
      $member_activity .= '</div>';
      $member_activity .= '<div class="group-activity-body page-activity-body resource-pdf border-grey">';
      $member_activity .= '<div class="group-activity-body-content page-activity-body-content">';
      $member_activity .= '<a class="resource-name" href="#">This Resource Name</a>';
      $member_activity .= 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less...';
      $member_activity .= '<div class="rate-align"><a href="#">Rate Resource</a><a href="#">Align to Standards</a></div>';
      $member_activity .= '</div>';
      $member_activity .= '</div>';
      $member_activity .= '</div>';
      $member_activity .= '</div>';

      $ic .= $member_activity;
      } */
  } elseif ($item == 'homepagequote') {
    foreach ($featured_items as $fi) {
      $user_q = "SELECT * FROM users WHERE userid='" . $fi->itemid . "'";
      $user = $wpdb->get_row($user_q);
      $testimonials = "";
      $testimonials .= '<div class="grid_6 testimonial">';
      $testimonials .= '<div class="testimonial-person grid_4">';
      if ($user->uniqueavatarfile)
        $testimonials .= '<img width="103" height="103" alt="user-icon-sample" class="circle" src="https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/' . $user->uniqueavatarfile . '">';
      else
        $testimonials .= '<img width="103" height="103" alt="user-icon-sample" class="circle" src="' . get_bloginfo('url') . '/wp-content/uploads/2015/03/user-icon-sample.png">';

      $testimonials .= '<div class="testimonial-name">' . $user->firstname . ' ' . $user->lastname . '</div>';
      $testimonials .= '<div class="testimonial-place">' . $fi->displaytitle . '</div>';
      $testimonials .= '</div>';
      $testimonials .= '<div class="grid_8"><div class="testimonial-text rounded-borders-full">' . __($fi->featuredtext,'curriki') . '</div></div>';
      $testimonials .= '</div>';
      $ic .= $testimonials;
    }
  }elseif ($item == 'dashboardgroup') {
    $groups .= '<ul>';
    if (count($featured_items) > 0)
      foreach ($featured_items as $fi) {
        $members_q = "SELECT slug FROM cur_bp_groups WHERE id='" . $fi->itemid . "'";
        $slug = $wpdb->get_var($members_q);
        $groups .= '<li class="group">';
        if ($fi->image != '')
          $groups .= '<img class="border-grey" src="' . $fi->image . '" alt="$fi->displaytitle" />';
        else
          $groups .= '<img class="border-grey" src="' . get_stylesheet_directory_uri() . '/images/group-icon-sample.png" alt="$fi->displaytitle" />';
        $groups .= '<div class="group-info"><span class="group-name name"><a href="' . get_bloginfo('url') . '/groups/' . $slug . '">' . $fi->displaytitle . '</a></span></div>';
        $groups .= '</li>';
      }
    $groups .= '</ul>';
    $ic .= $groups;
  }else {
    foreach ($featured_items as $fi) {
      $gom = 'groups';
      $slug = $fi->itemid;
      if ($fi->itemidtype == 'user') {
        $gom = 'members';
        $members_q = "SELECT user_nicename FROM cur_users WHERE ID='" . $fi->itemid . "'";
        $slug = $wpdb->get_var($members_q);
      } else {
        $groups_q = "SELECT slug FROM cur_bp_groups WHERE id='" . $fi->itemid . "'";
        $slug = $wpdb->get_var($groups_q);
      }
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" class="circle border-white" />';
      else
        $ic .= '<img src="' . site_url() . '/wp-content/uploads/2015/03/user-icon-sample.png" class="circle border-white" />';
      $ic .= '<div class="side-tab-title"><a href="' . get_bloginfo('url') . '/' . $gom . '/' . $slug . '">' . __($fi->displaytitle,'curriki') . '</a></div>';
      $ic .= __($fi->featuredtext,'curriki') . '<div class="clear">&nbsp;</div>';
    }
  }
  return $ic;
}

function curriki_show_new_item() {

  global $wpdb;

  $q_featured_items = "SELECT * FROM resources WHERE public = 'T' AND access = 'public' AND active = 'T' AND approvalstatus = 'approved' ORDER BY createdate DESC LIMIT 6";

  $featured_items = $wpdb->get_results($q_featured_items);
  $ic = "";
  $theme_url = get_stylesheet_directory_uri();

  $homepageCollectionItems = '';

  $education_levels = array(
    array('title' => __('Preschool (Ages 0-4)', 'curriki'), 'levels' => '8|9', 'arlevels' => array(8, 9)),
    array('title' => __('Kindergarten-Grade 2 (Ages 5-7) ', 'curriki'), 'levels' => '3|4', 'arlevels' => array(3, 4)),
    array('title' => __('Grades 3-5 (Ages 8-10)', 'curriki'), 'levels' => '5|6|7', 'arlevels' => array(5, 6, 7)),
    array('title' => __('Grades 6-8 (Ages 11-13)', 'curriki'), 'levels' => '11|12|13', 'arlevels' => array(11, 12, 13)),
    array('title' => __('Grades 9-10 (Ages 14-16)', 'curriki'), 'levels' => '15|16', 'arlevels' => array(15, 16)),
    array('title' => __('Grades 11-12 (Ages 16-18)', 'curriki'), 'levels' => '17|18', 'arlevels' => array(17, 18)),
    array('title' => __('College & Beyond', 'curriki'), 'levels' => '23|24|25', 'arlevels' => array(23, 24, 25)),
    array('title' => __('Professional Development', 'curriki'), 'levels' => '19|20', 'arlevels' => array(19, 20)),
    array('title' => __('Special Education', 'curriki'), 'levels' => '26|21', 'arlevels' => array(26, 21)),
  );

  foreach ($featured_items as $fi) {
    $educationlevelid = array();
    $itemUrl = '';
    $memberrating = 0;
    $reviewrating = 0;

    $itemUrl = get_bloginfo('url') . '/oer/' . $fi->pageurl;
    $memberrating = $fi->memberrating ? $fi->memberrating : 0;
    $reviewrating = $fi->reviewrating ? $fi->reviewrating : 0;


    $q_resource_subjectarea = "SELECT * FROM resource_subjectareas WHERE resourceid = '" . $fi->resourceid . "' LIMIT 1";
    $resource_subjectarea = $wpdb->get_row($q_resource_subjectarea);

    $subjectareaid = $resource_subjectarea->subjectareaid;

    $q_subjectarea = "SELECT * FROM subjectareas WHERE subjectareaid = '" . $subjectareaid . "' LIMIT 1";
    $subjectarea = $wpdb->get_row($q_subjectarea);

    $subjectid = $subjectarea->subjectid;
    $subjectareaDisplayname = $subjectarea->displayname;

    $q_subject = "SELECT * FROM subjects WHERE subjectid = '" . $subjectid . "' LIMIT 1";
    $subject = $wpdb->get_row($q_subject);

    $subjectDisplayname = $subject->displayname;


    $resourceThumbImage = $theme_url . '/images/subjects/Arts/General.jpg';
    $resourceSubject = '';
    $resourceSubjectArea = '';
    $resourceSubjectAreaExt = 'png';
    if ($subjectareaDisplayname && $subjectDisplayname) {
        $resourceSubject = preg_replace('/\PL/u', '', $subjectDisplayname);
        $resourceSubjectArea = preg_replace('/\PL/u', '', $subjectareaDisplayname);

        if ($resourceSubject == 'Arts' || $resourceSubject == 'CareerTechnicalEducation') {
            $resourceSubjectAreaExt = 'jpg';
        }

        $resourceThumbImage = $theme_url . '/images/subjects/' . $resourceSubject . '/' . $resourceSubjectArea . '.' . $resourceSubjectAreaExt;
    }

    foreach ($wpdb->get_results("SELECT * FROM resource_educationlevels WHERE resourceid = '" . $fi->resourceid . "'", ARRAY_A) as $row)
    {
      $educationlevelid[] = $row['educationlevelid'];
    }

    $educationlevels = '';
    foreach ($education_levels  as $education_level)
    {
      $commonCount = count(array_intersect($education_level['arlevels'], $educationlevelid));

      if ($commonCount > 0) {
        if ($educationlevels) {
          $educationlevels .= ', '.$education_level['title'];
          break;
        }

        $educationlevels = $education_level['title'];
      }
    }

    $homepageCollectionItems .= '<div class="item">';
      $homepageCollectionItems .= '<div class="item-thumbnail">';
        $homepageCollectionItems .= '<a href="' . $itemUrl . '">';
          $homepageCollectionItems .= '<img class="w-100" src="' . $resourceThumbImage . '" width="303" height="207" alt="' . $fi->title . '">';
        $homepageCollectionItems .= '</a>';
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '<div class="item-body">';
        $homepageCollectionItems .= '<div class="info-text-body">';
          $homepageCollectionItems .= '<h4 class="info-title"><a href="' . $itemUrl . '">' . $fi->title . '</a></h4>';
          $homepageCollectionItems .= '<p>' . $educationlevels . '</p>';
        $homepageCollectionItems .= '</div>';

        $homepageCollectionItems .= '<footer class="card-footer">';

          $homepageCollectionItems .= '<div class="member-rating">';
            $homepageCollectionItems .= '<span class="rating-stars">';

            for ($count = 1; $count <= 5; $count ++) {
              if ($count <= $memberrating) {
                $homepageCollectionItems .= '<span class="fa fa-star"></span>';
              } else {
                $homepageCollectionItems .= '<span class="fa fa-star-o"></span>';
              }
            }

            $homepageCollectionItems .= '</span>';
            $homepageCollectionItems .= '<span class="member-rating-title">Member Rating</span>';
          $homepageCollectionItems .= '</div>';

          $homepageCollectionItems .= '<div class="curriki-rating">';
            $homepageCollectionItems .= '<span class="rating-point">' . $reviewrating . '</span>';
            $homepageCollectionItems .= '<span class="curriki-rating-title">Curriki Rating</span>';
          $homepageCollectionItems .= '</div>';

        $homepageCollectionItems .= '</footer>';

      $homepageCollectionItems .= '</div>';
    $homepageCollectionItems .= '</div>';
  }

  $ic .= '<div class="owl-carousel owl-theme">';
  $ic .= $homepageCollectionItems;
  $ic .= '</div>';

  return $ic;
}

function curriki_home_show_featured_item($item = 'homepagealigned')
{

  $current_language = "eng";
  $current_language_slug = "";
  if (defined('ICL_LANGUAGE_CODE')) {
    $current_language = cur_get_current_language(ICL_LANGUAGE_CODE);
    $current_language_slug = (ICL_LANGUAGE_CODE == "en" ? "" : "/" . ICL_LANGUAGE_CODE);
  }

  global $wpdb;
  $groups = '';
  $cur_date = date('Y-m-d H:i:s');
  if ($item == 'homepagequote')
    $location = "quote";
  else
    $location = $item;

  $q_featured_items = "SELECT * FROM featureditems WHERE location = '$location' "
    . "AND (active = 'T' OR active = '1') "
    . "AND featuredstartdate < '" . $cur_date . "' AND featuredenddate > '" . $cur_date . "' AND displayseqno != '' ORDER BY displayseqno ASC";

  if ($current_language != 'eng') {
    $q_featured_items = cur_featureditems_ml_query($current_language, $location, $cur_date);
  }

  $featured_items = $wpdb->get_results($q_featured_items);
  $ic = "";
  if (count($featured_items) > 0)
    $site_url = site_url();
  if ($item == 'dashboarduser') {
    $ic .= '<ul>';
    foreach ($featured_items as $fi) {
      $ic .= '<li class="member">';
      $q_user = "SELECT * FROM users u inner join cur_users cu on cu.ID = u.userid WHERE userid = '" . $fi->itemid . "'";
      $user = $wpdb->get_row($q_user);

      if (isset($user) && isset($user->uniqueavatarfile)) {
        $ic .= '<img class="border-grey" src="' . 'https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/' . $user->uniqueavatarfile . '" alt="member-name" />';
      } else {

        $profile = get_user_meta($user->userid, "profile", true);
        $profile = isset($profile) ? json_decode($profile) : null;
        $gender_img = isset($profile) ? "-" . $profile->gender : "";
        $ic .= '<img class="border-grey" src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample' . $gender_img . '.png' . '" alt="member-name" />';
      }

      //$user_display_name = $user->firstname . ' ' . $user->lastname;      
      $user_display_name = $fi->displaytitle;
      $ic .= '<div class="member-info"><span class="member-name name"><A href="javascript:void(0);">' . $user_display_name . '</a></span><span class="occupation">' . __(UCWords($user->membertype), "curriki") . '</span><span class="location">' . $user->city . ', ' . $user->state . ', ' . $user->country . '</span></div>';
      $ic .= '</li>';
    }
    $ic .= '</ul>';
  } elseif ($item == 'partner') {

    $ic .= '<div class="row row-partner">';
    foreach ($featured_items as $fi) {
      $ic .= '<div class="partner-logo">';
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" width="300" height="126" alt="' . $fi->featuredtext . '" />';
      else
        $ic .= '<img src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" width="300" height="126" alt="partner-name" />';
      $ic .= '</div>';
    }
    $ic .= '</div>';
  } elseif ($item == 'dashboardresource') {
    $ic .= '<ul>';
    foreach ($featured_items as $fi) {
      $ic .= '<li class="member">';
      $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
      $resource = $wpdb->get_row($q_resource);
      if (!empty($fi->image))
        $ic .= '<img class="border-grey" src="' . $fi->image . '" alt="member-name" />';
      else
        $ic .= '<img class="border-grey" src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" alt="member-name" />';


      $resource_title = $fi->displaytitle;

      $ic .= '<div class="member-info"><span class="member-name name"><a href="' . get_bloginfo('url') . '/oer/' . $resource->pageurl . '">' . $resource_title . '</a></span></div>';
      $ic .= '</li>';
    }
    $ic .= '</ul>';
  } elseif ($item == 'homepagecollection') {
    $homepageCollectionSubjects = '';
    $homepageCollectionItems = '';
    $featuredItemsPerSubject = [];
    $randomfeaturedItems = [];

    $education_levels = array(
      array('title' => __('Preschool (Ages 0-4)', 'curriki'), 'levels' => '8|9', 'arlevels' => array(8, 9)),
      array('title' => __('Kindergarten-Grade 2 (Ages 5-7) ', 'curriki'), 'levels' => '3|4', 'arlevels' => array(3, 4)),
      array('title' => __('Grades 3-5 (Ages 8-10)', 'curriki'), 'levels' => '5|6|7', 'arlevels' => array(5, 6, 7)),
      array('title' => __('Grades 6-8 (Ages 11-13)', 'curriki'), 'levels' => '11|12|13', 'arlevels' => array(11, 12, 13)),
      array('title' => __('Grades 9-10 (Ages 14-16)', 'curriki'), 'levels' => '15|16', 'arlevels' => array(15, 16)),
      array('title' => __('Grades 11-12 (Ages 16-18)', 'curriki'), 'levels' => '17|18', 'arlevels' => array(17, 18)),
      array('title' => __('College & Beyond', 'curriki'), 'levels' => '23|24|25', 'arlevels' => array(23, 24, 25)),
      array('title' => __('Professional Development', 'curriki'), 'levels' => '19|20', 'arlevels' => array(19, 20)),
      array('title' => __('Special Education', 'curriki'), 'levels' => '26|21', 'arlevels' => array(26, 21)),
    );

    foreach ($featured_items as $fi) {
      if($fi->featured == 'T')
        $featuredItemsPerSubject[$fi->link][] = $fi->featureditemid;
    }

    $randomfeaturedItemSubjects = array_rand($featuredItemsPerSubject, 4);

    foreach ($randomfeaturedItemSubjects as $rfis) {
      $randomfeaturedItems[] = $featuredItemsPerSubject[$rfis][array_rand($featuredItemsPerSubject[$rfis], 1)];
    }

    foreach ($featured_items as $fi) {
      $educationlevelid = array();
      $itemUrl = '';
      $memberrating = 0;
      $reviewrating = 0;
      $communityTagline = '';

      if ($fi->itemidtype == 'collection') {
        $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
        $resource = $wpdb->get_row($q_resource);
        $itemUrl = get_bloginfo('url') . '/oer/' . $resource->pageurl;
        $memberrating = $resource->memberrating ? $resource->memberrating : 0;
        $reviewrating = $resource->reviewrating ? $resource->reviewrating : 0;

        foreach ($wpdb->get_results("SELECT * FROM resource_educationlevels WHERE resourceid = '" . $fi->itemid . "'", ARRAY_A) as $row)
        {
          $educationlevelid[] = $row['educationlevelid'];
        }
      } else if ($fi->itemidtype == 'community') {
        $q_community = "SELECT * FROM communities WHERE communityid = '" . $fi->itemid . "'";
        $community = $wpdb->get_row($q_community);
        $itemUrl = get_bloginfo('url') . '/community/' . $community->url;
        $communityTagline = $community->tagline;

        $resourceid = array();
        foreach ($wpdb->get_results("SELECT * FROM community_collections WHERE communityid = '" . $fi->itemid . "'", ARRAY_A) as $row)
        {
          $resourceid[] = $row['resourceid'];
        }

        foreach ($wpdb->get_results("SELECT * FROM resource_educationlevels WHERE resourceid IN (" . implode(',', $resourceid) . ")", ARRAY_A) as $row)
        {
          $educationlevelid[] = $row['educationlevelid'];
        }
      }

      $educationlevels = '';
      foreach ($education_levels  as $education_level)
      {
        $commonCount = count(array_intersect($education_level['arlevels'], $educationlevelid));

        if ($commonCount > 0) {
          if ($educationlevels) {
            $educationlevels .= ', '.$education_level['title'];
            break;
          }

          $educationlevels = $education_level['title'];
        }
      }

      if (in_array($fi->featureditemid, $randomfeaturedItems)) {
        $fi->link .= ' Featured';
      }

      $homepageCollectionItems .= '<div class="col-sm-6 col-md-3 isotope-item ' . $fi->link . '">';
      $homepageCollectionItems .= '<div class="c-item">';
      $homepageCollectionItems .= '<a href="' . $itemUrl . '">';
      if (!empty($fi->image))
        $homepageCollectionItems .= '<img class="w-100" src="' . $fi->image . '" width="383" height="218" alt="' . $fi->displaytitle . '">';
      else
        $homepageCollectionItems .= '<img class="w-100" src="' . $site_url . '/wp-content/uploads/2015/03/user-icon-sample.png" width="383" height="218" alt="' . $fi->displaytitle . '">';
      $homepageCollectionItems .= '</a>';
      $homepageCollectionItems .= '<div class="c-item-body">';
      $homepageCollectionItems .= '<div data-mh="c-head">';
      $homepageCollectionItems .= '<h4 class="article-heading"><a href="' . $itemUrl . '">' . $fi->displaytitle . '</a></h4>';
      $homepageCollectionItems .= '<p>' . $educationlevels . '</p>';
      $homepageCollectionItems .= '</div>';
      /*
      $homepageCollectionItems .= '<hr>';
      $homepageCollectionItems .= '<div class="row font-size-14">';

      if ($fi->itemidtype == 'collection') {
        $homepageCollectionItems .= '<div class="col-sm-6">';
        $homepageCollectionItems .= '<div class="rating-stars text-yellow">';

        for ($count = 1; $count <= 5; $count ++) {
          if ($count <= $memberrating) {
            $homepageCollectionItems .= '<i class="fa fa-star"></i>';
          } else {
            $homepageCollectionItems .= '<i class="fa fa-star-o"></i>';
          }
        }

        $homepageCollectionItems .= '</div>';
        $homepageCollectionItems .= '<span class="rating-by font-light">Member Ratings</span>';
        $homepageCollectionItems .= '</div>';
        $homepageCollectionItems .= '<div class="col-sm-6">';
        $homepageCollectionItems .= '<div class="rating-points font-size-20 font-semibold">' . $reviewrating . '</div>';
        $homepageCollectionItems .= '<span class="rating-by font-light">Curriki Rating</span>';
        $homepageCollectionItems .= '</div>';
      } else if ($fi->itemidtype == 'community') {
        $homepageCollectionItems .= '<div class="col-sm-12">';
        $homepageCollectionItems .= $communityTagline;
        $homepageCollectionItems .= '</div>';
      }

      $homepageCollectionItems .= '</div>';
      */
      $homepageCollectionItems .= '<hr>';
      $homepageCollectionItems .= '<div class="c-body" data-mh="c-cbody">';
      $homepageCollectionItems .= __(strlen($fi->featuredtext) > 85 ? substr($fi->featuredtext,0,95)."..." : $fi->featuredtext, 'curriki');
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '<hr>';
      $homepageCollectionItems .= '<div class="row">';
      $homepageCollectionItems .= '<div class="col-sm-6">';
      $homepageCollectionItems .= '<a href="' . $itemUrl . '">More Info <i class="fa fa-angle-right"></i></a>';
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '<div class="col-sm-6 text-right">';
      $homepageCollectionItems .= '<a href="' . $itemUrl . '"><i class="fa fa-share-alt"></i></a>';
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '</div>';
      $homepageCollectionItems .= '</div>';
    }

    $homepageCollectionSubjects .= '<li class="nav-item">';
    $homepageCollectionSubjects .= '<a class="nav-link" href="#" data-filter=".Featured">Featured</a>';
    $homepageCollectionSubjects .= '</li>';
    $homepageCollectionSubjects .= '<li class="nav-item">';
    $homepageCollectionSubjects .= '<a class="nav-link" href="#" data-filter=".Arts">Arts</a>';
    $homepageCollectionSubjects .= '</li>';
    $homepageCollectionSubjects .= '<li class="nav-item">';
    $homepageCollectionSubjects .= '<a class="nav-link" href="#" data-filter=".LanguageArts">Language Arts</a>';
    $homepageCollectionSubjects .= '</li>';
    $homepageCollectionSubjects .= '<li class="nav-item">';
    $homepageCollectionSubjects .= '<a class="nav-link" href="#" data-filter=".Mathematics">Mathematics</a>';
    $homepageCollectionSubjects .= '</li>';
    $homepageCollectionSubjects .= '<li class="nav-item">';
    $homepageCollectionSubjects .= '<a class="nav-link" href="#" data-filter=".Science">Science</a>';
    $homepageCollectionSubjects .= '</li>';
    $homepageCollectionSubjects .= '<li class="nav-item">';
    $homepageCollectionSubjects .= '<a class="nav-link" href="#" data-filter=".SocialStudies">Social Studies</a>';
    $homepageCollectionSubjects .= '</li>';
    $homepageCollectionSubjects .= '<li class="nav-item dropdown">';
    $homepageCollectionSubjects .= '<a class="nav-link dropdown-toggle" href="#" id="dropdownMore" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More...</a>';
    $homepageCollectionSubjects .= '<ul class="dropdown-menu" aria-labelledby="dropdownMore">';
    $homepageCollectionSubjects .= '<li><a class="dropdown-item" href="#" data-filter=".CareerTechnicalEducation"><i class="fa fa-angle-right"></i> Career & Technical Education</a></li>';
    $homepageCollectionSubjects .= '<li><a class="dropdown-item" href="#" data-filter=".ComputerScience"><i class="fa fa-angle-right"></i> Computer Science</a></li>';
    $homepageCollectionSubjects .= '</ul>';
    $homepageCollectionSubjects .= '</li>';

    $ic .= '<div class="filters">';
    $ic .= '<ul class="nav nav-pills nav-filters">';
    $ic .= $homepageCollectionSubjects;
    $ic .= '</ul>';
    $ic .= '</div>';
    $ic .= '<div class="isotope-container-fitrows row">';
    $ic .= $homepageCollectionItems;
    $ic .= '</div>';
  } elseif ($item == 'homepagepartner') {
    $ic .= '<div class="row row-partner">';
    foreach ($featured_items as $fi) {
      $itemUrl = '';

      if ($fi->itemidtype == 'collection') {
        $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
        $resource = $wpdb->get_row($q_resource);
        $itemUrl = get_bloginfo('url') . '/oer/' . $resource->pageurl;
      } else if ($fi->itemidtype == 'community') {
        $q_community = "SELECT * FROM communities WHERE communityid = '" . $fi->itemid . "'";
        $community = $wpdb->get_row($q_community);
        $itemUrl = get_bloginfo('url') . '/community/' . $community->url;
      }

      $ic .= '<div class="partner-logo"><a href="' . $itemUrl . '">';
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" width="300" height="126" alt="' . $fi->featuredtext . '" />';
      else
        $ic .= '<img src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" width="300" height="126" alt="partner-name" />';
      $ic .= '</a></div>';
    }

    $ic .= '</div>';
  } elseif ($item == 'homepagealigned') {
    foreach ($featured_items as $fi) {
      $q_resource = "SELECT * FROM resources WHERE resourceid = '" . $fi->itemid . "'";
      $resource = $wpdb->get_row($q_resource);
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" class="circle border-white" />';
      else
        $ic .= '<img src="' . $site_url . '/wp-content/uploads/2015/03/user-icon-sample.png" class="circle border-white" />';
      $ic .= '<div class="side-tab-title"><a href="' . get_bloginfo('url') . '/oer/' . $resource->pageurl . '">' . __($fi->displaytitle, 'curriki') . '</a></div>';
      $ic .= __($fi->featuredtext, 'curriki') . '<div class="clear">&nbsp;</div>';
    }
  } elseif ($item == 'quote') {
    ob_start();
    echo '<div id="content" class="activity" role="main">';
    gconnect_locate_template(array('activity/activity-loop.php'), true);
    echo '</div><!-- .activity -->';

    /* if ( is_user_logged_in() ) {
      echo bp_loggedin_user_domain();
      } */
    $activity = ob_get_contents();
    ob_end_clean();
    $ic .= $activity;
    /* foreach($featured_items as $fi){
      $member_activity .= '<div class="group-activity-card page-activity-card">';
      $member_activity .= '<div class="group-activity-member page-activity-member">';
      $member_activity .= '<img class="border-grey circle" src="' . get_stylesheet_directory_uri() . '/images/user-icon-sample.png" alt="member-name" />';
      $member_activity .= '</div>';
      $member_activity .= '<div class="group-activity page-activity">';
      $member_activity .= '<div class="group-activity-header page-activity-header">';
      $member_activity .= '<div class="group-activity-info page-activity-info">';
      $member_activity .= '<a href="#">Firstname Lastname</a> contributed to <a href="#">This Group Name</a>';
      $member_activity .= '</div>';
      $member_activity .= '<div class="group-activity-time page-activity-time">';
      $member_activity .= 'August 14, 2014  5:15 PM EST';
      $member_activity .= '</div>';
      $member_activity .= '</div>';
      $member_activity .= '<div class="group-activity-body page-activity-body resource-pdf border-grey">';
      $member_activity .= '<div class="group-activity-body-content page-activity-body-content">';
      $member_activity .= '<a class="resource-name" href="#">This Resource Name</a>';
      $member_activity .= 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less...';
      $member_activity .= '<div class="rate-align"><a href="#">Rate Resource</a><a href="#">Align to Standards</a></div>';
      $member_activity .= '</div>';
      $member_activity .= '</div>';
      $member_activity .= '</div>';
      $member_activity .= '</div>';

      $ic .= $member_activity;
      } */
  } elseif ($item == 'homepagequote') {
    foreach ($featured_items as $fi) {
      $user_q = "SELECT * FROM users WHERE userid='" . $fi->itemid . "'";
      $user = $wpdb->get_row($user_q);
      $testimonials = "";
      $testimonials .= '<div class="item testimonial-box text-center">';
      $testimonials .= '<div class="testimonial-inner">';
      $testimonials .= '<div class="testimonial-body" data-mh="testimonialheight">';
      $testimonials .= '<p>' . __($fi->featuredtext, 'curriki') . '</p>';
      $testimonials .= '</div>';
      $testimonials .= '<div class="testimonial-author">';
      $testimonials .= '<div class="testimonial-rating">';
      $testimonials .= '<i class="fa fa-star"></i>';
      $testimonials .= '<i class="fa fa-star"></i>';
      $testimonials .= '<i class="fa fa-star"></i>';
      $testimonials .= '<i class="fa fa-star"></i>';
      $testimonials .= '<i class="fa fa-star"></i>';
      $testimonials .= '</div>';
      $testimonials .= '<div class="author-name">' . $user->firstname . ' ' . $user->lastname . '</div>';
      $testimonials .= '<div class="author-desc">' . $fi->displaytitle . '</div>';
      $testimonials .= '</div>';
      $testimonials .= '</div>';
      if ($user->uniqueavatarfile)
        $testimonials .= '<img class="author-photo" src="https://archivecurrikicdn.s3-us-west-2.amazonaws.com/avatars/' . $user->uniqueavatarfile . '" width="89" height="89" alt="' . $user->firstname . ' ' . $user->lastname . '">';
      else
        $testimonials .= '<img class="author-photo" src="' . get_bloginfo('url') . '/wp-content/uploads/2015/03/user-icon-sample.png" width="89" height="89" alt="' . $user->firstname . ' ' . $user->lastname . '">';

      $testimonials .= '</div>';
      $ic .= $testimonials;
    }
  } elseif ($item == 'dashboardgroup') {
    $groups .= '<ul>';
    if (count($featured_items) > 0)
      foreach ($featured_items as $fi) {
        $members_q = "SELECT slug FROM cur_bp_groups WHERE id='" . $fi->itemid . "'";
        $slug = $wpdb->get_var($members_q);
        $groups .= '<li class="group">';
        if ($fi->image != '')
          $groups .= '<img class="border-grey" src="' . $fi->image . '" alt="$fi->displaytitle" />';
        else
          $groups .= '<img class="border-grey" src="' . get_stylesheet_directory_uri() . '/images/group-icon-sample.png" alt="$fi->displaytitle" />';
        $groups .= '<div class="group-info"><span class="group-name name"><a href="' . get_bloginfo('url') . '/groups/' . $slug . '">' . $fi->displaytitle . '</a></span></div>';
        $groups .= '</li>';
      }
    $groups .= '</ul>';
    $ic .= $groups;
  } else {
    foreach ($featured_items as $fi) {
      $gom = 'groups';
      $slug = $fi->itemid;
      if ($fi->itemidtype == 'user') {
        $gom = 'members';
        $members_q = "SELECT user_nicename FROM cur_users WHERE ID='" . $fi->itemid . "'";
        $slug = $wpdb->get_var($members_q);
      } else {
        $groups_q = "SELECT slug FROM cur_bp_groups WHERE id='" . $fi->itemid . "'";
        $slug = $wpdb->get_var($groups_q);
      }
      if (!empty($fi->image))
        $ic .= '<img src="' . $fi->image . '" class="circle border-white" />';
      else
        $ic .= '<img src="' . site_url() . '/wp-content/uploads/2015/03/user-icon-sample.png" class="circle border-white" />';
      $ic .= '<div class="side-tab-title"><a href="' . get_bloginfo('url') . '/' . $gom . '/' . $slug . '">' . __($fi->displaytitle, 'curriki') . '</a></div>';
      $ic .= __($fi->featuredtext, 'curriki') . '<div class="clear">&nbsp;</div>';
    }
  }
  return $ic;
}

function curriki_name_scripts() {
  wp_enqueue_style('curriki-jquery-smoothness', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
  wp_enqueue_script('curriki-script-jquery', '//code.jquery.com/jquery-1.11.1.js', array(), '1.0.0', true);
  wp_enqueue_script('curriki-script-jquery-ui', '//code.jquery.com/ui/1.11.4/jquery-ui.js', array(), '1.0.0', true);
}

function render_logout_only() {
    require_once 'logout-modal.php';
}


if (isset($_GET['curriki_ajax_action']) and $_GET['curriki_ajax_action'] == 'newsletter') {
  fn_wp_ajax_curriki_newsletter();
}

function fn_wp_ajax_curriki_newsletter() {
  if (empty($_POST['signup_newsletter']))
    die('Sorry!');

  global $wpdb;
  $dashboard_page = 6015;
  if (empty($_POST['nl_name']))
    $errors[] = "Name is required.";
  if (empty($_POST['nl_email']))
    $errors[] = "Email is required.";
  if (!empty($_POST['nl_email'])) {
    if (isset($_POST["nl_email"]) && strlen($_POST["nl_email"]) > 0 && !filter_var($_POST["nl_email"], FILTER_VALIDATE_EMAIL)) {
      $errors[] = "Please enter valid Email !";
    } else {
      $wpdb->prepare("SELECT * FROM newsletters WHERE email = %s ", $_POST['nl_email']);
      $q_newsletter_email = $wpdb->prepare("SELECT * FROM newsletters WHERE email =  %s", $_POST['nl_email']);
      $newsletter_email = $wpdb->get_row($q_newsletter_email);
      if (!empty($newsletter_email))
        $errors[] = "You have already been signed up for newsletter.";
    }
  }


  if (empty($errors)) {
    $wpdb->insert(
            'newsletters', array(
        'name' => $_POST['nl_name'],
        'email' => $_POST['nl_email']
            ), array(
        '%s',
        '%s'
            )
    );
    //curriki_redirect(get_permalink($dashboard_page));
    echo "Thanks for signing up!";
  } else {
    foreach ($errors as $error) {
      echo $error . '<br />';
    }
  }
  die;
  /*
    if(empty($_POST['log']))return;
    $creds = array();
    $creds['user_login'] = $_POST['log'];
    $creds['user_password'] = $_POST['pwd'];
    $creds['remember'] = $_POST['rememberme'];
    $user = wp_signon( $creds, false );
    if ( !is_wp_error($user) ){
    fn_curriki_wp_login();
    echo '1';
    die;
    }else{
    $error = $user->get_error_message();
    if(strstr($error, 'Invalid username', true)){
    echo "Invalid Username.";
    }elseif(strstr($error, 'The password you entered for the username', true)){
    echo "Password is incorrect.";
    }
    die;
    } */
}


if (isset($_GET['curriki_ajax_action']) && $_GET['curriki_ajax_action'] == 'signup' && isset($_POST["is_registration_invitation"]) &&  $_POST["is_registration_invitation"] == '1' ) {    
  fn_wp_ajax_curriki_signup();
}

function fn_wp_ajax_curriki_signup() {
  $dashboard_page = 6015;
  if (empty($_POST['firstname']))
    $errors[] = "\"First Name\" is required.";
  if (empty($_POST['lastname']))
    $errors[] = "\"Last Name\" is required.";
  if (empty($_POST['username']))
    $errors[] = "Username is required.";
  if (!preg_match('/^[a-zA-Z0-9,. ]*$/', $_POST['username']))
    $errors[] = "Username should not have special characters.";
  if (empty($_POST['email']))
    $errors[] = "Email is required.";
  if (empty($_POST['pwd']))
    $errors[] = "Password is required.";
  elseif (strlen($_POST['pwd']) < 6)
    $errors[] = "Password should be at least 6 characters long.";
  elseif (substr_count($_POST['pwd'], ' ') > 0)
    $errors[] = "Password should not contain spaces.";
  if (empty($_POST['confirm_pwd']))
    $errors[] = "Confirm Password is required.";
  if (username_exists($_POST['username']))
    $errors[] = "Username already exists.";
  if (email_exists($_POST['email']))
    $errors[] = "Email already exists.";
  if ($_POST['pwd'] != $_POST['confirm_pwd'])
    $errors[] = "Password and Confirm Password dont match.";
  //if(empty( $_POST['accept'] ))
  //    $errors[] = "Accepting terms and policy is required.";

  if (isset($_POST["email"]) && strlen($_POST["email"]) > 0 && !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
  }

  if (!empty($_POST['zipcode'])) {
    $zip = $_POST['zipcode'];
    if (strlen($zip) <= 6 && ctype_digit($zip)) {
      //valid            
    } else {
      //invalid            
      $errors[] = "Enter valid Zip/Postal code.";
    }
  }

  if (empty($errors)) {

    $userid = register_new_user($_POST['username'], $_POST['email']);
    wp_set_password($_POST['pwd'], $userid);
    //update_user_meta( $userid, "country", $_POST['country'] );
    //update_user_meta( $userid, "member_type", $_POST['member_type'] );

    global $wpdb;

    $q_newuserid = "select userid from users where userid = '" . $userid . "'";
    $newuserid = $wpdb->get_var($q_newuserid);
    if (!$newuserid > 0) {
      $wpdb->insert('users', array('userid' => $userid), array('%d'));
      //echo $wpdb->last_query;
    }
    $wpdb->update(
            'users', array(
        'firstname' => $_POST['firstname'],
        'lastname' => $_POST['lastname'],
        'user_login' => $_POST['username'],
        'password' => $_POST['pwd'],
        'indexrequired' => 'T',
        'indexrequireddate' => date('Y-m-d H:i:s')
            ), array('userid' => $userid), array('%s', '%s', '%s', '%s'), array('%d')
    );
    $wpdb->update(
            'users', array(
        'country' => $_POST['country'],
        'membertype' => $_POST['member_type'],
        'state' => $_POST['state'],
        'city' => $_POST['city'],
        'postalcode' => $_POST['zipcode'],
            ), array('userid' => $userid), array('%s', '%s', '%s', '%s'), array('%d')
    );
    //echo $wpdb->last_query;
    $profile_meta = array(
        "gender" => $_POST["gender"]
    );
    add_user_meta($userid, "profile", json_encode($profile_meta));

    $creds = array();
    $creds['user_login'] = $_POST['username'];
    $creds['user_password'] = $_POST['pwd'];
    $user = wp_signon($creds, false);    
    //curriki_signup_mail($_POST['email']);
    if (!is_wp_error($user)) 
    {              
        fn_curriki_wp_login($user);
    }
    echo "1";
    die;
  } else {      
    foreach ($errors as $error) {
      echo __($error,'curriki') . '<br />';
    }
    die;
  }
}

if (isset($_GET['curriki_ajax_action']) and $_GET['curriki_ajax_action'] == 'update_profile') {
  fn_wp_ajax_curriki_update_profile();
}

function fn_wp_ajax_curriki_update_profile() {
  global $wpdb;
  $wpdb->update(
          'users', array(
      $_POST['field'] => $_POST['value'],
      'indexrequired' => 'T',
      'indexrequireddate' => date('Y-m-d H:i:s')
          ), array('userid' => get_current_user_id()), array('%s', '%s', '%s'), array('%d')
  );
  die;
}


if (isset($_GET['curriki_ajax_action']) and $_GET['curriki_ajax_action'] == 'resource_rating') {
  $q = "SELECT resourceid FROM comments WHERE resourceid = '" . addslashes($_POST['resource_id']) . "' AND userid = '" . get_current_user_id() . "'";
  $rid = $wpdb->get_var($q);
  if ($rid > 0) {
    echo "You have already posted review for this.";
  } else {
    $wpdb->insert(
            'comments', array(
        'resourceid' => $_POST['resource_id'],
        'userid' => get_current_user_id(),
        'comment' => $_POST['comments'],
        'rating' => $_POST['rating'],
        'commentdate' => date("Y-m-d H:i:s"),
            ), array(
        '%d',
        '%d',
        '%s',
        '%d',
        '%s'
            )
    );
    $q_avg_rating = "SELECT avg(rating) FROM comments WHERE resourceid = '" . addslashes($_POST['resource_id']) . "'";
    $avg_rating = $wpdb->get_var($q_avg_rating);
    $wpdb->update(
            'resources', array(
        'memberrating' => $avg_rating
            ), array('resourceid' => $_POST['resource_id']), array(
        '%d'
            ), array('%d')
    );
    echo "1";
  }
  die;
}

/*
function library_pagination($old_url, $current_page, $total_pages) {
  if ($total_pages < 2)
    return [];

  $pagination_data = [
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'first_page_url' => '',
    'previous_page_url' => '',
    'next_page_url' => '',
    'last_page_url' => '',
    'page_urls' => []
  ];

  if ($current_page < 2)
    $current_page = 1;

  if ($current_page > 1) {
    $pagination_data['first_page_url'] = $old_url . '&page_no=1';
    $pagination_data['previous_page_url'] = $old_url . '&page_no=' . ($current_page - 1);
  }

  $first_page = 1;
  if ($current_page > 4)
    $first_page = $current_page - ($current_page % 5);

  for ($i = $first_page, $j = 0; $i <= $total_pages && $j < 9; $i++, $j++) {
    $pagination_data['page_urls'][] = [
      'page' => $i,
      'url' => $old_url . '&page_no=' . $i,
      'is_current' => ($current_page == $i)
    ];
  }

  if ($current_page < $total_pages) {
    $pagination_data['next_page_url'] = $old_url . '&page_no=' . ($current_page + 1);
    $pagination_data['last_page_url'] = $old_url . '&page_no=' . $total_pages;
  }

  return $pagination_data;
}
*/


function library_pagination($old_url, $current_page, $total_pages) {
  if ($total_pages < 2)
    return;
  $user_library = "";
  $user_library .= '<div class="pagination">';
  if ($current_page < 2)
    $current_page = 1;
  if ($current_page > 1)
    $user_library .= '<a class="pagination-first" href="' . $old_url . '&page_no=1"><span class="fa fa-angle-double-left"></span></a>';
  if ($current_page > 1)
    $user_library .= '<a class="pagination-previous" href="' . $old_url . '&page_no=' . ($current_page - 1) . '"><span class="fa fa-angle-left"></span> '.__('Previous','curriki').'</a>';

  $first_page = 1;
  $j = 0;
  if ($current_page > 4)
    $first_page = $current_page - ($current_page % 5);
  for ($i = $first_page; $i <= $total_pages; $i++) {
    $current = "";
    if ($current_page == $i)
      $current = " current";
    $user_library .= '<a class="pagination-num' . $current . '" href="' . $old_url . '&page_no=' . $i . '">' . $i . '</a>';
    if ($j++ > 8)
      break;
  }
  if ($current_page < $total_pages)
    $user_library .= '<a class="pagination-next" href="' . $old_url . '&page_no=' . ($current_page + 1) . '">'.__('Next','curriki').' <span class="fa fa-angle-right"></span></a>';
  if ($current_page < $total_pages)
    $user_library .= '<a class="pagination-last" href="' . $old_url . '&page_no=' . $total_pages . '"><span class="fa fa-angle-double-right"></span></a>';
  $user_library .= '</div>';
  return $user_library;
}


function curriki_member_rating($rating = 0) {
  $library = "";
  for ($star_count = 1; $star_count <= round($rating); $star_count++) {
    $library .= '<span class="fa fa-star"></span>';
  }for ($star_count = $star_count; $star_count < 6; $star_count++) {
    $library .= '<span class="fa fa-star-o"></span>';
  }
  return $library;
}

function curriki_library_scripts() {
  ob_start();
  ?>

<script type="text/javascript">
    function resourceRating(star) {
      
      jQuery("#resource-rating-" + star).siblings().addClass('far');
      jQuery("#resource-rating-" + star).siblings().removeClass('fas');

      for (i = 1; i <= star; i++)
      {
        jQuery("#resource-rating-" + i).addClass('fas');
        jQuery("#resource-rating-" + i).addClass('e-rating-icon-marked-color');
        
        jQuery("#resource-rating-" + i).removeClass('far');
      }

      jQuery("#resource-rating").val(star);
    }
  </script>
  
  <script type="text/javascript">
    jQuery("#resource-rating-form").submit(function (event) {
      // prevent default posting of form
      event.preventDefault();
      jQuery("#resource-rating-form_result").empty().append(jQuery("#please-wait-text").val());
      // Stop form from submitting normally
      event.preventDefault();
      // Get some values from elements on the page:
      var $form = jQuery(this),
              resource_rating = $form.find("input[name='resource-rating']").val(),
              resource_comments = $form.find("textarea[name='resource-comments']").val(),
              resource_id = $form.find("input[name='review-resource-id']").val(),
              url = '?curriki_ajax_action=resource_rating';

      // Send the data using post
      var posting = jQuery.post(url, {rating: resource_rating, comments: resource_comments, resource_id: resource_id, action: 'curriki_resource_rating'});
      // Put the results in a div
      posting.done(function (data) {
        jQuery("#resource-rating-form_result").empty().append('Review Posted!');
      });

      return false;

    });

    function curriki_sharethis(rid, title) {
      //"https://www.addthis.com/bookmark.php?source=tbx32nj-1.0&v=300&url='.urlencode(get_bloginfo('url').'/oer/?rid='.$rid).'";
      var url_to_share = '<?php echo get_bloginfo('url') . '/oer/?rid='; ?>' + rid;
      jQuery("#share-" + rid).html(url_to_share);
      alert(url_to_share);
    }
  </script>
  
  <div id="rate_resource-dialog" class="review-content-box rounded-borders-full border-grey join-oauth-modal modal border-grey rounded-borders-full grid_8">
    <h3 class="modal-title curriki-review-title">Rate This</h3>
    <div class="review review-form">
      <div class="dialog_result_div"><div id="resource-rating-form_result" class="dialog_result"></div></div>
      <div class="review-content" style="width: 100% !important;">
        <div class="review-rating rating">

          <span onclick="resourceRating(1);" id="resource-rating-1" class="far fa-star e-rating-icon-marked-color"></span>
          <span onclick="resourceRating(2);" id="resource-rating-2" class="far fa-star e-rating-icon-marked-color"></span>
          <span onclick="resourceRating(3);" id="resource-rating-3" class="far fa-star e-rating-icon-marked-color"></span>
          <span onclick="resourceRating(4);" id="resource-rating-4" class="far fa-star e-rating-icon-marked-color"></span>
          <span onclick="resourceRating(5);" id="resource-rating-5" class="far fa-star e-rating-icon-marked-color"></span></span>
        </div>
        <form method="post" action="" id="resource-rating-form">
          <input type="hidden" name="review-resource-id" id="review-resource-id" value="" />
          <input type="hidden" name="resource-rating" id="resource-rating" value="0">
          <textarea name="resource-comments"></textarea>
          <button class="green-button"><?php echo __('Submit Review','curriki'); ?></button>
        </form>
      </div>
    </div>
    <div class="close"><span class="fa fa-close" onclick="jQuery('#rate_resource-dialog').hide();"></span></div>
  </div>

 
  <?php
  return ob_get_clean();
}

/*
function curriki_library_sorting($page, $position, $selected = "", $userid = '') {
    $sorting_options = [
        'displayseqno' => '',
        'oldest' => '',
        'newest' => '',
        'rtc' => '',
        'ctr' => '',
        'mcf' => '',
        'mff' => '',
        'aza' => '',
        'azd' => '',
        'ru' => ''
    ];

    // Set the selected option
    if (array_key_exists($selected, $sorting_options)) {
        $sorting_options[$selected] = 'selected';
    }

    // Prepare the data set
    $data = [
        'position' => $position,
        'userid' => $userid,
        'page' => $page,
        'sorting_options' => $sorting_options,
        'labels' => [
            'sort_by' => __('Sort by', 'curriki'),
            'my_contributions_first' => __('My Contributions First', 'curriki'),
            'my_favorites_first' => __('My Favorites First', 'curriki'),
            'title_az' => __('Title [A-Z]', 'curriki'),
            'title_za' => __('Title [Z-A]', 'curriki'),
            'newest_first' => __('Newest First', 'curriki'),
            'oldest_first' => __('Oldest First', 'curriki'),
            'resources_then_collections' => __('Resources then Collections', 'curriki'),
            'collections_then_resources' => __('Collections then Resources', 'curriki'),
            'recently_updated' => __('Recently Updated', 'curriki')
        ]
    ];

    return $data;
}
*/


function curriki_library_sorting($page, $position, $selected = "", $userid = '') {
  if ($selected == 'displayseqno')
    $selected_displayseqno = ' selected="selected"';
  else
    $selected_displayseqno = '';
  if ($selected == 'oldest')
    $selected_oldest = ' selected="selected"';
  else
    $selected_oldest = '';
  if ($selected == 'newest')
    $selected_newest = ' selected="selected"';
  else
    $selected_newest = '';
  if ($selected == 'rtc')
    $selected_rtc = ' selected="selected"';
  else
    $selected_rtc = '';
  if ($selected == 'ctr')
    $selected_ctr = ' selected="selected"';
  else
    $selected_ctr = '';
  if ($selected == 'mcf')
    $selected_mcf = ' selected="selected"';
  else
    $selected_mcf = '';
  if ($selected == 'mff')
    $selected_mff = ' selected="selected"';
  else
    $selected_mff = '';
  if ($selected == 'aza')
    $selected_aza = ' selected="selected"';
  else
    $selected_aza = '';
  if ($selected == 'azd')
    $selected_azd = ' selected="selected"';
  else
    $selected_azd = '';
  if ($selected == 'ru')
    $selected_ru = ' selected="selected"';
  else
    $selected_ru = '';
  
  global $post;
  $qs = parse_url(site_url($_SERVER['REQUEST_URI']));
  // $qs 'query' is the query string
  $query_string = '';
  if ( isset($qs['query']) && !empty($qs['query']) ) {
    $query_string = '?' . $qs['query'];
  }
  $action_url = get_permalink() . $query_string; //site_url($post->post_name . $_SERVER['REQUEST_URI']);
  $library_sorting = '<form method="GET" action="' . $action_url . '" id="library_sorting_form-' . $position . '">';
  if (!empty($userid))
    $library_sorting .= '<input type="hidden" name="userid" value="' . $userid . '" />';
  $library_sorting .= '<select name="library_sorting" onchange="document.getElementById(\'library_sorting_form-' . $position . '\').submit();">';  
  if ($page == 'my') {
    $library_sorting .= '<option value="mcf"' . $selected_mcf . '>'.__('My Contributions First','curriki').'</option>';
    $library_sorting .= '<option value="mff"' . $selected_mff . '>'.__('My Favorites First','curriki').'</option>';
  }
  //. '<option value="displayseqno"'.$selected_displayseqno.'>Sequence No</option>'
  $library_sorting .= '<option value="aza"' . $selected_aza . '>'.__('Title [A-Z]','curriki').'</option>';
  $library_sorting .= '<option value="azd"' . $selected_azd . '>'.__('Title [Z-A]','curriki').'</option>';

  $library_sorting .= '<option value="newest"' . $selected_newest . '>'.__('Newest First','curriki').'</option>';
  $library_sorting .= '<option value="oldest"' . $selected_oldest . '>'.__('Oldest First','curriki').'</option>'
          . '<option value="rtc"' . $selected_rtc . '>'.__('Resources then Collections','curriki').'</option>'
          . '<option value="ctr"' . $selected_ctr . '>'.__('Collections then Resources','curriki').'</option>'
          . '<option value="ru"' . $selected_ru . '>'.__('Recently Updated','curriki').'</option>';

  $library_sorting .= '</select></form>';

  return $library_sorting;
}

function curriki_sharethis($rid, $title = '') {
  return '<a title="Share this resource with a friend" '
          . 'onclick="return addthis_sendto()" '
          . 'onmouseout="addthis_close()" '
          . 'onmouseover="return addthis_open(this, \'\', \'' . get_bloginfo('url') . '/oer/?rid=' . $rid . '\', \'' . $title . '\')" '
          . 'href="javascript:;"><span class="fa fa-share-alt-square"></span> <span>'.__('Share','curriki').'</span></a>';
//    return '<a href="javascript:;" onclick="curriki_sharethis(\''.$rid.'\', \''.$title.'\');"><span class="fa fa-share-alt-square"></span> <span>Share</span></a><span id="share-'.$rid.'"></span>';
}

function curriki_addthis_scripts() {
  wp_enqueue_script( 'addthis-widget', 'https://s7.addthis.com/js/300/addthis_widget.js#pubid=ra-554cdcac2ebf96b6', array(), false, true );
}

if (!empty($_GET['curriki_ajax_action']) and $_GET['curriki_ajax_action'] == 'curriki_organize_collection') {
  if (empty($_POST['s']))
    echo 'Nothing changed.';else {
    global $wpdb;
    $resources_posted = '';
    $cid_post = addslashes($_POST['cid']);
    $q_collectionid = "select resourceid from resources where resourceid = '" . $cid_post . "' and contributorid = '" . get_current_user_id() . "'";
    $cid = $wpdb->get_var($q_collectionid);
    if ($cid_post != $cid)
      echo "Bad attempt logged.";else {
      $seqs = explode(',', addslashes($_POST['s']));
      foreach ($seqs as $s) {
        $s = explode('=', $s);
        $wpdb->update(
                'collectionelements', array(
            'displayseqno' => $s[1] // string
                ), array('collectionid' => $cid, 'resourceid' => $s[0]), array(
            '%d' // value2
                ), array('%d', '%d')
        );
        $resources_posted .= ',' . $s[0];
      }
      $q_delete_elements = "delete from collectionelements where collectionid = '" . $cid . "' and resourceid not in (0" . $resources_posted . ")";
      $wpdb->query($q_delete_elements);
      echo '1';
    }
  }
  die;
}

//======= Setteubg resource slug in oer post's slug for WPML flangs =============
function cur_ls_languages( $languages ) {
  
    //global $post;      
    $pageurl_text = $_SESSION["pageurl_text"];     
    $pagename = get_query_var('pagename'); 
    if( $pagename === "oer")
    {                                        
      $request_uri = explode("/", preg_replace('{/$}', '', $_SERVER["REQUEST_URI"]));      
      $resource_slug = $request_uri[count($request_uri)-1];
      
      //$resource_slug = implode( "W" , $_GET ) ;
      
      //$pageurl_text = isset($pageurl_text) ? "yes":"no";
      
      foreach ($languages as $k=>$l)
      {
          $l["url"] = $l["url"].$pageurl_text;  
          //$l["url"] = $l["url"];  
          $languages[$k] = $l;
      }
    }      
    /*
    global $resourceUserGlobal;    
    $pagename = get_query_var('pagename'); 
    if( $pagename === "oer")
    {                                        
        //$resource_slug = $resourceUserGlobal->pageurl;
        $resource_slug = $resourceUserGlobal["pageurl"];
        foreach ($languages as $k=>$l)
        {
            $l["url"] = $l["url"].$resource_slug;  
            $languages[$k] = $l;
        }
    }
    */
    return $languages;
}

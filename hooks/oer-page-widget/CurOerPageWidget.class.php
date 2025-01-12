<?php
class CurOERPageWidget extends \Elementor\Widget_Base {
    
    // ... other methods like get_title(), get_icon(), get_categories(), get_keywords()

    public function get_name() {
        return 'oer-page-widget';
    }

    public function get_title() {
        return __('Curriki OER Page Widget', 'curriki-library');
    }

    protected function _register_controls() {
        // Add controls to the widget settings panel
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'curriki-library' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'resource_id',
            [
                'label' => __( 'Resource ID / Slug', 'curriki-library' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter resource ID or Slug', 'curriki-library' ),
            ]
        );

        $this->add_control(
            'template_id',
            [
                'label' => __( 'Select Page Template', 'curriki-library' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_template_options(),
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    private function get_template_options() {
        $templates = \Elementor\Plugin::$instance->templates_manager->get_templates();
        // filter $templates base on array item key 'type' => 'page'
        $templates = array_filter($templates, function($template) {
            return $template['type'] === 'page';
        });
        $options = [];
    
        foreach ($templates as $template) {
            $options[$template['template_id']] = $template['title'];
        }
    
        return $options;
    }

    private function prepare_collection_contents($resource) {
        $output_data = [];

        if (isset($resource['type']) && $resource['type'] == 'collection' && isset($resource['collection'])) {
            $rid = $resource['resourceid'];
            $persist_rids[] = $rid;
            $persist_rids = array_unique($persist_rids);
            $mrid = implode("-", $persist_rids);
            
            foreach ($resource['collection'] as $collection) {
                $url = get_bloginfo('url') . '/oer/' . $collection['pageurl'];
                $url .= "/?mrid=" . $mrid;
                
                if (
                    isset($collection["lp_object"]) && $collection["lp_object"] !== ''
                    && isset($collection["lp_object_id"]) && $collection["lp_object_id"] > 0
                    && isset($collection["lp_course_id"]) && $collection["lp_course_id"] > 0
                ) {
                    $url .= "&lp_object=" . $collection["lp_object"] . "&lp_object_id=" . $collection["lp_object_id"] . "&lp_course_id=" . $collection["lp_course_id"];
                }

                $url .= isset($_GET['oer-only']) && $_GET['oer-only'] == 'true' ? '&oer-only=true' : '';

                $content = htmlentities(!empty($collection['description']) ? $collection['description'] : $collection['content']);
                global $wpdb;
                $colObj = $wpdb->get_row("select * from resources where resourceid=" . $collection['resourceid']);
                
                $progress_content = "";
                if ($print_progress_for_collections) {
                    $progress_for_playlist = ltiGetProgressForPlaylist($collection['resourceid'], $res);
                    $progress_content = 'MY PROGRESS: ' . $progress_for_playlist['completed'] . '/' . $progress_for_playlist['total'] . ' Activities Completed';
                } elseif ($print_progress_for_lti_resource && is_object($colObj) && trim($colObj->type) === 'resource') {
                    $progress_lti_data = ltiGetResourceProgress($collection['resourceid'], get_current_user_id());
                    $progress_content = match ($progress_lti_data['status']) {
                        'take-lesson' => 'In Progress',
                        'completed' => 'MY SCORE: ' . $progress_lti_data['data']['gradepercent'] . '%',
                        default => 'Not Started',
                    };
                }

                // $m_stars = str_repeat('<span class="fa fa-star">', (int) $collection['memberrating']) . str_repeat('<span class="fa fa-star-o">', 5 - (int) $collection['memberrating']);
                $m_stars = (int) $collection['memberrating'];
                $reviewrating = round($collection['reviewrating'], 1);
                $reviewrating = number_format($reviewrating, 1);
                
                $output_data[] = [
                    'title' => htmlentities($collection['title']),
                    'url' => $url,
                    'contributor' => $collection['contributorid_Name'],
                    'progress_content' => $progress_content,
                    'description' => strip_tags(html_entity_decode($content)),
                    'member_rating_stars' => $m_stars,
                    'curriki_rating' => [
                        'review_rating' => $reviewrating ?? null,
                        'status' => $collection['reviewstatus'] ?? null,
                        'partner' => $collection['partner'] ?? null,
                    ],
                ];
            }
        }
        
        // The output data array now holds all the information in an associative array format.
        return $output_data;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $templateId = $settings['template_id'] ? intval($settings['template_id']) : 0;
        $resourceId = isset($_GET['rid']) && intval($_GET['rid']) > 0 ? $_GET['rid'] : $settings['resource_id'];

        require_once(__DIR__ . '/../core/CurrikiResources.class.php');
        $res = new CurrikiResources();
        
        global $oerPageData;
        global $oerPageDataById;

        if (isset($_GET['pageurl'])) {
            $oerPageData = $res->getResourceUserById(0, rtrim($_GET['pageurl'], '/')); 
            $oerPageDataById = $res->getResourceById(0, rtrim($_GET['pageurl'], '/'), true);
        } elseif ($resourceId && is_numeric($resourceId)) {
            $oerPageData = $res->getResourceUserById($resourceId, '');
            $oerPageDataById = $res->getResourceById($resourceId, '', true);
        } elseif ($resourceId && !is_numeric($resourceId)) {
            $resourceIdBeingSlug = $resourceId;
            $oerPageData = $res->getResourceUserById(0, $resourceIdBeingSlug);    
            $oerPageDataById = $res->getResourceById(0, $resourceIdBeingSlug, true);
        } else {
            $oerPageData = null;
            $oerPageDataById = null;
        }
        
        if ($templateId && !is_null($oerPageData)) {

            // if $oerPageDataById not null
            if (!is_null($oerPageDataById)) {
                $oerCollections = $this->prepare_collection_contents($oerPageDataById);
                $oerPageDataById['oerCollections'] = $oerCollections;
            }
            // Use the selected template
            echo \Elementor\Plugin::$instance->frontend->get_builder_content($templateId);    
        }
    }
}

?>
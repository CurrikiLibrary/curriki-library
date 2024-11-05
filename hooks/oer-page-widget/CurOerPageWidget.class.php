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

    protected function render() {
        $settings = $this->get_settings_for_display();
        $templateId = $settings['template_id'] ? intval($settings['template_id']) : 0;
        $resourceId = $settings['resource_id'];

        require_once(__DIR__ . '/../core/CurrikiResources.class.php');
        $res = new CurrikiResources();
        
        global $oerPageData;

        if (isset($_GET['pageurl'])) {
            $oerPageData = $res->getResourceUserById(0, rtrim($_GET['pageurl'], '/'));    
        } elseif ($resourceId && is_numeric($resourceId)) {
            $oerPageData = $res->getResourceUserById($resourceId, '');    
        } elseif ($resourceId && !is_numeric($resourceId)) {
            $resourceIdBeingSlug = $resourceId;
            $oerPageData = $res->getResourceUserById(0, $resourceIdBeingSlug);    
        } else {
            $oerPageData = null;
        }
        
        if ($templateId && !is_null($oerPageData)) {
            // Use the selected template
            echo Elementor\Plugin::$instance->frontend->get_builder_content( $templateId);    
        }
    }
}

?>
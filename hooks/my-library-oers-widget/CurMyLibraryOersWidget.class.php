<?php
class CurMyLibraryOersWidget extends \Elementor\Widget_Base {
    
    // ... other methods like get_title(), get_icon(), get_categories(), get_keywords()

    public function get_name() {
        return 'my-library-oers-widget';
    }

    public function get_title() {
        return __('Curriki My Library OERs Widget', 'curriki-library');
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
            'template_id',
            [
                'label' => __( 'Select Template', 'curriki-library' ),
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
            return $template['type'] === 'page' || $template['type'] === 'container';
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
        require_once dirname(__DIR__) . '/core/my-library.php';
        $user_library = curriki_user_my_library();
        $resources = $user_library['resources'];

        // message if template is not selected
        if (!$templateId) {
            echo '<p>Please select a template to display resources.</p>';
            return;
        }

        // message if resources is empty
        if (empty($resources)) {
            echo '<p>No resources found in your library.</p>';
            return;
        }       
         
        // iterate over $resources
        foreach ($resources as $resource) {
            global $myLibraryOerData;
            $myLibraryOerData = $resource;
            if ( $templateId && !is_null($myLibraryOerData) ) {
                echo \Elementor\Plugin::$instance->frontend->get_builder_content($templateId);
            }
        }
    }
}

?>
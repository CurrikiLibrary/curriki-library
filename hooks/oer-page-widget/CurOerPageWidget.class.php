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
        $template_id = $settings['template_id'] ? intval($settings['template_id']) : 0;
        $resourceId = $settings['resource_id'];

        /* // Fetch resource data from your custom table based on the resource ID
        $resourceData = $this->fetchResourceData($resourceId);

        // Display the resource data using Elementor's template engine
        echo '<div>';
        echo '<h2>' . $resourceData['title'] . '</h2>';
        echo '<p>' . $resourceData['content'] . '</p>';
        echo '</div>';
        */
        if ($template_id) {
            // Use the selected template
            echo Elementor\Plugin::$instance->frontend->get_builder_content( $template_id, array(
                'title' => 'test title',
                'content' => 'test content',
                // ... other dynamic data
            ) );    
        }
    }
}

?>
<?php
class OER_Page_Widget extends \Elementor\Widget_Base {
    
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

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $resourceId = $settings['resource_id'];

        /* // Fetch resource data from your custom table based on the resource ID
        $resourceData = $this->fetchResourceData($resourceId);

        // Display the resource data using Elementor's template engine
        echo '<div>';
        echo '<h2>' . $resourceData['title'] . '</h2>';
        echo '<p>' . $resourceData['content'] . '</p>';
        echo '</div>';
        */
        echo '<div>';
        echo '<h1>' . $resourceId . '</h1>';
        echo '<h2>Test Resource title</h2>';
        echo '<p>Test Resource Content</p>';
        echo '</div>';
    }
}

?>
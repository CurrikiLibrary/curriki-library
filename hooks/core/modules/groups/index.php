<?php

function curr_group_library_init() {        
    if (!class_exists( 'BP_Group_Extension' )) 
        return;
    
     class Crurriki_group_library extends BP_Group_Extension {
       
        public function __construct() {                
            $args = array(
                'slug' => 'library',
                'name' => 'Group Library',
            );
            parent::init( $args );                                
        }
      
        public function display( $group_id = NULL ) {                
            if ( class_exists('Elementor\Plugin') ) {
                $templateId = get_option('library_template_setting', '');
                if ( $templateId == '' ) {
                    echo 'Please select a template in the Group Library settings.';
                    return;
                }
            } else {
                echo 'Elementor is not installed.';
                return;
            }
            
            echo \Elementor\Plugin::$instance->frontend->get_builder_content($templateId);    
        }
                  
        /**
         * settings_screen() is the catch-all method for displaying the content
         * of the edit, create, and Dashboard admin panels
         */
        /*
        function settings_screen( $group_id = NULL ) {
            $setting = groups_get_groupmeta( $group_id, 'group_extension_example_1_setting' );
            ?>
            Save your plugin setting here: <input type="text" name="group_extension_example_1_setting" value="<?php echo esc_attr( $setting ) ?>" />
            <?php
        }
        */
        /**
         * settings_sceren_save() contains the catch-all logic for saving
         * settings from the edit, create, and Dashboard admin panels
         */
        /*
        function settings_screen_save( $group_id = NULL ) {
            $setting = '';

            if ( isset( $_POST['group_extension_example_1_setting'] ) ) {
                $setting = $_POST['group_extension_example_1_setting'];
            }

            groups_update_groupmeta( $group_id, 'group_extension_example_1_setting', $setting );
        }
        */
    }
    bp_register_group_extension( 'Crurriki_group_library' );
}        
add_action('bp_init', 'curr_group_library_init');       

add_filter('wp_nav_menu_objects', 'curr_library_link', 10, 2);
function curr_library_link($items, $args) {
    
    foreach ($items as &$item) {
        if (function_exists('bp_get_group_id') && bp_get_group_id() > 0 && $item->title === 'Resources') {
            $item->url = site_url('groups/' . bp_get_group_slug() . '/library');
        }
    }

    return $items;
}
?>
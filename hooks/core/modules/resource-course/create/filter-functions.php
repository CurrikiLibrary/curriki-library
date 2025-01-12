<?php

function selectedCourseObjectPost() : void {
    $lessons = array();
    global $wpdb;
    $table_name = $wpdb->prefix . 'resources_courses';
    $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
    $selected_course = null;
    $selected_course_object_post = null;
    if ($course_id > 0) {
        $selected_course = loadCourse($course_id);
        global $selected_course_object_post;
        $selected_course_object_post = loadCoursePost($selected_course);
        global $selected_course_object_resource;
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE course_id = $course_id AND section_id IS NULL AND lesson_id IS NULL AND quiz_id IS NULL AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $resource_query_result = $wpdb->get_row("SELECT * FROM resources WHERE resourceid = {$rows[0]['resource_id']}", ARRAY_A);
            $selected_course_object_resource = $resource_query_result;
        }
    }

    $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : 0;
    if ($section_id > 0 && $course_id > 0) {
        $course = learn_press_get_course( $course_id );
        $sections = $course->get_sections_data_arr();
        $current_section = array_filter($sections, function($section) use ($section_id) {
            return $section['section_id'] == $section_id;
        });
        $current_section = count($current_section) > 0 ? array_values($current_section)[0] : array();
        
        global $selected_course_object_post;
        $section_object = new stdClass();
        $section_object->post_title = $current_section['section_name'];
        $section_object->post_content = $current_section['section_description'];
        $selected_course_object_post = $section_object;
        global $selected_course_object_resource;
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE course_id = $course_id AND section_id = $section_id AND lesson_id IS NULL AND quiz_id IS NULL AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $resource_query_result = $wpdb->get_row("SELECT * FROM resources WHERE resourceid = {$rows[0]['resource_id']}", ARRAY_A);
            $selected_course_object_resource = $resource_query_result;
        }
        
        $lessons = array_filter($current_section['items'], function($item) {
            return $item->type == 'lp_lesson';
        });
        // map $lessons array based on id and type by querying posts table
        $lesson_ids = array_map(function($lesson) { return $lesson->id; }, $lessons);
        $lesson_ids = implode(',', $lesson_ids);
        $lessons = $wpdb->get_results("SELECT id, post_name, post_title, post_content, post_type FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND (post_type = 'lp_lesson') AND ID IN ({$lesson_ids})", ARRAY_A);
        
    }

    if (count($lessons) > 0 && isset($_GET['lesson_id'])) {
        global $selected_course_object_post;
        // filter $lessons based on $_GET['lesson_id']
        $current_lesson_id = $_GET['lesson_id'];
        $current_lesson = array_values( array_filter($lessons, function ($lesson_record) use ($current_lesson_id) {
            return $lesson_record["id"] == $current_lesson_id;
        }) );
        $selected_course_object_post = is_array($current_lesson) && count($current_lesson) > 0 ? get_post($current_lesson[0]['id']) : null;
        global $selected_course_object_resource;
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE course_id = $course_id AND section_id = $section_id AND lesson_id = $current_lesson_id AND quiz_id IS NULL AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $resource_query_result = $wpdb->get_row("SELECT * FROM resources WHERE resourceid = {$rows[0]['resource_id']}", ARRAY_A);
            $selected_course_object_resource = $resource_query_result;
        }
    }
}

function setCourseObjectType() {
    $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
    if ($course_id > 0) {
        global $courseSelectedObjectType;
        $courseSelectedObjectType = "course";
    }

    $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : 0;
    if ($section_id > 0) {
        global $courseSelectedObjectType;
        $courseSelectedObjectType = "section";
    }

    $lesson_id = isset($_REQUEST['lesson_id']) ? $_REQUEST['lesson_id'] : 0;
    if ($lesson_id > 0) {
        global $courseSelectedObjectType;
        $courseSelectedObjectType = "lesson";
    }
}

// course oer create functions

function resourceCourseFilter() : void {
    global $wpdb;
?>
                                    
    <?php
        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
        if ($course_id > 0 && courseResourceExist()) {
            $course = learn_press_get_course( $course_id );
            $sections = $course->get_sections_data_arr();
    ?>
        <div class="col-md-4 filter-col select-section-col">
            <h4><?php echo __('Select Section', 'curriki'); ?></h4>
            <p>
                <select name="section" id="section" class="form-control" style="width: 100%">
                    <option value="" selected="selected">Select Section</option>
                    <?php
                        foreach ($sections as $section) {
                            // get section_id from URL and set selected
                            $selected = '';
                            if(isset($_REQUEST['section_id']) && $_REQUEST['section_id'] == $section['section_id']){
                                $selected = ' selected="selected"';
                            }
                            echo '<option value="' . $section['section_id'] . '"' . $selected . '>' . $section['section_name'] . '</option>';
                        }
                    ?>
                </select>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery('#section').change(function(){
                            var section_id = jQuery(this).val();
                            // redirect to current page with section id using URL API
                            var url = new URL(window.location.href);
                            if (section_id == '') {
                                url.searchParams.delete('section_id');
                            } else {
                                url.searchParams.set('section_id', section_id);
                            }
                            url.searchParams.delete('lesson_id');
                            window.location.href = url;
                        });
                    });
                </script>
            </p>
        </div>
    <?php
        }
    ?>    
    
    <?php
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : 0;
        $lessons = array();
        if ($section_id > 0 && courseSectionResourceExist()) {
            $current_section = array_filter($sections, function($section) use ($section_id) {
                return $section['section_id'] == $section_id;
            });
            $current_section = count($current_section) > 0 ? array_values($current_section)[0] : array();
            $lessons = array_filter($current_section['items'], function($item) {
                return $item->type == 'lp_lesson';
            });

            // map $lessons array based on id and type by querying posts table
            $lesson_ids = array_map(function($lesson) { return $lesson->id; }, $lessons);
            $lesson_ids = implode(',', $lesson_ids);
            $lessons = $wpdb->get_results("SELECT id, post_name, post_title, post_content, post_type FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND (post_type = 'lp_lesson') AND ID IN ({$lesson_ids})", ARRAY_A);
        }

        if (count($lessons) > 0) {
    ?>
        <div class="col-md-4 filter-col select-lesson-col">
            <h4><?php echo __('Select Lesson', 'curriki'); ?></h4>
            <p>
                <select name="lesson" id="lesson" class="form-control" style="width: 100%">
                    <option value="" selected="selected">Select Lesson</option>
                    <?php
                        foreach ($lessons as $lesson) {
                            // get lesson_id from URL and set selected
                            $selected = '';
                            if(isset($_REQUEST['lesson_id']) && $_REQUEST['lesson_id'] == $lesson['id']){
                                $selected = ' selected="selected"';
                            }
                            echo '<option value="' . $lesson['id'] . '"' . $selected . '>' . $lesson['post_title'] . '</option>';
                        }
                    ?>
                </select>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery('#lesson').change(function(){
                            var lesson_id = jQuery(this).val();
                            // redirect to current page with lesson id using URL API
                            var url = new URL(window.location.href);
                            if (lesson_id == '') {
                                url.searchParams.delete('lesson_id');
                            } else {
                                url.searchParams.set('lesson_id', lesson_id);
                            }
                            window.location.href = url;
                        });
                    });
                </script>
            </p>
        </div>
    <?php
        }
    ?>
<?php    
}

function getCourseResourceId(): int {
    $ok = 0;
    global $wpdb;
    $table_name = $wpdb->prefix . 'resources_courses';
    // check if record exists in the table based on course_id
    $course_check = isset($_GET['course_id']) ? true : false;
    if ($course_check) {
        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
        $rows = $wpdb->get_results("
            SELECT * FROM $table_name as rc
            JOIN resources as r ON rc.resource_id = r.resourceid
            WHERE course_id = $course_id AND section_id IS NULL AND lesson_id IS NULL AND resource_id IS NOT NULL 
            ORDER BY rc.resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $ok = intval($rows[0]['resource_id']);
        }   
    }
    return $ok;
}

function courseObjectResourceExist(): bool {
    $ok = false;
    global $wpdb;
    $table_name = $wpdb->prefix . 'resources_courses';
    // check if record exists in the table based on course_id
    $course_check = isset($_GET['course_id']) && !isset($_GET['section_id']) && !isset($_GET['lesson_id']);
    if ($course_check) {
        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
        $rows = $wpdb->get_results("
        SELECT * FROM $table_name as rc
        JOIN resources as r ON rc.resource_id = r.resourceid
        WHERE course_id = $course_id AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $row = $rows[0];
            // $_REQUEST['resourceid'] = $row['resource_id'];
            $ok = true;
        }   
    }

    $course_section_check = isset($_GET['course_id']) && isset($_GET['section_id']) && !isset($_GET['lesson_id']);
    if ($course_section_check) {
        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : 0;
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE course_id = $course_id AND section_id = $section_id AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $row = $rows[0];
            // $_REQUEST['resourceid'] = $row['resource_id'];
            $ok = true;
        }   
    }

    $course_section_lesson_check = isset($_GET['course_id']) && isset($_GET['section_id']) && isset($_GET['lesson_id']);
    if ($course_section_lesson_check) {
        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : 0;
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : 0;
        $lesson_id = isset($_REQUEST['lesson_id']) ? $_REQUEST['lesson_id'] : 0;
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE course_id = $course_id AND section_id = $section_id AND lesson_id = $lesson_id AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $row = $rows[0];
            // $_REQUEST['resourceid'] = $row['resource_id'];
            $ok = true;
        }   
    }
    return $ok;
}

function courseResourceExist(): bool {
    $ok = false;
    global $wpdb;
    $table_name = $wpdb->prefix . 'resources_courses';
    // check if record exists in the table based on course_id
    $course_id = isset($_REQUEST['course_id']) ? intval($_REQUEST['course_id']) : 0;
    if ($course_id > 0) {
        $rows = $wpdb->get_results("
            SELECT rc.*, r.* FROM $table_name as rc
            JOIN resources as r ON rc.resource_id = r.resourceid
            WHERE course_id = $course_id AND section_id IS NULL AND lesson_id IS NULL AND quiz_id IS NULL AND resource_id IS NOT NULL 
            ORDER BY rc.resource_id DESC
        ", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $ok = true;
        }   
    }
    return $ok;
}

function courseSectionResourceExist() : bool {
    $ok = false;
    global $wpdb;
    $table_name = $wpdb->prefix . 'resources_courses';
    // check if record exists in the table based on course_id a section_id
    $course_id = isset($_REQUEST['course_id']) ? intval($_REQUEST['course_id']) : 0;
    $section_id = isset($_REQUEST['section_id']) ? intval($_REQUEST['section_id']) : 0;
    
    if ($course_id > 0 && $section_id > 0) {
        $rows = $wpdb->get_results("
            SELECT rc.* FROM $table_name as rc
            JOIN resources as r ON rc.resource_id = r.resourceid
            WHERE course_id = $course_id AND section_id = $section_id AND lesson_id IS NULL AND quiz_id IS NULL AND resource_id IS NOT NULL 
            ORDER BY rc.resource_id DESC
            ", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $ok = true;
        }   
    }
    return $ok;
}

function courseLessonResourceExist() : bool {
    $ok = false;
    global $wpdb;
    $table_name = $wpdb->prefix . 'resources_courses';
    // check if record exists in the table based on course_id a section_id
    $course_id = isset($_REQUEST['course_id']) ? intval($_REQUEST['course_id']) : 0;
    $section_id = isset($_REQUEST['section_id']) ? intval($_REQUEST['section_id']) : 0;
    $lesson_id = isset($_REQUEST['lesson_id']) ? intval($_REQUEST['lesson_id']) : 0;
    if ($course_id > 0 && $section_id > 0 && $lesson_id > 0) {
        $rows = $wpdb->get_results("
        SELECT * FROM $table_name as rc
        JOIN resources as r ON rc.resource_id = r.resourceid
        WHERE course_id = $course_id AND lesson_id = $lesson_id AND quiz_id IS NULL AND resource_id IS NOT NULL 
        ORDER BY rc.resource_id DESC", ARRAY_A);
        if ($wpdb->num_rows > 0) {
            $ok = true;
        }   
    }
    return $ok;
}

function getCourseObjectParentResource() {
    global $courseSelectedObjectType;
    $courseObjectParentResource = null;    
    if ($courseSelectedObjectType === 'section' ) {
        // get resource by resource_id using wpdb query
        global $wpdb;
        $row = $wpdb->get_row("SELECT * FROM resources WHERE resourceid = {$_REQUEST['resourceid']}", ARRAY_A);
        $courseObjectParentResource = $row;
    }
    
    if ($courseSelectedObjectType === 'lesson') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'resources_courses';
        $course_id = isset($_REQUEST['course_id']) ? intval($_REQUEST['course_id']) : 0;
        $section_id = isset($_REQUEST['section_id']) ? intval($_REQUEST['section_id']) : 0;
        if ($course_id > 0 && $section_id > 0) {
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE course_id = $course_id AND section_id = $section_id AND lesson_id IS NULL AND quiz_id IS NULL AND resource_id IS NOT NULL ORDER BY resource_id DESC", ARRAY_A);
            if (count($rows)) {
                $row = $rows[0];
                //var_dump($row['resource_id']); 
                $row = $wpdb->get_row("SELECT * FROM resources WHERE resourceid = {$row['resource_id']}", ARRAY_A);
                $courseObjectParentResource = $row;
            }
        }
    }

    return $courseObjectParentResource;
}
?>
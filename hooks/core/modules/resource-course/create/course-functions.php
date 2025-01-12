<?php

function loadCourse($course_id) {
    $course = null;
    if ($course_id > 0) {
        $course = learn_press_get_course( intval($course_id) );
    }
    return $course;
}

function loadCoursePost($course) {
    return $course->get_post();
}

?>
<?php
function display_user_courses() {
    $user_id = get_current_user_id();
    global $wpdb;
    $table_name = $wpdb->prefix . 'square_courses_attendees';
    $courses = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d", $user_id
    ));

    if ($courses) {
        echo '<h2>Your Courses</h2><ul>';
        foreach ($courses as $course) {
            $course_info = get_post($course->course_id);
            echo '<li>' . esc_html($course_info->post_title) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>You have not registered for any courses.</p>';
    }
}
?>

<?php
class Square_Courses_Course {
    public static function get_attendees($course_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_bookings';
        $attendees = $wpdb->get_results($wpdb->prepare(
            "SELECT id, full_name, email FROM $table_name WHERE course_id = %d", $course_id
        ));

        return $attendees;
    }
}

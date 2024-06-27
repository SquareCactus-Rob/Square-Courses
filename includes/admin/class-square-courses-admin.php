<?php
class Square_Courses_Admin {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_course_attendees_metabox']);
    }

    public function add_course_attendees_metabox() {
        add_meta_box(
            'course_attendees_metabox',
            'Course Attendees',
            [$this, 'display_course_attendees_metabox'],
            'course',
            'normal',
            'default'
        );
    }

    public function display_course_attendees_metabox($post) {
        $course_id = $post->ID;
        $attendees = Square_Courses_Course::get_attendees($course_id);
        $attendee_count = count($attendees);
        $max_attendees = (int) get_post_meta($course_id, '_square_courses_max_attendees', true);

        // Ensure max_attendees is a valid number
        if (empty($max_attendees) || !is_numeric($max_attendees) || $max_attendees == 0) {
            $max_attendees = 1; // Avoid division by zero
        }

        // Calculate the percentage of the course filled
        $percentage_filled = ($attendee_count / $max_attendees) * 100;

        // Determine the text color based on the percentage filled
        $color = '';
        $status = '';
        if ($percentage_filled >= 100) {
            $color = 'red';
            $status = ' - Fully booked';
        } elseif ($percentage_filled >= 80) {
            $color = 'orange';
        }

        echo '<p style="color: ' . esc_attr($color) . ';">' . esc_html($attendee_count . '/' . $max_attendees . ' Attendees' . $status) . '</p>';
        if ($attendees) {
            echo '<ul>';
            foreach ($attendees as $attendee) {
                $edit_url = admin_url('admin.php?page=edit_attendee&attendee=' . $attendee->id);
                echo '<li>' . esc_html($attendee->full_name) . ' (' . esc_html($attendee->email) . ') (<a href="' . esc_url($edit_url) . '">Edit</a>)</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No attendees yet.</p>';
        }
    }
}

new Square_Courses_Admin();
?>

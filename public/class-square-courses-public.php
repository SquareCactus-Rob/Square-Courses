<?php
class Square_Courses_Public {
    public function __construct() {
        add_action('init', [$this, 'handle_course_registration']);
    }

    public function handle_course_registration() {
        if (isset($_POST['register_course'])) {
            global $wpdb;
            $user_id = get_current_user_id();
            $course_id = intval($_POST['course_id']);
            $registration_date = current_time('mysql');

            $wpdb->insert(
                $wpdb->prefix . 'course_bookings',
                [
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'registration_date' => $registration_date
                ]
            );

            // Send confirmation emails
            $user_info = get_userdata($user_id);
            wp_mail($user_info->user_email, 'Course Registration', 'You have successfully registered for the course.');

            $admin_email = get_option('admin_email');
            wp_mail($admin_email, 'New Course Registration', 'A new user has registered for the course.');

            // Redirect or display a success message
        }
    }
}

new Square_Courses_Public();
?>

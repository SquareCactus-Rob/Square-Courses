<?php

// Display the booking form
function square_courses_display_booking_form($course_id, $category_color) {
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="handle_booking">
        <input type="hidden" name="booking_nonce" value="<?php echo wp_create_nonce('handle_booking'); ?>">
        <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>">
        <p>
            <label for="booking_name"><?php _e('Full Name:', 'textdomain'); ?></label>
            <input type="text" id="booking_name" name="full_name" required>
        </p>
        <p>
            <label for="booking_email"><?php _e('Email:', 'textdomain'); ?></label>
            <input type="email" id="booking_email" name="email" required>
        </p>
        <p>
            <label for="booking_phone"><?php _e('Phone Number:', 'textdomain'); ?></label>
            <input type="text" id="booking_phone" name="phone" required>
        </p>
        <p>
            <input type="checkbox" id="sms_reminder" name="sms_reminder">
            <label for="sms_reminder"><?php _e('I want to receive an SMS reminder 24 hours before this session starts', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="aged_19_or_over" name="aged_19_or_over" required>
            <label for="aged_19_or_over"><?php _e('I am aged 19 or over', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="resident_blackburn" name="resident_blackburn" required>
            <label for="resident_blackburn"><?php _e('I am a resident in Blackburn with Darwen', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="learning_support" name="learning_support">
            <label for="learning_support"><?php _e('I require additional learning support', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="english_level" name="english_level" required>
            <label for="english_level"><?php _e('I have an Entry 3 Level or higher understanding of English to complete this course', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="enrolment_form" name="enrolment_form" required>
            <label for="enrolment_form"><?php _e('YES I have completed the Enrolment Form. If not click here', 'textdomain'); ?></label>
        </p>
        <p>
            <button type="submit" class="book-now" style="background-color:<?php echo esc_attr($category_color); ?>"><?php _e('Book Now', 'textdomain'); ?></button>
        </p>
    </form>
    <?php
}


function square_courses_send_email($course_id, $user_email) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'square_courses_notifications';

    $notifications = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 1");

    foreach ($notifications as $notification) {
        // Add your conditional logic here
        if (true) { // Replace 'true' with your actual condition
            wp_mail($notification->send_to, $notification->subject, $notification->body);
        }
    }
}

// Function to send booking confirmation emails
function square_courses_send_booking_email($booking_data) {
    $to_user = $booking_data['email'];
    $to_admin = get_option('square_courses_admin_email');
    $subject = get_option('square_courses_confirmation_email_subject', 'Booking Confirmation');
    
    // User email content
    $message_user = str_replace(
        ['{{full_name}}', '{{course_title}}', '{{course_date}}', '{{start_time}}', '{{end_time}}', '{{location}}'],
        [
            $booking_data['full_name'],
            get_the_title($booking_data['course_id']),
            $booking_data['course_date'],
            $booking_data['start_time'],
            $booking_data['end_time'],
            $booking_data['location']
        ],
        get_option('square_courses_confirmation_email_body', 'Dear {{full_name}},\n\nThank you for booking the course: {{course_title}}.\n\nCourse Details:\nDate: {{course_date}}\nTime: {{start_time}} - {{end_time}}\nLocation: {{location}}\n\nWe look forward to seeing you!')
    );

    // Admin email content
    $message_admin = 'New booking received:\n\n';
    $message_admin .= 'Name: ' . $booking_data['full_name'] . '\n';
    $message_admin .= 'Email: ' . $booking_data['email'] . '\n';
    $message_admin .= 'Phone: ' . $booking_data['phone'] . '\n';
    $message_admin .= 'Course: ' . get_the_title($booking_data['course_id']) . '\n';
    $message_admin .= 'Date: ' . $booking_data['course_date'] . '\n';
    $message_admin .= 'Time: ' . $booking_data['start_time'] . ' - ' . $booking_data['end_time'] . '\n';
    $message_admin .= 'Location: ' . $booking_data['location'] . '\n';

    // Send emails
    wp_mail($to_user, $subject, $message_user);
    wp_mail($to_admin, 'New Booking Received', $message_admin);
}

// Handle form submission
function square_courses_handle_booking_submission() {
    if (isset($_POST['booking_nonce']) && wp_verify_nonce($_POST['booking_nonce'], 'handle_booking')) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_bookings';

        $data = array(
            'course_id' => intval($_POST['course_id']),
            'full_name' => sanitize_text_field($_POST['full_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'sms_reminder' => isset($_POST['sms_reminder']) ? 1 : 0,
            'aged_19_or_over' => isset($_POST['aged_19_or_over']) ? 1 : 0,
            'resident_blackburn' => isset($_POST['resident_blackburn']) ? 1 : 0,
            'learning_support' => isset($_POST['learning_support']) ? 1 : 0,
            'english_level' => isset($_POST['english_level']) ? 1 : 0,
            'enrolment_form' => isset($_POST['enrolment_form']) ? 1 : 0,
            'registration_date' => current_time('mysql'), // Add registration date
        );

        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            error_log('Database insert failed: ' . $wpdb->last_error);
        }

        // Send booking confirmation email
        square_courses_send_booking_email($data);

        // Redirect to the course page with booking complete notice
        $course_url = get_permalink($data['course_id']);
        wp_redirect(add_query_arg('booking', 'complete', $course_url));
        exit;
    } else {
        error_log('Nonce verification failed or booking_nonce not set.');
    }
}
add_action('admin_post_nopriv_handle_booking', 'square_courses_handle_booking_submission');
add_action('admin_post_handle_booking', 'square_courses_handle_booking_submission');


<?php
function display_edit_attendee_page() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    $attendee_id = isset($_GET['attendee']) ? intval($_GET['attendee']) : 0;

    if ($attendee_id) {
        $table_name = $wpdb->prefix . 'course_bookings';
        $courses_table = $wpdb->prefix . 'posts';
        $attendee = $wpdb->get_row($wpdb->prepare("SELECT cb.*, p.post_title as course_name FROM $table_name cb LEFT JOIN $courses_table p ON cb.course_id = p.ID WHERE cb.id = %d AND p.post_type = 'course'", $attendee_id));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $full_name = sanitize_text_field($_POST['full_name']);
            $email = sanitize_email($_POST['email']);
            $course_id = sanitize_text_field($_POST['course_id']);
            $registration_date = sanitize_text_field($_POST['registration_date']);
            $sms_reminder = isset($_POST['sms_reminder']) ? 1 : 0;
            $aged_19_or_over = isset($_POST['aged_19_or_over']) ? 1 : 0;
            $resident_blackburn = isset($_POST['resident_blackburn']) ? 1 : 0;
            $learning_support = isset($_POST['learning_support']) ? 1 : 0;
            $english_level = isset($_POST['english_level']) ? 1 : 0;
            $enrolment_form = isset($_POST['enrolment_form']) ? 1 : 0;

            $wpdb->update(
                $table_name,
                [
                    'full_name' => $full_name,
                    'email' => $email,
                    'course_id' => $course_id,
                    'registration_date' => $registration_date,
                    'sms_reminder' => $sms_reminder,
                    'aged_19_or_over' => $aged_19_or_over,
                    'resident_blackburn' => $resident_blackburn,
                    'learning_support' => $learning_support,
                    'english_level' => $english_level,
                    'enrolment_form' => $enrolment_form
                ],
                ['id' => $attendee_id],
                ['%s', '%s', '%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d'],
                ['%d']
            );

            // Redirect to prevent re-submission
            wp_redirect(admin_url('admin.php?page=attendees'));
            exit;
        }

        ?>
        <div class="wrap">
            <h2>Edit Attendee</h2>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="full_name">Full Name</label></th>
                        <td><input name="full_name" type="text" id="full_name" value="<?php echo esc_attr($attendee->full_name); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email">Email</label></th>
                        <td><input name="email" type="email" id="email" value="<?php echo esc_attr($attendee->email); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_id">Course ID</label></th>
                        <td><input name="course_id" type="text" id="course_id" value="<?php echo esc_attr($attendee->course_id); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_name">Course Name</label></th>
                        <td><input name="course_name" type="text" id="course_name" value="<?php echo esc_attr($attendee->course_name); ?>" class="regular-text" readonly></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="registration_date">Registration Date</label></th>
                        <td><input name="registration_date" type="text" id="registration_date" value="<?php echo esc_attr($attendee->registration_date); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sms_reminder">SMS Reminder</label></th>
                        <td><input name="sms_reminder" type="checkbox" id="sms_reminder" <?php checked($attendee->sms_reminder, 1); ?>></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aged_19_or_over">Aged 19 or Over</label></th>
                        <td><input name="aged_19_or_over" type="checkbox" id="aged_19_or_over" <?php checked($attendee->aged_19_or_over, 1); ?>></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="resident_blackburn">Resident in Blackburn</label></th>
                        <td><input name="resident_blackburn" type="checkbox" id="resident_blackburn" <?php checked($attendee->resident_blackburn, 1); ?>></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="learning_support">Learning Support</label></th>
                        <td><input name="learning_support" type="checkbox" id="learning_support" <?php checked($attendee->learning_support, 1); ?>></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="english_level">English Level</label></th>
                        <td><input name="english_level" type="checkbox" id="english_level" <?php checked($attendee->english_level, 1); ?>></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enrolment_form">Enrolment Form</label></th>
                        <td><input name="enrolment_form" type="checkbox" id="enrolment_form" <?php checked($attendee->enrolment_form, 1); ?>></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Update Attendee">
                </p>
            </form>
        </div>
        <?php
    } else {
        echo '<div class="wrap"><h2>Invalid Attendee ID</h2></div>';
    }
}
?>

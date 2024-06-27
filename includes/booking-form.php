<?php

// Function to display the dynamic booking form
function square_courses_dynamic_booking_form_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'course_id' => '',
            'category_color' => '',
        ),
        $atts,
        'dynamic_booking_form'
    );

    $fields = get_option('square_courses_booking_form_fields', '');
    $fields = !empty($fields) ? json_decode($fields, true) : array();

    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="handle_booking">
        <input type="hidden" name="booking_nonce" value="<?php echo wp_create_nonce('handle_booking'); ?>">
        <input type="hidden" name="course_id" value="<?php echo esc_attr($atts['course_id']); ?>">

        <?php
        foreach ($fields as $field) {
            echo '<p>';
            if (!empty($field['label'])) {
                echo '<label for="' . esc_attr($field['id']) . '">' . esc_html($field['label']) . '</label>';
            }
            echo '<input type="' . esc_attr($field['type']) . '" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['name']) . '" ' . (!empty($field['required']) ? 'required' : '') . '>';
            echo '</p>';
        }
        ?>

        <p>
            <button type="submit" class="book-now" style="background-color:<?php echo esc_attr($atts['category_color']); ?>"><?php _e('Book Now', 'textdomain'); ?></button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('dynamic_booking_form', 'square_courses_dynamic_booking_form_shortcode');

// Handle form submission
function square_courses_handle_booking_submission() {
    if (isset($_POST['booking_nonce']) && wp_verify_nonce($_POST['booking_nonce'], 'handle_booking')) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_bookings';

        $fields = get_option('square_courses_booking_form_fields', '');
        $fields = !empty($fields) ? json_decode($fields, true) : array();

        $data = array(
            'course_id' => intval($_POST['course_id']),
            'registration_date' => current_time('mysql'),
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field['name']])) {
                $data[$field['name']] = sanitize_text_field($_POST[$field['name']]);
            }
        }

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

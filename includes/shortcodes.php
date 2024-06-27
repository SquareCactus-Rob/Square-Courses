<?php
// Shortcode for login form
function square_courses_login_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/login-page.php';
    return ob_get_clean();
}
add_shortcode('square_courses_login_form', 'square_courses_login_form_shortcode');

// Shortcode for registration form
function square_courses_registration_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/registration-page.php';
    return ob_get_clean();
}
add_shortcode('square_courses_registration_form', 'square_courses_registration_form_shortcode');

// Booking Form Shortcode
function square_courses_booking_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'course_id' => '',
    ), $atts, 'booking_form');

    ob_start();
    ?>
    <form id="booking-form" method="post">
        <p>
            <label for="name"><?php _e('Full Name:', 'textdomain'); ?></label>
            <input type="text" name="name" id="name" required>
        </p>
        <p>
            <label for="email"><?php _e('Email:', 'textdomain'); ?></label>
            <input type="email" name="email" id="email" required>
        </p>
        <p>
            <label for="phone"><?php _e('Phone Number:', 'textdomain'); ?></label>
            <input type="text" name="phone" id="phone" required>
        </p>
        <p>
            <input type="checkbox" name="sms_reminder" id="sms_reminder">
            <label for="sms_reminder"><?php _e('I want to receive an SMS reminder 24 hours before this session starts', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="age_over_19" id="age_over_19" required>
            <label for="age_over_19"><?php _e('I am aged 19 or over', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="resident" id="resident" required>
            <label for="resident"><?php _e('I am a resident in Blackburn with Darwen', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="learning_support" id="learning_support">
            <label for="learning_support"><?php _e('I require additional learning support', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="english_level" id="english_level" required>
            <label for="english_level"><?php _e('I have an Entry 3 Level or higher understanding of English to complete this course', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="enrolment_form" id="enrolment_form" required>
            <label for="enrolment_form"><?php _e('YES I have completed the Enrolment Form. If not click here', 'textdomain'); ?></label>
        </p>
        <p>
            <input type="hidden" name="course_id" value="<?php echo esc_attr($atts['course_id']); ?>">
            <input type="submit" value="<?php _e('Book Now', 'textdomain'); ?>">
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('booking_form', 'square_courses_booking_form_shortcode');

// Shortcodes and Placeholders
function square_courses_replace_placeholders($content, $course_id, $user_email) {
    // Add your placeholders and their replacements
    $replacements = [
        '{course_name}' => get_the_title($course_id),
        '{user_email}' => $user_email,
    ];

    foreach ($replacements as $placeholder => $replacement) {
        $content = str_replace($placeholder, $replacement, $content);
    }

    return $content;
}


// Function to display upcoming courses
function square_courses_upcoming_courses_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'limit' => -1, // Show all courses by default
            'order' => 'ASC', // Order by ascending date by default
            'hide_fully_booked' => false, // Show fully booked courses by default
        ),
        $atts,
        'upcoming_courses'
    );

    ob_start();

    echo '<div class="course-list">';

    // Query the courses
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => intval($atts['limit']),
        'meta_key' => '_square_courses_start_date',
        'orderby' => 'meta_value',
        'order' => sanitize_text_field($atts['order']),
        'meta_query' => array(
            array(
                'key' => '_square_courses_start_date',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            )
        )
    );

    if ($atts['hide_fully_booked']) {
        $args['meta_query'][] = array(
            'key' => '_square_courses_max_attendees',
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC'
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $course_id = get_the_ID();
            $category = get_the_terms($course_id, 'course_category');
            $category_slug = !empty($category) && !is_wp_error($category) ? $category[0]->slug : '';
            $category_name = !empty($category) && !is_wp_error($category) ? $category[0]->name : '';
            $category_color = !empty($category) && !is_wp_error($category) ? get_term_meta($category[0]->term_id, 'category_color', true) : '';

            $start_date = get_post_meta($course_id, '_square_courses_start_date', true);
            $end_date = get_post_meta($course_id, '_square_courses_end_date', true);
            $start_date_formatted = date_i18n('l jS F', strtotime($start_date));
            $end_date_formatted = date_i18n('l jS F Y', strtotime($end_date));

            $max_attendees = (int) get_post_meta($course_id, '_square_courses_max_attendees', true);
            $attendees = Square_Courses_Course::get_attendees($course_id);
            $attendees_count = count($attendees);
            $remaining_spots = $max_attendees - $attendees_count;

            // Determine the availability status
            $availability_status = '';
            $availability_color = '';
            if ($remaining_spots <= 0) {
                $availability_status = __('Fully Booked', 'textdomain');
                $availability_color = 'red';
            } elseif ($remaining_spots <= ceil($max_attendees * 0.2)) {
                $availability_status = __('Limited Availability', 'textdomain');
                $availability_color = 'orange';
            } else {
                $availability_status = __('Availability', 'textdomain');
                $availability_color = 'green';
            }
            ?>
            <div class="course-item" data-category="<?php echo esc_attr($category_slug); ?>" style="border-color:<?php echo esc_attr($category_color); ?>">
                <a href="<?php the_permalink(); ?>" class="course-link">
                    <div class="course-thumbnail" style="background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');"></div>
                    <h2 class="course-title" style="color:<?php echo esc_attr($category_color); ?>"><?php the_title(); ?></h2>
                    <p class="course-dates"><?php echo esc_html($start_date_formatted); ?> - <?php echo esc_html($end_date_formatted); ?></p>
                    <p class="course-category"><a href="#" data-category="<?php echo esc_attr($category_slug); ?>" style="color:<?php echo esc_attr($category_color); ?>"><?php echo esc_html($category_name); ?></a></p>
                    <a href="<?php the_permalink(); ?>" class="button learn-more" style="background-color:<?php echo esc_attr($category_color); ?>"><?php _e('Learn More', 'textdomain'); ?></a>
                </a>
                <p class="course-availability" style="color:<?php echo esc_attr($availability_color); ?>"><?php echo esc_html($availability_status); ?></p>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>' . __('No upcoming courses found.', 'textdomain') . '</p>';
    endif;

    echo '</div>';

    return ob_get_clean();
}
add_shortcode('upcoming_courses', 'square_courses_upcoming_courses_shortcode');

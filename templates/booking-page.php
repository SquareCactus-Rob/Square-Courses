<?php
/* Template Name: Booking Page */
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        // Get the course title and start date from the query parameters
        $course_title = isset($_GET['course_title']) ? sanitize_text_field(urldecode($_GET['course_title'])) : '';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field(urldecode($_GET['start_date'])) : '';
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

        // Retrieve the course post by title and start date
        $args = array(
            'post_type' => 'course',
            'p' => $course_id,
            'meta_query' => array(
                array(
                    'key' => '_square_courses_start_date',
                    'value' => date('Y-m-d', strtotime($start_date)),
                    'compare' => '='
                )
            )
        );

        $course_query = new WP_Query($args);
        if ($course_query->have_posts()) {
            $course_query->the_post();
            $course_post = get_post();

            $num_sessions = get_post_meta($course_post->ID, '_square_courses_num_sessions', true);
            $schedule_type = get_post_meta($course_post->ID, '_square_courses_schedule_type', true);
            $start_date = get_post_meta($course_post->ID, '_square_courses_start_date', true);
            $end_date = get_post_meta($course_post->ID, '_square_courses_end_date', true);
            $start_time = get_post_meta($course_post->ID, '_square_courses_start_time', true);
            $end_time = get_post_meta($course_post->ID, '_square_courses_end_time', true);
            $contact_details = get_post_meta($course_post->ID, '_square_courses_contact_details', true);
            $venue_id = get_post_meta($course_post->ID, '_square_courses_location', true);
            $partner_id = get_post_meta($course_post->ID, '_square_courses_associate_partner', true);
            $max_attendees = (int) get_post_meta($course_post->ID, '_square_courses_max_attendees', true);

            // Retrieve attendees count
            $attendees = Square_Courses_Course::get_attendees($course_post->ID);
            $attendees_count = count($attendees);
            $remaining_spots = $max_attendees - $attendees_count;

            $category = get_the_terms($course_post->ID, 'course_category');
            $category_color = get_term_meta($category[0]->term_id, 'category_color', true);

            $venue_term = get_term($venue_id, 'course_venue');
            $venue_name = $venue_term ? $venue_term->name : '';
            $venue_address = $venue_term ? get_term_meta($venue_term->term_id, 'venue_address', true) : '';
            $partner_term = get_term($partner_id, 'course_partner');
            $partner_logo = $partner_term ? get_term_meta($partner_term->term_id, 'partner_featured_image', true) : '';
            $partner_url = $partner_term ? get_term_meta($partner_term->term_id, 'partner_url', true) : '';

            $category_color_style = !empty($category_color) ? 'style="color:' . esc_attr($category_color) . '"' : '';

            ?>
            <div class="booking-container">
                <div class="client-details">
                    <h2><?php _e('Your Details', 'textdomain'); ?></h2>
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <input type="hidden" name="action" value="handle_booking">
                        <input type="hidden" name="course_id" value="<?php echo esc_attr($course_post->ID); ?>">
                        <?php wp_nonce_field('handle_booking', 'booking_nonce'); ?>
                        <p>
                            <label for="full_name"><?php _e('Full Name *', 'textdomain'); ?></label>
                            <input type="text" name="full_name" id="full_name" required>
                        </p>
                        <p>
                            <label for="email"><?php _e('Email *', 'textdomain'); ?></label>
                            <input type="email" name="email" id="email" required>
                        </p>
                        <p>
                            <label for="phone"><?php _e('Phone Number *', 'textdomain'); ?></label>
                            <input type="text" name="phone" id="phone" required>
                        </p>
                        <p>
                            <label><input type="checkbox" name="sms_reminder"> <?php _e('I want to receive an SMS reminder 24 hours before this session starts', 'textdomain'); ?></label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="aged_19_or_over" required> <?php _e('I am aged 19 or over *', 'textdomain'); ?></label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="resident_blackburn" required> <?php _e('I am a resident in Blackburn with Darwen *', 'textdomain'); ?></label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="learning_support"> <?php _e('I require additional learning support', 'textdomain'); ?></label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="english_level" required> <?php _e('I have an Entry 3 Level or higher understanding of English to complete this course *', 'textdomain'); ?></label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="enrolment_form" required> <?php _e('YES I have completed the Enrolment Form. If not click here *', 'textdomain'); ?></label>
                        </p>
                        <p>
                            <button type="submit" class="book-now" style="color: white; background-color: <?php echo esc_attr($category_color); ?>; border: 1px solid transparent; "onmouseover="this.style.color = '<?php echo esc_attr($category_color); ?>'; this.style.backgroundColor = 'white'; this.style.borderColor = '<?php echo esc_attr($category_color); ?>'; "onmouseout=" this.style.color = 'white'; this.style.backgroundColor = '<?php echo esc_attr($category_color); ?>'; this.style.borderColor = 'transparent';"><?php _e('Book Now', 'textdomain'); ?></button>
                        </p>
                    </form>
                </div><!-- .client-details -->

                <div class="booking-details">
                    <?php if (has_post_thumbnail($course_post->ID)): ?>
                        <div class="course-featured-image" style="border-color:<?php echo esc_attr($category_color); ?>">
                            <?php echo get_the_post_thumbnail($course_post->ID); ?>
                        </div>
                    <?php endif; ?>

                    <h2><?php _e('Course Details', 'textdomain'); ?></h2>
                    <ul class="course-meta">
                        <li><strong><?php _e('Title:', 'textdomain'); ?></strong> <br><?php echo esc_html(get_the_title($course_post->ID)); ?></li>
                        <li><strong><?php _e('Dates:', 'textdomain'); ?></strong> <br><?php printf(__('From %1$s to %2$s', 'textdomain'), date_i18n('D d M Y', strtotime($start_date)), date_i18n('D d M Y', strtotime($end_date))); ?></li>
                        <li><strong><?php _e('Course ID:', 'textdomain'); ?></strong> <br><?php echo esc_html($course_id); ?></li>
                        <li><strong><?php _e('Number of Sessions:', 'textdomain'); ?></strong> <br><?php echo esc_html($num_sessions); ?></li>
                        <li><strong><?php _e('Contact Details:', 'textdomain'); ?></strong> <br><?php echo esc_html($contact_details); ?></li>
                        <li><strong><?php _e('Location:', 'textdomain'); ?></strong> <br><?php echo esc_html($venue_name . ', ' . $venue_address); ?></li>
                        <?php if ($partner_logo): ?>
                            <li><strong><?php _e('Associate Partner:', 'textdomain'); ?></strong><br>
                                <a href="<?php echo esc_url($partner_url); ?>" target="_blank"><img src="<?php echo esc_url($partner_logo); ?>" alt="<?php echo esc_attr($partner_term->name); ?>"></a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <strong><?php _e('Attendees:', 'textdomain'); ?></strong>
                            <br>
                            <span style="color:<?php echo esc_attr($category_color); ?>"><?php echo esc_html($attendees_count . '/' . $max_attendees . ' Attendees'); ?></span>
                        </li>
                    </ul>
                </div>
            </div><!-- .booking-container -->
            <?php
        } else {
            echo '<p>' . __('Course not found.', 'textdomain') . '</p>';
        }
        ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

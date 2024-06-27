<?php
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        while (have_posts()) : the_post();
            $course_id = get_the_ID();
            $num_sessions = get_post_meta($course_id, '_square_courses_num_sessions', true);
            $schedule_type = get_post_meta($course_id, '_square_courses_schedule_type', true);
            $start_date = get_post_meta($course_id, '_square_courses_start_date', true);
            $end_date = get_post_meta($course_id, '_square_courses_end_date', true);
            $start_time = get_post_meta($course_id, '_square_courses_start_time', true);
            $end_time = get_post_meta($course_id, '_square_courses_end_time', true);
            $contact_details = get_post_meta($course_id, '_square_courses_contact_details', true);
            $venue_id = get_post_meta($course_id, '_square_courses_location', true);
            $partner_id = get_post_meta($course_id, '_square_courses_associate_partner', true);
            $max_attendees = (int) get_post_meta($course_id, '_square_courses_max_attendees', true);

            // Retrieve attendees count
            $attendees = Square_Courses_Course::get_attendees($course_id);
            $attendees_count = count($attendees);
            $remaining_spots = $max_attendees - $attendees_count;
            $category = get_the_terms($course_id, 'course_category');
            $category_color = '';
            if (!is_wp_error($category) && !empty($category)) {
                $category_color = get_term_meta($category[0]->term_id, 'category_color', true);
            }

            $venue_term = get_term($venue_id, 'course_venue');
            $venue_name = $venue_term ? $venue_term->name : '';
            $venue_address = $venue_term ? get_term_meta($venue_term->term_id, 'venue_address', true) : '';
            $partner_term = get_term($partner_id, 'course_partner');
            $partner_logo = $partner_term ? get_term_meta($partner_term->term_id, 'partner_featured_image', true) : '';
            $partner_url = $partner_term ? get_term_meta($partner_term->term_id, 'partner_url', true) : '';

            $category_color_style = !empty($category_color) ? 'style="color:' . esc_attr($category_color) . '"' : '';

            // Calculate the percentage of the course filled
            $percentage_filled = $max_attendees > 0 ? ($attendees_count / $max_attendees) * 100 : 0;

            // Determine the text color based on the percentage filled
            $color = '';
            $status = '';
            if ($percentage_filled >= 100) {
                $color = 'red';
                $status = ' - Fully booked';
            } elseif ($percentage_filled >= 80) {
                $color = 'orange';
            }
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php if (isset($_GET['booking']) && $_GET['booking'] == 'complete') : ?>
                    <div class="notice notice-success">
                        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
                        <p><?php _e('Booking complete! You will receive a confirmation email shortly.', 'textdomain'); ?></p>
                    </div>
                <?php endif; ?>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title" ' . $category_color_style . '>', '</h1>'); ?>
                </header><!-- .entry-header -->

                <div class="booking-container">
                    <div class="client-details">
                        <div class="entry-content">
                            <?php the_content(); ?>

                            <?php if ($num_sessions && $start_date && $start_time && $end_time): ?>
                                <div class="course-schedule">
                                    <h2><?php _e('Upcoming Sessions', 'textdomain'); ?></h2>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><?php _e('Date', 'textdomain'); ?></th>
                                                <th><?php _e('Time and Runtime', 'textdomain'); ?></th>
                                                <?php if ($venue_name): ?>
                                                    <th><?php _e('Location', 'textdomain'); ?></th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $current_date = strtotime($start_date);
                                            for ($i = 0; $i < $num_sessions; $i++) {
                                                $session_date = date_i18n('D d M', $current_date);
                                                $session_start_time = date_i18n('H:i', strtotime($start_time));
                                                $session_end_time = date_i18n('H:i', strtotime($end_time));
                                                $session_duration = (strtotime($end_time) - strtotime($start_time)) / 3600;
                                                echo '<tr><td>' . $session_date . '</td><td>' . $session_start_time . ' - ' . $session_end_time . ' (' . $session_duration . ' hr)</td>';
                                                if ($venue_name) {
                                                    echo '<td>' . esc_html($venue_name) . '</td>';
                                                }
                                                echo '</tr>';
                                                $current_date = strtotime('+1 week', $current_date); // Adjust for other schedule types
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div><!-- .entry-content -->
                    </div><!-- .client-details -->

                    <div class="booking-details">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="course-featured-image" style="border-color:<?php echo esc_attr($category_color); ?>">
                                <?php the_post_thumbnail(); ?>
                            </div>
                        <?php endif; ?>

                        <h2><?php _e('Course Details', 'textdomain'); ?></h2>
                        <ul class="course-meta">
                            <li><strong><?php _e('Title:', 'textdomain'); ?></strong> <br><?php the_title(); ?></li>
                            <li><strong><?php _e('Dates:', 'textdomain'); ?></strong> <br><?php printf(__('From %1$s to %2$s', 'textdomain'), date_i18n('D d M Y', strtotime($start_date)), date_i18n('D d M Y', strtotime($end_date))); ?></li>
                            <li><strong><?php _e('Course ID:', 'textdomain'); ?></strong> <br><?php echo esc_html($course_id); ?></li>
                            <li><strong><?php _e('Number of Sessions:', 'textdomain'); ?></strong> <br><?php echo esc_html($num_sessions); ?></li>
                            <?php if ($contact_details): ?>
                                <li><strong><?php _e('Contact Details:', 'textdomain'); ?></strong> <br><?php echo esc_html($contact_details); ?></li>
                            <?php endif; ?>
                            <?php if ($venue_name || $venue_address): ?>
                                <li><strong><?php _e('Location:', 'textdomain'); ?></strong> <br><?php echo esc_html($venue_name . ($venue_address ? ', ' . $venue_address : '')); ?></li>
                            <?php endif; ?>
                            <?php if ($partner_logo): ?>
                                <li><strong><?php _e('Associate Partner:', 'textdomain'); ?></strong><br>
                                    <a href="<?php echo esc_url($partner_url); ?>" target="_blank"><img src="<?php echo esc_url($partner_logo); ?>" alt="<?php echo esc_attr($partner_term->name); ?>"></a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <strong><?php _e('Attendees:', 'textdomain'); ?></strong>
                                <br>
                                <span style="color:<?php echo esc_attr($color); ?>"><?php echo esc_html($attendees_count . '/' . $max_attendees . ' Attendees' . $status); ?></span>
                            </li>
                        </ul>

                        <?php if (is_user_logged_in()): ?>
                            <?php if ($remaining_spots <= 0): ?>
                                <p class="course-booking fully-booked"><?php _e('This course is fully booked', 'textdomain'); ?></p>
                            <?php elseif ($remaining_spots <= ceil($max_attendees * 0.2)): ?>
                                <p class="course-booking limited-spots"><?php _e('Limited availability - Only a few spots left', 'textdomain'); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(get_permalink(get_page_by_path('booking-page')->ID)); ?>?course_title=<?php echo urlencode(get_the_title()); ?>&start_date=<?php echo urlencode(date_i18n('d-m-Y', strtotime($start_date))); ?>&course_id=<?php echo urlencode($course_id); ?>" class="button book-now" style="background-color:<?php echo esc_attr($category_color); ?>"><?php _e('Book Now', 'textdomain'); ?></a>
                        <?php else: ?>
                            <a href="#" class="button login-to-book book-now" style="background-color:<?php echo esc_attr($category_color); ?>" data-toggle="modal" data-target="#loginModal"><?php _e('Login to Book', 'textdomain'); ?></a>
                        <?php endif; ?>
                    </div>
                </div><!-- .booking-container -->
            </article><!-- #post-<?php the_ID(); ?> -->

            <!-- Modal -->
            <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="loginModalLabel"><?php _e('Login', 'textdomain'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="<?php _e('Close', 'textdomain'); ?>">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php wp_login_form(); ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php the_title('<h2 class="entry-title">', '</h2>'); ?>
        <div class="entry-meta">
            <?php echo get_the_excerpt(); ?>
        </div>
    </header><!-- .entry-header -->

    <div class="entry-content">
        <?php the_content(); ?>
        <ul class="course-meta">
            <li><strong><?php _e('Number of Sessions:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_num_sessions', true); ?></li>
            <li><strong><?php _e('Schedule Type:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_schedule_type', true); ?></li>
            <li><strong><?php _e('Start Date:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_start_date', true); ?></li>
            <li><strong><?php _e('End Date:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_end_date', true); ?></li>
            <li><strong><?php _e('Start Time:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_start_time', true); ?></li>
            <li><strong><?php _e('End Time:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_end_time', true); ?></li>
            <li><strong><?php _e('Contact Details:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_contact_details', true); ?></li>
            <li><strong><?php _e('Location:', 'textdomain'); ?></strong> <?php
                $venue = get_post_meta(get_the_ID(), '_square_courses_location', true);
                if ($venue) {
                    $term = get_term($venue, 'course_venue');
                    echo esc_html($term->name);
                }
            ?></li>
            <li><strong><?php _e('Associate Partner:', 'textdomain'); ?></strong> <?php
                $partner = get_post_meta(get_the_ID(), '_square_courses_associate_partner', true);
                if ($partner) {
                    $term = get_term($partner, 'course_partner');
                    echo esc_html($term->name);
                }
            ?></li>
            <li><strong><?php _e('Course ID:', 'textdomain'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_square_courses_course_id', true); ?></li>
        </ul>
    </div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->

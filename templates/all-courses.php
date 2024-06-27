<?php
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="course-filter flex">
            <div class="category-filter-div" data-category="all" data-color="#230349">
                <img src="<?php echo plugins_url('assets/graphics/All.png', dirname(__FILE__)); ?>" alt="All Courses">
            </div>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'course_category',
                'hide_empty' => false,
            ));
            foreach ($categories as $category) {
                $sanitized_category_name = str_replace(' ', '-', $category->name);
                $category_color = get_term_meta($category->term_id, 'category_color', true);
                echo '<div class="category-filter-div" data-category="' . esc_attr($category->slug) . '" data-color="' . esc_attr($category_color) . '">';
                echo '<img src="' . plugins_url('assets/graphics/' . $sanitized_category_name . '.png', dirname(__FILE__)) . '" alt="' . esc_attr($category->name) . '">';
                echo '</div>';
            }
            ?>
        </div>
        
        <div id="course-list" class="course-list flex">
            <?php
            $args = array(
                'post_type' => 'course',
                'posts_per_page' => -1,
                'meta_key' => '_square_courses_start_date',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_square_courses_start_date',
                        'value' => date('Y-m-d'),
                        'compare' => '>=',
                        'type' => 'DATE'
                    )
                )
            );
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
            ?>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Function to filter courses
        function filterCourses(category) {
            var categoryColor = $('.category-filter-div[data-category="' + category + '"]').data('color');

            // Remove active class from all and add to the clicked one
            $('.category-filter-div').removeClass('active');
            $('.category-filter-div img').css('border', 'none'); // Remove border from all
            $('.category-filter-div[data-category="' + category + '"]').addClass('active');
            $('.category-filter-div[data-category="' + category + '"] img').css('border', '4px solid ' + categoryColor); // Add border with category color
            
            $.ajax({
                url: ajaxurl, // WordPress AJAX
                type: 'POST',
                data: {
                    action: 'filter_courses',
                    category: category,
                },
                success: function(response) {
                    $('#course-list').html(response);
                }
            });
        }

        // Event listener for category filter divs
        $('.category-filter-div').on('click', function() {
            var category = $(this).data('category');
            filterCourses(category);
        });

        // Check for category parameter in URL
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');

        if (category) {
            filterCourses(category);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const filterDivs = document.querySelectorAll('.category-filter-div');
        const courseItems = document.querySelectorAll('.course-item');

        filterDivs.forEach(function (div) {
            div.addEventListener('click', function () {
                const selectedCategory = this.getAttribute('data-category');

                courseItems.forEach(function (item) {
                    if (selectedCategory === 'all' || item.getAttribute('data-category') === selectedCategory) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });

                filterDivs.forEach(function (div) {
                    div.classList.remove('active');
                    div.style.borderColor = ""; // Reset border color
                });
                this.classList.add('active');
                this.style.borderColor = this.getAttribute('data-color'); // Set border color to category color
            });
        });

        // Apply initial filter if category is in URL
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');

        if (category) {
            filterDivs.forEach(function (div) {
                if (div.getAttribute('data-category') === category) {
                    div.click();
                }
            });
        }
    });
</script>

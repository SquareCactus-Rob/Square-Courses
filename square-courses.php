<?php
/*
Plugin Name: Square Courses
Description: A plugin to manage and book courses for BwD Adult Learning service.
Version: 1.0
Author: Square Cactus
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('SQUARE_COURSES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SQUARE_COURSES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/post-types.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/taxonomies.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/meta-boxes.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/shortcodes.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/settings.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/booking-form.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/admin.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/class-square-courses-install.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'public/class-square-courses-public.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/models/class-square-courses-course.php';
include_once SQUARE_COURSES_PLUGIN_DIR . 'includes/admin/class-square-courses-admin.php';

// Activation and Deactivation Hooks
register_activation_hook(__FILE__, 'square_courses_activate');
register_deactivation_hook(__FILE__, 'square_courses_deactivate');

function square_courses_activate() {
    square_courses_register_post_types();
    square_courses_register_taxonomies();
    flush_rewrite_rules(); // To refresh permalinks
    square_courses_create_booking_table();
    Square_Courses_Install::install();
}

function square_courses_deactivate() {
    flush_rewrite_rules(); // To refresh permalinks
}

function square_courses_create_booking_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'course_bookings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        course_id mediumint(9) NOT NULL,
        full_name tinytext NOT NULL,
        email text NOT NULL,
        phone text NOT NULL,
        sms_reminder boolean DEFAULT 0 NOT NULL,
        aged_19_or_over boolean DEFAULT 0 NOT NULL,
        resident_blackburn boolean DEFAULT 0 NOT NULL,
        learning_support boolean DEFAULT 0 NOT NULL,
        english_level boolean DEFAULT 0 NOT NULL,
        enrolment_form boolean DEFAULT 0 NOT NULL,
        registration_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action('init', 'square_courses_init');
function square_courses_init() {
    square_courses_register_post_types();
    square_courses_register_taxonomies();
}

function square_courses_enqueue_admin_scripts($hook) {
    if ($hook != 'edit.php?post_type=course' && $hook != 'post-new.php' && $hook != 'term.php' && $hook != 'edit-tags.php' && $hook != 'course_page_square_courses') {
        return;
    }

    wp_enqueue_script('square-courses-admin-js', SQUARE_COURSES_PLUGIN_URL . 'assets/admin.js', array('jquery', 'wp-color-picker', 'media-upload', 'thickbox'), null, true);
    wp_localize_script('square-courses-admin-js', 'square_courses_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('square_courses_nonce')
    ));
    wp_enqueue_style('square-courses-admin-css', SQUARE_COURSES_PLUGIN_URL . 'assets/admin.css');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('thickbox');
}

add_action('admin_enqueue_scripts', 'square_courses_enqueue_admin_scripts');

function square_courses_register_templates($templates) {
    $templates['all-courses.php'] = 'All Courses';
    return $templates;
}
add_filter('theme_page_templates', 'square_courses_register_templates');

function square_courses_load_plugin_template($template) {
    if (get_page_template_slug() == 'all-courses.php') {
        $plugin_template = SQUARE_COURSES_PLUGIN_DIR . 'templates/all-courses.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'square_courses_load_plugin_template');

function square_courses_single_template($single) {
    global $post;

    if ($post->post_type == 'course') {
        if (file_exists(SQUARE_COURSES_PLUGIN_DIR . 'templates/single-course.php')) {
            return SQUARE_COURSES_PLUGIN_DIR . 'templates/single-course.php';
        }
    }

    return $single;
}
add_filter('single_template', 'square_courses_single_template');

function square_courses_enqueue_styles() {
    wp_enqueue_style('square-courses-css', SQUARE_COURSES_PLUGIN_URL . 'assets/square-courses.css');
}
add_action('wp_enqueue_scripts', 'square_courses_enqueue_styles');

function square_courses_enqueue_media_uploader() {
    if (is_admin()) {
        wp_enqueue_media();
        wp_enqueue_script('square-courses-admin-scripts', plugin_dir_url(__FILE__) . 'js/admin-scripts.js', array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'square_courses_enqueue_media_uploader');

function square_courses_remove_default_metaboxes() {
    remove_meta_box('tagsdiv-course_partner', 'course', 'side');
}
add_action('admin_menu', 'square_courses_remove_default_metaboxes');

function enqueue_bootstrap_modal() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_modal');

function check_custom_template($template) {
    if (is_page_template('templates/booking-page.php')) {
        error_log('Booking Page Template Loaded');
    } else {
        error_log('Current Template: ' . $template);
    }
    return $template;
}
add_filter('template_include', 'check_custom_template');

function force_booking_page_template($template) {
    if (is_page('booking-page')) {
        $new_template = plugin_dir_path(__FILE__) . 'templates/booking-page.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'force_booking_page_template');

add_action('wp_ajax_resend_confirmation_email', 'resend_confirmation_email');
function resend_confirmation_email() {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $attendee_id = isset($_POST['attendee_id']) ? intval($_POST['attendee_id']) : 0;

    if ($attendee_id > 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_bookings';
        $attendee = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $attendee_id));

        if ($attendee) {
            $to = $attendee->email;
            $subject = 'Course Confirmation';
            $message = 'Hello ' . $attendee->full_name . ',<br><br>You have been successfully registered for the course.';
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            if (wp_mail($to, $subject, $message, $headers)) {
                echo 'Confirmation email reissued successfully.';
            } else {
                echo 'Failed to send confirmation email.';
            }
        } else {
            echo 'Attendee not found.';
        }
    } else {
        echo 'Invalid attendee ID.';
    }

    wp_die();
}

add_action('wp_ajax_filter_courses', 'filter_courses');
add_action('wp_ajax_nopriv_filter_courses', 'filter_courses');

function filter_courses() {
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

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

    if ($category !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'course_category',
                'field' => 'slug',
                'terms' => $category,
            )
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $category = get_the_terms(get_the_ID(), 'course_category');
            $category_slug = !empty($category) && !is_wp_error($category) ? $category[0]->slug : '';
            $category_name = !empty($category) && !is_wp_error($category) ? $category[0]->name : '';
            $category_color = !empty($category) && !is_wp_error($category) ? get_term_meta($category[0]->term_id, 'category_color', true) : '';
            
            $start_date = get_post_meta(get_the_ID(), '_square_courses_start_date', true);
            $end_date = get_post_meta(get_the_ID(), '_square_courses_end_date', true);

            $start_date_formatted = date_i18n('l jS F', strtotime($start_date));
            $end_date_formatted = date_i18n('l jS F Y', strtotime($end_date));
            ?>
            <div class="course-item" data-category="<?php echo esc_attr($category_slug); ?>" style="border-color:<?php echo esc_attr($category_color); ?>">
                <a href="<?php the_permalink(); ?>" class="course-link">
                    <div class="course-thumbnail" style="background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');"></div>
                    <h2 class="course-title" style="color:<?php echo esc_attr($category_color); ?>"><?php the_title(); ?></h2>
                    <p class="course-dates"><?php echo esc_html($start_date_formatted); ?> - <?php echo esc_html($end_date_formatted); ?></p>
                    <p class="course-category"><a href="#" data-category="<?php echo esc_attr($category_slug); ?>" style="color:<?php echo esc_attr($category_color); ?>"><?php echo esc_html($category_name); ?></a></p>
                    <a href="<?php the_permalink(); ?>" class="button learn-more" style="background-color:<?php echo esc_attr($category_color); ?>"><?php _e('Learn More', 'textdomain'); ?></a>
                </a>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>' . __('No upcoming courses found.', 'textdomain') . '</p>';
    endif;

    wp_die(); // Terminate immediately and return a proper response
}
function square_courses_enqueue_scripts() {
    wp_enqueue_style('square-courses-css', SQUARE_COURSES_PLUGIN_URL . 'assets/square-courses.css');
    wp_enqueue_script('square-courses-js', SQUARE_COURSES_PLUGIN_URL . 'assets/square-courses.js', array('jquery'), null, true);
    wp_localize_script('square-courses-js', 'square_courses_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('square_courses_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'square_courses_enqueue_scripts');


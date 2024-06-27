<?php
// Register Custom Post Types and Taxonomies
function square_courses_register_post_types() {
    $labels = array(
        'name' => _x('Courses', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Course', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => __('Courses', 'textdomain'),
        'name_admin_bar' => __('Course', 'textdomain'),
        'archives' => __('Course Archives', 'textdomain'),
        'attributes' => __('Course Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Course:', 'textdomain'),
        'all_items' => __('All Courses', 'textdomain'),
        'add_new_item' => __('Add New Course', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Course', 'textdomain'),
        'edit_item' => __('Edit Course', 'textdomain'),
        'update_item' => __('Update Course', 'textdomain'),
        'view_item' => __('View Course', 'textdomain'),
        'view_items' => __('View Courses', 'textdomain'),
        'search_items' => __('Search Course', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into course', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this course', 'textdomain'),
        'items_list' => __('Courses list', 'textdomain'),
        'items_list_navigation' => __('Courses list navigation', 'textdomain'),
        'filter_items_list' => __('Filter courses list', 'textdomain'),
    );

    $args = array(
        'label' => __('Course', 'textdomain'),
        'description' => __('A post type for courses', 'textdomain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies' => array('course_category', 'course_venue', 'course_partner'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => false, // Disable Gutenberg
    );

    register_post_type('course', $args);
}

add_action('init', 'square_courses_register_post_types', 0);



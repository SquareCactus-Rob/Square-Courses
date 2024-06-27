<?php

// Register the settings
function square_courses_register_settings() {
    register_setting('square_courses_options_group', 'square_courses_admin_email');
    register_setting('square_courses_options_group', 'square_courses_confirmation_email_subject');
    register_setting('square_courses_options_group', 'square_courses_confirmation_email_body');
}
add_action('admin_init', 'square_courses_register_settings');

// Register the options page
function square_courses_register_options_page() {
    add_submenu_page(
        'edit.php?post_type=course',
        'Square Courses Settings',
        'Settings',
        'manage_options',
        'square_courses',
        'square_courses_options_page'
    );
}
add_action('admin_menu', 'square_courses_register_options_page');

// Settings page content
function square_courses_options_page() {
    ?>
    <div>
        <h2>Square Courses Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('square_courses_options_group'); ?>
            <?php do_settings_sections('square_courses'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add the new submenu for email notifications
add_action('admin_menu', 'square_courses_admin_menu');

// Create or update notification form handler
add_action('admin_post_square_courses_handle_notification_form', 'square_courses_handle_notification_form');

// Render the notification form
if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')) {
    square_courses_notification_form($_GET['action'], isset($_GET['id']) ? intval($_GET['id']) : null);
} else {
    // Display the notification list
    square_courses_notifications_page();
}

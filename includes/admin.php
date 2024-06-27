<?php
function register_attendees_page() {
    add_menu_page(
        'Attendees',
        'Attendees',
        'manage_options',
        'attendees',
        'display_attendees_page',
        'dashicons-welcome-learn-more',
        6
    );

    add_submenu_page(
        null,
        'Edit Attendee',
        'Edit Attendee',
        'manage_options',
        'edit_attendee',
        'display_edit_attendee_page'
    );
}
add_action('admin_menu', 'register_attendees_page');

// Include the attendees.php file
require_once plugin_dir_path(__FILE__) . 'attendees.php';

// Add the edit attendee page
add_action('admin_menu', function() {
    if (isset($_GET['page']) && $_GET['page'] == 'edit_attendee') {
        include plugin_dir_path(__FILE__) . 'edit-attendee.php';
    }
});


function square_courses_admin_menu() {
    add_menu_page(
        'Square Courses',
        'Square Courses',
        'manage_options',
        'square_courses',
        'square_courses_admin_page',
        'dashicons-welcome-learn-more',
        6
    );

    add_submenu_page(
        'square_courses',
        'Email Notifications',
        'Email Notifications',
        'manage_options',
        'square_courses_notifications',
        'square_courses_notifications_page'
    );
}
add_action('admin_menu', 'square_courses_admin_menu');

function square_courses_notifications_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'square_courses_notifications';

    // Fetch notifications
    $notifications = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h1>Email Notifications</h1>';
    echo '<a href="?page=square_courses_notifications&action=add" class="page-title-action">Add New Notification</a>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ($notifications as $notification) {
        echo '<tr>';
        echo '<td>' . $notification->id . '</td>';
        echo '<td>' . $notification->name . '</td>';
        echo '<td>' . ($notification->status ? 'Enabled' : 'Disabled') . '</td>';
        echo '<td><a href="?page=square_courses_notifications&action=edit&id=' . $notification->id . '">Edit</a> | <a href="?page=square_courses_notifications&action=delete&id=' . $notification->id . '">Delete</a></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

function square_courses_notification_form($action, $id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'square_courses_notifications';

    if ($action === 'edit' && $id) {
        $notification = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    } else {
        $notification = (object) [
            'name' => '',
            'send_to' => '',
            'subject' => '',
            'body' => '',
            'status' => 1,
        ];
    }

    echo '<div class="wrap">';
    echo '<h1>' . ucfirst($action) . ' Notification</h1>';
    echo '<form method="post" action="">';
    wp_nonce_field('square_courses_notification_form', 'square_courses_notification_nonce');
    echo '<table class="form-table">';
    echo '<tr><th><label for="name">Name</label></th><td><input type="text" name="name" value="' . esc_attr($notification->name) . '" required /></td></tr>';
    echo '<tr><th><label for="send_to">Send To</label></th><td><input type="text" name="send_to" value="' . esc_attr($notification->send_to) . '" required /></td></tr>';
    echo '<tr><th><label for="subject">Subject</label></th><td><input type="text" name="subject" value="' . esc_attr($notification->subject) . '" required /></td></tr>';
    echo '<tr><th><label for="body">Body</label></th><td><textarea name="body" rows="10" cols="50" required>' . esc_textarea($notification->body) . '</textarea></td></tr>';
    echo '<tr><th><label for="status">Status</label></th><td><input type="checkbox" name="status" value="1" ' . checked($notification->status, 1, false) . ' /></td></tr>';
    echo '</table>';
    submit_button('Save Notification');
    echo '</form>';
    echo '</div>';
}

// Handle form submission
function square_courses_handle_notification_form() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'square_courses_notifications';

    if (isset($_POST['square_courses_notification_nonce']) && wp_verify_nonce($_POST['square_courses_notification_nonce'], 'square_courses_notification_form')) {
        $name = sanitize_text_field($_POST['name']);
        $send_to = sanitize_text_field($_POST['send_to']);
        $subject = sanitize_text_field($_POST['subject']);
        $body = sanitize_textarea_field($_POST['body']);
        $status = isset($_POST['status']) ? 1 : 0;

        if ($_GET['action'] === 'add') {
            $wpdb->insert($table_name, [
                'name' => $name,
                'send_to' => $send_to,
                'subject' => $subject,
                'body' => $body,
                'status' => $status,
            ]);
        } elseif ($_GET['action'] === 'edit' && isset($_GET['id'])) {
            $wpdb->update($table_name, [
                'name' => $name,
                'send_to' => $send_to,
                'subject' => $subject,
                'body' => $body,
                'status' => $status,
            ], ['id' => intval($_GET['id'])]);
        }

        wp_redirect(admin_url('admin.php?page=square_courses_notifications'));
        exit;
    }
}
add_action('admin_post_square_courses_handle_notification_form', 'square_courses_handle_notification_form');

?>
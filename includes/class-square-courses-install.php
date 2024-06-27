<?php
class Square_Courses_Install {
    public static function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_bookings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id mediumint(9) NOT NULL,
            registration_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function square_courses_create_notifications_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'square_courses_notifications';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        send_to text NOT NULL,
        subject text NOT NULL,
        body text NOT NULL,
        status tinyint(1) DEFAULT 1 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'square_courses_create_notifications_table');

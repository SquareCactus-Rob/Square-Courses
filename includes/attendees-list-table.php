<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Attendees_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('Attendee', 'sp'),
            'plural'   => __('Attendees', 'sp'),
            'ajax'     => false
        ]);
    }

    public static function get_attendees($per_page = 5, $page_number = 1) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}course_bookings";
        if (!empty($_POST['s'])) {
            $sql .= ' WHERE full_name LIKE \'%' . esc_sql($_POST['s']) . '%\'';
            $sql .= ' OR email LIKE \'%' . esc_sql($_POST['s']) . '%\'';
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    public static function delete_attendee($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}course_bookings", ['id' => $id], ['%d']);
    }

    public static function record_count() {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}course_bookings";
        return $wpdb->get_var($sql);
    }

    public function no_items() {
        _e('No attendees available.', 'sp');
    }

    function column_name($item) {
        $delete_nonce = wp_create_nonce('sp_delete_attendee');
        $title = '<strong>' . $item['full_name'] . '</strong>';
        $actions = [
            'edit' => sprintf('<a href="?page=%s&action=%s&attendee=%s">Edit</a>', 'attendees', 'edit_attendee', $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=%s&attendee=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
        ];
        return $title . $this->row_actions($actions);
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'email':
                return $item['email'];
            case 'course_id':
                return $item['course_id'];
            case 'registration_date':
                return $item['registration_date'];
            default:
                return print_r($item, true);
        }
    }

    function get_columns() {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'full_name' => __('Full Name', 'sp'),
            'email' => __('Email', 'sp'),
            'course_id' => __('Course ID', 'sp'),
            'registration_date' => __('Registration Date', 'sp')
        ];
        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = [
            'full_name' => ['full_name', true],
            'email' => ['email', true],
            'course_id' => ['course_id', true],
            'registration_date' => ['registration_date', true]
        ];
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete'
        ];
        return $actions;
    }

    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page('attendees_per_page', 5);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);
        $this->items = self::get_attendees($per_page, $current_page);
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    public function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'sp_delete_attendee')) {
                die('Go get a life script kiddies');
            } else {
                self::delete_attendee(absint($_GET['attendee']));
                wp_redirect(esc_url(add_query_arg()));
                exit;
            }
        }
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')) {
            $delete_ids = esc_sql($_POST['bulk-delete']);
            foreach ($delete_ids as $id) {
                self::delete_attendee($id);
            }
            wp_redirect(esc_url(add_query_arg()));
            exit;
        }
    }
}
?>

<?php
// Ensure this file is only included once
if (!class_exists('Attendees_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

    class Attendees_List_Table extends WP_List_Table {
        public function get_columns() {
            $columns = [
                'cb' => '<input type="checkbox" />',
                'full_name' => 'Full Name',
                'email' => 'Email',
                'course_id' => 'Course ID',
                'course_name' => 'Course Name',
                'registration_date' => 'Registration Date'
            ];
            return $columns;
        }

        public function prepare_items() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'course_bookings'; // Course bookings table
            $courses_table = $wpdb->prefix . 'posts'; // Courses table

            $query = "
                SELECT cb.*, p.post_title as course_name
                FROM $table_name cb
                LEFT JOIN $courses_table p ON cb.course_id = p.ID
                WHERE p.post_type = 'course' AND p.post_status = 'publish'
            ";
            $items = $wpdb->get_results($query);

            $columns = $this->get_columns();
            $hidden = [];
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = [$columns, $hidden, $sortable];

            $this->items = $items;
        }

        public function column_default($item, $column_name) {
            switch ($column_name) {
                case 'full_name':
                case 'email':
                case 'course_id':
                case 'course_name':
                case 'registration_date':
                    return $item->$column_name;
                default:
                    return print_r($item, true); // Show the whole array for troubleshooting purposes
            }
        }

        public function column_full_name($item) {
            $edit_url = add_query_arg([
                'page' => 'edit_attendee',
                'attendee' => $item->id
            ], admin_url('admin.php'));

            $delete_url = add_query_arg([
                'page' => 'attendees',
                'action' => 'delete',
                'attendee' => $item->id
            ], admin_url('admin.php'));

            return sprintf(
                '<strong><a href="%s">%s</a></strong><br><a href="%s">Edit</a> | <a href="%s">Delete</a>',
                esc_url($edit_url),
                esc_html($item->full_name),
                esc_url($edit_url),
                esc_url($delete_url)
            );
        }

        public function get_sortable_columns() {
            $sortable_columns = [
                'full_name' => ['full_name', true],
                'email' => ['email', true],
                'course_id' => ['course_id', true],
                'course_name' => ['course_name', true],
                'registration_date' => ['registration_date', true]
            ];
            return $sortable_columns;
        }

        public function get_bulk_actions() {
            $actions = [
                'delete' => 'Delete',
            ];
            return $actions;
        }
    }
}

function display_attendees_page() {
    $attendees_list_table = new Attendees_List_Table();
    $attendees_list_table->prepare_items();
    ?>
    <div class="wrap">
        <h2>Attendees</h2>
        <form method="post">
            <?php
            $attendees_list_table->display();
            ?>
        </form>
    </div>
    <?php
}
?>

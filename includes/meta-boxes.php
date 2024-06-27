<?php
// Add Meta Boxes
function square_courses_add_meta_boxes() {
    add_meta_box(
        'square_courses_details',
        __('Course Details', 'textdomain'),
        'square_courses_render_meta_box',
        'course',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'square_courses_add_meta_boxes');

// Render Meta Box Content
function square_courses_render_meta_box($post) {
    // Add nonce for security and authentication
    wp_nonce_field('square_courses_nonce_action', 'square_courses_nonce');

    // Retrieve existing values from the database
    $num_sessions = get_post_meta($post->ID, '_square_courses_num_sessions', true);
    $schedule_type = get_post_meta($post->ID, '_square_courses_schedule_type', true);
    $start_date = get_post_meta($post->ID, '_square_courses_start_date', true);
    $end_date = get_post_meta($post->ID, '_square_courses_end_date', true);
    $custom_dates = get_post_meta($post->ID, '_square_courses_custom_dates', true);
    $contact_details = get_post_meta($post->ID, '_square_courses_contact_details', true);
    $associate_partner = get_post_meta($post->ID, '_square_courses_associate_partner', true);
    $course_id = get_post_meta($post->ID, '_square_courses_course_id', true);
    $start_time = get_post_meta($post->ID, '_square_courses_start_time', true);
    $end_time = get_post_meta($post->ID, '_square_courses_end_time', true);
    $venue = get_post_meta($post->ID, '_square_courses_location', true);
    $max_attendees = get_post_meta($post->ID, '_square_courses_max_attendees', true);
    $availability_status = get_post_meta($post->ID, '_square_courses_availability_status', true);

    // Initialize $venue_contact_details
    $venue_contact_details = '';

    // Get contact details for the selected venue
    if (!empty($venue) && !is_wp_error($venue)) {
        $venue_contact_details = get_term_meta($venue, 'venue_contact', true);
    }

    ?>
    <p>
        <label for="square_courses_num_sessions"><?php _e('Number of Course Sessions:', 'textdomain'); ?></label>
        <input type="number" id="square_courses_num_sessions" name="square_courses_num_sessions" value="<?php echo esc_attr($num_sessions); ?>" min="1" />
    </p>
    <p>
        <label for="square_courses_max_attendees"><?php _e('Maximum Attendees:', 'textdomain'); ?></label>
        <input type="number" id="square_courses_max_attendees" name="square_courses_max_attendees" value="<?php echo esc_attr($max_attendees); ?>" min="1" />
    </p>
    <p>
        <label for="square_courses_schedule_type"><?php _e('Schedule Type:', 'textdomain'); ?></label>
        <select id="square_courses_schedule_type" name="square_courses_schedule_type">
            <option value="one-time" <?php selected($schedule_type, 'one-time'); ?>><?php _e('One-Time', 'textdomain'); ?></option>
            <option value="daily" <?php selected($schedule_type, 'daily'); ?>><?php _e('Daily', 'textdomain'); ?></option>
            <option value="weekly" <?php selected($schedule_type, 'weekly'); ?>><?php _e('Weekly', 'textdomain'); ?></option>
            <option value="monthly" <?php selected($schedule_type, 'monthly'); ?>><?php _e('Monthly', 'textdomain'); ?></option>
            <option value="custom" <?php selected($schedule_type, 'custom'); ?>><?php _e('Custom', 'textdomain'); ?></option>
        </select>
    </p>
    <div id="one-time-fields" style="display: <?php echo ($schedule_type == 'one-time') ? 'block' : 'none'; ?>;">
        <p>
            <label for="square_courses_start_date"><?php _e('Date:', 'textdomain'); ?></label>
            <input type="date" id="square_courses_start_date" name="square_courses_start_date" value="<?php echo esc_attr($start_date); ?>" />
        </p>
        <p>
            <label for="square_courses_start_time"><?php _e('Start Time:', 'textdomain'); ?></label>
            <input type="time" id="square_courses_start_time" name="square_courses_start_time" value="<?php echo esc_attr($start_time); ?>" />
        </p>
        <p>
            <label for="square_courses_end_time"><?php _e('End Time:', 'textdomain'); ?></label>
            <input type="time" id="square_courses_end_time" name="square_courses_end_time" value="<?php echo esc_attr($end_time); ?>" />
        </p>
    </div>
    <div id="recurring-fields" style="display: <?php echo ($schedule_type == 'one-time' || $schedule_type == 'custom') ? 'none' : 'block'; ?>;">
        <p>
            <label for="square_courses_start_date"><?php _e('Start Date:', 'textdomain'); ?></label>
            <input type="date" id="square_courses_start_date" name="square_courses_start_date" value="<?php echo esc_attr($start_date); ?>" />
        </p>
        <p>
            <label for="square_courses_end_date"><?php _e('End Date:', 'textdomain'); ?></label>
            <input type="date" id="square_courses_end_date" name="square_courses_end_date" value="<?php echo esc_attr($end_date); ?>" />
        </p>
        <p>
            <label for="square_courses_start_time"><?php _e('Start Time:', 'textdomain'); ?></label>
            <input type="time" id="square_courses_start_time" name="square_courses_start_time" value="<?php echo esc_attr($start_time); ?>" />
        </p>
        <p>
            <label for="square_courses_end_time"><?php _e('End Time:', 'textdomain'); ?></label>
            <input type="time" id="square_courses_end_time" name="square_courses_end_time" value="<?php echo esc_attr($end_time); ?>" />
        </p>
    </div>
    <div id="custom-fields" style="display: <?php echo ($schedule_type == 'custom') ? 'block' : 'none'; ?>;">
        <p><?php _e('Custom Dates and Times:', 'textdomain'); ?></p>
        <div id="custom-dates-container">
            <?php
            if (!empty($custom_dates)) {
                $custom_dates = unserialize($custom_dates);
                foreach ($custom_dates as $index => $custom_date) {
                    ?>
                    <div class="custom-date-time">
                        <label for="custom_date_<?php echo $index; ?>"><?php _e('Date:', 'textdomain'); ?></label>
                        <input type="date" id="custom_date_<?php echo $index; ?>" name="custom_dates[<?php echo $index; ?>][date]" value="<?php echo esc_attr($custom_date['date']); ?>" />
                        <label for="custom_start_time_<?php echo $index; ?>"><?php _e('Start Time:', 'textdomain'); ?></label>
                        <input type="time" id="custom_start_time_<?php echo $index; ?>" name="custom_dates[<?php echo $index; ?>][start_time]" value="<?php echo esc_attr($custom_date['start_time']); ?>" />
                        <label for="custom_end_time_<?php echo $index; ?>"><?php _e('End Time:', 'textdomain'); ?></label>
                        <input type="time" id="custom_end_time_<?php echo $index; ?>" name="custom_dates[<?php echo $index; ?>][end_time]" value="<?php echo esc_attr($custom_date['end_time']); ?>" />
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <button type="button" id="add-custom-date"><?php _e('Add Another Date', 'textdomain'); ?></button>
    </div>
    <p>
        <label for="square_courses_contact_details"><?php _e('Contact Details:', 'textdomain'); ?></label>
        <textarea id="square_courses_contact_details" name="square_courses_contact_details"><?php echo esc_textarea($contact_details); ?></textarea>
    </p>
    <p>
        <label for="square_courses_associate_partner"><?php _e('Associate Partner:', 'textdomain'); ?></label>
        <?php
        $partners = get_terms(array('taxonomy' => 'course_partner', 'hide_empty' => false));
        ?>
        <select id="square_courses_associate_partner" name="square_courses_associate_partner">
            <option value=""><?php _e('Select Partner', 'textdomain'); ?></option>
            <?php foreach ($partners as $partner) { ?>
                <option value="<?php echo esc_attr($partner->term_id); ?>" <?php selected($associate_partner, $partner->term_id); ?>><?php echo esc_html($partner->name); ?></option>
            <?php } ?>
        </select>
    </p>
    <p>
        <label for="square_courses_course_id"><?php _e('Course ID:', 'textdomain'); ?></label>
        <input type="text" id="square_courses_course_id" name="square_courses_course_id" value="<?php echo esc_attr($course_id); ?>" />
    </p>
    <p>
        <label for="square_courses_location"><?php _e('Location:', 'textdomain'); ?></label>
        <?php
        $venues = get_terms(array('taxonomy' => 'course_venue', 'hide_empty' => false));
        ?>
        <select id="square_courses_location" name="square_courses_location">
            <option value=""><?php _e('Select Venue', 'textdomain'); ?></option>
            <?php foreach ($venues as $venue_item) { ?>
                <option value="<?php echo esc_attr($venue_item->term_id); ?>" <?php selected($venue, $venue_item->term_id); ?>><?php echo esc_html($venue_item->name); ?></option>
            <?php } ?>
        </select>
    </p>
    <p>
        <label for="square_courses_availability_status"><?php _e('Availability Status:', 'textdomain'); ?></label>
        <select id="square_courses_availability_status" name="square_courses_availability_status">
            <option value="available" <?php selected($availability_status, 'available'); ?>><?php _e('Available', 'textdomain'); ?></option>
            <option value="limited" <?php selected($availability_status, 'limited'); ?>><?php _e('Limited Availability', 'textdomain'); ?></option>
            <option value="fully_booked" <?php selected($availability_status, 'fully_booked'); ?>><?php _e('Fully Booked', 'textdomain'); ?></option>
        </select>
    </p>
    <?php
}

// Save Meta Box Data
function square_courses_save_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['square_courses_nonce'])) {
        return;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['square_courses_nonce'], 'square_courses_nonce_action')) {
        return;
    }

    // Check if the user has permission to save the data
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (wp_is_post_autosave($post_id)) {
        return;
    }

    // Check if not a revision
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Sanitize and save data
    if (isset($_POST['square_courses_num_sessions'])) {
        update_post_meta($post_id, '_square_courses_num_sessions', absint($_POST['square_courses_num_sessions']));
    }
    if (isset($_POST['square_courses_max_attendees'])) {
        update_post_meta($post_id, '_square_courses_max_attendees', absint($_POST['square_courses_max_attendees']));
    }
    if (isset($_POST['square_courses_schedule_type'])) {
        update_post_meta($post_id, '_square_courses_schedule_type', sanitize_text_field($_POST['square_courses_schedule_type']));
    }
    if (isset($_POST['square_courses_start_date'])) {
        update_post_meta($post_id, '_square_courses_start_date', sanitize_text_field($_POST['square_courses_start_date']));
    }
    if (isset($_POST['square_courses_end_date'])) {
        update_post_meta($post_id, '_square_courses_end_date', sanitize_text_field($_POST['square_courses_end_date']));
    }
    if (isset($_POST['square_courses_start_time'])) {
        update_post_meta($post_id, '_square_courses_start_time', sanitize_text_field($_POST['square_courses_start_time']));
    }
    if (isset($_POST['square_courses_end_time'])) {
        update_post_meta($post_id, '_square_courses_end_time', sanitize_text_field($_POST['square_courses_end_time']));
    }
    if (isset($_POST['square_courses_contact_details'])) {
        update_post_meta($post_id, '_square_courses_contact_details', sanitize_textarea_field($_POST['square_courses_contact_details']));
    }
    if (isset($_POST['square_courses_associate_partner'])) {
        update_post_meta($post_id, '_square_courses_associate_partner', sanitize_text_field($_POST['square_courses_associate_partner']));
    }
    if (isset($_POST['square_courses_course_id'])) {
        update_post_meta($post_id, '_square_courses_course_id', sanitize_text_field($_POST['square_courses_course_id']));
    }
    if (isset($_POST['square_courses_location'])) {
        update_post_meta($post_id, '_square_courses_location', sanitize_text_field($_POST['square_courses_location']));
    }
    if (isset($_POST['custom_dates'])) {
        update_post_meta($post_id, '_square_courses_custom_dates', serialize($_POST['custom_dates']));
    }
    if (isset($_POST['square_courses_availability_status'])) {
        update_post_meta($post_id, '_square_courses_availability_status', sanitize_text_field($_POST['square_courses_availability_status']));
    }
}
add_action('save_post', 'square_courses_save_meta_box_data');

// Remove additional category box for venue
function remove_default_taxonomy_boxes() {
    remove_meta_box('course_venuediv', 'course', 'side');
}
add_action('admin_menu', 'remove_default_taxonomy_boxes');
?>

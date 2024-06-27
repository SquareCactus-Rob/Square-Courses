<?php
// Register Custom Taxonomy for Categories
function square_courses_register_taxonomies() {
    $labels = array(
        'name' => _x('Categories', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Category', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Categories', 'textdomain'),
        'all_items' => __('All Categories', 'textdomain'),
        'parent_item' => __('Parent Category', 'textdomain'),
        'parent_item_colon' => __('Parent Category:', 'textdomain'),
        'edit_item' => __('Edit Category', 'textdomain'),
        'update_item' => __('Update Category', 'textdomain'),
        'add_new_item' => __('Add New Category', 'textdomain'),
        'new_item_name' => __('New Category Name', 'textdomain'),
        'menu_name' => __('Category', 'textdomain'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'course-category'),
    );

    register_taxonomy('course_category', array('course'), $args);

    // Register Custom Taxonomy for Venues
    $venue_labels = array(
        'name' => _x('Venues', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Venue', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Venues', 'textdomain'),
        'all_items' => __('All Venues', 'textdomain'),
        'parent_item' => __('Parent Venue', 'textdomain'),
        'parent_item_colon' => __('Parent Venue:', 'textdomain'),
        'edit_item' => __('Edit Venue', 'textdomain'),
        'update_item' => __('Update Venue', 'textdomain'),
        'add_new_item' => __('Add New Venue', 'textdomain'),
        'new_item_name' => __('New Venue Name', 'textdomain'),
        'menu_name' => __('Venue', 'textdomain'),
    );

    $venue_args = array(
        'hierarchical' => true,
        'labels' => $venue_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'course-venue'),
    );

    register_taxonomy('course_venue', array('course'), $venue_args);

    // Register Custom Taxonomy for Partners
    $partner_labels = array(
        'name' => _x('Partners', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Partner', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Partners', 'textdomain'),
        'all_items' => __('All Partners', 'textdomain'),
        'parent_item' => __('Parent Partner', 'textdomain'),
        'parent_item_colon' => __('Parent Partner:', 'textdomain'),
        'edit_item' => __('Edit Partner', 'textdomain'),
        'update_item' => __('Update Partner', 'textdomain'),
        'add_new_item' => __('Add New Partner', 'textdomain'),
        'new_item_name' => __('New Partner Name', 'textdomain'),
        'menu_name' => __('Partner', 'textdomain'),
    );

    $partner_args = array(
        'hierarchical' => false,
        'labels' => $partner_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'course-partner'),
    );

    register_taxonomy('course_partner', array('course'), $partner_args);
}

add_action('init', 'square_courses_register_taxonomies');

// Add colour picker to category
function square_courses_add_category_color_field($taxonomy) {
    ?>
    <div class="form-field">
        <label for="category_color"><?php _e('Category Colour', 'textdomain'); ?></label>
        <input type="text" name="category_color" id="category_color" value="" class="color-field">
        <p><?php _e('Choose a colour for this category.', 'textdomain'); ?></p>
    </div>
    <?php
}

add_action('course_category_add_form_fields', 'square_courses_add_category_color_field');

function square_courses_edit_category_color_field($term, $taxonomy) {
    $category_color = get_term_meta($term->term_id, 'category_color', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="category_color"><?php _e('Category Colour', 'textdomain'); ?></label></th>
        <td>
            <input type="text" name="category_color" id="category_color" value="<?php echo esc_attr($category_color); ?>" class="color-field">
            <p class="description"><?php _e('Choose a colour for this category.', 'textdomain'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('course_category_edit_form_fields', 'square_courses_edit_category_color_field', 10, 2);

function square_courses_save_category_color_meta($term_id) {
    if (isset($_POST['category_color'])) {
        update_term_meta($term_id, 'category_color', sanitize_hex_color($_POST['category_color']));
    }
}

add_action('created_course_category', 'square_courses_save_category_color_meta');
add_action('edited_course_category', 'square_courses_save_category_color_meta');

// Add address and contact details to venue taxonomy
function square_courses_add_venue_fields($taxonomy) {
    ?>
    <div class="form-field">
        <label for="venue_address"><?php _e('Address', 'textdomain'); ?></label>
        <input type="text" name="venue_address" id="venue_address" value="">
        <p><?php _e('Enter the address for this venue.', 'textdomain'); ?></p>
    </div>
    <div class="form-field">
        <label for="venue_contact"><?php _e('Contact Details', 'textdomain'); ?></label>
        <textarea name="venue_contact" id="venue_contact"></textarea>
        <p><?php _e('Enter the contact details for this venue.', 'textdomain'); ?></p>
    </div>
    <?php
}

add_action('course_venue_add_form_fields', 'square_courses_add_venue_fields');

function square_courses_edit_venue_fields($term, $taxonomy) {
    $address = get_term_meta($term->term_id, 'venue_address', true);
    $contact_details = get_term_meta($term->term_id, 'venue_contact', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="venue_address"><?php _e('Address', 'textdomain'); ?></label></th>
        <td>
            <input type="text" name="venue_address" id="venue_address" value="<?php echo esc_attr($address); ?>">
            <p class="description"><?php _e('Enter the address for this venue.', 'textdomain'); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="venue_contact"><?php _e('Contact Details', 'textdomain'); ?></label></th>
        <td>
            <textarea name="venue_contact" id="venue_contact"><?php echo esc_textarea($contact_details); ?></textarea>
            <p class="description"><?php _e('Enter the contact details for this venue.', 'textdomain'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('course_venue_edit_form_fields', 'square_courses_edit_venue_fields', 10, 2);

function square_courses_save_venue_meta($term_id) {
    if (isset($_POST['venue_address'])) {
        update_term_meta($term_id, 'venue_address', sanitize_text_field($_POST['venue_address']));
    }
    if (isset($_POST['venue_contact'])) {
        update_term_meta($term_id, 'venue_contact', sanitize_textarea_field($_POST['venue_contact']));
    }
}

add_action('created_course_venue', 'square_courses_save_venue_meta');
add_action('edited_course_venue', 'square_courses_save_venue_meta');

// Add URL and featured image to partner taxonomy
function square_courses_add_partner_fields($taxonomy) {
    ?>
    <div class="form-field">
        <label for="partner_url"><?php _e('Partner URL', 'textdomain'); ?></label>
        <input type="url" name="partner_url" id="partner_url" value="">
        <p><?php _e('Enter the URL for this partner.', 'textdomain'); ?></p>
    </div>
    <div class="form-field">
        <label for="partner_featured_image"><?php _e('Featured Image', 'textdomain'); ?></label>
        <input type="text" name="partner_featured_image" id="partner_featured_image" value="">
        <button type="button" class="button" id="partner_featured_image_button"><?php _e('Upload Image', 'textdomain'); ?></button>
        <p><?php _e('Choose an image for this partner.', 'textdomain'); ?></p>
    </div>
    <?php
}

add_action('course_partner_add_form_fields', 'square_courses_add_partner_fields');

function square_courses_edit_partner_fields($term, $taxonomy) {
    $partner_url = get_term_meta($term->term_id, 'partner_url', true);
    $featured_image = get_term_meta($term->term_id, 'partner_featured_image', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="partner_url"><?php _e('Partner URL', 'textdomain'); ?></label></th>
        <td>
            <input type="url" name="partner_url" id="partner_url" value="<?php echo esc_attr($partner_url); ?>">
            <p class="description"><?php _e('Enter the URL for this partner.', 'textdomain'); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="partner_featured_image"><?php _e('Featured Image', 'textdomain'); ?></label></th>
        <td>
            <input type="text" name="partner_featured_image" id="partner_featured_image" value="<?php echo esc_attr($featured_image); ?>">
            <button type="button" class="button" id="partner_featured_image_button"><?php _e('Upload Image', 'textdomain'); ?></button>
            <p class="description"><?php _e('Choose an image for this partner.', 'textdomain'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('course_partner_edit_form_fields', 'square_courses_edit_partner_fields', 10, 2);

function square_courses_save_partner_meta($term_id) {
    if (isset($_POST['partner_url'])) {
        update_term_meta($term_id, 'partner_url', esc_url_raw($_POST['partner_url']));
    }
    if (isset($_POST['partner_featured_image'])) {
        update_term_meta($term_id, 'partner_featured_image', esc_url_raw($_POST['partner_featured_image']));
    }
}

add_action('created_course_partner', 'square_courses_save_partner_meta');
add_action('edited_course_partner', 'square_courses_save_partner_meta');


function square_courses_save_partner_featured_image_meta($term_id) {
    if (isset($_POST['partner_featured_image'])) {
        update_term_meta($term_id, 'partner_featured_image', esc_url_raw($_POST['partner_featured_image']));
    }
}

add_action('created_course_partner', 'square_courses_save_partner_featured_image_meta');
add_action('edited_course_partner', 'square_courses_save_partner_featured_image_meta');

// Custom update messages for taxonomies
function square_courses_custom_term_updated_messages($messages) {
    $messages['course_category'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __('Category added.', 'textdomain'),
        2 => __('Category deleted.', 'textdomain'),
        3 => __('Category updated.', 'textdomain'),
        4 => __('Category not added.', 'textdomain'),
        5 => __('Category not updated.', 'textdomain'),
        6 => __('Categories deleted.', 'textdomain'),
    );

    $messages['course_venue'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __('Venue added.', 'textdomain'),
        2 => __('Venue deleted.', 'textdomain'),
        3 => __('Venue updated.', 'textdomain'),
        4 => __('Venue not added.', 'textdomain'),
        5 => __('Venue not updated.', 'textdomain'),
        6 => __('Venues deleted.', 'textdomain'),
    );

    $messages['course_partner'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __('Partner added.', 'textdomain'),
        2 => __('Partner deleted.', 'textdomain'),
        3 => __('Partner updated.', 'textdomain'),
        4 => __('Partner not added.', 'textdomain'),
        5 => __('Partner not updated.', 'textdomain'),
        6 => __('Partners deleted.', 'textdomain'),
    );

    return $messages;
}

add_filter('term_updated_messages', 'square_courses_custom_term_updated_messages');


?>

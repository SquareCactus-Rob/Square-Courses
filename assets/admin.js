jQuery(document).ready(function ($) {
    // Initialize color picker
    $('.color-field').wpColorPicker();

    function toggleFields() {
        var scheduleType = $('#square_courses_schedule_type').val();
        $('#one-time-fields').toggle(scheduleType === 'one-time');
        $('#recurring-fields').toggle(scheduleType !== 'one-time' && scheduleType !== 'custom');
        $('#custom-fields').toggle(scheduleType === 'custom');
    }

    $('#square_courses_schedule_type').change(function () {
        toggleFields();
    });

    toggleFields();

    $('#add-custom-date').click(function () {
        var index = $('#custom-dates-container .custom-date-time').length;
        $('#custom-dates-container').append(
            '<div class="custom-date-time">' +
            '<label for="custom_date_' + index + '">Date:</label>' +
            '<input type="date" id="custom_date_' + index + '" name="custom_dates[' + index + '][date]">' +
            '<label for="custom_start_time_' + index + '">Start Time:</label>' +
            '<input type="time" id="custom_start_time_' + index + '" name="custom_dates[' + index + '][start_time]">' +
            '<label for="custom_end_time_' + index + '">End Time:</label>' +
            '<input type="time" id="custom_end_time_' + index + '" name="custom_dates[' + index + '][end_time]">' +
            '</div>'
        );
    });

    // Media uploader for partner featured image
    var mediaUploader;

    $('#partner_featured_image_button').click(function (e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#partner_featured_image').val(attachment.url);
        });
        mediaUploader.open();
    });

    // Handle the modal login button click
    $('.login-to-book').click(function(e) {
        e.preventDefault();
        $('#loginModal').modal('show');
    });

    // All Courses Filter
    const filterDropdown = document.getElementById('course-category-filter');
    const courseItems = document.querySelectorAll('.course-item');

    filterDropdown.addEventListener('change', function () {
        const selectedCategory = this.value;

        courseItems.forEach(function (item) {
            if (selectedCategory === 'all' || item.getAttribute('data-category') === selectedCategory) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Add click event to course category links for filtering
    const categoryLinks = document.querySelectorAll('.course-category a');
    categoryLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const selectedCategory = this.getAttribute('data-category');
            filterDropdown.value = selectedCategory;

            courseItems.forEach(function (item) {
                if (item.getAttribute('data-category') === selectedCategory) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

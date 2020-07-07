<?php

function copy_requirements($src, $dst)
{

    // open the source directory
    $dir = opendir($src);

    // Make the destination directory if not exist
    @mkdir($dst);

    // Loop through the files in source directory
    while ($file = readdir($dir)) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {

                // Recursively calling custom copy function
                // for sub directory
                custom_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}

add_filter('caldera_forms_private_upload_directory', function ($directory, $field_id, $form_id, $transient_id) {
    do_action('gw_validate_login', false);
    // Get current user
    $user_data = apply_filters('gw_current_user_login', null);

    $directory = 'temp/'. $user_data->{'ID'} .'';

    return $directory;
}, 10, 4);

// Check first if user already submitted information
add_filter('caldera_forms_pre_render_form', function ($html, $entry_id, $form) {
    $styles = "<style>.cf2-file-listed{display:grid;grid-template-columns:75% 25%;background-color:#e7f3fe;padding:10px;border-radius:10px}.cf2-file-control{height:25px}.cf2-file-extra-data{margin-top:0!important}.cf2-list-files{padding:5px 0;display:grid;grid-row-gap:7px}.cf2-file-listed progress{display:none}button.btn.btn-block{border-radius:30px}.caldera-grid ul.cf2-list-files .cf2-file-listed .cf2-file-control button.cf2-file-remove:after{text-decoration:none}</style>";
    echo $styles;
    //change to your form ID here!
    if ('CF5ef930501b83d' == $form[ 'ID' ]) {
        $result = apply_filters('gw_validate_submitted_information', null);
        $remaining_slots = do_shortcode('[gw_current_course field="slots_number_available"]');
        if ($result) {
            //echo do_shortcode("[gw_applied_course field=\'course\']");
            return GWUtility::_gw_render_shortcode('<div class="caldera-grid"><div class="alert alert-success">
				        Your information has been successfully submitted. An enrollment officer will contact you through the phone number you provided.
				            [elementor-template id="627"]
				            [elementor-template id="617"]
				            </div></div>');
        }

        if ($remaining_slots < 1) {
            return '<div class="caldera-grid"><div class="alert alert-error">We are sorry there is no slots remaining. Please choose another course</div></div>';
        }
    }
    return $html;
}, 10, 3);

// Validate User Requirements
add_action('caldera_forms_submit_start', function (array $form, $process_id) {
    if ('CF5ef930501b83d' == $form[ 'ID' ]) {
        do_action('gw_validate_login', false);

        $raw_data = Caldera_Forms::get_submission_data($form);
        $course_field_id = Caldera_Forms_Field_Util::get_field_by_slug('course', $form)['ID'];
        $selected_course = $raw_data[$course_field_id];
        do_action('gw_validate_course_availability', $selected_course); // Do course availability validation
        $remaining_slots = do_shortcode('[gw_current_course field="slots_number_available"]');

        if (apply_filters('gw_validate_submitted_information', null)) {
            echo "<div class=\"alert alert-error\" >Sorry, you are only allowed to submit once. Please contact (admin@mail.com) for more info.</div>";
            die();
        }
        if ($remaining_slots < 1) {
            echo "<div class=\"alert alert-error\" >We are sorry there is no slots remaining. Please choose another course</div>";
            die();
        }
    }
}, 10, 2);

// Inject User Meta into User Entry
add_filter('caldera_forms_ajax_return', function ($out, $form) {
    if ('CF5ef930501b83d' == $form[ 'ID' ]) { // Inject User Meta
        //do_action('gw_validate_session');
        $entry_id = $out['data']['cf_id'];
        $entry = new Caldera_Forms_Entry($form, $entry_id);

        // Get current user
        $user_data = apply_filters('gw_current_user_login', null);

        // Get to modify fields
        $field_uid = $entry->get_field(Caldera_Forms_Field_Util::get_field_by_slug('uid', $form)['ID']);
        $field_examinee_number = $entry->get_field(Caldera_Forms_Field_Util::get_field_by_slug('examinee_number', $form)['ID']);

        $raw_data = Caldera_Forms::get_submission_data($form);

        $course_field_id = Caldera_Forms_Field_Util::get_field_by_slug('course', $form)['ID'];
        $selected_course = $raw_data[$course_field_id];

        $wp_upload_dir = wp_get_upload_dir()['basedir'];
        $temp_directory = $wp_upload_dir . '/temp/'. $user_data->{'ID'} .'';
        $new_directory = $wp_upload_dir . '/user-requirements/'. $user_data->{'ID'} .'';

        copy_requirements($temp_directory, $new_directory);

        $requirements_files = array_diff(scandir($new_directory), array('.', '..'));

        $course_id = apply_filters('gw_get_course_meta', $selected_course, 'get_the_ID', null);

        $entry_manager = new GWEntriesManager(null);
        $result = $entry_manager->request_course($user_data->{'ID'}, $course_id, json_encode($requirements_files));
    }

    // Render shortcode
    $out['html'] = GWUtility::_gw_render_shortcode($out['html']);

    return $out;
}, 10, 3);

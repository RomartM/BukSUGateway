<?php

// Check first if user already submitted information
add_filter( 'caldera_forms_pre_render_form', function( $html, $entry_id, $form ){
    //change to your form ID here!
    if( 'CF5eec114f6ed6c' == $form[ 'ID' ]  ){
        $result = apply_filters('gw_validate_submitted_information', null);
        $remaining_slots = do_shortcode('[gw_current_course field="slots_number_available"]');
        if( $result ){
            //echo do_shortcode("[gw_applied_course field=\'course\']");
            return GWUtility::_gw_render_shortcode('<div class="caldera-grid"><div class="alert alert-success">
				Your information has been successfully submitted. An enrollment officer will contact you through the phone number you provided.
				[elementor-template id="627"]
				[elementor-template id="617"]
				</div></div>');
        }

        if($remaining_slots < 1){
            return '<div class="caldera-grid"><div class="alert alert-error">We are sorry there is no slots remaining. Please choose another course</div></div>';
        }
    }
    return $html;
}, 10, 3 );

// Validate User Requirements
add_action( 'caldera_forms_submit_start', function( array $form, $process_id ) {
    if( 'CF5eec114f6ed6c' == $form[ 'ID' ] ){
        do_action('gw_validate_login', false);

        $raw_data = Caldera_Forms::get_submission_data( $form );
        $course_field_id = Caldera_Forms_Field_Util::get_field_by_slug('course', $form)['ID'];
        $selected_course = $raw_data[$course_field_id];
        do_action('gw_validate_course_availability', $selected_course); // Do course availability validation
        $remaining_slots = do_shortcode('[gw_current_course field="slots_number_available"]');

        if(apply_filters('gw_validate_submitted_information', null)){
            echo "<div class=\"alert alert-error\" >Sorry, you are only allowed to submit once. Please contact (admin@mail.com) for more info.</div>";
            die();
        }
        if($remaining_slots < 1){
            echo "<div class=\"alert alert-error\" >We are sorry there is no slots remaining. Please choose another course</div>";
            die();
        }
    }
}, 10, 2 );

// Inject User Meta into User Entry
add_filter('caldera_forms_ajax_return', function($out, $form){
    if( 'CF5eec114f6ed6c' == $form[ 'ID' ] ){ // Inject User Meta
        //do_action('gw_validate_session');
        $entry_id = $out['data']['cf_id'];
        $entry = new Caldera_Forms_Entry( $form, $entry_id );

        // Get current user
        $user_data = apply_filters('gw_current_user_login', null);

        // Get to modify fields
        $field_full_name = $entry->get_field(Caldera_Forms_Field_Util::get_field_by_slug('full_name', $form)['ID']);
        $field_examinee_number = $entry->get_field(Caldera_Forms_Field_Util::get_field_by_slug('examinee_number', $form)['ID']);
        $field_exam_score = $entry->get_field(Caldera_Forms_Field_Util::get_field_by_slug('exam_score', $form)['ID']);
        $field_status = $entry->get_field(Caldera_Forms_Field_Util::get_field_by_slug('status', $form)['ID']);

        //Change fields value
        $field_full_name->value = $user_data->{'FULL_NAME'};
        $field_examinee_number->value = $user_data->{'EXAMINEE_NO'};
        $field_exam_score->value = $user_data->{'PERCENT'};

        // Status types
        // Pending - On Student Request
        // Approved - On enrollment officer approved
        // Rejected - On enrollment officer qualification did not pass
        $field_status->value = 'pending';

        // Put modified field back in entry
        $entry->add_field( $field_full_name );
        $entry->add_field( $field_examinee_number );
        $entry->add_field( $field_exam_score );
        $entry->add_field( $field_status );

        // Save entry
        $entry_id = $entry->save();
    }

    // Render shortcode
    $out['html'] = GWUtility::_gw_render_shortcode($out['html']);

    return $out;
}, 10, 3);
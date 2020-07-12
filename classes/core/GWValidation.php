<?php

/**
 * Validation Class
 */
class GWValidationClass
{

  function __construct()
  {
    add_action('gw_validate_login', array( $this, 'validateLogin'), 10, 2);

    add_action('gw_validate_new_student_exam_status', array( $this, 'validateExamStatus'));
    add_action('gw_validate_new_student_course_availability', array( $this, 'validateCourseAvailability'));
    add_action('gw_validate_new_student_course_request', array( $this, 'validateNewStudentCourseRequestStatus'));

    add_action('gw_validate_old_student_course_request', array( $this, 'validateOldStudentCourseRequestStatus'));
    add_action('gw_validate_old_student_course_preference', array( $this, 'validateCoursePreference'));

    add_action('gw_validate_confirmation_status', array( $this, 'validateConfirmationStatus'));
  }

  // Auth

  public function validateLogin($redirect_if_success=true, $redirect_if_fail=true)
  {
    $login_status = apply_filters('gw_session_login_validate', null);
    if ($login_status) {
        if ($redirect_if_success) {
            GWUtility::_gw_redirect('pass_process', null, null);
        }
    } else {
        if ($redirect_if_fail) {
            GWUtility::_gw_redirect('login', null, null);
        }
    }
  }

  // New Student

  public function validateExamStatus($redirect_if_success=true)
  {
    $exam_status = apply_filters('gw_session_user_validate', function($raw){
      return $raw;
    });

    if ($exam_status["uobj"]["EXAM_STATUS"] == 'PASSED') {
        if ($redirect_if_success) {
            GWUtility::_gw_redirect('pass_success', null, null);
        }
    } else {
        GWUtility::_gw_redirect('pass_fail', null, null);
    }

  }

  public function validateCourseAvailability()
  {
    // code...
  }

  public function validateNewStudentCourseRequestStatus()
  {
    // code...
  }

  // Old Student

  public function validateOldStudentCourseRequestStatus()
  {
    // code...
  }

  public function validateCoursePreference()
  {
    $user_data = apply_filters('gw_session_login_validate', function($raw){
      return $raw;
    });

    $data_source = new GWDataTable();
    $result = $data_source->getOldStudentData($user_data["uid"]);
    $record = apply_filters( 'gw_course_get_by_shortname', $result->{'CURRENT_COURSE_ID'}, function($raw){ return $raw; } );

    if(!empty($record)){
      $course_slug = get_post($course_id)->post_name;
      $url_to_redirect = add_query_arg(array(
        'page' => 'pass_course_apply',
        'course' => $record['slug'],
    ), GWUtility::_gw_current_page_url(null));
      wp_redirect($url_to_redirect);
    }else{
      GWUtility::_gw_redirect('pass_courses');
    }
  }

  // Generic user
  public function validateConfirmationStatus()
  {
    // code...
  }
}

new GWValidationClass();

?>

<?php


class GWEntriesManager
{
    // $data_table_instance;
    // $current_enrollment_officer;

    public function __construct($enrollment_officer_id)
    {
      $this->data_table_instance = new GWDataTable();
      $this->current_enrollment_officer = $enrollment_officer_id;
    }

    public function get_exam_entries($status, $per_page, $current_page, $search=null){
      return $this->data_table_instance->getExamEntries($status, $per_page, $current_page, $search);
    }

    public function validate_entry($id, $status){
      global $wpdb;

      // Validation Log Format
      $logger = new GWDataTable();
      $logger->insertLog($id, 'validation', json_encode(
        array(
          "officer" =>  $this->current_enrollment_officer,
          "status"    =>  $status
        )
      ));

      $action = $wpdb-> update(
          $this->data_table_instance->exam_results_table_name,
          array( 'VALIDATION_OFFICER' => $this->current_enrollment_officer,
                 'VALIDATION_STATUS' => $status ),
          array( 'id' => $id ),
          array( '%s', '%s', '%s' )
      );
      return $action;
    }

    public function request_course($id, $course_id, $requirements_files){
      global $wpdb;

      // Validation Log Format
      $data_table = new GWDataTable();
      $data_table->insertLog($id, 'validation', json_encode(
        array(
          "course_id" =>  $course_id,
          "status"    =>  "pending"
        )
      ));

      $data_table->generateTC($id, $course_id);

      $action = $wpdb-> update(
          $this->data_table_instance->exam_results_table_name,
          array( 'REQUESTED_COURSE_ID'=> $course_id,
                 'VALIDATION_REQUIREMENTS' => $requirements_files,
                 'VALIDATION_STATUS' => 'pending' ),
          array( 'id' => $id ),
          array( '%s', '%s', '%s' )
      );
      return $action;
    }

    public function get_entry_information($unique_id){
      return $this->data_table_instance->getExamResultData($unique_id);
    }

    public function update_entry_email($id, $email_address){
      global $wpdb;

      $action = $wpdb-> update(
          $this->data_table_instance->exam_results_table_name,
          array( 'EMAIL_ADDRESS' => $email_address ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function update_entry_phone($id, $phone_number){
      global $wpdb;

      $action = $wpdb-> update(
          $this->data_table_instance->exam_results_table_name,
          array( 'CONTACT_NUMBER' => $phone_number ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function update_entry_address($id, $address){
      global $wpdb;

      $action = $wpdb-> update(
          $this->data_table_instance->exam_results_table_name,
          array( 'ADDRESS' => $address ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function update_entry_feedback($id, $feedback){
      global $wpdb;

      $action = $wpdb-> update(
          $this->data_table_instance->exam_results_table_name,
          array( 'VALIDATION_FEEDBACK' => $feedback ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

}

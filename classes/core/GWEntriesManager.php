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

    public function validate_entry($id, $status, $utype){
      global $wpdb;

      // Validation Log Format
      $logger = new GWDataTable();
      $logger->insertLog($id, 'validation', json_encode(
        array(
          "officer" =>  $this->current_enrollment_officer,
          "status"    =>  $status,
          "type"      =>  $utype
        )
      ));

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $action = $wpdb-> update(
          $table_name,
          array( 'VALIDATION_OFFICER' => $this->current_enrollment_officer,
                 'VALIDATION_STATUS' => $status ),
          array( 'id' => $id ),
          array( '%s', '%s', '%s' )
      );
      
      return $action;
    }

    public function request_course($id, $course_id, $requirements_files, $utype){
      global $wpdb;

      // Validation Log Format
      $data_table = new GWDataTable();
      $data_table->insertLog($id, 'validation', json_encode(
        array(
          "course_id" =>  $course_id,
          "status"    =>  "pending",
          "type"      =>  $utype
        )
      ));

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $data_table->generateTC($id, $course_id, $utype);
      $course_college_slug = apply_filters('gw_get_course_meta_id', $course_id, 'get_the_category', null)[0]->slug;

      $action = $wpdb-> update(
          $table_name,
          array( 'REQUESTED_COURSE_ID'=> $course_id,
                 'REQUESTED_COURSE_COLLEGE'=>$course_college_slug,
                 'VALIDATION_REQUIREMENTS' => $requirements_files,
                 'VALIDATION_STATUS' => 'pending' ),
          array( 'id' => $id ),
          array( '%s', '%s', '%s' )
      );
      return $action;
    }

    public function update_requirements($id, $requirements_files, $utype){
      global $wpdb;

      // Validation Log Format
      $data_table = new GWDataTable();
      $data_table->insertLog($id, 'validation', json_encode(
        array(
          "officer" =>  $this->current_enrollment_officer,
          "status"    =>  "auto_delete",
          "type"      =>  $utype
        )
      ));

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $action = $wpdb-> update(
          $table_name,
          array( 'VALIDATION_REQUIREMENTS' => $requirements_files ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function get_entry_information($unique_id){
      return $this->data_table_instance->getExamResultData($unique_id);
    }

    public function update_entry_email($id, $email_address, $utype){
      global $wpdb;

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $action = $wpdb-> update(
          $table_name,
          array( 'EMAIL_ADDRESS' => $email_address ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function update_entry_phone($id, $phone_number, $utype){
      global $wpdb;

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $action = $wpdb-> update(
          $table_name,
          array( 'CONTACT_NUMBER' => $phone_number ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function update_entry_address($id, $address, $utype){
      global $wpdb;

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $action = $wpdb-> update(
          $table_name,
          array( 'ADDRESS' => $address ),
          array( 'id' => $id ),
          array( '%s', '%s' )
      );
      return $action;
    }

    public function update_entry_feedback($id, $feedback, $utype){
      global $wpdb;

      $table_name = "";
      if($utype == "new"){
        $table_name = $this->data_table_instance->exam_results_table_name;
      }elseif ($utype == "old") {
        $table_name = $this->data_table_instance->old_student_table_name;
      }

      if(empty($table_name)){
        return;
      }

      $action = $wpdb-> update(
          $table_name,
          array(
                'VALIDATION_FEEDBACK' => $feedback,
                'VALIDATION_OFFICER' => $this->current_enrollment_officer
                ),
          array( 'id' => $id ),
          array( '%s', '%s', '%s' )
      );
      return $action;
    }

}

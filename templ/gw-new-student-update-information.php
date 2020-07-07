<?php

function _gw_get_submitted_files($uid, $validation_req_array){
  $field = json_decode($validation_req_array);
  $styles = "<style>ul.gw-submitted-files {padding: 0px 25px;list-style: decimal;}ul.gw-submitted-files a {color: #2196F3;}ul.gw-submitted-files li {padding: 3px;}</style>";
  echo $styles;

  if(empty($field)){
    return 0;
  }
  $file_lists = "<ul class=\"gw-submitted-files\">";
  foreach ($field as $key => $value) {
    $file_lists.="<li><a href=\"" . GWUtility::_gw_generate_file_url($uid, $value) . "\" target=\"_blank\">" . basename($value) .  "</a></li>";
  }
  $file_lists.= "</ul>";
  return $file_lists;
}

    list($get_url, $update_nonce, $action_url) = apply_filters('gw_form_meta', 'pass_enrollment_fill', false);


 ?>
<style>
.gw-form-group-wrapper {
    display: grid;
    grid-gap: 10px;
    grid-template-columns: 50% 50%;
    margin: 10px;
}

.gw-form-group {
    padding: 10px;
    background-color: #c5c5c53d;
    border-style: solid;
    border-color: #c2c2c2;
    border-width: 1px;
    border-radius: 5px;
}

.gw-form-group-title {
    font-size: 1.3em;
    font-weight: 500;
    color: #02407d;
}

.gw-form-input-group label {
    font-size: 12px;
}

.gw-form-input-group input {
    padding: 3px 10px;
    font-weight: 600;
    color: #424242;
}

.gw-form-input-group input[readonly], .gw-form-input-group textarea[readonly] {
    background-color: #BDBDBD;
}

.gw-form-group.gw-examinee-info .gw-form-input-group-wrapper {
    display: grid;
    grid-template-columns: auto auto;
    grid-gap: 10px;
}

.gw-form-group.gw-personal-info .gw-form-input-group-wrapper {
    display: grid;
    grid-template-columns: auto auto;
    grid-gap: 10px;
}

.gw-form-group.gw-pre-info .gw-form-input-group-wrapper {
  display: grid;
  grid-gap: 10px;
  grid-template-areas:
      'requested requested'
      'pref pref'
      'level status'
      'officer officer'
      'req req'
      'remarks remarks';
}

.gw-form-group.gw-school-info .gw-form-input-group-wrapper {
  display: grid;
  grid-gap: 10px;
  grid-template-areas:
      'lrn s_status'
      's_name s_name'
      's_address s_address'
      's_strand s_type';
}

.gw-form-radio-group {
    display: flex;
    padding: 3px;
}

.gw-form-radio-group label {
    margin-left: 3px;
    font-weight: 600;
    color: #474242;
}

.gw-form-radio-wrapper {
    display: grid;
    grid-template-columns: auto auto;
}

.gw-form-input-group.gw-i-email {
    grid-area: email;
}

.gw-form-input-group.gw-i-contact {
    grid-area: contact;
}

.gw-form-input-group.gw-i-address {
    grid-area: address;
}

.gw-form-input-group.gw-i-province {
    grid-area: province;
}

.gw-form-input-group.gw-i-zip {
    grid-area: zip;
}

.gw-form-input-group.gw-i-tcm {
    grid-area: tcm;
}

.gw-form-input-group.gw-i-brgy {
    grid-area: brgy;
}

.gw-form-input-group.gw-i-street {
    grid-area: street;
}

.gw-form-group.gw-contact-info .gw-form-input-group-wrapper {
  grid-template-areas:
          'email contact'
          'tcm tcm'
          'brgy street'
          'province zip'
          'address address';
      display: grid;
      grid-gap: 10px;
}

.gw-form-input-group.gw-i-requested {
    grid-area: requested;
}

.gw-form-input-group.gw-i-pref {
    grid-area: pref;
}

.gw-form-input-group.gw-i-level {
    grid-area: level;
}

.gw-form-input-group.gw-i-req {
    grid-area: req;
}

.gw-form-input-group.gw-i-status {
    grid-area: status;
}

.gw-form-input-group.gw-i-officer {
    grid-area: officer;
}

.gw-form-input-group.gw-i-remarks {
    grid-area: remarks;
}

.gw-form-input-group.gw-i-lrn {
    grid-area: lrn;
}

.gw-form-input-group.gw-i-s_status {
    grid-area: s_status;
}

.gw-form-input-group.gw-i-s_name {
    grid-area: s_name;
}

.gw-form-input-group.gw-i-s_address {
    grid-area: s_address;
}

.gw-form-input-group.gw-i-s_strand {
    grid-area: s_strand;
}

.gw-form-input-group.gw-i-s_type {
    grid-area: s_type;
}

.gw-form-action {
    margin-top: 30px;
    text-align: center;
}

.gw-form-action input {
    border-radius: 30px;
    border-style: none;
    background-color: #4CAF50;
    color: white;
    font-weight: 500;
    padding: 10px 30px;
}

.gw-form-action input:hover {
    background-color: #fcaa52;
}

.gw-input-required > label::after {
    content: "*";
    font-size: 14px;
    font-weight: 700;
    color: red;
}

.gw-form-action input:focus {
    background-color: #4CAF50;
}

div#modal-216b767 > .uael-content {
    border-radius: 10px;
    overflow: hidden;
}

</style>
<?php

$updated_count = get_option('gw_user_updated');
if(!empty($updated_count)){
  if($updated_count<1){
    $message = "No field(s) updated";
  }else{
    $message = "{$updated_count} field(s) updated";
  }
  update_option('gw_user_updated', 0);
  $data = array('type' => 'update', 'message'=>$message );
  do_action('gw_admin_notice', $data);
}

 ?>
<div class="gw-student-update-information-self gw-wrapper">
  <!-- Form -->
  <form class="main-info-form" method='post' action='<?php echo $action_url; ?>'>
    <input type="hidden" name="gw_student_update_info_self_nonce" value="<?php echo $update_nonce; ?>" />
    <input type="hidden" name="action" value="gw_student_update_info_self" />
    <input type="hidden" name="gw_student_uid" value="<?php echo $gw_user_info['exam_id'] ?>" />
    <div class="gw-form-group-wrapper">
      <div class="gw-form-group gw-examinee-info">
        <div class="gw-form-group-title">Examinee Information</div>
        <div class="gw-form-input-group-wrapper">
          <div class="gw-form-input-group">
            <label for="gwStudentExamineeNo">Examinee No.</label>
            <input type="text" id="gwStudentExamineeNo" name="gw_student_examinee_no" value="<?php echo $gw_user_info['EXAMINEE_NO'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group">
            <label for="gwStudentExaminationDate">Examination Date</label>
            <input type="text" id="gwStudentExaminationDate" name="gw_student_examination_date" value="<?php echo $gw_user_info['EXAMINATION_DATE'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group">
            <label for="gwStudentExaminationTime">Examination Time</label>
            <input type="text" id="gwStudentExaminationTime" name="gw_student_examination_time" value="<?php echo $gw_user_info['EXAMINATION_TIME'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group">
            <label for="gwStudentExamTotal">Exam Result Score</label>
            <input type="text" id="gwStudentExamTotal" name="gw_student_exam_score_total" value="<?php echo $gw_user_info['TOTAL'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group">
            <label for="gwStudentExamPercent">Exam Result Percentage</label>
            <input type="text" id="gwStudentExamPercent" name="gw_student_exam_percent" value="<?php echo $gw_user_info['PERCENT'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group">
            <label for="gwStudentExamStatus">Exam Result Status</label>
            <input type="text" id="gwStudentExamStatus" name="gw_student_exam_status" value="<?php echo $gw_user_info['EXAM_STATUS'] ?>" readonly/>
          </div>
        </div>
      </div>
      <div class="gw-form-group gw-personal-info">
        <div class="gw-form-group-title">Personal Information</div>
        <div class="gw-form-input-group-wrapper">
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudentLastName">Last Name</label>
            <input type="text" id="gwStudentLastName" name="gw_student_update[last_name]" value="<?php echo $gw_user_info['LAST_NAME'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudent">First Name</label>
            <input type="text" id="gwStudent" name="gw_student_update[first_name]" value="<?php echo $gw_user_info['FIRST_NAME'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudentFirstName">Middle Name</label>
            <input type="text" id="gwStudentFirstName" name="gw_student_update[middle_name]" value="<?php echo $gw_user_info['MIDDLE_NAME'] ?>" required/>
          </div>
          <div class="gw-form-input-group">
            <label for="gwStudentNameSuffix">Name Suffix</label>
            <input type="text" id="gwStudentNameSuffix" name="gw_student_update[name_suffix]" value="<?php echo $gw_user_info['NAME_SUFFIX'] ?>" />
          </div>
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudentBirthDate">Birth Date</label>
            <input type="date" id="gwStudentBirthDate" name="gw_student_update[birthdate]" value="<?php echo date_format(date_create($gw_user_info['BIRTHDATE']),"Y-m-d");?>" required/>
          </div>
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudentCitizenship">Citizenship</label>
            <input type="text" id="gwStudentCitizenship" name="gw_student_update[citizenship]" value="<?php echo $gw_user_info['CITIZENSHIP'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudentSex">Sex</label>
            <div class="gw-form-radio-wrapper">
              <div class="gw-form-radio-group">
                <input type="radio" id="gwStudentMale" name="gw_student_update[sex]" value="male" <?php echo strtolower($gw_user_info['SEX']) == 'male'? 'checked' : '' ?> required>
                <label for="gwStudentMale">Male</label>
              </div>
              <div class="gw-form-radio-group">
                <input type="radio" id="gwStudentFemale" name="gw_student_update[sex]" value="female" <?php echo strtolower($gw_user_info['SEX']) == 'female'? 'checked' : '' ?> required>
                <label for="gwStudentFemale">Female</label>
              </div>
            </div>
          </div>
          <div class="gw-form-input-group gw-input-required">
            <label for="gwStudent">Civil Status</label>
            <div class="gw-form-radio-wrapper">
              <div class="gw-form-radio-group">
                <input type="radio" id="gwStudentSingle" name="gw_student_update[civil_status]" value="single" <?php echo strtolower($gw_user_info['CIVIL_STATUS']) == 'single'? 'checked' : '' ?> required>
                <label for="gwStudentSingle">Single</label>
              </div>
              <div class="gw-form-radio-group">
                <input type="radio" id="gwStudentMerried" name="gw_student_update[civil_status]" value="merried" <?php echo strtolower($gw_user_info['CIVIL_STATUS']) == 'merried'? 'checked' : '' ?> required>
                <label for="gwStudentMerried">Merried</label>
              </div>
              <div class="gw-form-radio-group">
                <input type="radio" id="gwStudentWidowed" name="gw_student_update[civil_status]" value="widowed" <?php echo strtolower($gw_user_info['CIVIL_STATUS']) == 'widowed'? 'checked' : '' ?> required>
                <label for="gwStudentWidowed">Widowed</label>
              </div>
              <div class="gw-form-radio-group">
                <input type="radio" id="gwStudentDivorced" name="gw_student_update[civil_status]" value="divorced" <?php echo strtolower($gw_user_info['CIVIL_STATUS']) == 'divorced'? 'checked' : '' ?> required>
                <label for="gwStudentDivorced">Divorced</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="gw-form-group gw-pre-info">
        <div class="gw-form-group-title">Pre-enlistment Information</div>
        <div class="gw-form-input-group-wrapper">
          <div class="gw-form-input-group gw-i-requested">
            <label for="gwStudentRequestedCourse">Course Selected</label>
            <input type="text" id="gwStudentRequestedCourse" name="gw_student_requested_course" value="<?php echo apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'] , 'get_the_title', null); ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-pref">
            <label for="gwStudentCoursePref">Course Preference</label>
            <input type="text" id="gwStudentCoursePref" name="gw_student_course_pref" value="<?php echo ucwords(strtolower($gw_user_info['COURSE_PREF'])) ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-level">
            <label for="gwStudentDegreeLevel">Level</label>
            <input type="text" id="gwStudentDegreeLevel" name="gw_student_degree_level" value="<?php echo ucfirst($gw_user_info['DEGREE_LEVEL']) ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-req">
            <label for="gwStudentValidationReq">Validation Requirements Submitted</label>
            <?php echo _gw_get_submitted_files($user_data->{'ID'}, $gw_user_info['VALIDATION_REQUIREMENTS']); ?>
          </div>
          <div class="gw-form-input-group gw-i-status">
            <label for="gwStudentValidationStatus">Validation Status</label>
            <input type="text" id="gwStudentValidationStatus" name="gw_student_validation_status" value="<?php echo ucfirst($gw_user_info['VALIDATION_STATUS']) ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-officer">
            <label for="gwStudentValidationOfficer">Validation Officer</label>
            <input type="text" id="gwStudentValidationOfficer" name="gw_student_validation_officer" value="<?php echo GWUtility::_gw_get_user_display_name($gw_user_info['VALIDATION_OFFICER']) ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-remarks">
            <label for="gwStudentValidationFeedback">Validation Remarks</label>
            <textarea id="gwStudentValidationFeedback" name="gw_student_validation_feedback" rows="3" readonly><?php echo $gw_user_info['VALIDATION_FEEDBACK'] ?></textarea>
          </div>
        </div>
      </div>
      <div class="gw-form-group gw-contact-info">
        <div class="gw-form-group-title">Contact Information</div>
        <div class="gw-form-input-group-wrapper">
          <div class="gw-form-input-group gw-i-email gw-input-required">
            <label for="gwStudentEmailAddress">Email Address</label>
            <input type="email" id="gwStudentEmailAddress" name="gw_student_update[email_address]" value="<?php echo $gw_user_info['EMAIL_ADDRESS'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-i-contact gw-input-required">
            <label for="gwStudentContactNumber">Contact Number</label>
            <input type="tel" id="gwStudentContactNumber" name="gw_student_update[contact_number]" value="<?php echo $gw_user_info['CONTACT_NUMBER'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-i-address">
            <label for="gwStudentAddress">Address</label>
            <textarea id="gwStudentAddress" name="gw_student_update[address]" rows="5"><?php echo $gw_user_info['ADDRESS'] ?></textarea>
          </div>
          <div class="gw-form-input-group gw-i-province gw-input-required">
            <label for="gwStudentProvince">Province</label>
            <input type="text" id="gwStudentProvince" name="gw_student_update[province]" value="<?php echo $gw_user_info['PROVINCE'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-i-zip gw-input-required">
            <label for="gwStudentZIPCode">ZIP Code</label>
            <input type="number" id="gwStudentZIPCode" name="gw_student_update[zip_code]" value="<?php echo $gw_user_info['ZIP_CODE'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-i-tcm gw-input-required">
            <label for="gwStudentTCM">Town / City / Municipality</label>
            <input type="text" id="gwStudentTCM" name="gw_student_update[tcm]" value="<?php echo $gw_user_info['TCM'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-i-brgy gw-input-required">
            <label for="gwStudentBrgy">Barangay</label>
            <input type="text" id="gwStudentBrgy" name="gw_student_update[brgy]" value="<?php echo $gw_user_info['BRGY'] ?>" required/>
          </div>
          <div class="gw-form-input-group gw-i-street gw-input-required">
            <label for="gwStudentStreet">Street</label>
            <input type="text" id="gwStudentStreet" name="gw_student_update[street]" value="<?php echo $gw_user_info['STREET'] ?>" required/>
          </div>
        </div>
      </div>
      <div class="gw-form-group gw-school-info">
        <div class="gw-form-group-title">School Information</div>
        <div class="gw-form-input-group-wrapper">
          <div class="gw-form-input-group gw-i-lrn">
            <label for="gwStudentLRN">LRN</label>
            <input type="text" id="gwStudentLRN" name="gw_student_lrn" value="<?php echo $gw_user_info['LRN'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-s_status">
            <label for="gwStudentStatus">Status</label>
            <input type="text" id="gwStudentStatus" name="gw_student_status" value="<?php echo $gw_user_info['STATUS'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-s_name">
            <label for="gwStudentSchoolName">School Name</label>
            <input type="text" id="gwStudentSchoolName" name="gw_student_school_name" value="<?php echo $gw_user_info['SCHOOL_NAME'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-s_address">
            <label for="gwStudentSchoolAddress">School Address</label>
            <input type="text" id="gwStudentSchoolAddress" name="gw_student_school_addr" value="<?php echo $gw_user_info['SCHOOL_ADDR'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-s_strand">
            <label for="gwStudentSHSStrand">SHS Strand</label>
            <input type="text" id="gwStudentSHSStrand" name="gw_student_shs_strand" value="<?php echo $gw_user_info['SHS_STRAND'] ?>" readonly/>
          </div>
          <div class="gw-form-input-group gw-i-s_type">
            <label for="gwStudentSchoolType">School Type</label>
            <input type="text" id="gwStudentSchoolType" name="gw_student_school_type" value="<?php echo $gw_user_info['SCHOOL_TYPE'] ?>" readonly/>
          </div>
        </div>
      </div>
    </div>
    <div class="gw-form-action">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Confirm Credentials">
    </div>
  </form>
</div>

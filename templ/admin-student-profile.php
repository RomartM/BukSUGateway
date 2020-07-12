<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

if(!isset($_GET['id'])){
  do_action('gw_admin_notice_no_student');
}

$user_id = sanitize_text_field($_GET['id']);
$page_name = sanitize_text_field($_GET['page']);
$data_source = new GWDataTable();

if($page_name == "gw-pre-listing-old"){
  $gw_user_info = GWUtility::gw_object_to_array($data_source->getOldStudentData($user_id));
  $user_type = "old";
}else{
  $gw_user_info = $data_source->getExamResultData($user_id);
  $user_type = "new";
}


if(empty($gw_user_info)){
  do_action('gw_admin_notice_no_student');
}

function _gw_get_submitted_files($uid){
  $data_source = new GWDataTable();
  $page_name = sanitize_text_field($_GET['page']);

  if($page_name == "gw-pre-listing-old"){
    $result = GWUtility::gw_object_to_array($data_source->getOldStudentData($uid));
  }else{
    $result = $data_source->getExamResultData($uid);
  }

  $field = json_decode($result['VALIDATION_REQUIREMENTS']);

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

?>
<style>
.gw-action-cards {
  background-color: white;
  border-color: #0000001a;
  border-style: solid;
  border-width: .5px;
  border-radius: 5px;
  padding: 15px;
  position: relative;
}

.gw-action-card-title {
  font-size: 1.5em;
  margin-bottom: 15px;
  color: #263238;
}

span.gw-field-value {
  font-weight: 600;
}

.gw-wrapper {
  display: grid;
  grid-column-gap: 10px;
  grid-row-gap: 10px;
  grid-template-areas:
   "gw-student gw-contact <?php echo ($user_type == "new")? 'gw-exam' : 'gw-contact'?>"
   "gw-requested gw-validation gw-validation";
}

.gw-action-cards.gw-exam {
    grid-area: gw-exam;
}

.gw-action-cards.gw-student {
    grid-area: gw-student;
}

.gw-action-cards.gw-requested {
    grid-area: gw-requested;
}

.gw-action-cards.gw-contact {
    grid-area: gw-contact;
	display: flex;
    flex-direction: column;
}

.gw-action-cards.gw-validation {
    grid-area: gw-validation;
}

.gw-content-field {
  font-size: 1.1em;
}

.gw-student-profile {
  margin-top: 30px;
}

span.gw-field-value {
  float: right;
  text-transform: uppercase;
}

.gw-content-field {
    border-bottom-color: #00000052;
    border-bottom-style: dotted;
    border-bottom-width: 1px;
    margin-bottom: 9px;
    overflow: overlay;
}

.gw-action-card-action {
    margin-top: auto;
}

.gw-action-cards.gw-announcements {
    grid-column: 3;
    grid-row: 1 / span 2;
}

.gw-action-card-content {
    overflow: overlay;
}

.gw-action-cards.gw-exam-failed *, .gw-action-cards.gw-status-inactive * {
    pointer-events: none;
    filter: blur(2px) grayscale(1);
    -webkit-touch-callout: none;
    /* iOS Safari */
    -webkit-user-select: none;
    /* Safari */
    -khtml-user-select: none;
    /* Konqueror HTML */
    -moz-user-select: none;
    /* Old versions of Firefox */
    -ms-user-select: none;
    /* Internet Explorer/Edge */
    user-select: none;
    /* Non-prefixed version, currently
                                  supported by Chrome, Edge, Opera and Firefox */
}

.gw-action-cards.gw-exam-failed::before, .gw-action-cards.gw-status-inactive::before {
    font-size: 13px;
    font-weight: 600;
    height: 20px;
    text-align: center;
    position: absolute;
    top: 50%;
    left: 0;
    bottom: 50%;
    right: 0;
    margin: auto;
}

.gw-action-cards.gw-exam-failed::before {
	content: "Evaluation is not applicable due to exam status.";
}

.gw-action-cards.gw-status-inactive::before {
	content: "Inactive user can not be evaluated.";
}


.button.button-error {
    color: #880E4F;
    border-color: #880E4F;
}

.gw-form-input-group.gw-form-address {
    display: grid;
    padding: 10px 0;
}

.gw-form-input-group textarea {
    border-style: solid;
    border-width: 1px;
    border-color: #adadad;
}

.gw-repeater-lists {
    font-size: 20px;
}

.gw-repeater-lists p {
    margin-top: 5px;
}

</style>

<div class="gw-student-profile">
  <div class="gw-wrapper">
    <?php if($page_name == "gw-pre-listing-new"): ?>
    <div class="gw-action-cards gw-exam">
      <div class="gw-action-card-title">Examinee Information</div>
      <div class="gw-action-card-content">
        <div class="gw-content-field">
          <span class="gw-field-label">Status</span>
          <span class="gw-field-value"><?php echo $gw_user_info['EXAM_STATUS']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">No.</span>
          <span class="gw-field-value"><?php echo $gw_user_info['EXAMINEE_NO']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Date</span>
          <span class="gw-field-value"><?php echo $gw_user_info['EXAMINATION_DATE']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Time</span>
          <span class="gw-field-value"><?php echo $gw_user_info['EXAMINATION_TIME']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Degree</span>
          <span class="gw-field-value"><?php echo $gw_user_info['DEGREE_LEVEL']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Total</span>
          <span class="gw-field-value"><?php echo $gw_user_info['TOTAL']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Percent</span>
          <span class="gw-field-value"><?php echo $gw_user_info['PERCENT']; ?></span>
        </div>
      </div>
      <div class="gw-action-card-action"></div>
    </div> <!--- Exam Information --->
    <?php endif; ?>
    <div class="gw-action-cards gw-student">
      <div class="gw-action-card-title">Student Information</div>
      <div class="gw-action-card-content">
        <div class="gw-content-field">
          <span class="gw-field-label">ID Number</span>
          <span class="gw-field-value"><?php echo $gw_user_info['ID_NUMBER']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Last Name</span>
          <span class="gw-field-value"><?php echo $gw_user_info['LAST_NAME']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">First Name.</span>
          <span class="gw-field-value"><?php echo $gw_user_info['FIRST_NAME']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Middle Name</span>
          <span class="gw-field-value"><?php echo $gw_user_info['MIDDLE_NAME']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Name Suffix</span>
          <span class="gw-field-value"><?php echo $gw_user_info['NAME_SUFFIX']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Sex</span>
          <span class="gw-field-value"><?php echo $gw_user_info['SEX']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Birth Date</span>
          <span class="gw-field-value"><?php echo $gw_user_info['BIRTHDATE']; ?></span>
        </div>
      </div>
      <div class="gw-action-card-action"></div>
    </div> <!--- Student Information --->
  	<?php if(!empty($gw_user_info['VALIDATION_STATUS'])): ?>
    <div class="gw-action-cards gw-requested">
      <div class="gw-action-card-title">Requested Course Requirements</div>
      <div class="gw-action-card-content">
      	<?php 
      		$req_list = apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'], 'get_field', 'requirements');
        		
        		if(!empty($req_list)){
                	echo "<style>.gw-repeater-item h5 {margin-bottom: 0;margin-top: 10px;}</style>";
                	$lists_html = "<div class=\"gw-repeater-lists\">";
                	forEach($req_list as $item){
                    	$lists_html .= sprintf("<div class=\"gw-repeater-item\"><h5>%s</h5><div class=\"gw-repeater-desc\">%s</div></div>", $item["label"], $item["description"]);
                    }
                	echo $lists_html."</div>";
                }
      	?>
      </div>
    </div> <!--- Requested Course Information --->
  	<?php endif; ?>
    <div class="gw-action-cards gw-contact"> 
      <div class="gw-action-card-title">Contact Information</div>
      <div class="gw-action-card-content">
        <div class="gw-content-field">
          <span class="gw-field-label">Email Address</span>
          <span class="gw-field-value"><a href="mailto:<?php echo $gw_user_info['EMAIL_ADDRESS']; ?>"><?php echo $gw_user_info['EMAIL_ADDRESS']; ?></a></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Contact Number</span>
          <span class="gw-field-value"><a href="tel:<?php echo $gw_user_info['CONTACT_NUMBER']; ?>"><?php echo $gw_user_info['CONTACT_NUMBER']; ?></a></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Address</span>
          <span class="gw-field-value"><?php echo $gw_user_info['ADDRESS']; ?></span>
        </div>
      </div>
      <div class="gw-action-card-action">
        <?php echo sprintf('<a href="?page=%s&sub=%s&id=%s" class="button button-primary">Update</a>', $_REQUEST['page'], 'gw-student-update' , $gw_user_info['id']); ?>
      </div>
    </div> <!--- Contact Information --->
    <div class="gw-action-cards gw-validation <?php
      if($user_type == "new"){
        echo 'gw-exam-'.strtolower($gw_user_info['EXAM_STATUS']);
      }
      if(empty($gw_user_info['VALIDATION_STATUS'])){
        	echo ' gw-status-inactive';
      }
     ?>">
      <div class="gw-action-card-title">Validation Information</div>
      <div class="gw-action-card-content">
        <div class="gw-content-field">
          <span class="gw-field-label">Reference No.</span>
          <span class="gw-field-value"><?php echo $gw_user_info['REQUESTED_TRANSACTION_ID']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Requested Course</span>
          <span class="gw-field-value"><?php echo (!empty($gw_user_info['REQUESTED_COURSE_ID']))?apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'] , 'get_the_title', null): ''; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">College</span>
          <span class="gw-field-value"><?php echo (!empty($gw_user_info['REQUESTED_COURSE_ID']))? (!empty(apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'] , 'get_the_category', null)[0]->cat_name)? apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'] , 'get_the_category', null)[0]->cat_name : '') : ''; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Status</span>
          <span class="gw-field-value"><?php echo $gw_user_info['VALIDATION_STATUS']; ?></span>
        </div>
        <div class="gw-content-field">
          <span class="gw-field-label">Officer</span>
          <span class="gw-field-value"><?php echo GWUtility::_gw_get_user_display_name($gw_user_info['VALIDATION_OFFICER']); ?></span>
        </div>
        <?php if($gw_user_info['VALIDATION_STATUS'] == 'approved'): ?>
        <div class="gw-content-field">
          <span class="gw-field-label">Certificate of Registration</span>
          <?php
          $cor_file_name = "cor.pdf";
          $cor_link = GWUtility::_gw_generate_file_url($gw_user_info['id'], $cor_file_name)
           ?>
          <span class="gw-field-value"><a href="<?php echo $cor_link ?>" target="_blank">View COR</a></span>
        </div>
      <?php endif; ?>
        <div class="gw-content-field">
          <span class="gw-field-label">Evaluation Remarks</span>
          <span class="gw-field-value"><?php echo $gw_user_info['VALIDATION_FEEDBACK']; ?></span>
        </div>
      </div>
      <div class="gw-action-card-action">
        <?php if(empty($gw_user_info['VALIDATION_STATUS'])){ ?>
      	  <button class="button button-primary">Evaluate</button>
        <?php }else { ?>
          <button class="button button-primary" id="gw_validate">Evaluate</button>
        <?php } ?>
      </div>
    </div> <!--- Validation Information --->
  </div>
</div>

<?php if(!empty($gw_user_info['VALIDATION_STATUS'])): ?>
<!-- The modal / dialog box, hidden somewhere near the footer -->
<div id="gw-dialog" class="hidden" style="max-width:800px">
    <div class="dialog-content">
      <div class="gw-action-cards">
        <div class="gw-action-card-title">Student Request Information</div>
        <div class="gw-action-card-content">
          <div class="gw-content-field">
            <span class="gw-field-label">Course</span>
            <span class="gw-field-value"><?php echo (!empty($gw_user_info['REQUESTED_COURSE_ID']))?apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'] , 'get_the_title', null): ''; ?></span>
          </div>
          <div class="gw-content-field">
            <span class="gw-field-label">College</span>
            <span class="gw-field-value"><?php echo (!empty($gw_user_info['REQUESTED_COURSE_ID']))?apply_filters('gw_get_course_meta_id', $gw_user_info['REQUESTED_COURSE_ID'] , 'get_the_category', null)[0]->cat_name: ''; ?></span>
          </div>
          <!-- <div class="gw-content-field">
            <span class="gw-field-label">Date of Submission</span>
            <span class="gw-field-value"><?php //echo $gw_user_info['id']; ?></span>
          </div> -->
          <div class="gw-content-field" style="display:grid">
            <span class="gw-field-label">Files Submitted</span>
            <span class="gw-field-value"><?php echo _gw_get_submitted_files($gw_user_info['id']); ?></span>
          </div>
        </div>
        <div class="gw-action-card-action">
          <?php
          $secret = sprintf("sub=%s&id=%s", $_GET['sub'], $_GET['id']);
          // Get form meta data
          list($get_url, $update_nonce, $action_url) = apply_filters('gw_form_meta', 'gw-student-validate', true, $secret);

          if(isset($_GET['updated'])){
            $updated_count = sanitize_text_field($_GET['updated']);
            if($updated_count<1){
              $message = "No field(s) updated";
            }else{
              $message = "{$updated_count} field(s) updated";
            }
            $data = array('type' => 'update', 'message'=>$message );
            do_action('gw_admin_notice', $data);
          }
           ?>
          <form class="formValidate" method='post' action='<?php echo $action_url; ?>' enctype='multipart/form-data'>
              <input type="hidden" name="gw_request_validation_nonce" value="<?php echo $update_nonce; ?>"/>
              <input type="hidden" name="action" value="gw_request_validation"/>
              <input type="hidden" name="gw_student_uid" value="<?php echo $gw_user_info['id']; ?>"/>
              <input type="hidden" name="gw_student_typ" value="<?php echo $user_type; ?>"/>
              <div class="gw-form-contact">
                <div class="gw-form-input-group gw-form-address">
                  <label for="gwEnrollmentOfficerFeedback">Evaluation Remarks:</label>
                  <?php echo wp_editor( $gw_user_info['VALIDATION_FEEDBACK'], "gwEnrollmentOfficerFeedback", array(
                    'textarea_name'=>'gw_enrollment_officer_feedback',
                    'media_buttons' => false,
                    'quicktags' => false
                  ) ); ?>
                </div>
                <div class="gw-form-input-group">
                    <label for="GWUploadCOR">Certificate of Registration(COR):</label>
                    <input type="file" name="gw-upload-cor" id="GWUploadCOR" accept="application/pdf" <?php echo (strtolower($gw_user_info['VALIDATION_STATUS'])=='approved')? 'disabled':'required';?>>
                </div>
              </div>
              <div class="gw-form-action">
                <input type="submit" name="submit[approved]" id="submit" class="button button-primary" value="Approve Request" <?php echo (strtolower($gw_user_info['VALIDATION_STATUS'])=='approved')? 'disabled':'';?>>
                <input type="submit" name="submit[denied]" id="submit" class="button button-error" value="Deny Request" <?php echo (strtolower($gw_user_info['VALIDATION_STATUS'])=='denied')? 'disabled':'';?>>
                <input type="submit" name="submit[pending]" id="submit" class="button button-info" value="Update Remarks">
              </div>
          </form>
        </div>
      </div>
    </div>
</div>
<?php endif; ?>

<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

if(!isset($_GET['id'])){
  do_action('gw_admin_notice_no_student');
}

$page_name = sanitize_text_field($_GET['page']);
$user_id = sanitize_text_field($_GET['id']);
$data_source = new GWDataTable();
$user_type = "";

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

$secret = sprintf("sub=%s&id=%s", $_GET['sub'], $_GET['id']);

// Get form meta data
list($get_url, $update_nonce, $action_url) = apply_filters('gw_form_meta', 'gw-student-update', true, $secret);

?>
<style>
.gw-student-summary h5 {
    margin-top: 0px;
}

.gw-student-summary h4 {
    margin-bottom: 3px;
}
.gw-form-action {
    margin: 30px 0;
}
.gw-form-input-group {
    display: grid;
    padding: 5px 0;
}
.gw-form-contact {
    display: grid;
    grid-template-columns: 30% 40%;
    grid-column-gap: 10px;
    grid-row-gap: 10px;
}
.gw-form-input-group.gw-form-address {
    grid-column: 2;
    grid-row: 1 / span 2;
}

textarea#gwStudentAddress {
    border-style: solid;
    border-width: thin;
    border-color: #7e8993;
}
</style>
<div class="gw-student-summary">
    <h4><?php echo sprintf("%s %s %s %s", $gw_user_info['FIRST_NAME'], $gw_user_info['MIDDLE_NAME'], $gw_user_info['LAST_NAME'], $gw_user_info['NAME_SUFFIX']); ?></h4>
    <h5><?php
    if($page_name == "gw-pre-listing-old"){
      echo $gw_user_info['ID_NUMBER'];
    }else{
      echo $gw_user_info['EXAMINEE_NO'];
    }
    ?></h5>
</div>
<?php

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
<!-- Form -->
<form method='post' action='<?php echo $action_url; ?>'>
    <input type="hidden" name="gw_student_update_nonce" value="<?php echo $update_nonce; ?>"/>
    <input type="hidden" name="action" value="gw_student_update"/>
    <input type="hidden" name="gw_student_uid" value="<?php echo $gw_user_info['id']; ?>"/>
    <input type="hidden" name="gw_student_utyp" value="<?php echo $user_type; ?>"/>
    <div class="gw-form-contact">
      <div class="gw-form-input-group">
          <label for="gwStudentEmail">Email Address:</label>
          <input type="email" id="gwStudentEmail" name="gw_student_update_email" value="<?php echo $gw_user_info['EMAIL_ADDRESS'] ?>" required/>
      </div>
      <div class="gw-form-input-group">
          <label for="gwStudentPhoneNumber">Phone Number:</label>
          <input type="tel" id="gwStudentPhoneNumber" name="gw_student_update_phone" value="<?php echo $gw_user_info['CONTACT_NUMBER'] ?>" required/>
      </div>
      <div class="gw-form-input-group gw-form-address">
          <label for="gwStudentAddress">Address:</label>
          <textarea id="gwStudentAddress" name="gw_student_update_address" rows="5"><?php echo $gw_user_info['ADDRESS'] ?></textarea>
      </div>
    </div>
    <div class="gw-form-action">
      <?php echo sprintf('<a href="?page=%s&sub=%s&id=%s" class="button button-secondary">Back to Student Profile</a>', $_REQUEST['page'], 'gw-student-profile' , $gw_user_info['id']); ?>
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Update Contact">
    </div>
</form>

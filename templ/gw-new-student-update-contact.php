<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

$user_obj = apply_filters('gw_session_login_validate', function($raw){
      return $raw;
});

$user_id = $user_obj["uid"];
$user_typ = $user_obj["utyp"];
$data_source = new GWDataTable();

if($user_typ == "old"){
  $gw_user_info = GWUtility::gw_object_to_array($data_source->getOldStudentData($user_id));
}else{
  $gw_user_info = $data_source->getExamResultData($user_id);
}

if(empty($gw_user_info)){
  //do_action('gw_admin_notice_no_student');
}

// Get form meta data
list($get_url, $update_nonce, $action_url) = apply_filters('gw_form_meta', 'update_contact_info', false);

?>
<style>
.gw-student-summary h5 {
    margin-top: 0px;
}

.gw-student-summary h4 {
    margin-bottom: 3px;
}
.gw-form-action {
    margin: 15px 0;
}
.gw-form-input-group {
    display: grid;
    padding: 5px 0;
}
.gw-form-contact {
    display: grid;
    /* grid-template-columns: 50% 50%; */
    grid-column-gap: 10px;
    grid-row-gap: 5px;
}

div#wpadminbar {
    display: none;
}

textarea#gwStudentAddress {
    border-style: solid;
    border-width: thin;
    border-color: #7e8993;
}

input#submit {
    background-color: #4CAF50;
    border-style: none;
    border-radius: 30px;
    color: white;
    font-weight: 600;
}

.gw-form-input-group input, .gw-form-input-group.gw-form-address textarea {
  border-radius: 30px !important;
  font-size: 15px;
  padding: 10px;
  outline: none;
  border-style: solid;
  border-width: 1px;
  border-color: #7e8993;
}

div#message.error p {
    background-color: #e91e635e !important;
}

div#message p {
  display: inline-block;
  background-color: #C8E6C9;
  padding: 4px 25px;
  font-size: 14px;
  font-weight: 500;
  border-radius: 30px;
  margin: 3px;
  font-family: sans-serif;
}

div#message {
    text-align: center;
}

html {
    margin: 0 !important;
}

.gw-form-input-group * {
    font-family: sans-serif;
}

input#submit {
    padding: 11px 25px;
}

</style>
<div class="gw-student-update-contact-self gw-wrapper">
<!-- <div class="gw-student-summary">
    <h4><?php echo sprintf("%s %s %s %s", $gw_user_info['FIRST_NAME'], $gw_user_info['MIDDLE_NAME'], $gw_user_info['LAST_NAME'], $gw_user_info['NAME_SUFFIX']); ?></h4>
    <h5><?php echo $gw_user_info['EXAMINEE_NO'] ?></h5>
</div> -->
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
<!-- Form -->
<form method='post' action='<?php echo $action_url; ?>'>
    <input type="hidden" name="gw_student_update_self_nonce" value="<?php echo $update_nonce; ?>"/>
    <input type="hidden" name="action" value="gw_student_update_self"/>
    <input type="hidden" name="gw_student_uid" value="<?php echo $gw_user_info['id'] ?>"/>
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
          <textarea id="gwStudentAddress" name="gw_student_update_address" rows="3"><?php echo $gw_user_info['ADDRESS'] ?></textarea>
      </div>
    </div>
    <div class="gw-form-action">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Update Contact">
    </div>
</form>
</div>

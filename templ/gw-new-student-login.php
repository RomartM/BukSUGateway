<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

list($get_login_url, $login_nonce, $action_url) = apply_filters('gw_form_meta', 'login-new-student'); ?>
<div class="wrapper">
    <form class="form-signin" action="<?php echo $action_url; ?>" method="post">
        <h2 class="form-signin-heading"></h2>
        <input type="hidden" name="gw_login_nonce" value="<?php echo $login_nonce; ?>"/>
        <input type="hidden" name="action" value="gw_new_login"/>
        <label for="ExamineeNumber">Examinee Number:</label>
        <input type="text" id="ExamineeNumber" class="form-control" name="gw_examinee_number" placeholder="Examinee Number" required="" autofocus="" />
        <label for="DateOfExam">Date of Examination:</label>
        <input type="date" id="DateOfExam" class="form-control" name="gw_date_of_exam" placeholder="Date of Examination" required="" autofocus="" />
        <label for="TimeOfExam">Time of Examination:</label>
        <select name="gw_time_of_exam" id="TimeOfExam">
            <option value="9:00AM"> 9:00 AM</option>
            <option value="2:00PM"> 2:00 PM</option>
        </select>
        <label for="DateOfBirth">Date of Birth:</label>
        <input type="date" id="DateOfBirth" class="form-control" name="gw_date_of_birth" placeholder="Date of Birth" required="" autofocus="" />
        <button class="btn btn-lg btn-primary btn-block" type="submit">View Exam Result</button>
    </form>
</div>
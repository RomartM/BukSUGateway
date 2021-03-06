<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

list($get_login_url, $login_nonce, $action_url) = apply_filters('gw_form_meta', 'login-instant'); ?>
<div class="wrapper">
    <form class="form-signin" action="<?php echo $action_url; ?>" method="post">
        <h2 class="form-signin-heading"></h2>
        <input type="hidden" name="gw_login_instant_nonce" value="<?php echo $login_nonce; ?>"/>
        <input type="hidden" name="action" value="gw_login_instant"/>
        <label for="ReferenceNumber">Reference Number:</label>
        <input type="text" id="ReferenceNumber" class="form-control" name="gw_tc_number" placeholder="Reference Number" required autofocu/>
        <button class="btn btn-lg btn-primary btn-block" type="submit">View Status</button>
    </form>
</div>

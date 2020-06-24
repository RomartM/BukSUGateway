<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

list($get_login_url, $login_nonce, $action_url) = apply_filters('gw_form_meta', 'login-old-student'); ?>
<div class="wrapper">
    <form class="form-signin" action="<?php echo $action_url; ?>" method="post">
        <h2 class="form-signin-heading"></h2>
        <input type="hidden" name="gw_login_nonce" value="<?php echo $login_nonce; ?>"/>
        <input type="hidden" name="action" value="gw_old_login"/>
        <label for="IDNumber">ID Number:</label>
        <input type="number" id="IDNumber" class="form-control" name="gw_id_number" placeholder="ID Number" required="" autofocus="" />
        <label for="DateOfBirth">Date of Birth:</label>
        <input type="date" id="DateOfBirth" class="form-control" name="gw_date_of_birth" placeholder="Date of Birth" required="" autofocus="" />
        <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
    </form>
</div>
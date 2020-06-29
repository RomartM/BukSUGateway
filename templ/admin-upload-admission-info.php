<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

// Get form meta data
list($get_url, $login_nonce, $action_url) = apply_filters('gw_form_meta', 'gw-upload-admission-info', true);

?>
<!-- Form -->
<form method='post' action='<?php echo $action_url; ?>' enctype='multipart/form-data'>
    <input type="hidden" name="gw_upload_admission_info_nonce" value="<?php echo $login_nonce; ?>">
    <input type="hidden" name="action" value="gw_upload_admission_info">
    <div class="gw-form-input-group">
        <label for="GWImportFile">CSV File:</label>
        <input type="file" name="gw-import-file" id="GWImportFile">
    </div>
    <?php echo submit_button('Import Admission Info'); ?>
</form>

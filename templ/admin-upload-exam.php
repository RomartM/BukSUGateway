<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

// Get form meta data
list($get_login_url, $login_nonce, $action_url) = apply_filters('gw_form_meta', 'gw-upload-exam');
?>

<!-- Form -->
<form method='post' action='<? echo $action_url; ?>' enctype='multipart/form-data'>
    <input type="hidden" name="gw_nonce" value="<?php echo $login_nonce; ?>">
    <input type="hidden" name="action" value="upload-exam">
    <div class="gw-form-input-group">
        <label for="GWDegreeType">Degree Type:</label>
        <select name="gw-degree-type" id="GWDegreeType" required>
            <option value="college">College</option>
            <option value="masters">Masters</option>
            <option value="doctorate">Doctorate</option>
            <option value="law">Law</option>
        </select>
    </div>
    <div class="gw-form-input-group">
        <label for="GWImportFile">CSV File:</label>
        <input type="file" name="gw-import-file" id="GWImportFile" required>
    </div>
    <input type="submit" name="gw-import-submit" value="Import">
</form>

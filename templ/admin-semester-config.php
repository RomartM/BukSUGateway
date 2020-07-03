<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

// Get form meta data
list($get_url, $update_nonce, $action_url) = apply_filters('gw_form_meta', 'gw-settings-semester', true);

?>

<?php

$index = 1; // Data ID
$year = '2020'; // Semester Year
$semester = 1; // Semester
$campus = str_pad(1, 2, "0", STR_PAD_LEFT);; // Campus
$formatted_id_number =  str_pad($index, 5, "0", STR_PAD_LEFT); // id_number
$formatted_year = substr( $year, 2 );

echo sprintf("%s%s%s%s", $formatted_year, $campus, $semester, $formatted_id_number);

 ?>
<!-- Form -->
<form method='post' action='<?php echo $action_url; ?>' enctype='multipart/form-data'>
    <input type="hidden" name="gw_settings_semester_nonce" value="<?php echo $update_nonce; ?>">
    <input type="hidden" name="action" value="gw_settings_semester">
    <div class="gw-form-input-group">
        <label for="GWSemester">Semester</label>
        <select id="GWSemester" name="gw-semester">
            <option value="1" <?php echo get_option('gw_settings_semester') == 1? 'selected': ''; ?>>First Semester</option>
            <option value="2" <?php echo get_option('gw_settings_semester') == 2? 'selected': ''; ?>>Second Semester</option>
            <option value="3" <?php echo get_option('gw_settings_semester') == 3? 'selected': ''; ?>>First Summer</option>
            <option value="4" <?php echo get_option('gw_settings_semester') == 4? 'selected': ''; ?>>Second Summer</option>
        </select>
    </div>
    <div class="gw-form-input-group">
        <label for="GWYear">Year</label>
        <input type="number" name="gw-semester-year" min="2020" max="2099" step="1" value="<?php echo get_option('gw_settings_semester_year'); ?>" id="GWYear">
    </div>
    <?php echo submit_button('Save Settings'); ?>
</form>

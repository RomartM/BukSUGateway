<?php

$admin_pages_config  = new GWAdminPages();
$tab_values = $admin_pages_config->gw_pre_listing_tabs();
$parent_page = sanitize_text_field($_REQUEST['page']);

$entries_tab = new GWTabs($tab_values, array("slug"=>"content_label", "content_id"=>"content_id"), $parent_page);
$entries_tab->build(function ($tab, $content_id) {
    global $gwEntriesNewStudentTable;

    if (empty($_REQUEST['tab'])) {
        $_REQUEST['tab'] = 'pending';
    }

    $gwEntriesNewStudentTable->views();
     ?>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php if (isset($_REQUEST['tab'])): ?>
        <input type="hidden" name="tab" value="<?php echo $_REQUEST['tab'] ?>"/>
        <?php endif; ?>
        <?php if (isset($_REQUEST['level'])): ?>
        <input type="hidden" name="level" value="<?php echo $_REQUEST['level'] ?>"/>
        <?php endif; ?>
        <?php if (isset($_REQUEST['paged'])): ?>
        <input type="hidden" name="paged" value="<?php echo $_REQUEST['paged'] ?>"/>
        <?php endif; ?>
        <?php if (isset($_REQUEST['exam_status'])): ?>
        <input type="hidden" name="exam_status" value="<?php echo $_REQUEST['exam_status'] ?>"/>
        <?php endif; ?>
        <?php
    if (isset($_REQUEST['s'])) {
        $gwEntriesNewStudentTable->prepare_items($content_id, $_REQUEST['s']);
    } else {
        $gwEntriesNewStudentTable->prepare_items($content_id);
    }
    $gwEntriesNewStudentTable -> search_box('search', 'search_id');
    $gwEntriesNewStudentTable -> display(); ?>
    </form>
    <?php
});

 ?>

<?php

if (! defined('ABSPATH')) {
    exit;
}

$tab_values = array(
  array("content_label"=>"Inactive", "content_id"=>"inactive"),
  array("content_label"=>"Pending", "content_id"=>"pending"),
  array("content_label"=>"Approved", "content_id"=>"approved"),
  array("content_label"=>"Denied", "content_id"=>"denied")
);

$entries_tab = new GWTabs($tab_values, array("slug"=>"content_label", "content_id"=>"content_id"), "gw-exam-results-manager");
$entries_tab->build(function ($tab, $content_id) {
    global $gwEntriesTable;

    if (((!empty($_REQUEST['tab'])) ? $_REQUEST['tab'] : '') == 'inactive') {
        $gwEntriesTable->views();
    } ?>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php if (isset($_REQUEST['tab'])): ?>
        <input type="hidden" name="tab" value="<?php echo $_REQUEST['tab'] ?>"/>
        <?php endif; ?>
        <?php if (isset($_REQUEST['paged'])): ?>
        <input type="hidden" name="paged" value="<?php echo $_REQUEST['paged'] ?>"/>
        <?php endif; ?>
        <?php if (isset($_REQUEST['exam_status'])): ?>
        <input type="hidden" name="exam_status" value="<?php echo $_REQUEST['exam_status'] ?>"/>
        <?php endif; ?>
        <?php
    if (isset($_REQUEST['s'])) {
        $gwEntriesTable->prepare_items($content_id, $_REQUEST['s']);
    } else {
        $gwEntriesTable->prepare_items($content_id);
    }
    $gwEntriesTable -> search_box('search', 'search_id');
    $gwEntriesTable -> display(); ?>
    </form>
    <?php
});

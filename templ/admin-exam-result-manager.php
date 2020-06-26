<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

$examResultTable = new GWEntriesTable();
$examResultTable->prepare_items();
?>
<form method="post">
    <input type="hidden" name="page" value="my_list_test" />
    <?php $examResultTable->search_box('search', 'search_id'); ?>
</form>
<?php
$examResultTable->display();

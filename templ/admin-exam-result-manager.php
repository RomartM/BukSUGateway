<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

?>
    <form method="post" >
        <?php
        global $gwEntriesTable;

        if( isset($_POST['s']) ){
            $gwEntriesTable->prepare_items($_POST['s']);
        } else {
            $gwEntriesTable->prepare_items();
        }
        $gwEntriesTable -> search_box( 'search', 'search_id' );
        $gwEntriesTable -> display();
        ?>
    </form>
<?php
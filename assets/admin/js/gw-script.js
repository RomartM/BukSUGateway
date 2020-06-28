jQuery(document).ready(function($) {
    var selector = $("#form-list");

    var dlg = $('#gw-dialog');
    var dlg_close;

    $("#gw_validate").click(function(){
      dlg.dialog('open');
    });

    dlg.dialog({
        title: 'Validate Student',
        dialogClass: 'wp-dialog',
        autoOpen: false,
        draggable: true,
        width: 'auto',
        height: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: "center",
            at: "center",
            of: window
        },
        open: function () {
            // close dialog by clicking the overlay behind it
            $('.ui-widget-overlay').bind('click', function () {
                $('#gw-dialog').dialog('close');
                dlg_close.click();
            })
        },
        create: function () {
            dlg_close = $('.ui-dialog-titlebar-close');
            dlg_close.addClass('ui-button');
        },
    });

})

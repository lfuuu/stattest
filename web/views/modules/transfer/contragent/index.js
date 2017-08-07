+function ($) {
    'use strict';

    $(function () {
        $('#dialog-close').click(function () {
            window.parent.$dialog.dialog('close');
        });
    })

}(jQuery);
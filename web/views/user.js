+function ($) {
    'use strict';

    $('#dialog-close').click(function() {
        window.parent.location.reload(true);
        window.parent.$dialog.dialog('close');
    });

}(jQuery);
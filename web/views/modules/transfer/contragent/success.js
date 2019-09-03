+function ($) {
    'use strict';

    $(function () {
        $('#dialog-close').click(function() {
            //window.parent.location.reload(true);
            window.parent.location.assign(window.parent.location.href);
            window.parent.$dialog.dialog('close');
        });

        $(document).bind('keydown', function(e) {
            if (e.keyCode === $.ui.keyCode.ESCAPE)
                $('#dialog-close').trigger('click');
        });
    })

}(jQuery);
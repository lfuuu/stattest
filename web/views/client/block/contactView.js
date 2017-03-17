+function ($) {
    'use strict';

    $(function () {
        $('#contacts-edit-link').on('click', function () {
            $('#contacts-view').slideUp();
            $('#contacts-edit').slideDown();
        });
    })

}(jQuery);
+function ($) {
    'use strict';

    $(function () {
        $('#buttonSave').closest('form')
            .on('submit', function () {
                $('#type-select .btn').not('.btn-primary').each(function () {
                    $($(this).data('tab')).remove();
                });
                return true;
            });
    })

}(jQuery);
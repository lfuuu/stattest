+function ($) {
    'use strict';

    $(function () {
        var symbolsPerMessage = 70,
            checkLength = function () {
                 if ($(this).val().length > symbolsPerMessage) {
                     $(this).addClass('warning');
                     $.notify('Сообщение больше ' + symbolsPerMessage + ' символов', 'error');
                 } else {
                     $(this).removeClass('warning');
                 }
             };

        $('textarea[name$="[content]"]')
            .on('keyup', checkLength)
            .each(checkLength);
    })

}(jQuery);
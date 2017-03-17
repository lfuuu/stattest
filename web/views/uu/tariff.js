+function ($) {
    'use strict';

    $(function () {
        $('.package-pricelist .multiple-input').on('afterInit', function () {
            $(this).multipleInput('remove');
        });
    })

}(jQuery);
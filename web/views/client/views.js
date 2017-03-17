+function ($) {
    'use strict';

    $(function () {
        $('.showFullTable')
            .on('click', function () {
                $(this).parent().find('.fullTable').toggle();
            });
    })

}(jQuery);
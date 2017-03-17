+function ($) {
    'use strict';

    $(function () {
        $('a.bill-info')
            .hover(
                function() {
                    $('div[data-bill="' + $(this).data('bill') + '"]').css('background-color', '#D0D0D0');
                },
                function() {
                    $('div[data-bill="' + $(this).data('bill') + '"]').css('background-color', 'inherit');
                }
            )
    })

}(jQuery);
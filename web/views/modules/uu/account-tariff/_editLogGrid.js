+function ($) {
    'use strict';

    $(function () {
        $('.account-tariff-button-cancel').on('click', function () {
            return confirm('Отменить смену тарифа?');
        });
    })

}(jQuery);
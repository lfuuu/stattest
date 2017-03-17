+function ($) {
    'use strict';

    $(function () {
        // если ресурс может быть выключен/включен, то при его включении цену указывать нет смысла, потому что она входит в абонентку
        $('#tariff-is_autoprolongation').on('change', function () {
            var $checkbox = $(this),
                $input = $('#tariff-count_of_validity_period');

            if ($checkbox.is(':checked')) {
                $input.attr('readonly', 'readonly');
            } else {
                $input.removeAttr('readonly');
            }
        })
        .trigger('change');
    })

}(jQuery);
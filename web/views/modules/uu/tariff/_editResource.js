+function ($) {
    'use strict';

    $(function () {
        // если ресурс может быть выключен/включен, то при его включении цену указывать нет смысла, потому что она входит в абонентку
        $('.tariffResources input[type=checkbox][name*=amount]')
            .on('change', function () {
                var $checkbox = $(this);
                var $priceDiv = $checkbox.parent().parent().next();
                var $priceInput = $priceDiv.find('input');
                var $minPriceInput = $priceDiv.next().find('input');
                if ($checkbox.is(':checked')) {
                    $priceInput.attr('readonly', 'readonly').val(0);
                    $minPriceInput.attr('readonly', 'readonly').val(0);
                } else {
                    $priceInput.removeAttr('readonly');
                    $minPriceInput.removeAttr('readonly');
                }
            })
            .trigger('change');
    })

}(jQuery);
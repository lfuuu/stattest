+function ($, param) {
    'use strict';

    $(function () {
        $('#changeTariffButton' + param.rndId).on('click', function () {
            // @todo еще надо отлавливать сабмит формы enter, но не путать с closeTariffButton. Можно и не делать здесь - достаточно в контроллере
            if ($('#accountTariffTariffPeriod' + param.rndId).val() == $(this).data('old-tariff-period-id')) {
                alert('Нет смысла менять тариф на тот же самый. Выберите другой тариф.');
                return false;
            }
        });
        $('#closeTariffButton' + param.rndId).on('click', function () {
            return confirm(param.confirmText);
        });
    })

}(
    jQuery,
    window.frontendVariables.modulesUuAccountTariffEditLogInput
);
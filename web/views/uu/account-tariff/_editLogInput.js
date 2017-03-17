+function ($) {
    'use strict';

    $(function () {
        $('#changeTariffButton' + frontendVariables.uuAccountTariffEditLogInput.rndId).on('click', function () {
            // @todo еще надо отлавливать сабмит формы enter, но не путать с closeTariffButton. Можно и не делать здесь - достаточно в контроллере
            if ($('#accountTariffTariffPeriod' + logInputRndId).val() == $(this).data('old-tariff-period-id')) {
                alert('Нет смысла менять тариф на тот же самый. Выберите другой тариф.');
                return false;
            }
        });
        $('#closeTariffButton' + frontendVariables.uuAccountTariffEditLogInput.rndId).on('click', function () {
            return confirm(frontendVariables.uuAccountTariffEditLogInput.confirmText);
        });
    })

}(
    jQuery,
    window.frontendVariables.uuAccountTariffEditLogInput.rndId,
    window.frontendVariables.uuAccountTariffEditLogInput.confirmText
);
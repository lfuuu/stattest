+function ($, isRemovePackageMinutes, isRemovePackagePrices, isRemovePackagePricelists) {
    'use strict';

    $(function () {

        var selectorArray = [];
        if (isRemovePackageMinutes) {
            selectorArray.push('.package-minute .multiple-input');
        }

        if (isRemovePackagePrices) {
            selectorArray.push('.package-price .multiple-input');
        }

        if (isRemovePackagePricelists) {
            selectorArray.push('.package-pricelist .multiple-input');
        }

        if (selectorArray.length) {
            // нет моделей, но виджет для рендеринга их обязательно требует
            // поэтому рендерим дефолтную модель и сразу ж ее удаляем
            $(selectorArray.join(', ')).on('afterInit', function () {
                $(this).multipleInput('remove');
            });
        }
    });

}(jQuery,
    window.frontendVariables.uuTariffEditMainVoipPackageMinute && window.frontendVariables.uuTariffEditMainVoipPackageMinute.isRemovePackageMinutes,
    window.frontendVariables.uuTariffEditMainVoipPackagePrice && window.frontendVariables.uuTariffEditMainVoipPackagePrice.isRemovePackagePrices,
    window.frontendVariables.uuTariffEditMainVoipPackagePricelist && window.frontendVariables.uuTariffEditMainVoipPackagePricelist.isRemovePackagePricelists
);
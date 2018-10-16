+function ($, isRemovePackageMinutes, isRemovePackagePrices, isRemovePackagePricelistsV1, isRemovePackagePricelistsV2) {
    'use strict';

    $(function () {

        var selectorArray = [];
        if (isRemovePackageMinutes) {
            selectorArray.push('.package-minute .multiple-input');
        }

        if (isRemovePackagePrices) {
            selectorArray.push('.package-price .multiple-input');
        }

        if (isRemovePackagePricelistsV1) {
            selectorArray.push('.package-pricelist #pricelist_v1 .multiple-input');
        }

        if (isRemovePackagePricelistsV2) {
            selectorArray.push('.package-pricelist #pricelist_v2 .multiple-input');
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
    window.frontendVariables.modulesUuTariffEditMainVoipPackageMinute && window.frontendVariables.modulesUuTariffEditMainVoipPackageMinute.isRemovePackageMinutes,
    window.frontendVariables.modulesUuTariffEditMainVoipPackagePrice && window.frontendVariables.modulesUuTariffEditMainVoipPackagePrice.isRemovePackagePrices,
    window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist && window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist.isRemovePackagePricelistsV1,
    window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist && window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist.isRemovePackagePricelistsV2
);
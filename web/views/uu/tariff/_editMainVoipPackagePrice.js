+function ($, isRemovePackagePrices) {
    'use strict';

    $(function () {

        if (isRemovePackagePrices) {
            // нет моделей, но виджет для рендеринга их обязательно требует
            // поэтому рендерим дефолтную модель и сразу ж ее удаляем
            $('.package-price .multiple-input').on('afterInit', function () {
                $(this).multipleInput('remove');
            });
        }
    });

}(jQuery, window.frontendVariables.uuTariffEditMainVoipPackagePrice.isRemovePackagePrices);
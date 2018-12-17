+function ($, isRemovePackagePricelistsV2) {
    'use strict';

    $(function () {

      if (isRemovePackagePricelistsV2) {
        // нет моделей, но виджет для рендеринга их обязательно требует
        // поэтому рендерим дефолтную модель и сразу ж ее удаляем
        $('.package-pricelist #pricelist_v2 .multiple-input').on('afterInit', function () {
          //$(this).multipleInput('remove');
        });
      }

    });

}(jQuery,
  window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist && window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist.isRemovePackagePricelistsV2
);
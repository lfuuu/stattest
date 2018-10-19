+function ($, isRemovePackagePricelistsV1, isRemovePackagePricelistsV2) {
    'use strict';

    $(function () {

        $('body')
            .on('change', '.package-pricelist #pricelist_v1 select', function () {
                // при изменении прайслиста вывести ссылку на его просмотр
                // событие надо ловить не только для нынешних select, но и для будущих
                var $this = $(this),
                    id = $this.attr('id').replace('-pricelist_id', '-id'),
                    $id = $('#' + id).next(),
                    $price = $id.next();

                if (!$price.length) {
                    $price = $('<a />').html('Цены').attr('target', '_blank').insertAfter($id);
                }

                var val = $this.val();
                if (val) {
                    $price.show();
                    $price.attr('href', '/index.php?module=voipnew&action=defs&pricelist=' + val);
                } else {
                    $price.hide();
                }
            });
        // при инициализации прайслиста вывести ссылку на его просмотр
        $('.package-pricelist #pricelist_v1 select').trigger('change');

        $('body')
          .on('change', '.package-pricelist input[type=checkbox]#is_pricelist_v2', function () {
            var $checkbox= $(this),
              $priceListV1 = $('body .package-pricelist #pricelist_v1'),
              $priceListV2 = $('body .package-pricelist #pricelist_v2');

            if ($checkbox.is(':checked')) {
              $priceListV1.addClass('hide');
              $priceListV2.removeClass('hide');
            } else {
              $priceListV1.removeClass('hide');
              $priceListV2.addClass('hide');
            }
      });
        $('.package-pricelist input[type=checkbox]#is_pricelist_v2').trigger('change');

      var selectorArray = [];
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
  window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist && window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist.isRemovePackagePricelistsV1,
  window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist && window.frontendVariables.modulesUuTariffEditMainVoipPackagePricelist.isRemovePackagePricelistsV2
);
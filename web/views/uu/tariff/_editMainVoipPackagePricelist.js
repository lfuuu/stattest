+function ($, isRemovePackagePricelists) {
    'use strict';

    $(function () {

        if (isRemovePackagePricelists) {
            // нет моделей, но виджет для рендеринга их обязательно требует
            // поэтому рендерим дефолтную модель и сразу ж ее удаляем
            $('.package-pricelist .multiple-input').on('afterInit', function () {
                $(this).multipleInput('remove');
            });
        }

        $('body')
            .on('change', '.package-pricelist select', function () {
                // при изменении прайслиста вывести ссылку на его просмотр
                // событие надо ловить не только для нынешних select, но и для будущих
                var $this = $(this),
                    id = $this.attr('id').replace('-pricelist_id', '-id'),
                    $id = $('#' + id),
                    $price = $id.next();

                if (!$price.length) {
                    $price = $('<a />').html('Цены').attr('target', '_blank').insertAfter($id);
                }
                $price.attr('href', '/index.php?module=voipnew&action=defs&pricelist=' + $this.val());
            });
        // при инициализации прайслиста вывести ссылку на его просмотр
        $('.package-pricelist select').trigger('change');
    });

}(jQuery, window.frontendVariables.uuTariffEditMainVoipPackagePricelist.isRemovePackagePricelists);
+function ($, isRemovePackageMinutes) {
    'use strict';

    $(function () {

        if (isRemovePackageMinutes) {
            // нет моделей, но виджет для рендеринга их обязательно требует
            // поэтому рендерим дефолтную модель и сразу ж ее удаляем
            $('.package-minute .multiple-input').on('afterInit', function () {
                $(this).multipleInput('remove');
            });
        }
    });

}(jQuery, window.frontendVariables.uuTariffEditMainVoipPackageMinute.isRemovePackageMinutes);
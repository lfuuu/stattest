+function ($) {
    'use strict';

    $(function () {
        $('.formReload').on('change', function () {
            $('#isFake').val(1);
            document.getElementById(frontendVariables.tariffDidGroupEdit.formId).submit();
        });
    })

}(
    jQuery,
    window.frontendVariables.tariffDidGroupEdit.formId
);
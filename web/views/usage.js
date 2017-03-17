function submitForm(scenario) {
    $('#scenario').val(scenario);
    $('#' + window.frontendVariables.usage.editFormId)[0].submit();

}

function submitForm2(scenario) {
    $('#scenario2').val(scenario);
    $('#' + window.frontendVariables.usage.tariffEditFormId)[0].submit();
}

+function ($) {
    'use strict';

    $(function () {
        $('.form-reload').change(function () {
            submitForm('default');
        });

        $('.form-reload2').change(function () {
            submitForm2('default');
        });
    })

}(jQuery);
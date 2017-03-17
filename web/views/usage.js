+function ($) {
    'use strict';

    function submitForm(scenario) {
        $('#scenario').val(scenario);
        $('#' + frontendVariables.usage.editFormId)[0].submit();

    }

    function submitForm2(scenario) {
        $('#scenario2').val(scenario);
        $('#' + frontendVariables.usage.tariffEditFormId)[0].submit();
    }

    $(function () {
        $('.form-reload').change(function() {
            submitForm('default');
        });

        $('.form-reload2').change(function(e) {
            submitForm2('default');
        });
    })

}(
    jQuery,
    window.frontendVariables.usage.editFormId,
    window.frontendVariables.usage.tariffEditFormId
);
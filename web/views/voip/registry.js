+function ($) {
    'use strict';

    $(function () {

        $('.formReload').on('change', function () {
            document.getElementById(frontendVariables.voipRegistryEdit.registryFormId).submit();
        });

        $('.formReloadOnLostFocus').on('focusout', function () {
            if ($('#registryform-source').val() == 'operator_not_for_sale') {
                document.getElementById(frontendVariables.voipRegistryEdit.registryFormId).submit();
            }
        });

        $('#registryform-number_from, #registryform-number_to').on('blur', function () {
            var $operator = $('#registryform-operator');
            $operator.val('');
            $.get('/uu/voip/get-number-range', {
                number: $(this).val()
            }, function(json) {
                    var operatorName = '(не указан)';
                    if (json && json.operator && json.operator.name) {
                        operatorName = json.operator.name;
                    }
                $operator.val(operatorName);
            });
        });
    })

}(
    jQuery,
    window.frontendVariables.voipRegistryEdit.registryFormId
);
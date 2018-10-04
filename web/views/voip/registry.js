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
            let $operator = $('#registryform-operator');
            let $nnp_operator_id = $('#registryform-nnp_operator_id');

            $nnp_operator_id.val("");
            $operator.val("");

            $.get('/uu/voip/get-number-range', {
                number: $(this).val()
            }, function(json) {
                let operatorName = '(не указан)';
                if (json && json.operator && json.operator.name) {
                    operatorName = json.operator.name;
                    if ($("#registryform-source").val() === "portability_not_for_sale") {
                        $nnp_operator_id.val(json.operator.id);
                    }
                }
                $operator.val(operatorName);
            });
        });
    })

}(
    jQuery,
    window.frontendVariables.voipRegistryEdit.registryFormId
);
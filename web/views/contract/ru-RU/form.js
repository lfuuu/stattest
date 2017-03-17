+function ($) {
    'use strict';

    $(function () {
        var $s1 = $('#contracteditform-business_id'),
            $s2 = $('#contracteditform-business_process_id'),
            $s3 = $('#contracteditform-business_process_status_id'),
            $s4 = $('#contracteditform-contract_type_id'),
            vals2 = $s2.val(),
            vals3 = $s3.val(),
            vals4 = $s4.val();

        $s2.empty();
        $(frontendVariables.contractRuRUForm.statuses.processes).each(function (k, v) {
            if ($s1.val() == v['up_id']) {
                $s2.append('<option ' + (v['id'] == vals2 ? 'selected' : '') + ' value="' + v['id'] + '">' + v['name'] + '</option>');
            }
        });

        $s3.empty();
        $(frontendVariables.contractRuRUForm.statuses).each(function (k, v) {
            if ($s2.val() == v['up_id']) {
                $s3.append('<option ' + (v['id'] == vals3 ? 'selected' : '') + ' value="' + v['id'] + '">' + v['name'] + '</option>');
            }
        });

        if ($s4) {
            $s4.empty();
            $s4.append('<option value="0">Не задано</option>');
            $(frontendVariables.contractRuRUForm.contractTypes).each(function (k, v) {
                if ($s2.val() == v['business_process_id']) {
                    $s4.append('<option value="' + v['id'] + '" ' + (v['id'] == vals4 ? 'selected' : '') + '>' + v['name'] + '</option>');
                }
            });
        }

        $s1.on('change', function () {
            var $form = $(this).closest('form');
            $('<input type="hidden" name="notSave" value="1" />').appendTo($form);
            $form.submit();
        });

        $s2.on('change', function () {
            $s3.empty();
            $(frontendVariables.contractRuRUForm.statuses).each(function (k, v) {
                if ($s2.val() == v['up_id']) {
                    $s3.append('<option value="' + v['id'] + '" ' + (v['id'] == vals3 ? 'selected' : '') + '>' + v['name'] + '</option>');
                }
            });
            if ($s4) {
                $s4.empty();
                $(frontendVariables.contractRuRUForm.contractTypes).each(function (k, v) {
                    if ($s2.val() == v['business_process_id']) {
                        $s4.append('<option value="' + v['id'] + '" ' + (v['id'] == vals4 ? 'selected' : '') + '>' + v['name'] + '</option>');
                    }
                });
            }
        });

        $('.btn-disabled').on('click', function () {
            return false;
        });

        $('.period-type').on('change', function () {
            var month = $(this).parent().parent().next();
            if ($(this).val() == 'month') {
                month.show();
            } else {
                month.hide();
            }
        })
    })

}(
    jQuery,
    window.frontendVariables.contractRuRUForm.statuses,
    window.frontendVariables.contractRuRUForm.contractTypes
);
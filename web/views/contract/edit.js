+function ($) {
    'use strict';

    $(function () {
        var $businessIdField = $('#contracteditform-business_id'),
            $changeExternal = $('#change-external');

        if ($businessIdField.val() == 3) {
            $changeExternal.val('external');
        } else {
            $changeExternal.val('internal');
        }

        $businessIdField.on('change', function () {
            if ($businessIdField.val() == 3) {
                $changeExternal.val('external');
            } else {
                $changeExternal.val('internal');
            }

            $changeExternal.trigger('change');
        });

        $changeExternal.on('change', function () {
            var fields = $('.tmpl-group[data-type="contract"], .tmpl[data-type="contract"], #agreement-block');

            if ($(this).val() == 'internal') {
                fields.show();
            } else {
                fields.hide();
            }
        }).trigger('change');

        $('a.show-all').on('click', function () {
            $(this).parents('table').find('tbody > tr.show-all').toggleClass('hidden');
            $(this).toggleClass('label-success');
            return false;
        });

        $('tr.editable').find('a').on('click', function () {
            var $fields = $(this).parents('tr').find('td[data-field]'),
                $form = $(this).parents('form');

            $fields.each(function () {
                var $field = $form.find('[name*="' + $(this).data('field') + '"]'),
                    $value = $(this).data('value') ? $(this).data('value') : $(this).text();

                $field.val($value).trigger('change');
            });
            $form.find('input:eq(2)').trigger('focus');

            return false;
        });

        $('select[name*="period_type"]').on('change', function () {
            var $nextInput = $(this).parents('td').next().find('input');
            if ($(this).val() == 'month') {
                $nextInput.show();
            } else {
                $nextInput.hide();
            }
        });
    })

}(jQuery);
+function ($) {
    'use strict';

    $(function () {
        var
            $legalType = $('#contragenteditform-legal_type'),
            $legalTypes = $('#type-select .btn'),
            $tab = $legalTypes.filter('[data-tab="#' + $legalType.val() + '"]'),
            $tabs = $('.tab-pane'),
            $legalName = $('#legal #contragenteditform-name'),
            $legalNameFull = $('#legal #contragenteditform-name_full'),
            $addressJur = $('#legal #contragenteditform-address_jur'),
            $addressPost = $('#legal #contragenteditform-address_post');

        if ($tab.length < 1) {
            $tab = $legalTypes.first();
            $legalType.val($tab.data('tab').replace(/#/, ''));
        }
        $tab.addClass('btn-primary').removeClass('btn-default');

        $tabs.hide();
        $($legalTypes.filter('.btn-primary').data('tab')).show();

        $('#contragenteditform-country_id').on('change', function () {
            var $form = $(this).closest('form');
            $('<input type="hidden" name="notSave" value="1" />').appendTo($form);
            $form.submit();
        });

        $legalTypes.on('click', function () {
            var oldT = $legalTypes.filter('.btn-primary').data('tab'),
                newT = $(this).data('tab');

            $(oldT + ' .form-control').each(function () {
                $(newT + ' .form-control[name="' + $(this).attr('name') + '"]').val($(this).val());
            });

            $legalType.val($(newT).attr('id'));

            $legalTypes.removeClass('btn-primary').addClass('btn-default');
            $(this).addClass('btn-primary');
            $tabs.hide();
            $(newT).show();
        });

        $legalName.on('blur', function () {
            if ($legalNameFull.val() == '') {
                $legalNameFull.val($(this).val());
            }
        });
        $legalNameFull.on('blur', function () {
            if ($legalName.val() == '') {
                $legalName.val($(this).val());
            }
        });

        $addressJur.on('blur', function () {
            if ($addressPost.val() == '') {
                $addressPost.val($(this).val());
            }
        });
        $addressPost.on('blur', function () {
            if ($addressJur.val() == '') {
                $addressJur.val($(this).val());
            }
        });

        $('#contragenteditform-is_take_signatory').on('change', function (event) {
            var isChecked = $(event.currentTarget).is(':checked')
            $('#contragenteditform-signatory_position').prop('disabled', !isChecked);
            $('#contragenteditform-signatory_fio').prop('disabled', !isChecked);
        });
    })

}(jQuery);
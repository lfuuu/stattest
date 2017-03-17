+function ($) {
    'use strict';

    $(function () {
        var $form = $('form#' + frontendVariables.transferIndex.formId);

        $('a#transfer-select-all').on('click', function() {
            var $usages = $form.find('input[type="checkbox"]');
            $usages.prop('checked', !$usages.prop('checked'));
            $(this).toggleClass('label-success');
            return false;
        });

        $('input[name="' + frontendVariables.transferIndex.formName + '[actual_custom]"]').on('focus', function() {
            $(this).prev('input').prop('checked', true);
        });

        $('input[name="target_account_search"]')
            .on('keydown', function(e) {
                if (e.keyCode === $.ui.keyCode.TAB && $(this).autocomplete('instance').menu.active) {
                    e.preventDefault();
                }
                if (e.keyCode === $.ui.keyCode.ENTER) {
                    $(this).blur();
                }
            })
            .on('focus', function() {
                $(this).prev('input').prop('checked', true);
            })
            .on('blur', function() {
                var value = $(this).val();
                if (value.length && value.test(/^[0-9]+$/)) {
                    $('input[name="' + frontendVariables.transferIndex.formName + '[target_account_id_custom]"]').val(value);
                }
            })
            .autocomplete({
                source: '/transfer/account-search?clientAccountId=' + frontendVariables.transferIndex.clientAccountId,
                minLength: 2,
                focus: function() {
                    return false;
                },
                select: function(event, ui) {
                    $('input[name="' + frontendVariables.transferIndex.formName + '[target_account_id_custom]"]').val(ui.item.value);
                    $(this).val(ui.item.label);
                    return false;
                }
            })
            .data('autocomplete')._renderItem = function(ul, item) {
                return $('<li />')
                    .data('item.autocomplete', item)
                    .append('<a title="' + item.full + '">' + item.label + '</a>')
                    .appendTo(ul);
            };
    })
}(
    jQuery,
    window.frontendVariables.transferIndex.formId,
    window.frontendVariables.transferIndex.formName,
    window.frontendVariables.transferIndex.clientAccountId
);
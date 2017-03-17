+function ($) {
    'use strict';

    $('.rights_mode')
        .on('click', function() {
            var mode = $(this).val(),
                group = $(this).data('group'),
                inputs = $('input[id^="' + group + '"]'),
                labels = $('label[for^="' + group + '"]');

            if (mode == 'custom') {
                inputs.prop('disabled', false);
                labels.toggleClass('disabled-label');
            }
            else {
                inputs
                    .prop('checked', false)
                    .prop('disabled', true);
                labels.toggleClass('disabled-label');

                if (frontendVariables.userControlRights.groupRights[group]) {
                    var values = frontendVariables.userControlRights.groupRights[group].split(',');
                    for (var i=0,s=values.length; i<s; i++) {
                        inputs.filter('[value="' + values[i] + '"]').prop('checked', true);
                    }
                }
            }
        });

}(
    jQuery,
    window.frontendVariables.userControlRights.groupRights
);
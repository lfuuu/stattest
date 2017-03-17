+function ($) {
    'use strict';

    $(function () {
        var $storedVersions = $('#historyVersionStoredDate'),
            $datepicker = $('#deferred-date-input');

        $datepicker.parent().parent().hide();

        $('#buttonSave').closest('form')
            .on('submit', function () {
                var $storedVersion = $storedVersions.find('option:selected');

                $('#type-select .btn').not('.btn-primary').each(function () {
                    $($(this).data('tab')).remove();
                });
                if ($storedVersion.val() == '') {
                    $storedVersion.val($datepicker.val()).select();
                }
                return true;
            });

        $storedVersions.on('change', function () {
            if ($('option:selected', this).val() == '') {
                $datepicker.parent().parent().show();
            } else {
                $datepicker.parent().parent().hide();
            }
        });
    })

}(jQuery);
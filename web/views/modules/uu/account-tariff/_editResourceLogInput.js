+function ($) {
    'use strict';

    $(function () {
        $('.setActualFrom').on('click', function () {
            var val = $(this).data('date');
            $('#accounttariffresourcelog-actual_from-kvdate').kvDatepicker('update', val);
        });
    })

}(jQuery);
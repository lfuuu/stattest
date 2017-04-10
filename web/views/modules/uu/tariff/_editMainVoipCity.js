+function ($) {
    'use strict';

    $(function () {
        $('select[name="Tariff[country_id]"]').on('change', function () {
            var location = self.location.href.replace(/&?countryId=[0-9]+/, '');
            if (confirm('Страница будет перезагружена, для установки нового списка городов, уверены ?')) {
                self.location.href = location + '&countryId=' + $(this).val();
            }
        });
    })

}(jQuery);
+function ($) {
    'use strict';

    $(function () {
        $('select[name="Tariff[country_id]"]').on('change', function () {
            var location = self.location.href.replace(/&?countryId=[0-9]+/, '');
            if (confirm('Для установки нового списка городов страница будет перезагружена без сохранения. Уверены?')) {
                self.location.href = location + '&countryId=' + $(this).val();
            }
        });
    })

}(jQuery);
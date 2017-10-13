+function ($) {
    'use strict';

    $(function () {

        $('body')
            .on('change', '.package-minute select', function () {
                // при изменении направления вывести ссылку на его скачивание
                // событие надо ловить не только для нынешних select, но и для будущих
                var $this = $(this),
                    id = $this.attr('id').replace('-destination_id', '-id'),
                    $id = $('#' + id).next(),
                    $price = $id.next();

                if (!$price.length) {
                    $price = $('<a />').html('Скачать префиксы номеров').insertAfter($id);
                }

                var val = $this.val();
                if (val) {
                    $price.show();
                    $price.attr('href', '/nnp/destination/download/?id=' + val);
                } else {
                    $price.hide();
                }
            });
        // при инициализации прайслиста вывести ссылку на его просмотр
        $('.package-minute select').trigger('change');
    })

}(jQuery);
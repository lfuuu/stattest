+function ($) {
    'use strict';

    $(function () {

        $('body')
            .on('change', '.package-price select', function () {
                // при изменении направления вывести ссылку на его скачивание
                // событие надо ловить не только для нынешних select, но и для будущих
                var $this = $(this),
                    id = $this.attr('id').replace('-destination_id', '-id'),
                    $id = $('#' + id).next(),
                    $price = $id.next();

                if (!$price.length) {
                    $price = $('<a />').html('Скачать префиксы номеров').insertAfter($id);
                }
                $price.attr('href', '/nnp/destination/download/?id=' + $this.val());
            });
        // при инициализации прайслиста вывести ссылку на его просмотр
        $('.package-price select').trigger('change');

        $('.package-price .multiple-input')
            .on('afterInit afterAddRow afterDeleteRow onChangePeriod', function () {
                setTimeout(function () {
                    // пустым строчкам установить 0

                    $(".package-price .list-cell__interconnect_price input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });

                    $(".package-price .list-cell__connect_price input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });

                    $(".package-price .list-cell__weight input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });

                }, 400); // потому что select2 рендерится чуть позже
            });
    })

}(jQuery);
+function ($) {
    'use strict';

    // Все эти танцы с бубном для правильной ajax-загрузки данных в грид без запросов данных для фильтров
    $(function () {
        $('#w0').removeAttr('id');

        $('#w1')
            .yiiGridView({'filterUrl':'', 'filterSelector':'.beforeHeaderFilters input, .beforeHeaderFilters select'});

        $(document)
            .on('pjax:start', '#w1-pjax', function () {
                $(this).find('#w0-container').addClass('kv-grid-loading');
            })
            .on('pjax:end', '#w1-pjax', function () {
                $(this).find('#w0-container').removeClass('kv-grid-loading');
            });

        $(document).off('change.yiiGridView', '.beforeHeaderFilters input, .beforeHeaderFilters select');

        $('.summary b:eq(1)').before('примерно ');
    })

}(jQuery);
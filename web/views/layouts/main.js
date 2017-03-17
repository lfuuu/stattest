+function ($) {
    'use strict';

    var LOADED = 1,
        datepicker_ru = {
            closeText: 'Закрыть',
            prevText: '&#x3c;Пред',
            nextText: 'След&#x3e;',
            currentText: 'Сегодня',
            monthNames: [
                'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ],
            monthNamesShort: [
                'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
                'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'
            ],
            dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
            dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            weekHeader: 'Не',
            dateFormat: 'yy-mm-dd',
            firstDay: 1,
            showMonthAfterYear: false,
            yearSuffix: ''
        };

    // перерендерить пришпиленную шапка таблицы
    window.reflowTableHeader = function () {
        var $table = $('.kv-grid-table');
        try {
            $table.floatThead && $table.floatThead('reflow');
        } catch (err) {
        }
    };

    $(function () {
        $('.panel-toggle-button')
            .on('click', function() {
                var $this = $(this),
                    $layoutLeft = $('.layout_left'),
                    $layoutMain = $('.layout_main');

                if ($this.hasClass('active')) {
                    $layoutLeft.animate({left: '-350px'});
                    $layoutMain.animate({left: 0}, function() {
                        $layoutMain.removeClass('col-sm-10 col-md-push-2').addClass('col-sm-12');
                        window.reflowTableHeader();
                    });
                    $this.text('›');
                    $this.removeClass('active');
                    $.get('/utils/layout/hide/'); // запомнить
                } else {
                    $layoutLeft.animate({left: 0});
                    $layoutMain.animate({left: '16.667%'}, function() {
                        $layoutMain.removeClass('col-sm-12').addClass('col-sm-10 col-md-push-2');
                        window.reflowTableHeader();
                    });
                    $this.text('‹');
                    $this.addClass('active');
                    $.get('/utils/layout/show/'); // запомнить
                }
            });

        $('.select2').select2({'width': '100%'});
        $.datepicker.setDefaults(datepicker_ru);
        $('.datepicker').datepicker();

        $('.layout_main , .layout_left, .panel-toggle-button').css('top', $('#top_search').closest('.row').height() + 25);
    })

}(jQuery);
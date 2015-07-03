<div class="row" style="margin-right: 0;">
    <div class="col-sm-12">
        <?= $this->render('block/main', ['client' => $client, 'active' => $account]); ?>
        <div class="row">
            <div class="col-sm-10">
                <?= $this->render('block/status', ['account' => $account, 'contractForm' => $contractForm]); ?>
                <?= $this->render('block/contact', ['account' => $account]); ?>
                <?= $this->render('block/document', ['account' => $account]); ?>
                <?= $this->render('block/file', ['account' => $account]); ?>
            </div>
            <div class="col-sm-2">
                <?= $this->render('block/rightmenu', ['account' => $account]); ?>
            </div>
        </div>
        <?= $this->render('block/trouble', ['troubles' => $troubles]); ?>
        <?= $this->render('block/service', ['services' => $services]); ?>

    </div>

    <?= $this->render('block/style'); ?>


    <script>
        d = false;
        $('.showFullTable').on('click', function () {
            $(this).parent().find('.fullTable').toggle();
        });
        var datepicker_ru = {
            closeText: 'Закрыть',
            prevText: '&#x3c;Пред',
            nextText: 'След&#x3e;',
            currentText: 'Сегодня',
            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
                'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
            dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
            dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            weekHeader: 'Не',
            dateFormat: 'yy-mm-dd',
            firstDay: 1,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $(document).ready(function () {
            $('.select2').select2();
            $.datepicker.setDefaults(datepicker_ru);
            $('.contract_datepicker').datepicker();
        });
    </script>
</div>


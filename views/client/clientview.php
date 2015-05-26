<div style="padding: 30px;" class="col-sm-10">
    <?= $this->render('block/main', ['sClient' => $sClient, 'activeClient' => $activeClient]); ?>
    <?= $this->render('block/status', ['client' => $activeClient, 'contractForm' => $contractForm]); ?>
    <?= $this->render('block/contact', ['client' => $activeClient]); ?>
    <?= $this->render('block/document', ['client' => $activeClient]); ?>
    <?= $this->render('block/trouble', ['troubles' => $troubles]); ?>
    <?= $this->render('block/service', ['client' => $activeClient, 'services' => $services]); ?>

</div>

<div style="
    padding: 10px;
    position: fixed;
    right: 20px;
    top: 0;
    border-left: 1px solid black;
    width: 14%;
    height: 100%;
    background: #eeeeee;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);

">
    <?= $this->render('block/rightmenu', ['client' => $activeClient]); ?>
</div>

<?= $this->render('block/style'); ?>


<script>
    d = false;
    $('.showFullTable').on('click', function () {
        $(this).next().find('.fullTable').toggle();
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

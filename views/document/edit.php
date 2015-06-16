<?php
\app\assets\TinymceAsset::register(Yii::$app->view);
?>

<form action="/document/edit?id=<?= $model->id ?>" method="post">
    <?= $model->type == 'contract' ? 'Договор' : ($model->type == 'agreement' ? 'Дополнительное соглашение' : 'Бланк заказа') ?>
    <?php if ($model->type != 'blank'): ?>
        №
        <input class="text" style="width:100px" type="text" name="contract_no"
               value="<?= $model->type == 'contract' ? $model->contract_no : $model->contract_dop_no ?>">
        от <input class="text contract_datepicker" style="width:180px" type="text" name="contract_date"
                  value="<?= $model->type == 'contract' ? date('d.m.Y', strtotime($model->contract_date)) : date('d.m.Y', strtotime($model->contract_dop_date)) ?>">
    <?php endif; ?>
    <br>Комментарий <input class="text" style="width:100px" type="text" name="comment" value="<?= $model->comment ?>">

    <textarea id="text" name="contract_content" style="width: 100%; margin: 0px; height: 600px;">
        <?= $content ?>
    </textarea>
    <button type="submit">Сохранить</button>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        tinymce.init({
            selector: "textarea",
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
        });
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
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        showMonthAfterYear: false,
        yearSuffix: ''
    };

    $('.contract_datepicker').datepicker(datepicker_ru);
</script>
<?php


use app\helpers\DateTimeZoneHelper;
use app\modules\mchs\models\MchsMessage;
use yii\widgets\Breadcrumbs;

echo Breadcrumbs::widget([
    'links' => [
        'Главная',
        ['label' => $this->title = 'Сообщение от МЧС', 'url' => '/news/mchs'],
    ],
]);


\yii\bootstrap\ActiveForm::begin(['id' => 'sendForm']);
echo \app\classes\Html::hiddenInput('doSend', 1);

?>

<div class="well col-sm-6">
    <div class="row">
        <div class="form-group">
            <label for="message">Текст сообщения</label>
            <textarea class="form-control" name="message" id="message" placeholder="Введите сообщение..."
                      rows="5" maxlength="500"><?= htmlspecialchars($message) ?></textarea>
            <p class="help-block" id="message_length"></p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10">&nbsp;</div>
        <div class="col-sm-2">
            <button type="button" class="btn btn-danger" id="send_btn">Отправить</button>
        </div>
    </div>
</div>
<div class="row"></div>
<div class="row">
    <?php
    \yii\bootstrap\ActiveForm::end();


    echo \app\classes\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'message',
            [
                'attribute' => 'date',
                'value' => function (MchsMessage $data) {
                    return DateTimeZoneHelper::getDateTime($data->date);
                }
            ],
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function (MchsMessage $data) {
                    return $data->user->name;
                },
            ],
            'status',
        ],

    ]);

    ?>
</div>
<script>
  $(document).ready(function () {

    $('#message').on('focusout input propertychange ', function (event) {
      var messageStr = $(this).val().replace(/\s/g, ' ').replace(/ +/g, ' ');

      $('#message_length').text('Длина сообщения: ' + messageStr.trim().length);

      if (messageStr != $(this).val()) {
        $(this).val(messageStr);

        event.preventDefault();
      }
    }).trigger('input');

    $('#send_btn').click(function () {
      var messageStr = $('#message').val().trim();
      $('#message').val(messageStr);

      if (confirm('Вы уверены, что хотите отправить сообщение: \n' + messageStr + '\n  ???') && confirm('Точно уверены?')) {
        $('#sendForm').submit();
      }
    });
  });
</script>
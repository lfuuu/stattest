<?php

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use app\classes\Html;

/** @var $model \app\forms\buh\PaymentYandexTransfer */

echo Html::formLabel($this->title = 'Перенос платежей');

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Бухгалтерия'],
        ['label' => $this->title, 'url' => '/payment/yandex-transfer'],
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="col-sm-2">
        <?= $form->field($model, 'from_client_id')->textInput(['disabled' => true]) ?>
    </div>
    <div class="col-sm-8">
        <?= $form->field($model, 'payment_id')->dropDownList(['0' => '----'] + $model->getPayments(), ['class' => 'select2']) ?>
    </div>
    <div class="col-sm-2">
        <?= $form->field($model, 'to_client_id')->textInput() ?>
    </div>

    <div class="col-sm-10">
        &nbsp;
    </div>

    <?= $this->render('//layouts/_submitButton', [
        'text' => 'Перенести платеж',
        'glyphicon' => 'glyphicon-transfer',
        'params' => [
            'class' => 'btn btn-primary'
        ],
    ]) ?>

    <?php ActiveForm::end(); ?>

</div>

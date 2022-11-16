<?php

/** @var $client ClientAccount */
/** @var $model PaymentForm */

use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\ClientAccount;
use app\forms\buh\PaymentForm;
use app\models\Currency;
use app\models\Payment;

echo Html::formLabel('Новый платеж');
echo Breadcrumbs::widget([
    'links' => [
        'Новый платеж'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'payment_date' => ['type' => Form::INPUT_TEXT],
            'original_sum' => ['type' => Form::INPUT_TEXT, 'options' => ['id' => 'payment_original_sum']],
            'sum' => ['type' => Form::INPUT_TEXT, 'options' => ['id' => 'payment_sum']],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'oper_date' => ['type' => Form::INPUT_TEXT],
            'original_currency' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Currency::map(), 'options' => ['id' => 'payment_original_currency']],
            'payment_rate' => ['type' => Form::INPUT_TEXT, 'options' => ['id' => 'payment_rate']],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 6,
        'attributes' => [
            'payment_no' => ['type' => Form::INPUT_TEXT, 'options' => ['id' => 'payment_no'], 'columnOptions' => ['colspan' => 2],],
            'bill_no' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => ['' => '-- Привязать к счету --'] + $model->getAvailableBills(), 'options' => ['class' => 'select2'], 'columnOptions' => ['colspan' => 2]],
            'currency' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Currency::map(), 'options' => ['id' => 'payment_currency', 'disabled'=>'disabled']],
            'payment_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Payment::$paymentTypes,],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Payment::$types, 'options' => ['id' => 'payment_type']],
            'bank' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => ['' => '-- Банк --'] + Payment::$banks, 'options' =>['id' => 'payment_bank', 'disabled'=>'disabled']],
            'ecash_operator' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => ['' => '-- Оператор --'] + Payment::$ecash, 'options' =>['id' => 'payment_ecash', 'disabled'=>'disabled']],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'comment' => ['type' => Form::INPUT_TEXT],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'client_id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'client_id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'history.back();',
                        ]) .
                        Html::submitButton('Добавить платеж', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);
    ActiveForm::end();
    ?>
</div>
<?php
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\ClientAccount;
use app\forms\buh\PaymentForm;
use app\models\Currency;
use app\models\Payment;
use kartik\widgets\DatePicker;
/** @var $client ClientAccount */
/** @var $model PaymentForm */

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
        'columns' => 3,
        'attributes' => [
            'payment_no' => ['type' => Form::INPUT_TEXT],
            'bill_no' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => ['' => '-- Привязать к счету --'] + $model->getAvailableBills(), 'options' => ['class' => 'select2']],
            'currency' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Currency::map(), 'options' => ['id' => 'payment_currency', 'disabled'=>'disabled']],
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
<script>
    $('#payment_original_currency').on('change', function(){
        var originalCurrency = $('#payment_original_currency').val();
        var currency = $('#payment_currency').val();
        $.get('/currency/get-rate', {from:originalCurrency,to:currency}, function(data) {
            $('#payment_rate').val(data);
            $('#payment_rate').trigger('change');
        })
    });
    $('#payment_original_sum').on('change', function(){
        var originalSum = $('#payment_original_sum').val();
        var paymentRate = $('#payment_rate').val();
        var sum = originalSum / paymentRate;
        $('#payment_sum').val(sum.toFixed(2));
    });
    $('#payment_sum').on('change', function(){
        var originalSum = $('#payment_original_sum').val();
        var sum = $('#payment_sum').val();
        var paymentRate = originalSum / sum;
        $('#payment_rate').val(paymentRate.toFixed(4));
    });
    $('#payment_rate').on('change', function(){
        var originalSum = $('#payment_original_sum').val();
        var paymentRate = $('#payment_rate').val();
        var sum = originalSum / paymentRate;
        $('#payment_sum').val(sum.toFixed(2));
    });
    $('#payment_type').on('change', function(){
        var type = $('#payment_type').val();

        if (type == 'bank') {
            $('#payment_bank').removeAttr('disabled');
        } else {
            $('#payment_bank').attr('disabled','disabled');
        }

        if (type == 'ecash') {
            $('#payment_ecash').removeAttr('disabled');
        } else {
            $('#payment_ecash').attr('disabled','disabled');
        }
    });

    $('#payment_original_currency').trigger('change');
    $('#payment_type').trigger('change');
</script>
<?php

use app\classes\Html;
use app\widgets\DatePicker;

$form = \kartik\form\ActiveForm::begin(['action' => '/uu/account-tariff/disable-all']);
echo $form->field($model, 'clientAccountId')->hiddenInput();
?>
    <div class="well">
    <div class="text-danger">Введя код вы подтверждаете, что хотите отключить все У-услуги
        на <?= $clientAccount->getLink() ?></div>
    <div class="row">
        <div class="col-sm-2"><h3>Введите код: </h3>
            <h1 style="    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;"><?= $code ?></h1></div>

        <div class="col-sm-4"><?= $form->field($model, 'code')->textInput() ?></div>
        <div class="col-sm-2">
            <div class="form-group field-disableform-date required">
                <label class="control-label" for="disableform-date">Дата отключения</label>


                <?php echo DatePicker::widget([
                    'name' => 'DisableForm[date]',
                    'type' => DatePicker::TYPE_INPUT,
                    'value' => date('Y-m-01', strtotime('first day of next month')),
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true,
                        'startDate' => '0'
                    ],
                    'options' => [
                        'class' => 'process-datepicker1',
                    ],
                ]); ?>
            </div>
        </div>
        <div class="col-sm-2"><?= Html::submitButton(
                Html::tag('i', '', [
                    'class' => 'glyphicon glyphicon-trash',
                    'aria-hidden' => 'true',
                ]) .
                ' Отключить(!)',
                [
                    'class' => 'btn btn-danger',
                    'data-old-tariff-period-id' => $formModel->accountTariff->tariff_period_id,
                ]
            ) ?></div>
    </div>

<?php
\kartik\form\ActiveForm::end();
?>
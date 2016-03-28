<?php
/**
 * Строчка
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\widgets\ActiveForm;

$accountTariffLog = $formModel->accountTariffLog;
?>

<div class="row">
    <div class="col-sm-6">
        <?= $form->field($accountTariffLog, 'tariff_period_id')
            ->widget(Select2::className(), [
                'data' => $formModel->getAvailableTariffPeriods(true),
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($accountTariffLog, 'actual_from')->widget(DatePicker::className(), [
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true,
            ]
        ]) ?>
    </div>
</div>

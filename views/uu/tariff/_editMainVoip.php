<?php
/**
 * свойства тарифа для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\uu\TariffController;
use kartik\select2\Select2;

$tariff = $formModel->tariff;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<div class="well">
    <div class="row">

        <div class="col-sm-4">
            <?= $form->field($tariff, 'voip_tarification_free_seconds')->textInput($options) ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariff, 'voip_tarification_interval_seconds')->textInput($options) ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariff, 'voip_tarification_type')
                ->widget(Select2::className(), [
                    'data' => [2 => 'В большую сторону (ceil)', 1 => 'Математическое округление (round)'],
                    'options' => $options,
                ]) ?>
        </div>

    </div>

    <?= $this->render('_editMainVoipCity', [
        'formModel' => $formModel,
        'form' => $form,
        'editableType' => $editableType,
    ]) ?>

</div>
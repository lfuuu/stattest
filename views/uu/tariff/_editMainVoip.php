<?php
/**
 * свойства тарифа для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\TariffVoipTarificate;
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
            <?= $form->field($tariff, 'voip_tarificate_id')
                ->widget(Select2::className(), [
                    'data' => TariffVoipTarificate::getList(),
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
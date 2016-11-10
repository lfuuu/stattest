<?php
/**
 * свойства тарифа для "VM collocation"
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\TariffVm;
use app\controllers\uu\TariffController;
use kartik\select2\Select2;

$tariff = $formModel->tariff;
$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
    'editableType' => $editableType,
];

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<div class="well">
    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($tariff, 'vm_id')
                ->widget(Select2::className(), [
                    'data' => TariffVm::getList(true),
                    'options' => $options,
                ]) ?>
        </div>
    </div>
</div>

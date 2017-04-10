<?php
/**
 * свойства тарифа для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\uu\controllers\TariffController;

$tariff = $formModel->tariff;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<div class="well">
    <?= $this->render('_editMainVoipCity', [
        'formModel' => $formModel,
        'form' => $form,
        'editableType' => $editableType,
    ]) ?>
</div>
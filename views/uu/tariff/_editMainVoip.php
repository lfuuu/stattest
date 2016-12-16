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
    <?= $this->render('_editMainVoipCity', [
        'formModel' => $formModel,
        'form' => $form,
        'editableType' => $editableType,
    ]) ?>

</div>
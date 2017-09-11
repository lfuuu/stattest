<?php
/**
 * свойства тарифа для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

$tariff = $formModel->tariff;

$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
    'editableType' => $editableType,
];

?>

<div class="well">
    <?= $this->render('_editMainVoipCity', $viewParams) ?>
    <?= $this->render('_editMainVoipNdcType', $viewParams) ?>
</div>
<?php

/**
 * Создание/редактирование DID-группы
 *
 * @var \app\classes\BaseView $this
 * @var RewardClientContractFormEdit $formModel
 * @var RewardClientContractService $serviceRewards
 */

use kartik\form\ActiveForm as FormActiveForm;
?>

<?php
$form = FormActiveForm::begin();
$this->registerJsVariable('formId', $form->getId());

$viewParams = [
    'formModel' => $formModel,
    'serviceRewards' => $serviceRewards,
    'form' => $form,
];
?>

<?php
// сообщение об ошибке
if ($formModel->validateErrors) {
    Yii::$app->session->setFlash('error', $formModel->validateErrors);
}
?>

<?= $this->render('_editRewardServices', $viewParams) ?>
<?php FormActiveForm::end(); ?>

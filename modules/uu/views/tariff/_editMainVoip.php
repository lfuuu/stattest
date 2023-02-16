<?php
/**
 * свойства тарифа для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\uu\models\ServiceType;

$tariff = $formModel->tariff;

$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
    'editableType' => $editableType,
];

?>

<div class="well">
    <h2>Телефония <?= $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP)) ?></h2>
    <?= $this->render('_editMainVoipCountry', $viewParams) ?>
    <?= $this->render('_editMainVoipCity', $viewParams) ?>
    <?= $this->render('_editMainVoipNdcType', $viewParams) ?>
    <?= $this->render('_editMainVoipSource', $viewParams) ?>
</div>
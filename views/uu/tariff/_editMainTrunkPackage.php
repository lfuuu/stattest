<?php
/**
 * свойства тарифа для "Транк. Пакеты"
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\uu\TariffController;

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

<?= $this->render('_editMainVoipPackageMinute', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePrice', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePricelist', $viewParams) ?>
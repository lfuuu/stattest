<?php
/**
 * свойства тарифа для "Транк. Пакеты"
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\nnp\models\Package;
use app\modules\uu\controllers\TariffController;

$tariff = $formModel->tariff;
$package = $tariff->package;
if (!$package) {
    $package = new Package;
}

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
        <?= $this->render('_editMainTarification', ['form' => $form, 'package' => $package, 'options' => $options]) ?>
    </div>

<?= $this->render('_editMainVoipPackageMinute', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePrice', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePricelist', $viewParams) ?>
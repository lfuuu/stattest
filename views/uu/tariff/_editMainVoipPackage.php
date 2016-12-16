<?php
/**
 * свойства тарифа для "телефонии. Пакеты"
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\TariffVoipGroup;
use app\controllers\uu\TariffController;
use app\modules\nnp\models\Package;
use kartik\select2\Select2;

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

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($package, 'tarification_free_seconds')->textInput($options) ?>
            </div>

            <div class="col-sm-3">
                <?= $form->field($package, 'tarification_min_paid_seconds')->textInput($options) ?>
            </div>

            <div class="col-sm-3">
                <?= $form->field($package, 'tarification_interval_seconds')->textInput($options) ?>
            </div>

            <div class="col-sm-3">
                <?= $form->field($package, 'tarification_type')
                    ->widget(Select2::className(), [
                        'data' => [Package::TARIFICATION_TYPE_CEIL => 'В большую сторону (ceil)', Package::TARIFICATION_TYPE_ROUND => 'Математическое округление (round)'],
                        'options' => $options,
                    ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($tariff, 'voip_group_id')
                    ->widget(Select2::className(), [
                        'data' => TariffVoipGroup::getList(true),
                        'options' => $options,
                    ]) ?>
            </div>
        </div>

        <?= $this->render('_editMainVoipCity', $viewParams) ?>
    </div>

<?= $this->render('_editMainVoipPackageMinute', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePrice', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePricelist', $viewParams) ?>
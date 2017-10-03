<?php
/**
 * свойства тарифа для "телефонии. Пакеты"
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\nnp\models\Package;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\TariffVoipGroup;
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
                <?= $form->field($tariff, 'voip_group_id')
                    ->widget(Select2::className(), [
                        'data' => TariffVoipGroup::getList(true),
                    ]) ?>
            </div>

            <div class="col-sm-3">
                <?= $form->field($package, 'is_termination')->checkbox($options) ?>
            </div>

        </div>

        <?= $this->render('_editMainTarification', ['form' => $form, 'package' => $package, 'options' => $options]) ?>
        <?= $this->render('_editMainVoipCity', $viewParams) ?>
        <?= $this->render('_editMainVoipNdcType', $viewParams) ?>

    </div>

<?= $this->render('_editMainVoipPackageMinute', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePrice', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePricelist', $viewParams) ?>
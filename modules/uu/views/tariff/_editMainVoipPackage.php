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
use app\modules\uu\models\ServiceType;
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

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));
?>

    <div class="well">
        <h2>
            Телефония. Пакет звонков
            <?= $helpConfluence ?>
        </h2>
        <?= $this->render('_editMainVoipCountry', $viewParams) ?>
        <div class="row">

            <div class="col-sm-3">
                <?= $form->field($tariff, 'voip_group_id')
                    ->widget(Select2::class, [
                        'data' => TariffVoipGroup::getList(true),
                    ])
                    ->label($tariff->getAttributeLabel('voip_group_id') . $helpConfluence)
                ?>
            </div>

            <div class="col-sm-3">
                <?= $form->field($package, 'is_termination')
                    ->checkbox(['label' => $package->getAttributeLabel('is_termination') . $helpConfluence] + $options)
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($package, 'is_inversion_mgp')
                    ->checkbox([
                            'label' => $package->getAttributeLabel('is_inversion_mgp') . $helpConfluence,
                        ] + $options
                    )
                ?>
            </div>

        </div>

        <?= $this->render('_editMainTarification', ['form' => $form, 'package' => $package, 'options' => $options]) ?>
        <?= $this->render('_editMainVoipCity', $viewParams) ?>
        <?= $this->render('_editMainVoipNdcType', $viewParams) ?>
        <?= $this->render('_editMainLocation', ['form' => $form, 'package' => $package, 'options' => $options]) ?>

    </div>

<?= $this->render('_editMainVoipPackageMinute', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePrice', $viewParams) ?>
<?= $this->render('_editMainVoipPackagePricelist', $viewParams) ?>
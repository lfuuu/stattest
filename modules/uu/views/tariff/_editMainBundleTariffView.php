<?php
/**
 * Бандл-тариф
 *
 * @var BaseView $this
 * @var TariffForm $formModel
 * @var ActiveForm $form
 * @var int $editableType
 */

use app\classes\BaseView;
use app\models\Organization;
use app\modules\uu\forms\TariffForm;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffBundle;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$tariffBundle = $formModel->tariffBundles;

$tariff = $formModel->tariff;
$list = [];
foreach ($tariff->bundlePackages as $bundlePackage) {
    $list[$bundlePackage->package_tariff_id] = '#' . $bundlePackage->package_tariff_id . ' ' . $bundlePackage->packageTariff;
}

$tariffBundleTableName = TariffBundle::tableName();
$tariffTableName = Tariff::tableName();

?>

<div class="row">
    <div class="col-sm-12">

        <label><?= Yii::t('models/' . $tariffBundleTableName, 'package_tariff_id') ?></label>
        <?= Select2::widget([
            'name' => 'TariffBundle[]',
            'value' => array_keys($list),
            'data' => $list,
            'options' => [
                'multiple' => true,
                'disabled' => true,
            ]
        ]) ?>

    </div>
</div>

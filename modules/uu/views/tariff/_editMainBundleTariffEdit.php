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

$tariffBundles = $formModel->tariffBundles;
$tariffs = [];
foreach ($tariffBundles as $tariffBundle) {
    $tariffs[] = $tariffBundle->tariff_id;
}

$tariff = $formModel->tariff;
$where = [
        'currency_id' => $tariff->currency_id,
        'is_include_vat' => $tariff->is_include_vat,
        'is_bundle' => 1,
];

$tariffList = Tariff::getList(false, false, \app\modules\uu\models\ServiceType::$packages[$tariff->service_type_id], $where);

$tariffBundleTableName = TariffBundle::tableName();
$tariffTableName = Tariff::tableName();

?>

<div class="row">
    <div class="col-sm-12">

        <label><?= Yii::t('models/' . $tariffBundleTableName, 'tariff_id') ?></label>
        <?= Select2::widget([
            'name' => 'TariffBundle[]',
            'value' => $tariffs,
            'data' => $tariffList,
            'options' => [
                'multiple' => true,
            ],
        ]) ?>

        <?php if (!$tariff->isNewRecord) : ?>
            <?= $this->render('//layouts/_showHistory', [
                'parentModel' => [new TariffBundle(), $tariff->id],
            ]) ?>
        <?php endif; ?>

    </div>
</div>

<?php
/**
 * Организации тарифа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\models\Organization;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use kartik\select2\Select2;

$tariffOrganizations = $formModel->tariffOrganizations;
$organizationList = Organization::dao()->getList($isWithEmpty = false);
$tariff = $formModel->tariff;

$tariffOrganizationTableName = TariffOrganization::tableName();
$tariffTableName = Tariff::tableName();
?>

<div class="row">
    <div class="col-sm-12">

        <label><?= Yii::t('models/' . $tariffOrganizationTableName, 'organization_id') ?></label>
        <?= Select2::widget([
            'name' => 'TariffOrganization[]',
            'value' => array_keys($tariffOrganizations),
            'data' => $organizationList,
            'options' => [
                'multiple' => true,
            ],
        ]) ?>

        <?php if (!$tariff->isNewRecord) : ?>
            <?= $this->render('//layouts/_showHistory', ['model' => $tariffOrganizations, 'deleteModel' => [new TariffOrganization(), 'tariff_id', $tariff->id]]) ?>
        <?php endif; ?>

    </div>
</div>

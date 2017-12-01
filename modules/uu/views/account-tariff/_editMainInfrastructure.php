<?php
/**
 * Свойства услуги для инфраструктуры
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\models\City;
use app\modules\uu\models\AccountTariff;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::className(), [
                'data' => City::getList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // проект ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'infrastructure_project')
            ->widget(Select2::className(), [
                'data' => AccountTariff::getInfrastructureProjectList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // уровень ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'infrastructure_level')
            ->widget(Select2::className(), [
                'data' => AccountTariff::getInfrastructureLevelList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // цена ?>
    <div class="col-sm-6">
        <?= $form->field($accountTariff, 'price')
            ->input('number', ['step' => 0.01]) ?>
    </div>
</div>


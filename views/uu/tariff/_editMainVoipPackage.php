<?php
/**
 * свойства тарифа для "телефонии. Пакеты"
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\uu\model\TariffVoipGroup;
use kartik\select2\Select2;

$tariff = $formModel->tariff;
$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
];

?>

<div class="well">
    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($tariff, 'voip_group_id')
                ->widget(Select2::className(), [
                    'data' => TariffVoipGroup::getList(true),
                ]) ?>
        </div>
    </div>

    <?= $this->render('_editMainVoipCity', $viewParams) ?>
</div>

<div class="row">
    <div class="col-sm-4">
        <?= $this->render('_editMainVoipPackageMinute', $viewParams) ?>
    </div>
    <div class="col-sm-4">
        <?= $this->render('_editMainVoipPackagePrice', $viewParams) ?>
    </div>
    <div class="col-sm-4">
        <?= $this->render('_editMainVoipPackagePricelist', $viewParams) ?>
    </div>
</div>
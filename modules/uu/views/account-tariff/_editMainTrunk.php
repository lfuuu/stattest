<?php
/**
 * Свойства услуги для транка
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\ReturnFormatted;
use app\models\billing\Trunk;
use app\modules\uu\models\AccountTariff;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

// при смене точки подключение обновить список транков
$this->registerJsVariable('format', ReturnFormatted::FORMAT_OPTIONS);

$accountTariff = $formModel->accountTariff;
if (!$accountTariff->isNewRecord) {
    // Здесь нечего делать. Можно только отредактировать "логический транк" в другом интерфейсе
    return;
}
?>

<div class="row">

    <?php // мега/мульти транк ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'trunk_type_id')
            ->widget(Select2::className(), [
                'data' => AccountTariff::getTrunkTypeList(true),
            ]) ?>
    </div>

    <?php // транк ?>
    <div class="col-sm-2">
        <label class="control-label" for="accounttariff-trunk_id">Транк</label>
        <?= Select2::widget(
            [
                'id' => 'accounttariff-trunk_id',
                'name' => 'trunkId',
                'data' => Trunk::dao()->getList(['serverIds' => $accountTariff->region_id], $isWithEmpty = true),
            ]
        ) ?>
        <div class="help-block"></div>
    </div>

</div>

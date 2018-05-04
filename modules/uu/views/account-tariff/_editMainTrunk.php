<?php
/**
 * Свойства услуги для транка
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\classes\ReturnFormatted;
use app\models\billing\Trunk;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

// при смене точки подключение обновить список транков
$this->registerJsVariable('format', ReturnFormatted::FORMAT_OPTIONS);

$accountTariff = $formModel->accountTariff;
$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_TRUNK));
?>

<div class="row">

    <?php // мега/мульти транк ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'trunk_type_id')
            ->widget(Select2::className(), [
                'data' => AccountTariff::getTrunkTypeList(true),
                'disabled' => !$accountTariff->isNewRecord,
            ])
            ->label($accountTariff->getAttributeLabel('trunk_type_id') . $helpConfluence)
        ?>
    </div>

    <?php // транк ?>
    <div class="col-sm-2">
        <label class="control-label" for="accounttariff-trunk_id">Транк <?= $helpConfluence ?></label>
        <?= Select2::widget(
            [
                'id' => 'accounttariff-trunk_id',
                'name' => 'trunkId',
                'data' => Trunk::dao()->getList(['serverIds' => $accountTariff->region_id], $isWithEmpty = true),
                'disabled' => !$accountTariff->isNewRecord,
                'value' => (!$accountTariff->isNewRecord && $accountTariff->usageTrunk) ? $accountTariff->usageTrunk->trunk_id : '',
            ]
        ) ?>
        <div class="help-block"></div>
    </div>

    <?php if (!$accountTariff->isNewRecord) : ?>
        <div class="col-sm-2">
            <?= Html::a('<span class="glyphicon glyphicon-random" aria-hidden="true"></span> Маршрутизация', ['/usage/trunk/edit', 'id' => $accountTariff->id]) . $helpConfluence ?>
        </div>
    <?php endif ?>

</div>

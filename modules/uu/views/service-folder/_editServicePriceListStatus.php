<?php

/**
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\ServiceFolderForm $formModel
 *
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\models\PriceLevel;
use app\modules\uu\models\TariffStatus;

$priceLevelStatuses = $formModel->priceLevelStatuses->all();
$priceLevelPackageStatuses = null;

$isWithPackage = array_key_exists($formModel->service_type_id, \app\modules\uu\models\ServiceType::$serviceToPackage);

$tariffStatusList = TariffStatus::getList($formModel->service_type_id, $isWithEmpty = false)
?>
<div class="well">
    <?php
    $this->registerJsVariable('formId', $form->getId());

    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];

    ?>
    <div class="row">
        <div class="col-sm-2"><label>Уровен цен</label></div>
        <div class="col-sm-3"><label>Тариф</label></div>
        <?php if ($isWithPackage) { ?>
        <div class="col-sm-3"><label>Пакет</label></div>
        <?php } ?>
    </div>
    <?php

    $priceLevelList = PriceLevel::getList();
    $toSave = [];
    foreach ($priceLevelList as $priceLevelId => $priceLevel) :
        ?>
        <div class="row">
            <div class="col-sm-2">
                <?= $priceLevel ?>
            </div>
            <?php
            $serviceFolder = $priceLevelStatuses[$priceLevelId] ?:
                (new \app\modules\uu\models\ServiceTypeFolder([
                    'service_type_id' => $formModel->service_type_id,
                    'price_level_id' => $priceLevelId,
                    'tariff_status_main_id' => TariffStatus::ID_PUBLIC,
                ] + ($isWithPackage ? ['tariff_status_package_id' => TariffStatus::ID_PUBLIC] : [])));

            ?>

            <div class="col-sm-3">
                <?= $form->field($serviceFolder, "[".$priceLevelId."]tariff_status_main_id")
                    ->dropDownList($tariffStatusList)
                    ->label(false) ?>
            </div>
            <?php if ($isWithPackage) { ?>
            <div class="col-sm-3">
                <?= $form->field($serviceFolder, "[{$priceLevelId}]tariff_status_package_id")
                    ->dropDownList($tariffStatusList)
                    ->label(false) ?>
            </div>
            <?php }; ?>

        </div>
    <?php endforeach; ?>

</div>
<?php

/**
 * @var \app\classes\BaseView $this
 * @var \app\forms\tariff\DidGroupFormEdit $formModel
 *
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\DidGroupController;
use app\models\DidGroup;
use app\models\PriceLevel;
use app\modules\uu\models\TariffStatus;

$didGroup = $formModel->didGroup;
$didGroupPriceLevelModel = $formModel->didGroupPriceLevels;

?>
<div class="well">
    <?php
    $this->registerJsVariable('formId', $form->getId());

    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];

    ?>
    <h2>Уровни цен для DID групп</h2>
    <div class="row">
        <div class="col-sm-1 col-sm-offset-1"><label>Подключение, ¤</label></div>
        <div class="col-sm-1"><label for="didgroup_label_is_with_discount">Подключение <br>со скидкой, ¤</label></div>
        <div class="col-sm-3"><label>Тариф</label></div>
        <div class="col-sm-3"><label>Пакет</label></div>
        <div class="col-sm-3"><label>Пакет за красивость</label></div>
    </div>
    <?php

    $priceLevelList =  PriceLevel::getList();
    $toSave = [];
    foreach ($priceLevelList as $id => $priceLevel) :
    ?>
        <div class="row">
            <div class="col-sm-1">
                <?= $priceLevel ?>
            </div>
            <?php
            foreach ($didGroupPriceLevelModel as $index => $didGroupPriceLevel) :
            ?>
                <?php if ($didGroupPriceLevel['price_level_id'] == $id) : ?>
                    <div class="col-sm-1">
                        <?= $form->field($didGroupPriceLevel, "[$index]price")->input('number', ['step' => 0.01])->label(false) ?>
                    </div>

                    <div class="col-sm-1">
                        <?= $form->field($didGroupPriceLevel, "[$index]price_discounted")->input('number', ['step' => 0.01])->label(false) ?>
                    </div>

                    <div class="col-sm-3">
                        <?= $form->field($didGroupPriceLevel, "[$index]tariff_status_main_id")
                            ->dropDownList(TariffStatus::getList($serviceTypeId = null, $isWithEmpty = false))
                            ->label(false) ?>
                    </div>

                    <div class="col-sm-3">
                        <?= $form->field($didGroupPriceLevel, "[$index]tariff_status_package_id")
                            ->dropDownList(TariffStatus::getList($serviceTypeId = null, $isWithEmpty = false))
                            ->label(false) ?>
                    </div>

                    <div class="col-sm-3">
                        <?php
                        if ($index < DidGroup::MIN_PRICE_LEVEL_FOR_BEAUTY) {
                            echo '-';
                        } elseif ($index === DidGroup::MIN_PRICE_LEVEL_FOR_BEAUTY) {
                            echo $form->field($didGroup, 'tariff_status_beauty')
                                ->dropDownList(TariffStatus::getList($serviceTypeId = null, $isWithEmpty = true))
                                ->label(false);
                        } else {
                            echo '—«—';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
    <?php endforeach; ?>

</div>
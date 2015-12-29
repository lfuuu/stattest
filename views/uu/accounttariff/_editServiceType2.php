<?php
/**
 * свойства услуги для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\Html;
use app\models\Country;
use app\models\Number;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
?>

<div class="row">

    <div class="col-sm-3">
        <?php // страна ?>
        <?= $form->field($accountTariff, 'voip_country_id')// @todo
        ->widget(Select2::className(), [
            'data' => Country::getList(true),
            'options' => [
                'id' => 'voipCountryId',
            ],
        ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // тип номера ?>
        <?= $form->field($accountTariff, 'voip_number_type')// @todo
        ->widget(Select2::className(), [
            'data' => [], //TariffVoip::getTypesByCountryId($accountTariff->voip_country_id, true),
            'options' => [
                'disabled' => true,
                'id' => 'voipNumberType',
            ],
        ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // регион (город) ?>
        <?= $form->field($accountTariff, 'voip_regions')// @todo
        ->widget(Select2::className(), [
            'data' => [], //City::dao()->getList(true, $accountTariff->voip_country_id),
            'options' => [
                'disabled' => true,
                'id' => 'voipRegions',
            ],
        ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // тип красивости ?>
        <?= $form->field($accountTariff, 'voip_did_group')// @todo
        ->widget(Select2::className(), [
            'data' => [], // DidGroup::dao()->getList(true, $accountTariff->voip_regions),
            'options' => [
                'disabled' => true,
                'id' => 'voipDidGroup',
            ],
        ]) ?>
    </div>

</div>

<div class="row">

    <div class="col-sm-3">
        <?php // кол-во столбцов ?>
        <?= $form->field($accountTariff, 'voip_numbers_list_class')// @todo
        ->widget(Select2::className(), [
            'data' => [ // класс bootstrap, соотвествующий кол-ву столбцов
                12 => 1,
                6 => 2,
                4 => 3,
                3 => 4,
                2 => 6,
                1 => 12,
            ],
            'options' => [
                'id' => 'voipNumbersListClass',
            ],
        ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // сортировка (поле) ?>
        <?php $number = new Number(); ?>
        <?= $form->field($accountTariff, 'voip_numbers_list_order_by_field')// @todo
        ->widget(Select2::className(), [
            'data' => [
                'number' => $number->getAttributeLabel('number'),
                'beauty_level' => $number->getAttributeLabel('beauty_level'),
            ],
            'options' => [
                'id' => 'voipNumbersListOrderByField',
            ],
        ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // сортировка (тип) ?>
        <?= $form->field($accountTariff, 'voip_numbers_list_order_by_type')// @todo
        ->widget(Select2::className(), [
            'data' => [
                SORT_ASC => Yii::t('common', 'Ascending'),
                SORT_DESC => Yii::t('common', 'Descending'),
            ],
            'options' => [
                'id' => 'voipNumbersListOrderByType',
            ],
        ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // маска ?>
        <?= $form->field($accountTariff, 'voip_numbers_list_mask')// @todo
        ->input('string', ['id' => 'voipNumbersListMask']) ?>
        Допустимы цифры, _ (одна любая цифра), % (любая последовательность цифр)
    </div>

</div>

<div id="voipNumbersListSelectAll">
    <?= Html::checkbox('voipNumbersListSelectAll', false, [
        'label' => Yii::t('common', 'Select all'),
    ]) ?>
</div>

<div id="voipNumbersList"></div>

<script type="text/javascript" src="/js/uu/accounttariffEdit.js"></script>


<?php
/**
 * свойства услуги для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariffVoip;
use app\classes\uu\model\Tariff;
use app\models\City;
use app\models\Country;
use app\models\Number;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariffVoip = new AccountTariffVoip();
$accountTariffVoip->voip_country_id = $formModel->accountTariff->clientAccount->country_id;

?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">

    <div class="col-sm-3" title="Страна берется от страны клиента">
        <?php // страна ?>
        <?= $form->field($accountTariffVoip, 'voip_country_id')
            ->widget(Select2::className(), [
                'data' => Country::getList(true),
                'options' => [
                    'disabled' => true,
                    'id' => 'voipCountryId',
                ],
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // тип номера ?>
        <?= $form->field($accountTariffVoip, 'voip_number_type')
            ->widget(Select2::className(), [
                'data' => Tariff::getVoipTypesByCountryId($accountTariffVoip->voip_country_id, true), // страна выбрана от клиента
                'options' => [
                    'id' => 'voipNumberType',
                ],
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // регион (город) ?>
        <?= $form->field($accountTariffVoip, 'voip_regions')
            ->widget(Select2::className(), [
                'data' => City::dao()->getList(true, $accountTariffVoip->voip_country_id), // страна выбрана от клиента
                'options' => [
                    'disabled' => true,
                    'id' => 'voipRegions',
                ],
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // тип красивости ?>
        <?= $form->field($accountTariffVoip, 'voip_did_group')
            ->widget(Select2::className(), [
                'data' => [], // DidGroup::dao()->getList(true, $accountTariffVoip->voip_regions),
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
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_class')
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
                    'disabled' => true,
                    'id' => 'voipNumbersListClass',
                ],
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // сортировка (поле) ?>
        <?php $number = new Number(); ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_order_by_field')
            ->widget(Select2::className(), [
                'data' => [
                    'number' => $number->getAttributeLabel('number'),
                    'beauty_level' => $number->getAttributeLabel('beauty_level'),
                ],
                'options' => [
                    'disabled' => true,
                    'id' => 'voipNumbersListOrderByField',
                ],
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // сортировка (тип) ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_order_by_type')
            ->widget(Select2::className(), [
                'data' => [
                    SORT_ASC => Yii::t('common', 'Ascending'),
                    SORT_DESC => Yii::t('common', 'Descending'),
                ],
                'options' => [
                    'disabled' => true,
                    'id' => 'voipNumbersListOrderByType',
                ],
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?php // маска ?>
        <?= $this->render('//layouts/_helpMysqlLike'); ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_mask')
            ->input('string', [
                'disabled' => true,
                'id' => 'voipNumbersListMask',
            ]) ?>
    </div>

</div>

<div id="voipNumbersListSelectAll" style="display: none;">
    <?= Html::checkbox('voipNumbersListSelectAll', false, [
        'label' => Yii::t('common', 'Select all'),
    ]) ?>
</div>

<div id="voipNumbersList"></div>

<?php ActiveForm::end(); ?>

<script type="text/javascript" src="/js/uu/accounttariffEdit.js"></script>


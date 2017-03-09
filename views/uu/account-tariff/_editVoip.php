<?php
/**
 * свойства услуги для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariffVoip;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\models\City;
use app\models\Country;
use app\models\Number;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

$clientAccount = $formModel->accountTariff->clientAccount;
$accountTariffVoip = new AccountTariffVoip();
$accountTariffVoip->voip_country_id = $clientAccount->country_id;

?>

<?php $form = ActiveForm::begin([
    'id' => 'addAccountTariffVoipForm',
]); ?>

<div class="row">

    <?= Html::hiddenInput('', ServiceType::ID_VOIP, ['id' => 'voipServiceTypeId']) ?>
    <?= Html::hiddenInput('', $formModel->accountTariff->clientAccount->currency, ['id' => 'voipCurrency']) ?>
    <?= Html::hiddenInput('', $clientAccount->is_postpaid, ['id' => 'isPostpaid']) ?>

    <div class="col-sm-2" title="Страна берётся от страны клиента">
        <?php // страна ?>
        <?= $form->field($accountTariffVoip, 'voip_country_id')
            ->widget(Select2::className(), [
                'data' => Country::getList(true),
                'options' => [
                    'id' => 'voipCountryId',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // тип номера ?>
        <?= $form->field($accountTariffVoip, 'voip_number_type')
            ->widget(Select2::className(), [
                'data' => Tariff::getVoipTypesByCountryId($accountTariffVoip->voip_country_id, true), // страна выбрана от клиента
                'options' => [
                    'id' => 'voipNumberType',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // регион (город) ?>
        <?= $form->field($accountTariffVoip, 'city_id')
            ->widget(Select2::className(), [
                'data' => City::dao()->getList(true, $accountTariffVoip->voip_country_id), // страна выбрана от клиента
                'options' => [
                    'disabled' => true,
                    'id' => 'voipRegions',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // тип красивости ?>
        <?= $form->field($accountTariffVoip, 'voip_did_group')
            ->widget(Select2::className(), [
                'data' => [], // DidGroup::dao()->getList(true, $accountTariffVoip->city_id),
                'options' => [
                    'disabled' => true,
                    'id' => 'voipDidGroup',
                ],
            ]) ?>
    </div>

</div>

<?php // фильтры списка номеров ?>
<div class="row collapse" id="voipNumbersListFilter">

    <div class="col-sm-2">
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
                    // 'disabled' => true,
                    'id' => 'voipNumbersListClass',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // сортировка (поле) ?>
        <?php $number = new Number(); ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_order_by_field')
            ->widget(Select2::className(), [
                'data' => [
                    'number' => $number->getAttributeLabel('number'),
                    'beauty_level' => $number->getAttributeLabel('beauty_level'),
                ],
                'options' => [
                    // 'disabled' => true,
                    'id' => 'voipNumbersListOrderByField',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // сортировка (тип) ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_order_by_type')
            ->widget(Select2::className(), [
                'data' => [
                    SORT_ASC => Yii::t('common', 'Ascending'),
                    SORT_DESC => Yii::t('common', 'Descending'),
                ],
                'options' => [
                    // 'disabled' => true,
                    'id' => 'voipNumbersListOrderByType',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // маска ?>
        <?= $this->render('//layouts/_helpMysqlLike'); ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_mask')
            ->input('string', [
                'id' => 'voipNumbersListMask',
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?php // лимит ?>
        <?= $form->field($accountTariffVoip, 'voip_numbers_list_limit')
            ->input('integer', [
                'id' => 'voipNumbersListLimit',
            ]) ?>
    </div>

</div>

<?php // чекбокс "выбрать все" ?>
<div id="voipNumbersListSelectAll" class="collapse">
    <?= Html::checkbox('voipNumbersListSelectAll', false, [
        'label' => Yii::t('common', 'Select all'),
    ]) ?>
</div>


<?php // список номеров ?>
<div id="voipNumbersList" class="alert"></div>


<br/>
<?php // тариф ?>
<div id="voipTariffDiv" class="collapse">
    <?= $this->render('_editLogInput', [
        'formModel' => $formModel,
        'form' => $form,
    ]) ?>
</div>

<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['uu/account-tariff', 'serviceTypeId' => $formModel->serviceTypeId])]) ?>
    <?= $this->render('//layouts/_submitButtonCreate') ?>
</div>

</div>


<?php ActiveForm::end(); ?>

<script type="text/javascript" src="/js/uu/accountTariffEdit.js"></script>

<?php

use app\classes\grid\column\RegionColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\traits\YesNoTraits;
use app\models\voip\StateServiceVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\filter\AccountTariffFilter;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\models\filter\UsageVoipFilter;
use kartik\widgets\Select2;
use yii\web\View;
use kartik\widgets\ActiveForm;
use app\models\Region;
use \app\models\User;

/** @var $filter \app\models\voip\StateServiceVoip */

$this->registerJs("var gve_targetElementName = 'address';\n", View::POS_HEAD);
$this->registerJs("var gve_targetUrl = 'sorm/numbers/save-address';\n", View::POS_HEAD);
$this->registerJsFile('js/grid_view_edit.js',  ['position' => yii\web\View::POS_END]);

echo Html::formLabel('Номера. Включенные. Без адреса установки оборудования');

$form = ActiveForm::begin([
    'method' => 'get',
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($filter, 'region')->widget(Select2::className(), [
                'data' => Region::getList($isWithEmpty = true, $countryId = null, [Region::TYPE_HUB, Region::TYPE_POINT, Region::TYPE_NODE])
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($filter, 'is_b2c')->widget(Select2::className(), [
                'data' => YesNoTraits::getYesNoList(true),
            ]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($filter, 'account_manager')->widget(Select2::className(), [
                'data' => User::getAccountManagerList(true)
            ]) ?>
        </div>
        <div class="col-md-1" style="margin-top: 20px">
            <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-info']) ?>
        </div>
    </div>

<?php
ActiveForm::end();
?>
    <div style="clear: both;"></div>

<?php

$columns = [
    [
        'label' => 'ЛС',
        'attribute' => 'clientAccount.id',
        'format' => 'html',
        'filter' => false,
        'value' => function (StateServiceVoip $state) {
            return $state->clientAccount->getLink();
        },
    ],
    [
        'label' => 'Название контрагента',
        'attribute' => 'clientAccount.contragent.name',
        'filter' => false,
    ],
    [
        'label' => 'Ак. Менеджер',
        'attribute' => 'clientAccount.clientContractModel.accountManagerName',
        'filter' => false,
    ],
    [
        'label' => 'Юр. адрес',
        'attribute' => 'clientAccount.contragent.address_jur',
        'filter' => false,
    ],
    [
        'attribute' => 'e164',
        'filter' => false,
    ],
    [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'filter' => false,
        'value' => function (StateServiceVoip $state) {
            $accountTariffHeap = $state->accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->date_sale) ?
                $accountTariffHeap->date_sale : '';
        },
    ],
    [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'filter' => false,
        'value' => function (StateServiceVoip $state) {
            $accountTariffHeap = $state->accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->disconnect_date) ?
                $accountTariffHeap->disconnect_date : '';
        },
    ],
];

$columns['address'] = [
    'label' => 'Адрес',
    'attribute' => 'device_address',
    'filter' => false,
    'format' => 'raw',
    'value' => function (StateServiceVoip $state) {
        return
            '<span>' . $state->accountTariff->device_address . '</span>' .
            '<img src="/images/icons/edit.gif" role="button" data-id=' . $state->accountTariff->id . ' class="edit pull-right" alt="Редактировать" />';
    },
    'width' => '20%',
];


echo GridView::widget([
    'dataProvider' => $filter->search(),
    'filterModel' => $filter,
    'columns' => $columns,

    'toolbar' => [],
    'panel' => true,
]);

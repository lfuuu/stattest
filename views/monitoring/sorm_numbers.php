<?php

use app\classes\grid\column\RegionColumn;
use app\classes\grid\GridView;
use app\classes\Html;
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

/** @var $filterModelSearch AccountTariffFilter */
/** @var $filterModel AccountTariffFilter */
/** @var $filterModelOld UsageVoipFilter */

$this->registerJs("var gve_targetElementName = 'address';\n", View::POS_HEAD);
$this->registerJs("var gve_targetUrl = 'monitoring/save-address';\n", View::POS_HEAD);
$this->registerJsFile('js/grid_view_edit.js',  ['position' => yii\web\View::POS_END]);

echo Html::formLabel('СОРМ Номера');

$form = ActiveForm::begin([
    'method' => 'get',
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($filterModelSearch, 'region_id')->widget(Select2::className(), [
                'data' => Region::getList($isWithEmpty = true, $countryId = null, [Region::TYPE_HUB, Region::TYPE_POINT, Region::TYPE_NODE])
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($filterModelSearch, 'account_manager')->widget(Select2::className(), [
                'data' => User::getAccountManagerList(true)
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($filterModelSearch, 'is_device_empty')->widget(Select2::className(), [
                'data' => \app\classes\traits\GetListTrait::getEmptyList(true, true)
            ]) ?>
        </div>
        <div class="col-md-2" style="margin-top: 20px">
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
        'label'     => 'ЛС',
        'attribute' => 'clientAccount.id',
        'format' => 'html',
        'filter'    => false,
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->getLink();
        },
    ],
    [
        'label'     => 'Название контрагента',
        'attribute' => 'clientAccount.contragent.name',
        'filter'    => false,
    ],
    [
        'label'     => 'Ак. Менеджер',
        'attribute' => 'clientAccount.clientContractModel.accountManagerName',
        'filter'    => false,
    ],
    [
        'label'     => 'Юр. адрес',
        'attribute' => 'clientAccount.contragent.address_jur',
        'filter'    => false,
    ],
    [
        'attribute' => 'region_id',
        'class'     => RegionColumn::class,
        'filter'    => false,
    ],
    [
        'attribute' => 'voip_number',
        'filter'    => false,
    ],
    [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'filter'    => false,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->date_sale) ?
                $accountTariffHeap->date_sale : '';
        },
    ],
    [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'filter'    => false,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->disconnect_date) ?
                $accountTariffHeap->disconnect_date : '';
        },
    ],
];
$columns['address'] = [
    'label' => 'Адрес',
    'attribute' => 'device_address',
    'filter'    => false,
    'format' => 'raw',
    'value' => function (AccountTariff $accountTariff) {
        return
            '<span>' . $accountTariff->device_address . '</span>' .
            '<img src="/images/icons/edit.gif" role="button" data-id=' . $accountTariff->id . ' class="edit pull-right" alt="Редактировать" />';
    },
    'width' => '20%',
];

$dataProvider = $filterModel->search();
echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,

    'toolbar' => [],
    'panel'=> false,
]);


?>
<br />
<?php
// --------- Old
echo Html::formLabel('СОРМ Номера (старые)');

$columns = [
    [
        'label'     => 'ЛС',
        'attribute' => 'client_id',
        'format' => 'html',
        'filter'    => false,
        'value' => function (UsageVoip $accountTariff) {
            return $accountTariff->clientAccount->getLink();
        },
    ],
    [
        'label'     => 'Название контрагента',
        'attribute' => 'clientAccount.contragent.name',
        'filter'    => false,
    ],
    [
        'label'     => 'Аккаунт менеджер',
        'attribute' => 'clientAccount.clientContractModel.accountManagerName',
        'filter'    => false,
    ],
    [
        'label'     => 'Юр. адрес',
        'attribute' => 'clientAccount.contragent.address_jur',
        'filter'    => false,
    ],
    [
        'label'     => 'Регион',
        'attribute' => 'region',
        'value'     => 'regionName',
        'filter'    => false,
    ],
    [
        'label'     => 'Номер',
        'attribute' => 'E164',
        'filter'    => false,
    ],
    [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'filter'    => false,
        'value' => function (UsageVoip $accountTariff) {
            return $accountTariff->actual_from;
        },
    ],
    [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'filter'    => false,
        'value' => function (UsageVoip $accountTariff) {
            return $accountTariff->actual_to < UsageInterface::MAX_POSSIBLE_DATE ? $accountTariff->actual_to : '';
        },
    ],
];
$columns['address'] = [
    'label' => 'Адрес',
    'attribute' => 'address',
    'filter'    => false,
    'format' => 'raw',
    'value' => function (UsageVoip $accountTariff) {
        return
            '<span>' . $accountTariff->address . '</span>' .
            '<img src="/images/icons/edit.gif" role="button" data-id=' . $accountTariff->id . ' class="edit pull-right" alt="Редактировать" />';
    },
    'width' => '20%',
];

echo GridView::widget([
    'dataProvider' => $filterModelOld->search(),
    'filterModel' => $filterModelOld,
    'columns' => $columns,

    'toolbar' => [],
    'panel'=> false,
]);


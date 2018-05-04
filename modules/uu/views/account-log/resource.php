<?php
/**
 * Расчет платы за ресурсы
 *
 * @var \app\classes\BaseView $this
 * @var AccountLogResourceFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\commands\UbillerController;
use app\modules\uu\column\ResourceColumn;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\filter\AccountLogResourceFilter;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal tarifficator') .
                $this->render('//layouts/_helpConfluence', UbillerController::getHelpConfluence()),
            'encode' => false,
        ],

        ['label' => $this->title = Yii::t('tariff', 'Resource tariffication'), 'url' => '/uu/account-log/resource'],
        [
            'label' => $this->render('//layouts/_helpConfluence', AccountLogResource::getHelpConfluence()),
            'encode' => false,
        ],
    ],
]) ?>

<?php
// отображаемые колонки
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'date_from',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'date_to',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'tariff_period_id',
        'format' => 'html',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountLogResource $accountLogResource) {
            $accountTariff = $accountLogResource->accountTariff;
            return Html::a(
                Html::encode($accountTariff->getName(false)),
                $accountTariff->getUrl()
            );
        },
    ],
    [
        'attribute' => 'tariff_resource_id',
        'class' => ResourceColumn::className(),
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountLogResource $accountLogResource) {
            return $accountLogResource->tariffResource->resource->name;
        },
    ],
    [
        'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (AccountLogResource $accountLogResource) {
            return $accountLogResource->accountTariff->clientAccount->getLink();
        },
    ],
    [
        'attribute' => 'amount_use',
        'class' => FloatRangeColumn::className(),
    ],
    [
        'attribute' => 'account_tariff_resource_log_id',
        'value' => function (AccountLogResource $accountLogResource) {
            if (!$accountLogResource->account_tariff_resource_log_id) {
                return '';
            }

            $accountTariffResourceLog = $accountLogResource->accountTariffResourceLog;
            return $accountTariffResourceLog->amount . ' / ' . $accountTariffResourceLog->actual_from;
        },
    ],
    [
        'attribute' => 'amount_free',
        'class' => FloatRangeColumn::className(),
    ],
    [
        'attribute' => 'amount_overhead',
        'class' => FloatRangeColumn::className(),
    ],
    [
        'attribute' => 'price_per_unit',
        'class' => FloatRangeColumn::className(),
    ],
    [
        'attribute' => 'coefficient',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'price',
        'class' => FloatRangeColumn::className(),
    ],
    [
        'attribute' => 'cost_price',
        'class' => FloatRangeColumn::className(),
    ],
    [
        'attribute' => 'account_entry_id',
        'class' => IsNullAndNotNullColumn::className(),
        'format' => 'html',
        'value' => function (AccountLogResource $accountLogResource) {
            $accountEntry = $accountLogResource->accountEntry;
            if (!$accountEntry) {
                return Yii::t('common', '(not set)');
            }

            return Html::a($accountEntry->date, $accountEntry->getUrl());
        }
    ],
];

// фильтрация перед таблицей
$filterColumns = [
    [
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::className(),
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
    'resizableColumns' => false, // все равно не влезает на экран
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
<?php
/**
 * Расчет платы за ресурсы
 *
 * @var \yii\web\View $this
 * @var AccountLogResourceFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\ResourceColumn;
use app\classes\grid\column\universal\ServiceTypeColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\Html;
use app\classes\uu\filter\AccountLogResourceFilter;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;

$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Resource tariffication'), 'url' => '/uu/accountlog/resource']
    ],
]) ?>

<?php
// отображаемые колонки
$columns = [
    [
        'attribute' => 'date',
        'class' => DateRangeColumn::className(),
    ],
    [
        'attribute' => 'tariff_period_id',
        'format' => 'html',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountLogResource $accountLogResource) {
            $accountTariff = $accountLogResource->accountTariff;
            return Html::a(
                Html::encode($accountLogResource->tariffPeriod->getName()), // $accountTariff->getName(false)
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
            return Html::a(
                Html::encode($accountLogResource->accountTariff->clientAccount->client),
                ['/client/view', 'id' => $accountLogResource->accountTariff->client_account_id]
            );
        },
    ],
    [
        'attribute' => 'amount_use',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'amount_free',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'amount_overhead',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'price_per_unit',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'price',
        'class' => IntegerRangeColumn::className(),
    ],
];

// фильтрация перед таблицей
$filterColumns = [
    [
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::className(),
    ],
];
?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => $this->render('//layouts/_gridBeforeHeaderFilters', [
        'filterModel' => $filterModel,
        'filterColumns' => $filterColumns,
    ]),
    'filterSelector' => '.beforeHeaderFilters input, .beforeHeaderFilters select',
    'resizableColumns' => false, // все равно не влезает на экран
]) ?>

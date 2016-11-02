<?php
/**
 * Расчет минималки
 *
 * @var \yii\web\View $this
 * @var AccountLogMinFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\column\universal\ServiceTypeColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\filter\AccountLogMinFilter;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountTariff;
use yii\widgets\Breadcrumbs;

$accountLogMinTableName = AccountLogMin::tableName();
$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Min resource tariffication'), 'url' => '/uu/account-log/min']
    ],
]) ?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => [
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
            'label' => 'Тип услуги',
            'attribute' => 'service_type_id',
            'class' => ServiceTypeColumn::className(),
            'value' => function (AccountLogMin $accountLogMin) {
                return $accountLogMin->accountTariff->serviceType->name;
            }
        ],
        [
            'label' => Yii::t('models/' . $accountLogMinTableName, 'account_tariff_id'),
            'attribute' => 'tariff_period_id',
            'format' => 'html',
            'class' => TariffPeriodColumn::className(),
            'serviceTypeId' => $filterModel->service_type_id,
            'value' => function (AccountLogMin $accountLogMin) {
                $accountTariff = $accountLogMin->accountTariff;
                return Html::a(
                    Html::encode($accountLogMin->tariffPeriod->getName()), // $accountTariff->getName(false)
                    $accountTariff->getUrl()
                );
            }
        ],
        [
            'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
            'attribute' => 'client_account_id',
            'class' => IntegerColumn::className(),
            'format' => 'html',
            'value' => function (AccountLogMin $accountLogMin) {
                return $accountLogMin->accountTariff->clientAccount->getLink();
            }
        ],
        [
            'attribute' => 'period_price',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'coefficient',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'price_with_coefficient',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'price_resource',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'price',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'account_entry_id',
            'class' => IsNullAndNotNullColumn::className(),
            'format' => 'html',
            'value' => function (AccountLogMin $accountLogMin) {
                $accountEntry = $accountLogMin->accountEntry;
                if (!$accountEntry) {
                    return Yii::t('common', '(not set)');
                }
                return Html::a($accountEntry->date, $accountEntry->getUrl());
            }
        ],
    ],
]) ?>

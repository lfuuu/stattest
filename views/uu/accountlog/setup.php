<?php
/**
 * Расчет платы за подключение
 *
 * @var \yii\web\View $this
 * @var AccountLogSetupFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\Html;
use app\classes\uu\filter\AccountLogSetupFilter;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;

$accountLogPeriodTableName = AccountLogPeriod::tableName();
$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Setup tariffication'), 'url' => '/uu/accountlog/setup']
    ],
]) ?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => [
        [
            'attribute' => 'date',
            'class' => DateRangeColumn::className(),
        ],
        [
            'label' => Yii::t('models/' . $accountLogPeriodTableName, 'account_tariff_id'),
            'attribute' => 'tariff_period_id',
            'format' => 'html',
            'class' => TariffPeriodColumn::className(),
            'serviceTypeId' => $filterModel->service_type_id,
            'value' => function (AccountLogSetup $accountLogSetup) {
                $accountTariff = $accountLogSetup->accountTariff;
                return Html::a(
                    Html::encode($accountLogSetup->tariffPeriod->getName()), // $accountTariff->getName(false)
                    $accountTariff->getUrl()
                );
            }
        ],
        [
            'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
            'attribute' => 'client_account_id',
            'class' => IntegerColumn::className(),
            'format' => 'html',
            'value' => function (AccountLogSetup $accountLogSetup) {
                return Html::a(
                    Html::encode($accountLogSetup->accountTariff->clientAccount->client),
                    ['/client/view', 'id' => $accountLogSetup->accountTariff->client_account_id]
                );
            }
        ],
        [
            'attribute' => 'price',
            'class' => IntegerRangeColumn::className(),
        ],
    ],
]) ?>

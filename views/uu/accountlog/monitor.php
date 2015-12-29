<?php
/**
 * Мониторинг расчетов
 *
 * @var \yii\web\View $this
 * @var AccountLogMonitorFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\grid\column\universal\WithoutFilterColumn;
use app\classes\Html;
use app\classes\uu\filter\AccountLogMonitorFilter;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\monitor\AccountLogPeriodMonitor;
use app\classes\uu\monitor\AccountLogResourceMonitor;
use app\classes\uu\monitor\AccountLogSetupMonitor;
use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Monitoring'), 'url' => '/uu/accountlog/monitor']
    ],
]) ?>

<?php
$accountLogPeriodTableName = AccountLogPeriod::tableName();
$columns = [
    [
        'label' => Yii::t('models/' . $accountLogPeriodTableName, 'account_tariff_id'),
        'attribute' => 'tariff_period_id',
        'format' => 'html',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountTariff $accountTariff) {
            return Html::a(
                Html::encode($accountTariff->getName(false)),
                $accountTariff->getUrl()
            );
        }
    ],
    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return Html::a(
                Html::encode($accountTariff->clientAccount->client),
                ['/client/view', 'id' => $accountTariff->client_account_id]
            );
        }
    ],
];

$monthDateTime = $filterModel->getMonthDateTime();
if ($monthDateTime) {

    $days = $monthDateTime->format('t');
    for ($day = 1; $day <= $days; $day++) {
        $column = [
            'label' => $day,
            'attribute' => $day,
            'class' => WithoutFilterColumn::className(),
            'format' => 'html',
            'value' => function (AccountTariff $accountTariff) use ($monthDateTime, $day) {
                $accountLogSetup = AccountLogSetupMonitor::getMonitor($accountTariff, $monthDateTime, $day);
                $accountLogPeriod = AccountLogPeriodMonitor::getMonitor($accountTariff, $monthDateTime, $day);
                $accountLogResource = AccountLogResourceMonitor::getMonitor($accountTariff, $monthDateTime, $day);
                return
                    ($accountLogSetup ? '+' : '') . '<br />' .
                    ($accountLogPeriod ? '+' : '') . '<br />' .
                    ($accountLogResource ?: '');
            }
        ];
        if ($day == 1) {
            $column['attribute'] = 'month';
            $column['class'] = MonthColumn::className();
            $column['filterOptions'] = ['colspan' => $days];
        }
        $columns[] = $column;
    }

} else {

    $columns[] = [
        'label' => Yii::t('tariff', 'Select a month'),
        'attribute' => 'month',
        'class' => MonthColumn::className(),
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return '';
        },
    ];

}

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'resizableColumns' => false, // все равно не влезает на экран
]) ?>

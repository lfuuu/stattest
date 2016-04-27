<?php
/**
 * Мониторинг расчетов
 *
 * @var \yii\web\View $this
 * @var AccountLogMonitorFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\ServiceTypeColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\grid\column\universal\WithoutFilterColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\filter\AccountLogMonitorFilter;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\Resource;
use app\classes\uu\monitor\AccountLogPeriodMonitor;
use app\classes\uu\monitor\AccountLogResourceMonitor;
use app\classes\uu\monitor\AccountLogSetupMonitor;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Monitoring'), 'url' => '/uu/accountlog/monitor']
    ],
]) ?>

<?php
/** @var Resource[][] $resourcesGroupedByServiceType */
$resourcesGroupedByServiceType = Resource::getGroupedByServiceType();

$accountLogPeriodTableName = AccountLogPeriod::tableName();

$columns = [
    [
        'label' => 'Тип услуги',
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::className(),
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->serviceType->name;
        }
    ],
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
            'value' => function (AccountTariff $accountTariff) use ($monthDateTime, $day, $resourcesGroupedByServiceType) {

                $accountLogSetup = AccountLogSetupMonitor::getMonitor($accountTariff, $monthDateTime, $day);
                $accountLogPeriod = AccountLogPeriodMonitor::getMonitor($accountTariff, $monthDateTime, $day);
                $accountLogResource = (int)AccountLogResourceMonitor::getMonitor($accountTariff, $monthDateTime, $day);

                // сколько всего ресурсов должно быть посчитано
                $totalResources = isset($resourcesGroupedByServiceType[$accountTariff->service_type_id]) ?
                    count($resourcesGroupedByServiceType[$accountTariff->service_type_id]) :
                    0;

                // обязательно ли считать транзакции за этот день
                $dateStr = $monthDateTime->modify($day . ' day')->format('Y-m-d');
                if ($dateStr >= date('Y-m-d')) {

                    // дата в будущем, считать не обязательно
                    $isNeedCalculate = false;

                } elseif ($accountTariff->tariff_period_id) {

                    // сейчас услуга действует. Надо считать
                    // @todo еще надо учесть дату включения тарифа
                    $isNeedCalculate = true;

                } else {

                    // сейчас услуга отключена. Но в прошлом она могла действовать
                    /** @var AccountTariffLog $accountTariffLog */
                    $accountTariffLogs = $accountTariff->accountTariffLogs;
                    $accountTariffLog = reset($accountTariffLogs);
                    $isNeedCalculate = (!$accountTariffLog || $accountTariffLog->actual_from >= $dateStr);

                }

                // стили для ресурсов
                $resourceTitle = $accountLogResource . ' / ' . $totalResources . '. ';
                if ($accountLogResource) {
                    if ($accountLogResource === $totalResources) {
                        $resourceTitle .= 'Плата за все ресурсы посчитана';
                        $resourceClassName = 'success';
                    } else {
                        $resourceTitle .= 'Плата за часть ресурсов не посчитана';
                        $resourceClassName = 'warning';
                    }
                } else {
                    $resourceTitle .= 'Плата за ресурсы не посчитана';
                    $resourceClassName = 'danger';
                }

                return
                    // подключение
                    ($accountLogSetup ? '<span class="label label-success" title="Плата за подключение посчитана">+</span>' : '') . '<br />' .

                    // абонентка
                    ($accountLogPeriod ?
                        '<span class="label label-success" title="Абоненская плата посчитана">+</span>' :
                        ($isNeedCalculate ?
                            '<span class="label label-danger" title="Абоненская плата не посчитана">-</span>' :
                            ''
                        )
                    ) . '<br />' .

                    // ресурсы
                    (
                    ($isNeedCalculate && $totalResources) ?
                        sprintf('<span class="label label-%s" title="%s">%d</span>', $resourceClassName, $resourceTitle, $accountLogResource) :
                        ($accountLogResource ?: '')
                    );
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

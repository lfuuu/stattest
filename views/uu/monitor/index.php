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
use app\helpers\DateTimeZoneHelper;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Monitoring'), 'url' => '/uu/monitor']
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
            return $accountTariff->clientAccount->getLink();
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
                list($accountLogResource, $accountLogResourceWithEntry) = AccountLogResourceMonitor::getMonitor($accountTariff, $monthDateTime, $day);

                // сколько всего ресурсов должно быть посчитано
                $totalResources = isset($resourcesGroupedByServiceType[$accountTariff->service_type_id]) ?
                    count($resourcesGroupedByServiceType[$accountTariff->service_type_id]) :
                    0;

                // обязательно ли считать транзакции за этот день
                $dateStr = $monthDateTime
                    ->setDate($monthDateTime->format('Y'), $monthDateTime->format('n'), $day)
                    ->format(DateTimeZoneHelper::DATE_FORMAT);
                if ($dateStr >= date(DateTimeZoneHelper::DATE_FORMAT)) {

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
                    $isNeedCalculate = ($accountTariffLog && $accountTariffLog->actual_from > $dateStr);

                }

                ob_start();

                ?>


                <?php // подключение ?>
                <?php if ($accountLogSetup) : ?>
                    <span class="label label-<?= ($accountLogSetup === 2) ? 'success' : 'warning' ?>"
                          title="Плата за подключение посчитана <?= ($accountLogSetup === 2) ? 'и учтена' : ', но не учтена' ?> в проводке">+</span>
                <?php endif ?>
                <br/>

                <?php // абонентка ?>
                <?php if ($accountLogPeriod) : ?>
                    <span class="label label-<?= ($accountLogPeriod === 2) ? 'success' : 'warning' ?>"
                          title="Абонентская плата посчитана <?= ($accountLogPeriod === 2) ? 'и учтена' : ', но не учтена' ?> в проводке">+</span>
                <?php elseif ($isNeedCalculate): ?>
                    <span class="label label-danger" title="Абонентская плата не посчитана">-</span>
                <?php endif ?>
                <br/>

                <?php // ресурсы ?>
                <?php if ($isNeedCalculate && $totalResources) : ?>
                    <?php
                    $resourceTitle = $accountLogResource . ' / ' . $totalResources . '. ';
                    if ($accountLogResource) {

                        if ($accountLogResource === $totalResources) {
                            $resourceTitle .= 'Плата за все ресурсы посчитана';
                            if ($accountLogResource === $accountLogResourceWithEntry) {
                                $resourceTitle .= ' и учтена в проводках';
                                $resourceClassName = 'success';
                            } else {
                                $resourceTitle .= ', но не учтена в проводках';
                                $resourceClassName = 'warning';
                            }

                        } else {

                            $resourceTitle .= 'Плата за часть ресурсов не посчитана';
                            if ($accountLogResource !== $accountLogResourceWithEntry) {
                                $resourceTitle .= ' и не учтена в проводках';
                            }
                            $resourceClassName = 'warning';
                        }
                    } else {
                        $resourceTitle .= 'Плата за ресурсы не посчитана';
                        $resourceClassName = 'danger';
                    }
                    ?>
                    <span class="label label-<?= $resourceClassName ?>" title="<?= $resourceTitle ?>"><?= $accountLogResource ?></span>
                <?php elseif ($accountLogResource): ?>
                    <?= $accountLogResource ?>
                <?php endif ?>
                <br/>

                <?php
                return ob_get_clean();
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

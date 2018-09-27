<?php
/**
 * Список статистики по sms
 *
 * @var \app\classes\BaseView $this
 * @var MttRawFilter $filterModel
 */

use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\DropdownColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\ServiceColumn;
use app\classes\grid\GridView;
use app\models\filter\mtt_raw\MttRawFilter;
use app\models\mtt_raw\MttRaw;
use app\modules\mtt\column\SrcNumberColumn;
use app\widgets\GridViewExport\GridViewExport;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal services'),
        [
            'label' => $this->title = 'MTT. Статистика',
            'url' => Url::to(['/uu/mtt/'])
        ],
    ],
]) ?>

<?php
// При экспорте данных происходит передача параметра driver в GET - запросе
$driver = Yii::$app->request->get('driver');
// Флаг форсированного приведения к информационным единицам измерения для интернета
$isForcibly = false;
foreach ((array)$filterModel->serviceid as $serviceid) {
    if (!in_array($serviceid, MttRaw::SERVICE_ID_INET)) {
        $isForcibly = false;
        break;
    }
    $isForcibly = true;
}

$columns = [
    [
        'attribute' => 'connect_time',
        'class' => DateTimeRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'chargedqty',
        'class' => IntegerRangeColumn::class,
        'value' => function (MttRaw $mttRaw) use ($driver, $isForcibly) {
            return $driver ?
                $mttRaw->chargedqty : $mttRaw->getBeautyChargedQty($isForcibly);
        },
    ],
    [
        'attribute' => 'usedqty',
        'class' => IntegerRangeColumn::class,
        'value' => function (MttRaw $mttRaw) use ($driver, $isForcibly) {
            return $driver ?
                $mttRaw->usedqty : $mttRaw->getBeautyUsedQty($isForcibly);
        },
    ],
];

/** @var MttRawFilter $dataProvider */
$dataProvider = $filterModel->search();

$widgetConfig = [
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [
        'columns' => [
            [
                'label' => 'Временная группировка',
                'attribute' => 'group_time',
                'class' => DropdownColumn::class,
                'filter' => ['' => '----'] + $filterModel->getGroupTimeList(),
            ],
            [
                'attribute' => 'account_id',
                'class' => IntegerColumn::class,
                'format' => 'html',
                'value' => function (MttRaw $mttRaw) {
                    $clientAccount = $mttRaw->clientAccount;
                    return $clientAccount ? $clientAccount->getLink() : '';
                },
            ],
            [
                'attribute' => 'number_service_id',
                'class' => IntegerColumn::class,
            ],
            [
                'attribute' => 'serviceid',
                'class' => ServiceColumn::class,
                'isWithEmpty' => false,
                'filterInputOptions' => [
                    'multiple' => true,
                ],
            ],
            [
                'attribute' => 'src_number',
                'class' => SrcNumberColumn::class,
                'accountId' => $filterModel->account_id,
            ],
            [
                'attribute' => 'dst_number',
                'class' => IntegerColumn::class,
            ],
            [
                'attribute' => 'chargedamount',
                'class' => FloatRangeColumn::class,
            ],
        ],
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
];

if ($summary = $filterModel->getSummary()) {
    $amountColumns = [['content' => Yii::t('common', 'Summary')]];
    $amountColumns[0] += ['options' => ['colspan' => 1]];

    foreach($summary as $key => $value) {
        if ($isForcibly) {
            switch($key)
            {
                case 'chargedqty':
                    $value = MttRaw::getBeautyFormattedValue($value * 1024, $decimals = 2);
                    break;
                case 'usedqty':
                    $value = MttRaw::getBeautyFormattedValue($value * 1);
                    break;
            }
        }

        $amountColumns[] = ['content' => $value];
    }

    $widgetConfig['afterHeader'] = [
        [
            'options' => ['class' => GridView::TYPE_WARNING],
            'columns' => $amountColumns,
        ]
    ];
}

echo GridView::widget($widgetConfig);
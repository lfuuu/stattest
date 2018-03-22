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

// Флаг форсированного приведения к информационным единицам измерения для интернета
$isForcibly = false;
foreach((array) $filterModel->serviceid as $serviceid) {
    if (!in_array($serviceid, MttRaw::SERVICE_ID_INET)) {
        $isForcibly = false;
        break;
    }
    $isForcibly = true;
}

$columns = [
    [
        'attribute' => 'connect_time',
        'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'chargedqty',
        'class' => IntegerRangeColumn::className(),
        'value' => function(MttRaw $mttRaw) use($isForcibly) {
            return $mttRaw->getBeautyChargedQty($isForcibly);
        },
    ],
    [
        'attribute' => 'usedqty',
        'class' => IntegerRangeColumn::className(),
        'value' => function(MttRaw $mttRaw) use ($isForcibly) {
            return $mttRaw->getBeautyUsedQty($isForcibly);
        },
    ],
];

/** @var MttRawFilter $dataProvider */
$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [
        'columns' => [
            [
                'label' => 'Временная группировка',
                'attribute' => 'group_time',
                'class' => DropdownColumn::className(),
                'filter' => ['' => '----'] + $filterModel->getGroupTimeList(),
            ],
            [
                'attribute' => 'account_id',
                'class' => IntegerColumn::className(),
                'format' => 'html',
                'value' => function (MttRaw $mttRaw) {
                    $clientAccount = $mttRaw->clientAccount;
                    return $clientAccount ? $clientAccount->getLink() : '';
                },
            ],
            [
                'attribute' => 'number_service_id',
                'class' => IntegerColumn::className(),
            ],
            [
                'attribute' => 'serviceid',
                'class' => ServiceColumn::className(),
                'isWithEmpty' => false,
                'filterInputOptions' => [
                    'multiple' => true,
                ],
            ],
            [
                'attribute' => 'src_number',
                'class' => SrcNumberColumn::className(),
                'accountId' => $filterModel->account_id,
            ],
            [
                'attribute' => 'dst_number',
                'class' => IntegerColumn::className(),
            ],
            [
                'attribute' => 'chargedamount',
                'class' => FloatRangeColumn::className(),
            ],
        ],
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
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
$columns = [
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
        'attribute' => 'connect_time',
        'class' => DateTimeRangeDoubleColumn::class,
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
        'attribute' => 'chargedqty',
        'class' => IntegerRangeColumn::class,
        'value' => function(MttRaw $mttRaw) {
            return $mttRaw->getBeautyChargedQty();
        },
    ],
    [
        'attribute' => 'usedqty',
        'class' => IntegerRangeColumn::class,
        'value' => function(MttRaw $mttRaw) {
            return $mttRaw->getBeautyUsedQty();
        },
    ],
    [
        'attribute' => 'chargedamount',
        'class' => FloatRangeColumn::class,
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
                'class' => DropdownColumn::class,
                'filter' => ['' => '----'] + $filterModel->getGroupTimeList(),
            ],
        ],
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
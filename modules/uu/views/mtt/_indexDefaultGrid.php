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
        'attribute' => 'connect_time',
        'class' => DateTimeRangeDoubleColumn::className(),
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
        'attribute' => 'chargedqty',
        'class' => IntegerRangeColumn::className(),
        'value' => function(MttRaw $mttRaw) {
            return $mttRaw->getBeautyChargedQty();
        },
    ],
    [
        'attribute' => 'usedqty',
        'class' => IntegerRangeColumn::className(),
        'value' => function(MttRaw $mttRaw) {
            return $mttRaw->getBeautyUsedQty();
        },
    ],
    [
        'attribute' => 'chargedamount',
        'class' => FloatRangeColumn::className(),
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
        ],
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
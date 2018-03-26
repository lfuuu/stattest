<?php
/**
 * Список статусов бизнес процессов
 *
 * @var app\classes\BaseView $this
 * @var BusinessProcessStatusFilter $filterModel
 */

use app\classes\grid\column\universal\DropdownColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\BusinessProcess;
use app\models\filter\BusinessProcessStatusFilter;
use app\models\BusinessProcessStatus;
use app\widgets\GridViewSequence\GridViewSequence;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Статусы бизнес процессов', 'url' => '/dictionary/business-process-status/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, BusinessProcessStatus $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, BusinessProcessStatus $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'business_process_id',
        'class' => DropdownColumn::className(),
        'filter' => BusinessProcess::getListWithBusinessName($isWithEmpty = true),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'is_bill_send',
        'class' => YesNoColumn::className(),
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/business-process-status/new/']),
    'columns' => $columns,
]);
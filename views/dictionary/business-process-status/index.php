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
use app\classes\Html;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

?>
<?= Html::formLabel('Статусы бизнес процессов'); ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Статусы бизнес процессов', 'url' => Url::toRoute(['/dictionary/business-process-status/'])],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
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
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'business_process_id',
        'class' => DropdownColumn::class,
        'filter' => BusinessProcess::getListWithBusinessName($isWithEmpty = true),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'is_off_stage',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_bill_send',
        'class' => YesNoColumn::class,
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/business-process-status/new/']),
    'columns' => $columns,
]);
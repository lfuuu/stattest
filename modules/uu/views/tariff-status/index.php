<?php
/**
 * Список статусов тарифа
 *
 * @var \app\classes\BaseView $this
 * @var TariffStatusFilter $filterModel
 */

use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\filter\TariffStatusFilter;
use app\modules\uu\models\TariffStatus;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [Yii::t('tariff', 'Tariff statuses')],
]) ?>

<?php
// базовые столбцы
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, TariffStatus $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::class,
    ],
    [
        'class' => ActionColumn::class,
        'template' => '{delete}',
        'buttons' => [
            'delete' => function ($url, TariffStatus $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/uu/tariff-status/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
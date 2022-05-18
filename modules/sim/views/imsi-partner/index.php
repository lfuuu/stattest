<?php
/**
 * MVNO-партнеры IMSI. Список
 *
 * @var app\classes\BaseView $this
 * @var ImsiPartnerFilter $filterModel
 */

use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\modules\sim\filters\ImsiPartnerFilter;
use app\modules\sim\models\ImsiPartner;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        $this->title = 'MVNO-партнеры IMSI',
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, ImsiPartner $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, ImsiPartner $model, $key) use ($baseView) {
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
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::class,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/sim/imsi-partner/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
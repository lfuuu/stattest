<?php
/**
 * Список регионов
 *
 * @var app\classes\BaseView $this
 * @var RegionFilter $filterModel
 */

use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\filter\RegionFilter;
use app\models\Region;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Регионы (точки подключения)', 'url' => '/dictionary/region/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}', // {delete}
        'buttons' => [
            'update' => function ($url, Region $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Region $model, $key) use ($baseView) {
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
        'attribute' => 'short_name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'code',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'timezone_name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'country_id',
        'class' => CountryColumn::class,
    ],
    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_use_sip_trunk',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'type_id',
        'class' => ConstructColumn::class,
        'filter' => Region::$typeNames,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/region/new/']),
    'columns' => $columns,
]);
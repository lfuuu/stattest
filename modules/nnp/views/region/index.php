<?php
/**
 * Регионы
 *
 * @var app\classes\BaseView $this
 * @var RegionFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\column\RegionColumn;
use app\modules\nnp\filters\RegionFilter;
use app\modules\nnp\models\Region;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Регионы', 'url' => '/nnp/region/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, Region $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
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
        'format' => 'html',
        'value' => function (Region $model) use ($baseView) {
            return Html::a($model->id, $model->getUrl());
        }
    ],
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
        'indexBy' => 'code',
    ],
    [
        'attribute' => 'iso',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name_translit',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'parent_id',
        'class' => RegionColumn::class,
        'countryCodes' => $filterModel->country_code,
    ],
    [
        'attribute' => 'cnt',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (Region $region) {
            return $region->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $region->country_code, 'NumberRangeFilter[region_id]' => $region->id])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $region->country_code, 'NumberFilter[region_id]' => $region->id])
                ) . ')';
        }
    ],
    [
        'attribute' => 'cnt_active',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (Region $region) {
            return $region->cnt_active . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/',
                        'NumberRangeFilter[country_code]' => $region->country_code,
                        'NumberRangeFilter[region_id]' => $region->id,
                        'NumberRangeFilter[is_active]' => 1,
                        ])
                ) . ')';
        }
    ],
    [
        'class' => ActionColumn::class,
        'template' => '{delete}',
        'buttons' => [
            'delete' => function ($url, Region $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/region/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
<?php
/**
 * Города
 *
 * @var app\classes\BaseView $this
 * @var CityFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\column\RegionColumn;
use app\modules\nnp\filters\CityFilter;
use app\modules\nnp\models\City;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Города', 'url' => '/nnp/city/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, City $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, City $model, $key) use ($baseView) {
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
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
        'indexBy' => 'code',
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
        'countryCodes' => $filterModel->country_code,
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
        'attribute' => 'cnt',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (City $city) {
            return $city->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $city->country_code, 'NumberRangeFilter[city_id]' => $city->id])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $city->country_code, 'NumberFilter[city_id]' => $city->id])
                ) . ')';
        }
    ],
    [
        'attribute' => 'cnt_active',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (City $city) {
            return $city->cnt_active . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $city->country_code, 'NumberRangeFilter[city_id]' => $city->id])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $city->country_code, 'NumberFilter[city_id]' => $city->id])
                ) . ')';
        }
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/city/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
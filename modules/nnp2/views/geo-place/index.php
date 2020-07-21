<?php
/**
 * Местоположения
 *
 * @var app\classes\BaseView $this
 * @var GeoPlaceFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp2\column\RegionColumn;
use app\modules\nnp2\column\CityColumn;
use app\modules\nnp2\filters\GeoPlaceFilter;
use app\modules\nnp2\models\GeoPlace;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Местоположения', 'url' => '/nnp2/geo-place/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, GeoPlace $model, $key) use ($baseView) {
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
        'value' => function (GeoPlace $model) {

            $html = $model->id;
            if ($model->is_valid) {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
            } else {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            }

            return $html;
        },
    ],
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
        'indexBy' => 'code',
    ],
    [
        'attribute' => 'ndc',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
        'countryCodes' => $filterModel->country_code,
        'showVerified' => true,
        'showParent' => true,
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::class,
        'countryCodes' => $filterModel->country_code,
        'showVerified' => true,
        'showParent' => true,
    ],
    [
        'attribute' => 'is_valid',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'parent_id',
        'format' => 'html',
        'value' => function (GeoPlace $geoPlace) {
            $html = '';
            if ($parent = $geoPlace->parent) {
                $html .= strval($parent);
            }

            return $html;
        },
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
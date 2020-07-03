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
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp2\column\RegionColumn;
use app\modules\nnp2\filters\CityFilter;
use app\modules\nnp2\models\City;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Города', 'url' => '/nnp2/city/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, City $model, $key) use ($baseView) {
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
        'format' => 'html',
        'value' => function (City $city) {
            $html = $city->name;
            if ($city->is_valid) {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
            } else {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            }

            return $html;
        },
    ],
    [
        'attribute' => 'name_translit',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'is_valid',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'parent_id',
        'format' => 'html',
        'value' => function (City $city) {
            $html = '';
            if ($parent = $city->parent) {
                $html .= $parent->name;
            }

            return $html;
        },
    ],
    [
        'attribute' => 'cnt',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (City $city) {
            return $city->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp2/number-range/', 'NumberRangeFilter[country_code]' => $city->country_code, 'NumberRangeFilter[city_id]' => $city->id])
                )
                . ')';
        }
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
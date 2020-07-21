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
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp2\column\RegionColumn;
use app\modules\nnp2\filters\RegionFilter;
use app\modules\nnp2\models\Region;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Регионы', 'url' => '/nnp2/region/'],
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
        'attribute' => 'parent_id',
        'class' => RegionColumn::class,
        'countryCodes' => $filterModel->country_code,
        'format' => 'html',
        'value' => function (Region $region) {
            $html = '';
            if ($parent = $region->parent) {
                $html .= $parent->name;
            }

            return $html;
        }
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
        'format' => 'html',
        'value' => function (Region $region) {
            $html = $region->name;
            if ($region->is_valid) {
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
        'attribute' => 'cnt',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (Region $region) {
            return $region->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp2/number-range/', 'NumberRangeFilter[country_code]' => $region->country_code, 'NumberRangeFilter[region_id]' => $region->id])
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
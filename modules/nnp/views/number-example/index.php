<?php
/**
 * Примеры номеров
 *
 * @var app\classes\BaseView $this
 * @var NumberExampleFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\nnp\column\CityColumn;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\nnp\column\OperatorColumn;
use app\modules\nnp\column\RegionColumn;
use app\modules\nnp\filters\NumberExampleFilter;
use app\modules\nnp\models\NumberExample;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Примеры номеров', 'url' => '/nnp/number-example/'],
    ],
]) ?>

<?php
$baseView = $this;

$filterColumns = [
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
        'indexBy' => 'code',
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'operator_name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'region_name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'city_name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'ndc',
        'class' => IntegerColumn::class,
    ],
    [
        'label' => 'Полный номер (маска)&nbsp;' . $this->render('//layouts/_helpMysqlLike'),
        'attribute' => 'full_number_mask',
        'class' => StringColumn::class,
    ],
];

$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{view}',
        'buttons' => [
            'view' => function ($url, NumberExample $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionView', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'label' => 'Номер',
        'attribute' => 'full_number',
        'class' => IntegerColumn::class,
        'format' => 'html',
    ],
    [
        'label' => 'Префикс',
        'attribute' => 'prefix',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (NumberExample $numberExample) {
            return $numberExample->prefix;
        },
    ],
    [
        'attribute' => 'operator_id',
        'class' => OperatorColumn::class,
        'countryCode' => $filterModel->country_code,
        // 'isWithNullAndNotNull' => false,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
        // 'isWithNullAndNotNull' => true,
        'countryCodes' => $filterModel->country_code,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::class,
        // 'isWithNullAndNotNull' => true,
        'countryCodes' => $filterModel->country_code,
        'regionIds' => $filterModel->region_id,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::class,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'numbers',
        'filter' => false,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);

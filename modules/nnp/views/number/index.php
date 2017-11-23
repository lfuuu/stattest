<?php
/**
 * Номера
 *
 * @var app\classes\BaseView $this
 * @var NumberFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CityColumn;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\column\OperatorColumn;
use app\modules\nnp\column\RegionColumn;
use app\modules\nnp\filters\NumberFilter;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Портированные номера', 'url' => '/nnp/number/'],
    ],
]) ?>

<?php
$baseView = $this;

$filterColumns = [
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::className(),
        'indexBy' => 'code',
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'operator_source',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'region_source',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'city_source',
        'class' => StringColumn::className(),
    ],
];

$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, \app\modules\nnp\models\Number $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
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
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'operator_id',
        'class' => OperatorColumn::className(),
        'countryCode' => $filterModel->country_code,
        // 'isWithNullAndNotNull' => false,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::className(),
        // 'isWithNullAndNotNull' => true,
        'countryCodes' => $filterModel->country_code,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
        // 'isWithNullAndNotNull' => true,
        'countryCodes' => $filterModel->country_code,
        'regionIds' => $filterModel->region_id,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'label' => 'Диапазон номеров',
        'format' => 'html',
        'value' => function (\app\modules\nnp\models\Number $number) {
            return Html::a('Показать', ['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $number->country_code, 'NumberRangeFilter[full_number_from]' => $number->full_number, 'NumberRangeFilter[is_active]' => 1]);
        },
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

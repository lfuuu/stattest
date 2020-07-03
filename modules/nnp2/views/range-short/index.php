<?php
/**
 * Диапазон номеров (готовый)
 *
 * @var app\classes\BaseView $this
 * @var RangeShortFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp2\column\CityColumn;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp2\column\NdcTypeColumn;
use app\modules\nnp2\column\OperatorColumn;
use app\modules\nnp2\column\RegionColumn;
use app\modules\nnp2\filters\RangeShortFilter;
use app\modules\nnp2\models\RangeShort;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Готовый список номеров', 'url' => '/nnp2/range-short/'],
    ],
]) ?>

<?php
$baseView = $this;

$filterColumns = [
    [
        'label' => 'Полный номер начала диапазона (маска)&nbsp;' . $this->render('//layouts/_helpMysqlLike'),
        'attribute' => 'full_number_mask',
        'class' => StringColumn::class,
    ],
    [
        'label' => 'Кол-во номеров от',
        'attribute' => 'numbers_count_from',
        'class' => IntegerColumn::class,
    ],
    [
        'label' => 'Кол-во номеров до',
        'attribute' => 'numbers_count_to',
        'class' => IntegerColumn::class,
    ],
];

$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{view}',
        'buttons' => [
            'update' => function ($url, RangeShort $model, $key) use ($baseView) {
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
        'attribute' => 'full_number_from',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (RangeShort $model) {
            return $model->full_number_from . '<br>' .
                $model->full_number_to;
        }
    ],
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
    ],
    [
        'attribute' => 'ndc',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
        'isWithNullAndNotNull' => true,
        'countryCodes' => $filterModel->country_code,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::class,
        'isWithNullAndNotNull' => true,
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
        'attribute' => 'operator_id',
        'class' => OperatorColumn::class,
        'countryCode' => $filterModel->country_code,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],

    [
        'attribute' => 'allocation_date_start',
        'class' => DateRangeDoubleColumn::class,
        'format' => 'html',
        'value' => function (RangeShort $model) {

            if ($model->allocation_date_start) {
                $htmlArray[] = Html::tag('span', Yii::$app->formatter->asDate($model->allocation_date_start, 'medium'));
            } else {
                $htmlArray[] = '-';
            }

            return $htmlArray ?
                implode('<br/>', $htmlArray) :
                Yii::t('common', '(not set)');
        },
    ],
    [
        'label' => 'Создано / редактировано',
        'attribute' => 'insert_time',
        'class' => MonthColumn::class,
        'format' => 'html',
        'value' => function (RangeShort $model) {
            $htmlArray = [];
            if ($model->insert_time) {
                $htmlArray[] = Yii::$app->formatter->asDate($model->insert_time, 'medium');
            } else {
                $htmlArray[] = '-';
            }

            if ($model->update_time) {
                $htmlArray[] = Yii::$app->formatter->asDate($model->update_time, 'medium');
            } else {
                $htmlArray[] = '-';
            }

            return $htmlArray ?
                implode('<br/>', $htmlArray) :
                Yii::t('common', '(not set)');
        },
    ],
];

$dataProvider = $filterModel->search();

echo $this->render('info', ['filterModel' => $filterModel]);

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
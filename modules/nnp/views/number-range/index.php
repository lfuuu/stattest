<?php
/**
 * Диапазон номеров
 *
 * @var app\classes\BaseView $this
 * @var NumberRangeFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CityColumn;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\nnp\column\OperatorColumn;
use app\modules\nnp\column\PrefixColumn;
use app\modules\nnp\column\RegionColumn;
use app\modules\nnp\filters\NumberRangeFilter;
use app\modules\nnp\models\NumberRange;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Диапазон номеров', 'url' => '/nnp/number-range/'],
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
    [
        'attribute' => 'ndc',
        'class' => IntegerColumn::className(),
    ],
    [
        'label' => 'Полный номер начала диапазона (маска)&nbsp;' . $this->render('//layouts/_helpMysqlLike'),
        'attribute' => 'full_number_mask',
        'class' => StringColumn::className(),
    ],
    [
        'label' => 'Кол-во номеров от',
        'attribute' => 'numbers_count_from',
        'class' => IntegerColumn::className(),
    ],
    [
        'label' => 'Кол-во номеров до',
        'attribute' => 'numbers_count_to',
        'class' => IntegerColumn::className(),
    ],
];

$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, NumberRange $model, $key) use ($baseView) {
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
        'attribute' => 'full_number_from',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return sprintf('%s<br>%s', $numberRange->full_number_from, $numberRange->full_number_to);
        }
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
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::className(),
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::className(),
    ],
    [
        'label' => 'Префиксы',
        'attribute' => 'prefix_id',
        'class' => PrefixColumn::className(),
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            $htmlArray = [];
            foreach ($numberRange->numberRangePrefixes as $numberRangePrefixes) {
                $prefix = $numberRangePrefixes->prefix;
                $htmlArray[] = Html::a($prefix->name, $prefix->getUrl());
            }

            return implode('<br/>', $htmlArray);
        },
    ],
    [
        'attribute' => 'date_resolution',
        'class' => DateRangeDoubleColumn::className(),
        'value' => function (NumberRange $numberRange) {
            return $numberRange->date_resolution ?
                Yii::$app->formatter->asDate($numberRange->date_resolution, 'medium') :
                Yii::t('common', '(not set)');
        },
    ],
    [
        'label' => 'Создано / редактировано / выключено',
        'attribute' => 'insert_time',
        'class' => MonthColumn::className(),
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            $htmlArray = [];

            if ($numberRange->insert_time) {
                $htmlArray[] = Yii::$app->formatter->asDate($numberRange->insert_time, 'medium');
            }

            if ($numberRange->update_time) {
                $htmlArray[] = Yii::$app->formatter->asDate($numberRange->update_time, 'medium');
            }

            if ($numberRange->date_stop) {
                $htmlArray[] = Yii::$app->formatter->asDate($numberRange->date_stop, 'medium');
            }

            return $htmlArray ?
                implode('<br/>', $htmlArray) :
                Yii::t('common', '(not set)');
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

if (NumberRange::isTriggerEnabled()) {
    echo $this->render('_indexTriggerEnabled');
} else {
    echo $this->render('_indexReset');
    echo $this->render('_indexFilterToPrefix');
    echo $this->render('_indexTriggerDisabled');
}

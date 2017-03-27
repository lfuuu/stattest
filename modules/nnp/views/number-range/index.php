<?php
/**
 * Диапазон номеров
 *
 * @var app\classes\BaseView $this
 * @var NumberRangeFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
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
use app\modules\nnp\filter\NumberRangeFilter;
use app\modules\nnp\models\NumberRange;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Диапазон номеров') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/number-range/'],
    ],
]) ?>

<?php
if (NumberRange::isTriggerEnabled()) {
    echo $this->render('_indexTriggerEnabled');
} else {
    echo $this->render('_indexPrefix');
    echo $this->render('_indexTriggerDisabled');
}
?>

<?php
$baseView = $this;

$filterColumns = [
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::className(),
        'indexBy' => 'code',
    ],
    [
        'attribute' => 'ndc',
        'class' => IntegerColumn::className(),
    ],
    [
        'label' => 'Кол-во номеров',
        'attribute' => 'numbers_count',
        'class' => IntegerRangeColumn::className(),
        'value' => function (NumberRange $numberRange) {
            return 1 + $numberRange->number_to - $numberRange->number_from;
        }
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
        'label' => 'Диапазон номеров',
        'attribute' => 'full_number_from',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return sprintf('%s<br>%s', $numberRange->full_number_from, $numberRange->full_number_to);
        }
    ],
    [
        'attribute' => 'operator_source',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'operator_id',
        'class' => OperatorColumn::className(),
        'countryCode' => $filterModel->country_code,
    ],
    [
        'attribute' => 'region_source',
        'class' => StringColumn::className(),
        'value' => function (NumberRange $numberRange) {
            return str_replace('|', ', ', $numberRange->region_source);
        }
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::className(),
        'countryCodes' => $filterModel->country_code,
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
        'isWithNullAndNotNull' => true,
        'countryCodes' => $filterModel->country_code,
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::className(),
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
        }
    ],
    [
        'attribute' => 'insert_time',
        'class' => MonthColumn::className(),
        'filterOptions' => [
            'colspan' => 2, // фильтр на оба столбца
        ],
    ],
    [
        'attribute' => 'date_stop',
        'filterOptions' => [
            'class' => 'collapse',
        ],
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
]);
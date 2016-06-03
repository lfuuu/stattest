<?php
/**
 * Диапазон номеров
 *
 * @var app\classes\BaseView $this
 * @var NumberRangeFilter $filterModel
 */

use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
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

<?= $this->render('_indexPrefix') ?>

<?php
$baseView = $this;

$filterColumns = [
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::className(),
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
        'attribute' => 'ndc',
        'class' => IntegerColumn::className(),
    ],
    [
        'label' => 'Диапазон номеров',
        'attribute' => 'number_from',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return sprintf('%06d<br>%06d', $numberRange->number_from, $numberRange->number_to);
        }
    ],
    [
        'attribute' => 'operator_source',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'operator_id',
        'class' => OperatorColumn::className(),
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
    ],
    [
        'attribute' => 'city_id',
//        'reverseCheckboxAttribute' => 'is_reverse_city_id', // выключен для упрощения интерфейса
        'class' => CityColumn::className(),
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'is_mob',
        'class' => YesNoColumn::className(),
        'yesLabel' => 'DEF',
        'noLabel' => 'ABC',
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
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
]);
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
use app\modules\nnp2\column\CityColumn;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp2\column\NdcTypeColumn;
use app\modules\nnp2\column\OperatorColumn;
use app\modules\nnp2\column\RegionColumn;
use app\modules\nnp2\filters\NumberRangeFilter;
use app\modules\nnp2\models\NumberRange;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Диапазон номеров', 'url' => '/nnp2/number-range/'],
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
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return $numberRange->full_number_from . '<br>' .
                $numberRange->full_number_to;
        }
    ],
    [
        'attribute' => 'geo_place_id',
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            $geoPlace = $numberRange->geoPlace;

            $verified = '';
            if ($geoPlace->is_valid) {
                $verified .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
            } else {
                $verified .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            }

            $html = $geoPlace->country->name_rus . $verified . '<br>' .
                strval($geoPlace->ndc ? : '-') . '<br>' .
                strval($geoPlace->region->name ? : '-') . '<br>' .
                strval($geoPlace->city->name ? : '-')
            ;

            $html = Html::a(
                $html,
                $geoPlace->getUrl()
            );

            return Html::tag('span', $html, ['style' => 'white-space : nowrap;']);
        },
    ],
    [
        'label' => 'Гео-родитель',
        'attribute' => 'geoPlace.parent_id',
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            $geoPlace = $numberRange->geoPlace->parent;
            if (!$geoPlace) {
                return '';
            }

            $verified = '';
            if ($geoPlace->is_valid) {
                $verified .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
            } else {
                $verified .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            }

            $html = $geoPlace->country->name_rus . $verified . '<br>' .
                strval($geoPlace->ndc ? : '-') . '<br>' .
                strval($geoPlace->region->name ? : '-') . '<br>' .
                strval($geoPlace->city->name ? : '-')
            ;

            $html = Html::a(
                $html,
                $geoPlace->getUrl()
            );

            return Html::tag('span', $html, ['style' => 'white-space : nowrap;']);
        },
    ],
    [
        'attribute' => 'country_code',
        'label' => 'Страна',
        'class' => CountryColumn::class,
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return $numberRange->country;
        }
    ],
    [
        'attribute' => 'ndc_str',
        'label' => 'Ndc',
        'class' => StringColumn::class,
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return $numberRange->ndc;
        }
    ],
    [
        'attribute' => 'region_id',
        'label' => 'Регион',
        'class' => RegionColumn::class,
        'countryCodes' => $filterModel->country_code,
        'isWithNullAndNotNull' => true,
        'isWithEmpty' => false,
        'showVerified' => true,
        'showParent' => true,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return $numberRange->region;
        }
    ],
    [
        'attribute' => 'city_id',
        'label' => 'Город',
        'class' => CityColumn::class,
        'countryCodes' => $filterModel->country_code,
        'regionIds' => $filterModel->region_id,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
        'showVerified' => true,
        'showParent' => true,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            return $numberRange->city;
        }
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::class,
        'isWithEmpty' => false,
        'showVerified' => true,
        'showParent' => true,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],

    [
        'attribute' => 'operator_id',
        'class' => OperatorColumn::class,
        'countryCode' => $filterModel->country_code,
        'isWithEmpty' => false,
        'showVerified' => true,
        'showParent' => true,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],

    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::class,
    ],

    [
        'attribute' => 'is_valid',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'allocation_date_start',
        'class' => DateRangeDoubleColumn::class,
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {

            if ($numberRange->allocation_date_start) {
                $htmlArray[] = Html::tag('span', Yii::$app->formatter->asDate($numberRange->allocation_date_start, 'medium'));
            } else {
                $htmlArray[] = '-';
            }

            if ($numberRange->allocation_date_stop) {
                $htmlArray[] = Html::tag('span', Yii::$app->formatter->asDate($numberRange->allocation_date_stop, 'medium'));
            } else {
                $htmlArray[] = '-';
            }

            return $htmlArray ?
                implode('<br/>', $htmlArray) :
                Yii::t('common', '(not set)');

            return $numberRange->allocation_date_start ?
                Html::tag('b', Yii::$app->formatter->asDate($numberRange->allocation_date_start, 'medium')) :
                Yii::$app->formatter->asDate($numberRange->insert_time, 'medium');
        },
    ],
    [
        'label' => 'Создано / выключено',
        'attribute' => 'insert_time',
        'class' => MonthColumn::class,
        'format' => 'html',
        'value' => function (NumberRange $numberRange) {
            $htmlArray = [];
            if ($numberRange->insert_time) {
                $htmlArray[] = Yii::$app->formatter->asDate($numberRange->insert_time, 'medium');
            } else {
                $htmlArray[] = '-';
            }

            if ($numberRange->allocation_date_stop || $numberRange->stop_time) {
                $htmlArray[] = $numberRange->allocation_date_stop ?
                    Html::tag('b', Yii::$app->formatter->asDate($numberRange->allocation_date_stop, 'medium')) :
                    Yii::$app->formatter->asDate($numberRange->stop_time, 'medium');
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

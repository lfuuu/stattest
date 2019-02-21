<?php
/**
 * Страны
 *
 * @var app\classes\BaseView $this
 * @var CountryFilter $filterModel
 */

use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\nnp\column\OperatorColumn;
use app\modules\nnp\filters\CountryFilter;
use app\modules\nnp\models\Country;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\db\ArrayExpression;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Страны', 'url' => '/nnp/country/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, Country $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => Url::to(['/nnp/country/edit', 'id' => $model->code]),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],

    [
        'attribute' => 'code',
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name_rus',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name_eng',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'alpha_2',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'alpha_3',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'prefix',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'prefixes',
        'value' => function ($data) {
            if ($data->prefixes instanceof ArrayExpression) {
                return implode(', ', $data->prefixes->getValue());
            }
            return '';
        },
        'width' => '10%'
    ],
    [
        'attribute' => 'is_open_numbering_plan',
        'class' => YesNoColumn::class
    ],
    [
        'attribute' => 'use_weak_matching',
        'class' => YesNoColumn::class
    ],
    [
        'attribute' => 'default_operator',
        'class' => OperatorColumn::class
    ],
    [
        'attribute' => 'default_type_ndc',
        'label' => 'NDC тип по-умолчанию',
        'class' => NdcTypeColumn::class
    ],
    [
        'label' => '',
        'format' => 'html',
        'value' => function (Country $country) {
            return Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $country->code, 'NumberRangeFilter[is_active]' => 1])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $country->code])
                );
        }
    ]
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/country/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
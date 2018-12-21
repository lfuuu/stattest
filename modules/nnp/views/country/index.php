<?php
/**
 * Страны
 *
 * @var app\classes\BaseView $this
 * @var CountryFilter $filterModel
 */

use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\filters\CountryFilter;
use app\modules\nnp\models\Country;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
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
        'attribute' => 'alpha_3',
        'class' => StringColumn::class,
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
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
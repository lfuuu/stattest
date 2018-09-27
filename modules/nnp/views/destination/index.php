<?php
/**
 * Направления
 *
 * @var app\classes\BaseView $this
 * @var DestinationFilter $filterModel
 */

use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\LandColumn;
use app\modules\nnp\column\PrefixColumn;
use app\modules\nnp\column\StatusColumn;
use app\modules\nnp\filters\DestinationFilter;
use app\modules\nnp\models\Destination;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Направления', 'url' => '/nnp/destination/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, Destination $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Destination $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],

    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],

    [
        'attribute' => 'land_id',
        'class' => LandColumn::class,
    ],

    [
        'attribute' => 'country_id',
        'class' => \app\modules\nnp\column\CountryColumn::className(),
    ],

    [
        'attribute' => 'status_id',
        'class' => StatusColumn::class,
    ],

    [
        'label' => 'Префиксы (+)',
        'attribute' => 'addition_prefix_destination',
        'class' => PrefixColumn::class,
        'isAddLink' => false,
        'format' => 'html',
        'value' => function (Destination $destination) {
            $htmlArray = [];
            foreach ($destination->additionPrefixDestinations as $prefixDestination) {
                $prefix = $prefixDestination->prefix;
                $htmlArray[] = Html::a($prefix->name, $prefix->getUrl());
            }

            return implode('<br />', $htmlArray);
        },
    ],

    [
        'label' => 'Префиксы (-)',
        'attribute' => 'subtraction_prefix_destination',
        'class' => PrefixColumn::class,
        'isAddLink' => false,
        'format' => 'html',
        'value' => function (Destination $destination) {
            $htmlArray = [];
            foreach ($destination->subtractionPrefixDestinations as $prefixDestination) {
                $prefix = $prefixDestination->prefix;
                $htmlArray[] = Html::a($prefix->name, $prefix->getUrl());
            }

            return implode('<br />', $htmlArray);
        },
    ],

    [
        'label' => 'Префиксы номеров',
        'format' => 'html',
        'value' => function (Destination $destination) {
            return Html::a('Скачать', ['/nnp/destination/download', 'id' => $destination->id]);
        },
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/destination/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
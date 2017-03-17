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
use app\modules\nnp\filter\DestinationFilter;
use app\modules\nnp\models\Destination;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Направления') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/destination/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
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
        'class' => StringColumn::className(),
    ],

    [
        'attribute' => 'land_id',
        'class' => LandColumn::className(),
    ],

    [
        'attribute' => 'status_id',
        'class' => StatusColumn::className(),
    ],

    [
        'label' => 'Префиксы (+)',
        'attribute' => 'addition_prefix_destination',
        'class' => PrefixColumn::className(),
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
        'class' => PrefixColumn::className(),
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
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/destination/new/']),
    'columns' => $columns,
]);
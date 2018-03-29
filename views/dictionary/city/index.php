<?php
/**
 * Список городов
 *
 * @var app\classes\BaseView $this
 * @var CityFilter $filterModel
 */

use app\classes\grid\column\universal\CityBillingMethodColumn;
use app\classes\grid\column\universal\ConnectionPointColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IsShowInLkColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\City;
use app\models\filter\CityFilter;
use app\widgets\GridViewSequence\GridViewSequence;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Города', 'url' => '/dictionary/city/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, City $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, City $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
    ],
    [
        'attribute' => 'connection_point_id',
        'class' => ConnectionPointColumn::className(),
    ],
    [
        'attribute' => 'voip_number_format',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'billing_method_id',
        'class' => CityBillingMethodColumn::className(),
    ],
    [
        'attribute' => 'in_use',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'is_show_in_lk',
        'class' => IsShowInLkColumn::className(),
    ],
    [
        'attribute' => 'order',
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/city/new/']),
    'columns' => $columns,
]);
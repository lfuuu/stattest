<?php

/**
 * @var app\classes\BaseView $this
 * @var PriceLevelFilter $filterModel
 */

use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use app\widgets\GridViewSequence\GridViewSequence;
use kartik\grid\ActionColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\models\filter\PriceLevelFilter;
use app\models\PriceLevel;
?>

<?= Html::formLabel('Уровни цен'); ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Уровни цен', 'url' => Url::toRoute(['/dictionary/price-level'])],
    ],
]); ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => Html::tag('div', '{update} {delete}', ['class' => 'text-center']),
        'buttons' => [
            'update' => function ($url, PriceLevel $model, $key) use ($baseView) {
                return $baseView->render(
                    '//layouts/_actionEdit',
                    [
                        'url' => Url::toRoute([
                            '/dictionary/price-level/edit',
                            'id' => $model->id,
                        ]),
                    ]
                );
            },
            'delete' => function ($url, $model, $key) use ($baseView) {
                return $baseView->render(
                    '//layouts/_actionDrop',
                    [
                        'url' => Url::toRoute([
                            '/dictionary/price-level/delete',
                            'id' => $model->id,
                        ]),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/price-level/add']),
    'columns' => $columns,
]); 
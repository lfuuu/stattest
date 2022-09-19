<?php

/**
 * @var app\classes\BaseView $this
 * @var SourceFilter $filterModel
 */

use app\classes\grid\column\universal\YesNoColumn;
use app\models\filter\voip\SourceFilter;
use app\models\voip\Source;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use app\widgets\GridViewSequence\GridViewSequence;
use kartik\grid\ActionColumn;
use app\classes\grid\column\universal\StringColumn;
?>

<?= Html::formLabel('Телефония: Источники'); ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Телефония: Источники', 'url' => Url::toRoute(['/dictionary/voip/source'])],
    ],
]); ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'code',
        'class' => StringColumn::class,
        'format' => 'raw',
        'value' => function(Source $source) {
            return Html::a($source->code, Url::to(['dictionary/voip/source/edit', 'code' => $source->code]));
        }
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'is_service',
        'class' => YesNoColumn::class,
    ],
    [
        'class' => ActionColumn::class,
        'template' => Html::tag('div', '{delete}', ['class' => 'text-center']),
        'buttons' => [
            'delete' => function ($url, Source $model, $key) use ($baseView) {
                return $baseView->render(
                    '//layouts/_actionDrop',
                    [
                        'url' => Url::toRoute([
                            '/dictionary/voip/source/delete',
                            'code' => $model->code,
                        ]),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/voip/source/add']),
    'columns' => $columns,
]); 
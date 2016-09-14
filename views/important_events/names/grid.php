<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\grid\GridView;
use kartik\grid\ActionColumn;
use app\classes\Html;
use app\models\important_events\ImportantEventsNames;
use app\classes\grid\column\universal\WithEmptyFilterColumn;
use app\classes\grid\column\important_events\GroupColumn;
use app\classes\grid\column\universal\TagsColumn;

/** @var ImportantEventsNames $dataProvider */
/** @var ImportantEventsNames $filterModel */
/** @var \yii\web\View $baseView */

$baseView = $this;

echo Html::formLabel('Список групп событий');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        'Список названий событий'
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'attribute' => 'code',
            'class' => WithEmptyFilterColumn::className(),
            'label' => 'Код',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->code, ['/important_events/names/edit', 'id' => $data->id]);
            },
            'width' => '20%',
        ],
        [
            'attribute' => 'title',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function($data) {
                return
                    Html::a($data->value, ['/important_events/names/edit', 'id' => $data->id]) .
                    (
                        $data->comment
                            ? Html::tag('br') . Html::tag('label', $data->comment, ['class' => 'label label-default'])
                            : ''
                    );
            },
            'width' => '*',
        ],
        [
            'attribute' => 'tags',
            'class' => TagsColumn::className(),
        ],
        [
            'attribute' => 'group_id',
            'class' => GroupColumn::className(),
            'label' => 'Группа',
            'width' => '20%',
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, ImportantEventsNames $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute(['/important_events/names/delete', 'id' => $model->id]),
                        ]
                    );
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/important_events/names/edit']),
]);
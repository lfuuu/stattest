<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\grid\GridView;
use kartik\grid\ActionColumn;
use app\classes\Html;
use app\forms\user\GroupForm;
use app\models\important_events\ImportantEventsSources;

/** @var GroupForm $dataProvider */
/** @var \yii\web\View $baseView */

$baseView = $this;

echo Html::formLabel('Список источников событий');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        'Список источников событий'
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'code',
            'label' => 'Код',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->code, ['/important_events/sources/edit', 'id' => $data->id]);
            },
            'width' => '20%',
        ],
        [
            'attribute' => 'title',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->title, ['/important_events/sources/edit', 'id' => $data->id]);
            },
            'width' => '*',
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, ImportantEventsSources $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute(['/important_events/sources/delete', 'id' => $model->id]),
                        ]
                    );
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/important_events/sources/edit']),
]);
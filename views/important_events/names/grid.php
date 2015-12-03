<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\classes\Html;
use app\forms\user\GroupForm;
use app\models\important_events\ImportantEventsRulesConditions;

/** @var GroupForm $dataProvider */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['/important_events/names/delete', 'id' => $model->id],
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить название ?")',
            ]
        );
    },
];

echo Html::formLabel('Список групп событий');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        'Список названий событий'
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
                return Html::a($data->code, ['/important_events/names/edit', 'code' => $data->id]);
            },
            'width' => '20%',
        ],
        [
            'attribute' => 'title',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->value, ['/important_events/names/edit', 'code' => $data->id]);
            },
            'width' => '*',
        ],
        [
            'attribute' => 'group_id',
            'label' => 'Группа',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->group->title, ['/important_events/groups/edit', 'id' => $data->group->id]);
            },
            'width' => '20%',
        ],
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'buttons' => $recordBtns,
            'hAlign' => 'center',
            'width' => '90px',
        ]
    ],
    'pjax' => false,
    'toolbar'=> [
        [
            'content' =>
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Добавить',
                    ['/important_events/names/edit'],
                    [
                        'data-pjax' => 0,
                        'class' => 'btn btn-success btn-sm form-lnk',
                    ]
                ),
        ]
    ],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
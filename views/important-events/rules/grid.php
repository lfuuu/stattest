<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\classes\Html;
use app\forms\user\GroupForm;
use app\models\ImportantEventsRules;

/** @var GroupForm $dataProvider */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['/important-events/rules-delete', 'rule_id' => $model->id],
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить правило ?")',
            ]
        );
    },
];

echo Html::formLabel('Список правил на события');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important-events'])],
        'Список правил на события'
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'title',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->title, ['/important-events/rules-edit', 'id' => $data->id]);
            },
            'width' => '*',
        ],
        [
            'label' => 'Действие',
            'format' => 'raw',
            'value' => function($data) {
                return $data->getAction($data->action)->title;
            },
            'width' => '20%',
        ],
        [
            'label' => 'Шаблон сообщения',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->template->name, ['/message/template/edit', 'id' => $data->template->id], ['target' => '_blank']);
            },
            'width' => '20%',
        ],
        [
            'label' => 'Условие',
            'format' => 'raw',
            'value' => function($data) {
                return Html::tag('b', $data->property) . ' ' . ImportantEventsRules::$conditions[$data->condition] . ' ' . Html::tag('b', $data->value);
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
                    ['/important-events/rules-edit'],
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
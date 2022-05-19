<?php

use app\classes\Html;
use app\forms\user\DepartmentForm;
use kartik\grid\GridView;

/** @var DepartmentForm $dataProvider */

$recordBtns = [
    'delete' => function ($url, $model, $key) {
        if (!\Yii::$app->user->can('users.change')) {
            return '';
        }
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['delete', 'id' => $model->id],
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить отдел ?")',
            ]
        );
    },
];

echo Html::formLabel('Отделы');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'buttons' => $recordBtns,
            'hAlign' => 'center',
            'width' => '90px',
        ],
        [
            'attribute' => 'name',
            'label' => 'Отдел',
        ],
        [
            'attribute' => 'usersCount',
            'label' => 'Кол-во пользователей',
            'width' => '20%',
        ],
    ],
    'pjax' => true,
    'toolbar' => [
        [
            'content' => \Yii::$app->user->can('users.change') ?
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Добавить',
                    ['add'],
                    [
                        'data-pjax' => 0,
                        'class' => 'btn btn-success btn-sm form-lnk',
                        'onClick' => 'return showIframePopup(this);',
                        'data-width' => 400,
                        'data-height' => 450,
                    ]
                ) : '',
        ]
    ],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
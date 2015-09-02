<?php

use kartik\grid\GridView;
use app\classes\Html;
use app\forms\user\GroupForm;

/** @var GroupForm $dataProvider */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        if ($model->usersCount > 0)
            return Html::tag('span', '', [
                'title' => 'В группе есть пользователи',
                'class' => 'glyphicon glyphicon-trash',
                'style' => 'opacity: 0.5;'
            ]) .
            Html::tag('span', 'Удаление', ['style' => 'margin-left: 4px;']);

        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['delete', 'id' => $model->usergroup],
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить группу ?")',
            ]
        );
    },
];
?>

<legend>
    Группы
</legend>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\group\GroupNameColumn',
            'attribute' => 'usergroup',
            'label' => 'Группа',
        ],
        [
            'attribute' => 'comment',
            'label' => 'Комментарий',
        ],
        [
            'attribute' => 'usersCount',
            'label' => 'Кол-во пользователей',
        ],
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'buttons' => $recordBtns,
            'hAlign' => 'center',
            'width' => '90px',
        ]
    ],
    'pjax' => true,
    'toolbar'=> [
        [
            'content' =>
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
?>
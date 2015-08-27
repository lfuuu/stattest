<?php

use kartik\grid\GridView;
use app\classes\Html;
use app\forms\user\UserListForm;

/** @var UserListForm $dataProvider */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            '#' . $model->user,
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить пользователя ?")',
            ]
        );
    },
];
?>

<legend>
    Операторы
</legend>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        'user' => [
            'class' => 'app\classes\grid\column\user\UserNameColumn',
            'value' => 'user',
            'attribute' => 'user',
            'label' => 'Логин',
            'width' => '10%',
        ],
        'usergroup' => [
            'class' => 'app\classes\grid\column\user\UserGroupColumn',
            'label' => 'Группа',
            'width' => '10%',
        ],
        'depart_id' => [
            'class' => 'app\classes\grid\column\user\UserDepartColumn',
            'label' => 'Отдел',
            'width' => '10%',
        ],
        'name' => [
            'class' => 'app\classes\grid\column\user\UserNameColumn',
            'label' => 'Полное имя',
            'width' => '45%',
        ],
        'trouble_redirect' => [
            'class' => 'app\classes\grid\column\user\UserNameColumn',
            'value' => 'trouble_redirect',
            'label' => 'Редирект',
            'width' => '200px',
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
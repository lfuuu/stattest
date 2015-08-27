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
            'class' => 'app\classes\grid\column\NameColumn',
            'attribute' => 'user',
            'label' => 'Логин',
            'width' => '10%',
        ],
        'usergroup' => [
            'attribute' => 'usergroup',
            'width' => '10%',
        ],
        'depart_id' => [
            'class' => 'app\classes\grid\column\user\UserDepartColumn',
            'width' => '10%',
        ],
        'name' => [
            'class' => 'app\classes\grid\column\NameColumn',
            'attribute' => 'name',
            'label' => 'Полное имя',
            'width' => '45%',
        ],
        'trouble_redirect' => [
            'attribute' => 'trouble_redirect',
            'width' => '200px',
        ],
        'photo' => [
            'attribute' => 'photo',
            'width' => '100px',
        ],
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'buttons' => $recordBtns,
            'hAlign' => 'left',
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
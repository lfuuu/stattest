<?php

use kartik\grid\GridView;
use app\classes\Html;
use app\forms\user\UserListForm;

/** @var UserListForm $dataProvider */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['delete', 'id' => $model->id],
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
        'name' => [
            'class' => 'app\classes\grid\column\user\UserNameColumn',
            'label' => 'Полное имя',
        ],
        'usergroup' => [
            'class' => 'app\classes\grid\column\user\UserGroupColumn',
            'label' => 'Группа',
            'width' => '20%',
        ],
        'depart_id' => [
            'class' => 'app\classes\grid\column\user\UserDepartColumn',
            'label' => 'Отдел',
            'width' => '10%',
        ],
        'enabled' => [
            'class' => 'app\classes\grid\column\user\UserEnabledColumn',
            'label' => 'Активность',
            'width' => '5%',
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
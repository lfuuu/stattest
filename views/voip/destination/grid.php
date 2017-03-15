<?php

use app\classes\Html;
use kartik\grid\GridView;

$recordBtns = [
    'delete' => function ($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            '/voip/destination/delete/?id=' . $model->id,
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить запись ?")',
            ]
        );
    },
];

echo Html::formLabel('Направления');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'header' => '',
            'buttons' => $recordBtns,
            'hAlign' => 'center',
            'width' => '7%',
        ],
        [
            'class' => 'app\classes\grid\column\NameColumn',
        ],
    ],
    'pjax' => true,
    'toolbar' => [
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
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
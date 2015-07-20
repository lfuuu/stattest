<?php

use kartik\grid\GridView;
use app\classes\Html;
use app\models\voip\Prefixlist;

$recordBtns = [
    'delete' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            '/voip/prefixlist/delete/?id=' . $model->id,
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить запись ?")',
            ]
        );
    },
];
?>

<legend>
    Списки префиксов
</legend>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\NameColumn',
        ],
        [
            'class' => 'app\classes\grid\column\FromArrayColumn',
            'variants' => Prefixlist::$types,
            'attribute' => 'type_id',
            'label' => 'Тип',
            'width' => '20%',
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'header' => '',
            'buttons' => $recordBtns,
            'hAlign' => 'center',
            'width' => '7%',
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
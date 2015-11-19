<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\classes\Html;
use app\forms\user\GroupForm;

/** @var GroupForm $dataProvider */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['delete', 'id' => $model->id],
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить шаблон ?")',
            ]
        );
    },
];

echo Html::formLabel('Список шаблонов');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Сообщения', 'url' => Url::toRoute(['message/template'])],
        'Список шаблонов'
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'name',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a($data->name, ['/message/template/edit', 'id' => $data->id]);
            },
        ],
        /*[
            'attribute' => 'type',
            'label' => 'Тип',
        ],
        [
            'attribute' => 'lang_code',
            'label' => 'Язык',
        ],*/
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
                    ['edit'],
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
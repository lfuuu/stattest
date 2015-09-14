<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use app\forms\person\PersonForm;
use app\classes\Html;

/** @var $dataProvider ActiveDataProvider */
/** @var $model PersonForm */

$recordBtns = [
    'delete' => function($url, $model, $key) {
        if ($model->canDelete !== true)
            return Html::tag('span', '', [
                'title' => 'Данная персона указана в организациях',
                'class' => 'glyphicon glyphicon-trash',
                'style' => 'opacity: 0.5;'
            ]) .
            Html::tag('span', 'Удаление', ['style' => 'margin-left: 4px;']);

        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            '/person/delete/?id=' . $model->id,
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить запись ?")',
            ]
        );
    },
];

echo Html::formLabel('Ответственные лица');

echo GridView::widget([
    'id' => 'PersonList',
    'dataProvider' => $dataProvider,
    'columns' => [
        'name_nominative' => [
            'class' => 'app\classes\grid\column\NameColumn',
            'attribute' => 'name_nominative',
            'label' => 'ФИО',
            'width' => '30%',
        ],
        'post_nominative' => [
            'attribute' => 'post_nominative',
            'label' => 'Должность',
            'width' => '30%',
        ],
        'organizations' => [
            'class' => 'app\classes\grid\column\PersonOrganizationColumn',
            'width' => '20%',
        ],
        'signature_file_name' => [
            'class' => 'app\classes\grid\column\SignatureColumn',
            'width' => '10%',
        ],
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'buttons' => $recordBtns,
            'hAlign' => 'left',
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
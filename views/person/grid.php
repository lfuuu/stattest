<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use app\forms\person\PersonForm;
use app\classes\Html;

/** @var $dataProvider ActiveDataProvider */
/** @var $model PersonForm */

$recordBtns = [
    'update' => function($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-pencil"></span>',
            '/person/edit/?id=' . $model->id,
            [
                'title' => Yii::t('kvgrid', 'Update'),
                'data-pjax' => 0,
                'data-height' => 650,
                'onClick' => 'return showIframePopup(this);',
            ]
        );
    },
    'delete' => function($url, $model, $key) {
        if ($model->canDelete !== true)
            return Html::tag('span', '', [
                'title' => 'Данная персона указана в организациях',
                'class' => 'glyphicon glyphicon-trash',
                'style' => 'opacity: 0.5;'
            ]);

        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span>',
            '/person/delete/?id=' . $model->id,
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить запись ?")',
            ]
        );
    },
];

echo GridView::widget([
    'id' => 'PersonList',
    'dataProvider' => $dataProvider,
    'columns' => [
        'name_nominativus' => [
            'class' => 'app\classes\grid\column\NameColumn',
            'attribute' => 'name_nominativus',
            'label' => 'ФИО',
            'width' => '30%',
        ],
        'post_nominativus' => [
            'attribute' => 'post_nominativus',
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
            'template' => '<div style="text-align: center;">{update} {delete}</div>',
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
                        'data-height' => 650,
                        'onClick' => 'return showIframePopup(this);',
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
        'heading' => 'Персоналии',
    ],
]);
?>
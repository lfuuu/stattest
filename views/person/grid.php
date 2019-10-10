<?php

use app\classes\Html;
use app\forms\person\PersonForm;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

/** @var $dataProvider ActiveDataProvider */
/** @var $model PersonForm */

$recordBtns = [
    'delete' => function ($url, $model, $key) {
        if ($model->canDelete !== true) {
            return Html::tag('span', '', [
                    'title' => 'Данная персона указана в организациях',
                    'class' => 'glyphicon glyphicon-trash',
                    'style' => 'opacity: 0.5;'
                ]) .
                Html::tag('span', 'Удаление', ['style' => 'margin-left: 4px;']);
        }

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
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Ответственные лица', 'url' => Url::toRoute(['/person'])],
    ],
]);

echo GridView::widget([
    'id' => 'PersonList',
    'dataProvider' => $dataProvider,
    'columns' => [
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'buttons' => $recordBtns,
            'hAlign' => 'left',
        ],
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
?>
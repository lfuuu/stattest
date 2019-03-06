<?php
/* @var $this \yii\web\View */

use app\classes\grid\GridView;
use kartik\grid\ActionColumn;
use yii\helpers\Html;

/* @var $dataProvider \yii\data\ActiveDataProvider */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/roistat-number-fields/create']),
    'columns' => [
        'id',
        'number',
        [
            'attribute' => 'fields',
            'format' => 'html',
            'value' => function ($data) {
                $fields = json_decode($data->fields, true);
                $str = '';
                foreach ($fields as $key => $val) {
                    $str .= $key . ': ' . $val . '<br>';
                }
                return $str;
            }
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function ($url, $model) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-pencil"> </span>',
                        ['create', 'id' => $model->id]
                    );
                },
                'delete' => function ($url, $model) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-trash"> </span>',
                        ['delete', 'id' => $model->id],
                        [
                            'data' => [
                                'confirm' => 'Вы уверены что хотите удалить?',
                            ],
                        ]
                    );
                },
            ]
        ],
    ]
]);

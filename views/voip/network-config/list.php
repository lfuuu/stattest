<?php
use yii\helpers\Url;
use kartik\grid\GridView;
use app\classes\Html;

echo Html::formLabel('Местные Префиксы');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'Точка присоединения',
            'format' => 'raw',
            'value' => function ($data) use ($connectionPoints) {
                return $connectionPoints[ $data->instance_id ];
            },
        ],
        [
            'label' => 'Ид',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->id, Url::toRoute(['voip/network-config/edit', 'id' => $data->id]));
            },
        ],
        [
            'label' => 'Название',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->name, Url::toRoute(['/voip/network-config/edit', 'id' => $data->id]));
            },
        ],
        [
            'label' => 'Файлы',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a('файлы', Url::toRoute(['voip/network-config/files', 'networkConfigId' => $data->id]));
            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '<div style="text-align: center;">{delete}</div>',
            'header' => '',
            'buttons' => [
                'delete' => function($url, $model, $key) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-trash"></span> Удаление',
                        '/voip/network-config/delete/?id=' . $model->id,
                        [
                            'title' => Yii::t('kvgrid', 'Delete'),
                            'data-pjax' => 0,
                            'onClick' => 'return confirm("Вы уверены, что хотите удалить запись?")',
                        ]
                    );
                },
            ],
            'hAlign' => 'center',
            'width' => '7%',
        ]
    ],
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
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
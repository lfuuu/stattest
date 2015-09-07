<?php
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
                return Html::a($data->id, '/voip/network-config/edit?id=' . $data->id);
            },
        ],
        [
            'label' => 'Название',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->name, '/voip/network-config/edit?id=' . $data->id);
            },
        ],
        [
            'label' => 'Файлы',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a('файлы', '/voip/network-config/files?networkConfigId=' . $data->id);
            },
        ],
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
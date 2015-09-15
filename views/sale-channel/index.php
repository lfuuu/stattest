<?php
use kartik\grid\GridView;
use app\classes\Html;

echo Html::formLabel('Каналы продаж');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label'=> (new $dataProvider->query->modelClass)->getAttributeLabel('name'),
            'format' => 'raw',
            'value'=>function ($data) {
                return \yii\helpers\Html::a($data->name,'/sale-channel/edit?id='.$data->id);
            },
        ],
        'dealer_id',
        'is_agent',
        'interest',
        'courierName',
    ],
    'toolbar'=> [
        [
            'content' =>
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Добавить',
                    ['create'],
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
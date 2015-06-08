<?php
use \kartik\grid\GridView;
use kartik\widgets\Select2;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => "{items}\n{pager}",
    'columns' => [
        'id' => [
            'label' => (new $dataProvider->query->modelClass)->attributeLabels()['id'],
            'format' => 'raw',
            'value' => function($data){
                return '<a href="/client/clientview?id='.$data->id.'">'.$data->id.'</a>';
            }
        ],
        'companyName',
        'inn',
        'managerName',
        'channelName',
    ],
]);